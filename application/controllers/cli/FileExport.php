<?php defined('BASEPATH') OR exit('No direct script access allowed');

use \phpseclib\Net\SFTP;
use \phpseclib\Crypt\RSA;

/**
 * Class FileExport
 *
 * This background task will collect the items specified, ( things like reports ) for
 * a given company or a collection of companies associated with a parent.  These items will
 * be decrypted and grouped together into a single archive (zip) file.  The archive will
 * then be encrypted and stored on S3 under the A2P account.
 *
 */
class FileExport extends A2PWorker
{
    private $_output_path;
    private $_output_filename;
    private $_output;

    public function __construct()
    {
        parent::__construct();

        $this->_output_path = APPPATH . "../export";
        $this->_output_filename = "IDENTIFIER_IDENTIFIERTYPE_EXPORTID.zip";
        $this->_output = "{$this->_output_path}/OUTPUTFILENAME";

        $this->load->model('Export_model');

    }

    public function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
        parent::index($user_id, $company_id, $companyparent_id, $job_id);

        $export_id = null;
        try
        {

            // EXPORT - Find the export associated with this job request.
            // Collect information about what we are going to export.
            $export_id = $this->Export_model->get_export_id_by_job($job_id);
            $export = $this->Export_model->select_export($export_id);
            if ( empty($export) ) throw new Exception("Unable to find the export record.");

            $identifier = GetArrayStringValue('Identifier', $export);
            $identifier_type = GetArrayStringValue('IdentifierType', $export);
            $identifier_name = GetIdentifierName($identifier, $identifier_type);

            // FILENAME
            // Name the output filename to match the export_id
            $this->load->library('A2PExport');
            $obj = new A2PExport();
            $this->_output_filename = $obj->GetS3Filename($export_id);
            $this->_output = ReplaceFor($this->_output, 'OUTPUTFILENAME', $this->_output_filename);

            // REQUESTED
            // The export must be in the requested status!  If it is move it to IN_PROGRESS.
            $status = GetArrayStringValue('Status', $export);
            if ( $status !== 'REQUESTED' )
            {
                throw new Exception("Only exports with the status of REQUESTED can be generated.");
            }
            $this->Export_model->set_export_status($export_id, 'IN_PROGRESS');
            NotifyCompanyChannelUpdate(A2P_COMPANY_ID, 'export_dashboard_task', 'ExportListUpdateHandler', []);

            // COMPANIES
            // The export request might be for a single company or for a collection of companies
            // associated with a parent.  Create a collection of company ids that we will collect
            // in this export request.
            $companies = array();
            if ( $this->getCompanyId() !== '' )
            {
                $companies = [ $this->getCompanyId() ];
            }
            else
            {
                $parent_companies = $this->CompanyParent_model->get_companies_by_parent($this->getCompanyParentId());
                foreach($parent_companies as $company)
                {
                    $companies[] = GetArrayStringValue("company_id", $company);
                }
            }
            if ( empty($companies) ) throw new Exception("Found no companies associated with the export request.");



            // Prepare the "export" folder for a fresh export.
            if ( file_exists($this->_output) ) unlink($this->_output);
            $this->emptyExportFolder();

            // Create a ZIP file to hold all the reports.
            $zip = new ZipArchive();
            if ($zip->open($this->_output, ZipArchive::CREATE)!==TRUE) {
                throw new Exception("Unable to open zip file.");
            }

            // Collect and decrypt the company reports into our temp folder.
            $file_count = 0;
            foreach($companies as $company_id)
            {
                $file_count += $this->collectCompanyReports($company_id, $export_id);
            }

            // If the export resulted in no results, then just go ahead and delete it from
            // the list and move on.
            if ( $file_count === 0 )
            {
                $this->Export_model->set_export_status($export_id, 'NO_RESULTS');
                NotifyCompanyChannelUpdate(A2P_COMPANY_ID, 'export_dashboard_task', 'ExportListUpdateHandler', []);
                sleep(5);
                $obj->DeleteExport($export_id, $user_id);
                NotifyCompanyChannelUpdate(A2P_COMPANY_ID, 'export_dashboard_task', 'ExportListUpdateHandler', []);
                return;
            }

            // Add each of the files to the zip archive.
            $ignore = [ '.', '..', '.htaccess', 'index.html' ];
            if ($handle = opendir( $this->_output_path) ) {
                while (false !== ($entry = readdir($handle)))
                {
                    if ( ! in_array($entry, $ignore) )
                    {
                        $zip->addFile("{$this->_output_path}/{$entry}","/{$entry}");
                    }
                }
                closedir($handle);
            }
            $zip->close();

            // Clean up all the loose files, keeping only the zip file.
            $this->emptyExportFolder($this->_output_filename);

            // Encrypt the zip file using the A2P Encryption Key.
            $a2p_encryption_key = A2PGetEncryptionKey();
            $data = file_get_contents($this->_output);
            $big_data = base64_encode($data);
            $secured = A2PEncryptString($big_data, $a2p_encryption_key);
            file_put_contents("{$this->_output}_secure", $secured);
            copy("{$this->_output}_secure", "{$this->_output}");
            unlink("{$this->_output}_secure");

            // Now upload any files in the export folder, except our index.html file, to AWS.
            S3MakeBucketPrefix( S3_BUCKET, 'export' );
            $prefix = GetS3Prefix('export', $identifier, $identifier_type);
            S3DeleteFile(S3_BUCKET, $prefix, "{$this->_output_filename}" );
            copy ( $this->_output , "s3://".S3_BUCKET."/{$prefix}/{$this->_output_filename}_partial");
            S3EncryptExistingFile(S3_BUCKET, $prefix, "{$this->_output_filename}_partial", $prefix, $this->_output_filename);
            S3DeleteFile(S3_BUCKET, $prefix, "{$this->_output_filename}_partial" );

            // Remove the encrypted zip file from the local machine.
            if ( file_exists($this->_output) ) unlink($this->_output);


            // Audit the export.
            $payload = array();
            $payload['Identifier']      = $identifier;
            $payload['IdentifierType']  = $identifier_type;
            $payload['IdentifierName']  = $identifier_name;
            $payload['report_code']     = $this->Export_model->select_export_property_by_key($export_id, 'report_code');
            $payload['export_id']       = GetArrayStringValue('Id', $export);
            AuditIt('Created an export.', $payload, $user_id, A2P_COMPANY_ID);

            // Update the status and notify anyone listening we are done.
            $this->Export_model->set_export_status($export_id, 'COMPLETE');
            NotifyCompanyChannelUpdate(A2P_COMPANY_ID, 'export_dashboard_task', 'ExportListUpdateHandler', []);

        }
        catch(Throwable $e)
        {
            if ( GetStringValue($export_id) !== '' )
            {
                $this->Export_model->set_export_status($export_id, 'FAILED');
                NotifyCompanyChannelUpdate(A2P_COMPANY_ID, 'export_dashboard_task', 'ExportListUpdateHandler', []);
                print $e->getMessage();
            }
        }

    }

    /**
     * emptyExportFolder
     *
     * Remove all files in the local directory.  Keeping only the $keep_this_file
     * if one is provided.
     * @param string $keep_this_file
     */
    protected function emptyExportFolder($keep_this_file='')
    {
        $ignore = [ '.', '..', '.htaccess', 'index.html' ];
        if ($handle = opendir( $this->_output_path) ) {
            while (false !== ($entry = readdir($handle)))
            {
                if ( ! in_array($entry, $ignore) )
                {
                    if ( $entry !== $keep_this_file )
                    {
                        unlink("{$this->_output_path}/{$entry}");
                    }
                }
            }
            closedir($handle);
        }
    }

    /**
     * collectCompanyReports
     *
     * This function will find all the reports the the specified company.
     * Those reports will be downloaded and decrypted onto the local secure
     * vm for temporary storage while they are being organized.
     *
     * @param $company_id
     * @param $export_id
     * @throws Exception
     */
    protected function collectCompanyReports($company_id, $export_id)
    {
        // Keep track of the number of files we place into the temp folder.
        $file_count = 0;

        // Each export is for a specific report code.  Create a filtered array
        // that contains only the report codes we want to export.
        $report_code = $this->Export_model->select_export_property_by_key($export_id, 'report_code');
        $included_reports = [ $report_code ];

        // Each export is for a specific year.  Create a filtered array
        // that contains only the years we want to export.
        $year = $this->Export_model->select_export_property_by_key($export_id, 'year');
        $included_years = [ $year ];

        // Find all of the carriers for this company
        $carriers = $this->Company_model->get_company_carriers($company_id);

        // Find the import dates for this company.
        $imports = $this->Reporting_model->select_import_dates($company_id);
        foreach($imports as $import)
        {
            // This is the import date we are currently looking at.
            $import_date = GetArrayStringValue($import, "ImportDate");
            $date_tag = FormatDateYYYYMM($import_date);
            $report_year = substr($date_tag, '0', 4);

            // Skip this report if it is not in the set of included years.
            if ( !empty($included_years) && ! in_array($report_year, $included_years) ) continue;

            foreach($carriers as $carrier)
            {
                // These are all of the downloads for the the specified carrier and import date.
                $carrier_id = GetArrayStringValue('Id', $carrier);
                $downloads = $this->Reporting_model->get_downloadable_reports($company_id, $carrier_id, $import_date);

                // Download and decrypt each to our secure container.
                foreach($downloads as $report)
                {
                    $report_id = GetArrayStringValue('ReportId', $report);
                    $report_code = GetArrayStringValue('ReportCode', $report);

                    // Skip this report if it was excluded from the request.
                    if ( !empty($included_reports) && ! in_array($report_code, $included_reports) ) continue;
                    if ( $report_id === '' ) continue;

                    // This is where we are going to look for reports on S3 for this company.
                    $prefix = GetS3Prefix('reporting', $company_id, 'company');
                    $prefix = replaceFor($prefix, 'DATE', $date_tag);
                    $prefix = replaceFor($prefix, 'TYPE', $report_code);

                    $files = S3ListFiles(S3_BUCKET, $prefix);
                    foreach($files as $file)
                    {
                        $filepath = GetArrayStringValue('Key', $file);
                        $filename = fRightBack($filepath, "/");
                        if ( $filename !== '' )
                        {
                            $temp_filename = $this->_decodeFileForTransfer($company_id, $prefix, $filename);
                            if ( $temp_filename !== FALSE )
                            {
                                $path = fLeftBack($temp_filename, "/");
                                $filename = GetReportFilename($report_id);
                                rename($temp_filename, $path . '/' . $filename );
                                $file_count++;
                            }
                        }
                    }
                }
            }
        }


        // We now have all of the files locally.
        return $file_count;

    }

    /**
     * _decodeFileForTransfer
     *
     * This function will download all reports on S3 for a given company to a local
     * folder.
     *
     * @param $company_id
     * @param $aws_prefix
     * @param $source_filename
     * @return bool|string
     */
    private function _decodeFileForTransfer($company_id, $aws_prefix, $source_filename)
    {
        $fp = null;
        $fp_temp = null;
        try
        {
            // Grab the encryption key for the company we are working with.
            $company_encryption_key = GetCompanyEncryptionKey($company_id);

            // What kind of file are we dealing with here?
            $suffix = strtoupper(fRightBack($source_filename, "."));

            // Create a temp filename for the decode.
            $temp_filename = fLeftBack($source_filename, ".");
            $temp_filename = "Export_{$company_id}_{$temp_filename}";
            $temp_filename = tempnam($this->_output_path, $temp_filename);

            // No way that file can be there, but let's make sure.
            if ( file_exists($temp_filename) ) unlink($temp_filename);

            // Open the file handle to our temp location.
            $fp_temp = fopen($temp_filename,'w');
            if ( ! is_resource($fp_temp) ) throw new Exception("Unable to create temp file.");

            // open the file on AWS.
            $fp = S3OpenFile( S3_BUCKET, $aws_prefix, $source_filename, 'r' );
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
                        fwrite($fp_temp, A2PDecryptString(trim($line), $company_encryption_key) . WINDOWS_NEWLINE);
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
            if ( is_resource($fp) ) fclose($fp);
            if ( is_resource($fp_temp) ) fclose($fp_temp);
        }
        return false;

    }

}

/* End of file FileExport.php */
/* Location: ./application/controllers/cli/FileExport.php */
