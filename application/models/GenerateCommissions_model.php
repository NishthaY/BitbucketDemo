<?php

class GenerateCommissions_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    function save_commissions_with_ignored_gaps($company_id, $import_date)
    {
        // Find all the lives this month that last month had a record that was IGNORED.
        // This means the user supplied a record, but there was no coverage.  We need
        // to know the last time it was part of the commission data.  We need to investigate
        // this.  Sash it in the worker table for further review.
        $file = "database/sql/commissions/CompanyCommissionWorkerINSERT_CommissionsWithIgnoredGap.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        CopyFromInto($this->db, $file, $vars);
    }
    function save_commissions_with_gaps($company_id, $import_date)
    {
        // Find all lives that have a gap in their commission data and
        // stash them the the CompanyCommissionWorker table.
        // i.e.  They did not have a commission record last month.
        $file = "database/sql/commissions/CompanyCommissionWorkerINSERT_CommissionsWithGap.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        CopyFromInto($this->db, $file, $vars);
    }
    function select_commission_worker_records($company_id, $import_date)
    {
        // Grab each of the items so we can manually calculate the gap in
        // coverage.
        $file = "database/sql/commissions/CompanyCommissionWorkerSELECT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        $results = GetDBResults($this->db, $file, $vars);
        return $results;
    }
    function find_most_recent_lifeplan_from_companycommissiondata($company_id, $import_date, $item)
    {

        $replacefor = array();
        $replacefor['{IMPORT_DATE}'] = $import_date;

        // Find the most recent record, other than the one for this import.
        // Get that records import date and calculate the gap, in months between now and then.
        $file = "database/sql/commissions/CompanyCommissionDataSELECT_MostRecent.sql";
        $vars = array(
            GetIntValue($company_id),
            GetArrayIntValue("LifeId", $item),
            GetArrayIntValue("CarrierId", $item),
            GetArrayIntValue("PlanTypeId", $item),
            GetArrayIntValue("PlanId", $item),
            GetStringValue($import_date)
        );
        $results = GetDBResults($this->db, $file, $vars, $replacefor);
        if ( empty($results) ) return array();
        return $results[0];
    }
    function delete_individual_companycommissiondata($company_id, $import_date, $item)
    {
        $file = "database/sql/commissions/CompanyCommissionDataCompareDelete_Individual.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetArrayIntValue("LifeId", $item),
            GetArrayIntValue("CarrierId", $item),
            GetArrayIntValue("PlanTypeId", $item),
            GetArrayIntValue("PlanId", $item),
            GetArrayIntValue("CoverageTierId", $item),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function insert_individual_companycommissiondata($company_id, $import_date, $coverage_gap_offset, $last_import_date, $item)
    {
        $file = "database/sql/commissions/CompanyCommissionDataCompareINSERT_Individual.sql";
        $vars = array(
            GetStringValue($coverage_gap_offset),
            GetStringValue($last_import_date),
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetArrayIntValue("LifeId", $item),
            GetArrayIntValue("CarrierId", $item),
            GetArrayIntValue("PlanTypeId", $item),
            GetArrayIntValue("PlanId", $item),
            GetArrayIntValue("CoverageTierId", $item),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function validate_rollback($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionValidateDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function validate_capture_commission_records($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionValidateINSERT_CommissionablePremium.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        CopyFromInto($this->db, $file, $vars);
    }
    function validate_capture_billing_records($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionWorkerINSERT_BillingMonthlyCost.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        CopyFromInto($this->db, $file, $vars);
    }
    function validate_capture_billed_only_records($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionValidateINSERT_BilledOnly.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        CopyFromInto($this->db, $file, $vars);
    }
    function validate_update_commission_records_with_billing_data($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionValidateUPDATE_MonthlyCost.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function validate_mark_records_that_passed_validation($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionValidateUPDATE_IsValid.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function validate_remove_valid_records($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionValidateDELETE_RemoveValidatedRecords.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL($this->db, $file, $vars);
    }


    function remove_zero_dollar_warnings($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionWarningDELETE_ZeroDollar.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function rollback_report_review_warnings_created_by_commission ( $company_id, $import_date )
    {
        $file = "database/sql/commissions/ReportReviewWarningsDELETE_CommissionWarnings.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function get_stack_total( $company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id )
    {
        $file = "database/sql/commissions/CompanyCommissionSELECT_StackTotal.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( count($results) !== 1 ) throw new Exception("Unable to total up current stack.");
        $results = $results[0];
        return GetArrayFloatValue("StackTotal", $results);
    }
    function delete_csd_change( $company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $effective_dt)
    {
        $file = "database/sql/commissions/CompanyCommissionDELETE_StacksGreaterThanCED.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id),
            GetStringValue($effective_dt)

        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function select_most_recent_life_plan_stack( $company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id )
    {
        $file = "database/sql/commissions/CompanyCommissionSELECT_MostRecentRecord.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id),
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        if ( count($results) > 1) throw new Exception( "Found too many results when looking for one.");
        return $results[0];
    }
    function update_commission_effective_date_on_record( $record_id, $effective_dt)
    {
        $file = "database/sql/commissions/CompanyCommissionUPDATE_CommissionEffectiveDateById.sql";
        $vars = array(
            GetStringValue($effective_dt),
            GetIntValue($record_id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function find_csd_changes($company_id, $import_date)
    {
        $this->clear_worker_table($company_id, $import_date);

        // Capture the records that had just a Coverage Start Date change this import.
        $file = "database/sql/commissions/CompanyCommissionWorkerINSERT_StackedCoverageStartDateChange.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        CopyFromInto($this->db, $file, $vars);

        $file = 'select * from "CompanyCommissionWorker" where "CompanyId" = ? and "ImportDate" = ?';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }
    function mark_reset_record($company_id, $import_date)
    {

        // Set the Reset Record to FALSE for this life/plan
        $file = "database/sql/commissions/CompanyCommissionUPDATE_RecalculateStack_ResetRecord_Step1.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);

        // Set the Reset Record to TRUE on the oldest life/plan
        $file = "database/sql/commissions/CompanyCommissionUPDATE_RecalculateStack_ResetRecord_Step2.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);

    }
    function mark_reset_record2($company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id)
    {
        // Set the Reset Record to FALSE for this life/plan
        $file = "database/sql/commissions/CompanyCommissionUPDATE_RecalculateStack2_ResetRecord_Step1.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id)
        );
        ExecuteSQL($this->db, $file, $vars);


        // Find the oldest record on this life/plan
        $file = "database/sql/commissions/CompanyCommissionUPDATE_RecalculateStack2_ResetRecord_Step2.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if( empty($results) ) return;
        if( count($results) != 1 ) return;
        $results = $results[0];
        $record_id = GetArrayStringValue('Id', $results);

        // Update the reset record by Id.
        $file = "database/sql/commissions/CompanyCommissionUPDATE_RecalculateStack2_ResetRecord_Step3.sql";
        $vars = array(
            GetIntValue($record_id)
        );
        ExecuteSQL($this->db, $file, $vars);

    }
    function delete_commission_research($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionLifeResearchDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function insert_commission_research($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionLifeResearchINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        CopyFromInto($this->db, $file, $vars);
    }

    function update_commission_research_missing($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionLifeResearchUPDATE_Missing.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function delete_commission_life($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionLifeDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function insert_commission_life($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionLifeINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        CopyFromInto($this->db, $file, $vars);
    }

    function update_commission_life_missing($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionLifeUPDATE_Missing.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }

    function retro_window_start($company_id, $import_date, $key )
    {
        $file = "database/sql/commissions/CompanyPlanTypeSELECT_RetroWindowStart.sql";
        $vars = array(
            GetStringValue($import_date),
            GetIntValue($company_id),
            GetArrayIntValue("CarrierId", $key),
            GetArrayIntValue("PlanTypeId", $key),
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( count($results) != 1 ) throw new Exception("Found too many results when looking for the retro start window");
        $results = $results[0];
        return GetArrayStringValue("RetroWindowStart", $results);
    }
    function is_coverage_start_date_correction($company_id, $import_date, $key )
    {
        $file = "database/sql/commissions/RetroDataBOOLEAN_IsCoverageStartDateCorrection.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetArrayIntValue("LifeId", $key),
            GetArrayIntValue("CarrierId", $key),
            GetArrayIntValue("PlanTypeId", $key),
            GetArrayIntValue("PlanId", $key),
            GetArrayIntValue("CoverageTierId", $key)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( empty($results) ) return false;
        if ( count($results) > 1 ) throw new Exception("Found too many results when doing a boolean check");
        $results = $results[0];
        if ( GetArrayStringValue("IsCoverageStartDateCorrection", $results) === 't' ) return true;
        if ( GetArrayStringValue("IsCoverageStartDateCorrection", $results) === 'f' ) return false;
        throw new Exception("Found unexpected results when doing a boolean check");
    }
    function publish_report_warnings($company_id, $import_date)
    {
        $file = "database/sql/commissions/ReportReviewWarningINSERT_Commissions.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function company_commission_warning_insert( $company_id, $import_date, $key, $description, $internal=true )
    {
        $file = "database/sql/commissions/CompanyCommissionWarningINSERT.sql";
        $vars = array(
            GetStringValue($description),
            $internal ? 't' : 'f',
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetArrayIntValue("LifeId", $key),
            GetArrayIntValue("CarrierId", $key),
            GetArrayIntValue("PlanTypeId", $key),
            GetArrayIntValue("PlanId", $key),
            GetArrayIntValue("CoverageTierId", $key)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function company_commission_warning_save_WARNING( $company_id, $import_date )
    {
        $file = "database/sql/commissions/CompanyCommissionWarningINSERT_ByCodeWARNING.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function company_commission_data_compare_tier_changed( $company_id, $import_date )
    {
        // Note:  We are setting the tier change flag based on the information
        // we have in the retro engine.

        // Clear out the worker table.
        $this->clear_worker_table($company_id, $import_date);

        // Identify the records that changing tiers this month and save their
        // keys into the worker table.
        $file = "database/sql/commissions/CompanyCommissionWorkerINSERT_TierChanged.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        CopyFromInto($this->db, $file, $vars);

        // Update the CompanyCommissionCompare table with the records we identified
        // as having a tier change this month.
        $file = "database/sql/commissions/CompanyCommissionDataCompareUPDATE_TierChanged.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL($this->db, $file, $vars);

    }
    function delete_company_commission_newer_stacks( $company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $coverage_start_date )
    {
        $file = 'database/sql/commissions/CompanyCommissionDELETE_ByLifePlan_NewStacks.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id),
            GetStringValue($coverage_start_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function delete_company_commission_warning( $company_id, $import_date )
    {
        $file = "database/sql/commissions/CompanyCommissionWarningDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function insert_commission_summary($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionSummaryINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        CopyFromInto($this->db, $file, $vars);
    }
    function delete_commission_summary($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionSummaryDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function insert_commission_data($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionDataINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        CopyFromInto($this->db, $file, $vars);
    }
    function delete_company_commission_data($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionDataDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }

    function insert_company_commission_data_compare($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionDataCompareINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        CopyFromInto($this->db, $file, $vars);
    }
    function delete_company_commission_data_compare($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionDataCompareDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function update_company_commission_data_compare_Ignore($company_id, $import_date, $code, $description)
    {
        // Clear out the worker table.
        $this->clear_worker_table($company_id, $import_date);

        // Capture the indexes for each record that will be flagged as IGNORE for this
        // import.  We will place the indexes into the Worker table.
        $file = "database/sql/commissions/CompanyCommissionWorkerINSERT_Code_Ignore.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        CopyFromInto($this->db, $file, $vars);

        // Update the Compare table using the keys stored in the Worker table.
        $file = "database/sql/commissions/CompanyCommissionDataCompareUPDATE_Code_Ignore.sql";
        $vars = array(
            GetStringValue($code),
            GetStringValue($description),
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);

    }
    function clear_worker_table($company_id, $import_date)
    {
        // Delete everything from the CompanyCommissionWorkder by company and import date.
        $file = 'database/sql/commissions/CompanyCommissionWorkerDELETE.sql';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function update_company_commission_data_compare_WarnOnMultipleTiersPerLifePlan( $company_id, $import_date )
    {

        // Clear out the worker table.
        $this->clear_worker_table($company_id, $import_date);

        // Find all the records we want to tag and place their keys in the worker table.
        $file = "database/sql/commissions/CompanyCommissionWorkerINSERT_Code_WarningOnMultipleTiers.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        CopyFromInto($this->db, $file, $vars);

        // Update the compare table.
        $file = "database/sql/commissions/CompanyCommissionDataCompareUPDATE_Code_WarningOnMultipleTiers.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);


    }
    function update_company_commission_data_compare_OriginalEffectiveDateHasReset($company_id, $import_date, $code, $description)
    {
        $file = "database/sql/commissions/CompanyCommissionDataCompareUPDATE_Code_OriginalEffectiveDateHasReset.sql";
        $vars = array(
            GetStringValue($code),
            GetStringValue($description),
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function update_commission_data_compare_VolumeOrTierChangeWithCostIncrease($company_id, $import_date, $code, $description)
    {
        $file = "database/sql/commissions/CompanyCommissionDataCompareUPDATE_Code_VolumeOrTierChangeWithCostIncrease.sql";
        $vars = array(
            GetStringValue($code),
            GetStringValue($description),
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function update_company_commission_data_compare_VolumeOrTierChangeWithCostDecrease($company_id, $import_date, $code, $description)
    {
        $file = "database/sql/commissions/CompanyCommissionDataCompareUPDATE_Code_VolumeOrTierChangeWithCostDecrease.sql";
        $vars = array(
            GetStringValue($code),
            GetStringValue($description),
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }

    function update_company_commission_data_compare_VolumeDecreasedWithCostIncrease($company_id, $import_date, $code, $description)
    {
        $file = "database/sql/commissions/CompanyCommissionDataCompareUPDATE_Code_VolumeDecreaseWithCostIncrease.sql";
        $vars = array(
            GetStringValue($code),
            GetStringValue($description),
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function update_company_commission_data_compare_VolumeDecreaseWithCostDecrease($company_id, $import_date, $code, $description)
    {
        $file = "database/sql/commissions/CompanyCommissionDataCompareUPDATE_Code_VolumeDecreaseWithCostDecrease.sql";
        $vars = array(
            GetStringValue($code),
            GetStringValue($import_date),
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function insert_company_commission_records_RESET($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionINSERT_ResetRecords.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function select_tagged_commissionable_records($company_id, $import_date)
    {
        $file = "database/sql/commissions/CompanyCommissionDataCompareSELECT_Tagged.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }
    function insert_company_commission_life_plan($company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $effective_date, $commisionable_premium, $reset_record='f')
    {
        $file = "database/sql/commissions/CompanyCommissionINSERT_LifePlan.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id),
            GetStringValue($effective_date),
            GetFloatValue($commisionable_premium),
            GetStringValue($reset_record)

        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function delete_company_commission( $company_id, $import_date )
    {
        $file = 'delete from "CompanyCommission" where "CompanyId" = ? and "ImportDate" = ?';
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function select_distinct_leftover_coverage_gaps( $company_id, $import_date )
    {
        $file = "database/sql/commissions/CompanyCommissionDataCompareSELECT_LeftoverCoverageGaps.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }
    function copy_leftover_company_commission_life_plan( $company_id, $import_date, $offset )
    {
        $replacefor = array();
        $replacefor['{OFFSET}'] = $offset;

        $file = "database/sql/commissions/CompanyCommissionINSERT_NonActionableRecords.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($offset)
        );
        CopyFromInto($this->db, $file, $vars, $replacefor);
    }
    function copy_previous_company_commission_life_plan( $company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $offset='-1' )
    {

        $replacefor = array();
        $replacefor['{OFFSET}'] = $offset;

        $file = "database/sql/commissions/CompanyCommissionINSERT_PreviousLifePlanRecords.sql";
        $vars = array(
            GetStringValue($import_date),
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id)
        );
        CopyFromInto($this->db, $file, $vars, $replacefor);
    }
    function selet_total_commisionable_premium($company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $offset="-1")
    {
        $replacefor = array();
        $replacefor['{OFFSET}'] = $offset;

        $file = "database/sql/commissions/CompanyCommissionSELECT_TotalCommissionPremium_ByLifePlan.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id)
        );
        $results = GetDBResults($this->db, $file, $vars, $replacefor);
        if ( empty($results) ) return array();
        return GetArrayFloatValue("TotalCommissionablePremium", $results[0]);
    }
    function select_previous_company_commission_life_plan( $company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $offset = "-1" )
    {
        $replacefor = array();
        $replacefor['{OFFSET}'] = $offset;

        $file = "database/sql/commissions/CompanyCommissionSELECT_PreviousLifePlanRecords.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id)
        );
        $results = GetDBResults($this->db, $file, $vars, $replacefor);
        if ( empty($results) ) return array();
        return $results;
    }
    function select_company_commission_life_plan( $company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $order="asc" )
    {
        // Allow the user to change the order.
        $replacefor = array();
        $replacefor['{ORDER}'] = $order;

        $file = "database/sql/commissions/CompanyCommissionSELECT_CurrentLifePlanRecords.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($life_id),
            GetIntValue($carrier_id),
            GetIntValue($plantype_id),
            GetIntValue($plan_id)
        );
        $results = GetDBResults($this->db, $file, $vars, $replacefor);
        if ( empty($results) ) return array();
        return $results;
    }
    function increase_company_commission_life_plan($company_id, $import_date, $id, $diff)
    {
        $file = 'database/sql/commissions/CompanyCommissionUPDATE_IncreaseCommissionablePremium_ById.sql';
        $vars = array(
            GetFloatValue($diff),
            GetIntValue($id),
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function select_commissions_by_lifeplan($life_id, $plan_id)
    {
        $file = "database/sql/commissions/CompanyCommissionSELECT_ByLife_ByPlanId.sql";
        $vars = array(
            GetIntValue($life_id),
            GetIntValue($plan_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }

}
/* End of file GenerateCommissions_model.php */
/* Location: ./system/application/models/GenerateCommissions_model.php */
