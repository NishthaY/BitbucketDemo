<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Plans extends SecureController {

	function __construct(){
		parent::__construct();

		$this->load->model('Company_model','company_model',true);
		$this->load->model('mapping_model','mapping_model',true);
        $this->load->model('Wizard_model','wizard_model',true);
		$this->load->model('Queue_model','queue_model',true);
		$this->load->model('Ageband_model','ageband_model',true);
		$this->load->model('Tobacco_model','tobacco_model',true);
		$this->load->helper("wizard");
		$this->load->helper("plans");
		$this->load->helper("dashboard");
        $this->load->helper("carrier");
		$this->load->library('form_validation');


		// Protect against multiple users working the wizard at the same
        // time.  If the wizard state no longer matches this step, push
        // them to the dashboard.
        if ( IsPlanReviewStepComplete() )
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
            $companyparent_id = GetCompanyParentId($company_id);
			$user_id = GetSessionValue("user_id");

            // Carrier
            $carrier_widget = new UIWidget("carrier_widget");
            $carrier_widget->setHref(base_url("wizard/review/carrier/edit/CARRIER"));
            $carrier_widget = $carrier_widget->render();

            // Plan Type Mapping
			$plantype_widget = new UIWidget("plantype_widget");
			$plantype_widget->setHref(base_url("wizard/review/plantype/edit/CARRIER/PLANTYPE"));
			$plantype_widget = $plantype_widget->render();

			// Plan Settings
			$plan_widget = new UIWidget("plan_widget");
			$plan_widget->setHref(base_url("wizard/review/plan/edit/CARRIER/PLANTYPE/PLAN"));
			$plan_widget = $plan_widget->render();

			// Agebands
			$ageband_widget = new UIWidget("ageband_widget");
			$ageband_widget->setHref(base_url("wizard/review/ageband/edit/CARRIER/PLANTYPE/PLANTYPECODE/PLAN/TIER"));
			$ageband_widget = $ageband_widget->render();

			// Tobacco Attribute
			$tobacco_widget = new UIWidget("tobacco_widget");
			$tobacco_widget->setHref(base_url("wizard/review/tobacco/edit/CARRIER/PLANTYPE/PLANTYPECODE/PLAN/TIER"));
			$tobacco_widget = $tobacco_widget->render();


			$page_header = new UIFormHeader();
			$page_header->setTitle("Review Plan Types");
			$page_header = $page_header->render();

            // Generate the form for this step.

            // Before we display any data, attempt to default any carriers where applicable.
            $this->_setDefaultCarriers($company_id, $companyparent_id);

			// collect all of the data needed to render this screen.
			$payload = GetPlansDataReview( $company_id );

			if ( ! isset($payload['data'] ) ) throw new UIException("Unable to locate the customers data.");
			if ( ! isset($payload['valid'] ) ) throw new UIException("Unable to locate the customers data.");
			if ( ! isset($payload['warning'] ) ) throw new UIException("Unable to locate the customers data.");


			// Generate the UIWizardForm for this page.  This is the top bit that holds
            // all of the wizard buttons.  The continue button will need to be modified depending on
            // if the data collected is valid or not.
            $continue_disabled = false;
            if ( ! $payload['valid'] ) $continue_disabled = true;

            $continue_class = "btn-primary";
            if ( ! $payload['valid'] ) $continue_class = "btn-working";

            $page_form = new UIWizardForm("page_form");
            $page_form->addTopWizardButton($page_form->button("continue_btn", "Continue", $continue_class, true, array("href" => base_url("wizard/review/plans/continue")), $continue_disabled));
            $page_form->addTopWizardButton($page_form->button("wizard_start_over_btn", "Start Over", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/cancel"))));
            $page_form->addTopWizardButton($page_form->button("wizard_rematch_btn", "Match Columns", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/rematch"))));
            if ( HasRelationship($company_id) ) $page_form->addTopWizardButton($page_form->button("wizard_relationship_btn", "Relationships", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/navigate/relationships"))));
            if ( HasLivesToCompare($company_id) ) $page_form->addTopWizardButton($page_form->button("wizard_lives_btn", "Lives", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/navigate/lives"))));
            $page_form->addElement($page_form->top_buttons());
            $page_form = $page_form->render();



            $view_array = array();
            $view_array = array_merge($view_array, array("page_header" => $page_header));
            $view_array = array_merge($view_array, array("page_form" => $page_form));
            $view_array = array_merge($view_array, array("company_id" => $company_id));
			$view_array = array_merge($view_array, array("data" => $payload['data']));
			$view_array = array_merge($view_array, array("continue_flg" => $payload['valid']));
			$view_array = array_merge($view_array, array("warning_flg" => $payload['warning']));
			$view_array = array_merge($view_array, array("plantype_widget" => $plantype_widget));
			$view_array = array_merge($view_array, array("plan_widget" => $plan_widget));
			$view_array = array_merge($view_array, array("ageband_widget" => $ageband_widget));
			$view_array = array_merge($view_array, array("tobacco_widget" => $tobacco_widget));
            $view_array = array_merge($view_array, array("carrier_widget" => $carrier_widget));
			$view_array = array_merge($view_array, array("company_id" => $company_id));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("plans/js_assets")));
            $page_template = array_merge($page_template, array("view" => "plans/review"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }

    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	public function tobacco_save() {
		try {
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission company_write.");

			$ignore_checkbox = getArrayStringValue("ignore_checkbox", $_POST);
			$carrier = getArrayStringValue("carrier", $_POST);
			$plantypecode = getArrayStringValue("plantypecode", $_POST);
			$plan = getArrayStringValue("plan", $_POST);
			$coveragetier = getArrayStringValue("coveragetier", $_POST);
			$company_id = GetSessionValue("company_id");
            $carrier_id = getArrayStringValue("carrier_id", $_POST);
            $plantype_id = getArrayStringValue("plantype_id", $_POST);
            $plan_id = getArrayStringValue("plan_id", $_POST);
            $coveragetier_id = getArrayStringValue("coveragetier_id", $_POST);

            if ( GetStringValue($carrier_id) === '' ) throw new UIException("Missing required input carrier_id");
            if ( GetStringValue($plantype_id) === '' ) throw new UIException("Missing required input plantype_id");
            if ( GetStringValue($plan_id) === '' ) throw new UIException("Missing required input plan_id");
            if ( GetStringValue($coveragetier_id) === '' ) throw new UIException("Missing required input coveragetier_id");

			if ( $ignore_checkbox == "on" ) $this->tobacco_model->update_tobacco_attribute($company_id, $coveragetier_id, true);
			if ( $ignore_checkbox != "on" ) $this->tobacco_model->update_tobacco_attribute($company_id, $coveragetier_id, false);

			AJAXSuccess("Tobacco settings saved.");


		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}

    /**
     * ageband_save (POST)
     *
     * This function will hand the save request from a user for the ageband form
     * data.  This will evaluate what tier the data was filled out for and then
     * it will review the "depth level" of the save request.  It will then save
     * the submitted data against the TIER, PLAN or PLANTYPE.
     */
	public function ageband_save()
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission company_write.");

            // Who are se saving this data against?
            $company_id = GetSessionValue('company_id');

            // Pull out the key values we will be saving the ageband data against.
            $carrier_id = getArrayIntValue('carrier_id', $_POST);
            $plantype_id = getArrayIntValue('plantype_id', $_POST);
            $plan_id = getArrayIntValue('plan_id', $_POST);
            $coveragetier_id = getArrayIntValue('coveragetier_id', $_POST);

            // Make sure we have our index values for the key.
            if ( GetStringValue($carrier_id) === '' ) throw new UIException("Missing required input carrier_id");
            if ( GetStringValue($plantype_id) === '' ) throw new UIException("Missing required input plantype_id");
            if ( GetStringValue($plan_id) === '' ) throw new UIException("Missing required input plan_id");
            if ( GetStringValue($coveragetier_id) === '' ) throw new UIException("Missing required input coveragetier_id");

            // Remove these from the POST object.
            unset($_POST['carrier_id']);
            unset($_POST['plantype_id']);
            unset($_POST['plan_id']);
            unset($_POST['coveragetier_id']);

            // What "depth" are we saving?  By default, we save at the tier level.
            $save_type = GetArrayStringValue('create_ageband_btn', $_POST);
            if ( $save_type === '' ) $save_type = 'TIER';
            SetSessionValue("plan_save_type", $save_type);  // Remember this for the rest of their session.


            // Call the save function one to many times based on what level we ar saving.
            if ( $save_type === 'TIER' )
            {
                $this->_save_ageband_data($_POST, $carrier_id, $plantype_id, $plan_id, $coveragetier_id);
            }
            else if ( $save_type === 'PLAN' )
            {
                $tiers = $this->Company_model->get_company_coveragetier_by_plan_id($company_id, $plan_id);
                foreach($tiers as $tier)
                {
                    $item_carrier_id = GetArrayStringValue('CarrierId', $tier);
                    $item_plantype_id = GetArrayStringValue('PlanTypeId', $tier);
                    $item_plan_id = GetArrayStringValue('PlanId', $tier);
                    $item_coveragetier_id = GetArrayStringValue('Id', $tier);
                    $this->_save_ageband_data($_POST, $item_carrier_id, $item_plantype_id, $item_plan_id, $item_coveragetier_id);
                }
            }
            else if ( $save_type === 'PLANTYPE' )
            {
                $tiers = $this->Company_model->get_company_coveragetier_by_plantype_id($company_id, $plantype_id);
                foreach($tiers as $tier)
                {
                    $item_carrier_id = GetArrayStringValue('CarrierId', $tier);
                    $item_plantype_id = GetArrayStringValue('PlanTypeId', $tier);
                    $item_plan_id = GetArrayStringValue('PlanId', $tier);
                    $item_coveragetier_id = GetArrayStringValue('Id', $tier);
                    $this->_save_ageband_data($_POST, $item_carrier_id, $item_plantype_id, $item_plan_id, $item_coveragetier_id);
                }
            }
            else throw new UIException("Unexpected save type.");

            AJAXSuccess("Age band saved.");
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }


    }

	public function plantype_save() {
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission company_write.");

			// Validate our inputs.
			$ignore_checkbox = getArrayStringValue("ignore_checkbox", $_POST);
			$this->form_validation->set_rules('plantype_mapping','plan type','callback__plantype_ignored_validator['.$ignore_checkbox.']');
			$this->form_validation->set_rules('retro_rules','retro rules','callback__retrorule_ignored_validator['.$ignore_checkbox.']');
			$this->form_validation->set_rules('wash_rules','wash rules','callback__washrule_ignored_validator['.$ignore_checkbox.']');
			$this->form_validation->set_rules('carrier','carrier','required');
			$this->form_validation->set_rules('plantype','plan type','required');
			$this->form_validation->set_message('_plantype_ignored_validator','This plan type has not been marked as ignored and the plan type has not been identified.');
			$this->form_validation->set_message('_retrorule_ignored_validator','This plan type has not been marked as ignored and the retro rule has not been specified.');
			$this->form_validation->set_message('_washrule_ignored_validator','This plan type has not been marked as ignored and the wash rule has not been specified.');

			if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}

			// Grab all of the validated data that we need to start making
			// business decisions.
			$ignore_checkbox = getArrayStringValue("ignore_checkbox", $_POST);
			$carrier = getArrayStringValue("carrier", $_POST);
			$plantype = getArrayStringValue("plantype", $_POST);
			$company_id = GetSessionValue("company_id");

			// Pull the data for this existing record to make the following update easier.
			$data = $this->company_model->get_company_plantype_data($company_id, $carrier, $plantype);
			$carrier_id = getArrayIntValue("CarrierId", $data);
			$plantype_normalized = getArrayStringValue("PlanTypeNormalized", $data);

			if ( $ignore_checkbox == "on"){
				// The user has set this plan type as ignored.  Just set that value.
				$this->company_model->set_company_plantype_ignored($company_id, $carrier_id, $plantype_normalized, true);
			}else{

				// The user has provided us with plan type settings.  Save them all.
				$wash = getArrayStringValue("wash_rules", $_POST);
				$retro = getArrayStringValue("retro_rules", $_POST);
				$code = getArrayStringValue("plantype_mapping", $_POST);
				$anniversary = getArrayStringValue("plan_anniversary_month", $_POST);

				$this->company_model->set_company_plantype_washrule($company_id, $carrier_id, $plantype_normalized, $wash);
				$this->company_model->set_company_plantype_retrorule($company_id, $carrier_id, $plantype_normalized, $retro);
				$this->company_model->set_company_plantype_code($company_id, $carrier_id, $plantype_normalized, $code);
				$this->company_model->set_company_plantype_ignored($company_id, $carrier_id, $plantype_normalized, false);
				$this->company_model->set_company_plantype_plananniversarymonth($company_id, $carrier_id, $plantype_normalized, $anniversary);

			}

			// FEES.  Take the attributes we just saved on the plantype and
			// push them to any of the fee plantype records that are related.
			$this->PlanFees_model->push_plantype_attributes($company_id, $carrier_id, $plantype);

			AJAXSuccess("Plan Type saved.");


		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	public function plan_save() {
		try
		{

			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission company_write.");

			// Collect and organize our data.
			$company_id = GetSessionValue("company_id");
			$carrier = getArrayStringValue("carrier", $_POST);
			$plantype = getArrayStringValue("plantype", $_POST);
			$plan = getArrayStringValue("plan", $_POST);
			$aso_fee = getArrayStringValue("aso_fee", $_POST);
			$aso_carrier = getArrayStringValue("aso_carrier", $_POST);
			$stoploss_fee = getArrayStringValue("stoploss_fee", $_POST);
			$stoploss_carrier = getArrayStringValue("stoploss_carrier", $_POST);

			// Validate our inputs.
			$this->form_validation->set_rules('aso_fee','aso fee','callback__fee_validator');
			$this->form_validation->set_rules('aso_carrier','aso carrier','callback__fee_carrier_validator['.$aso_fee.']');
			$this->form_validation->set_rules('stoploass_fee','stop loss fee','callback__fee_validator');
			$this->form_validation->set_rules('stoploss_carrier','stop loss carrier','callback__fee_carrier_validator['.$stoploss_fee.']');
			$this->form_validation->set_message('_fee_validator','Fee validation error.');
			$this->form_validation->set_message('_fee_carrier_validator','Fee carrier validation error.');

			if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}


			// Process the inputs and save them.
			$plan_data = $this->PlanFees_model->select_company_plan($company_id, $carrier, $plantype, $plan);
			$plan_id = getArrayStringValue("Id", $plan_data);
			$carrier_id = getArrayStringValue("CarrierId", $plan_data);

			/*
			pprint_r("company_id: " . $company_id);
			pprint_r("carrier: " . $carrier);
			pprint_r("plantype: " . $plantype);
			pprint_r("plan: " . $plan);
			pprint_r("aso_carrier: ". $aso_carrier);
			pprint_r("stoploss_carrier: ". $stoploss_carrier);
			pprint_r("plan_id: ". $plan_id);
			pprint_r($plan_data);
			exit;
			*/

			// Here we converted the selected carrier_id to the carrier description.
			//$aso_carrier = getArrayStringValue($aso_carrier, $carrier_dropdown);
			//$stoploss_carrier = getArrayStringValue($stoploss_carrier, $carrier_dropdown);


			// Remove any "money" values that might have been allowed as input
			// by the user that are not allowed in the database.
			$aso_fee = replaceFor($aso_fee, "$", "");
			$aso_fee = replaceFor($aso_fee, ",", "");
			$stoploss_fee = replaceFor($stoploss_fee, "$", "");
			$stoploss_fee = replaceFor($stoploss_fee, ",", "");

			// If there is no fee set, save the fact that they dropped the carrier.
			if ( $aso_fee == "" ) $aso_carrier = "";
			if ( $stoploss_fee == "" ) $stoploss_carrier = "";

			// Premium Equivalent is always enable.  If we have no fees, disable it.
			$premium_equivalent_checkbox = true;
			if ( $aso_fee == "" && $stoploss_fee == "" ) $premium_equivalent_checkbox = false;

			$this->PlanFees_model->update_company_plan_fee($company_id, $plan_id, $aso_fee, $aso_carrier, "aso");
			$this->PlanFees_model->update_company_plan_fee($company_id, $plan_id, $stoploss_fee, $stoploss_carrier, "stoploss");
			$this->PlanFees_model->update_premium_equivalent($company_id, $plan_id, $premium_equivalent_checkbox);

			// FEES.  Take the PlanType of the Plan we just saved and
			// push that plan's PlanType values down to all the related child PlanType records.
			$this->PlanFees_model->push_plantype_attributes( $company_id, $carrier_id, $plantype);
			AJAXSuccess("Plan saved.");


		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	public function carrier_save()
    {
        try
        {

            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission company_write.");

            // Collect and organize our data.
            $company_id = GetSessionValue("company_id");
            $carrier = getArrayStringValue("carrier", $_POST);
            $carrier_code = getArrayStringValue("carrier_code", $_POST);

            // Validate our inputs.
            if ( $company_id === '' ) throw new UIException("Missing required input company_id");
            if ( $carrier === '' ) throw new UIException("Missing required input carrier");
            if ( $carrier_code === '' ) throw new UIException("Missing required input carrier_code");

            // Save the specified carrier for the company.
            $this->_saveCarrier($company_id, $carrier, $carrier_code);

            AJAXSuccess("");

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
	public function planreview_continue() {


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

			$payload = GetPlansDataReview( $company_id );
			if ( ! isset($payload['data'] ) ) throw new UIException("Unable to locate the customers data.");
			if ( ! isset($payload['valid'] ) ) throw new UIException("Unable to locate the customers data.");
			if ( ! isset($payload['warning'] ) ) throw new UIException("Unable to locate the customers data.");

			// Our page data must be valid to continue.
			if ( $payload['valid'] !== TRUE ) throw new UIException("Unexpected situation.  Please try again later.");

			// For production support, archive the settings that were just saved.
			ArchivePlanSettings($company_id, $user_id);

			// Audit this transaction
            AuditIt("Reviewed plan settings.", array());

			$this->Wizard_model->plan_review_step_complete($company_id);
			$this->queue_model->add_worker_job($companyparent_id, $company_id, $user_id, "GenerateReports", "index");

			AJAXSuccess("", base_url("dashboard"));

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}

	// VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	public function _plantype_ignored_validator($value, $ignore_checkbox) {
		return $this->_ignored_validator($value, $ignore_checkbox);
	}
	public function _retrorule_ignored_validator($value, $ignore_checkbox) {
		return $this->_ignored_validator($value, $ignore_checkbox);
	}
	public function _washrule_ignored_validator($value, $ignore_checkbox) {
		return $this->_ignored_validator($value, $ignore_checkbox);
	}
	public function _ignored_validator($value, $ignore_checkbox) {
		if ( getStringValue($ignore_checkbox) == "on" ) return true;
		if ( getStringValue($value) == "" ) return false;
		return true;
	}
	public function _fee_validator($value) {

		if ( getStringValue($value) == "" ) return true;

        // We will store this value as a numeric in the database, but we will
        // display it as money to the user.  Covert the input into a money
        // value before we validate it.  That way things like ".25" or "1.1"
        // will work as these are really 0.25 and 1.10.  No need to make the
        // user deal with typing in things in a specifc way when it's obvious
		// what they meant.
        $value = replaceFor(getMoneyValue($value), "$", "");

		if ( getFloatValue($value) <= 0 ) return false;
		$is_valid_money = preg_match("/^[+-]?[0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]{2})?$/", $value);
		return $is_valid_money;
	}
	public function _fee_carrier_validator($value, $fee) {
		if ( getStringValue($fee) == "null" ) $fee = "";
		if ( getStringValue($fee) == "" ) return true;
		if ( getStringValue($value) != "" ) return true;
		return false;
	}

	// RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function render_carrier_form($carrier) {

        try
        {

            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( getStringValue($carrier) == "" ) throw new Exception("Missing required input carrier.");

            $carrier = RestoreDisallowedCharacters($carrier);

            $form_html = $this->_carrier_form($carrier);

            $array = array();
            $array['responseText'] = $form_html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }

    }
	public function render_plantype_form($carrier, $plantype) {

		try
		{

			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( getStringValue($carrier) == "" ) throw new Exception("Missing required input carrier.");
			if ( getStringValue($plantype) == "" ) throw new Exception("Missing required input plan type.");

            $carrier = RestoreDisallowedCharacters($carrier);
            $plantype = RestoreDisallowedCharacters($plantype);

			$form_html = $this->_plantype_form($carrier, $plantype);

			$array = array();
			$array['responseText'] = $form_html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}

	}
	public function render_plan_form($carrier, $plantype, $plan) {
		try
		{

			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( getStringValue($carrier) == "" ) throw new Exception("Missing required input carrier.");
			if ( getStringValue($plantype) == "" ) throw new Exception("Missing required input plan type.");

			$company_id = GetSessionValue("company_id");

            $carrier = RestoreDisallowedCharacters($carrier);
            $plantype = RestoreDisallowedCharacters($plantype);
            $plan = RestoreDisallowedCharacters($plan);

			$form_html = $this->_plan_form($company_id, $carrier, $plantype, $plan);

			$array = array();
			$array['responseText'] = $form_html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
	public function render_ageband_form($carrier, $plantype, $plantypecode, $plan, $coveragetier) {

		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( getStringValue($carrier) == "" ) throw new Exception("Missing required input carrier.");
            if ( getStringValue($plantype) == "" ) throw new Exception("Missing required input plan type.");
			if ( getStringValue($plantypecode) == "" ) throw new Exception("Missing required input plan type code.");
			if ( getStringValue($plan) == "" ) throw new Exception("Missing required input plan type code.");
			if ( getStringValue($coveragetier) == "" ) throw new Exception("Missing required input plan type.");


			$carrier = RestoreDisallowedCharacters($carrier);
            $plantype = RestoreDisallowedCharacters($plantype);
			$plantypecode = RestoreDisallowedCharacters($plantypecode);
			$plan = RestoreDisallowedCharacters($plan);
			$coveragetier = RestoreDisallowedCharacters($coveragetier);

			$form_html = $this->_ageband_form($carrier, $plantype, $plantypecode, $plan, $coveragetier);

			$array = array();
			$array['responseText'] = $form_html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}

	}
	public function render_tobacco_form($carrier, $plantype, $plantypecode, $plan, $coveragetier) {

		try
		{

			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( getStringValue($carrier) == "" ) throw new Exception("Missing required input carrier.");
            if ( getStringValue($plantype) == "" ) throw new Exception("Missing required input plan type.");
			if ( getStringValue($plantypecode) == "" ) throw new Exception("Missing required input plan type code.");
			if ( getStringValue($plan) == "" ) throw new Exception("Missing required input plan type code.");
			if ( getStringValue($coveragetier) == "" ) throw new Exception("Missing required input plan type.");

			$carrier = RestoreDisallowedCharacters($carrier);
            $plantype = RestoreDisallowedCharacters($plantype);
			$plantypecode = RestoreDisallowedCharacters($plantypecode);
			$plan = RestoreDisallowedCharacters($plan);
			$coveragetier = RestoreDisallowedCharacters($coveragetier);

			$form_html = $this->_tobacco_form($carrier, $plantype, $plantypecode, $plan, $coveragetier);

			$array = array();
			$array['responseText'] = $form_html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}

	}
	public function render_band_defaults( ) {
		try
		{

			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

			$band_code = getArrayStringValue("default_group", $_POST);

			switch( $band_code )
			{
				case "5-YEAR":
				case "10-YEAR":
					break;
				default:
					throw new Exception("Unsupported default band grouping.");
					break;
			}

			// Find the CARRIER_CODE associated with the band you are trying to default.
			$company_id = GetSessionValue('company_id');
			$carrier_id = GetArrayStringValue('carrier_id', $_POST);
			$carrier_code = "";
			$carrier_description = "";
			if ( $carrier_id !== '' )
            {
                $carrier = $this->Company_model->get_company_carrier( $company_id, $carrier_id );
                $carrier_code = GetArrayStringValue("CarrierCode", $carrier);
                $carrier_description = GetArrayStringValue('UserDescription', $carrier);
            }

			// Collect the DEFAULT age bands to be shown.  Use carrier specific if they exist.
            $carrier_bands = true;
			$bands = $this->Ageband_model->get_default_carrier_agebands($carrier_code, $band_code);
			if ( empty($bands) )
            {
                $carrier_bands = false;
                $bands = $this->Ageband_model->get_default_agebands($band_code);
            }
			if ( empty($bands) ) throw new Exception("Found no bands to default.");


            // Display the bands and pass along a notification if we used carrier specific bands.
			$view_array = array( 'bands' => $bands);
			$view_array['bands'] = $bands;
            $array['notification'] = "";
            if ( $carrier_bands ) $array['notification'] = "Age Bands Inserted Based on {$carrier_description} Default Preferences";
			$array['html'] = RenderViewAsString("plans/ageband_default", $view_array);

			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}


    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    private function _carrier_form($carrier)
    {
        $company_id = GetSessionValue("company_id");
        $carrier = urldecode(getStringValue($carrier));

        $carrier = RestoreDisallowedCharacters($carrier);

        // Get the current customer mapping for this carrier.
        $carrier_code = "";
        $mapping = $this->Company_model->get_company_carrier_by_user_description( $company_id, $carrier );
        if ( ! empty($mapping) )
        {
            $carrier_code = getArrayStringValue("CarrierCode", $mapping);
        }

        // The selected value will be the user's description, unless we already
        // have a carrier code.
        $selected = trim(strtoupper($carrier));
        if ( $carrier_code !== '' ) $selected = $carrier_code;

        $carriers = new Select2("modal");
        $carriers->setId("carrier_code");
        $carriers->setSelectedValue($selected);
        $carriers->addDefaultItem("Unspecified Carrier", "UNSPECIFIED CARRIER");
        $carriers = $this->_addCarriersToSelect2($carriers);

        // Create the form.
        $form = new UIModalForm("carrier_form", "carrier_form", base_url("wizard/review/carrier/save"));
        $form->setTitle( "Carrier Review" );
        $form->setBreadcrumb( array($carrier) );
        $form->addElement($form->htmlView("plans/carrier_help", array( "carrier" => $carrier), ""));
        $form->addElement($carriers);
        $form->addElement($form->hiddenInput("carrier", $carrier));
        $form->addElement($form->submitButton("create_plantype_btn", "Save", "btn-primary pull-right"));
        $form->addElement($form->button("cancel_btn", "Cancel", "btn-default pull-right"));
        $form = $form->render();

        return $form;

    }
	private function _plantype_form($carrier, $plantype) {


		$company_id = GetSessionValue("company_id");
		$carrier = urldecode(getStringValue($carrier));
		$plantype = urldecode(getStringValue($plantype));

        $carrier = RestoreDisallowedCharacters($carrier);
        $plantype = RestoreDisallowedCharacters($plantype);

		// Grag our drowndown values.
		$plantypes = $this->_plantypes_dropdown();
		$retrorules = $this->_retro_dropdown();
		$washrules = $this->_wash_dropdown();


		// Grab the existing data, if there is some, for this form.
		$data = $this->company_model->get_company_plantype_data( $company_id, $carrier, $plantype );
		$code = getArrayStringValue("PlanTypeCode", $data);
		$retrorule = getArrayStringValue("RetroRule", $data);
		$washrule = getArrayStringValue("WashRule", $data);
		$plan_anniversary_month = getArrayStringValue("PlanAnniversaryMonth", $data);
		$plan_anniversary_month = ( $plan_anniversary_month == "" ? "" : substr(str_pad( $plan_anniversary_month, 2, "0", STR_PAD_LEFT),-2) );
		$ignored = getArrayStringValue("Ignored", $data);
		if ( $ignored == "t" ) $ignored = true;
		if ( $ignored != "t" ) $ignored = false;

		// Let's assume plan anniversary month, retro and wash rules are
		// specific to a carrier and default them to our best guess if not set.
		$best_guess = false;
		if ( $retrorule == "" ) {
			$retrorule = $this->Wizard_model->get_best_guess_retrorule($company_id, $carrier);
			if ( getStringValue($retrorule) != "" ) $best_guess = true;
		}
		if ( $washrule == "" ) {
			$washrule = $this->Wizard_model->get_best_guess_washrule($company_id, $carrier);
			if ( getStringValue($washrule) != "" ) $best_guess = true;
		}
		if ( $plan_anniversary_month == "" ) {
			$plan_anniversary_month = $this->Wizard_model->get_best_guess_plananniversarymonth($company_id, $carrier);
			if ( getStringValue($plan_anniversary_month) != "" ) $best_guess = true;
		}

		// Create a dropdown with all the months, but make the first item
		// "No Anniversary" so we can turn off the feature.
		$plan_anniversary_months = array();
		$plan_anniversary_months['00'] = "No Anniversary";
		$plan_anniversary_months = $plan_anniversary_months + DropdownMonths(); // Union Arrays to Preserve Keys.

		// Create the form.
        $form = new UIModalForm("plantype_form", "plantype_form", base_url("wizard/review/plantype/save"));
        $form->setTitle( "Review Plan Types" );
		$form->setBreadcrumb( array($carrier, $plantype) );
		$form->addElement($form->htmlView("plans/plantype_help", array( "plantype" => $plantype), ""));
		$form->addElement($form->dropdown("plantype_mapping", "Identify Plan Type", null, $plantypes, $code, "", "", false, true));
		$form->addElement($form->htmlView("plans/best_guess", array('best_guess' => $best_guess)));
		$form->addElement($form->htmlView("plans/plan_anniversary_month_help", array()));
		$form->addElement($form->dropdown("plan_anniversary_month", "Plan Anniversary Month", null, $plan_anniversary_months, $plan_anniversary_month, "", "PlanAnniversaryMonthChangeHandler", false, true));
		$form->addElement($form->htmlView("plans/retro_rules_help", array(), ""));
		$form->addElement($form->dropdown("retro_rules", "Retro Rules", null, $retrorules, $retrorule, ""));
		$form->addElement($form->htmlView("plans/wash_rules_help", array()));
		$form->addElement($form->dropdown("wash_rules", "Wash Rules", null, $washrules, $washrule, ""));
		$form->addElement($form->htmlView("plans/wash_rules_help2", array()));
		$form->addElement($form->checkBox("ignore_checkbox", "", "Ignore this plan type", $ignored));
		$form->addElement($form->hiddenInput("carrier", $carrier));
		$form->addElement($form->hiddenInput("plantype", $plantype));
        $form->addElement($form->submitButton("create_plantype_btn", "Save", "btn-primary pull-right"));
        $form->addElement($form->button("cancel_btn", "Cancel", "btn-default pull-right"));
        $form = $form->render();

        return $form;
	}
	private function _plan_form($company_id, $carrier, $plantype, $plan) {


		$carrier = urldecode(getStringValue($carrier));
		$plantype = urldecode(getStringValue($plantype));
		$plan = urldecode(getStringValue($plan));

        $carrier = RestoreDisallowedCharacters($carrier);
        $plantype = RestoreDisallowedCharacters($plantype);
        $plan = RestoreDisallowedCharacters($plan);
        
		$plan_data = $this->PlanFees_model->select_company_plan($company_id, $carrier, $plantype, $plan);
		$carrier_id = GetArrayStringValue("CarrierId", $plan_data);
		$carrier_data = $this->Company_model->get_company_carrier($company_id, $carrier_id);
		$carrier_code = GetArrayStringValue("CarrierCode", $carrier_data);

		//pprint_r($company_id);
		//pprint_r($carrier);
		//pprint_r($plantype);
		//pprint_r($plan);
		//pprint_r($plan_data);
		//exit;

		// ASO Fee
		// Grab the fee data and set the carrier dropdown or the alt carrier name.
		$aso_fee = getArrayStringValue("ASOFee", $plan_data);
		$aso_fee_carrier = getArrayStringValue("ASOFeeCarrierId", $plan_data);
        $aso_fee_carrier_data = $this->Company_model->get_company_carrier($company_id, $aso_fee_carrier);
        $aso_fee_carrier_code = GetArrayStringValue("CarrierCode", $aso_fee_carrier_data);
        if ( $aso_fee_carrier_code === "" ) $aso_fee_carrier_code = $carrier_code;


		// Stop Loss
		// Grab the fee data and set the carrier dropdown or the alt carrier name.
		$stoploss_fee = getArrayStringValue("StopLossFee", $plan_data);
		$stoploss_fee_carrier = getArrayStringValue("StopLossFeeCarrierId", $plan_data);
        $stoploss_fee_carrier_data = $this->Company_model->get_company_carrier($company_id, $stoploss_fee_carrier);
        $stoploss_fee_carrier_code = GetArrayStringValue("CarrierCode", $stoploss_fee_carrier_data);
        if ( $stoploss_fee_carrier_code === "" ) $stoploss_fee_carrier_code = $carrier_code;

		// Format the money value for the end user, but remove the $ at the front
		// because our UI added that for them.
		if ( $aso_fee != "" ) $aso_fee = replacefor(getMoneyValue($aso_fee), "$", "");
		if ( $stoploss_fee != "" ) $stoploss_fee = replacefor(getMoneyValue($stoploss_fee), "$", "");

		// Create the form.
		$form = new UIModalForm("plan_form", "plan_form", base_url("wizard/review/plan/save"));
		$form->setTitle( "Review Plans" );
		$form->setBreadcrumb( array($carrier, $plantype, $plan) );

		$form->addElement($form->htmlView( "plans/aso_help", array(), "" ));
		$form->addElement($form->moneyInput( "aso_fee", "ASO Fee", $aso_fee));

		// Add the ASO Fee Carriers dropdown.
        $aso_carriers = new Select2("modal");
        $aso_carriers->setId("aso_carrier");
        $aso_carriers->setSelectedValue($aso_fee_carrier_code);
        $aso_carriers->addDefaultItem("Unspecified Carrier", "UNSPECIFIED CARRIER");
        $aso_carriers = $this->_addCarriersToSelect2($aso_carriers);
		$form->addElement($aso_carriers);

		$form->addElement($form->htmlView( "plans/stoploss_help", array(), "" ));
		$form->addElement($form->moneyInput( "stoploss_fee", "Stop Loss Fee", $stoploss_fee));

        // Add the ASO Fee Carriers dropdown.
        $stoploss_carriers = new Select2("modal");
        $stoploss_carriers->setId("stoploss_carrier");
        $stoploss_carriers->setSelectedValue($stoploss_fee_carrier_code);
        $stoploss_carriers->addDefaultItem("Unspecified Carrier", "UNSPECIFIED CARRIER");
        $stoploss_carriers = $this->_addCarriersToSelect2($stoploss_carriers);
        $form->addElement($stoploss_carriers);

		$form->addElement($form->htmlView( "plans/premium_equivalent_help", array(), "" ));

		$form->addElement($form->hiddenInput("carrier", $carrier));
		$form->addElement($form->hiddenInput("plantype", $plantype));
		$form->addElement($form->hiddenInput("plan", $plan));
		$form->addElement($form->submitButton("create_plan_btn", "Save", "btn-primary pull-right"));
		$form->addElement($form->button("cancel_btn", "Cancel", "btn-default pull-right"));
		$form = $form->render();

		return $form;
	}
	private function _plantypes_dropdown() {
		$types = $this->mapping_model->get_plan_types_for_user_dopdown();
		$dropdown = array();
		foreach($types as $type)
		{
			$name = getArrayStringValue("name", $type);
			$display = getArrayStringValue("display", $type);
			$dropdown[$name] = $display;
		}
		return $dropdown;
	}
	private function _retro_dropdown() {
		$data = $this->Wizard_model->get_retrorules();
		$dropdown = array();
		foreach($data as $item)
		{
			$name = getArrayStringValue("Name", $item);
			$display = getArrayStringValue("Display", $item);
			$dropdown[$name] = $display;
		}
		return $dropdown;
	}
	private function _wash_dropdown() {
		$data = $this->Wizard_model->get_washrules();
		$dropdown = array();
		foreach($data as $item)
		{
			$name = getArrayStringValue("Name", $item);
			$display = getArrayStringValue("Display", $item);
			$dropdown[$name] = $display;
		}
		return $dropdown;
	}
	private function _ageband_form($carrier, $plantype, $plantypecode, $plan, $coveragetier) {

		$company_id = GetSessionValue("company_id");
		$carrier = RestoreDisallowedCharacters(urldecode(getStringValue($carrier)));
        $plantype = RestoreDisallowedCharacters(urldecode(getStringValue($plantype)));
		$plantypecode = RestoreDisallowedCharacters(urldecode(getStringValue($plantypecode)));
		$plan = RestoreDisallowedCharacters(urldecode(getStringValue($plan)));
		$coveragetier = RestoreDisallowedCharacters(urldecode(getStringValue($coveragetier)));
        
        // Convert all of the human readable data into their id references.
		$data = $this->company_model->get_company_coveragetier_by_descriptions($company_id, $carrier, $plantype, $plan, $coveragetier);
		$carrier_id = getArrayIntValue("CarrierId", $data);
		$plantype_id = getArrayIntValue("PlanTypeId", $data);
		$plan_id = getArrayIntValue("PlanId", $data);
		$coveragetier_id = getArrayIntValue("Id", $data);

		// Grab the human readable name of the plan type.
		$data = $this->Company_model->get_compmay_plantype_data_by_ids( $company_id, $carrier_id, $plantype_id );
		$plantype = getArrayStringValue("UserDescription", $data);

		// Has the user elected to ignore agebands on this coverage tier?
		$ignored = false;
		$data = $this->ageband_model->coverage_tier_ageband_details( $company_id, $coveragetier_id);
		$ignored = getArrayStringValue("Ignored", $data);
		if ( $ignored == "t" ) $ignored = true;
		if ( $ignored != "t" ) $ignored = false;

		// Grab any existing age bands on this coverage tier.
		$bands = $this->ageband_model->get_age_bands( $coveragetier_id );
		$best_guess_flg = false;
		if ( empty($bands) && $ignored != "t" )
		{
			// There are no agebands for this coverage tier.
			// Set the bands, logically, to our best guess based on other coverage
			// tiers that belong in the same carier, plantype, plan grouping.
			$bands = $this->ageband_model->get_best_guess_age_bands( $company_id, $carrier_id, $plantype_id, $plan_id );
			if ( ! empty($bands) )
			{
				$best_guess_flg = true;
			}
		}

		// Grab any existing age type data on this coverage tier.
		$age_rules = $this->ageband_model->get_age_type_by_tier( $coveragetier_id );
		$age_best_guess_flg = false;
		if ( empty($age_rules) && $ignored != "t" )
		{
			// There are not age rules for this coverage tier.
			// Set the rules, logically, to our best guess based on their coverage
			// tiers that belong in the same carrier, plantype and plan grouping.
			$age_rules = $this->ageband_model->get_best_guess_age_rules( $company_id, $carrier_id, $plantype_id, $plan_id );
			if ( ! empty($age_rules) ){
				$age_best_guess_flg = true;
			}
		}


		// Create the form.
        $form = new UIModalForm("ageband_form", "ageband_form", base_url("wizard/review/ageband/save"));
        $form->setTitle( "Edit Age Bands" );
        $form->setBreadcrumb( array($carrier, $plantype, $plan, $coveragetier) );
        $form->addElement($form->htmlView("plans/age_calculation_help", array(), ""));
		$form->addElement($form->agetypeEditor("agetype_editor", "Age", $age_rules, $age_best_guess_flg));
		$form->addElement($form->agebandEditor("ageband_editor", "Age Bands", $bands, $best_guess_flg));
		$form->addElement($form->checkBox("ignore_checkbox", "", "This coverage tier does not have age bands.", $ignored));
		$form->addElement($form->hiddenInput("carrier", $carrier));
		$form->addElement($form->hiddenInput("plantypecode", $plantypecode));
		$form->addElement($form->hiddenInput("plan", $plan));
		$form->addElement($form->hiddenInput("coveragetier", $coveragetier));
        $form->addElement($form->hiddenInput("carrier_id", $carrier_id));
        $form->addElement($form->hiddenInput("plantype_id", $plantype_id));
        $form->addElement($form->hiddenInput("plan_id", $plan_id));
        $form->addElement($form->hiddenInput("coveragetier_id", $coveragetier_id));



        // Make the save button
        $button = new MultiOptionButton();
        $button->id = "create_ageband_btn";
        $button->size = "normal";
        $button->selected = GetSessionValue('plan_save_type');
        $button->setIsSubmit(true);
        $button->addItem('TIER', 'Save');
        $button->addItem('PLAN', 'Save + Plan');
        $button->addItem('PLANTYPE', 'Save + Plan Type');
        $button->addClass('pull-right');




        //$form->addElement($form->submitButton("create_ageband_btn", "Save", "btn-primary pull-right"));
        $form->addElement($button);
        $form->addElement($form->button("cancel_btn", "Cancel", "btn-default pull-right"));
        $form = $form->render();

        return $form;
	}
	private function _tobacco_form($carrier, $plantype, $plantypecode, $plan, $coveragetier) {

		$company_id = GetSessionValue("company_id");
		$carrier = RestoreDisallowedCharacters(urldecode(getStringValue($carrier)));
        $plantype = RestoreDisallowedCharacters(urldecode(getStringValue($plantype)));
		$plantypecode = RestoreDisallowedCharacters(urldecode(getStringValue($plantypecode)));
		$plan = RestoreDisallowedCharacters(urldecode(getStringValue($plan)));
		$coveragetier = RestoreDisallowedCharacters(urldecode(getStringValue($coveragetier)));

		// Convert all of the human readable data into their id references.
		$data = $this->company_model->get_company_coveragetier_by_descriptions($company_id, $carrier, $plantype, $plan, $coveragetier);
		$carrier_id = getArrayIntValue("CarrierId", $data);
		$plantype_id = getArrayIntValue("PlanTypeId", $data);
		$plan_id = getArrayIntValue("PlanId", $data);
		$coveragetier_id = getArrayIntValue("Id", $data);

		// Grab the human readable name of the plan type.
		$data = $this->Company_model->get_compmay_plantype_data_by_ids( $company_id, $carrier_id, $plantype_id );
		$plantype = getArrayStringValue("UserDescription", $data);

		// Has the user elected to ignore tobacco on this coverage tier?
		$ignored = false;
		$data = $this->tobacco_model->coverage_tier_tobacco_details( $company_id, $coveragetier_id);
		$ignored = getArrayStringValue("Ignored", $data);
		if ( $ignored == "t" ) $ignored = true;
		if ( $ignored != "t" ) $ignored = false;

		// Create the form.
		$form = new UIModalForm("tobacco_form", "tobacco_form", base_url("wizard/review/tobacco/save"));
		$form->setTitle( "Edit Tobacco Settings" );
		$form->setBreadcrumb( array($carrier, $plantype, $plan, $coveragetier) );
		$form->setDescription("Your data has a column indicating tobacco usage mapped.  We will use this data in breaking out records for benefit reporting on this coverage tier unless you check the box below.");
		$form->addElement($form->checkBox("ignore_checkbox", "", "This coverage tier does not require indicating tobacco usage.", $ignored));
		$form->addElement($form->hiddenInput("carrier", $carrier));
		$form->addElement($form->hiddenInput("plantypecode", $plantypecode));
		$form->addElement($form->hiddenInput("plan", $plan));
		$form->addElement($form->hiddenInput("coveragetier", $coveragetier));
        $form->addElement($form->hiddenInput("carrier_id", $carrier_id));
        $form->addElement($form->hiddenInput("plantype_id", $plantype_id));
        $form->addElement($form->hiddenInput("plan_id", $plan_id));
        $form->addElement($form->hiddenInput("coveragetier_id", $coveragetier_id));
		$form->addElement($form->submitButton("save_tobacco_btn", "Save", "btn-primary pull-right"));
		$form->addElement($form->button("cancel_btn", "Cancel", "btn-default pull-right"));
		$form = $form->render();

		return $form;
	}

	private function _age_types() {
        $dropdown = array();
        $dropdown["anniversary"] = "Anniversary";
        $dropdown["washed"] = "Washed";
        return $dropdown;
    }

    private function _addCarriersToSelect2( $select2 )
    {
	    $carriers = $this->Carrier_model->get_known_carriers();
	    foreach($carriers as $carrier)
        {
            $normalized = getArrayStringValue("CarrierCode", $carrier);
            $user_description = getArrayStringValue("UserDescription", $carrier);
            $select2->addItem("", $user_description, $normalized);
        }
        return $select2;
    }

    private function _saveCarrier( $company_id, $carrier_description, $carrier_code )
    {
        // Collect and organize our data.
        $companyparent_id = GetCompanyParentId($company_id);

        // Validate our inputs.
        if ( $company_id === '' ) throw new UIException("Missing required input company_id");
        if ( $carrier_description === '' ) throw new UIException("Missing required input carrier description");
        if ( $carrier_code === '' ) throw new UIException("Missing required input carrier_code");

        // Find the corresponding CompanyCarrier record for the company and carrier and
        // set the CarrierCode on the record to match the selection they just picked.  This will
        // mark the carrier as mapped.
        $carrier_details = $this->Company_model->get_company_carrier_by_name($company_id, $carrier_description);
        $id = getArrayStringValue("Id", $carrier_details);
        if ( $id === '' ) throw new UIException("Unable to save the carrier.  Please try again.");
        $this->Company_model->update_company_carrier_code($id, $carrier_code);


        // UNSPECIFIED CARRIER
        // If the user has "Unspecified Carrier" in their data set, we will record the
        // very first thing they map "Unspecified Carrier" too. This will become the value
        // we always map when they don't have a carrier column specified.

        if ( ! $this->Mapping_model->does_column_mapping_exist($company_id, $companyparent_id, "carrier") )
        {
            // The user has NOT mapped the carrier column.

            if ( $carrier_code !== 'UNSPECIFIED CARRIER' )
            {
                // The user election was NOT Unspecified Carrier.


                // Any data in their import data that is currently set to "Unspecified Carrier" will
                // now be updated to the value they just mapped for that field.
                $current_user_description = "Unspecified Carrier";
                $selected_user_description = $this->Carrier_model->get_carrier_description_by_carrier_code($carrier_code);
                $this->Mapping_model->update_default_mapping_column($company_id, "Carrier", $current_user_description, $selected_user_description);

                // Update the CompanyCarrier record to match the code and description the user selected.
                $this->Company_model->update_company_carrier_description($id, $selected_user_description);
                $this->Company_model->update_company_carrier_code($id, $carrier_code);

                // Record this election in their company preferences so we can apply it when
                // we import data too!
                $this->Company_model->save_company_preference($company_id, "column_default_value", "carrier", $selected_user_description);

            }
        }
    }

    /**
     * _setDefaultCarriers
     *
     * Examine the data for this company.  If any of the carriers are blank or set to
     * Unspecified Carrier, attempt to default the carrier to the default company or
     * companyparent carrier specified in features.
     *
     * @param $company_id
     * @param $companyparent_id
     * @param $payload
     * @throws Exception
     */
    private function _setDefaultCarriers($company_id, $companyparent_id)
    {
        // Grab all of the data for the plan screen so we can look for carriers that need
        // to be defaulted.
        $payload = GetPlansDataReview( $company_id );

        // Find the default carrier.  The parent default carrier should be used first, if that
        // does not exist we will look for one on the company.
        $default_carrier = GetDefaultCarrier($companyparent_id, 'companyparent');
        if ( $default_carrier === '' ) $default_carrier = GetDefaultCarrier($company_id, 'company');

        // For each carrier in the data, set the carrier to the default value IF the
        // carrier is empty or unspecified.
        if ( $default_carrier !== '' )
        {
            // We might have multiple carriers that are not set.  We only need to save
            // the carrier once.  On save, we will add the carrier to this lookup and then
            // on future loops we can tell if we need to save or not.
            $lookup = array();

            $data = array();
            if ( isset($payload['data'] ) ) $data = $payload['data'];
            foreach($data as $item)
            {
                $carrier = GetArrayStringValue('Carrier', $item);
                $is_carrier_mapped = GetArrayStringValue('IsCarrierMapped', $item);
                if ( ! isset($lookup[$carrier] ) )
                {
                    if ($is_carrier_mapped === 'FALSE' )
                    {
                        try
                        {
                            $this->_saveCarrier($company_id, $carrier, $default_carrier);
                            $lookup[$carrier] = true;
                        }
                        catch (Exception $e)
                        {
                            LogIt('Unable to save default carrier.', $e->getMessage(), array('carrier' => $carrier, 'default_carrier' => $default_carrier));
                        }
                    }
                }
            }
        }
    }

    /**
     * _save_ageband_data
     *
     * This function does the heavy lifting for a "save" of the ageband data from the
     * ageband form.  It saves all of the data on the form for a specific carrier-tier.
     * The data array is basically the POST data from the ageband form.  This will
     * throw UIExceptions as it is expected to be called from an AJAX call.
     *
     * @param $data
     * @param $carrier_id
     * @param $plantype_id
     * @param $plan_id
     * @param $coveragetier_id
     * @throws UIException
     */
    private function _save_ageband_data( $data, $carrier_id, $plantype_id, $plan_id, $coveragetier_id )
    {

        // Make sure we have our index values for the key.
        if ( GetStringValue($carrier_id) === '' ) throw new UIException("Missing required input carrier_id");
        if ( GetStringValue($plantype_id) === '' ) throw new UIException("Missing required input plantype_id");
        if ( GetStringValue($plan_id) === '' ) throw new UIException("Missing required input plan_id");
        if ( GetStringValue($coveragetier_id) === '' ) throw new UIException("Missing required input coveragetier_id");

        // Validate the data.
        $keys = array_keys($data);
        foreach($keys as $key)
        {
            if ( StartsWith($key, "band") && EndsWith($key, "-start") )
            {
                $index = fBetween($key, "band", "-start");

                if ( getStringValue($index) != "X" )
                {
                    $first = getArrayStringValue("band{$index}-start", $data);
                    $second = getArrayStringValue("band{$index}-end", $data);

                    if ( getStringValue($first) == "" ) throw new UIException("Invalid age band detected.");
                    if ( getStringValue($second) == "" ) throw new UIException("Invalid age band detected.");

                    if ( strtoupper($first) == "BIRTH" ) $first = 0;
                    if ( strtoupper($first) == "B" ) $first = 0;
                    if ( strtoupper($first) == "DEATH" ) $first = 1000;
                    if ( strtoupper($first) == "D" ) $first = 1000;
                    if ( strtoupper($second) == "BIRTH" ) $second = 0;
                    if ( strtoupper($second) == "B" ) $second = 0;
                    if ( strtoupper($second) == "DEATH" ) $second = 1000;
                    if ( strtoupper($second) == "D" ) $second = 1000;

                    if ( stripNonNumeric($first) != $first ) throw new UIException("Invalid age band detected.");
                    if ( stripNonNumeric($second) != $second ) throw new UIException("Invalid age band detected.");
                    if ( $second < $first ) throw new UIException("Invalid age band detected.");
                }
            }
        }

        // Grab all of the data about the age calculation rules.  Run some validation.
        $age_calculation_type = getArrayStringValue("age_calculation_type", $data);
        $anniversary_month = getArrayStringValue("anniversary_month", $data);
        $anniversary_day = getArrayStringValue("anniversary_day", $data);
        $anniversary_year = date('Y',strtotime(GetUploadDate()));
        $age_type = $this->ageband_model->get_age_type_by_name($age_calculation_type);

        // We must find the rule in the database.
        if ( count($age_type) == 0 ) throw new UIException("Invalid age calculation rule detected.");
        $age_type_id = getArrayStringValue("Id", $age_type);

        // If anniversary rule was choosen, we must have a valid anniversary date.
        if ( $age_calculation_type == "anniversary" ) {

            $day = getIntValue($anniversary_day);
            if ( $day <= 0 || $day > 31 ) throw new UIException("Invalid day.");

            $month = getIntValue($anniversary_month);
            if ( $month <= 0 || $month > 12 ) throw new UIException("Invalid month.");

            // Make sure the date they selected is a real date.
            $anniversary_date = "{$anniversary_month}/{$anniversary_day}/{$anniversary_year}";
            $time = strtotime($anniversary_date);
            $newformat = date('m/d/Y',$time);
            if ( $anniversary_date != $newformat ) throw new UIException("Invalid date.  Did you mean {$newformat}?");

        }else{
            $anniversary_day = "";
            $anniversary_month = "";
        }

        // Grab all of the validated data that we need to start making
        // business decisions.
        $ignore_checkbox = getArrayStringValue("ignore_checkbox", $data);
        $company_id = GetSessionValue("company_id");

        if ( $ignore_checkbox == "on")
        {
            // The user has set this plan type as ignored.  Just set that value.
            $this->ageband_model->set_coverage_tier_ageband_ignored($company_id, $coveragetier_id, true);

            // Delete existing agebands as the user had deactivated agebands.
            $this->ageband_model->delete_age_bands($coveragetier_id);
        }
        else
        {
            $this->ageband_model->set_coverage_tier_ageband_ignored($company_id, $coveragetier_id, false);

            // Delete existing agebands before we save the new data.
            $this->ageband_model->delete_age_bands($coveragetier_id);

            // Create a collection of the starting band values.  Don't keep
            // blanks.  Create a reverse lookup so we can find the end value
            // after we sort the starting value.  Replace "birth" with the
            // numeric value of zero.
            $start = array();
            $lookup = array();
            $keys = array_keys($data);
            foreach($keys as $key)
            {
                if ( StartsWith($key, "band") && EndsWith($key, "-start") )
                {
                    if ( fBetween($key, "band", "-start") == "X" ) continue;
                    $value = getArrayStringValue($key, $data);
                    if ( strtoupper($value) == "BIRTH" ) $value = "0";
                    if ( strtoupper($value) == "B" ) $value = "0";
                    if ( strtoupper($value) == "DEATH" ) $value = "1000";
                    if ( strtoupper($value) == "D" ) $value = "1000";
                    if ( $value != "" )
                    {
                        $start[] = $value;
                        $lookup[$value] = $key;
                    }
                }
            }

            // Sort the starting values.
            sort($start);

            // Write the sorted agebands to the database.
            foreach($start as $range_start)
            {
                $key = getArrayStringValue($range_start, $lookup);
                $key = replaceFor($key, "start", "end");
                $range_end = getArrayStringValue($key, $data);
                if ( strtoupper($range_end) == "DEATH" ) $range_end = "1000";
                if ( strtoupper($range_end) == "D" ) $range_end = "1000";

                $this->ageband_model->insert_age_band( $coveragetier_id, $range_start, $range_end, $age_type_id, $anniversary_month, $anniversary_day );
            }
        }
    }

}
