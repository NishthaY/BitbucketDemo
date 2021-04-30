<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wizard extends SecureController {

	function __construct(){
		parent::__construct();

		$this->load->model('User_model','user_model',true);
		$this->load->model('Company_model','company_model',true);
		$this->load->model('Widgettask_model','widgettask_model',true);
		$this->load->model('mapping_model','mapping_model',true);
        $this->load->model('Wizard_model','wizard_model',true);
		$this->load->model('Queue_model','queue_model',true);
		$this->load->model('Reporting_model','reporting_model',true);
		$this->load->model('Life_model','life_model',true);
		$this->load->model('Retro_model','retro_model',true);
		$this->load->helper("wizard");

		ini_set('auto_detect_line_endings',TRUE);

	}


    // SCREENS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-



	// POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    public function notify_workflow_step_changed() {
	    try
        {
            $company_id = GetArrayStringValue("company_id", $_POST);
            if ( GetStringValue($company_id) === '' ) $company_id = GetSessionValue('company_id');

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write,company_write",'company', $company_id) ) throw new SecurityException("Missing required permission.");

            NotifyCompanyChannel($company_id, "workflow_step_changed", array('company_id' => $company_id));
        }
        catch(Exception $e)
        {
            // If this does not work, don't do anything.
        }
        AJAXSuccess();
    }
    public function notify_workflow_step_changing() {
        try
        {
            $company_id = GetArrayStringValue("company_id", $_POST);
            if ( GetStringValue($company_id) === '' ) $company_id = GetSessionValue('company_id');

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write,company_write",'company', $company_id) ) throw new SecurityException("Missing required permission.");

            NotifyCompanyChannel($company_id, "workflow_step_changing", array('company_id' => $company_id));
        }
        catch(Exception $e)
        {
            // If this does not work, don't do anything.
        }
        AJAXSuccess();
    }

	public function cancel() {
		try{

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");


			$company_id = GetSessionValue("company_id");
            $import_date = GetUploadDate($company_id);

            $payload = array();
            $payload['company_id'] = $company_id;
            $payload['import_date'] = $import_date;
			RollbackWizardAttempt($company_id);



			// Audit this transaction.
            $payload = array();
            $payload["UploadDate"] = $import_date;
            AuditIt('Start over.', $payload);

			if ( getStringValue($this->input->server('REQUEST_METHOD')) == "POST" )
			{
				AJAXSuccess("Rollback complete.", base_url("dashboard"));
			}else{
				redirect(base_url("dashboard"));
				exit;
			}


		}
		catch ( UIException $e ) {
			if ( getStringValue($this->input->server('REQUEST_METHOD')) == "POST" )
			{
				AjaxDanger($e->getMessage());
			}else{
				redirect(base_url("dashboard"));
			}
			exit;
		}
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
	}
    public function rematch() {

		// REMEMBER!
		// There are three ways data can be rolled back.  If you are adding
		// additional delete logic, add it in all three places.
		// 1. Companies controller rolls back the most recent attempt, finalized or in progress.
		// 2. Wizard Helper rolls back the most recent wizard attempt which is in progress.
		// 3. Wizard controller allows you to jump back to various places in the wizard.

        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");

			// Do not allow the user to try and re-match unless they are far enough along in the wizard to allow it.
			if ( ! IsStartupStepComplete() ) throw new SecurityException("Re-match not allowed. not started.");
			if ( ! IsUploadStepComplete() )  throw new SecurityException("Re-match not allowed. not uploaded.");

            $company_id = GetSessionValue("company_id");

            // Clear the wizard columns that will allow us to move back to the match step.
            $this->Wizard_model->reset_wizard_to_match($company_id);

            // Audit this transaction
            AuditIt('Re-match columns.', array());

            AJAXSuccess("Ready to re-match.", base_url("wizard/match"));


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }
	public function edit_manual_adjustments() {

		try
        {

            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");


			// Do not allow the user to try and edit plan settings unless they are far enough along in the wizard to allow it.
			if ( ! IsStartupStepComplete() ) throw new SecurityException("Manual adjustments not allowed. not started.");
			if ( ! IsUploadStepComplete() )  throw new SecurityException("Manual adjustments not allowed. not uploaded.");
			if ( ! IsMatchStepComplete() ) throw new SecurityException("Manual adjustments not allowed. not matched.");
            if ( ! IsCorrectStepComplete() ) throw new SecurityException("Manual adjustments not allowed. not corrected.");

			$company_id = GetSessionValue("company_id");
			$this->Wizard_model->reset_wizard_to_adjustments($company_id);
            AJAXSuccess("Moving to Manual Adjustments.", base_url("adjustments"));


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }

	}
	public function edit_relationships() {

		try
        {

            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");


			// Do not allow the user to try and edit plan settings unless they are far enough along in the wizard to allow it.
			if ( ! IsStartupStepComplete() ) throw new SecurityException("Manual adjustments not allowed. not started.");
			if ( ! IsUploadStepComplete() )  throw new SecurityException("Manual adjustments not allowed. not uploaded.");
			if ( ! IsMatchStepComplete() ) throw new SecurityException("Manual adjustments not allowed. not matched.");
            if ( ! IsCorrectStepComplete() ) throw new SecurityException("Manual adjustments not allowed. not corrected.");

			$company_id = GetSessionValue("company_id");
			$this->Wizard_model->reset_wizard_to_relationships($company_id);
            AJAXSuccess("Moving to Relationships.", base_url("relationships"));


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }

	}
	public function edit_lives() {

		try
		{

			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");


			// Do not allow the user to try and edit plan settings unless they are far enough along in the wizard to allow it.
			if ( ! IsStartupStepComplete() ) throw new SecurityException("Life review not allowed. not started.");
			if ( ! IsUploadStepComplete() )  throw new SecurityException("Life review not allowed. not uploaded.");
			if ( ! IsMatchStepComplete() ) throw new SecurityException("Life review not allowed. not matched.");
			if ( ! IsCorrectStepComplete() ) throw new SecurityException("Life review not allowed. not corrected.");

			$company_id = GetSessionValue("company_id");
			$this->Wizard_model->reset_wizard_to_lives($company_id);
			AJAXSuccess("Moving to Lives.", base_url("lives"));


		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404( $e ); }

	}
	public function edit_clarifications() {

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");

			// Do not allow the user to try and edit plan settings unless they are far enough along in the wizard to allow it.
			if ( ! IsStartupStepComplete() ) throw new SecurityException("Life review not allowed. not started.");
			if ( ! IsUploadStepComplete() )  throw new SecurityException("Life review not allowed. not uploaded.");
			if ( ! IsMatchStepComplete() ) throw new SecurityException("Life review not allowed. not matched.");
			if ( ! IsCorrectStepComplete() ) throw new SecurityException("Life review not allowed. not corrected.");

			$company_id = GetSessionValue("company_id");
			$this->Wizard_model->reset_wizard_to_clarifications($company_id);
			AJAXSuccess("Moving to clarifications.", base_url("clarifications"));


		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404( $e ); }

	}
	public function edit_plan_settings() {

		try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");

			// Do not allow the user to try and edit plan settings unless they are far enough along in the wizard to allow it.
			if ( ! IsStartupStepComplete() ) throw new SecurityException("Edit plan settings not allowed. not started.");
			if ( ! IsUploadStepComplete() )  throw new SecurityException("Edit plan settings not allowed. not uploaded.");
			if ( ! IsMatchStepComplete() ) throw new SecurityException("Edit plan settings not allowed. not matched.");
            if ( ! IsCorrectStepComplete() ) throw new SecurityException("Edit plan settings not allowed. not corrected.");

			$company_id = GetSessionValue("company_id");
			$this->Wizard_model->reset_wizard_to_plan_review($company_id);
			$this->Wizard_model->adjustment_step_complete($company_id);
            AJAXSuccess("Moving to Plan Review.", base_url("wizard/review/plans"));


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
	}





    // VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    // RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

}
