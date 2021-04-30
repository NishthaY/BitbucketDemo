<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Relationships extends SecureController {

	function __construct(){
		parent::__construct();

        // Protect against multiple users working the wizard at the same
        // time.  If the wizard state no longer matches this step, push
        // them to the dashboard.
        if ( IsRelationshipStepComplete() )
        {
            redirect( base_url("dashboard") );
        }

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

            $company_id = GetSessionValue("company_id");
			$user_id = GetSessionValue("user_id");

			//if ( HasRelationship($company_id) ) pprint_R("HasRelationship: TRUE");
			//if ( ! HasRelationship($company_id) ) pprint_R("HasRelationship: FALSE");
			//if ( AllRelationshipsMapped($company_id) ) pprint_R("AllRelationshipsMapped: TRUE");
			//if ( ! AllRelationshipsMapped($company_id) ) pprint_R("AllRelationshipsMapped: FALSE");

			// DATA COLLECTION
			// All unique relationships found in import file.
			$relationships = $this->Relationship_model->select_relationships_for_import($company_id);

			// dropdown structure containing the relationships a2p support.
			$dropdown = $this->Relationship_model->select_relationship_dropdown();

			// Child Record Pricing Model.
			$pricing_model = $this->Company_model->get_company_preference($company_id, "relationships", "dependent_pricing_model");
			( empty($pricing_model) ) ? $pricing_model = "" : $pricing_model = getArrayStringValue("value", $pricing_model);

			$page_header = new UIFormHeader();
			$page_header->setTitle("Review Relationships");
			$page_header = $page_header->render();

			$view_array = array();
			$view_array = array_merge($view_array, array("data" => $relationships));
			$view_array = array_merge($view_array, array("dropdown" => $dropdown));
			$view_array = array_merge($view_array, array("pricing_model" => $pricing_model));
			$view_array = array_merge($view_array, array("pref_url" => base_url("company/preference/save")));
			$view_array = array_merge($view_array, array("pref_group" => "relationships"));
			$view_array = array_merge($view_array, array("pref_groupcode" => "dependent_pricing_model"));

			// Generate the form.
			$relationship_form = new UIWizardForm("relationships_page_form");
			$relationship_form->setAction(base_url("relationships/continue"));
			$relationship_form->addTopWizardButton($relationship_form->button("relationships_complete_button", "Continue", "btn-working", true));
			$relationship_form->addTopWizardButton($relationship_form->button("wizard_start_over_btn", "Start Over", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/cancel"))));
			$relationship_form->addTopWizardButton($relationship_form->button("wizard_start_over_btn", "Match Columns", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/rematch"))));

			$relationship_form->addElement($relationship_form->top_buttons());
			$relationship_form->addElement($relationship_form->htmlView("relationships/review", $view_array));
			$relationship_form = $relationship_form->render();


			// Render the Page Template with our content.
			$template_array = array();
			$template_array = array_merge($template_array, array("page_header" => $page_header));
			$template_array = array_merge($template_array, array("relationship_form" => $relationship_form));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("relationships/js_assets")));
            $page_template = array_merge($page_template, array("view" => "relationships/wizard_controls"));
            $page_template = array_merge($page_template, array("view_array" => $template_array));
            RenderView('templates/template_body_default', $page_template);


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }

    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	public function relationships_save() {
		try {
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission company_write.");

			// Organize our data
			$company_id = GetSessionValue("company_id");
			$company_relationship_id = getArrayStringVAlue("id", $_POST);
			$relationship_code = getArrayStringVAlue("code", $_POST);

			// Validate our data
			if ( $company_relationship_id == "" ) throw new Exception("Missing required input id.");
			if ( $relationship_code == "" ) throw new Exception("Missing required input code.");

			// Save the mapping.
			$this->Relationship_model->update_company_relationship($company_id, $company_relationship_id, $relationship_code);

			AJAXSuccess("Relationship mapping saved.");
		}
		catch( Exception $e ) { AJAXSuccess("Relationship mapping not saved."); }
	}
	public function relationships_continue() {
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



			// SAVE.
			// Save all of the data on the relationship page. Yes, it might have already
			// been saved as they interacted with screen, but if we defaulted something for them, maybe not.
			foreach($_POST as $key=>$value)
			{
				$key = getStringValue($key);
				$value = getStringValue($value);

				if ( $value == "" ) throw new UIException("Please select the relationship type for all items marked with a question mark indicator.");

				if ( strpos($key, "selected_value") !== FALSE)
				{
					$company_relationship_id = fLeft($key, "_");
					$relationship_code = $value;
					$this->Relationship_model->update_company_relationship($company_id, $company_relationship_id, $relationship_code);
				}
			}

			$pricing_model = getArrayStringValue("dependent_pricing_model", $_POST);
			if ( $pricing_model != "" )
			{
				$this->Company_model->save_company_preference(  $company_id, "relationships", "dependent_pricing_model", $pricing_model );
			}
			else
			{
				throw new UIException("Please select the pricing model for dependent records.");
			}

			// VALIDATE
			// Ensure all relationships are mapped and we have the pricing model saved.
			if ( ! AllRelationshipsMapped($company_id) ) throw new UIException("Unable to save your data.  Please try again.");
			if ( ! IsRelationshipPricingModelSet($company_id) ) throw new UIExceptin("Unable to save your pricing model.  Please try again.");


			// Audit this transaction.
            AuditIt("Reviewed relationships.", array());

			// MOVE ON.
			// The RELATIONSHIP step is complete.  The next step in the workflow
			// is the Plan Settings step.  This might already be complete or it
			// might not.  Decide which is the case and either move to Plan Settings
			// or move to the step after it.
			$this->Wizard_model->relationship_step_complete($company_id);
			if ( ! IsLivesStepComplete() )
			{
				// Move to the Plan Settings screen.
				AJAXSuccess("", base_url("wizard/review/lives"));
			}
			else if ( ! IsPlanReviewStepComplete() )
			{
				// Move to the Plan Settings screen.
				AJAXSuccess("", base_url("wizard/review/plans"));
			}
			else{
				// Plan Settings is complete, take the Plan Settings complete action.
				$this->Queue_model->add_worker_job($companyparent_id, $company_id, $user_id, "GenerateReports", "index");
				AJAXSuccess("", base_url("dashboard"));
			}


		}
		catch ( UIException $e ) { AJAXDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
		//catch( Exception $e ) { pprint_r($e->getMessage()); exit; }
	}




	// VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	// RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

}
