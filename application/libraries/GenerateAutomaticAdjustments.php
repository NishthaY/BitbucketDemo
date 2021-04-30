<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


defined('ADJUST_PRIOR_ADJUSTMENTS') OR define('ADJUST_PRIOR_ADJUSTMENTS', 1);
defined('ADJUST_PRIOR_CHARGE') OR define('ADJUST_PRIOR_CHARGE', 2);
defined('ADJUST_RECALCULATED_CHARGE') OR define('ADJUST_RECALCULATED_CHARGE', 3);

class GenerateAutomaticAdjustments extends A2PLibrary {

    function __construct( $debug=false )
    {
        parent::__construct($debug);
    }

    public function execute( $company_id, $user_id=null )
    {
        try {

            parent::execute($company_id);
            $CI = $this->ci;


            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");

            // Clean up any records that already exists that we are about to
            // create from previous runs.
            $this->debug("Removing any existing automatic adjustments from the previous run, if there was one.");
            $CI->Retro_model->delete_automatic_adjustments( $company_id );

            $retro_data = $CI->Retro_model->select_retro_data( $company_id );
            if ( ! empty($retro_data) )
            {
                foreach($retro_data as $retro)
                {
                    $this->debug("Retro Record causing the review.");
                    $this->debug(print_r($retro, true));

                    // Pull some useful data out of this retro data.
                    $carrier_id = GetArrayStringValue("CarrierId", $retro);
                    $plantype_id = GetArrayStringValue("PlanTypeId", $retro);
                    $coverage_start_date = GetArrayStringValue("CoverageStartDate", $retro);
                    $coverage_end_date = GetArrayStringValue("CoverageEndDate", $retro);
                    $adjustment_type = GetArrayStringValue("AdjustmentType", $retro);
                    $retro_id 		 	= getArrayStringValue("Id", $retro);
                    $before_coveragetier_list = getArrayStringValue("Before-CoverageTierKey", $retro);
                    $plantypecode = getArrayStringValue("PlanTypeCode", $retro);
                    $life_id = getArrayStringValue("LifeId", $retro);
                    $adjustment_type = getArrayStringValue("AdjustmentType", $retro);

                    // Grab the retro and wash rules.
                    $plantype = $CI->Company_model->get_compmay_plantype_data_by_ids($company_id, $carrier_id, $plantype_id);
                    $retro_rule = getArrayIntValue("RetroRule", $plantype);
                    $wash_rule = getArrayIntValue("WashRule", $plantype);
                    $this->debug("wash_rule[{$wash_rule}], retro_rule[{$retro_rule}]");

                    // Loop back in time, by month, for the MAX retro period supported by the software.
                    for($count=1;$count<=RETRO_RULE_MAX;$count++)
                    {
                        // What month are we looking at.
                        $target_month = $CI->Retro_model->get_retro_month( $company_id, $count);
                        $target_month = GetArrayStringValue("TargetMonth", $target_month);
                        $this->debug("TargetMonth[{$target_month}]");

                        // Do we even have data for this month?	No? stop.
                        $target_month_exists = $CI->Retro_model->does_import_exist($company_id, $target_month);
                        if ( $target_month_exists == "f" ) continue;

                        // ROLBACK
                        // Do we have automatic adjustments for this date already?  Revert them all.
                        $this->apply_automatic_adjustment(ADJUST_PRIOR_ADJUSTMENTS, $company_id, $retro_id, $target_month);

                        // REVERT
                        // Find the $ we calculated for the target month before and enter a negative entry to revert the charge.
                        $this->apply_automatic_adjustment(ADJUST_PRIOR_CHARGE, $company_id, $retro_id, $target_month);

                        // ADD
                        // Add an adjustment for the $ that we would have charged for this month had we this data to begin with.
                        $washed_out = $this->_isWashedOut($company_id, $wash_rule, $coverage_start_date, $coverage_end_date, $target_month );
                        if ( ! $washed_out ) $this->apply_automatic_adjustment(ADJUST_RECALCULATED_CHARGE, $company_id, $retro_id, $target_month);

                        // WIDE CHECK.
                        if ( $before_coveragetier_list != "" ) {

                            // Woah!  We have a coverage tier change.  To handle this, we have to rollback and recalculate
                            // all coverage tiers in our list.
                            $list = explode($before_coveragetier_list, ",");
                            $this->debug(" Going WIDE [{$before_coveragetier_list}] for adjustment_type[{$adjustment_type}]");
                            $wide_retros = $CI->Retro_model->select_retro_data_WIDE($company_id, $target_month, $plantypecode, $before_coveragetier_list, $life_id );

                            foreach($wide_retros as $wide_retro)
                            {
                                $wide_retro_id = getArrayStringValue("RetroId", $wide_retro);
                                $washed_out = $this->_isWashedOut($company_id, $wash_rule, $coverage_start_date, $coverage_end_date, $target_month );

                                // In the case of RetroChange by CoverageTier, we only want to rollback wide if we would actually "charge" for the month
                                if ( ! $washed_out )
                                {
                                    // ROLBACK
                                    // Do we have automatic adjustments for this date already?  Revert them all.
                                    $this->apply_automatic_adjustment(ADJUST_PRIOR_ADJUSTMENTS, $company_id, $wide_retro_id, $target_month, $retro_id);

                                    // REVERT
                                    // Find the $ we calculated for the target month before and enter a negative entry to revert the charge.
                                    $this->apply_automatic_adjustment(ADJUST_PRIOR_CHARGE, $company_id, $wide_retro_id, $target_month, $retro_id);

                                    // ADD
                                    // Add an adjustment for the $ that we would have charged for this month had we this data to begin with.
                                    if ( ! $washed_out ) $this->apply_automatic_adjustment(ADJUST_RECALCULATED_CHARGE, $company_id, $wide_retro_id, $target_month, $retro_id);
                                }

                            }

                        }
                    }
                }
            }


            // Clean up automatic payments that were generated that are outside of the retro rule for the carrier.
            // This is a thing because we could have a RetroChangeTier that jump carriers.  In that case there could
            // be multiple retro rules in play.  To that end, we calculated adjustments for all retro rules.  Here
            // we prune off the ones we don't need.
            $CI->Retro_model->delete_automatic_adjustment_outside_retro_rules($company_id);


            // $0 Cost on Termination:  When the system receives a record with an end date in the current month causing
            // a wash so the current month is not charged and it sees an intra-tier change to $0, the update cost of $0
            // should not retro back to prior months.
            $CI->Adjustment_model->delete_logically_zero_cost_termination($company_id);

            // Limit Retro Period When Coverage Start Date Does Not Change: When the coverage start date does not change
            // but the monthly cost and/or premium does change, the retros applied go to the LATER of either the coverage
            // start date OR the retro look-back period.
            $CI->Adjustment_model->delete_logically_limit_retro_period_adjustments($company_id);


            // PLAN ANNIVERSARY: If the plantype in question has the Plan Anniversary Month setting set then we need
            // to apply specialized rules for various automatic adjustments types for this feature.
            $plantypes = $this->get_plan_anniversary_plan_types($company_id);
            foreach($plantypes as $plantype)
            {
                $plan_anniversary_date = getArrayStringValue("PlanAnniversaryDate", $plantype);
                $carrier_id = getArrayStringValue("CarrierId", $plantype);
                $plantype_id = getArrayStringValue("PlanTypeId", $plantype);
                $plan_id = getArrayStringValue("PlanId", $plantype);
                $coveragetier_id = getArrayStringValue("CoverageTierId", $plantype);

                // Remove any negative adjustments that crossed th plan anniversary boundary.
                $CI->Adjustment_model->delete_negative_plan_anniversary_adjustments($company_id, $plan_anniversary_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id);

                // Insert any warning messages for retro adds that cross the plan anniversary border.
                $CI->Adjustment_model->insert_plan_anniversary_retro_add_warnings($company_id, $plan_anniversary_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id);

                // Insert any warning messages for retro changes ( WIDE ) that cross the plan anniversary border.
                $CI->Adjustment_model->insert_plan_anniversary_retro_change_wide_warnings($company_id, $plan_anniversary_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id);

                // Ignore any retro changes (NARROW) that cross the plan anniversary border.
                $CI->Adjustment_model->update_automatic_adjustments_ignore_retro_change_narrow_for_plan_anniversary($company_id, $plan_anniversary_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id);

            }

            // LIFE EVENTS: Remove any adjustments that have been flagged as a life event.
            $this->_remove_life_event_adjustments($company_id);




        } catch(Exception $e) {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }

    private function _remove_life_event_adjustments($company_id) {

        $CI = $this->ci;

        // LIFE EVENTS: (Standard)
        // If the software, or the user, has decided a "Retro Data Event" was
        // triggered by a life event, we will remove the adjustments that
        // were generated by the A2P RetroEngine for the associated "Retro Data Event".
        $CI->Adjustment_model->update_automatic_adjustments_life_event($company_id);

        // LIFE EVENTS: (Many2Many)
        // In the case where we have a many2many situation because of a coverage tier
        // change, we will used the MAX CoverageStartDate for eliminating adjustments.
        // We will check along the way to see if using the MIN CoverageStartDate would
        // have resulted in more or less changes. If using the MIN date does not remove
        // the same number as the MAX date, issue a warning.
        $many2many = $CI->LifeEvent_model->select_many2many_retrodatalifeevent_range($company_id);
        foreach($many2many as $item) {

            $list = getArrayStringValue("BeforeCoverageTierIdList", $item);
            $min_coveragestartdate = getArrayStringValue("MinCoverageStartDate", $item);
            $max_coveragestartdate = getArrayStringValue("MaxCoverageStartDate", $item);

            $min_count = $CI->LifeEvent_model->count_many2many_retrodatalifeevent( $company_id, $list, $min_coveragestartdate );
            $max_count = $CI->LifeEvent_model->count_many2many_retrodatalifeevent( $company_id, $list, $max_coveragestartdate );

            if ( $min_count != $max_count )
            {
                $tiers = $CI->LifeEvent_model->select_coveragetier_description($list);
                $CI->LifeEvent_model->insert_lifeevent_warning( $company_id, $list );
            }

            // Remove adjustments specific to this many to many item using the MAX date.
            $CI->Adjustment_model->update_automatic_adjustments_life_event_many2_many($company_id, $max_coveragestartdate, $list);

        }
    }
    private function get_plan_anniversary_plan_types($company_id) {

        // get_plan_anniversary_plan_types
        //
        // Review the unique plantypes for this company and import that have
        // a plan anniversary month set.  Calculate the plan anniversary
        // date for each of them and then return the collection.
        // ------------------------------------------------------------------

        $CI = $this->ci;

        // Find all plantypes that have the plan anniversary month set.
        $import_date = GetUploadDate($company_id);
        $items = $CI->Adjustment_model->select_automatic_adjustments_plan_anniversary_groupings($company_id);

        foreach($items as &$item)
        {
            // Calculate the plan anniversary date for this plantype.
            $pa_month = getArrayStringValue("PlanAnniversaryMonth", $item);
            $plan_anniversary_date = $this->_calculate_plan_anniversary_date($import_date, $pa_month);
            $item["PlanAnniversaryDate"] = $plan_anniversary_date;
        }
        return $items;
    }
    private function _calculate_plan_anniversary_date($import_date, $pa_month) {

        // _calculate_plan_anniversary_date
        //
        // The Plan Anniversary Date is the date in which the plan anniversary
        // last happened in relation to the import date.  This, the PAD is
        // always equal to or less than the import date.
        // ------------------------------------------------------------------

        $import_year = date('Y',strtotime($import_date));
        $pa_date = date('m/d/Y', strtotime("{$pa_month}/01/{$import_year}"));

        // If the calculated PA-DATE is in the future compared to our import
        // date, the kick the PA-DATE back to the previous year.
        if ( strtotime($pa_date) > strtotime($import_date) )
        {
            $pa_year = date('Y', strtotime($pa_date));
            $pa_previous_year = getIntValue($pa_year) - 1;
            $pa_date = date('m/d/Y', strtotime("{$pa_month}/01/{$pa_previous_year}"));
        }
        return $pa_date;

    }
    private function apply_automatic_adjustment( $type, $company_id, $retro_id, $target_date, $parent_retro_id=null ) {

        $CI = $this->ci;

        // Grab the data that tells us what we are trying to retro fit.
        $retro_data = $CI->Retro_model->select_retro_data_by_id($company_id, $retro_id);

        // Narrow Adjustments
        // There is not a coverage tier change in play.  That means the retro data has
        // all of the specific data that we need to do the adjustment.  Roll back just those items.
        $carrier_id = getArrayStringValue("CarrierId", $retro_data);
        $plantype_id = getArrayStringValue("PlanTypeId", $retro_data);
        $plan_id = getArrayStringValue("PlanId", $retro_data);
        $coverage_tier_id = getArrayStringValue("CoverageTierId", $retro_data);
        $life_id = getArrayStringValue("LifeId", $retro_data);
        $adjustment_type = getArrayStringValue("AdjustmentType", $retro_data);

        switch ($type) {
            case ADJUST_RECALCULATED_CHARGE:
                $this->_adjustForRecalculatedCharge($company_id, $retro_id, $target_date, $carrier_id, $plantype_id, $plan_id, $coverage_tier_id, $life_id, $adjustment_type, $parent_retro_id);
                break;
            case ADJUST_PRIOR_CHARGE:
                $this->_adjustForPriorCharge($company_id, $retro_id, $target_date, $carrier_id, $plantype_id, $plan_id, $coverage_tier_id, $life_id, $adjustment_type, $parent_retro_id);
                break;
            case ADJUST_PRIOR_ADJUSTMENTS:
                $this->_adjustForPriorAdjustment($company_id, $retro_id, $target_date, $carrier_id, $plantype_id, $plan_id, $coverage_tier_id, $life_id, $adjustment_type, $parent_retro_id);
                break;
            default:
                throw new Exception("I don't know how to apply an automatic adjustment of type [{$type}]");
                break;
        }


    }

    private function _adjustForRecalculatedCharge($company_id, $retro_id, $target_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $life_id, $adjustment_type, $parent_retro_id=null) {
        $CI = $this->ci;

        $message = " Caluclating Prior Charges: ";
        $message .= "company_id[{$company_id}], ";
        $message .= "retro_id[{$retro_id}], ";
        $message .= "target_date[{$target_date}], ";
        $message .= "carrier_id[{$carrier_id}], ";
        $message .= "plantype_id[{$plantype_id}], ";
        $message .= "plan_id[{$plan_id}], ";
        $message .= "coveragetier_id[{$coveragetier_id}], ";
        $message .= "life_id[{$life_id}], ";
        $message .= "adjustment_type[{$adjustment_type}]";
        $this->debug("{$message}");

        $import_date = GetUploadDate($company_id);
        $target_data = $CI->Retro_model->select_importdata_by_life( $company_id, $import_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $life_id );

        // Revert the existing charge using an adjustment.  ( only if there was one a charge )
        if ( ! empty($target_data) )
        {
            $volume = GetArrayFloatValue("Volume", $target_data);
            $monthly_cost = GetArrayStringValue("MonthlyCost", $target_data);

            $first_name = getArrayStringValue("MemoFirstName", $target_data);
            $last_name = getArrayStringValue("MemoLastName", $target_data);
            $middle_name = getArrayStringValue("MemoMiddleName", $target_data);
            $employee_id = getArrayStringValue("MemoEmployeeId", $target_data);
            $memo_import_date = $CI->Retro_model->get_date_MMM_YYYY( $import_date );
            $memo_target_date = $CI->Retro_model->get_date_MMM_YYYY( $target_date );

            $first_name = A2PDecryptString($first_name, $this->encryption_key);
            $last_name = A2PDecryptString($last_name, $this->encryption_key);
            $middle_name = A2PDecryptString($middle_name, $this->encryption_key);
            $employee_id = A2PDecryptString($employee_id, $this->encryption_key);

            $type = "Credit";
            if ( $monthly_cost >= 0 ) $type = "Debit";
            $memo = "Adjustment {$type} for {$memo_import_date}, {$last_name}, {$first_name} {$middle_name} [{$employee_id}], Adding recalculated charge for {$memo_target_date}.";

            $this->debug("  ADJUSTED-1:{$memo} [{$monthly_cost}]");
            $CI->Retro_model->insert_automatic_adjustment( $company_id, $retro_id, $life_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $adjustment_type, $volume, $monthly_cost, $target_date, $memo, $parent_retro_id  );

        }
    }

    private function _adjustForPriorCharge($company_id, $retro_id, $target_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $life_id, $adjustment_type, $parent_retro_id=null) {
        $CI = $this->ci;

        $message = " Reviewing Prior Charges: ";
        $message .= "company_id[{$company_id}], ";
        $message .= "retro_id[{$retro_id}], ";
        $message .= "target_date[{$target_date}], ";
        $message .= "carrier_id[{$carrier_id}], ";
        $message .= "plantype_id[{$plantype_id}], ";
        $message .= "plan_id[{$plan_id}], ";
        $message .= "coveragetier_id[{$coveragetier_id}], ";
        $message .= "life_id[{$life_id}], ";
        $message .= "adjustment_type[{$adjustment_type}]";
        $this->debug("{$message}");

        $target_data = $CI->Retro_model->select_importdata_by_life( $company_id, $target_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $life_id );

        // Revert the existing charge using an adjustment.  ( only if there was one a charge )
        if ( ! empty($target_data) )
        {
            $volume = GetArrayFloatValue("Volume", $target_data) * -1;
            $monthly_cost = GetArrayStringValue("MonthlyCost", $target_data) * -1;

            $first_name = getArrayStringValue("MemoFirstName", $target_data);
            $last_name = getArrayStringValue("MemoLastName", $target_data);
            $middle_name = getArrayStringValue("MemoMiddleName", $target_data);
            $employee_id = getArrayStringValue("MemoEmployeeId", $target_data);
            $memo_import_date = $CI->Retro_model->get_date_MMM_YYYY( GetUploadDate($company_id) );
            $memo_target_date = getArrayStringValue("MemoTargetDate", $target_data);

            $first_name = A2PDecryptString($first_name, $this->encryption_key);
            $last_name = A2PDecryptString($last_name, $this->encryption_key);
            $middle_name = A2PDecryptString($middle_name, $this->encryption_key);
            $employee_id = A2PDecryptString($employee_id, $this->encryption_key);

            $type = "Credit";
            if ( $monthly_cost >= 0 ) $type = "Debit";
            $memo = "Adjustment {$type} for {$memo_import_date}, {$last_name}, {$first_name} {$middle_name} [{$employee_id}], Reverting original charge applied on {$memo_target_date}.";

            $this->debug("  ADJUSTED-2:{$memo} [{$monthly_cost}]");
            $CI->Retro_model->insert_automatic_adjustment( $company_id, $retro_id, $life_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $adjustment_type, $volume, $monthly_cost, $target_date, $memo, $parent_retro_id  );

        }
    }
    private function _adjustForPriorAdjustment($company_id, $retro_id, $target_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $life_id, $adjustment_type, $parent_retro_id) {

        // _adjustForPriorAdjustment
        //
        // Given all of the data details for this prior adjustment, revert the
        // adjustment by adding new inverse records and creating a memo that
        // explains what was done and why.
        // ------------------------------------------------------------------

        $message = " Reviewing Prior Adjustments: ";
        $message .= "company_id[{$company_id}], ";
        $message .= "retro_id[{$retro_id}], ";
        $message .= "target_date[{$target_date}], ";
        $message .= "carrier_id[{$carrier_id}], ";
        $message .= "plantype_id[{$plantype_id}], ";
        $message .= "plan_id[{$plan_id}], ";
        $message .= "coveragetier_id[{$coveragetier_id}], ";
        $message .= "life_id[{$life_id}], ";
        $message .= "adjustment_type[{$adjustment_type}]";
        $this->debug("{$message}");

        $CI = $this->ci;
        $adjustments = $CI->Retro_model->select_adjustments_by_target_date_for_life( $company_id, $target_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $life_id );

        // Revert the adjustments.
        foreach($adjustments as $adjustment) {
            $volume = getArrayFloatValue("Volume", $adjustment) * -1;
            $monthly_cost = getArrayStringValue("MonthlyCost", $adjustment) * -1;

            //$retro_id = getArrayStringValue("Id", $adjustments);
            //$adjustment_type = getArrayStringValue("AdjustmentType", $adjustments);

            $first_name = getArrayStringValue("FirstName", $adjustment);
            $last_name = getArrayStringValue("LastName", $adjustment);
            $middle_name = getArrayStringValue("MiddleName", $adjustment);
            $employee_id = getArrayStringValue("EmployeeId", $adjustment);
            $memo_import_date = $CI->Retro_model->get_date_MMM_YYYY( GetUploadDate($company_id) );
            $memo_target_date = getArrayStringValue("MemoTargetDate", $adjustment);

            $first_name = A2PDecryptString($first_name, $this->encryption_key);
            $last_name = A2PDecryptString($last_name, $this->encryption_key);
            $middle_name = A2PDecryptString($middle_name, $this->encryption_key);
            $employee_id = A2PDecryptString($employee_id, $this->encryption_key);

            $type = "Credit";
            if ( $monthly_cost >= 0 ) $type = "Debit";
            $memo = "Adjustment {$type} for {$memo_import_date}, {$last_name}, {$first_name} {$middle_name} [{$employee_id}], Reverting prior adjustment applied on {$memo_target_date}.";

            $this->debug("  ADJUSTED-3:{$memo} [{$monthly_cost}]");
            $CI->Retro_model->insert_automatic_adjustment( $company_id, $retro_id, $life_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $adjustment_type, $volume, $monthly_cost, $target_date, $memo, $parent_retro_id  );
        }


    }

    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    private function _isWashedOut($company_id, $wash_rule, $start_date, $end_date, $target_date ) {

        $CI = $this->ci;

        // Convert the target month and then the associated dates into PHP timestamps so we can do
        // compares against them.
        $next_month 	= strtotime($CI->Retro_model->get_next_month($target_date));
        $mid_month 		= strtotime($CI->Retro_model->get_mid_month($target_date));  // 16th of the month.
        $target_date 	= strtotime($target_date);
        $almost_next_month = strtotime('-1 day', $next_month);  // Last day of the month.

        // Ensure the start and end date are the empty string if null.
        $start_date 	= getStringValue($start_date);
        $end_date 		= getStringValue($end_date);

        // Turn the start date, into a PHP time number so we can do compairs.
        // Note: reformat YYYY-MM-DD to MM/DD/YYYY
        if ( $start_date != "" ) {
            if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$start_date))
            {
                $start_date = fBetween($start_date, "-", "-") . "/" . fRightBack($start_date, "-") . "/" . fLeft($start_date, "-");
            }
            $start_date = strtotime($start_date);
        }

        // Turn the end date, into a PHP time number so we can do compairs.
        // Note: reformat YYYY-MM-DD to MM/DD/YYYY
        if ( $end_date != "" ) {
            if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$end_date))
            {
                $end_date = fBetween($end_date, "-", "-") . "/" . fRightBack($end_date, "-") . "/" . fLeft($end_date, "-");
            }
            $end_date = strtotime($end_date);
        }

        if ( $this->debug )
        {
            $d_start_date = ( getStringValue($start_date) == "" ) ? "" : date("m/d/Y", $start_date);
            $d_target_date = ( getStringValue($target_date) == "" ) ? "" : date("m/d/Y", $target_date);
            $d_end_date = ( getStringValue($end_date) == "" ) ? "" : date("m/d/Y", $end_date);
            $d_next_month = ( getStringValue($next_month) == "" ) ? "" : date("m/d/Y", $next_month);
            $this->debug ( " IsWashedOut: start_date[{$d_start_date}], target_date[{$d_target_date}], end_date[{$d_end_date}], next_month[{$d_next_month}]");
        }


        // Calculate if we should be washed out or not.
        if ( $wash_rule == WASH_RULE_1ST )
        {
            // Coverage ends before it starts.
            if ( $start_date != "" && $end_date != "" && $end_date < $start_date ) return true;

            // If person has a start date before the month and a terminate date after the month, count them.
            if ( $start_date < $target_date && $end_date == "" ) return false;
            if ( $start_date < $target_date && $end_date >= $next_month ) return false;

            // If a person has a start date within the month and a terminate date of after the month, count them.
            if ( $start_date >= $target_date && $start_date < $next_month && $end_date == "" ) return false;
            if ( $start_date >= $target_date && $start_date < $next_month && $end_date >= $next_month ) return false;

            // If a person has a start date before the month and a terminate date that is the last day of the current month, count them.
            if ( $start_date < $target_date && $end_date > $target_date && $end_date == $almost_next_month ) return false;
        }
        else if ( $wash_rule == WASH_RULE_15TH )
        {
            // Coverage ends before it starts.
            if ( $start_date != "" && $end_date != "" && $end_date < $start_date ) return true;

            // If a person has a start date before the month and a terminate date after the month, count them.
            // If a person has a start date on or before the 15th of the month and a terminate date after the 15th of the month, count them.
            if ( $start_date < $mid_month && $end_date == "" ) return false;
            if ( $start_date < $mid_month && $end_date >= $mid_month ) return false;

            // If a person has a start date of the 16th or after and a terminate date after the month, do not count them.
            if ( $start_date >= $mid_month && $end_date == "" ) return true;
            if ( $start_date >= $mid_month && $end_date >= $next_month ) return true;

            // If a person has a start date before the month and a terminate date on or before the 15th of the month, do not count them.
            if ( $start_date < $target_date && $end_date < $mid_month ) return true;

            // If a person has a start date before the month and a terminate date on the 16th or after, count them.
            if ( $start_date < $target_date && $end_date == "" ) return false;
            if ( $start_date < $target_date && $end_date >= $mid_month ) return false;
        }

        return true;
    }

}
