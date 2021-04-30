<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateCommissions extends A2PLibrary
{

    function __construct( $debug=false )
    {
        parent::__construct($debug);

    }

    public function execute($company_id, $user_id=null, $import_date=null )
    {
        try
        {
            parent::execute($company_id);

            // What is our import date?
            if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
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

            // COMMISSION TYPE
            // What type of commission calculation are we using?
            $commission_type = GetCommissionType($company_id);
            if ( $commission_type === '' ) throw new UIException("Commission feature is enabled, but the commission type is not specified.  Commissions not processed.");
            $this->debug(" CommissionType: [{$commission_type}]");


            // Create a record for each life/tier that we find in the import this month.
            // Bring a few pieces of information forward that we will need to start organizing the
            // data for commission calculation.
            $this->debug(" INSERT: Adding records to company commission data table.");
            SupportTimerStart($company_id, $import_date, 'insert_commission_data', __CLASS__);
            $this->ci->GenerateCommissions_model->insert_commission_data($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, 'insert_commission_data', __CLASS__);


            // Copy all of the records we found that live in the CompanyCommissionData table into the
            // CompanyCommissionDataCompare table.  As data moves from the data table to the compare table
            // we will investigate information about each records from the previous month.  As a result,
            // this table will hold booleans indicating specific information about the life-tier we can
            // later use to make decisions against.  After this INSERT, the business logic flags are set,
            // but the Code and Descriptions are blank.
            $this->debug(" INSERT: Adding compare records for this months commission data.");
            SupportTimerStart($company_id, $import_date, 'insert_company_commission_data_compare', __CLASS__);
            $this->ci->GenerateCommissions_model->insert_company_commission_data_compare($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, 'insert_company_commission_data_compare', __CLASS__);


            // The query above assumed every record had a previous record the month before.  ( CoverageGapOffset = -1 )
            // Now we will look at the records that did not exist last month and figure out how
            // many months it has been since we last saw this life/plan.  The is the CoverageGapOffset.
            $this->debug(" INSERT: Adding compare records for this months commission data that were missing last month.");
            $this->_insert_commission_data_compare_with_gap($company_id, $import_date);
            $this->timer(" INSERT: Adding compare records for this months commission data that were missing last month.");


            // So we have bulk calculated the offsets, then we calculated the offset for missing records.
            // Now, we need to calculate the gap when we have records, but the previous records were IGNORED
            // because they were terminated.
            $this->debug(" INSERT: Adding compare records for this months commission data that were ignored last month.");
            $this->_insert_commission_data_compare_with_termination($company_id, $import_date);
            $this->timer(" INSERT: Adding compare records for this months commission data that were ignored last month.");


            // Tier Changed
            // The CompanyCommissionDataCompare table has a column called TierChanged.  That has
            // not been set yet.  Review the Data table and set that column if there was a tier
            // change between this month and last.
            $this->debug(" UPDATE: Looking for tier changes for this months commission data.");
            SupportTimerStart($company_id, $import_date, 'company_commission_data_compare_tier_changed', __CLASS__);
            $this->ci->GenerateCommissions_model->company_commission_data_compare_tier_changed($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, 'company_commission_data_compare_tier_changed', __CLASS__);

            // Now that we have researched the CommissionData, we can start making decisions on what
            // business actions we will apply to each record.  Next we will apply those actions and
            // descriptions below.  Once done, the compare table will tell you what needs to be done
            // for each tier this month to correctly calculate the commission data.

            // IGNORE
            $this->debug(" IGNORE: Set code to COMPLETE for life-tiers that no longer exist.");
            $code = 'IGNORE';
            $description = 'Ignoring his record.  Life tier no longer in effect for this import date.';
            SupportTimerStart($company_id, $import_date, 'update_company_commission_data_compare_Ignore', __CLASS__);
            $this->ci->GenerateCommissions_model->update_company_commission_data_compare_Ignore($company_id, $import_date, $code, $description);
            SupportTimerEnd($company_id, $import_date, 'update_company_commission_data_compare_Ignore', __CLASS__);

            // WARNING
            // Scan the compare table and look for life-plans that have multiple tiers.  I have been told
            // that this should not happen with valid data.  I don't trust this!  We will find these guys
            // now and mark them with the code of WARNING.
            $this->debug(" UPDATE: Set code to WARNING if multiple tiers found per life-plan.");
            SupportTimerStart($company_id, $import_date, 'update_company_commission_data_compare_WarnOnMultipleTiersPerLifePlan', __CLASS__);
            $this->ci->GenerateCommissions_model->update_company_commission_data_compare_WarnOnMultipleTiersPerLifePlan($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, 'update_company_commission_data_compare_WarnOnMultipleTiersPerLifePlan', __CLASS__);


            // RESET
            // The original effective date changed on the life tier this month.  We will
            // set this record to RESET.
            $this->debug(" UPDATE: Set code to RESET.  The original effective date has changed.");
            $code = 'RESET';
            $description = 'Commissionable Premium records reset.  All stacked premiums reset to new premium.';
            SupportTimerStart($company_id, $import_date, 'update_company_commission_data_compare_OriginalEffectiveDateHasReset', __CLASS__);
            $this->ci->GenerateCommissions_model->update_company_commission_data_compare_OriginalEffectiveDateHasReset($company_id, $import_date, $code, $description);
            SupportTimerEnd($company_id, $import_date, 'update_company_commission_data_compare_OriginalEffectiveDateHasReset', __CLASS__);

            if ( $commission_type === COMMISSION_TYPE_HEAP_STACKED )
            {
                // ADD
                // Volume changed or tier changed, monthly cost increased.
                $this->debug(" UPDATE: Set code to ADD.  Volume or tier changed.  Monthly cost increased.");
                $code = 'ADD';
                $description = 'Adding a new commissionable stack with an CED matching the CSD.  Volume or Tier changed and the monthly cost increased.';
                SupportTimerStart($company_id, $import_date, 'update_commission_data_compare_VolumeOrTierChangeWithCostIncrease', __CLASS__);
                $this->ci->GenerateCommissions_model->update_commission_data_compare_VolumeOrTierChangeWithCostIncrease($company_id, $import_date, $code, $description);
                SupportTimerEnd($company_id, $import_date, 'update_commission_data_compare_VolumeOrTierChangeWithCostIncrease', __CLASS__);
            }
            else
            {
                // COMMISSION_TYPE_HEAP_FLAT
                // COMMISSION_TYPE_LEVEL

                // INCREASE
                // Volume changed or tier changed, monthly cost increased.
                $this->debug(" UPDATE: Set code to INCREASE.  Volume or tier changed.  Monthly cost increased.");
                $code = "INCREASE";
                $description = 'Increasing commission data to new monthly total.';
                SupportTimerStart($company_id, $import_date, 'update_commission_data_compare_VolumeOrTierChangeWithCostIncrease', __CLASS__);
                $this->ci->GenerateCommissions_model->update_commission_data_compare_VolumeOrTierChangeWithCostIncrease($company_id, $import_date, $code, $description);
                SupportTimerEnd($company_id, $import_date, 'update_commission_data_compare_VolumeOrTierChangeWithCostIncrease', __CLASS__);
            }

            // REDUCE
            // Volume changed or tier changed, monthly cost decreased.
            $this->debug(" UPDATE: Set code to REDUCE.  Volume unchanged or tier changed. Monthly cost decreased.");
            $code = 'REDUCE';
            $description = 'Reduce the amount of premium commissionable. Volume or tier changed and monthly cost decreased.';
            SupportTimerStart($company_id, $import_date, 'update_company_commission_data_compare_VolumeOrTierChangeWithCostDecrease', __CLASS__);
            $this->ci->GenerateCommissions_model->update_company_commission_data_compare_VolumeOrTierChangeWithCostDecrease($company_id, $import_date, $code, $description);
            SupportTimerEnd($company_id, $import_date, 'update_company_commission_data_compare_VolumeOrTierChangeWithCostDecrease', __CLASS__);

            // INCREASE
            // Volume Unchanged, Monthly cost increased.
            $this->debug(" UPDATE: Set code to INCREASE.  Volume unchanged. Monthly cost increased.");
            $code = "INCREASE";
            $description = 'Increasing commission data to new monthly total.';
            SupportTimerStart($company_id, $import_date, 'update_company_commission_data_compare_VolumeDecreasedWithCostIncrease', __CLASS__);
            $this->ci->GenerateCommissions_model->update_company_commission_data_compare_VolumeDecreasedWithCostIncrease($company_id, $import_date, $code, $description);
            SupportTimerEnd($company_id, $import_date, 'update_company_commission_data_compare_VolumeDecreasedWithCostIncrease', __CLASS__);

            // REDUCE
            // Volume Unchanged, Monthly cost decreased.
            $this->debug(" UPDATE: Set code to REDUCE.  Volume unchanged.  Montly cost decreased.");
            $code = "REDUCE";
            $description = 'Reduce the amount of premium commissionable. Volume remained, but monthly cost did not increase.';
            SupportTimerStart($company_id, $import_date, 'update_company_commission_data_compare_VolumeDecreaseWithCostDecrease', __CLASS__);
            $this->ci->GenerateCommissions_model->update_company_commission_data_compare_VolumeDecreaseWithCostDecrease($company_id, $import_date, $code, $description);
            SupportTimerEnd($company_id, $import_date, 'update_company_commission_data_compare_VolumeDecreaseWithCostDecrease', __CLASS__);

            // Now we will start processsing the business action that have been set on each of the
            // compare records.  Some we can do in bulk.  Others we have to process life by life.

            // Capture the RESET records for this month.
            $this->debug(" RESET: processing reset records for this month.");
            SupportTimerStart($company_id, $import_date, 'insert_company_commission_records_RESET', __CLASS__);
            $this->ci->GenerateCommissions_model->insert_company_commission_records_RESET($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, 'insert_company_commission_records_RESET', __CLASS__);


            $this->debug(" ADD,REDUCE,INCREASE: processing add, reduce and increase records for this month.");
            SupportTimerStart($company_id, $import_date, 'select_tagged_commissionable_records', __CLASS__);
            $results = $this->ci->GenerateCommissions_model->select_tagged_commissionable_records($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, 'select_tagged_commissionable_records', __CLASS__);

            SupportTimerStart($company_id, $import_date, 'add_reduce_increase_lives', __CLASS__);
            foreach($results as $result) {
                $life_id = GetArrayStringValue("LifeId", $result);
                $carrier_id = GetArrayStringValue("CarrierId", $result);
                $plantype_id = GetArrayStringValue("PlanTypeId", $result);
                $plan_id = GetArrayStringValue("PlanId", $result);
                $coveragetier_id = GetArrayStringValue("CoverageTierId", $result);
                $code = GetArrayStringValue("Code", $result);
                $description = GetArrayStringValue("Description", $result);
                $oed_reset = GetArrayStringValue("OEDReset", $result);
                $tier_changed = GetArrayStringValue("TierChanged", $result);
                $volume_changed = GetArrayStringValue("VolumeChanged", $result);
                $monthly_cost_changed = GetArrayStringValue("MonthlyCostChanged", $result);
                $volume_increased = GetArrayStringValue("VolumeIncreased", $result);
                $monthly_cost_increased = GetArrayStringValue("MonthlyCostIncreased", $result);
                $oed_code = GetArrayStringValue("OEDCode", $result);
                $monthly_cost = GetArrayStringValue("MonthlyCost", $result);
                $volume = GetArrayStringValue("Volume", $result);
                $effective_date = GetArrayStringValue("Calculated-EffectiveDate", $result);
                $coverage_start_date = GetArrayStringValue("CoverageStartDate", $result);
                $coverage_gap_offset = GetArrayStringValue("CoverageGapOffset", $result);

                if ($code === 'ADD') {
                    $this->_add($company_id, $import_date, $result);

                } else if ($code === 'REDUCE') {
                    $this->_reduce($company_id, $import_date, $result);

                } else if ($code === 'INCREASE')
                {
                    $this->_increase($company_id, $import_date, $result);
                }
            }
            SupportTimerEnd($company_id, $import_date, 'add_reduce_increase_lives', __CLASS__);

            // LEFTOVERS
            // Now that we are done processing each of the actionable events, we need to "copy"
            // forward the non-actionable events.
            $this->debug(" LEFTOVERS: Processing records that had no actions this month.");
            SupportTimerStart($company_id, $import_date, 'copy_leftover_company_commission_life_plan', __CLASS__);
            $this->_copy_leftovers($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, 'copy_leftover_company_commission_life_plan', __CLASS__);

            // COVERAGE START DATE CHANGE
            // Next we will look for date changes and make adjustments.
            $this->debug(" RETRO-CHANGE: Process retro change activity.");
            SupportTimerStart($company_id, $import_date, '_process_coverage_start_date_changes', __CLASS__);
            $this->_process_coverage_start_date_changes($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, '_process_coverage_start_date_changes', __CLASS__);

            // SUMMARY
            // Generate our summary data based on the the data we just created.
            $this->debug(" SUMMARY: Creating commission summary data.");
            SupportTimerStart($company_id, $import_date, 'insert_commission_summary', __CLASS__);
            $this->ci->GenerateCommissions_model->insert_commission_summary($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, 'insert_commission_summary', __CLASS__);

            // VALIDATION
            // Review the commission data and the billing data and look for places
            // where things don't match.
            $this->debug(" VALIDATION: Validating the commission data.");
            SupportTimerStart($company_id, $import_date, '_validate_commission_data', __CLASS__);
            $this->_validate_commission_data($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, '_validate_commission_data', __CLASS__);

            // COMMISSION LIFE
            // Map the life record back to a single record in the import table for this month.
            $this->debug(" DETAILS: Creating commission detail data.");
            SupportTimerStart($company_id, $import_date, '_generate_commission_life', __CLASS__);
            $this->_generate_commission_life($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, '_generate_commission_life', __CLASS__);

            // Report Warnings.
            $this->debug(" WARNINGS: Capture all commission warnings.");
            SupportTimerStart($company_id, $import_date, 'capture_all_commission_warnings', __CLASS__);
            $this->ci->GenerateCommissions_model->company_commission_warning_save_WARNING($company_id, $import_date);
            $this->ci->GenerateCommissions_model->remove_zero_dollar_warnings($company_id, $import_date);
            $this->ci->GenerateCommissions_model->publish_report_warnings($company_id, $import_date);
            SupportTimerEnd($company_id, $import_date, 'capture_all_commission_warnings', __CLASS__);

        }
        catch(UIException $e)
        {
            $this->debug("EXCEPTION: " . $e->getMessage());

            // If we have an exception and the message is intended for the user,
            // rollback the commission data only.  Then push the message onto the
            // report review table.
            if ( GetStringValue($company_id) !== '' && GetStringValue($import_date) !== '' )
            {
                $this->rollback($company_id, $import_date);
            }
            WriteReportReviewWarningMessage($company_id, $import_date, $e->getMessage());
        }
        catch(Exception $e)
        {
            // Yeah, that's not great.  Best stop and roll it all back.
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * rollback
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

        $this->debug(" ROLLBACK: CompanyCommissionValidate for company [{$company_id}] and import [{$import_date}].");
        $this->ci->GenerateCommissions_model->validate_rollback($company_id, $import_date);

        $this->debug(" ROLLBACK: CompanyCommissionWorker for company [{$company_id}] and import [{$import_date}].");
        $this->ci->GenerateCommissions_model->clear_worker_table($company_id, $import_date);

        $this->debug(" ROLLBACK: CompanyComissionLife for company [{$company_id}] and import [{$import_date}].");
        $this->ci->GenerateCommissions_model->delete_commission_life($company_id, $import_date);

        $this->debug(" ROLLBACK: CompanyComissionLifeResearch for company [{$company_id}] and import [{$import_date}].");
        $this->ci->GenerateCommissions_model->delete_commission_research($company_id, $import_date);

        $this->debug(" ROLLBACK: CompanyComissionSummary for company [{$company_id}] and import [{$import_date}].");
        $this->ci->GenerateCommissions_model->delete_commission_summary($company_id, $import_date);

        $this->debug(" ROLLBACK: CompanyComission for company [{$company_id}] and import [{$import_date}].");
        $this->ci->GenerateCommissions_model->delete_company_commission($company_id, $import_date);

        $this->debug(" ROLLBACK: CompanyComissionDataCompare for company [{$company_id}] and import [{$import_date}].");
        $this->ci->GenerateCommissions_model->delete_company_commission_data_compare($company_id, $import_date);

        $this->debug(" ROLLBACK: CompanyComissionData for company [{$company_id}] and import [{$import_date}].");
        $this->ci->GenerateCommissions_model->delete_company_commission_data($company_id, $import_date);

        $this->debug(" Deleting records from the ReportReviewWarnigns table related to commission processing.");
        $this->ci->GenerateCommissions_model->rollback_report_review_warnings_created_by_commission($company_id, $import_date);

        $this->debug(" Deleting records from the CompanyCommissionWarning table.");
        $this->ci->GenerateCommissions_model->delete_company_commission_warning($company_id, $import_date);

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
     * _copy_leftovers
     *
     * We have marked actionable items in the compare table.  Now we need to move the items that have
     * no action into the commission table.  Since they are not actionable, nothing changed.  We will just
     * copy them forward from the previous record.
     *
     * Now, since there could be a gap in coverage, we can't assume the records we want to copy forward existed
     * last month.  Good thing we know the "gap" since the last time we saw each record.
     *
     * We are going to act in bulk here, but we have to do it in bulk by each unique coverage gap we are
     * dealing with.  First, pull out the distinct gaps and then copy once per gap.
     *
     * @param $company_id
     * @param $import_date
     */
    private function _copy_leftovers($company_id, $import_date)
    {
        $coverage_gaps = $this->ci->GenerateCommissions_model->select_distinct_leftover_coverage_gaps($company_id, $import_date);
        if ( ! empty($coverage_gaps) )
        {
            foreach( $coverage_gaps as $row)
            {
                $offset = GetArrayIntValue('Offset', $row);
                $this->ci->GenerateCommissions_model->copy_leftover_company_commission_life_plan($company_id, $import_date, $offset);
            }
        }

    }

    /**
     * _process_coverage_start_date_changes
     *
     * Look for CSD changes, with no other vectors, and make corrections to the stack.
     * - Find them.
     * - Delete old stacks >= the new CSD.
     * - Calculate how much the new stack needs to be updated.
     * - add the new record
     * - Reset the reset record flag on oldest record.
     *
     * @param $company_id
     * @param $import_date
     */
    private function _process_coverage_start_date_changes($company_id, $import_date)
    {
        // CLEAR WORKER
        // Empty out the worker table.  We will need it to track keys for updates.
        $this->ci->GenerateCommissions_model->clear_worker_table($company_id, $import_date);

        // FIND
        // Find all records that have a CSD change and there are no tier changes, monthly cost changes
        // and/or volume changes.  These items will require us to restructure the stacks to match the
        // new CSD.
        $changed = $this->ci->GenerateCommissions_model->find_csd_changes($company_id, $import_date);



        // RECALCULATE
        // For each change, decide what we are going to do in order to correct the stack.
        foreach($changed as $item)
        {
            $life_id = GetArrayStringValue("LifeId", $item);
            $carrier_id = GetArrayStringValue("CarrierId", $item);
            $plantype_id = GetArrayStringValue("PlanTypeId", $item);
            $plan_id = GetArrayStringValue("PlanId", $item);
            $effective_dt = GetArrayStringValue("CommissionEffectiveDate", $item);
            $premium = GetArrayStringValue("MonthlyCost", $item);


            // The effective date on this item is <= the most recent CED in the stack.  Here we will
            // remove part of the stack, insert this new stack record and the normalize the dollar values.

            // DELETE
            // Delete all stacks for this life/plan that have an effective date that is >= the effective date on this life/plan.
            $this->ci->GenerateCommissions_model->delete_csd_change($company_id,$import_date,$life_id,$carrier_id, $plantype_id,$plan_id, $effective_dt);

            // SUM
            // Get the total premium amount on the stack that is left.  Then calculate how much premium we
            // will need from the current life/plan to fill in the gap.
            $stack_total = $this->ci->GenerateCommissions_model->get_stack_total($company_id,$import_date,$life_id,$carrier_id, $plantype_id,$plan_id);
            $adjusted_premium = $premium - $stack_total;

            // INSERT
            // Insert a stack if $adjusted_total is > 0
            if ( $adjusted_premium > 0 )
            {
                $this->ci->GenerateCommissions_model->insert_company_commission_life_plan($company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $effective_dt, $adjusted_premium);
            }
            else
            {
                // ADDED: 201905
                // In the case where the updated CSD moves forward rather than backwards, we will find ourselves in
                // this block.  It represents no dollar value change on the stack, but the most recent record on
                // the stack needs to be updated to the new CSD.  Find the most recent record and update the
                // Commission Effective Date forward to match.  Due to the retro engine ignoring things that are
                // in the far future, this will only update things if the new CSD is newer than the current CED
                // but not greater than the current import date.  See test case "05_DateChangeCSD", "corrected"
                // step.

                $record = $this->ci->GenerateCommissions_model->select_most_recent_life_plan_stack($company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $effective_dt);
                if ( ! empty($record) )
                {
                    $record_id = GetArrayStringValue("Id", $record);
                    $this->ci->GenerateCommissions_model->update_commission_effective_date_on_record($record_id, $effective_dt);
                }
            }

            // RESET RECORD
            // The reset record could be all messed up now because stacks just shifted around.
            // Blank out the reset records for this stack and set it on the oldest item.
            $this->ci->GenerateCommissions_model->mark_reset_record2($company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id);

            // 20190529: I replaced the function above.  Below is what it was.  I could make no sense of it!
            // We were trying to reset things in bulk, but we were processing at the individual level AND it was not working.
            // I re-tooled it to do what the comment said it would do using the full life/plan key that WAS being passed into
            // the original function, but not used.  Remove this comment after a few months.
            //$this->ci->GenerateCommissions_model->mark_reset_record($company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id);
        }



    }
    /**
     * _increase
     *
     * This function will take the INCREASE action.  The previous months records
     * are moved forward to this month.  We then pop the most recent record and
     * add the increase difference to the record so that we match the total
     * premium amount.
     *
     *
     * [ INCREASE ]
     * Increase most recent commissionable item level with new premium.  CED OED remains.
     *
     * This function will take the ADD action.
     * @param $company_id
     * @param $import_date
     * @param $input
     */
    private function _increase($company_id, $import_date, $input)
    {
        try
        {

            $life_id = GetArrayStringValue("LifeId", $input);
            $carrier_id = GetArrayStringValue("CarrierId", $input);
            $plantype_id = GetArrayStringValue("PlanTypeId", $input);
            $plan_id = GetArrayStringValue("PlanId", $input);
            $monthly_cost = GetArrayStringValue("MonthlyCost", $input);
            $coverage_start_date = GetArrayStringValue("CoverageStartDate", $input);
            $coverage_gap_offset = GetArrayIntValue("CoverageGapOffset", $input);

            // SUM TOTAL: Get the sum total of all Commissionable Premiums for this Life/Plan as of last month.
            $total = $this->ci->GenerateCommissions_model->selet_total_commisionable_premium($company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $coverage_gap_offset);
            if ( is_array($total) ) throw new Exception("Needed to do an INCREASE, but could not find previous amount.");

            // DIFF: Find the difference between this month's total and last month's total.
            $diff = $monthly_cost - $total;
            if ( $diff < 0 ) throw new Exception("Needed to do an INCREASE, but the difference was less than zero!");

            // COPY: Copy all Life/Plan records from last month to this month.
            $this->ci->GenerateCommissions_model->copy_previous_company_commission_life_plan( $company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $coverage_gap_offset );

            // Collect all records from last month order by CED desc. ( Newest First )
            $current_collection = $this->ci->GenerateCommissions_model->select_company_commission_life_plan( $company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, "desc" );
            if ( empty($current_collection)  ) throw new Exception("Need to do an INCREASE, but we have no records to apply it to.");

            // UPDATE: Increase the commissionable premium by the diff amount for the most recent
            // record we identified in the current collection.
            $record = $current_collection[0];
            $record_id = GetArrayIntValue("Id", $record);
            $this->ci->GenerateCommissions_model->increase_company_commission_life_plan($company_id, $import_date, $record_id, $diff);

        }catch(Exception $e)
        {
            $this->ci->GenerateCommissions_model->company_commission_warning_insert($company_id, $import_date, $input, $e->getMessage(), true);
        }
    }

    /**
     * _add
     *
     * This function will take the ADD action.  The previous months records
     * will be copied forward to this month and then a new record will be
     * added with the difference between the new monthly_cost of the sum
     * total from the previous month.
     *
     * The end result is that the total collection of records will equal
     * the total commissionable premium for this month on the life/plan.
     *
     * [ ADD ]
     * The difference between the combined stacked commissionable premiums and the new premium is saved as a new commissionable amount with a CED of the new CSD.
     * Existing commissionable premium sets stay in effect with existing CEDs.
     *
     * @param $company_id
     * @param $import_date
     * @param $input
     */
    private function _add($company_id, $import_date, $input)
    {
        try
        {
            $life_id = GetArrayStringValue("LifeId", $input);
            $carrier_id = GetArrayStringValue("CarrierId", $input);
            $plantype_id = GetArrayStringValue("PlanTypeId", $input);
            $plan_id = GetArrayStringValue("PlanId", $input);
            $monthly_cost = GetArrayStringValue("MonthlyCost", $input);
            $coverage_start_date = GetArrayStringValue("CoverageStartDate", $input);
            $coverage_gap_offset = GetArrayIntValue("CoverageGapOffset", $input);

            // COPY: Copy all Life/Plan records from the last month to this month.
            $this->ci->GenerateCommissions_model->copy_previous_company_commission_life_plan( $company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $coverage_gap_offset );

            // DELETE: Remove any stacks NEWER than the coverage start date we have in hand.
            $this->ci->GenerateCommissions_model->delete_company_commission_newer_stacks( $company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $coverage_start_date );

            // SUM TOTAL: Get the sum total of all Commissionable Premiums for our Life/Plans.
            $total = $this->ci->GenerateCommissions_model->selet_total_commisionable_premium($company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, 0);
            if ( is_array($total) ) throw new Exception("Needed to do an ADD, but could not find previous amount.");

            // DIFF: Find the difference we need to ADD to the stacks..
            $diff = $monthly_cost - $total;
            if ( $diff < 0 ) throw new Exception("Needed to do an ADD, but the difference was less than zero!");

            // ADD: Add a new Life/Plan records to record the difference between the previous total and the new total.
            $this->ci->GenerateCommissions_model->insert_company_commission_life_plan($company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $coverage_start_date, $diff);

        }
        catch(Exception $e)
        {
            $this->ci->GenerateCommissions_model->company_commission_warning_insert($company_id, $import_date, $input, $e->getMessage(), true);
        }
    }

    /**
     * _reduce
     *
     * This function will create new records in the CompanyCommission table
     * by doing the REDUCE action on the previous months records for the given
     * life plan.
     *
     * If only a single commissionable item in effect:
     *   - Reduce the amount of premium commissionable.  CED remains.
     * If stacked items in effect:
     *   - Reduce / eliminate the amounts in order of stacked from most recent to oldest.
     *     CED remains if amount not eliminated.
     *
     * The CED does not remain in all cases.  We have expanded the logic here to
     * link up with the billing engine so that the CED will adjust inline with changes
     * that triggered retro changes.  The following logic will determin if we can
     * update the CED on the stack that we are reducing.
     *
     * --> If a stack is not eliminated by your reduce, you need to consider updating the CED.
     * --> If the billing engine thinks there is a retro-change happening that involves the CSD, you need to consider updating the CED.
     * --> If the stack you reduced has a CED that is not before the current retro window, you need to consider updating the CED.
     * --> IF the CSD you have from the import is earlier than the CED on the stack you reduced, you need to consider updating the CED.
     *      YES.  If all of the above is true, the stack up reduced needs to have the CED updated to match the CSD.
     *
     * @param $company_id
     * @param $import_date
     * @param $input
     * @throws Exception
     */
    private function _reduce($company_id, $import_date, $input )
    {
        $logit = false;
        try
        {

            $life_id = GetArrayStringValue("LifeId", $input);
            $carrier_id = GetArrayStringValue("CarrierId", $input);
            $plantype_id = GetArrayStringValue("PlanTypeId", $input);
            $plan_id = GetArrayStringValue("PlanId", $input);
            $monthly_cost = GetArrayStringValue("MonthlyCost", $input);
            $coverage_gap_offset = GetArrayIntValue("CoverageGapOffset", $input);

            if ( $logit ) LogIt("bah", "Reducing [{$life_id}] [{$carrier_id}] [{$plantype_id}] [{$plan_id}] [{$coverage_gap_offset}]");

            // What is the sum TOTAL from last month.
            $total = $this->ci->GenerateCommissions_model->selet_total_commisionable_premium($company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $coverage_gap_offset);
            if ( is_array($total) ) $total = 0;
            if ( $logit ) LogIt("bah", "  The total monthly cost was [{$total}]");
            if ( $logit ) LogIt("bah", "  The new monthly cost will be [{$monthly_cost}]");

            // If we reduce the sum TOTAL from the monthly cost on this record, do things look okay?
            $reduce_amount = $total - $monthly_cost;
            if ( $reduce_amount < 0 ) throw new Exception("Unable to reduce the record because the total ({$total}) from last month is less than the amount ({$monthly_cost}) we are going to reduce this month.");
            if ( $logit ) LogIt("bah", "  We will REDUCE the total monthly cost by [{$reduce_amount}]");

            // Collect all records from last month order by CED desc. ( Newest First )
            $previous_collection = $this->ci->GenerateCommissions_model->select_previous_company_commission_life_plan( $company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $coverage_gap_offset );

            // Did we happen to remove a whole stack while doing the reduce?
            // If we did, there will be extra work to be done.  Denote that with this boolean.
            $stack_removed = false;

            // Loop the records and reduce the total amount until we run out of money.
            $collection = array();
            foreach( $previous_collection as $record )
            {
                $record_cost = GetArrayFloatValue("CommissionablePremium", $record);
                $leftovers = $record_cost - $reduce_amount;
                if ( $logit ) LogIt("bah", "  record cost[{$record_cost}]");
                if ( $logit ) LogIt("bah", "  leftovers[{$leftovers}]");

                if ( $reduce_amount == 0 )
                {
                    // Nothing left to reduce.

                    // Copy the row to the collection.
                    $collection[] = $record;
                    if ( $logit ) LogIt("bah", "  reduce amount is zero, we are done here.");
                }
                else if( $leftovers > 0 )
                {
                    // We have leftovers.  This means we were able to reduce this
                    // record by the total reduce amount and we still have an amount
                    // we need to report.

                    // Update the record so the total amount is reduced by the leftovers.
                    $record["CommissionablePremium"] = $leftovers;

                    // Save the row to the new collection.
                    $collection[] = $record;

                    // Set reduce_amount to zero.
                    $reduce_amount = 0;
                    if ( $logit ) LogIt("bah", "  Extra leftovers");
                }
                elseif ( $leftovers == 0 )
                {
                    // Zero leftovers.
                    // This means we reduced the cost of this record by our reduce amount
                    // and there is no positive value left.  Do not move this record into
                    // the new collection.  Also update our reduce amount by the cost
                    // of this record.

                    // Set reduce_amount to be zero.
                    $reduce_amount = 0;
                    $stack_removed = true;

                    if ( $logit ) LogIt("bah", "  Zero leftovers");
                }
                else
                {
                    // Negative leftovers.
                    // This means we have eliminated this row and we still have
                    // more to reduce.  Do not add this record into the new
                    // collection.  Change reduce_amount to be the leftovers
                    // and continue processing.

                    // Set the reduce amount to be the ABS(leftovers)
                    $reduce_amount = abs($leftovers);
                    $stack_removed = true;

                    if ( $logit ) LogIt("bah", "  Negative leftovers");

                }
            }

            // CED - CHANGE
            // If we did not remove a stack when processing this REMOVE action, then we
            // might need to change the CED too.  Let's evaluate the situation.
            if ( ! $stack_removed && isset($collection[0]) )
            {
                // Does the retro engine think that there was a Retro-Change event that had something to
                // do with the Coverage Start Date?
                $csd_retro_change = $this->ci->GenerateCommissions_model->is_coverage_start_date_correction($company_id, $import_date, $input);
                if ( $csd_retro_change )
                {
                    // There is a CSD change in place for this Retro-Change event.
                    // Now, we need to know if the stack we reduced has a CED that is outside of the Retro Window.
                    if ( $logit )  LogIt("bah", "  CSD Retro-Change has been detected.");

                    $stack = $collection[0];
                    $ced = GetArrayStringValue("CommissionEffectiveDate", $stack);
                    $csd = GetArrayStringValue("CoverageStartDate", $input);
                    $retro_window_start = $this->ci->GenerateCommissions_model->retro_window_start($company_id, $import_date, $input);

                    // Decide if the CED from the stack we modified is before the retro window start date.
                    $is_before_retro_window = false;
                    if ( strtotime($ced) < strtotime($retro_window_start) ) $is_before_retro_window = true;

                    if ( $logit ) LogIt("bah", "  ced[{$ced}] [".strtotime($ced)."]");
                    if ( $logit ) LogIt("bah", "  csd[{$csd}]");
                    if ( $logit ) LogIt("bah", "  retro_window_start[{$retro_window_start}] [".strtotime($retro_window_start)."]");
                    if ( $logit && $is_before_retro_window ) LogIt("bah", "  is_before_retro_window[t]");
                    if ( $logit && ! $is_before_retro_window ) LogIt("bah", "  is_before_retro_window[f]");

                    if ( ! $is_before_retro_window )
                    {
                        // If the CSD is before the CED, this is allowed.  If it's not we will keep the stack date we have.
                        $is_csd_before_ced = false;
                        if ( strtotime($csd) < strtotime($ced) ) $is_csd_before_ced = true;
                        if ( $is_csd_before_ced )
                        {
                            // The stack we are changing has a CED that is in the Retro Window for the import date.
                            // IN this case, we will allow the CED to update on the stack to the new CSD value.
                            if ( $logit ) LogIt("bah", "  We reduced a single stack and there is CSD change in play. The new CSD was in the retro window.  The CSD is before the CED.  Updating the CED.");
                            $collection[0]["CommissionEffectiveDate"] = $csd;
                        }
                        else
                        {
                            if ( $logit ) LogIt("bah", "  We reduced a single stack and there is CSD change in play. The new CSD was in the retro window.  The CSD is NOT before the CED.  We will be keeping our existing CED.");
                        }
                    }
                    else
                    {
                        if ( $logit )  LogIt("bah", "  We reduced a single stack and there is a CSD change in play.  However, the CSD was outside the retro window.  We will be keeping our existing CED.");
                    }
                }
            }


            // Save collection.
            if ( ! empty($collection) )
            {
                $collection = array_reverse($collection);
                foreach($collection as $record)
                {
                    // ADD: Add a new Life/Plan records to record the difference between the previous total and the new total.
                    $date = GetArrayStringValue("CommissionEffectiveDate", $record);
                    $cost = GetArrayFloatValue("CommissionablePremium", $record);
                    $reset = GetArrayStringValue("ResetRecord", $record);

                    $this->ci->GenerateCommissions_model->insert_company_commission_life_plan($company_id, $import_date, $life_id, $carrier_id, $plantype_id, $plan_id, $date, $cost, $reset);
                }
            }
        }
        catch(Exception $e)
        {
            $this->ci->GenerateCommissions_model->company_commission_warning_insert($company_id, $import_date, $input, $e->getMessage(), true);
        }
    }

    /**
     * _generate_commission_life
     *
     * We are going to need to map back our CompanyCommission data back to individual lives
     * so we can report on it.  This function will generate a table that has the
     * Life/Plan key mapped back to one of the ImportData table records that corresponds to
     * the life being tracked in then CompanyCommission table.
     *
     * @param $company_id
     * @param $import_date
     */
    private function _generate_commission_life($company_id, $import_date)
    {
        // RESEARCH
        // Grow the CompanyCommission data so that it maps back to the ImportData table by life.  This
        // can possibly result in multiple rows per company commission record.
        $this->debug(" GenerateCommissionLife: Adding records to the commission research table.");
        SupportTimerStart($company_id, $import_date, 'insert_commission_research', __FUNCTION__);
        $this->ci->GenerateCommissions_model->insert_commission_research($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'insert_commission_research', __FUNCTION__);


        // MISSING
        // It's possible that we identified a missing life and we can't map back to an import data
        // record this month.  Update the research data to be -1 rather than null so we can work with
        // the data without the hassle of maybe/maybe not.
        $this->debug(" GenerateCommissionLife: Marking records that do not have a import data id.");
        SupportTimerStart($company_id, $import_date, 'update_commission_research_missing', __FUNCTION__);
        $this->ci->GenerateCommissions_model->update_commission_research_missing($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'update_commission_research_missing', __FUNCTION__);

        // DUPLICATES
        // Since going from CompanyCommission to ImportDate is potentially a one to many relationship
        // we need to back that off again.  We don't care if there are many items in the import data
        // per life.  We just need one so we can capture the unique life data which is the same for
        // all of them.  Do that here by keeping only the largest import data of the multiples, if we
        // see any.
        $this->debug(" GenerateCommissionLife: Removing duplicate records in commission life.");
        SupportTimerStart($company_id, $import_date, 'insert_commission_life', __FUNCTION__);
        $this->ci->GenerateCommissions_model->insert_commission_life($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'insert_commission_life', __FUNCTION__);

        // MISSING
        // Okay, now we have linked the CompanyCommission table to a single record in the import data
        // table for this month.  Remember how we marked NULL items as -1 above?  Let's undo that
        // now.
        $this->debug(" GenerateCommissionLife: Restoring records that did not have an import data id.");
        SupportTimerStart($company_id, $import_date, 'update_commission_life_missing', __FUNCTION__);
        $this->ci->GenerateCommissions_model->update_commission_life_missing($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'update_commission_life_missing', __FUNCTION__);


    }

    /**
     * _validate_commission_data
     *
     * Comparing the commission data against the billing data and collecting life/plans
     * that do not match between the Commissionable Premium and the Monthly Cost.
     *
     * @param $company_id
     * @param $import_date
     */
    private function _validate_commission_data($company_id, $import_date)
    {

        // EMPTY WORKER
        // We will use the worker table for this process. Clear it out.
        $this->debug("  Clearing out the worker table.");
        SupportTimerStart($company_id, $import_date, 'clear_worker_table', __FUNCTION__);
        $this->ci->GenerateCommissions_model->clear_worker_table($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'clear_worker_table', __FUNCTION__);

        // CAPTURE COMMISSION DATA
        // Collapse the commission down to life/plan and store the total commissionable premium
        $this->debug("  Collecting commissionable premium per life/plan.");
        SupportTimerStart($company_id, $import_date, 'validate_capture_commission_records', __FUNCTION__);
        $this->ci->GenerateCommissions_model->validate_capture_commission_records($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'validate_capture_commission_records', __FUNCTION__);

        // CAPTURE BILLING DATA
        // Collapse the billing down to life/plan and store the total monthly cost
        $this->debug("  Collecting montly cost per life/plan");
        SupportTimerStart($company_id, $import_date, 'validate_capture_billing_records', __FUNCTION__);
        $this->ci->GenerateCommissions_model->validate_capture_billing_records($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'validate_capture_billing_records', __FUNCTION__);

        // BILLING ONLY
        // Capture any records that were in the billing data set that were not in the commission data set.
        $this->debug("  Looking for records billed, but not in commission data set.");
        SupportTimerStart($company_id, $import_date, 'validate_capture_billed_only_records', __FUNCTION__);
        $this->ci->GenerateCommissions_model->validate_capture_billed_only_records($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'validate_capture_billed_only_records', __FUNCTION__);

        // MERGE COMMISSION DATA and BILLING DATA
        // Merge the billing Monthly Cost data into the commission Commissionable Premium data.
        $this->debug("  Merging the billing data into the commission data for comparison.");
        SupportTimerStart($company_id, $import_date, 'validate_update_commission_records_with_billing_data', __FUNCTION__);
        $this->ci->GenerateCommissions_model->validate_update_commission_records_with_billing_data($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'validate_update_commission_records_with_billing_data', __FUNCTION__);

        // VALIDATE
        // Mark the records that have the same commissionable premium and monthly cost as valid.
        $this->debug("  Validating records that match between commission and billing data.");
        SupportTimerStart($company_id, $import_date, 'validate_mark_records_that_passed_validation', __FUNCTION__);
        $this->ci->GenerateCommissions_model->validate_mark_records_that_passed_validation($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'validate_mark_records_that_passed_validation', __FUNCTION__);

        // DELETE
        // Remove valid records from the table, leaving only items of concern.
        $this->debug("  Removing validate records, leaving trouble records behind.");
        SupportTimerStart($company_id, $import_date, 'validate_remove_valid_records', __FUNCTION__);
        $this->ci->GenerateCommissions_model->validate_remove_valid_records($company_id, $import_date);
        SupportTimerEnd($company_id, $import_date, 'validate_remove_valid_records', __FUNCTION__);

    }


    /**
     * _insert_commission_data_compare_with_gap
     *
     * Since there could be a gap in coverage, we need to know what that gap is so
     * we can find the previous commission record for calculation.  We have already
     * assumed the gap is -1.  ( this is fine for the first upload )  Now we will
     * look for lives that were missing last month and calculate the gap per life.
     *
     * @param $company_id
     * @param $import_date
     */
    function _insert_commission_data_compare_with_gap($company_id, $import_date)
    {
        // FIRST IMPORT
        // If this is the first import, we do not want to calculate the gap.  We can
        // just use the default gap.  No need to do all this extra work.
        if ( $this->ci->Retro_model->is_first_import($company_id, $import_date) == "t" )
        {
            $this->debug(  "  Skipping the gap calculation because this is the first month.");
            return;
        }


        // EMPTY WORKER
        // We will use the worker table for this process. Clear it out.
        $this->debug("  Clearing out the worker table.");
        $this->ci->GenerateCommissions_model->clear_worker_table($company_id, $import_date);
        $this->timer("  Clearing out the worker table.");

        // FIND GAPS
        // Find all of the lives that did not have a commission data record from last
        // month.  Store these in the worker table.
        $this->debug("  Find records that have a gap in commission history.");
        $this->ci->GenerateCommissions_model->save_commissions_with_gaps($company_id, $import_date);
        $this->timer("  Find records that have a gap in commission history.");

        // SELECT GAPS
        // Grab each of the "gapped" records in the worker table so we can investigate them.
        $this->debug("  Select records that have a gap in commission history.");
        $results = $this->ci->GenerateCommissions_model->select_commission_worker_records($company_id, $import_date);
        $this->timer("  Select records that have a gap in commission history.");

        if ( ! empty($results) )
        {
            foreach($results as $item)
            {

                $most_recent = $this->ci->GenerateCommissions_model->find_most_recent_lifeplan_from_companycommissiondata($company_id, $import_date, $item);
                if ( ! empty($most_recent))
                {
                    // Okay, we found one.  We already have a record in the CompanyCommissionDataCompare
                    // table, but the logic calculations are wrong because we assumed the incorrect gap
                    // Delete that record and make a new one based on the gap we calculated.
                    $last_import_date = GetArrayStringValue("LastImportDate", $most_recent);
                    $coverage_gap_offset = GetArrayStringValue("CoverageGapOffset", $most_recent);

                    $this->ci->GenerateCommissions_model->delete_individual_companycommissiondata($company_id, $import_date, $item);
                    $this->ci->GenerateCommissions_model->insert_individual_companycommissiondata($company_id, $import_date, $coverage_gap_offset, $last_import_date, $item);

                }
            }
        }

    }


    /**
     * _insert_commission_data_compare_with_termination
     *
     * We just checked for coverage gaps.  If the life terminated previously and there
     * was no gap ( They kept sending us the record ) we need to correctly calculate the
     * gap in coverage ( not in records ) since we last saw the life.  In this case
     * we are going to do things similar to the gap research logic, but this time
     * we expect to see a record previously, but it was marked IGNORE.  In these cases
     * we want the GAP since we previously did not IGNORE the record.
     *
     * @param $company_id
     * @param $import_date
     */
    function _insert_commission_data_compare_with_termination($company_id, $import_date)
    {
        // FIRST IMPORT
        // If this is the first import, we do not want to calculate the gap.  We can
        // just use the default gap.  No need to do all this extra work.
        if ( $this->ci->Retro_model->is_first_import($company_id, $import_date) == "t" )
        {
            $this->debug(  "  Skipping the gap calculation because this is the first month.");
            return;
        }

        // EMPTY WORKER
        // We will use the worker table for this process. Clear it out.
        $this->debug("  Clearing out the worker table.");
        $this->ci->GenerateCommissions_model->clear_worker_table($company_id, $import_date);
        $this->timer("  Clearing out the worker table.");

        // FIND IGNORED GAPS
        // Find all of the lives that were marked IGNORE last month.  These are items with a
        // "gap" in coverage but we were still supplied the record.
        $this->debug("  Find records that have a gap in history due to coverage.");
        $this->ci->GenerateCommissions_model->save_commissions_with_ignored_gaps($company_id, $import_date);
        $this->timer("  Find records that have a gap in history due to coverage.");

        // SELECT GAPS
        // Grab each of the "gapped" records in the worker table so we can investigate them.
        $this->debug("  Select records that have a gap in commission history.");
        $results = $this->ci->GenerateCommissions_model->select_commission_worker_records($company_id, $import_date);
        $this->timer("  Select records that have a gap in commission history.");

        if ( ! empty($results) )
        {
            foreach($results as $item)
            {
                $most_recent = $this->ci->GenerateCommissions_model->find_most_recent_lifeplan_from_companycommissiondata($company_id, $import_date, $item);
                if ( ! empty($most_recent))
                {
                    // Okay, we found one.  We already have a record in the CompanyCommissionDataCompare
                    // table, but the logic calculations are wrong because we assumed the incorrect gap
                    // Delete that record and make a new one based on the gap we calculated.
                    $last_import_date = GetArrayStringValue("LastImportDate", $most_recent);
                    $coverage_gap_offset = GetArrayStringValue("CoverageGapOffset", $most_recent);

                    $this->ci->GenerateCommissions_model->delete_individual_companycommissiondata($company_id, $import_date, $item);
                    $this->ci->GenerateCommissions_model->insert_individual_companycommissiondata($company_id, $import_date, $coverage_gap_offset, $last_import_date, $item);
                }
            }
        }
    }


}
