<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class GenerateDuplicateLivesReport
 *
 * While testing with Transamerica, they gave us a very large test file that
 * had bad data in in.  The bad data would cause our application to crash so
 * we added this bit of logic to look for the bad data before we tried to process
 * it.  If found, we will write a warning message and shutdown report generation.
 * The end user may download the duplicate lives report to help them identify
 * which files are borked.
 *
 */
class GenerateDuplicateLivesReport extends A2PLibrary
{

    private $_filename = 'duplicate_lives.csv';

    function __construct( $debug=false )
    {
        parent::__construct($debug);
    }

    public function getFilename()
    {
        return $this->_filename;
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

            // ROLLBACK - In the case where the user makes changes to their life data during the review process,
            // we must rollback any previous work we have done for this month before we begin.
            $this->rollback($company_id, $import_date);

            // INSERT - Create a table of unique lives that appear to be duplicates.
            $this->debug(" Looking for duplicate lives.");
            $this->ci->GenerateDuplicateLivesReport_model->insert_duplicate_lives($company_id, $import_date);
            $this->timer(" Looking for duplicate lives.");

            // CHECK - Do we have any duplicate lives?
            if ( $this->ci->GenerateDuplicateLivesReport_model->check_for_duplicate_lives($company_id, $import_date) )
            {

                $prefix = GetConfigvalue("errors_prefix");
                $prefix = replaceFor($prefix, "COMPANYID", $company_id);

                $fh = S3OpenFile(S3_BUCKET, $prefix, 'duplicate_lives.csv', 'w');
                if ( is_resource($fh) )
                {
                    $this->ci->GenerateDuplicateLivesReport_model->create_report( $fh, $this->encryption_key, $company_id, $import_date);
                    if ( is_resource($fh) ) fclose($fh);

                    $link = "/download/duplicates/" . $company_id;
                    $this->ci->GenerateDuplicateLivesReport_model->write_warning_message( $company_id, $import_date, $link);
                    throw new Exception("Inconsistent or duplicate data was found for this life.  Check for duplicate entries or consistency in name fields.");
                }
            }
        }
        catch(Exception $e)
        {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
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

        // Rollback any "NEW" items that were generated for the specified import date.
        $this->debug(" Rolling back duplicate lives we previously discovered. ( If any )");
        $this->ci->GenerateDuplicateLivesReport_model->delete_duplicate_lives($company_id, $import_date);

        // Rollback the file
        $prefix = GetConfigvalue("errors_prefix");
        $prefix = replaceFor($prefix, "COMPANYID", $company_id);
        if ( S3DoesFileExist(S3_BUCKET, $prefix, $this->_filename) )
        {
            S3DeleteFile(S3_BUCKET, $prefix, $this->_filename);
        }

    }

}
