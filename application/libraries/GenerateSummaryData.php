<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateSummaryData extends A2PLibrary {

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

            // Generate Summary Data
			// For every carrier that has data not washed out, create
			$CI->Reporting_model->delete_summary_data($company_id);
            $CI->Reporting_model->delete_summary_data_premium_equivalent($company_id);
			$carriers = $CI->Reporting_model->select_summary_report_carriers($company_id);
            $this->debug(" Looking for all carriers that we need to include in the summary report data.");
			foreach($carriers as $item)
			{
				$carrier_id = getArrayIntValue("CarrierId", $item);
				$CI->Reporting_model->insert_summary_data($company_id, $carrier_id);
                $this->debug(" Adding control records for carrier [".getArrayStringValue("CarrierDescription", $item)."]");
			}
            $this->timer(" Looking for all carriers that we need to include in the summary report data.");

			// Summary Data: Add the AgeBand Catch All records, if needed.
            $this->debug(" Adding AgeBand catch all record, if needed");
			$data = $CI->Reporting_model->select_summary_data_banded( $company_id );
			foreach($data as &$item) {
				$carrier_id = getArrayStringValue("CarrierId", $item);
				$plantype_id = getArrayStringValue("PlanTypeId", $item);
				$plan_id = getArrayStringValue("PlanId", $item);
				$coveragetier_id = getArrayStringValue("CoverageTierId", $item);
				$ageband_id = getArrayStringValue("AgeBandId", $item);
				$tobacco_user = getArrayStringValue("TobaccoUser", $item);
				$exists = $CI->Reporting_model->does_summary_data_ageband_catch_all_record_exist( $company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $tobacco_user);
				if ( $exists != "t" )
				{
					$CI->Reporting_model->insert_summary_data_record( $company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, null, $tobacco_user );
				}
			}
            $this->timer(" Adding AgeBand catch all record, if needed");

			// Summary Data: Add the Tobacco User Catch All records, if needed.
            $this->debug(" Adding Tobacco catch all record, if needed");
			$data = $CI->Reporting_model->select_summary_data_tobacco_user( $company_id );
			foreach($data as &$item) {
				$carrier_id = getArrayStringValue("CarrierId", $item);
				$plantype_id = getArrayStringValue("PlanTypeId", $item);
				$plan_id = getArrayStringValue("PlanId", $item);
				$coveragetier_id = getArrayStringValue("CoverageTierId", $item);
				$ageband_id = getArrayStringValue("AgeBandId", $item);
				$tobacco_user = getArrayStringValue("TobaccoUser", $item);
				$exists = $CI->Reporting_model->does_summary_data_tobacco_user_catch_all_record_exist( $company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $ageband_id);
				if ( $exists != "t" )
				{
					$CI->Reporting_model->insert_summary_data_record( $company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $ageband_id, null);
				}
			}
            $this->timer(" Adding Tobacco catch all record, if needed");

			// Summary Data: Calculate the lives, volume and premium for each unique summary data record.
            $this->debug(" Calculating the lives, volume and premium for each unique summary data record.");
			$data = $CI->Reporting_model->select_summary_data($company_id);
            if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));
			foreach($data as $item)
			{

				$summarydata_id = getArrayStringValue("Id", $item);
				$carrier_id = getArrayStringValue("CarrierId", $item);
				$plantype_id = getArrayStringValue("PlanTypeId", $item);
				$plan_id = getArrayStringValue("PlanId", $item);
				$coveragetier_id = getArrayStringValue("CoverageTierId", $item);
				$ageband_id = getArrayStringValue("AgeBandId", $item);
				$tobacco_user = getArrayStringValue("TobaccoUser", $item);
                $this->debug(" select_summary_data_totals [{$company_id}][{$carrier_id}][{$plantype_id}][{$plan_id}][{$coveragetier_id}][{$ageband_id}][{$tobacco_user}]");
				$totals = $CI->Reporting_model->select_summary_data_totals($company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $ageband_id, $tobacco_user);
                $this->timer(" select_summary_data_totals [{$company_id}][{$carrier_id}][{$plantype_id}][{$plan_id}][{$coveragetier_id}][{$ageband_id}][{$tobacco_user}]");
                if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));

				$lives = getArrayStringValue("Lives", $totals);
				$premium = getArrayStringValue("Premium", $totals);
				$volume = getArrayStringValue("Volume", $totals);
                $this->debug(" update_summary_data_totals");
				$CI->Reporting_model->update_summary_data_totals( $company_id, $summarydata_id, $lives, $volume, $premium );
                if ( getStringValue($this->slowdown !== '' ) ) sleep(getIntValue($this->slowdown));

			}

			// Summary Data: Update summary data to include any automatic adjustments.
            $this->debug(" Adding the automatic adjustment data for the summary data.");
			$CI->Retro_model->update_summary_data_with_automatic_updates($company_id);
            $this->timer(" Adding the automatic adjustment data for the summary data.");

            // Summary Data: Update summary data to include any manual adjustments.
            $this->debug(" Adding the manual adjustment data for the summary data.");
			$data = $CI->Adjustment_model->update_summary_data_with_manual_adjustments($company_id);
            $this->timer(" Adding the manual adjustment data for the summary data.");

            // Premium equivalent
            // Move data from one bucket to another based on the fee relationships.
            $this->_premium_equivalent_data_move($company_id);


            // Summary Data YTD: Removing any spend data for the specified company and import.
            $this->debug(" Removing spend data for specified company and import date.");
            $CI->Spend_model->delete_spend_data($company_id);
            $this->timer(" Removing spend data for specified company and import date.");

            // Summary Data YTD: Adding spend data for the specified company and import.
            $this->debug(" Inserting spend data for specified company and import date.");
            $CI->Spend_model->insert_spend_data($company_id);
            $this->timer(" Inserting spend data for specified company and import date.");



        } catch(Exception $e) {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }

    private function _premium_equivalent_data_move($company_id) {

        // _premium_equivalent_data_move
        //
        // This function will copy all SummaryData records for PlanTypes that
        // have PlanFees associated with them.  Thus, if we generated "Medical ASO"
        // then all "Medical" will be copied to the SummaryDataPremiumEquivalent
        // table.  Once done, we will remove the "Medical" items from the "SummaryData"
        // table.
        // ------------------------------------------------------------------

        $CI = $this->ci;
        $this->debug(" Moving data from the summary table to the premium equivalent table.");

        // Locate all of the PlanTypes that have PlanFees associated with them.
        $results = $CI->PlanFees_model->select_planfee_plantype_relationships( $company_id );
        foreach($results as $relationship)
        {
            // Insert records from SummaryData to SummaryDataPremiumEquivalent for the
            // PlanTypes that have fees.
            $parent_carrier_id = getArrayStringValue($relationship, "CarrierId");
            $parent_plantype_id = getArrayStringValue($relationship, "PlanTypeId");
            $CI->PlanFees_model->insert_premium_equivalent($company_id, $parent_carrier_id, $parent_plantype_id, $parent_carrier_id);

        }
        foreach($results as $relationship)
        {
            // Now that we have copied the data from SummaryData
            // into SummaryDataPremiumEquivalent, we can delete the cooresponding
            // records from SummaryData.  Thus completing the "move".
            $carrier_id = getArrayStringValue($relationship, "CarrierId");
            $plantype_id = getArrayStringValue($relationship, "PlanTypeId");
            $CI->PlanFees_model->delete_related_premium_equivalent_records($company_id, $carrier_id, $plantype_id);
        }

    }
}
