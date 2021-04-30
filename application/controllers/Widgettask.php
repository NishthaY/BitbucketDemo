<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Widgettask extends SecureController {

	protected $route;

	function __construct(){
		parent::__construct();
		$this->load->model('User_model','user_model',true);
		$this->load->model('Widgettask_model','widgettask_model',true);
		$this->route = base_url("widgettask");


	}

    /**
     * workflow_widget_start
     *
     * This function will start a workflow, if it is not already running.
     * Pass in the workflow name, as defined in "Workflow" table.
     *
     * @param $workflow_name
     */
    public function workflow_widget_start($workflow_name) {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            // Set the identifier and identifier type based on session values of the requester.
            $identifier_type = 'company';
            $identifier = GetSessionValue('company_id');
            if ( GetStringValue($identifier) === '' )
            {
                $identifier_type = 'companyparent';
                $identifier = GetSessionValue('companyparent_id');
            }

            // User must have write permissions to start a workflow.
            if ( $identifier_type === 'company' && ! IsAuthenticated("company_write") ) AJAXDanger("Insufficient permissions.");
            if ( $identifier_type === 'companyparent' && ! IsAuthenticated("parent_company_write") ) AJAXDanger("Insufficient permissions.");

            // Check to see if the workflow is running.  If not, start it.
            $current_state = WorkflowStateGetCurrentState($identifier, $identifier_type, $workflow_name);
            if (empty($current_state))
            {
                WorkflowStart($identifier, $identifier_type,$workflow_name);
                WorkflowStartBackgroundJob($identifier, $identifier_type, $workflow_name, GetSessionValue('user_id'));
            }

            // Return the workflow widget HTML.
            $array['responseText'] = WorkflowWidget( $workflow_name, $identifier, $identifier_type);
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }

    /**
     * workflow_widget (AJAX)
     *
     * This function will return a running workflow widget for an
     * ajax call.
     *
     * @param $workflow_name
     * @param $identifier
     * @param string $identifier_type
     */
    public function workflow_widget($workflow_name, $identifier, $identifier_type='company') {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            // Set the identifier and identifier type based on session values of the requester.
            $identifier_type = 'company';
            $identifier = GetSessionValue('company_id');
            if ( GetStringValue($identifier) === '' )
            {
                $identifier_type = 'companyparent';
                $identifier = GetSessionValue('companyparent_id');
            }

            // User must have write permissions to start a workflow.
            if ( $identifier_type === 'company' && ! IsAuthenticated("company_write") ) AJAXDanger("Insufficient permissions.");
            if ( $identifier_type === 'companyparent' && ! IsAuthenticated("parent_company_write") ) AJAXDanger("Insufficient permissions.");

            // No running workflow state, check to see if we should start it.
            $current_state = WorkflowStateGetCurrentState($identifier, $identifier_type, $workflow_name);
            if (empty($current_state))
            {
                // If the js library is our DEFAULT library, you can auto start.  In all other cases
                // the widget creator will need to start the widget themselves.
                $wf_jslibrary = GetWorkflowProperty($workflow_name, 'WidgetJSLibrary');
                if( $wf_jslibrary === '../widget.js' )
                {
                    $this->workflow_widget_start($workflow_name);
                    return;
                }
            }

            // Render the workflow widget as the payload and send the data back.
            if ( empty($current_state) ) $array['responseText'] = "";
            if ( ! empty($current_state) ) $array['responseText'] = WorkflowWidget( $workflow_name, $identifier, $identifier_type);
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
	public function developer_tools() {
		try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

			// Dev Check
			$allowed = false;
			if ( IsDevelopment() ) $allowed = true;
			if ( IsUAT() ) $allowed = true;
			if ( ! $allowed ) throw new SecurityException("Nope.  You can't access developer tools unless you are in development.");


            $array['responseText'] = DeveloperToolsWidget();
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
	}
	public function edit_password() {
		try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            $array['responseText'] = EditPasswordWidget();
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
	}
	public function top_bar() {
		try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            $array['responseText'] = TopBarWidget();
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
	}
    public function edit_profile() {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            $array['responseText'] = EditProfileWidget();
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
	public function wizard_dashboard() {
		try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            $array['responseText'] = WizardDashboardWidget();
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
	}
	public function dashboard_task() {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

			// Do serve side work if needed here, but there might not need to
			// be any if we are using this to just refresh widgets.


			$task_config = $this->widgettask_model->task_config('dashboard_task');
			AJAXSuccess("", null, $task_config);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}

	}
    public function admin_dashboard_task() {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            // Do serve side work if needed here, but there might not need to
            // be any if we are using this to just refresh widgets.


            $task_config = $this->widgettask_model->task_config('admin_dashboard_task');
            AJAXSuccess("", null, $task_config);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }

    }
    public function export_dashboard_task() {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            // Do serve side work if needed here, but there might not need to
            // be any if we are using this to just refresh widgets.

            $task_config = $this->Widgettask_model->task_config('export_dashboard_task');
            AJAXSuccess("", null, $task_config);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }

    }
	public function dashboard_report_review() {
		try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            $array['responseText'] = ReportReviewWidget();
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
	}
	public function dashboard_welcome() {
		try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            $array['responseText'] = DashboardWelcomeWidget();
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
	}
	public function getting_started($company_id=null) {
		try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            $array['responseText'] = GettingStartedWidget($company_id);
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
	}
	public function manual_adjustment( $id="" ) {
		try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            $array['responseText'] = ManualAdjustmentWidget( $id );
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
	}
    public function parent_upload() {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            $array['responseText'] = ParentImportDataWidget();
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function skip_month($companies, $url_identifier='company') {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            $delim = '-';
            $companies = explode($delim, $companies);

            $array['responseText'] = SkipMonthProcessingWidget($companies, $url_identifier);
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }


}
