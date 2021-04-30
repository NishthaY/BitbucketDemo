<?php

class Life_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);

    }
    function insert_missing_lives_by_importlife( $company_id )
    {

        $import_date = GetRecentDate($company_id);
        $file = 'database/sql/life/ImportLifeINSERT_MissingLives.sql';
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function insert_importlife_keys( $company_id, $import_date=null )
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        if ( $import_date === '' ) return;
        $file = 'database/sql/life/ImportLifeINSERT.sql';
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function delete_importlife( $company_id, $import_date="" )
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        if ( $import_date === '' ) return;

        $file = 'database/sql/life/ImportLifeDELETE.sql';
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function has_ssn_data( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) throw new Exception("Missing required input import_date.");

        $file = "database/sql/life/ImportDataSELECT_HasSSNs.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return "f";
        if ( count($results) > 1 ) throw new Exception("Found too many results when trying to count relationship data.");
        $results = $results[0];
        $has_ssns = getArrayStringValue("HasSSNs", $results);
        return $has_ssns;
    }
    public function select_companylifecompare_is_complete( $company_id, $import_date=null ) {

        // select_companylifecompare_is_complete
        //
        // This function will return TRUE if all records in the CompanyLifeCompare
        // table for the given inputs have been filled in.
        // ------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareSELECT_IsComplete.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1 ) throw new Exception("Unable to tell if Life Review is complete.");
        $results = $results[0];
        if ( getArrayStringValue("IsComplete", $results ) == "t" ) return true;
        if ( getArrayStringValue("IsComplete", $results ) == "f" ) return false;
        throw new Exception("Unable to tell if Life Review is complete.");

    }

    public function enable_companylife( $life_id ) {

        // enable_companylife
        //
        // This will enable a life so that the life in question is used
        // when calculating reports.
        // ---------------------------------------------------------------

        $file = "database/sql/life/CompanyLifeUPDATE_enable.sql";
        $vars = array(
            getIntValue($life_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function disable_companylife( $life_id ) {

        // disable_companylife
        //
        // This will disable a life so that the life in question is NOT used
        // when calculating reports.
        // ---------------------------------------------------------------

        $file = "database/sql/life/CompanyLifeUPDATE_disable.sql";
        $vars = array(
            getIntValue($life_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function rollback_companylife( $life_id, $company_id, $import_date=null ) {

        // rollback_companylife
        //
        // This function will look for for the life in the CompanyLifeCompare table
        // and rollback any changes that were made to the CompanyLife table based on
        // the information in the CompanyLifeCompare table.
        //
        // If the CompanyLifeCompare table indicates that nothing needs to be
        // rolled back, then this function will just exit.
        //
        // There could be many companylifecompare records found for the life_id.  This is because
        // a single life could have multiple policies such as both Medical and Vision.  Even though
        // we might get multiple records here, the life associated is unique.  Since the supporting
        // functions will execute against all policies for the associated life, we only need
        // to run the items below on the first companylifecompare record that we find.
        //
        // ------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        // Select life you are going to rollback.  bail if we have no CompanyLifeCompare record.
        $records = $this->select_companylifecompare_by_id($life_id, $company_id, $import_date);
        if ( empty($records) ) reutrn;
        $life = $records[0];

        // Organize our data.
        $is_new_life = getArrayStringValue("IsNewLife", $life);
        $updates_life_id = getArrayStringValue("UpdatesLifeId", $life);

        // If IsNewLife is null, then we have nothing to rollback. bail.
        if ( $is_new_life == "" ) return;

        // If needed, rollback life data stored in the compare record to the
        // targeted life.
        if ( $is_new_life == "f" ) {
            // At some point in the past, we have replaced $updates_life_id with
            // the data found on $life_id.  We need to put things back they way they
            // were before.  Do that by taking the "rollback" fields off the compare
            // record for life_id and push them over the life record referenced by
            // update_life_id.
            $this->restore_companylife_from_companylifecompare( $life_id, $updates_life_id, $company_id, $import_date );
            $this->updates_lifedata_by_companylifecompare( $life_id, $life_id, $company_id, $import_date);
        }

        // always, reset the compare record back to it's unset state.
        $file = "database/sql/life/CompanyLifeCompareUPDATE_ResetRecord.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($life_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function select_companylifecompare_by_id( $life_id, $company_id, $import_date=null ) {

        // select_companylifecompare_by_id
        //
        // Select and return a single CompanyLifeCompare record by life_id,
        // company_id and import_date.  We expect zero or one records. Anything
        // else is an error.
        // ------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareSELECT_ById.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($life_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;

    }
    public function select_companylifecompare( $company_id, $import_date=null ) {

        // select_companylifecompare
        //
        // Select all CompanyLifeCompare records for the given company and
        // import.
        // ------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;

    }
    public function replace_companylife($source_lifeid, $target_lifeid ) {

        // replace_companylife
        //
        // This function will replace the record content for life with an id
        // matching the target_lifeid with the record contect for the life with
        // an id matching source_lifeid.
        // ------------------------------------------------------------------

        $file = "database/sql/life/CompanyLifeUPDATE_ReplaceLife.sql";
        $vars = array(
            getIntValue($source_lifeid)
            , getIntValue($target_lifeid)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function backup_companylife_to_companylifecompare( $life_id, $updates_life_id, $company_id, $import_date=null ) {

        // backup_companylifecompare
        //
        // This function will take the life record referenced by updates_life_id
        // in the CompanyLifeCompare table and backup the life referenced by life_id
        // so that we can rollback to the values before we made changes.
        // ------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareUPDATE_BackupLife.sql";
        $vars = array(
            getIntValue($updates_life_id)
            , getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($life_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function restore_companylife_from_companylifecompare( $life_id, $updates_life_id, $company_id, $import_date=null ) {

        // restore_companylife_from_companylifecompare
        //
        // This function will take the life_id record from the CompanyLifeCompare
        // table and push the "Rollback" data column over the updates_life_id
        // record in the CompanyLife table.
        // ------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareUPDATE_RestoreLife.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($life_id)
            , getIntValue($updates_life_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function update_companylifecompare_new_life( $life_id, $company_id, $import_date=null ) {

        // update_companylifecompare_new_life
        //
        // This function will empty the CompanyLifeCompare record referenced
        // back to it's default settings as if the user has made no elections
        // but needs to.
        // --------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareUPDATE_SetAsNewLife.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($life_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function set_autoselected_companylife( $life_id, $updates_life_id, $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;


        $file = "database/sql/life/CompanyLifeCompareUPDATE_SetAutoSelected.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($life_id)
            , getIntValue($updates_life_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function update_companylife( $life_id, $updates_life_id, $company_id, $import_date=null ) {

        // update_companylife
        //
        // This function will update an existing life based on the information
        // stored in the CompanyLifeCompare table for the given company and import
        // where we will update the $updates_life_id with the data found in life $life_id.
        // --------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        // We need to update a life!  First, rollback any changes we made previously.
        $this->rollback_companylife($life_id, $company_id, $import_date);

        if ( getStringValue($updates_life_id) == "" )
        {
            // Treat this life as a new life record.
            $this->update_companylifecompare_new_life($life_id, $company_id, $import_date);
            $this->updates_lifedata_by_companylifecompare($life_id, $life_id, $company_id, $import_date, $life_id);
            $this->enable_companylife( $life_id );
            $this->enable_companylife( $updates_life_id );
        }
        else
        {
            // Update an existing life record with new data.
            $this->backup_companylife_to_companylifecompare($life_id, $updates_life_id, $company_id, $import_date);
            $this->updates_lifedata_by_companylifecompare($life_id, $updates_life_id, $company_id, $import_date);
            $this->disable_companylife( $life_id );
            $this->enable_companylife( $updates_life_id );
            $this->replace_companylife( $life_id, $updates_life_id);
        }

    }
    public function updates_lifedata_by_companylifecompare($compare_life_id, $new_lifedata_life_id, $company_id, $import_date ){

        // updates_lifedata_by_companylifecompare
        //
        // This query will set the related LifeData record's LifeId based
        // on the "selected" life_id from the CompanyLifeCompare table.
        // -------------------------------------------------------------------

        // Now we can delete the CompanyLifeCompare records.
        $file = "database/sql/life/LifeDataUPDATE_LifeIdByCompare.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($compare_life_id)
            , getIntValue($new_lifedata_life_id)
        );
        ExecuteSQL( $this->db, $file, $vars );


    }
    public function select_companylifecompare_parents( $company_id, $import_date=null){

        // select_companylifecompare_parents
        //
        // This function will select all of the "parent" records.  Meaning the
        // records that were identified in the CompanyLifeCompare table as
        // needed a decision made against them.  Do we update, or treat as new?
        // -------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareSELECT_Parents.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;

    }
    public function select_companylifecompare_children( $company_id, $employee_id, $import_date=null) {

        // select_companylifecompare_children
        //
        // This function will select all of the "child" records.  Meaning the
        // records that were identified in the CompanyLifeCompare table as
        // needed a decision made against them have other lives associated
        // by employee_id.  This query will select all the other lives.  One
        // of which might be updated by the related parent record.
        // -------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareSELECT_Children.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($employee_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }

    public function delete_companylifecompare($company_id, $import_date=null){

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        // Before we can delete the company life compare records, we will need
        // to "rollback" any records we are about to delete.
        $records = $this->select_companylifecompare($company_id, $import_date);
        foreach($records as $record)
        {
            $life_id = getArrayStringValue("LifeId", $record);
            $this->rollback_companylife( $life_id, $company_id, $import_date );
        }

        // Now we can delete the CompanyLifeCompare records.
        $file = "database/sql/life/CompanyLifeCompareDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function select_insert_companylifecompare_has_lifes_to_compare( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareSELECT_HasLivesToCompare.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) > 1 ) throw new Exception("Found too many rows.");
        $results = $results[0];
        $value = getArrayStringValue("HasLivesToCompare", $results);
        return $value;

    }

    public function select_insert_companylifecompare_has_lifes_yet_to_compare( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareSELECT_HasLivesYetToCompare.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) > 1 ) throw new Exception("Found too many rows.");
        $results = $results[0];
        $value = getArrayStringValue("HasLivesToCompare", $results);
        return $value;

    }


    public function insert_companylifecompare( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }

    public function delete_companyliferesearch( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeResearchDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );


    }
    public function insert_companyliferesearch_previous_month( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        /*
            -- CompanyLifeResearch
            -- This table will help us identify if a life has dropped off from last months
            -- run.  This insert statement will insert all of the lives we are interested
            -- in by employee id from last month.
         */

        $file = "database/sql/life/CompanyLifeResearchINSERT_PreviousMonth.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function update_companyliferesearch_current_month( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        /*
            -- CompanyLifeResearch
            -- This table will help us identify if a life has dropped off from last months
            -- run.  This update statement will add the current life key next to the
            -- previous month key.  If an item is in the previous column, but not in
            -- the current column for a given EmployeeId, then we need to investigate.
         */

        $file = "database/sql/life/CompanyLifeResearchUPDATE_CurrentMonth.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function update_lifedata_mark_eid_existed_last_month( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/LifeDataUPDATE_EIDExistedLastMonthFlg.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        SelectIntoUpdate($this->db, $file, $vars);

    }
    public function update_lifedata_mark_new_lives ( $company_id, $import_date = null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $max = $this->select_lifedata_max_lifeid_last_month($company_id, $import_date);

        $file = "database/sql/life/LifeDataUPDATE_NewLives.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($max)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function select_lifedata_max_lifeid_last_month( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/LifeDataSELECT_MaxLifeIdLastMonth.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) > 1 ) throw new Exception("Found too many rows.");
        $results = $results[0];
        $value = getArrayStringValue("Max", $results);
        if ( $value == "" ) $value = 0;
        return $value;

    }
    public function delete_companylife_new_lives( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeDELETE_NewLives.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function delete_companylife_disabled( $company_id ) {
        $file = "database/sql/life/CompanyLifeDELETE_Disabled.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function delete_lifedata( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/LifeDataDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function insert_new_life_records( $company_id, $import_date )
    {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/life/CompanyLifeINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        SelectIntoInsert( $this->db, $file, $vars );

    }
    public function insert_life_data( $company_id, $import_date )
    {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/life/LifeDataINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        SelectIntoInsert( $this->db, $file, $vars );

    }
    public function select_companylifecompare_auto_update_canidates( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareSELECT_AutoUpdateCanidates.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function select_companylifecompare_auto_update_canidates_count( $company_id, $ssn, $import_date=null )
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareSELECT_AutoUpdateCanidates_BySSN.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date),
            getStringValue($ssn),
        );
        $results = GetDBResults( $this->db, $file, $vars );
        return count($results);
    }

    public function select_companylifecompare_auto_update_canidate_match( $company_id, $ssn, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareSELECT_AutoUpdateCanidateMatch.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($ssn)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) return $results[0];

        // You must have an exact match.  If you don't get exactly
        // one result, you don't get a match.
        return array();
    }


    public function select_companylifecompare_auto_update_canidate_match_no_ssn( $company_id, $ssn, $firstname, $lastname, $middlename, $eid, $dob, $relationship, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/life/CompanyLifeCompareSELECT_AutoUpdateCanidateMatch_NoSSN.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($ssn)
            , getStringValue($firstname)
            , getStringValue($lastname)
            , getStringValue($middlename)
            , getStringValue($eid)
            , getStringValue($dob)
            , getStringValue($relationship)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) return $results[0];

        // You must have an exact match.  If you don't get exactly
        // one result, you don't get a match.
        return array();
    }

    public function select_all_lives($company_id, $max=1000)
    {
        $file = "database/sql/life/CompanyLifeSELECT.sql";
        $vars = array(
            getIntValue($company_id),
            getIntValue($max)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();

        return $results;
    }

    public function delete_import_life_worker($company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        $file = "database/sql/life/ImportLifeWorkerDELETE_ByImport.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function delete_import_life_warning($company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        $file = "database/sql/life/ImportLifeWarningDELETE_ByImport.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function insert_new_lives_into_worker($company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        $file = "database/sql/life/ImportLifeWorkerINSERT_NewLives.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function insert_duplicates_into_warning($company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        $file = "database/sql/life/ImportLifeWarningINSERT_DuplicateLives.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function insert_unwanted_lives_into_worker($company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        $file = "database/sql/life/ImportLifeWorkerINSERT_UnwantedLives.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update_companylife_new_duplicate_lives($company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        $file = "database/sql/life/CompanyLifeUPDATE_UnwantedLives.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

}


/* End of file life_model.php */
/* Location: ./system/application/models/life_model.php */
