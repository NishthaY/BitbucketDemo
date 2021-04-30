<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends SecureController {

	function __construct(){
		parent::__construct();
		$this->load->model('Wizard_model', 'wizard_model');
		$this->load->model('Company_model', 'company_model');
		$this->load->model('Queue_model', 'queue_model');
		$this->load->model('Widgettask_model', 'widgettask_model');
		$this->load->model('Reporting_model', 'reporting_model');
		$this->load->model('Life_model', 'life_model');
		$this->load->model('Retro_model', 'retro_model');
		$this->load->helper("dashboard");
		$this->load->helper("wizard");
	}

	// SCREENS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	public function index()
	{
		try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

			$company_id = GetSessionValue("company_id");
			$company = $this->company_model->get_company($company_id);
			$company_name = getArrayStringValue("company_name", $company);

			// Redirect
			if ( IsAuthenticated("support_read") ) { redirect( base_url() . "dashboard/support" ); exit; }
			if ( IsAuthenticated("parent_company_read") ) { redirect( base_url() . "dashboard/parent" ); exit; }

			// Look for a runtime error.  If we have one that we recognize as internal, we will
			// obfuscate it.
			$runtime_error = GetRuntimeError($company_id);

			// Draw the dashboard.
			$view_array = array();
			$view_array = array_merge($view_array, array("company_name" => $company_name));
			$view_array = array_merge($view_array, array("company_id" => $company_id));

			$page_template = array();
			$page_template = array_merge($page_template, array("error_message" => $runtime_error));
	        $page_template = array_merge($page_template, array("view" => "dashboard/default"));
			$page_template = array_merge($page_template, array("view_array" => $view_array));
			$page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("dashboard/js_assets")));
			$page_template = array_merge($page_template, array("dashboard_task" => $this->_dashboard_task() ));
            $page_template = array_merge($page_template, array("status_message" => GetRecentWizardStatus($company_id) ));

	        RenderView('templates/template_body_default', $page_template);

        }
		catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }

	}
	public function support() {

		// support
		//
		// This is the support dashbaord for admins.
		// ----------------------------------------------------------------
		try
		{
		    if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required security rights.");

			// Collect some data.
			$company_id = GetSessionValue("company_id");
			$company = $this->company_model->get_company($company_id);
			$company_name = getArrayStringValue("company_name", $company);

			// Draw the dashboard.
			$view_array = array();
			$view_array = array_merge($view_array, array("company_name" => $company_name));
			$view_array = array_merge($view_array, array("company_id" => $company_id));

			$page_template = array();
			$page_template = array_merge($page_template, array("view" => "dashboard/support"));
			$page_template = array_merge($page_template, array("view_array" => $view_array));
			$page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("dashboard/js_assets")));

			RenderView('templates/template_body_default', $page_template);

		}
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	public function tools() {
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated() ) throw new SecurityException("Missing required security rights.");

			// Collect some data.
			$company_id = GetSessionValue("company_id");
			$company = $this->company_model->get_company($company_id);
			$company_name = getArrayStringValue("company_name", $company);

			// Draw the dashboard.
			$view_array = array();
			$view_array = array_merge($view_array, array("company_name" => $company_name));
			$view_array = array_merge($view_array, array("company_id" => $company_id));

			$page_template = array();
			$page_template = array_merge($page_template, array("view" => "dashboard/tools"));
			$page_template = array_merge($page_template, array("view_array" => $view_array));
			$page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("dashboard/js_assets")));

			RenderView('templates/template_body_default', $page_template);

		}
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
    public function security() {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required security rights.");

            // Collect some data.
            $company_id = GetSessionValue("company_id");
            $company = $this->company_model->get_company($company_id);
            $company_name = getArrayStringValue("company_name", $company);

            // Draw the dashboard.
            $view_array = array();
            $view_array = array_merge($view_array, array("company_name" => $company_name));
            $view_array = array_merge($view_array, array("company_id" => $company_id));

            $page_template = array();
            $page_template = array_merge($page_template, array("view" => "dashboard/security"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("dashboard/js_assets")));

            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }



	// POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	public function changeback()
	{
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

			if ( ! IsActingAs() ) throw new Exception("Changeback does not exist if you are not acting as a customer.");
			ChangeBack();
			redirect( base_url("dashboard") );
            exit;

		}
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	public function save_getting_started() {
		try
		{

			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new SecurityException("Missing required input");

            // Collect the company_id from the POST or from the session, if not in the POST.
			$company_id = GetArrayStringValue('company_id', $_POST);
			if ( GetStringValue($company_id) === '' ) $company_id = GetSessionValue('company_id');

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("parent_company_write,company_write", 'company', $company_id) ) throw new SecurityException("Missing required permission company_write.");

			$user_id = GetSessionValue("user_id");

			// Validate our inputs.
			$month = getArrayStringValue("month", $_POST);
			$year = getArrayStringValue("year", $_POST);
			if ( $month == "" ) throw new UIException("Unsupported month.  Please try again later.");
			if ( $year == "" ) throw new UIException("Unsupported year.  Please try again later.");

			// Write the data as company preferences.
			$this->company_model->save_company_preference($company_id, "starting_date", "month", $month);
			$this->company_model->save_company_preference($company_id, "starting_date", "year", $year);

			// Note, startup is complete.
			$exists = $this->Wizard_model->does_wizard_record_exist($company_id);
			if ( ! $exists ) $this->Wizard_model->create_wizard_record($company_id, $user_id);
			$this->Wizard_model->startup_step_complete($company_id);

			// Audit this transaction
            $payload = array();
            $payload["Month"] = $month;
            $payload["Year"] = $year;
            AuditIt('Set start month.', $payload);

            // Notify the dashboads that a workflow step has changed.
            NotifyCompanyChannel($company_id, 'workflow_step_changed', array('company_id' => $company_id));

			AJAXSuccess("");

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}

	// RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	public function render_recent_reports_table() {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_read") ) throw new SecurityException("Missing required write permission.");

			// validate required inputs.
			$company_id = GetSessionValue("company_id");

			$array = array();
			$array['responseText'] = $this->_recent_reports_table($company_id);
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
	public function render_spend_details_table() {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_read") ) throw new SecurityException("Missing required write permission.");

			// validate required inputs.
			$company_id = GetSessionValue("company_id");

			$array = array();
			$array['responseText'] = $this->_spend_details_table($company_id);
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
	public function render_spend_cardbox() {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_read") ) throw new SecurityException("Missing required write permission.");

			// validate required inputs.
			$company_id = GetSessionValue("company_id");

			// What is the numeric value we are highlighting.
			$data = $this->Spend_model->select_spend_data_monthly_spend($company_id);
			$value = getArrayStringValue("MonthlySpend", $data);
			$value = GetReportMoneyValue($value);
			$value = replaceFor($value, "$", "");
			if ( $value == "-" ) $value = "0.00";

			// What is the description of the money value?
			$description = "Benefit Spend " . GetRecentMon($company_id);

			$view_array = array();
			$view_array = array_merge($view_array, array("value" => $value));
			$view_array = array_merge($view_array, array("description" => $description));

			$array = array();
			$array['responseText'] = RenderViewAsString("dashboard/money_cardbox", $view_array);
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
	public function render_spend_ytd_cardbox() {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_read") ) throw new SecurityException("Missing required write permission.");

			// validate required inputs.
			$company_id = GetSessionValue("company_id");

			// What is the numeric value we are highlighting.
			$data = $this->Spend_model->select_spend_data_monthly_spend_ytd($company_id);
			$value = getArrayStringValue("MonthlySpendYTD", $data);
			$value = GetReportMoneyValue($value);
			$value = replaceFor($value, "$", "");
			if ( $value == "-" ) $value = "0.00";

			// What is the description of the money value?
			$description = "Benefit Spend YTD";

			$view_array = array();
			$view_array = array_merge($view_array, array("value" => $value));
			$view_array = array_merge($view_array, array("description" => $description));

			$array = array();
			$array['responseText'] = RenderViewAsString("dashboard/money_cardbox", $view_array);
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
	public function render_spend_wash_retro_ytd_cardbox() {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexepcted method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_read") ) throw new SecurityException("Missing required write permission.");

			// validate required inputs.
			$company_id = GetSessionValue("company_id");

			// What is the numeric value we are highlighting.
			$data = $this->Spend_model->select_spend_data_wash_retro_ytd($company_id);
			$value = getArrayStringValue("WashRetroSpendYTD", $data);
			$value = GetReportMoneyValue($value);
			$value = replaceFor($value, "$", "");
			if ( $value == "-" ) $value = "0.00";

			// What is the description of the money value?
			$description = "Wash/Retro YTD";

			// What is the icon for this cardbox.
			$icon = "fa fa-exchange";

			$view_array = array();
			$view_array = array_merge($view_array, array("value" => $value));
			$view_array = array_merge($view_array, array("description" => $description));
			$view_array = array_merge($view_array, array("icon" => $icon));

			$array = array();
			$array['responseText'] = RenderViewAsString("dashboard/money_cardbox", $view_array);
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
	public function render_spend_wash_retro_ytd_percentage_cardbox() {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_read") ) throw new SecurityException("Missing required write permission.");

			// validate required inputs.
			$company_id = GetSessionValue("company_id");

			// What is the YTD Spend
			$data = $this->Spend_model->select_spend_data_monthly_spend_ytd($company_id);
			$spend = getArrayStringValue("MonthlySpendYTD", $data);

			// What is the YTD Spend Wash/Retro
			$data = $this->Spend_model->select_spend_data_wash_retro_ytd($company_id);
			$washretro = getArrayStringValue("WashRetroSpendYTD", $data);

			// This is the value we are pushing around.
			$value = 0;
			if ( $spend != 0 ) {
				$value = ( $washretro * 100 ) / $spend;
			}
			$value = getMoneyValue($value);


			// What is the description of the money value?
			$description = "Wash/Retro %";

			// What is the icon for this cardbox.
			$icon = "fa fa-percent";

			$view_array = array();
			$view_array = array_merge($view_array, array("value" => $value));
			$view_array = array_merge($view_array, array("description" => $description));
			$view_array = array_merge($view_array, array("icon" => $icon));

			$array = array();
			$array['responseText'] = RenderViewAsString("dashboard/percentage_cardbox", $view_array);
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
    public function render_recent_changeto_table()
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            $html = $this->_recent_changto_table();

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }

	// PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	private function _spend_details_table( $company_id ) {

		// _spend_details_table
		//
		// Return the table and data for the spend details widget.
		// ------------------------------------------------------------------

		$data = $this->Spend_model->select_spend_data($company_id);

		return RenderViewAsString("dashboard/spend_details_table", array("data" => $data));
	}
	private function _recent_reports_table( $company_id ) {
		$data = $this->Spend_model->select_spend_data_recent_reports_by_carrier($company_id);
		return RenderViewAsString("dashboard/recent_reports_table", array("data" => $data));
	}
	private function _dashboard_task() {
		$task_config = $this->widgettask_model->task_config('dashboard_task');
		$task = new UIBackgroundTask('dashboard_task');
		$task->setHref(base_url("widgettask/dashboard_task"));
		$task->setRefreshMinutes(getArrayIntValue("refresh_minutes", $task_config));
		$task->setDebug(getArrayStringValue("debug", $task_config));
		$task->setInfo(getArrayStringValue("info", $task_config));
		$task = $task->render();
		return $task;
	}
    private function _recent_changto_table()
    {
        $items = array();
        $items = $this->Support_model->recent_changeto_list(GetSessionValue('user_id'), 5);
        return RenderViewAsString("dashboard/recent_changeto_table", array("data" => $items));
    }



}
