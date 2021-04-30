<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateReportTransamericaCommissions extends GenerateReportTransamerica {

    //protected $encryption_key;
    //protected $import_date;
    //protected $report_code;
    private $_write_report_warnings;

    function __construct( $debug=false )
    {
        parent::__construct($debug);

        $this->report_code = REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE;

        // By default, always write report warnings.  If we are running the
        // regenerate function, the we are operating out of normal sequence
        // and we want to suppress the normal error handling.
        $this->_write_report_warnings = true;
    }

    /**
     * regenerate
     *
     * This function is used by the TransamericaCommissions tool to rebuild the
     * commission files for a given company and import.  
     *
     * @param $company_id
     * @param $import_date
     * @param $user_id
     * @throws Exception
     */
    public function regenerate($company_id, $import_date, $user_id)
    {
        $CI = $this->ci;
        $this->debug = false;

        try
        {
            // Does this company have this report enabled in the feature list?  No, do not generate this report.
            $enabled = $this->isEnabled($company_id);
            if ( ! $enabled ) return;

            // Does this company have Transamerica as a carrier?  No, do not generate this report.
            $carrier_details = $CI->Company_model->get_company_carrier_by_name($company_id, "TRANSAMERICA");
            $carrier_id = GetArrayStringValue("Id", $carrier_details);
            if ( $carrier_id === "" ) return;

            // ROLLBACK
            // Rollback any data that we might have in hand from a previous attempt.
            $this->rollback($company_id, $import_date);

            // GENERATE
            // Create the data and report.
            $this->_write_report_warnings = false;
            $this->import_date = $import_date;
            $this->execute($company_id, $user_id);

        }
        catch(Exception $e)
        {
            throw $e;
        }


    }

    /**
     * execute
     *
     * Generate the Transamerica Commission Export File for the specified company.
     * The report will automatically select the current open month being processed by this company.
     * The report will only generate if the customer has TRANSAMERICA as a carrier.
     * If the report fails because of the lack of input data, Report Warnings will be issued.
     *
     * @param $company_id
     */
    public function execute($company_id, $user_id=null )
    {
        try
        {
            $CI = $this->ci;
            parent::execute($company_id);
            if ( getStringValue($this->company_id) == "" ) throw new Exception("Invalid input company_id.");
            if ( GetStringValue($this->import_date) === '' ) $this->import_date = GetUploadDate($company_id);

            $this->debug("  company_id: [{$this->company_id}]");
            $this->debug(" import_date: [{$this->import_date}]");

            // Does this company have this report enabled in the feature list?  No, do not generate this report.
            $enabled = $this->isEnabled($company_id);
            if ( ! $enabled ) return;

            // Does this company have Transamerica as a carrier?  No, do not generate this report.
            $carrier_details = $CI->Company_model->get_company_carrier_by_name($this->company_id, "TRANSAMERICA");
            $carrier_id = GetArrayStringValue("Id", $carrier_details);
            if ( $carrier_id === "" ) return;

            // ROLLBACK
            // Rollback any data that we might have in hand from a previous attempt.
            $this->rollback($company_id, $this->import_date);

            // LIVES - key data
            // Collect the "keys" for each life we want to report on.
            $this->debug("Find all of the lives we want to report on.");
            SupportTimerStart($company_id, $this->import_date, 'insert_report_data', __CLASS__);
            $CI->ReportTransamericaCommissions_model->insert_report_data($company_id, $this->import_date, $carrier_id);
            SupportTimerEnd($company_id, $this->import_date, 'insert_report_data', __CLASS__);

            // LOST LIVES - key data
            // Collect the "keys" for each life that we lost between this month and last.
            $this->debug("Find all of the lost lives we want to report on.");
            SupportTimerStart($company_id, $this->import_date, 'insert_report_data_lost_lives', __CLASS__);
            $CI->ReportTransamericaCommissions_model->insert_report_data_lost_lives($company_id, $this->import_date, $carrier_id);
            SupportTimerEnd($company_id, $this->import_date, 'insert_report_data_lost_lives', __CLASS__);

            // LIVES - detailed data
            // Collect and organize the data for each life we found above so we can transform our data
            // into their data as we write each record to the cloud.
            $this->debug("Collecting the details for each life that will be in the Transamerica Commission report.");
            SupportTimerStart($company_id, $this->import_date, 'insert_report_detail_data', __CLASS__);
            $CI->ReportTransamericaCommissions_model->insert_report_detail_data($company_id, $this->import_date);
            SupportTimerEnd($company_id, $this->import_date, 'insert_report_detail_data', __CLASS__);

            // LOST LIVES - detailed data
            // Collect and organize the data for each life we found above so we can transform our data
            // into their data as we write each record to the cloud.
            $this->debug("Collecting the details for each lost life that will be in the Transamerica Commission report.");
            SupportTimerStart($company_id, $this->import_date, 'insert_report_detail_data_lost_lives', __CLASS__);
            $CI->ReportTransamericaCommissions_model->insert_report_detail_data_lost_lives($company_id, $this->import_date);
            SupportTimerEnd($company_id, $this->import_date, 'insert_report_detail_data_lost_lives', __CLASS__);

            // Create the report.
            $this->debug("Generating the Transamerica Commission report.");
            SupportTimerStart($company_id, $this->import_date, '_save_report', __CLASS__);
            $save_results = $this->save_report($this->company_id, $this->import_date, $carrier_id);
            SupportTimerEnd($company_id, $this->import_date, '_save_report', __CLASS__);

            if ( GetArrayIntValue('file_length', $save_results) === 0 )
            {
                $this->debug("Attempting to save the Transamerica Commission report to S3 again.");
                SupportTimerStart($company_id, $this->import_date, 'retry', __CLASS__);
                $this->retry($this->company_id, $this->import_date, $carrier_id, $save_results);
                SupportTimerEnd($company_id, $this->import_date, 'retry', __CLASS__);
            }


        } catch(Exception $e) {

            // If report warnings are turned off, just throw the error.
            if ( ! $this->_write_report_warnings ) throw $e;


            // This is an "optional" report.  If we can't generate this report,
            // don't throw an error and cause a rollback.  Just don't show
            // the report.

            //$this->debug("Rolling back due to exception: " . $e->getMessage());
            //$this->rollback($this->company_id, $this->import_date);

            $report = $this->ci->Reporting_model->select_report_type($this->report_code);
            $report_name = GetArrayStringValue("Display", $report);
            $companyparent_id = GetCompanyParentId($this->company_id);

            $details = array();
            $details['company_id'] = $this->company_id;
            $details['companyparent_id'] = $companyparent_id;
            $details['import_date'] = $this->import_date;
            $details['report_code'] = $this->report_code;
            $details['report_name'] = $report_name;
            $details['error'] = $e->getMessage();
            LogIt(__CLASS__, "Unable to generate {$report_name} report.", $details, null, $this->company_id, $companyparent_id );

            $this->ci->ReportTransamericaCommissions_model->write_report_warning( $this->company_id, $this->import_date, $report_name, $e->getMessage(), true);
        }
    }


    /**
     * rollback
     *
     * Revert the data in the database and S3 that was created for this report
     * for the specified import date.  If the import date is not provided, we will
     * use the current open month for this company.
     *
     * @param $company_id
     * @param null $import_date
     * @throws Exception
     */
    public function rollback($company_id, $import_date=null )
    {
        try
        {
            $CI = $this->ci;
            parent::rollback($company_id);
            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");
            if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
            
            $this->debug(" Removing report data for specified month.");
            $CI->ReportTransamericaCommissions_model->delete_report_data($company_id, $import_date);
            $CI->ReportTransamericaCommissions_model->delete_report_detail_data($company_id, $import_date);
            $CI->Reporting_model->delete_company_report_by_type($company_id, REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE, $import_date);

            // CLEAN UP S3
            $this->debug(" Removing the report from S3.");

            // Where is the file stored?
            $prefix = GetConfigValue("reporting_prefix");
            $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
            $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id));
            $prefix  = replaceFor($prefix, "TYPE", REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE);
            S3MakeBucketPrefix(S3_BUCKET, $prefix);

            // What is the filename we are looking for?
            $carrier_details = $CI->Company_model->get_company_carrier_by_name($this->company_id, "TRANSAMERICA");
            $carrier_id = GetArrayStringValue("Id", $carrier_details);
            $filename = "{$carrier_id}.txt";

            if ( S3DoesFileExist(S3_BUCKET, $prefix, $filename) )
            {
                S3DeleteFile(S3_BUCKET, $prefix, $filename);
            }

        }
        catch(Exception $e)
        {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * isEnabled
     *
     * Returns TRUE or FALSE if this report will be generated or not when
     * executed.
     *
     * @param $company_id
     * @return mixed
     */
    public function isEnabled($company_id)
    {
        $CI = $this->ci;
        $enabled = $CI->Feature_model->is_feature_enabled($company_id, 'TRANSAMERICA_COMMISSION_REPORT');
        return $enabled;
    }

    /**
     * save_report
     *
     * This function will directly select the data from the database and
     * then stream it to S3 all at once to optimize memory management.
     *
     * @param $company_id
     * @param $import_date
     * @param $carrier_id
     * @throws Exception
     */
    protected function save_report($company_id, $import_date, $carrier_id)
    {
        $CI = $this->ci;

        $retval = array();
        $retval['file_details_saved'] = array();
        $retval['file_details_encrypted'] = array();
        $fetval['file_length'] = 0;

        $fh = null;
        try
        {
            $prefix = GetConfigValue("reporting_prefix");
            $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
            $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id, $import_date));
            $prefix  = replaceFor($prefix, "TYPE", REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE);
            S3MakeBucketPrefix(S3_BUCKET, $prefix);

            $filename = "{$carrier_id}.a2p";
            S3SaveEncryptedFile(S3_BUCKET, $prefix, $filename, "");
            $fh = S3OpenFile( S3_BUCKET, $prefix, $filename, $options='w' );

            $CI->ReportTransamericaCommissions_model->save_report_to_s3($company_id, $import_date, $fh, $this->encryption_key, $this->debug);

            $this->debug("Closing report file.");
            if ( is_resource($fh) ) fclose($fh);

            $file_details_saved = S3ListFile(S3_BUCKET, $prefix, $filename);

            $this->debug("Applying at rest encryption to the report.");
            S3EncryptExistingFile( S3_BUCKET, $prefix, $filename, $prefix, "{$carrier_id}.txt" );
            S3DeleteFile(S3_BUCKET, $prefix, $filename);
            $file_details_encrypted = S3ListFile(S3_BUCKET, $prefix, "{$carrier_id}.txt");

            // Save the new report to the CompanyReport table.
            $exists = $CI->Reporting_model->company_report_exists( $company_id, $carrier_id, REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE, $import_date );
            if ( ! $exists )
            {
                // We are writing this record only if it dies not exist.  Since we can "resend" this report, we only want one
                // of these records in the CompanyReport table.
                $CI->Reporting_model->insert_company_report( $company_id, $carrier_id, REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE, $import_date );
            }

            // Package up some information for the return.
            $retval['file_details_saved'] = $file_details_saved;
            $retval['file_details_encrypted'] = $file_details_encrypted;
            $retval['file_length'] = GetArrayIntValue('Size', $file_details_encrypted);

            // If the file size is zero length, clean the file out.
            if ( GetArrayIntValue('file_length', $retval) === 0 )
            {
                S3DeleteFile(S3_BUCKET, $prefix, "{$carrier_id}.txt");
            }

        }
        catch(Exception $e)
        {
            if ( is_resource($fh) ) fclose($fh);
            throw $e;
        }

        return $retval;

    }


}
