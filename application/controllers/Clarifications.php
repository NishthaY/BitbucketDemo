<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clarifications extends SecureController {

	function __construct(){
		parent::__construct();

	}


    // SCREENS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function index() {

        try
        {

            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");

			// Do not allow the user to 'jump ahead' in the wizard.
			if ( ! IsStartupStepComplete() ) redirect( base_url() );
            if ( ! IsUploadStepComplete() ) redirect( base_url() );
            if ( ! IsMatchStepComplete() ) redirect( base_url() . "wizard/match" );
            if ( ! IsCorrectStepComplete() ) redirect( base_url() . "wizard/correct" );
			if ( ! IsRelationshipStepComplete() ) redirect( base_url() . "wizard/navigate/relationships" );
            if ( ! IsLivesStepComplete() ) redirect( base_url() . "wizard/navigate/lives" );
            if ( ! IsPlanReviewStepComplete() ) redirect( base_url() . "wizard/navigate/plans" );

            $company_id = GetSessionValue("company_id");
			$user_id = GetSessionValue("user_id");
			$import_date = GetUploadDate($company_id);

			// Initialize the controller for this company.
			$this->init($company_id);

            // Business Logic Goes Here.
			$page_header = new UIFormHeader();
			$page_header->setTitle("Data Clarifications");
			$page_header = $page_header->render();

			// If you view this page, you have "incompleted" the workflow step.
			$this->Wizard_model->clarifications_step_incomplete($company_id);

			$data = $this->LifeEvent_model->select_all_retrodatalifeevent($company_id);
			$data = A2PDecryptArray($data, $this->encryption_key);
			$data = ArrayRemoveKeyStartWith("Encrypted", $data);

            $view_array = array();
			$view_array = array_merge($view_array, array("data"=>$data));

			// Generate the form.
			$form = new UIWizardForm("clarifications_form");
			$form->setAction(base_url("clarifications/continue"));
			$form->addTopWizardButton($form->button("clarifications_complete_button", "Continue", "btn-working", true));
			$form->addTopWizardButton($form->button("wizard_start_over_btn", "Start Over", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/cancel"))));
			$form->addTopWizardButton($form->button("wizard_start_over_btn", "Match Columns", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/rematch"))));
			if ( HasRelationship($company_id) ) $form->addTopWizardButton($form->button("wizard_relationship_btn", "Relationships", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/navigate/relationships"))));
			if ( HasLivesToCompare($company_id) ) $form->addTopWizardButton($form->button("wizard_lives_btn", "Lives", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/navigate/lives"))));
			$form->addTopWizardButton($form->button("wizard_start_over_btn", "Plan Settings", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/navigate/plans"))));
			$form->addElement($form->top_buttons());
			$form->addElement($form->htmlView("clarifications/review", $view_array));
			$form = $form->render();

			// Render the Page Template with our content.
			$template_array = array();
			$template_array = array_merge($template_array, array("page_header" => $page_header));
			$template_array = array_merge($template_array, array("form" => $form));
            $template_array = array_merge($template_array, array("month" => FormatDateMonth($import_date)));
            $template_array = array_merge($template_array, array("company_id" => $company_id));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("clarifications/js_assets")));
            $page_template = array_merge($page_template, array("view" => "clarifications/wizard_controls"));
            $page_template = array_merge($page_template, array("view_array" => $template_array));
            RenderView('templates/template_body_default', $page_template);


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }

    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	public function clarifications_save() {
		try {

			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission company_write.");


			// Organize our data
			$company_id = GetSessionValue("company_id");
			$name = getArrayStringValue("name", $_POST);
			$value = getArrayStringValue("value", $_POST);
			$type = fLeft($name, "-");
			$id = fRight($name, "-");
			$import_date = GetUploadDate($company_id);


			// Validate our data
			if ( $company_id == "" ) throw new Exception("Missing required input company_id.");
			if ( $name == "" ) throw new Exception("Missing required input name.");
			if ( $value == "" ) throw new Exception("Missing required value name.");
			if ( $id == "" ) throw new Exception("Invalid clarification id.");
			if ( strtolower($type) != "lifeevent" ) throw new Exception("Invalid clarification type.");
			if ( strtolower($value) != "yes" && strtolower($value) != "no") throw new Exception("Invalid value.");

			// What is the existing value?
            $data = $this->LifeEvent_model->select_retrodatalifeevent_by_id( $id, $company_id, $import_date );
            $existing = GetArrayStringValue('LifeEvent', $data);
            if ( $existing === 'TRUE' ) $existing = 't';
            if ( $existing === 'FALSE' ) $existing = 'f';

			// Save the user's values.
			strtolower($value) == "yes" ? $value = "t" : $value = "f";
			$this->LifeEvent_model->update_retrodatalifeevent( $company_id, $id, $value );

			// Remove the "default" tag, if there was one applied indicating this was defaulted by the system
            // and not a human since a human just interacted with it.  Oh, and remove any warnings associated
            // with it too, if there were any.  Only do this if the use's election caused a change in the data.
            if ( $value !== $existing )
            {
                $this->LifeEvent_model->set_default_type_off( $company_id, $import_date, $id );
                $this->LifeEvent_model->remove_clarification_warning( $company_id, $import_date, $id, 'default');
            }


			AJAXSuccess("Clarification saved.");
		}
		catch ( UIException $e ) { AJAXDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
		//catch( Exception $e ) { pprint_r($e->getMessage()); exit; }
	}
	public function clarifications_continue() {
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission company_write.");

			$user_id = GetSessionValue("user_id");
			$company_id = GetSessionValue("company_id");
			$companyparent_id = GetCompanyParentId($company_id);

			// VALIDATE
			// Has all Clarification records been saved?
			if ( HasClarificationsYetToReview($company_id) ) throw new UIException("Unable to continue.  Please provide clarifications for each item shown.");


			// APPLY
            // Any clarifications that have been elected, that were not autoselcted, are now applied to the LifeEventCompare
            // table for processing.  This applies not only manual entries, but also auto selected entries.
			$this->LifeEvent_model->delete_lifeeventcompare($company_id);
			$this->LifeEvent_model->insert_lifeeventcompare($company_id);

			// Audit this transaction.
            AuditIt("Reviewed data clarifications.", array());

			// MOVE ON.
			// The Clarifictions step is complete.  The next step in the workflow
			// is generate reports.  Make sure the report generation step is incomplete!
            $this->Wizard_model->report_generation_incomplete($company_id);
			$this->Wizard_model->clarifications_step_complete($company_id);
			$this->Queue_model->add_worker_job($companyparent_id, $company_id, $user_id, "GenerateReports", "index");
			AJAXSuccess("", base_url("dashboard"));

		}
		catch ( UIException $e ) { AJAXDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}




	// VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	// RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

}
