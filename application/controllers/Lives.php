<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lives extends SecureController {

	function __construct(){
		parent::__construct();

        // Protect against multiple users working the wizard at the same
        // time.  If the wizard state no longer matches this step, push
        // them to the dashboard.
        if ( IsLivesStepComplete() )
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
			if ( ! IsRelationshipStepComplete() ) redirect( base_url() . "wizard/navigate/relationships" );

            $company_id = GetSessionValue("company_id");
			$user_id = GetSessionValue("user_id");

			$this->init($company_id);

			$page_header = new UIFormHeader();
			$page_header->setTitle("Review Life Record Updates");
			$page_header = $page_header->render();

			// Collect the data we will show on the page.
			$lookup = array();
			$parents = $this->Life_model->select_companylifecompare_parents($company_id);
			$parents = A2PDecryptArray($parents, $this->encryption_key);
			foreach($parents as $parent)
			{
                $encrypted_eid = getArrayStringValue("EncryptedEmployeeId", $parent);
                $eid = getArrayStringValue("EmployeeId", $parent);
				if ( ! isset($lookup[$eid] ) )
				{
                    $lookup_results = $this->Life_model->select_companylifecompare_children($company_id, $encrypted_eid);
                    //$lookup_results = $this->Life_model->select_companylifecompare_children($company_id, $eid);
                    $lookup_results = A2PDecryptArray($lookup_results, $this->encryption_key);
                    $lookup_results = ArrayRemoveKeyStartWith("Encrypted", $lookup_results);
					$lookup[$eid] = $lookup_results;
				}
			}

			// Do we have SSN data?
			$has_ssn_data = HasSSN($company_id);

            $parents = ArrayRemoveKeyStartWith("Encrypted", $parents);



			$view_array = array();
			$view_array = array_merge($view_array, array("parents" => $parents));
			$view_array = array_merge($view_array, array("lookup" => $lookup));
			$view_array = array_merge($view_array, array("has_ssn" => $has_ssn_data));

			// Generate the form.
			$form = new UIWizardForm("lives_page_form");
			$form->setAction(base_url("lives/continue"));
			$form->addTopWizardButton($form->button("lives_complete_button", "Continue", "btn-working", true));
			$form->addTopWizardButton($form->button("wizard_start_over_btn", "Start Over", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/cancel"))));
			$form->addTopWizardButton($form->button("wizard_start_over_btn", "Match Columns", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/rematch"))));
			if ( HasRelationship($company_id) )
			{
				$form->addTopWizardButton($form->button("wizard_relationship_btn", "Relationships", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/navigate/relationships"))));
			}
			$form->addElement($form->top_buttons());
			$form->addElement($form->htmlView("lives/review", $view_array));
			$form = $form->render();

			// Render the Page Template with our content.
			$template_array = array();
			$template_array = array_merge($template_array, array("page_header" => $page_header));
			$template_array = array_merge($template_array, array("form" => $form));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("lives/js_assets")));
            $page_template = array_merge($page_template, array("view" => "lives/wizard_controls"));
            $page_template = array_merge($page_template, array("view_array" => $template_array));
            RenderView('templates/template_body_default', $page_template);


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }

    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	public function lives_save() {
		try {

			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission company_write.");


			// Organize our data
			$company_id = GetSessionValue("company_id");
			$new_life_flg = getArrayStringValue("new_life_flg", $_POST);
			$life_id = getArrayStringValue("life_id", $_POST);
			$updates_life_id = getArrayStringValue("updates_life_id", $_POST);

			// Validate our data
			if ( $company_id == "" ) throw new Exception("Missing required input company_id.");
			if ( $new_life_flg == "" ) throw new Exception("Missing required input new_life_flg.");
			if ( $life_id == "" ) throw new Exception("Missing required input life_id.");
			if ( $updates_life_id == "" && $new_life_flg == "f" ) throw new Exception("Missing required input updates_life_id.");

			// Save the mapping.
			$this->Life_model->update_companylife( $life_id, $updates_life_id, $company_id );

			AJAXSuccess("Relationship mapping saved.");
		}
		catch ( UIException $e ) { AJAXDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
		//catch( Exception $e ) { pprint_r($e->getMessage()); exit; }
	}
	public function lives_continue() {
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
			// Has all CompanyLifeCompare records been saved?

			$complete = $this->Life_model->select_companylifecompare_is_complete($company_id);
			if ( ! $complete ) throw new UIException("Unable to continue.  Please provide answers for each life.");

			// Audit this transaction
            AuditIt("Reviewed lives.", array());

			// MOVE ON.
			// The LIFE step is complete.  The next step in the workflow
			// is the Plan Settings step.  This might already be complete or it
			// might not.  Decide which is the case and either move to Plan Settings
			// or move to the step after it.
			$this->Wizard_model->lives_step_complete($company_id);
			if ( ! IsPlanReviewStepComplete() )
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
	}




	// VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	// RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

}
