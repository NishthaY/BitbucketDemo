<?php

class Retro_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    function get_coverage_tier_lookup($company_id, $target_date)
    {
        $file = "database/sql/retrodata/RetroDataSELECT_CoverageTierLookup.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($target_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function get_best_guess_before_coveragestartdate( $company_id, $life_id, $coveragetiers_list, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $replaceFor = array();
        $replaceFor["{LIST}"] = $coveragetiers_list;

        $file = "database/sql/retrodata/RetroDataSELECT_BestGuess_BeforeCoverageStartDate_Many2Many.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($life_id)
        );
        $results = GetDBResults( $this->db, $file, $vars, $replaceFor );

        // No results, return the empty string.
        if ( count($results) == 0 ) return "";

        // More than one result, return a comma delimited list of date strings.
        if ( count($results) != 1 ) {
            $retval = "";
            foreach($results as $result)
            {
                $date = getArrayStringValue("BestGuess_Before-CoverageStartDate", $result);
                $retval .= $date . ",";
            }
            $retval = fLeftBack($retval, ",");
            return $retval;
        }

        // Exactly one, return the date as a strng.
        $results = $results[0];
        return getArrayStringValue("BestGuess_Before-CoverageStartDate", $results);

    }
    function get_best_guess_before_planid( $company_id, $life_id, $coveragetiers_list, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();
        
        $replaceFor = array();
        $replaceFor["{LIST}"] = $coveragetiers_list;

        $file = "database/sql/retrodata/RetroDataSELECT_BestGuess_BeforePlanId_Many2Many.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($life_id)
        );
        $results = GetDBResults( $this->db, $file, $vars, $replaceFor );

        // No results, return the empty string.
        if ( count($results) == 0 ) return "";

        // More than one result, return a comma delimited list of date strings.
        if ( count($results) != 1 ) {
            $retval = "";
            foreach($results as $result)
            {
                $date = getArrayStringValue("BestGuess_Before-PlanId", $result);
                $retval .= $date . ",";
            }
            $retval = fLeftBack($retval, ",");
            return $retval;
        }

        // Exactly one, return the date as a strng.
        $results = $results[0];
        return getArrayStringValue("BestGuess_Before-PlanId", $results);

    }
    function get_best_guess_before_volume( $company_id, $life_id, $coveragetiers_list, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $replaceFor = array();
        $replaceFor["{LIST}"] = $coveragetiers_list;

        $file = "database/sql/retrodata/RetroDataSELECT_BestGuess_BeforeVolume_Many2Many.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($life_id)
        );
        $results = GetDBResults( $this->db, $file, $vars, $replaceFor );

        // No results, return the empty string.
        if ( count($results) == 0 ) return "";

        // More than one result, return a comma delimited list of date strings.
        if ( count($results) != 1 ) {
            $retval = "";
            foreach($results as $result)
            {
                $date = getArrayStringValue("BestGuess_Before-Volume", $result);
                $retval .= $date . ",";
            }
            $retval = fLeftBack($retval, ",");
            return $retval;
        }

        // Exactly one, return the date as a strng.
        $results = $results[0];
        return getArrayStringValue("BestGuess_Before-Volume", $results);

    }
    function get_best_guess_before_monthlycost( $company_id, $life_id, $coveragetiers_list, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $replaceFor = array();
        $replaceFor["{LIST}"] = $coveragetiers_list;

        $file = "database/sql/retrodata/RetroDataSELECT_BestGuess_BeforeMonthlyCost_Many2Many.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($life_id)
        );
        $results = GetDBResults( $this->db, $file, $vars, $replaceFor );

        // No results, return the empty string.
        if ( count($results) == 0 ) return "";

        // More than one result, return a comma delimited list of date strings.
        if ( count($results) != 1 ) {
            $retval = "";
            foreach($results as $result)
            {
                $date = getArrayStringValue("BestGuess_Before-MonthlyCost", $result);
                $retval .= $date . ",";
            }
            $retval = fLeftBack($retval, ",");
            return $retval;
        }

        // Exactly one, return the date as a strng.
        $results = $results[0];
        return getArrayStringValue("BestGuess_Before-MonthlyCost", $results);

    }
    function select_retro_data_WIDE( $company_id, $target_date, $plantypecode, $coveragetiers_list, $life_id ) {

        $replaceFor = array();
        $replaceFor["{LIST}"] = $coveragetiers_list;

        $file = "database/sql/retrodata/RetroDataSELECT_ByPlanTypeCode.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($target_date)
            , getStringValue($plantypecode)
            , getIntValue($life_id)
        );
        $results = GetDBResults( $this->db, $file, $vars, $replaceFor );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function update_retro_data_coverage_tier( $company_id, $coveragetierid, $before_coveragetierid, $life_id, $plantypecode, $before_coveragestartdate, $before_volume, $before_monthlycost, $before_coveragestartdatelist, $before_planid, $before_planidlist, $import_date=null ){

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/retrodata/RetroDataUPDATE_BeforeData-CoverageTier.sql";
        $vars = array(
            getStringValue($coveragetierid)
            ,getStringValue($before_coveragetierid)
            ,( getStringValue($before_coveragestartdate) == "" ? null : getStringValue($before_coveragestartdate) )
            ,( getStringValue($before_volume) == "" ? null : getStringValue($before_volume) )
            ,( getStringValue($before_monthlycost) == "" ? null : getStringValue($before_monthlycost) )
            ,( getStringValue($before_coveragestartdatelist) == "" ? null : getStringValue($before_coveragestartdatelist) )
            ,( getStringValue($before_planid) == "" ? null : getStringValue($before_planid) )
            ,( getStringValue($before_planidlist) == "" ? null : getStringValue($before_planidlist) )
            ,getIntValue($company_id)
            ,getStringValue($import_date)
            ,getIntValue($life_id)
            ,getStringValue($plantypecode)

        );
        ExecuteSQL( $this->db, $file, $vars );
    }

    function DEFUNCTselect_retro_data_distinct_coverage_tiers_by_life_and_plantype($company_id, $life_id, $plantypecode, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/retrodata/DEFUNCT-RetroDataSELECT_DistinctCoverageTiersByLifeAndPlanTypeCode.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($life_id)
            , getStringValue($plantypecode)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }

    function DEFUNCTselect_retro_data_distinct_plantypes_by_life( $company_id, $life_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/retrodata/DEFUNCT-RetroDataSELECT_DistinctPlanTypesByLife.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($life_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function DEFUNCTselect_retro_data_distinct_lives( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/retrodata/DEFUNCT-RetroDataSELECT_DistinctLives.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;

    }
    function select_retro_data_ordered_coverage_tiers($company_id) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/RetroDataSELECT_OrderedCoverageTiers.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return "";

        return $results;
    }


    function update_summary_data_with_automatic_updates( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        // Grab a collection of data from the database that has all of our automatic
        // adjustments.  This groups the adjustments by key, life and attributes.
        $file = "database/sql/adjustments/AutomaticAdjustmentsSELECT_ByRetroGroupings.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if (empty($results)) return;

        // Take the adjustment data we pulled above, examine the attributes and
        // then construct and update statement that will place the adjustment
        // total into the correct already existing location in the summary table
        // for the report.
        foreach($results as $item)
        {
            $carrier_id = getArrayIntValue("CarrierId", $item);
            $plantype_id = getArrayIntValue("PlanTypeId", $item);
            $plan_id = getArrayIntValue("PlanId", $item);
            $coveragetier_id = getArrayIntValue("CoverageTierId", $item);
            $ageband_id = getArrayStringValue("AgeBandId", $item);
            $tobaccouser = getArrayStringValue("TobaccoUser", $item);

            $record_exists = "";
            $record_exists = $this->does_summary_data_record_exist($company_id, $import_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $ageband_id, $tobaccouser);

            // Does the summary record exist?
            if ( $record_exists == "f" )
            {
                // Nope.  This can happen once in a while when there is an adjustment for
                // something that is no longer in the file.  Just add it now so we can update.
                // it below.
                $band = $ageband_id;
                if ( $band == "" ) $band = null;

                $tobacco = $tobaccouser;
                if ( $tobacco == "" ) $tobacco = null;

                $this->Reporting_model->insert_summary_data_record($company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $band, $tobacco);


            }

            // Attributes may be null.  Assume they are not, then if they are fix it.
            $replaceFor = array();
            $replaceFor['{AGEBAND}'] =  " = {$ageband_id}";
            $replaceFor['{TOBACCOUSER}'] =  " = '{$tobaccouser}'";
            if ( $ageband_id == ""  ) $replaceFor['{AGEBAND}'] = " is null ";
            if ( $tobaccouser == ""  ) $replaceFor['{TOBACCOUSER}'] = " is null ";

            $file = "database/sql/summarydata/SummaryDataUPDATE_AutomaticAdjustmentByAttributes.sql";
            $vars = array(
                getArrayFloatValue("TotalAdjustedVolume", $item)
                , getArrayFloatValue("TotalAdjustedPremium", $item)
                , getIntValue($company_id)
                , getStringValue($import_date)
                , getArrayIntValue("CarrierId", $item)
                , getArrayIntValue("PlanTypeId", $item)
                , getArrayIntValue("PlanId", $item)
                , getArrayIntValue("CoverageTierId", $item)
            );
            ExecuteSQL($this->db, $file, $vars, $replaceFor);
        }

        // Last but not least, we need to update the "lives" for the automatic adjustments
        // that we just inserted into the SummaryData table.
        $file = "database/sql/summarydata/SummaryDataUPDATE_AutomaticAdjustmentLives.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars, $replaceFor);

    }
    function does_summary_data_record_exist( $company_id, $import_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $ageband_id, $tobaccouser) {

        $replaceFor = array();

        $replaceFor['{PLANTYPEID}'] =  " = '{$plantype_id}'";
        if ( getStringValue($plantype_id) == ""  ) $replaceFor['{PLANTYPEID}'] = " is null ";

        $replaceFor['{PLANID}'] =  " = '{$plan_id}'";
        if ( getStringValue($plan_id) == ""  ) $replaceFor['{PLANID}'] = " is null ";

        $replaceFor['{COVERAGETIERID}'] =  " = '{$coveragetier_id}'";
        if ( getStringValue($coveragetier_id) == ""  ) $replaceFor['{COVERAGETIERID}'] = " is null ";

        $replaceFor['{AGEBAND}'] =  " = '{$ageband_id}'";
        if ( getStringValue($ageband_id) == ""  ) $replaceFor['{AGEBAND}'] = " is null ";

        $replaceFor['{TOBACCOUSER}'] =  " = '{$tobaccouser}'";
        if ( getStringValue($tobaccouser) == ""  ) $replaceFor['{TOBACCOUSER}'] = " is null ";

        $file = "database/sql/summarydata/SummaryDataEXISTS.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue( $carrier_id )
        );
        $results = GetDBResults( $this->db, $file, $vars, $replaceFor );
        if ( count($results) == 1 )
        {
            $results = $results[0];
            return getArrayStringValue("Exists", $results);
        }
        throw new Exception("Found too many / too few import records for the given life!");
    }

    function select_importdata_by_life( $company_id, $import_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $life_id ) {

        $file = "database/sql/importdata/ImportDataSELECT_ByLife.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue( $carrier_id )
            , getIntValue( $plantype_id )
            , getIntValue( $plan_id )
            , getIntValue( $coveragetier_id )
            , getIntValue( $life_id )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) == 1 ) return $results[0];

        LogIt(__FUNCTION__, "Found too many import records for the given live.", $life_id);
        throw new Exception("Found too many import records for the given life.");

    }
    function select_adjustments_by_target_date_for_life( $company_id, $target_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $life_id ) {

        $file = "database/sql/adjustments/AutomaticAdjustmentsSELECT_ByTargetDateForLife.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue( $carrier_id )
            , getIntValue( $plantype_id )
            , getIntValue( $plan_id )
            , getIntValue( $coveragetier_id )
            , getIntValue( $life_id )
            , getStringValue( $target_date )
        );

        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function delete_automatic_adjustments( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/adjustments/AutomaticAdjustmentDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function delete_automatic_adjustment_outside_retro_rules( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/adjustments/AutomaticAdjustmentDELETE_OutsideRetroRuleRange.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function insert_automatic_adjustment( $company_id, $retro_id, $life_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $adjustment_type, $volume, $employer_cost, $target_date, $memo, $parent_retro_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        // If you have a parent_retro_id, then your adjustment_type is really a RETRO_CHANGE.
        if ( $parent_retro_id != null ) $adjustment_type = ADJUSTMENT_TYPE_RETRO_CHANGE;


        $file = "database/sql/adjustments/AutomaticAdjustmentINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($target_date)
            , getIntValue( $retro_id )
            , getIntValue( $life_id )
            , getIntValue( $carrier_id )
            , getIntValue( $plantype_id )
            , getIntValue( $plan_id )
            , getIntValue( $coveragetier_id )
            , getIntValue( $adjustment_type )
            , getFloatValue( $volume )
            , getFloatValue( $employer_cost )
            , getStringValue( $memo )
            , ( $parent_retro_id == null ? null : getIntValue($parent_retro_id) )
        );
        ExecuteSQL( $this->db, $file, $vars );

    }

    function is_first_import($company_id, $target_date) {

        // Make sure the target-date is in MM/DD/YYYY format.  If it's not,
        // this function could fail.
        $target_date = FormatDateMMDDYYYY($target_date);

        $file = "database/sql/importdata/ImportDataSELECT_IsFirstImport.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($target_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) {
            $results = $results[0];
            $results = GetArrayStringValue("IsFirstImport", $results);
            if ( $results == "t" || $results == "f" ) return $results;
        }
        throw new Exception("Unable to figure out if this is the first import or not.");
    }
    function get_retro_month( $company_id, $retro_rule ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/GetRetroMonth.sql";
        $vars = array(
            getStringValue($import_date)
            , getIntValue($retro_rule)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) == 1 ) return $results[0];
        throw new Exception("Unable to calculate the retro month.");

    }
    function get_next_month($date) {

        $file = "database/sql/retrodata/GetNextMonth.sql";
        $vars = array(
            getStringValue($date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) == 1 ) {
            return getArrayStringValue("NextMonth", $results[0]);
        }
        throw new Exception("Unable to calculate the retro month.");
    }
    function get_mid_month($date) {
        $file = "database/sql/retrodata/GetMidMonth.sql";
        $vars = array(
            getStringValue($date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) == 1 ) {
            return getArrayStringValue("MidMonth", $results[0]);
        }
        throw new Exception("Unable to calculate the retro month.");
    }

    function get_retro_months( $company_id, $wash_rule ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $replaceFor = array();
        $replaceFor['{TARGET_DATE}'] = $import_date;
        $replaceFor['{WASH_INTERVAL}'] = $wash_rule;

        $file = "database/sql/retrodata/GetRetroMonths.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars, $replaceFor );
        if ( count($results) == 0 ) return array();
        if ( count($results) == 1 ) return $results[0];
        throw new Exception("Unable to calculate the retro months.");

    }
    function does_import_exist( $company_id, $target_month ) {
        $file = "database/sql/importdata/ImportDataSELECT_DoesImportExist.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($target_month)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 )
        {
            return getArrayStringValue("ImportExists", $results[0]);
        }
        throw new Exception("Unable to tell if the import exists or not.");
    }
    function select_retro_data($company_id) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/RetroDataSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function select_retro_data_by_id($company_id, $retro_id) {

        $file = "database/sql/retrodata/RetroDataSELECT_byId.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($retro_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) == 1 ) return $results[0];
        throw new Exception("Found too many retro data objects with the same id");
    }
    function delete_retro_data($company_id, $import_date=null) {

        // delete_retro_data
        //
        // Delete all data for the given company and the current import file
        // located in the "RetroData" table.
        // ------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/retrodata/RetroDataDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function insert_retro_data($company_id) {

        //$this->insert_retro_data_ORIGINAL($company_id);
        $this->insert_retro_data_FASTER($company_id);

    }
    function insert_retro_data_ORIGINAL($company_id) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/RetroDataINSERT.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function insert_retro_data_FASTER($company_id) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/RetroDataINSERT_Faster.sql";
        $vars = array(
            getIntValue($company_id),
            GetStringValue($import_date)

        );
        //SelectIntoInsert( $this->db, $file, $vars );
        CopyFromInto( $this->db, $file, $vars );
    }
    function update_retro_data($company_id) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/RetroDataUPDATE_BeforeData.sql";
        $vars = array(
            getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_retro_data_change_new_coverage_tier($company_id) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/RetroDataUPDATE_RetroChange_NewCoverageTier.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_retro_data_change_new_employer_cost($company_id) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/RetroDataUPDATE_RetroChange_NewMonthlyCost.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_retro_data_change_new_volume($company_id) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/RetroDataUPDATE_RetroChange_NewVolume.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_retro_data_change_new_effective_date($company_id) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/RetroDataUPDATE_RetroChange_NewEffectiveDate.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_retro_data_add_new_entry($company_id) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/RetroDataUPDATE_RetroAdd_NewEntry.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_retro_data_add_updated_entry($company_id) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/RetroDataUPDATE_RetroAdd_UpdatedEntry.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_retro_data_term($company_id) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/retrodata/RetroDataUPDATE_RetroTerm.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function get_date_MMM_YYYY( $date ) {
        $file = "database/sql/adjustments/GetDateMMMYYYY.sql";
        $vars = array(
            getStringValue($date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) return getArrayStringValue("FormattedDate", $results[0]);
        throw new Exception("Unexpected results when formatting date.");
    }



}


/* End of file retro_model.php */
/* Location: ./system/application/models/retro_model.php */
