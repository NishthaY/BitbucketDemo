<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LoadImportFiles extends A2PWizardStep {


	function __construct()
    {
        // Construct our parent class
        parent::__construct();

        //CLI ONLY! GO AWAY!
        if ( ! $this->input->is_cli_request() ) {
            Error404();
            return;
        }

		// Toggle these for Research and Debugging.
		$this->timers = false;
		$this->debug = false;

		// Include any shared items.
		$this->load->helper("wizard");
		$this->load->helper("plans");
		$this->load->helper("carrier");


    }
    function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
		// index
        //
        //
		// ---------------------------------------------------------------

        try {

            parent::index($user_id, $company_id, $companyparent_id, $job_id);


            // Record when we start.
            $this->timer("start");
            $this->debug("LoadImportFiles: start");

            if ( getStringValue($user_id) == "" ) throw new Exception("Invalid input user_id.");
            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");

            // Get our import date and start our support timer.
            $import_date = GetUploadDate($company_id);
            SupportTimerStart($company_id, $import_date, __CLASS__, null);

			// Before we start.  Let's ensure our previous attempts to save
			// our data have been cleared out of the database.
            $this->debug("remove_import_records");
			$this->Wizard_model->remove_import_records($company_id);

            // Now, import the file you just saved to S3
            $this->debug("EchoBatchImport");
            $this->debug("importing file data into database");
            GetStringValue($companyparent_id) === '' ? $param3 = "NULL" : $param3 = $companyparent_id;
            GetStringValue($company_id) === '' ? $param2 = "NULL" : $param2 = $company_id;
            GetStringValue($user_id) === '' ? $param1 = "NULL" : $param1 = $user_id;
            $command = "php index.php cli/EchoBatchImport index {$user_id} {$company_id} {$companyparent_id} | ./scripts/db_import.sh";
            $results = `$command`;
            if ( StartsWith(trim(strtoupper($results)), 'ABORT') ) throw new Exception($results);


			$this->timer("Database import.");

			// Set any column default values if needed.
            $this->_defaultColumnValues($company_id);

            // AUTO SELECT CARRIER
            // Look over the CompanyCarrier data.  If the user has not yet picked
            // a CarrierCode and we can find a match for them, go ahead and select it.
            $this->_autoSelectCarrier($company_id);

			// Next, let's record any new Carriers that we might have found.
            $this->debug("Create new carriers, plantypes, plans, coveragetiers and company relationships.");
			$this->Reporting_model->save_new_company_carriers($company_id);
			$this->Reporting_model->save_new_company_plan_types($company_id);
			$this->Reporting_model->save_new_company_plans($company_id);
			$this->Reporting_model->save_new_company_coverage_tiers($company_id);
			$this->Relationship_model->save_new_company_relationships($company_id);

            // Create universal employee id values, if needed.
            $this->debug("Generating universal employee id data.");
            SupportTimerStart($company_id, $import_date, "GenerateUniversalEmployeeId", __CLASS__);
            $obj = new GenerateUniversalEmployeeId();
            $obj->execute($company_id);
            SupportTimerEnd($company_id, $import_date, "GenerateUniversalEmployeeId", __CLASS__);
            $obj = null;

			//  Look for new lives and generate our LifeData.
            $this->debug("Generating our life data.");
            SupportTimerStart($company_id, $import_date, "GenerateLifeData", __CLASS__);
			$obj = new GenerateLifeData();
			$obj->execute($company_id);
            SupportTimerEnd($company_id, $import_date, "GenerateLifeData", __CLASS__);
			$obj = null;

			// The save was complete!  Wrap it up.
            $this->debug("Step complete");
            $this->Wizard_model->saving_step_complete($company_id);

			// Relationship Check.
            $this->debug("routing ...");
			if ( ! HasRelationship($company_id) )
			{
				// If there are no relationships to map, then the relationship step is done.
				$this->Wizard_model->relationship_step_complete($company_id);
			}
			else if ( HasRelationship($company_id) && AllRelationshipsMapped($company_id) && IsRelationshipPricingModelSet($company_id) )
			{
				// If all relationship data appears to already be in place skip the step.
				$this->Wizard_model->relationship_step_complete($company_id);
			}

			// Life Compare Check
			if ( ! HasLivesYetToCompare($company_id) )
			{
				// No lives were identified for compare.
				$this->Wizard_model->lives_step_complete($company_id);
			}


			// Plan Check.
			$this->Wizard_model->plan_review_step_incomplete($company_id);
			$payload = GetPlansDataReview( $company_id );
			if ( isset($payload['valid']) && $payload['valid'] === true)
			{
				// YES!  This customer has already supplied all of the plan related
				// data.  Continue on.

				// We are about to jump over plan settings because we already have
				// all the data needed to process the request from a previous run.
				// Archive the plan setting data for production support.
				ArchivePlanSettings($company_id, $user_id);

				// Mark the plan settings complete.
				$this->Wizard_model->plan_review_step_complete($company_id);
			}

			if ( IsPlanReviewStepComplete($company_id) && IsRelationshipStepComplete($company_id) && IsLivesStepComplete($company_id) )
			{
				// No need to stop the workflow.  Continue on with report generation.
                $this->schedule_next_step("GenerateReports");

                // Update the UI, notifying anyone watching that this
                // step is complete.
                NotifyStepComplete($company_id);
                SupportTimerEnd($company_id, $import_date, __CLASS__, null);

                $this->timer("end");

				return;
			}

			// Notify the user that we are ready for them to
			// verify their plan data.
            SendDataValidationCompleteEmail($user_id, $company_id);

            // Record when we start.
            $this->timer("end");

        }catch(Exception $e) {

            // Show why, if in debug.
            if ( $this->debug ) { print "Exception! " . $e->getMessage() . "\n"; }

            // Reset the wizard back to match.  If in development, rollbacks are turned
            // off you will need to reset to match so you can move forward and recover
            // from the error.
            $this->Wizard_model->reset_wizard_to_match( $company_id );

            // We should not get an exception here.  If we do, it might mean a configuration
            // issue so let's create a support ticket with this information.
            CreateSupportTicket($company_id, $user_id, $e->getMessage(), $job_id);

            // Print something to STDOUT here with the appropriate tag so the end
            // user will get the message there was a problem and support was notified.
            print "A2P-INTERNAL: " . $e->getMessage();

            // Record when we end.
            $this->timer("end");
        }

        // Update the UI, notifying anyone watching that this
        // step is complete.
        NotifyStepComplete($company_id);
        SupportTimerEnd($company_id, $import_date, __CLASS__, null);

    }

    /**
     * _autoSelectCarrier
     *
     * Automate the selection of a carrier based on the UserDescription.
     *
     * This function review the CompanyCarrier records for the given company.
     * If the company does not have a Carrier Code yet, this function will
     * compare the UserDescription to the values in the CarrierMapping table.
     * If we have a match, the CompanyCarrier record is updated with the
     * cooresponding Carrier Code.
     * 
     * @param $company_id
     */
    private function _autoSelectCarrier($company_id)
    {
        // Find any CompanyCarrier records that do not have CarrierCodes
        // that have a match against a record in the CarrierMapping table.
        $matches = $this->Company_model->get_company_carrier_best_match_carrier_code($company_id);
        foreach($matches as $match)
        {
            // Update the CompanyCarrier with the best match so the user does not
            // have to make an obvious election.
            $company_carrier_id = GetArrayStringValue("Id", $match);
            $best_match = GetArrayStringValue("BestMatch", $match);
            $this->Company_model->update_company_carrier_code($company_carrier_id, $best_match);

        }
    }

    /**
     * _defaultColumnValues
     *
     * Find all of our "Default Values" for mapping columns.  Search
     * the companies import data that has not been finalized.  Any
     * data cells that are empty in a column that has a default value
     * will be updated from blank to the default value.
     *
     * @param $company_id
     * @throws Exception
     */
    private function _defaultColumnValues($company_id)
    {

        // Find all of the columns that HAVE a default value.  These are specified in the
        // database in the mapping MappingColumns table.  They will have the DefaultValue and
        // DefaultColumn fields filled in.
        $results = $this->Mapping_model->get_mapped_columns_with_default_values();
        foreach($results as $result)
        {
            $column_code = getArrayStringValue("Name", $result);
            $default_value = getArrayStringValue("DefaultValue", $result);
            $column_name = getArrayStringValue("ColumnName", $result);

            if ( $column_name !== '' )
            {
                // STEP 1:  Check the company parent features to see if this particular column
                // has a default value.  If it has the feature, and it is enabled, and a default
                // value has been set, capture the 'parent_default' value.
                $parent_default = "";
                if ( $column_code === 'carrier' )
                {
                    $companyparent_id = GetCompanyParentId($company_id);
                    $parent_default = GetDefaultCarrier( $companyparent_id, 'companyparent');
                }

                // STEP 2:  Check the company features to see if this particular column
                // has a default value.  If it has the feature, and it is enabled, and a default
                // value has been set, capture the 'company_default' value.
                $company_default = "";
                if ( $column_code === 'carrier' )
                {
                    $company_default = GetDefaultCarrier($company_id, 'company');
                }

                // STEP 3: Check to see if the user has specified a column default_value through their
                // interactions with the application.  If we can find one, capture it.
                $pref = $this->Company_model->get_company_preference($company_id, "column_default_value", $column_code);
                $user_default = GetArrayStringValue("value", $pref);

                // Time to decide which default value we are going to use.  Start with the generic
                // default value.  If the parent specified one, we will use that.  They they didn't but the
                // company did, we will use that.  Still nothing?  Okay, then we will use the value specified
                // by the user as they interact with the application.  If we had none of those things then
                // the generic default value is used.
                $value = $default_value;
                if ( $parent_default !== '' ) $value = $parent_default;
                else if ( $company_default !== ''  ) $value = $company_default;
                else if ( $user_default !== '' ) $value = $user_default;

                // Finally, update the ImportData column with the default data.
                $this->Mapping_model->default_mapping_column($company_id, $column_name, $value);
            }
        }
    }

}

/* End of file LoadImportFiles.php */
/* Location: ./application/controllers/cli/LoadImportFiles.php */
