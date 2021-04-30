<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateOriginalEffectiveDateData extends A2PLibrary
{

    function __construct( $debug=false )
    {
        parent::__construct($debug);
    }

    /**
     * REQUIREMENTS
     * Taken from Tello on 11/10/2017
     *
     * For purposes of commission tracking, an original effective date is needed that doesn’t update with annual renewals (as coverage start date sometimes does).  The problem is that not all systems provide (or even store) this date.
     *
     * An Original Effective Date field was added to the A2P input fields in mid-2017.
     *
     * For data files that include this field:
     * If the OED is included on the data file, we use it.
     *
     * For data files that don’t include this field:
     * If the OED is not included on the data file, the A2P system takes the Coverage Start Date field and locks it in place.  This date does not change if the CSD changes unless any of the following circumstances occur:
     *
     * Coverage ends on the record, either via termination or a gap in coverage, and then later resumes with a new CSD representing a gap in coverage.  In this case the OED should be set to the new CSD.
     * In the initial retro period after the record is first seen, the CSD is retroactively changed.   In this case the OED should be set to the new CSD.
     *
     * @param $company_id
     * @throws Exception
     */
    public function execute($company_id, $user_id=null, $import_date=null )
    {
        try
        {
            parent::execute($company_id);

            // What is our import date?
            if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
            if ( $import_date == "" ) throw new Exception("Invalid import_date. How is that possible?");

            // What is our starting date?
            $starting_date = $this->_getStartingDate($company_id);
            if ( $starting_date === '' ) throw new Exception("Unable to find starting date.");

            $this->debug(" StartingDate: [{$starting_date}]");
            $this->debug(" ImportDate: [{$import_date}]");
            $this->debug(" CompanyId: [{$company_id}]");

            // FEATURE CHECK
            // Before we start, we need to know if feature is enabled.
            $enabled = $this->isEnabled($company_id);
            if ( ! $enabled ) return;

            // ROLLBACK - In the case where the user makes changes to their life data during the review process,
            // we must rollback any previous work we have done for this month before we begin.
            $this->rollback($company_id, $import_date);

            // EXISTING - Capture them now.
            // Compare the latest import to our vault and create "EXISTING" records in the compare table.
            $this->debug(" Capture existing records for compare");
            $this->ci->GenerateOriginalEffectiveDateData_model->capture_existing_items($company_id, $import_date);
            $this->timer(" Capture existing records for compare");

            // MISSING - Capture them now.
            // Compare the latest import to our vault and create "MISSING" records in the compare table.
            $this->debug(" Capturing records that went missing this month.");
            $this->ci->GenerateOriginalEffectiveDateData_model->capture_coverage_missing($company_id, $import_date);
            $this->timer(" Capturing records that went missing this month.");

            // RESTART - Capture them now.
            // Look at the EXISTING records and mark them as RESTART if they have a coverage start date beyond our lost date.
            $this->debug(" Capturing records that that need reset this month.");
            $this->ci->GenerateOriginalEffectiveDateData_model->capture_coverage_restart($company_id, $import_date);
            $this->timer(" Capturing records that that need reset this month.");

            // EXISTING -> UPDATE
            // Update the LostDate for all EXISTING records that have now ended.
            $this->debug(" Setting the LostDate on all coverage records that have ended.");
            $this->ci->GenerateOriginalEffectiveDateData_model->update_coverage_stop_date($company_id, $import_date);
            $this->timer(" Setting the LostDate on all coverage records that have ended.");

            // EXISTING -> UPDATE
            // Update the LostDate for all EXISTING records that have returned!
            $this->debug(" Clearing the LostDate on all coverage records that have returned.");
            $this->ci->GenerateOriginalEffectiveDateData_model->update_coverage_stop_date_returning($company_id, $import_date);
            $this->timer(" Clearing the LostDate on all coverage records that have returned.");


            // MISING - Update the LostDate for all MISSING records.
            $this->debug(" Setting the LostDate on all coverage records that went missing.");
            $this->ci->GenerateOriginalEffectiveDateData_model->update_coverage_missing($company_id, $import_date);
            $this->timer(" Setting the LostDate on all coverage records that went missing.");

            // NEW - Capture them now.
            // Identify any new coverage for this month and add them to the compare table as NEW.
            // We pass in the starting_date because we only use the OED date from the import on the first month import.
            // After that, we always use CSD.
            $this->debug(" Rule #1: Calculating EffectiveDate for all new items.");
            $this->ci->GenerateOriginalEffectiveDateData_model->rule1($starting_date, $company_id, $import_date);
            $this->timer(" Rule #1: Calculating EffectiveDate for all new items.");

            // FEATURE: OED Variant
            // When enabled, we will track the OldestLifePlanEffectiveDate and use it to update the
            // Calculated-EffectiveDate column in order to keep the oldest effective date in play over
            // all tiers in the same life plan.
            $this->debug(" ASSUME: Assuming the oldest lifeplan effective date.");
            $this->_assume_oldest_lifeplan_effective_date($company_id, $import_date, $starting_date);
            $this->timer(" ASSUME: Assuming the oldest lifeplan effective date.");

            // We no longer do RULE #2.  That rule stated, if the OED is provided to us on import, we always use it.
            // We don't do that anymore.  We only use the OED from the import on the very first import.

            // UPDATE,EXISTING records.
            // If we have an Original Effective Date, use that as the Calculated-EffectiveDate
            //$this->debug(" Rule #2: If we have an OriginalEffectiveDate, use it.");
            //$this->ci->GenerateOriginalEffectiveDateData_model->rule2($company_id, $import_date);
            //$this->timer(" Rule #2: If we have an OriginalEffectiveDate, use it.");

            // UPDATE,EXISTING -> UPDATE
            // Allow the coverage start date to change to a future date if a few things are true.
            //  - The Effective Date in the lock box must be sourced from the CSD import field.
            //  - We are still in our change window.
            //  - CSD is moving forwards into the future.
            $this->debug(" Rule #3: If sourced from CoverageStartDate, allow limited updates");
            $this->ci->GenerateOriginalEffectiveDateData_model->rule3($company_id, $import_date);
            $this->timer(" Rule #3: If sourced from CoverageStartDate, allow limited updates");

            // UPDATE,EXISTING -> UPDATE
            // Allow the coverage start date to change to a past date if a few things are true.
            //  - The Effective Date in the lock box must be sourced from the CSD import field.
            //  - CSD is moving backwards into a date earlier than our current Effective date in the vault.
            $this->debug(" Rule #4: If sourced from CoverageStartDate, allow EF date to move backwards.");
            $this->ci->GenerateOriginalEffectiveDateData_model->rule4($company_id, $import_date);
            $this->timer(" Rule #4: If sourced from CoverageStartDate, allow EF date to move backwards.");

            // RESTART records
            // For all the RESTART records, update the compare table with the new data.
            $this->debug(" Resetting data for items that are restarting.");
            $this->ci->GenerateOriginalEffectiveDateData_model->update_coverage_restart($company_id, $import_date);
            $this->timer(" Resetting data for items that are restarting.");

            // OED RESET
            // We want to set a flag on the compare record that indicates if we consider this to be
            // an OEDReset event.
            $this->_oed_reset($company_id, $import_date, $starting_date);

            $this->debug(" Capture rollback data before we commit.");
            $this->ci->GenerateOriginalEffectiveDateData_model->insert_new_rollback($company_id, $import_date);
            $this->ci->GenerateOriginalEffectiveDateData_model->insert_existing_rollback($company_id, $import_date);
            $this->ci->GenerateOriginalEffectiveDateData_model->insert_missing_rollback($company_id, $import_date);
            $this->ci->GenerateOriginalEffectiveDateData_model->insert_restart_rollback($company_id, $import_date);
            $this->timer(" Capture rollback data before we commit.");

            $this->debug(" commiting.");
            $this->ci->GenerateOriginalEffectiveDateData_model->commit($company_id, $import_date);
            $this->timer(" commiting.");

            // Report Warnings.
            $this->debug(" WARNINGS: Capture all report review warnings.");
            $this->ci->GenerateOriginalEffectiveDateData_model->remove_zero_dollar_warnings($company_id, $import_date);
            $this->ci->GenerateOriginalEffectiveDateData_model->publish_report_warnings($company_id, $import_date);
            $this->timer(" WARNINGS: Capture all report review warnings.");


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
        $this->debug(" Rolling back NEW items.");
        $this->ci->GenerateOriginalEffectiveDateData_model->rollback_new_items($company_id, $import_date);

        // Rollback any "UPDATE" items that were generated for the specified import date.
        $this->debug(" Rolling back UPDATE items.");
        $this->ci->GenerateOriginalEffectiveDateData_model->rollback_update_items($company_id, $import_date);

        // Remove the compare files that were generated.
        $this->debug(" Deleting records from the compare table.");
        $this->ci->GenerateOriginalEffectiveDateData_model->delete_compare($company_id, $import_date);

        // Remove the reollback files that were generated.
        $this->debug(" Deleting records from the rollback table.");
        $this->ci->GenerateOriginalEffectiveDateData_model->delete_rollback($company_id, $import_date);

        // Remove the warning records that were placed into the report review warning table.
        $this->debug(" Deleting records from the report review warning table.");
        $this->ci->GenerateOriginalEffectiveDateData_model->rollback_report_review_warnings_created_by_oed_process($company_id, $import_date);

        // Remove the warnings we placed in the OED warning table.
        $this->debug(" Deleting warning messages.");
        $this->ci->GenerateOriginalEffectiveDateData_model->rolback_warnings($company_id, $import_date);


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

        $enabled = false;

        // COMMISSION_TRACKING
        // If the commission tracking feature is enabled, then OED calculation is required.
        if ( ! $enabled )
        {
            $enabled = $this->ci->Feature_model->is_feature_enabled($company_id, 'COMMISSION_TRACKING');
        }

        // TRANSAMERICA_ACTUARIAL_REPORT
        // If the Transamerica actuarial report feature is enabled, then OED calculation is required.
        if ( ! $enabled )
        {
            $enabled = $this->ci->Feature_model->is_feature_enabled($company_id, 'TRANSAMERICA_ACTUARIAL_REPORT');
        }

        return $enabled;
    }


    private function _oed_reset($company_id, $import_date, $starting_date)
    {

        // Calculate the OED Reset flag based on the commission effective date type.
        $commission_effective_date_type = GetCommissionEffectiveDateType($company_id);

        if ($commission_effective_date_type !== OLDEST_LIFE_PLAN_EFFECTIVE_DATE )
        {
            // RECENT_TIER_CHANGE

            // Every NEW event is considered an OEDReset event.
            $this->ci->GenerateOriginalEffectiveDateData_model->oed_reset_new($company_id, $import_date);
        }
        else
        {
            // OLDEST_LIFE_PLAN_EFFECTIVE_DATE

            // The OED Tier Change feature is in effect.  This means that tier changes are NOT to be
            // flagged as on OEDReset unless there is a gap in coverage.  Mark only those now.

            // NEW ( No Lockbox Records )
            // If this is a new record and we do not have a corresponding lifeplan in the lockbox
            // for the life, then it brand new and will be flagged as an OEDReset.
            $this->ci->GenerateOriginalEffectiveDateData_model->oed_reset_new_exclude_tier_changes($company_id, $import_date, $starting_date);

            // NEW & MISSING
            // If this month we have a NEW and MISSING record for a given life plan we will check for a
            // gap in coverage between these records.  If there is a gap, it will be flagged as an OEDReset
            $this->ci->GenerateOriginalEffectiveDateData_model->oed_rest_tier_change_NEW_and_MISSING($company_id, $import_date);

            // NEW & UPDATE
            // If this month we have both NEW and UPDATE records for given life plan, we will check for a
            // gap in coverage between these records.  If there is a gap, it will be flagged as an OEDReset.
            $this->ci->GenerateOriginalEffectiveDateData_model->oed_rest_tier_change_NEW_and_UPDATE($company_id, $import_date);

            // RESTART
            // If we have a record marked as RESTART, you can think of this as NEW coverage that is starting that
            // has a gap in the coverage and just happens to be the same coverage tier we had before.
            $this->ci->GenerateOriginalEffectiveDateData_model->oed_rest_tier_change_RESTART($company_id, $import_date);

            // NEW ( With Lockbox Records )
            // Find records that are NEW this month, that are not yet marked as OEDReset.  Check to see if those items
            // are stand alone NEW items.  Look back to the Lockbox and get the lost date for the LifePlan.  If there is
            // a gap in coverage, this is an OED event.  Mark it as such.
            $results = $this->ci->GenerateOriginalEffectiveDateData_model->oed_rest_tier_change_historical_NEW($company_id, $import_date);
            foreach($results as $item)
            {
                $has_active_life_plan = $this->ci->GenerateOriginalEffectiveDateData_model->has_active_life_plan_in_lockbox($item);
                if ( ! $has_active_life_plan )
                {
                    $coverage_start_date = GetArrayStringValue("CoverageStartDate", $item);
                    $has_gap = $this->ci->GenerateOriginalEffectiveDateData_model->has_life_plan_coverage_gap($coverage_start_date, $item);
                    if ( $has_gap )
                    {
                        $description = "Calculated the effective date based on import data because this is a new item and there is a gap in coverage. ( NEW wHistoricalGap )";
                        $this->ci->GenerateOriginalEffectiveDateData_model->compare_mark_record_oed_reset($item, $import_date, $coverage_start_date, $description);
                    }
                }
            }

        }
    }

    /**
     * _assume_oldest_lifeplan_effective_date
     *
     * Originally, the OED process would treat a coverage tier change as something
     * that would result in a new OED value.  However, some clients want to always
     * assume the oldest date for a given life-plan for the OED.
     *
     * This can be accomplished by keeping track of the OldestLifePlanEffectiveDate
     * as we process files each month.  This function will do that, but it will only
     * do that if the cooresponding feature is enabled.
     *
     * @param $company_id
     * @param $import_date
     */
    private function _assume_oldest_lifeplan_effective_date( $company_id, $import_date, $starting_date)
    {
        // FEATURE CHECK
        // Before we start, we need to see what type of commission effective date logic we are
        // dealing with.  We only need to track the oldest life plan effective date if that is how the
        // commission tracking feature is enabled.
        $commission_effective_date_type = GetCommissionEffectiveDateType($company_id);
        if ( $commission_effective_date_type != OLDEST_LIFE_PLAN_EFFECTIVE_DATE ) return;

        // FIRST MONTH
        if ( strtotime($import_date) == strtotime($starting_date) )
        {
            $this->debug(" ASSUME: Starting Month!  No need to calculate the oldest effective date on life/plan.");
            $this->ci->GenerateOriginalEffectiveDateData_model->new_assume_oldest_lifeplan_date_starting_month($company_id, $import_date);

            // That's it.  Nothing else to do.
            return;
        }



        // IDENTIFY - NEW
        // Find all NEW items for this import.
        $this->debug(" ASSUME: Finding new items in this import.");
        $tier_changes = $this->ci->GenerateOriginalEffectiveDateData_model->new_identify($company_id, $import_date);

        // RESEARCH - NEW
        // Each new item needs investigated so we can capture the OLDEST life plan effective date
        // between other LifePlan EffectiveDates and the current Coverage Start Date.
        $this->debug(" ASSUME: Investigating individual NEW items.");
        $this->debug(" ASSUME: Found " . count($tier_changes) . " new items to investigate individually.");
        foreach($tier_changes as $tier_change) {
            $key = array();
            $key['life_id'] = getArrayIntValue('LifeId', $tier_change);
            $key['carrier_id'] = getArrayIntValue('CarrierId', $tier_change);
            $key['plantype_id'] = getArrayIntValue('PlanTypeId', $tier_change);
            $key['plan_id'] = getArrayIntValue('PlanId', $tier_change);

            $results = $this->ci->GenerateOriginalEffectiveDateData_model->new_find_oldest_lifeplan_date($company_id, $import_date, $key);
            if ( ! empty($results) && count($results) === 1 )
            {
                $results = $results[0];
                $oldest_effective_date = GetArrayStringValue("OldestLifePlanEffectiveDate", $results);
                $oldest_discovery_date = GetArrayStringValue("OldestLifePlanDiscoveryDate", $results);
                $this->ci->GenerateOriginalEffectiveDateData_model->new_save_oldest_lifeplan_date($company_id, $import_date, $key, $oldest_effective_date, $oldest_discovery_date);
            }

        }

        // CALCULATE - NEW
        // Update the Calculated-EffectiveDate to be the OldestLifePlanEffectiveDate
        $this->debug(" ASSUME: Updating the Calculated-Effective date.");
        $this->ci->GenerateOriginalEffectiveDateData_model->new_assume_oldest_lifeplan_date($company_id, $import_date);


        // CARRY FORWARD - OTHER
        // NOTE: We do this so there will be less steps below in the identify and research steps.
        // For records that are not NEW, match them up with previous records and carry the oldest life plan
        // effective date forward.
        $this->debug(" ASSUME: Carrying forward oldest LifePlan dates.");
        $this->ci->GenerateOriginalEffectiveDateData_model->other_carry_forward_oldest_lifeplan_date($company_id, $import_date);

        // IDENTIFY - OTHER
        // Find all not NEW items for this import.
        $this->debug(" ASSUME: Finding other items. ( not new )");
        $tier_changes = $this->ci->GenerateOriginalEffectiveDateData_model->other_identify($company_id, $import_date);

        // RESEARCH - OTHER
        // Each other item needs investigated so we can capture the OLDEST life plan effective date
        // between other LifePlan EffectiveDates and the current Coverage Start Date.
        $this->debug(" ASSUME: Investigating individual other items.");
        $this->debug(" ASSUME: Found " . count($tier_changes) . " other items to investigate individually.");
        foreach($tier_changes as $tier_change) {
            $key = array();
            $key['life_id'] = getArrayIntValue('LifeId', $tier_change);
            $key['carrier_id'] = getArrayIntValue('CarrierId', $tier_change);
            $key['plantype_id'] = getArrayIntValue('PlanTypeId', $tier_change);
            $key['plan_id'] = getArrayIntValue('PlanId', $tier_change);

            $results = $this->ci->GenerateOriginalEffectiveDateData_model->other_find_oldest_lifeplan_date($company_id, $import_date, $key);
            if ( ! empty($results) && count($results) === 1 )
            {
                $results = $results[0];
                $oldest_effective_date = GetArrayStringValue("OldestLifePlanEffectiveDate", $results);
                $oldest_discovery_date = GetArrayStringValue("OldestLifePlanDiscoveryDate", $results);
                $this->ci->GenerateOriginalEffectiveDateData_model->other_save_oldest_lifeplan_date($company_id, $import_date, $key, $oldest_effective_date, $oldest_discovery_date);

            }
        }

        // CALCULATE - OTHER
        // Now that the records that are not NEW have the oldest life plan effective date filled in
        // push that value into the Calculated-EffectiveDate field if it's older than the current
        // coverage start date.
        $this->debug(" ASSUME: Updating the Calculated-Effective date for OTHER items.");
        $this->ci->GenerateOriginalEffectiveDateData_model->other_assume_oldest_lifeplan_date($company_id, $import_date);


    }



    private function _getStartingDate($company_id)
    {
        $month = $this->ci->Company_model->get_company_preference( $company_id, "starting_date", "month" );
        $month = getArrayStringValue("value", $month);
        if ( $month == "" ) return "";

        $year = $this->ci->Company_model->get_company_preference( $company_id, "starting_date", "year" );
        $year = getArrayStringValue("value", $year);
        if ( $year == "" ) return "";

        $starting_date = "{$month}/01/{$year}";
        return $starting_date;
    }
}
