<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateCommissionReport extends A2PLibrary
{

    function __construct( $debug=false )
    {
        parent::__construct($debug);

    }


    public function execute($company_id, $user_id=null )
    {
        try
        {
            parent::execute($company_id);

            // What is our import date?
            $import_date = GetUploadDate($company_id);
            if ( $import_date == "" ) throw new Exception("Invalid import_date. How is that possible?");

            $this->debug(" ImportDate: [{$import_date}]");
            $this->debug(" CompanyId: [{$company_id}]");

            // FEATURE CHECK
            // Before we start, we need to know if feature is enabled.
            $enabled = $this->isEnabled($company_id);
            if ( ! $enabled ) return;

            // ROLLBACK - In the case where the user makes changes to their life data during the review process,
            // we must rollback any previous work we have done for this month before we begin.
            $this->rollback($company_id, $import_date);

            // Generate A2P Commission Report.
            $this->debug(" REPORT: Creating the A2P Commission report.");
            $this->_generate_report($company_id);
            $this->timer(" REPORT: Creating the A2P Commission report.");

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
     * Return TRUE or FALSE.  Should we run this business unit based on feature configuration.
     *
     * @param $company_id
     * @return mixed
     */
    public function isEnabled($company_id)
    {
        // FEATURE CHECK
        // Before we start, we need to know if feature is enabled.
        $enabled = $this->ci->Feature_model->is_feature_enabled($company_id, 'COMMISSION_TRACKING');
        return $enabled;
    }




    /**
     *
     * Undo any changes that were made for the specified import date and company_id for
     * this business unit.
     *
     * @param $company_id
     * @param null $import_date
     * @throws Exception
     */
    public function rollback($company_id, $import_date=null )
    {
        parent::rollback($company_id);

        // What is our import date?
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) throw new Exception("Invalid import_date. How is that possible?");

        $this->debug(" ImportDate: [{$import_date}]");
        $this->debug(" CompanyId: [{$company_id}]");

        $this->debug(" ROLLBACK: GenerateCommissionReport for company [{$company_id}] and import [{$import_date}].");
        $this->ci->Reporting_model->delete_company_report_by_type( $company_id, REPORT_TYPE_COMMISSION_CODE, $import_date );
        $this->timer(" ROLLBACK: GenerateCommissionReport for company [{$company_id}] and import [{$import_date}].");



    }

    private function _generate_report($company_id) {

        $CI = $this->ci;

        // Collect our list of carriers from the summary report.
        $this->debug(" select_summary_report_carriers");
        $carriers = $CI->Reporting_model->select_summary_report_carriers($company_id);

        $report_type = REPORT_TYPE_COMMISSION_CODE;

        // Create the detail report folder
        $prefix = GetConfigValue("reporting_prefix");
        $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
        $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id));
        $prefix  = replaceFor($prefix, "TYPE", $report_type);
        S3MakeBucketPrefix(S3_BUCKET, $prefix);

        // We only want to write headers once.
        $write_headers = true;

        // Generate the Detail Report.
        foreach($carriers as $item)
        {

            // For each new report, we want to activate the headers.
            $write_headers = true;

            $fh = null;
            try
            {
                // create the CSV file for download, make sure it's encrypted.
                $carrier_id = getArrayIntValue("CarrierId", $item);
                $filename = "{$carrier_id}.csv";
                S3SaveEncryptedFile(S3_BUCKET, $prefix, $filename, "");
                $fh = S3OpenFile( S3_BUCKET, $prefix, $filename, $options='w' );


                // Create the report.
                $this->debug(" write_commission_report");
                $result = $CI->Reporting_model->write_commission_report($fh, $company_id, $carrier_id, $this->encryption_key, $write_headers);
                if ( $result > 0 ) $write_headers = false;


                // Record that this report exists in the DB.
                $this->debug(" insert_company_report [{$company_id}] [{$carrier_id}] [{$report_type}]");
                $CI->Reporting_model->insert_company_report( $company_id, $carrier_id, $report_type );
            }
            catch(Exception $e)
            {
                if ( $fh != null ) fclose($fh);
                throw $e;
            }

        }

        // Secure the reports on S3
        S3EncryptAllFiles(S3_BUCKET, $prefix);

    }

}
