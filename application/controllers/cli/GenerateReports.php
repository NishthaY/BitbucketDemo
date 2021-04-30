<?php defined('BASEPATH') OR exit('No direct script access allowed');

class GenerateReports extends A2PWizardStep {


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
		$this->timers 	= false;
		$this->debug 	= false;

		// Include any shared items.
		$this->load->helper("wizard");

    }
    function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
		// index
        //
		// ---------------------------------------------------------------

        try {

            parent::index($user_id, $company_id, $companyparent_id, $job_id);

            // Record when we start.
            $this->timer("start");

            // Remove Report Warnings
			$this->Reporting_model->delete_report_review_warnings($company_id);

			// Remove Pending Report Records  ( we are about to create them again. )
			$this->Reporting_model->delete_company_report( $company_id );

			// Get our import date.
            $import_date = GetUploadDate($company_id);

            SupportTimerStart($company_id, $import_date, __CLASS__, null);


			// GeneratePlanFees
			// This may create new import records.
			$this->debug("GeneratingPlanFees");
            SupportTimerStart($company_id, $import_date, "GeneratePlanFees", __CLASS__);
			$this->notify_status_update("PLAN_FEES");
			$obj = new GeneratePlanFees();
			$obj->execute($company_id, $user_id);
			SupportTimerEnd($company_id, $import_date, "GeneratePlanFees", __CLASS__);
			$obj = null;

			// Calculate Age
			$this->debug("GeneratingAgeData");
            SupportTimerStart($company_id, $import_date, "GeneratingAgeData", __CLASS__);
            $this->notify_status_update("AGE_DATA");
			$obj = new GenerateAgeData();
			$obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GeneratingAgeData", __CLASS__);
			$obj = null;

			// Generate Washed Data
			$this->debug("GeneratingWashedData");
            SupportTimerStart($company_id, $import_date, "GeneratingWashedData", __CLASS__);
            $this->notify_status_update("WASHING");
			$obj = new GenerateWashedData();
			$obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GeneratingWashedData", __CLASS__);
			$obj = null;

			// Look for Duplicate lives and error if we find some.
            SupportTimerStart($company_id, $import_date, "GenerateDuplicateLivesReport", __CLASS__);
			$this->debug("GenerateDuplicateLivesReport");
            $this->notify_status_update("DUPLICATE_LIVES");
            $obj = new GenerateDuplicateLivesReport();
            $obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GenerateDuplicateLivesReport", __CLASS__);
            $obj = null;

            // Generate Relationship Data
            $this->debug("GeneratingRelationshipData");
            SupportTimerStart($company_id, $import_date, "GeneratingRelationshipData", __CLASS__);
            $this->notify_status_update("RELATIONSHIPS");
            $obj = new GenerateRelationshipData();
            $obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GeneratingRelationshipData", __CLASS__);
            $obj = null;

			// Generate Retro Data
			$this->debug("GeneratingRetroData");
            SupportTimerStart($company_id, $import_date, "GeneratingRetroData", __CLASS__);
            $this->notify_status_update("RETRO_RULES");
			$obj = new GenerateRetroData();
			$obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GeneratingRetroData", __CLASS__);
			$obj = null;

			// Clarifications: Check to see if we have any data clarifications
			// now that we have generated our retro data.
			if ( HasClarificationsYetToReview($company_id) )
			{
				$this->Wizard_model->clarifications_step_incomplete($company_id);
				throw new Exception();
			}else
			{
				$this->Wizard_model->clarifications_step_complete($company_id);
			}

			// Generate Automatic Adjustments
			$this->debug("GeneratingAutomaticAdjustments");
            SupportTimerStart($company_id, $import_date, "GeneratingAutomaticAdjustments", __CLASS__);
            $this->notify_status_update("AUTOMATIC_ADJUSTMENTS");
			$obj = new GenerateAutomaticAdjustments();
			$obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GeneratingAutomaticAdjustments", __CLASS__);
			$obj = null;

			// Generate Summary Data
			$this->debug("GeneratingSummaryData");
            SupportTimerStart($company_id, $import_date, "GeneratingSummaryData", __CLASS__);
            $this->notify_status_update("SUMMARY_DATA");
			$obj = new GenerateSummaryData();
			$obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GeneratingSummaryData", __CLASS__);
			$obj = null;

			// Capture Original Effective Date
            $this->debug("GenerateOriginalEffectiveDate");
            SupportTimerStart($company_id, $import_date, "GenerateOriginalEffectiveDate", __CLASS__);
            $this->notify_status_update("ORIGINAL_EFFECTIVE_DATES");
            $obj = new GenerateOriginalEffectiveDateData();
            $obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GenerateOriginalEffectiveDate", __CLASS__);
            $obj = null;

            // Generate Commissions
            $this->debug("Generating Commission Data");
            SupportTimerStart($company_id, $import_date, "GenerateCommissions", __CLASS__);
            $obj = new GenerateCommissions();
            $this->notify_status_update('COMMISSION_DATA');
            $obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GenerateCommissions", __CLASS__);
            $obj = null;

			// Generate Downloadable Reports.
			$this->debug("GeneratingDownloadableReports");
            SupportTimerStart($company_id, $import_date, "GeneratingDownloadableReports", __CLASS__);
            $this->notify_status_update("BILLING_REPORTS");
			$obj = new GenerateDownloadableReports();
			$obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GeneratingDownloadableReports", __CLASS__);
			$obj = null;

            // Generate A2P Commission Report.
            $this->debug("Generating A2P Commission Report");
            SupportTimerStart($company_id, $import_date, "GenerateCommissionReport", __CLASS__);
            $obj = new GenerateCommissionReport();
            if ( $obj->isEnabled($company_id) ) $this->notify_status_update('GENERATING_A2P_COMMISSION_REPORT');
            $obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GenerateCommissionReport", __CLASS__);
            $obj = null;

            // Generate Downloadable Reports.
            $this->debug("Generating Transamerica Eligibility Import File");
            SupportTimerStart($company_id, $import_date, "GenerateReportTransamericaEligibility", __CLASS__);
            $obj = new GenerateReportTransamericaEligibility();
            if ( $obj->isEnabled($company_id) ) $this->notify_status_update('TRANSAMERICA_ELIGIBILITY_REPORT');
            $obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GenerateReportTransamericaEligibility", __CLASS__);
            $obj = null;

            // Generate Downloadable Reports.
            $this->debug("Generating Transamerica Commissions Import File");
            SupportTimerStart($company_id, $import_date, "GenerateReportTransamericaCommissions", __CLASS__);
            $obj = new GenerateReportTransamericaCommissions();
            if ( $obj->isEnabled($company_id) ) $this->notify_status_update('TRANSAMERICA_COMMISSION_REPORT');
            SupportTimerEnd($company_id, $import_date, "GenerateReportTransamericaCommissions", __CLASS__);
            $obj->execute($company_id, $user_id);
            $obj = null;

            // Generate Downloadable Reports.
            $this->debug("Generating Transamerica Actuarial Import File");
            SupportTimerStart($company_id, $import_date, "GenerateReportTransamericaActuarial", __CLASS__);
            $obj = new GenerateReportTransamericaActuarial();
            if ( $obj->isEnabled($company_id) ) $this->notify_status_update('TRANSAMERICA_ACTUARIAL_REPORT');
            $obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GenerateReportTransamericaActuarial", __CLASS__);
            $obj = null;

            // Generate Warning Report.
            $this->debug("Generating Warning Report");
            SupportTimerStart($company_id, $import_date, "GenerateWarningReport", __CLASS__);
            $obj = new GenerateWarningReport();
            $this->notify_status_update('WARNINGS_REPORT');
            $obj->execute($company_id, $user_id);
            SupportTimerEnd($company_id, $import_date, "GenerateWarningReport", __CLASS__);
            $obj = null;




            $this->notify_status_update('EMPTY_STRING');

			// SNAPSHOTS
            $this->debug("Taking Snapshots");
			TakeSnapshots($company_id, $user_id, $this->encryption_key);

			// Notify user.
            $this->debug("Sending emails.");
			SendDraftReportsGeneratedEmail($user_id, $company_id);

            $this->Wizard_model->report_generation_complete($company_id);
			$this->Wizard_model->adjustment_step_complete($company_id);

            // Record when we start.
            $this->timer("end");

        }
        catch(Exception $e)
        {

            // We need to see this error in the process queue.  Write to STDOUT.
            print $e->getMessage() . "\n";

			// Report the failure to the user.
			SendDraftReportsFailedEmail($user_id, $company_id);

			// Denote we are not done here.
            $this->Wizard_model->report_generation_complete($company_id);
            $this->Wizard_model->adjustment_step_complete($company_id);

            // Record when we end.
            $this->timer("end");
        }

        // Update the UI, notifying anyone watching that this
        // step is complete.
        NotifyStepComplete($company_id);
        SupportTimerEnd($company_id, $import_date, __CLASS__, null);

    }




}

/* End of file GenerateImportFiles.php */
/* Location: ./application/controllers/cli/GenerateImportFiles.php */
