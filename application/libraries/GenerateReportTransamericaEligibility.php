<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateReportTransamericaEligibility extends GenerateReportTransamerica {

    //protected $encryption_key;
    //protected $import_date;
    //protected $report_code;

    function __construct( $debug=false)
    {
        parent::__construct($debug);

        $this->report_code = REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE;
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

            // Does this company have Transamerica as a carrier?  No, do not generate this report.
            $carrier_details = $CI->Company_model->get_company_carrier_by_name($this->company_id, "TRANSAMERICA");
            $carrier_id = GetArrayStringValue("Id", $carrier_details);
            if ( $carrier_id === "" ) return;

            // ROLLBACK
            // Rollback any data that we might have in hand from a previous attempt.
            $this->rollback($company_id);

            $this->debug("Collecting eligibility report data");
            SupportTimerStart($company_id, $this->import_date, 'insert_report_data', __CLASS__);
            $CI->ReportTransamericaEligibility_model->insert_report_data($this->company_id, $this->import_date, $carrier_id);
            SupportTimerEnd($company_id, $this->import_date, 'insert_report_data', __CLASS__);

            if ( $CI->Retro_model->is_first_import($this->company_id, $this->import_date) == "f" )
            {
                $this->debug("Looking for lives lost between this month and last.");
                SupportTimerStart($company_id, $this->import_date, 'insert_lost_data', __CLASS__);
                $CI->ReportTransamericaEligibility_model->insert_lost_data($this->company_id, $this->import_date, $carrier_id);
                SupportTimerEnd($company_id, $this->import_date, 'insert_lost_data', __CLASS__);
            }

            $this->debug("Collecting eligibility report lives");
            SupportTimerStart($company_id, $this->import_date, 'insert_report_details', __CLASS__);
            $CI->ReportTransamericaEligibility_model->insert_report_details($this->company_id, $this->import_date, $carrier_id);
            SupportTimerEnd($company_id, $this->import_date, 'insert_report_details', __CLASS__);

            // When the parent record has the tier of EO, we only send the employee.  No dependents.
            $this->debug("EO – we only send the employee record, regardless of dependents in the input file.");
            SupportTimerStart($company_id, $this->import_date, 'update_report_details_IgnoreTierEO', __CLASS__);
            $CI->ReportTransamericaEligibility_model->update_report_details_IgnoreTierEO($this->company_id, $this->import_date);
            SupportTimerEnd($company_id, $this->import_date, 'update_report_details_IgnoreTierEO', __CLASS__);

            // When the parent record has the tier of ES, we only send the employee and spouse.  No children.
            $this->debug("ES – we only send the employee record and the spouse dependent record, regardless of other child dependents in the input file.");
            SupportTimerStart($company_id, $this->import_date, 'update_report_details_IgnoreTierES', __CLASS__);
            $CI->ReportTransamericaEligibility_model->update_report_details_IgnoreTierES($this->company_id, $this->import_date);
            SupportTimerEnd($company_id, $this->import_date, 'update_report_details_IgnoreTierES', __CLASS__);

            // When the parent record has the tier of EC, we only send the employee and children.  No spouse.
            $this->debug("EC – we only send the employee record and the child dependent records, regardless of spouse in the input file.");
            SupportTimerStart($company_id, $this->import_date, 'update_report_details_IgnoreTierEC', __CLASS__);
            $CI->ReportTransamericaEligibility_model->update_report_details_IgnoreTierEC($this->company_id, $this->import_date);
            SupportTimerEnd($company_id, $this->import_date, 'update_report_details_IgnoreTierEC', __CLASS__);

            // Look for dependent records associated with a parent that do not have matching coverage tiers.
            // These are records of interest and need to produce a warning.  Since this report rolls the dependent
            // records under the parent, it will appear as of the dependent is in the parent tier when the data says
            // differently.  These records will be flagged as a warning, but will still show up on the report.
            $this->debug("Looking for children that do not match their parent's coverage tier.");
            SupportTimerStart($company_id, $this->import_date, 'update_report_details_ChildTierMismatch', __CLASS__);
            $CI->ReportTransamericaEligibility_model->update_report_details_ChildTierMismatch($this->company_id, $this->import_date);
            SupportTimerEnd($company_id, $this->import_date, 'update_report_details_ChildTierMismatch', __CLASS__);

            // TIER_CHANGE_IGNORE
            // When a dependent is terminating before the current import date and the parent changes tiers at the same time
            // we do not want the terminating dependent to show up on the new tier record.
            $this->debug("Looking for children that changed tiers and terminated before import date ( and were not lost ).");
            SupportTimerStart($company_id, $this->import_date, 'update_report_details_IgnoreTierChange', __CLASS__);
            $this->update_report_details_IgnoreTierChange($this->company_id, $this->import_date);
            SupportTimerEnd($company_id, $this->import_date, 'update_report_details_IgnoreTierChange', __CLASS__);

            // NO_DETAILS
            // Now that we have processed all of the 'rules' for this report, mark any lives that have
            // no remaining detail records.  These will just drop out of the file.
            $this->debug("Looking for lives that have no details to report on.");
            SupportTimerStart($company_id, $this->import_date, 'update_report_NoDetails', __CLASS__);
            $CI->ReportTransamericaEligibility_model->update_report_NoDetails($this->company_id, $this->import_date);
            SupportTimerEnd($company_id, $this->import_date, 'update_report_NoDetails', __CLASS__);

            // Various actions above set "IssueCodes" on records.  Pull the ones we want to report on an
            // insert them into our warning table for display.
            $this->debug("Capturing report warnings.");
            SupportTimerStart($company_id, $this->import_date, 'insert_warnings', __CLASS__);
            $CI->ReportTransamericaEligibility_model->insert_warnings($this->company_id, $this->import_date);
            SupportTimerEnd($company_id, $this->import_date, 'insert_warnings', __CLASS__);

            $this->debug("Saving eligibility report to S3.");
            SupportTimerStart($company_id, $this->import_date, 'save_report', __CLASS__);
            $save_results = $this->save_report($this->company_id, $this->import_date, $carrier_id);
            SupportTimerEnd($company_id, $this->import_date, 'save_report', __CLASS__);

            if ( GetArrayIntValue('file_length', $save_results) === 0 )
            {
                $this->debug("Attempting to save the eligibility report to S3 again.");
                $this->retry($this->company_id, $this->import_date, $carrier_id, $save_results);
            }



        } catch(Exception $e) {

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

            $this->ci->ReportTransamericaEligibility_model->write_report_warning( $this->company_id, $this->import_date, $report_name, $e->getMessage(), true);
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

            $this->debug(" Removing eligibility data.");
            $CI->ReportTransamericaEligibility_model->delete_report_data($company_id, $import_date);

            $this->debug(" Removing eligibility lives.");
            $CI->ReportTransamericaEligibility_model->delete_report_details($company_id, $import_date);

            $this->debug(" Removing eligibility reports for this month.");
            $CI->Reporting_model->delete_company_report_by_type($company_id, REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE, $import_date);


            // CLEAN UP S3
            $this->debug(" Removing the report from S3.");

            // Where is the file stored?
            $prefix = GetConfigValue("reporting_prefix");
            $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
            $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id));
            $prefix  = replaceFor($prefix, "TYPE", REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE);
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
        $enabled = $CI->Feature_model->is_feature_enabled($company_id, 'TRANSAMERICA_ELIGIBILITY_REPORT');
        return $enabled;
    }

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
            $prefix  = replaceFor($prefix, "TYPE", REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE);
            S3MakeBucketPrefix(S3_BUCKET, $prefix);

            $filename = "{$carrier_id}.a2p";
            S3SaveEncryptedFile(S3_BUCKET, $prefix, $filename, "");
            $fh = S3OpenFile( S3_BUCKET, $prefix, $filename, $options='w' );

            $CI->ReportTransamericaEligibility_model->save_report_to_s3($company_id, $import_date, $fh, $this->encryption_key, $this->debug);

            $this->debug("Closing report file..");
            if ( is_resource($fh) ) fclose($fh);

            $file_details_saved = S3ListFile(S3_BUCKET, $prefix, $filename);

            $this->debug("Applying at rest encryption to the report.");
            S3EncryptExistingFile( S3_BUCKET, $prefix, $filename, $prefix, "{$carrier_id}.txt" );
            S3DeleteFile(S3_BUCKET, $prefix, $filename);
            $file_details_encrypted = S3ListFile(S3_BUCKET, $prefix, "{$carrier_id}.txt");

            // Save the new report to the CompanyReport table.
            $exists = $CI->Reporting_model->company_report_exists( $company_id, $carrier_id, REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE, $import_date );
            if ( ! $exists )
            {
                // We are writing this record only if it dies not exist.  Since we can "resend" this report, we only want one
                // of these records in the CompanyReport table.
                $CI->Reporting_model->insert_company_report( $company_id, $carrier_id, REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE );
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

    /**
     * update_report_details_IgnoreTierChange
     *
     * Here we will attempt to identify any child records that terminate in the past where their
     * parent on the same coverage-key ( carrier,plantype,plan,coveragetier ) are not terminating.
     * These child records need to be dropped off the parent row.
     *
     * @param $company_id
     * @param $import_date
     */
    private function update_report_details_IgnoreTierChange($company_id, $import_date)
    {
        /*
        USE CASE:
        April: Brian and Mary are on tier ES
        May: Brian Mary and Emily move to FA but Mary has a terminate date of April 20.

        Without this step, we would show for May:
        Terminate record for ES including Brian and Mary
        New record for FA with Brian, Mary and Emily, but Mary showing a termination date of April 20.

        Going forward, TA wants that new record to not include Mary because her terminate date was prior
        to the month in question (and she was already terminated on the terminate record we generated).
        */


        $CI = $this->ci;

        // Empty the worker.
        SupportTimerStart($company_id, $import_date, 'clean_worker_table', 'update_report_details_IgnoreTierChange');
        $CI->ReportTransamericaEligibility_model->clean_worker_table( $company_id, $import_date );
        SupportTimerEnd($company_id, $import_date, 'clean_worker_table', 'update_report_details_IgnoreTierChange');

        // capture dependents terminating this month with a termination date before the current import date
        SupportTimerStart($company_id, $import_date, 'capture_ignore_tier_change_dependents', 'update_report_details_IgnoreTierChange');
        $CI->ReportTransamericaEligibility_model->capture_ignore_tier_change_dependents($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'capture_ignore_tier_change_dependents', 'update_report_details_IgnoreTierChange');

        // and the parent changes tiers at the same time
        SupportTimerStart($company_id, $import_date, 'capture_ignore_tier_change_dependents', 'update_report_details_IgnoreTierChange');
        $CI->ReportTransamericaEligibility_model->ignore_tier_change_update_parent_not_terminating($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'capture_ignore_tier_change_dependents', 'update_report_details_IgnoreTierChange');

        // make sure any of the records we identified were not flagged as lost.  If they were, then
        // we should not flag them to be ignored.
        SupportTimerStart($company_id, $import_date, 'ignore_tier_change_lost_lives', 'update_report_details_IgnoreTierChange');
        $CI->ReportTransamericaEligibility_model->ignore_tier_change_lost_lives($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'ignore_tier_change_lost_lives', 'update_report_details_IgnoreTierChange');

        // Any records in the worker that have the ignore flag set should be deleted.
        SupportTimerStart($company_id, $import_date, 'ignore_tier_change_removed_flagged', 'update_report_details_IgnoreTierChange');
        $CI->ReportTransamericaEligibility_model->ignore_tier_change_removed_flagged($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'ignore_tier_change_removed_flagged', 'update_report_details_IgnoreTierChange');

        // Set the IssueCode to IGNORE_TIER_CHANGE for the identified files.
        SupportTimerStart($company_id, $import_date, 'ignore_tier_change_update_from_worker', 'update_report_details_IgnoreTierChange');
        $CI->ReportTransamericaEligibility_model->ignore_tier_change_update_from_worker($this->company_id, $this->import_date);
        SupportTimerEnd($company_id, $import_date, 'ignore_tier_change_update_from_worker', 'update_report_details_IgnoreTierChange');

        // Empty the worker.
        $CI->ReportTransamericaEligibility_model->clean_worker_table( $company_id, $import_date );

    }

}
