<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateLifeData extends A2PLibrary {

    protected $slowdown;

    function __construct( $debug=false )
    {
        parent::__construct($debug);
        $this->slowdown = null;
    }

    public function execute( $company_id, $user_id=null )
    {
        try {

            parent::execute($company_id);

            $CI = $this->ci;
            $this->slowdown = GetAppOption(REST_SECONDS_BETWEEN_QUERIES);

            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");

            // What is our import date?
            $import_date = GetUploadDate($company_id);
            if ( $import_date == "" ) throw new Exception("Invalid import_date.  How is that possible?");

            $this->debug(" ImportDate: [{$import_date}]");
            $this->debug(" ComapnyId:  [{$company_id}]");

            $this->debug(" Removing CompanyLifeCompare.");
            SupportTimerStart($company_id, $import_date, 'delete_companylifecompare', __CLASS__);
            $CI->Life_model->delete_companylifecompare($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'delete_companylifecompare', __CLASS__);

            $this->debug(" Removing CompanyLifeResearch");
            SupportTimerStart($company_id, $import_date, 'delete_companyliferesearch', __CLASS__);
            $CI->Life_model->delete_companyliferesearch($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'delete_companyliferesearch', __CLASS__);

            $this->debug(" Removing CompanyLife data.");
            SupportTimerStart($company_id, $import_date, 'delete_companylife_new_lives', __CLASS__);
            $CI->Life_model->delete_companylife_new_lives($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'delete_companylife_new_lives', __CLASS__);

            $this->debug(" Removing LifeData.");
            SupportTimerStart($company_id, $import_date, 'delete_lifedata', __CLASS__);
            $CI->Life_model->delete_lifedata($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'delete_lifedata', __CLASS__);

            $this->debug(" Removing Disabled CompanyLife records.");
            SupportTimerStart($company_id, $import_date, 'delete_companylife_disabled', __CLASS__);
            $CI->Life_model->delete_companylife_disabled($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'delete_companylife_disabled', __CLASS__);

            $this->debug( " Removing ImportLife data.");
            SupportTimerStart($company_id, $import_date, 'delete_importlife', __CLASS__);
            $this->ci->Life_model->delete_importlife($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'delete_importlife', __CLASS__);

            $this->debug( " Removing ImportLifeWarning data.");
            SupportTimerStart($company_id, $import_date, 'delete_importlifewarning', __CLASS__);
            $this->ci->Life_model->delete_import_life_warning($company_id);
            SupportTimerEnd($company_id, $import_date, 'delete_importlifewarning', __CLASS__);

            $this->debug( " Calculating the life keys for our import records.");
            SupportTimerStart($company_id, $import_date, 'insert_importlife_keys', __CLASS__);
            $CI->Life_model->insert_importlife_keys($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'insert_importlife_keys', __CLASS__);

            $this->debug(" Looking for new lives and adding them to CompanyLife.");
            SupportTimerStart($company_id, $import_date, 'insert_new_life_records', __CLASS__);
            $CI->Life_model->insert_new_life_records($company_id, $import_date);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'insert_new_life_records', __CLASS__);

            $this->debug(" Inserting LifeData. Linking ImportData and CompanyLife.");
            SupportTimerStart($company_id, $import_date, 'insert_life_data', __CLASS__);
            $CI->Life_model->insert_life_data($company_id, $import_date);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'insert_life_data', __CLASS__);

            $this->debug(" Updating LifeData.  Marking new records for this month.");
            SupportTimerStart($company_id, $import_date, 'update_lifedata_mark_new_lives', __CLASS__);
            $CI->Life_model->update_lifedata_mark_new_lives($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'update_lifedata_mark_new_lives', __CLASS__);

            $this->debug(" Updating LifeData.  Setting if EID existed last month or not.");
            SupportTimerStart($company_id, $import_date, 'update_lifedata_mark_eid_existed_last_month', __CLASS__);
            $CI->Life_model->update_lifedata_mark_eid_existed_last_month($company_id, $import_date);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'update_lifedata_mark_eid_existed_last_month', __CLASS__);

            $this->debug(" Inserting CompanyLifeResearch for previous month.");
            SupportTimerStart($company_id, $import_date, 'insert_companyliferesearch_previous_month', __CLASS__);
            $CI->Life_model->insert_companyliferesearch_previous_month($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'insert_companyliferesearch_previous_month', __CLASS__);

            $this->debug(" Inserting CompanyLifeResearch for current month.");
            SupportTimerStart($company_id, $import_date, 'update_companyliferesearch_current_month', __CLASS__);
            $CI->Life_model->update_companyliferesearch_current_month($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'update_companyliferesearch_current_month', __CLASS__);

            $this->debug(" Inserting CompanyLifeCompare.");
            SupportTimerStart($company_id, $import_date, 'insert_companylifecompare', __CLASS__);
            $CI->Life_model->insert_companylifecompare($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'insert_companylifecompare', __CLASS__);

            $this->debug(" Inserting CompanyLifeCompare.");
            SupportTimerStart($company_id, $import_date, 'AutoUpdateLifeCompareBySSN', __CLASS__);
            AutoUpdateLifeCompareBySSN( $company_id );
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'AutoUpdateLifeCompareBySSN', __CLASS__);

            $this->debug(" AutoMatch matching CompanyLifeCompare records by everything except SSN.");
            SupportTimerStart($company_id, $import_date, 'AutoUpdateLifeCompareByNoSSN', __CLASS__);
            AutoUpdateLifeCompareByNoSSN($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
            SupportTimerEnd($company_id, $import_date, 'AutoUpdateLifeCompareByNoSSN', __CLASS__);

            $this->debug(" Looking for new lives that caused duplicates in the CompanyLife table.");
            SupportTimerStart($company_id, $import_date, 'new_life_duplicates', __CLASS__);
            $this->_handle_new_life_duplicates($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, 'new_life_duplicates', __CLASS__);


        } catch(Exception $e) {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * _handle_new_life_duplicates
     *
     * When we find a "new life" on the import, the life might have multiple records
     * if they have different types of coverage.  The life key used to make a life
     * unique is stored in the CompanyLife table along with a few other bits of data
     * like LastName and MiddleName.  Duplicate lives can happen if some of these
     * fields in the import data are not exactly the same between all of the records
     * for the life.
     *
     * The life compare process, ignores new lives.  It assumes the data over the new
     * life records are the same which can allow these duplicate lives to happen.
     *
     * This function will detect when we have created a duplicate life from new
     * records on the import.  Once detected will will keep one of the CompanyLife
     * records and remove the others.  We will attempt to keep the records with
     * the most data should a MiddleName or LastName be missing.
     *
     * This logic should happen AFTER we go through the life compare process.  One of
     * the first steps in the LifeCompare process is to identify all the new lives
     * to facilitate rollback.  We will use that identification to decide if the data
     * we are reviewing is a new life.
     *
     * In the end, we this function will end up "disabling" CompanyLife records that
     * are actually duplicates due to new lives being imported with inconsistent data
     * on their various life/tier records in the swing columns of middle and last name.
     *
     * Finalization will remove disabled lives at the end of the import process.  The
     * ImportLifeWarning table has a list of lives we trimmed down, but not what we
     * removed.
     *
     * @param $company_id
     * @param $import_date
     */
    private function _handle_new_life_duplicates($company_id, $import_date)
    {
        $CI = $this->ci;

        // Empty the worker table.
        $CI->Life_model->delete_import_life_worker($company_id, $import_date);

        // Empty the warning table.
        $CI->Life_model->delete_import_life_warning($company_id, $import_date);

        // Capture the unique life keys that were generated on this import associated with new lives.
        $CI->Life_model->insert_new_lives_into_worker($company_id, $import_date);

        // Create a list of "life keys" that are associated with "new lives" for this company
        // import that have more than one life key in the CompanyLife table.  (Table of record)
        // The records in the warning table are the life keys that have been dupliated that need
        // fixed.
        $CI->Life_model->insert_duplicates_into_warning($company_id, $import_date);

        // Empty the worker table.
        $CI->Life_model->delete_import_life_worker($company_id, $import_date);

        // Using the duplicate list now in the ImportLifeWarning table, create a list
        // of CompanyLife records we no longer want.  We will "flag" all but one of the
        // CompanyLife records for removal into the worker table.
        $CI->Life_model->insert_unwanted_lives_into_worker($company_id, $import_date);

        // Disable the CompanyLife records we flagged for deactivation.
        // Update the identified duplicates in the CompanyLife table by disabling them.
        $CI->Life_model->update_companylife_new_duplicate_lives($company_id, $import_date);

        // Empty the worker table.
        $CI->Life_model->delete_import_life_worker($company_id, $import_date);

    }
}
