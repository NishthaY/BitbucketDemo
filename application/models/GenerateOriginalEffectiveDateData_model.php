<?php

class GenerateOriginalEffectiveDateData_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    function remove_zero_dollar_warnings($company_id, $import_date)
    {
        $file = "database/sql/originaleffectivedate/LifeOriginalEffectiveDateWarningDELETE_ZeroDollar.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function publish_report_warnings($company_id, $import_date)
    {
        $file = "database/sql/originaleffectivedate/ReportReviewWarningINSERT_LifeOriginalEffectiveDate.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function rolback_warnings($company_id, $import_date)
    {
        $file = "database/sql/originaleffectivedate/LifeOriginalEffectiveDateWarningDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function rollback_report_review_warnings_created_by_oed_process ( $company_id, $import_date )
    {
        $file = "database/sql/originaleffectivedate/ReportReviewWarningsDELETE_LifeOriginalEffectiveDateWarning.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function compare_mark_record_oed_reset( $key, $import_date, $coverage_start_date, $description )
    {
        $file = "database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_OEDReset_Generic.sql";

        $life_id = GetArrayIntValue("LifeId", $key);
        $carrier_id = GetArrayIntValue("CarrierId", $key);
        $plantype_id = GetArrayIntValue("PlanTypeId", $key);
        $plan_id = GetArrayIntValue("PlanId", $key);
        $coverage_tier_id = GetArrayIntValue("CoverageTierId", $key);
        $code = GetArrayStringValue("Code", $key);

        $vars = array(
            GetStringValue($import_date),
            GetStringValue($coverage_start_date),
            GetStringValue($coverage_start_date),
            GetStringValue($description),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id),
            GetIntValue($coverage_tier_id),
            GetStringValue($code)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function has_life_plan_coverage_gap($coverage_start_date, $key)
    {
        $file = "database/sql/originaleffectivedate/LifeOriginalEffectiveDateSELECT_HasLifePlanCoverateGap.sql";

        $life_id = GetArrayIntValue("LifeId", $key);
        $carrier_id = GetArrayIntValue("CarrierId", $key);
        $plantype_id = GetArrayIntValue("PlanTypeId", $key);
        $plan_id = GetArrayIntValue("PlanId", $key);

        $vars = array(
            GetStringValue($coverage_start_date),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( count($results) !== 1 ) throw new Exception("Unable to count results");
        $results = $results[0];
        $has_gap = GetArrayStringValue("HasGapInCoverage", $results);
        if ( $has_gap === 't' ) return true;
        if ( $has_gap === 'f' ) return false;
        throw new Exception("Unable to process boolean check");
    }

    function has_active_life_plan_in_lockbox($key)
    {

        $file = "database/sql/originaleffectivedate/LifeOriginalEffectiveDateSELECT_HasActiveLifePlan.sql";

        $life_id = GetArrayIntValue("LifeId", $key);
        $carrier_id = GetArrayIntValue("CarrierId", $key);
        $plantype_id = GetArrayIntValue("PlanTypeId", $key);
        $plan_id = GetArrayIntValue("PlanId", $key);

        $vars = array(
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( count($results) !== 1 ) throw new Exception("Unable to count results");
        $results = $results[0];
        $has_active_life_plan = GetArrayStringValue("HasActiveLifePlan", $results);
        if ( $has_active_life_plan === 't' ) return true;
        if ( $has_active_life_plan === 'f' ) return false;
        throw new Exception("Unable to process boolean check");
    }
    // Get all of the NEW items that have nothing else happening this month.
    // Sand alone, not already processed by OEDReset logic.
    function oed_rest_tier_change_historical_NEW($company_id, $import_date)
    {
        $file = "database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_OEDReset_NEW_Historical.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        return $results;
    }


    function oed_reset_new($company_id, $import_date)
    {
        $file = "database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_OEDReset_New.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
    }
    function oed_reset_new_exclude_tier_changes($company_id, $import_date, $starting_date)
    {
        $file = "database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_OEDReset_New_ExcludeTierChanges.sql";
        $vars = array(
            GetStringValue($starting_date),
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
    }
    function oed_rest_tier_change_RESTART( $company_id, $import_date)
    {
        $file = "database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_OEDReset_RESTART.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function oed_rest_tier_change_NEW_and_MISSING( $company_id, $import_date)
    {
        $file = "database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_OEDReset_NEW_and_MISSING.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function oed_rest_tier_change_NEW_and_UPDATE( $company_id, $import_date)
    {
        $file = "database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_OEDReset_NEW_and_UPDATE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }


    /**
     * new_assume_oldest_lifeplan_date_starting_month
     *
     * If we are processing our starting month, there is nothing to research.  Everything is NEW and
     * the oldest dates are the ones we have in hand.  Save those now.
     * @param $company_id
     * @param $import_date
     */
    public function new_assume_oldest_lifeplan_date_starting_month($company_id, $import_date)
    {
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_AssumeOldestLifePlanDate_StartingMonth.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date ),
        );
        ExecuteSQL( $this->db, $file, $vars );

    }


    /**
     * new_identify
     * Find all NEW items in the Compare table for this month.
     *
     * @param $company_id
     * @param $import_date
     * @return array
     */
    public function new_identify($company_id, $import_date)
    {
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareSELECT_NewItems.sql';

        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 0 ) return array();
        return $results;
    }


    /**
     * new_find_oldest_lifeplan_date
     *
     * Given a lifeplan key, find the oldest effective date over all tiers.
     * This includes an NEW items that are in the current import as well even
     * though they are not yet in the lock box.
     *
     * @param $company_id
     * @param $import_date
     * @param $key
     * @return string
     */
    public function new_find_oldest_lifeplan_date($company_id, $import_date, $key)
    {
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateSELECT_NewItems_OldestLifePlanDate.sql';
        $vars = array(

            GetArrayIntValue('life_id', $key),
            GetArrayIntValue('carrier_id', $key),
            GetArrayIntValue('plantype_id', $key),
            GetArrayIntValue('plan_id', $key),
            GetIntValue($company_id),
            GetStringValue( $import_date ),
            GetArrayIntValue('life_id', $key),
            GetArrayIntValue('carrier_id', $key),
            GetArrayIntValue('plantype_id', $key),
            GetArrayIntValue('plan_id', $key)
        );

        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 0 ) return array();
        return $results;
    }

    /**
     * new_save_oldest_lifeplan_date
     *
     * Now that we have calculated the oldest lifeplan effective date for the NEW
     * records this month, save it.
     *
     * @param $company_id
     * @param $import_date
     * @param $key
     * @param $oldest
     */
    public function new_save_oldest_lifeplan_date($company_id, $import_date, $key, $oldest_effective_date, $oldest_discovery_date)
    {
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_NewItem_OldestLifePlanDate.sql';

        $vars = array(
            GetStringValue( $oldest_effective_date ),
            GetStringValue( $oldest_discovery_date ),
            GetIntValue($company_id),
            GetStringValue( $import_date ),
            GetArrayIntValue('life_id', $key),
            GetArrayIntValue('carrier_id', $key),
            GetArrayIntValue('plantype_id', $key),
            GetArrayIntValue('plan_id', $key)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }

    /**
     * new_assume_oldest_lifeplan_date
     *
     * For all NEW records this month, compare the OldestLifePlanEffectiveDate column to the
     * Calculated-EffectiveDate column and make sure the calculated field has the oldest value
     * between the two.
     *
     * @param $company_id
     * @param $import_date
     */
    public function new_assume_oldest_lifeplan_date($company_id, $import_date)
    {
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_NewPlans_AssumeOldestLIfePlanDate.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date ),
        );
        ExecuteSQL( $this->db, $file, $vars );

    }

    /**
     * other_carry_forward_oldest_lifeplan_date
     *
     * Find all records in the compare table for this month that are not NEW.  Then choose the
     * oldest lifeplan date for the same lifeplan from last month and save the previous date
     * to the records for this month.
     *
     * @param $company_id
     * @param $import_date
     */
    public function other_carry_forward_oldest_lifeplan_date($company_id, $import_date)
    {
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_OtherItems_CarryForwardOldestLifePlanDate.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date ),
            GetIntValue($company_id),
            GetStringValue( $import_date ),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }


    /**
     * other_identify
     * Find all not NEW items in the Compare table for this month.
     *
     * @param $company_id
     * @param $import_date
     * @return array
     */
    public function other_identify($company_id, $import_date)
    {
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareSELECT_OtherItems.sql';

        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 0 ) return array();
        return $results;
    }


    /**
     * other_find_oldest_lifeplan_date
     *
     * Given a lifeplan key, find the oldest effective date over all tiers.
     * This includes an NEW items that are in the current import as well even
     * though they are not yet in the lock box.
     *
     * @param $company_id
     * @param $import_date
     * @param $key
     * @return string
     */
    public function other_find_oldest_lifeplan_date($company_id, $import_date, $key)
    {
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateSELECT_OtherItems_OldestLifePlanDate.sql';
        $vars = array(

            GetArrayIntValue('life_id', $key),
            GetArrayIntValue('carrier_id', $key),
            GetArrayIntValue('plantype_id', $key),
            GetArrayIntValue('plan_id', $key),
            GetIntValue($company_id),
            GetStringValue( $import_date ),
            GetArrayIntValue('life_id', $key),
            GetArrayIntValue('carrier_id', $key),
            GetArrayIntValue('plantype_id', $key),
            GetArrayIntValue('plan_id', $key)
        );

        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 0 ) return array();
        return $results;
    }


    /**
     * other_save_oldest_lifeplan_date
     *
     * Now that we have calculated the oldest lifeplan effective date for the not NEW
     * records this month, save it.
     *
     * @param $company_id
     * @param $import_date
     * @param $key
     * @param $oldest
     */
    public function other_save_oldest_lifeplan_date($company_id, $import_date, $key, $oldest_effective_date, $oldest_discovery_date)
    {
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_OtherItem_OldestLifePlanDate.sql';

        $vars = array(
            GetStringValue( $oldest_effective_date ),
            GetStringValue( $oldest_discovery_date ),
            GetIntValue($company_id),
            GetStringValue( $import_date ),
            GetArrayIntValue('life_id', $key),
            GetArrayIntValue('carrier_id', $key),
            GetArrayIntValue('plantype_id', $key),
            GetArrayIntValue('plan_id', $key)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

    /**
     * other_assume_oldest_lifeplan_date
     *
     * Once we have the OldestLifePlanEffectiveDate column filled in for all the
     * compare records for this month that are not NEW, update the Calcualted-EffectiveDate
     * column so that is contains the oldest value between the two fields.
     *
     * @param $company_id
     * @param $import_date
     */
    public function other_assume_oldest_lifeplan_date($company_id, $import_date)
    {
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_OtherItems_AssumeOldestLifePlanDate.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date ),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }



    public function insert_existing_rollback($company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateRollbackINSERT_Existing.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function insert_new_rollback($company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateRollbackINSERT_New.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function insert_missing_rollback($company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateRollbackINSERT_Missing.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        CopyFromInto( $this->db, $file, $vars );

    }
    public function insert_restart_rollback( $company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateRollbackINSERT_Restart.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function capture_existing_items($company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareINSERT_Existing.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        SelectIntoInsert( $this->db, $file, $vars );

    }
    public function rule1($start_date, $company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareINSERT_Rule1.sql';
        $vars = array(
            GetStringValue( $start_date ),
            GetStringValue( $start_date ),
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        CopyFromInto( $this->db, $file, $vars );

    }
    public function rule3($company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_Rule3.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function rule4($company_id, $import_date=null)
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        // First, log the changes you are about to make.
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_Rule4_Logging.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Next, make the changes.
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_Rule4.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function commit($company_id, $import_date=null)
    {
        // Add any new records to the vault.
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateINSERT.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Update exiting records in the vault.
        //  - EF Date, Lost Date, IsCoverageStartDate
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateUPDATE.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Update MISSING records in the vault.
        //  - Lost Date
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateUPDATE_Missing.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Update RESTART records in teh vault
        //  - EF Date, Lost Date, IsCoverageStartDate, Discovery Date
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateUPDATE_Restart.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function rollback_new_items( $company_id, $import_date=null )
    {
        // Add any new records to the vault.
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateROLLBACK_New.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function rollback_update_items($company_id, $import_date=null)
    {
        // Add any new records to the vault.
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateROLLBACK_Update.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function delete_rollback($company_id, $import_date=null)
    {
        // Add any new records to the vault.
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateRollbackDELETE.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function delete_compare($company_id, $import_date=null)
    {
        // Add any new records to the vault.
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareDELETE.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

    public function update_coverage_stop_date($company_id, $import_date=null)
    {
        // Add any new records to the vault.
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_CoverageStopped.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update_coverage_stop_date_returning($company_id, $import_date=null)
    {
        // Add any new records to the vault.
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_CoverageReturns.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update_coverage_restart( $company_id, $import_date=null )
    {
        // Add any new records to the vault.
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_Reset.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue( $import_date )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update_coverage_missing($company_id, $import_date = null)
    {

        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_CoverageMissing.sql';
        $vars = array(
            GetStringValue($import_date),
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function capture_coverage_restart($company_id, $import_date = null)
    {

        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareUPDATE_CoverageRestart.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function capture_coverage_missing($company_id, $import_date=null)
    {
        // Calculate the recent date, which is one month prior to the import date.
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        $recent_date = date("m/d/Y", strtotime("-1 month", strtotime($import_date)));
        if ( $recent_date === '' ) return;

        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateCompareINSERT_Missing.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($recent_date)
        );
        CopyFromInto( $this->db, $file, $vars );
    }
    public function delete_all_items_by_company( $company_id )
    {
        $file = 'database/sql/originaleffectivedate/LifeOriginalEffectiveDateDELETE_ByCompanyId.sql';
        $vars = array(
            GetIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

}
/* End of file GenerateOriginalEffectiveDateData_model.php */
/* Location: ./system/application/models/GenerateOriginalEffectiveDateData_model.php */
