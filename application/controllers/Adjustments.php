<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Adjustments extends SecureController {

	function __construct(){
		parent::__construct();
		$this->load->model("Wizard_model", "wizard_model", true);
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

            // Manual Adjustments
			// Add the manual adjustment widget, if needed.
			$manual_adjustment_widget = new UIWidget("manual_adjustment_widget");
			$manual_adjustment_widget->setBody( ManualAdjustmentWidget() );
			$manual_adjustment_widget->setHref(base_url("widgettask/manual_adjustment/ID"));
			$manual_adjustment_widget = $manual_adjustment_widget->render();
			if ( IsAuthenticated("parent_company_write,parent_company_write") ) $manual_adjustment_widget = "";

            $page_header = new UIFormHeader();
            $page_header->setTitle("Manual Adjustments");
            $page_header = $page_header->render();

			$manual_adjustments = $this->Adjustment_model->select_manual_adjustments($company_id);

            // Generate the form.
            $adjustment_form = new UIWizardForm("manual_adjustment_page_form");
            $adjustment_form->setAction(base_url("adjustments/continue"));
            $adjustment_form->addTopWizardButton($adjustment_form->button("adjustments_complete_button", "Continue", "btn-primary", true));
            $adjustment_form->addTopWizardButton($adjustment_form->button("wizard_start_over_btn", "Start Over", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/cancel"))));
			$adjustment_form->addTopWizardButton($adjustment_form->button("wizard_start_over_btn", "Match Columns", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/rematch"))));
			if ( HasRelationship($company_id) ) $adjustment_form->addTopWizardButton($adjustment_form->button("wizard_start_over_btn", "Relationships", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/navigate/relationships"))));
			if ( HasLivesToCompare($company_id) ) $adjustment_form->addTopWizardButton($adjustment_form->button("wizard_start_over_btn", "Lives", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/navigate/lives"))));
			$adjustment_form->addTopWizardButton($adjustment_form->button("wizard_start_over_btn", "Plan Settings", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/navigate/plans"))));
			if ( HasClarifications($company_id) ) $adjustment_form->addTopWizardButton($adjustment_form->button("wizard_start_over_btn", "Clarifications", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/navigate/clarifications"))));
            $adjustment_form->addElement($adjustment_form->top_buttons());
			$add_button = $adjustment_form->renderElement($adjustment_form->button( "add_adjustment_btn", "Add Adjustment", "btn-primary"));
            $adjustment_form->addElement($adjustment_form->adjustment_table( $manual_adjustments, $add_button ));
            $adjustment_form = $adjustment_form->render();



            $view_array = array();
			$view_array = array_merge($view_array, array("manual_adjustment_widget" => $manual_adjustment_widget));
            $view_array = array_merge($view_array, array("page_header" => $page_header));
            $view_array = array_merge($view_array, array("form_html" => $adjustment_form));


            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("adjustments/js_assets")));
            $page_template = array_merge($page_template, array("view" => "adjustments/edit"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }

    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    public function adjustments_continue() {
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

			$this->Wizard_model->report_generation_incomplete($company_id);
			$this->Wizard_model->adjustment_step_complete($company_id);
            $this->Queue_model->add_worker_job($companyparent_id, $company_id, $user_id, "GenerateReports", "index");
            AJAXSuccess("", base_url("dashboard"));

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
	public function save_manual_adjustment() {
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

			// Validate our inputs.
			$carrier_id = getArrayStringValue("carrier_id", $_POST);
			$description = getArrayStringValue("description", $_POST);
			$amount = getArrayStringValue("amount", $_POST);
			$adjustment_type = getArrayStringValue("type_id", $_POST);
			$adjustment_id = getArrayStringValue("adjustment_id", $_POST);
			if ( $carrier_id == "" ) throw new UIException("Unsupported carrier_id.  Please try again later.");
			if ( $description == "" ) throw new UIException("Unsupported description.  Please try again later.");
			if ( strtolower($adjustment_type) != "credit" && strtolower($adjustment_type) != "debit") throw new UIException("Unsupported adjustment_type.  Please try again.");
			if ( $amount == "" ) throw new UIException("Unsupported amount.  Please try again later.");

			// Was the carrier we received valid?
			$known_carrier = false;
			$available_carriers = $this->Reporting_model->select_summary_report_carriers($company_id);
			if ( ! empty($available_carriers) )
			{
				foreach($available_carriers as $carrier)
				{
					if ( getArrayStringValue("CarrierId", $carrier) == $carrier_id )
					{
						$known_carrier = true;
						break;
					}
				}
			}
			if ( ! $known_carrier ) throw new UIException("Unsupported carrier.  Please try again later.");

			// Was the amount we received valid?
			if ( ! preg_match('/^[+-]?[0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]{2})?$/', $amount) ) throw new UIExceptin("Unsupported money value. Please try again later.");

			// Normalize the money value we got.
			if ( strpos($amount, ".") !== FALSE )
			{
				$negative = false;
				if ( strpos($amount, "-") !== FALSE ) $negative = true;
				$left = stripNonNumeric(fLeft($amount, "."));
				$right = stripNonNumeric(fRight($amount, "."));
				if ($left == "" ) $left = "0";
				if ($right == "" ) $right = "";
				$amount = "{$left}.{$right}";
				if ( $negative ) $amount = "-{$amount}";
			}
			else
			{
				$amount = "{$amount}.00";
			}


			// Adjustment Type.
			// Now that we take in a type, we don't care if they specified negative or positive.
			// We will change it to meet their type settings.
			$amount = replaceFor($amount, "-", "");
			if ( $adjustment_type == "credit") $amount = "-{$amount}";


			// Add the adjustment.
			if( getStringValue($adjustment_id) == "" )
			{
				$this->Adjustment_model->insert_manual_adjustment($company_id, $carrier_id, $description, $amount);
            }
			else
			{
				$this->Adjustment_model->update_manual_adjustment($company_id, $adjustment_id, $carrier_id, $description, $amount);
			}


			AJAXSuccess("", base_url("adjustments"));

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	function delete_manual_adjustment() {
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

			// Validate our inputs.
			$adjustment_id = getArrayStringValue("adjustment_id", $_POST);
			if ( $adjustment_id == "" ) throw new UIException("Unsupported adjustment_id.  Please try again later.");

			// Remove the adjustment.
			$this->Adjustment_model->delete_manual_adjustment_by_id($company_id, $adjustment_id);

			AJAXSuccess("", base_url("adjustments"));

		}
		catch ( UIException $e ) { }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { }
	}

	// VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+

	// RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-


}
