<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateWarningReport extends A2PLibrary {

    protected $encryption_key;

    function __construct( $debug=false )
    {
        parent::__construct($debug);
    }

    public function execute( $company_id, $user_id=null )
    {
        try
        {
            $CI = $this->ci;
            parent::execute($company_id);
            if ( getStringValue($this->company_id) == "" ) throw new Exception("Invalid input company_id.");
            $this->import_date = GetUploadDate($company_id);

            $this->debug("  company_id: [{$this->company_id}]");
            $this->debug(" import_date: [{$this->import_date}]");

            // Does this company have this report enabled in the feature list?  No, do not generate this report.
            $enabled = $this->isEnabled($company_id);
            if ( ! $enabled ) return;

            // ROLLBACK
            // Rollback any data that we might have in hand from a previous attempt.
            $this->rollback($company_id);

            // Save the report.
            $this->_save_report($company_id);


        } catch(Exception $e) {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }
    public function rollback( $company_id, $import_date=null )
    {
        try
        {
            $CI = $this->ci;
            parent::rollback($company_id);
            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");
            $import_date = GetUploadDate($company_id);

            // CLEAN UP S3
            $this->debug(" Removing the report from S3.");

            // Where is the file stored?
            $prefix = GetConfigValue("reporting_prefix");
            $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
            $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id, $import_date));
            $prefix  = replaceFor($prefix, "TYPE", REPORT_TYPE_ISSUES_CODE);
            S3MakeBucketPrefix(S3_BUCKET, $prefix);

            // What is the filename we are looking for?
            $filename = $this->getFilename();
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
        return true;

        //$CI = $this->ci;
        //$enabled = $CI->Feature_model->is_feature_enabled($company_id, 'WARNING_REPORT');
        //return $enabled;
    }

    /**
     * getFilename
     *
     * This function returns the filename we will save this report as
     * on our internal systems.
     *
     * @return string
     */
    public function getFilename()
    {
        return "potential_issues.csv";
    }

    /**
     * doesReportExist
     *
     * Return TRUE or FALSE, does the report for this company and import
     * date exist in the cloud?
     *
     * @param $company_id
     * @param string $import_date
     * @return bool
     */
    public function doesReportExist($company_id, $import_date="")
    {
        $prefix = GetConfigValue("reporting_prefix");
        $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
        $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id, $import_date));
        $prefix  = replaceFor($prefix, "TYPE", REPORT_TYPE_ISSUES_CODE);
        S3MakeBucketPrefix(S3_BUCKET, $prefix);

        // What is the filename we are looking for?
        $filename = $this->getFilename();
        if ( S3DoesFileExist(S3_BUCKET, $prefix, $filename) ) return TRUE;
        return FALSE;
    }

    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

    private function _save_report($company_id)
    {
        $CI = $this->ci;

        $fh = null;
        try
        {
            $prefix = GetConfigValue("reporting_prefix");
            $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
            $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id));
            $prefix  = replaceFor($prefix, "TYPE", REPORT_TYPE_ISSUES_CODE);
            S3MakeBucketPrefix(S3_BUCKET, $prefix);

            $filename = replaceFor($this->getFilename(), ".csv", ".a2p");
            S3SaveEncryptedFile(S3_BUCKET, $prefix, $filename, "");
            $fh = S3OpenFile( S3_BUCKET, $prefix, $filename, $options='w' );

            $CI->Reporting_model->create_warnings_report( $fh, $this->encryption_key, $company_id);

            $this->debug("Closing report file..");
            if ( is_resource($fh) ) fclose($fh);
            $this->debug("Applying at rest encryption to the report.");
            S3EncryptExistingFile( S3_BUCKET, $prefix, $filename, $prefix, $this->getFilename() );
            S3DeleteFile(S3_BUCKET, $prefix, $filename);

        }
        catch(Exception $e)
        {
            if ( is_resource($fh) ) fclose($fh);
            throw $e;
        }
        if ( is_resource($fh) ) fclose($fh);

    }
}
