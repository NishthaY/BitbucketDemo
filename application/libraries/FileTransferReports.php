<?php defined('BASEPATH') OR exit('No direct script access allowed');

use \phpseclib\Net\SFTP;
use \phpseclib\Crypt\RSA;

class filetransferreports extends A2PLibrary
{
    public $debug;

    private $report_id_filter;
    private $company_id_filter;
    private $companyparent_id_filter;

    private $hostname;
    private $username;
    private $destination_path;
    private $target_filename;
    private $protocol;
    private $port;
    private $transfer_company_id;
    private $transfer_companyparent_id;
    public $report_date;

    public function __construct($debug=false)
    {
        parent::__construct($debug);
        $this->_init();
        $this->debug = false;
        $this->report_id_filter         = array();
        $this->company_id_filter        = array();
        $this->companyparent_id_filter  = array();
    }
    public function addReportIdFilter($report_id)
    {

        $report_id = GetStringValue($report_id);
        if ( $report_id === '' ) return;
        if ( strtoupper($report_id) === 'NULL' ) return;

        // NOTE: If they try to filter on multiple reports from different import
        // dates, they will only get the reports from the import date of the last
        // report added.  This might be an issue later, but for now this is not a
        // problem.

        // When filtering by a specific report, we know what the report date
        // should be.  Set that now.  If we don't do this, then we will assume
        // the last finalized report set and that might not be what they
        // are looking for.
        $details = $this->ci->Reporting_model->select_report_filename_details($report_id);
        if ( GetArrayStringValue('ImportDate', $details) !== '' )
        {
            $this->report_date = GetArrayStringValue('ImportDate', $details);
        }

        $this->report_id_filter[] = $report_id;
    }
    public function addCompanyIdFilter($company_id)
    {
        $company_id = GetStringValue($company_id);
        if ( $company_id === '' ) return;
        if ( strtoupper($company_id) === 'NULL' ) return;

        $this->company_id_filter[] = $company_id;
    }
    public function addCompanyParentIdFilter($companyparent_id)
    {
        $company_id = GetStringValue($companyparent_id);
        if ( $companyparent_id === '' ) return;
        if ( strtoupper($companyparent_id) === 'NULL' ) return;

        $this->companyparent_id_filter[] = $companyparent_id;
    }
    public function execute( $company_id, $user_id=null )
    {
        parent::execute( $company_id, $user_id );

        $fp = null;
        try
        {
            // PRODCOPY
            // Do not allow file transfers to happen on prodcopy.
            if ( APP_NAME === 'a2p-prodcopy' ) throw new Exception("FileTransfer not allowed on PRODCOPY.");

            // Set the date we will be pulling our reports for.  If it's not already set
            // then we will default to the most recently finalized report.
            if ( GetStringValue($this->report_date) === '' ) $this->report_date = GetRecentDate($company_id);

            if ( GetStringValue($company_id) === '' ) throw new Exception("Missing required input: company_id");
            if ( GetStringValue($user_id) === '' ) $user_id = GetSessionValue('user_id');
            if ( GetStringValue($user_id) === '' ) throw new Exception("Missing required input: user_id");
            if ( GetStringValue($this->report_date) === '' ) throw new Exception("Unable to determine report date.");

            $transfers = $this->_getTransfers($company_id);
            $transfers = $this->_filterTransfersByEnabledFeature($transfers);
            $transfers = $this->_filterTransfersByCompanyId($transfers);
            $transfers = $this->_filterTransfersByCompanyParentId($transfers);

            foreach($transfers as $transfer)
            {

                // Make sure there is not cross contamination from each transfer.
                $this->_init();

                // Collect our transfer data.
                $this->username = GetArrayStringValue('Username', $transfer);
                $this->hostname = GetArrayStringValue('Hostname', $transfer);
                $this->destination_path = GetArrayStringValue('DestinationPath', $transfer);
                $password = GetArrayStringValue('Password', $transfer);
                $ssh_key = GetArrayStringValue('SSHKey', $transfer);
                $this->protocol = GetArrayStringValue('Protocol', $transfer);
                $this->port = GetArrayStringValue('Port', $transfer);
                $this->transfer_company_id = GetArrayStringValue("CompanyId", $transfer);
                $this->transfer_companyparent_id = GetArrayStringValue("CompanyParentId", $transfer);

                // Collect the reports for this company and the most recent import.
                $reports = $this->ci->FileTransfer_model->get_reports_for_transfer($company_id, $this->report_date);
                foreach($reports as $report)
                {
                    $carrier_id = GetArrayStringValue('CarrierId', $report);
                    $report_type = GetArrayStringValue('ReportTypeCode', $report);
                    $report_date = GetArrayStringValue('ReportDate', $report);
                    $report_id = GetArrayStringValue('CompanyReportId', $report);

                    // If the report_filter array on this class is not empty, we only want to deliver only specific
                    // reports from the collection of available reports.
                    if ( ! empty($this->report_id_filter) )
                    {
                        if ( ! in_array($report_id, $this->report_id_filter) )
                        {
                            continue;
                        }
                    }

                    $this->target_filename = GetReportFilename($report_id);
                    $source_filename = "{$carrier_id}." . fRightBack($this->target_filename, '.');
                    if ($this->debug) print "  Report Id: [{$report_id}]\n";
                    if ($this->debug) print "  Report Filename: [{$this->target_filename}]\n";
                    if ($this->debug) print "  --\n";

                    S3GetClient();

                    $prefix = GetConfigValue('reporting_prefix');
                    $prefix  = replaceFor($prefix, 'COMPANYID', $company_id);
                    $prefix  = replaceFor($prefix, 'DATE', $report_date);
                    $prefix  = replaceFor($prefix, 'TYPE', $report_type);

                    $local_filename = null;
                    try
                    {
                        set_error_handler('error_to_exception',E_WARNING);

                        $exists = S3DoesFileExist( S3_BUCKET, $prefix, $source_filename );
                        if ( ! $exists ) throw new Exception('Report not found on AWS server.');
                        $local_filename = $this->_decodeFileForTransfer($company_id, $prefix, $source_filename);
                        if ( $local_filename === FALSE ) throw new Exception("Unable to prepare the file locally for delivery.");


                        $fp = fopen($local_filename, 'r');
                        if ( is_resource($fp) )
                        {
                            if ( $this->protocol === FILE_TRANSFER_SFTP_CODE && $ssh_key !== '' ) $this->_transferSFTPByKey($fp, $ssh_key, $password, $user_id);
                            if ( $this->protocol === FILE_TRANSFER_SFTP_CODE && $ssh_key === '' ) $this->_transferSFTPByPassword($fp, $password, $user_id);
                            if ( is_resource($fp) ) fclose($fp);
                        }

                        if ( file_exists($local_filename) ) unlink($local_filename);
                        restore_error_handler();

                    }
                    catch(Exception $e)
                    {
                        restore_error_handler();
                        if ( is_resource($fp) ) fclose($fp);

                        if ( file_exists($local_filename) ) unlink($local_filename);

                        $audit = array();
                        $audit['hostname'] = $this->hostname;
                        $audit['username'] = $this->username;
                        $audit['output'] = "{$this->destination_path}{$this->target_filename}";
                        $audit['protocol'] = "{$this->protocol}";
                        $audit['port'] = "{$this->port}";
                        $audit['error'] = $e->getMessage();
                        AuditIt(__CLASS__ . '-failure', $audit, $user_id, $this->transfer_company_id, $this->transfer_companyparent_id);
                    }
                }

                // OTHER REPORTS
                // Non-Carrier Specific Reports.

                // The potential issues report is not broken down by carrier but by line of import file.
                // If the user is not asking for a specific report, then they should get it.
                if ( empty($this->report_id_filter) )
                {
                    $this->_transferPotentialIssuesReport($company_id, $user_id, $this->report_date, $transfer);
                }

            }
        }
        catch(Exception $e)
        {
            if ( is_resource($fp) ) fclose($fp);
            throw $e;
        }

    }
    private function _init()
    {
        $this->hostname = '';
        $this->username = '';
        $this->destination_path = '';
        $this->target_filename = '';
        $this->protocol = '';
        $this->port = '';
        $this->company_parent_id = '';
    }

    /**
     * _transferPotentialIssuesReport
     *
     * This function will transfer the potential issues report for the specified
     * date using the details stored in the transfer object.
     *
     * @param $company_id
     * @param $user_id
     * @param $report_date
     * @param $transfer
     */
    private function _transferPotentialIssuesReport( $company_id, $user_id, $report_date, $transfer )
    {
        // First check and see if there is a warning report.  If not, done.
        $report = new GenerateWarningReport();
        if ( ! $report->doesReportExist($company_id, $report_date) ) return;

        // grab the password information out of the transfer object.
        $password = GetArrayStringValue('Password', $transfer);
        $ssh_key = GetArrayStringValue('SSHKey', $transfer);

        // Setup the filenames fo the transfer.
        $this->target_filename = GetProcessReportFilename($company_id);

        S3GetClient();
        $prefix = GetConfigValue('reporting_prefix');
        $prefix  = replaceFor($prefix, 'COMPANYID', $company_id);
        $prefix  = replaceFor($prefix, 'DATE', GetUploadDateFolderName($company_id, $report_date));
        $prefix  = replaceFor($prefix, 'TYPE', REPORT_TYPE_ISSUES_CODE);

        $source_filename = $report->getFilename();
        if ($this->debug) print "  Report Filename: [{$this->target_filename}]\n";
        if ($this->debug) print "  Source Filename: [{$source_filename}]\n";
        if ($this->debug) print "           Prefix: [{$prefix}]\n";
        if ($this->debug) print "  --\n";

        $fp = null;
        $local_filename = null;
        try
        {
            set_error_handler('error_to_exception',E_WARNING);

            $exists = S3DoesFileExist( S3_BUCKET, $prefix, $source_filename );
            if ( ! $exists ) throw new Exception('Report not found on AWS server.');
            $local_filename = $this->_decodeFileForTransfer($company_id, $prefix, $source_filename);
            if ( $local_filename === FALSE ) throw new Exception("Unable to prepare the file locally for delivery.");


            $fp = fopen($local_filename, 'r');
            if ( is_resource($fp) )
            {
                if ( $this->protocol === FILE_TRANSFER_SFTP_CODE && $ssh_key !== '' ) $this->_transferSFTPByKey($fp, $ssh_key, $password, $user_id);
                if ( $this->protocol === FILE_TRANSFER_SFTP_CODE && $ssh_key === '' ) $this->_transferSFTPByPassword($fp, $password, $user_id);
                if ( is_resource($fp) ) fclose($fp);
            }

            if ( file_exists($local_filename) ) unlink($local_filename);
            restore_error_handler();
        }
        catch(Exception $e)
        {
            restore_error_handler();
            if ( is_resource($fp) ) fclose($fp);

            if ( file_exists($local_filename) ) unlink($local_filename);

            $audit = array();
            $audit['hostname'] = $this->hostname;
            $audit['username'] = $this->username;
            $audit['output'] = "{$this->destination_path}{$this->target_filename}";
            $audit['protocol'] = "{$this->protocol}";
            $audit['port'] = "{$this->port}";
            $audit['error'] = $e->getMessage();
            AuditIt(__CLASS__ . '-failure', $audit, $user_id, $this->transfer_company_id, $this->transfer_companyparent_id);

        }

    }

    /**
     * _transferSFTPByKey
     *
     * Helper function that will transfer a file via SFTP by SSH Key.
     *
     * @param $fp
     * @param $ssh_key
     * @param $key_password
     * @param $user_id
     * @throws Exception
     */
    private function _transferSFTPByKey($fp, $ssh_key, $key_password, $user_id)
    {
        if ($this->debug) print "Processing SFTP via SSH key.\n";
        $sftp = new SFTP($this->hostname, $this->port);
        $rsa = new RSA();
        $rsa->setPassword($key_password);
        $rsa->loadKey($ssh_key);
        if (!$sftp->login($this->username, $rsa)) {
            throw new Exception('Unable to authenticate with server over the SFTP protocol with key.');
        }
        $sftp->put($this->destination_path . $this->target_filename, $fp);

        $audit = array();
        $audit['hostname'] = $this->hostname;
        $audit['username'] = $this->username;
        $audit['output'] = "{$this->destination_path}{$this->target_filename}";
        $audit['protocol'] = "{$this->protocol}";
        $audit['port'] = "{$this->port}";
        AuditIt(__CLASS__ . '-success', $audit, $user_id, $this->transfer_company_id, $this->transfer_companyparent_id);

    }

    /**
     * _transferSFTPByPassword
     *
     * Helper function that will transfer a file via SFTP by user password.
     *
     * @param $fp
     * @param $ssh_key
     * @param $key_password
     * @param $user_id
     * @throws Exception
     */
    private function _transferSFTPByPassword($fp, $password, $user_id)
    {
        if ($this->debug)print "Processing SFTP via Password\n";
        $sftp = new SFTP($this->hostname, $this->port);
        if (!$sftp->login($this->username, $password))
        {
            throw new Exception('Unable to authenticate with server over the SFTP protocol with password.');
        }
        $sftp->put($this->destination_path . $this->target_filename, $fp);

        $audit = array();
        $audit['hostname'] = $this->hostname;
        $audit['username'] = $this->username;
        $audit['output'] = "{$this->destination_path}{$this->target_filename}";
        $audit['protocol'] = "{$this->protocol}";
        $audit['port'] = "{$this->port}";
        AuditIt(__CLASS__ . '-success', $audit, $user_id, $this->transfer_company_id, $this->transfer_companyparent_id);

    }
    /**
     * _decodeFileForTransfer
     *
     * Prepare the file for transfer to SFTP dropbox.
     *
     * @param $company_id
     * @param $prefix
     * @param $source_filename
     * @return bool|string
     */
    private function _decodeFileForTransfer($company_id, $prefix, $source_filename)
    {
        $fp = null;
        $fp_temp = null;
        try
        {

            // What kind of file are we dealing with here?
            $suffix = strtoupper(fRightBack($source_filename, "."));

            // The SFTP class must read from a file handle.  While we have one
            // on AWS, that file is encoded.  Create a safe place to decode the
            // file in question on the secure Heroku server.
            $temp_path = APPPATH . "../transfer";
            $temp_filename = fLeftBack($source_filename, ".");
            $temp_filename = "FileTransfer_{$company_id}_{$temp_filename}";
            $temp_filename = tempnam($temp_path, $temp_filename);

            // No way that file can be there, but let's make sure.
            if ( file_exists($temp_filename) ) unlink($temp_filename);

            // Open the file handle to our temp location.
            $fp_temp = fopen($temp_filename,'w');
            if ( ! is_resource($fp_temp) ) throw new Exception("Unable to create temp file.");

            // open the file on AWS.
            $fp = S3OpenFile( S3_BUCKET, $prefix, $source_filename, 'r' );
            if ( ! is_resource($fp) ) throw new Exception("Unable to find file on AWS.");

            if ( $suffix === 'PDF')
            {
                // PDF files are not encrypted.  Just copy them down to the local server
                // for transfer.
                stream_copy_to_stream($fp, $fp_temp);
            }
            else
            {
                // All other files are encrypted and we need to decrypt them prior to
                // the transfer.
                while (($line = fgets($fp)) !== false)
                {
                    if ( IsEncryptedString($line) )
                    {
                        fwrite($fp_temp, A2PDecryptString(trim($line), $this->encryption_key) . WINDOWS_NEWLINE);
                    }
                    else if ( IsEncryptedStringComment($line) )
                    {
                        // Do nothing here.  Don't write our internal comments out in the final file.
                    }
                    else
                    {
                        fwrite($fp_temp, trim($line) . WINDOWS_NEWLINE);
                    }
                }
            }



            // Close all our connections.
            if ( is_resource($fp) ) fclose($fp);
            if ( is_resource($fp_temp) ) fclose($fp_temp);

            // Tell the calling program where to find the file.
            return $temp_filename;

        }catch(Exception $e)
        {
            print $e->getMessage();
            if ( is_resource($fp) ) fclose($fp);
            if ( is_resource($fp_temp) ) fclose($fp_temp);
        }
        return false;

    }


    /**
     * _filterTransfersByEnabledFeature
     *
     * Given a list of all possible transfers we are about to make, review
     * each one and check to see if the FILE_TRANSFER feature is enabled
     * for the entity ( company, parent company ).
     *
     * If the associated entity has not enabled the feature, do not transfer
     * the files.  It's possible we have transfer information on file, but the
     * overall feature has been disabled.
     *
     * @param $transfers
     * @return array
     */
    private function _filterTransfersByEnabledFeature($transfers)
    {
        if ( empty($transfers) ) return array();

        $filtered = array();
        foreach($transfers as $transfer)
        {
            // The transfer object will have either a CompanyId or a CompanyParentId
            // field, depending on which entity is getting the transfer.
            $company_id = GetArrayStringValue("CompanyId", $transfer);
            $companyparent_id = GetArrayStringValue("CompanyParentId", $transfer);

            // Only allow this transfer if the Company has enabled the FILE_TRANSFER feature.
            if ( $company_id !== '' )
            {
                if ( $this->ci->Feature_model->is_feature_enabled($company_id, 'FILE_TRANSFER' ) )
                {
                    $filtered[] = $transfer;
                }
            }

            // Only allow this transfer if the CompanyParent has enabled the FILE_TRANSFER feature.
            if ( $companyparent_id !== '' )
            {
                if ( $this->ci->Feature_model->is_companyparent_feature_enabled($companyparent_id, 'FILE_TRANSFER' ) )
                {
                    $filtered[] = $transfer;
                }
            }
        }
        return $filtered;
    }
    private function _filterTransfersByCompanyId($transfers)
    {
        if ( empty($transfers) ) return array();

        // If there are no filters in place, just return the full collection.
        if ( empty($this->company_id_filter) ) return $transfers;

        $filtered = array();
        foreach($transfers as $transfer)
        {
            $transfer_company_id = GetArrayStringValue('CompanyId', $transfer);
            if ( in_array($transfer_company_id, $this->company_id_filter) )
            {
                if ( $transfer_company_id !== '' )
                {
                    $filtered[] = $transfer;
                }
            }
        }
        return $filtered;
    }
    private function _filterTransfersByCompanyParentId($transfers)
    {
        if ( empty($transfers) ) return array();

        // If there are no filters in place, just return the full collection.
        if ( empty($this->companyparent_id_filter) ) return $transfers;

        $filtered = array();
        foreach($transfers as $transfer)
        {
            $transfer_companyparent_id = GetArrayStringValue('CompanyParentId', $transfer);
            if ( in_array($transfer_companyparent_id, $this->companyparent_id_filter) )
            {
                if ( $transfer_companyparent_id !== '' )
                {
                    $filtered[] = $transfer;
                }
            }
        }
        return $filtered;
    }
    private function _getTransfers($company_id)
    {
        $normalized_transfers = array();

        $transfers = $this->ci->FileTransfer_model->get_file_transfer_by_company($company_id);
        foreach($transfers as $transfer) {

            $row = array();

            // Find the encryption key for the company we are transferring the data to.
            $transfer_encryption_key = EMPTY_ENCRYPTION_KEY;
            $transfer_company_id = GetArrayStringValue("CompanyId", $transfer);
            $transfer_companyparent_id = GetArrayStringValue("CompanyParentId", $transfer);

            // Company
            if ( GetStringValue($transfer_company_id) !== '' )
            {
                $row["CompanyId"] = $transfer_company_id;
                $transfer_encryption_key = GetCompanyEncryptionKey($transfer_company_id);
            }

            // CompanyParent
            if ( GetStringValue($transfer_companyparent_id) !== '' )
            {
                $row["CompanyParentId"] = $transfer_companyparent_id;
                $transfer_encryption_key = GetCompanyParentEncryptionKey($transfer_companyparent_id);
            }

            // required
            $row['Username'] = GetArrayStringValue('Username', $transfer);
            if ( GetArrayStringValue('Username', $row) === '' ) continue;

            // required
            $row['Hostname'] = GetArrayStringValue('Hostname', $transfer);
            if ( GetArrayStringValue('Hostname', $row) === '' ) continue;

            // required
            $row['DestinationPath'] = GetArrayStringValue('DestinationPath', $transfer);
            if ( GetArrayStringValue('DestinationPath', $row) === '' ) continue;

            // required
            $row['Password'] = A2PDecryptString(GetArrayStringValue('EncryptedPassword', $transfer), $transfer_encryption_key);
            if ( GetArrayStringValue('Password', $row) === '' ) continue;

            // required
            $row['Protocol'] = A2PDecryptString(GetArrayStringValue('Protocol', $transfer), $transfer_encryption_key);
            if ( GetArrayStringValue('Protocol', $row) === '' ) continue;

            // required
            $row['Port'] = A2PDecryptString(GetArrayStringValue('Port', $transfer), $transfer_encryption_key);
            if ( GetArrayStringValue('Port', $row) === '' ) continue;

            // optional
            $row['SSHKey'] = A2PDecryptString(GetArrayStringValue('EncryptedSSHKey', $transfer), $transfer_encryption_key);


            // Do your best to make sure the destination path ends in a slash.
            // If it doesn't, look for other slashes in the string and mimic the
            // style provided.
            $destination_path = GetArrayStringValue('DestinationPath', $row);
            if (!EndsWith($destination_path, '/') && !EndsWith($destination_path, '\\')) {
                if (strpos($destination_path, '\\') !== FALSE) $destination_path .= '\\';
                if (strpos($destination_path, '/') !== FALSE) $destination_path .= '/';
            }
            $row['DestinationPath'] = $destination_path;

            // Add this transfer to the return set.
            $normalized_transfers[] = $row;
        }
        return $normalized_transfers;
    }

}

/* End of file FileTransfer.php */
/* Location: ./application/controllers/cli/FileTransfer.php */
