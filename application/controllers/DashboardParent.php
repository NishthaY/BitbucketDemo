<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DashboardParent extends SecureController {

    function __construct(){
        parent::__construct();
    }
    public function sample_workflow()
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_read") ) throw new SecurityException("Missing required security rights.");

            // Collect some data.
            $companyparent_id = GetSessionValue("companyparent_id");
            $parent = $this->CompanyParent_model->get_companyparent($companyparent_id);
            $company_parent_name = getArrayStringValue("name", $parent);




            // If our workflow is not running, go back to the parent dashboard.
            if ( ! HasWorkflowStarted($companyparent_id, 'companyparent', 'sample'))
            {
                redirect(base_url("dashboard/parent"));
                exit;
            }

            $sample_workflow_widget = new UIWidget("sample_workflow_widget");
            $sample_workflow_widget->setHref(base_url("widgettask/workflow/sample/{$companyparent_id}/companyparent"));
            $sample_workflow_widget->setBody(WorkflowWidget('sample', $companyparent_id, 'companyparent'));
            $sample_workflow_widget = $sample_workflow_widget->render();

            // Page Header
            $header = new UIFormHeader("Parent Dashboard");
            $header->addLink("Sample Workflow");
            $header->addWidget($sample_workflow_widget);
            $header_html = $header->render();

            // Draw the dashboard.
            $view_array = array();
            $view_array = array_merge($view_array, array("company_parent_name" => $company_parent_name));
            $view_array = array_merge($view_array, array("dashboard_task" => $this->_dashboardTask() ));
            $view_array['header_html'] = $header_html;

            $page_template = array();
            $page_template = array_merge($page_template, array("companyparent_id" => $companyparent_id));
            $page_template = array_merge($page_template, array("view" => "dashboardparent/dashboard_sample"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("dashboardparent/js_assets")));


            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

    public function quick_look()
    {
        try
        {

            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_read") ) throw new SecurityException("Missing required security rights.");

            // Collect some data.
            $company_parent_id = GetSessionValue("companyparent_id");
            $parent = $this->CompanyParent_model->get_companyparent($company_parent_id);
            $company_parent_name = getArrayStringValue("name", $parent);


            // If our workflow is not running, go back to the parent dashboard.
            if ( HasWorkflowStarted($company_parent_id, 'companyparent', 'sample'))
            {
                redirect(base_url("dashboard/parent/workflow"));
                exit;
            }

            // Do we have multi company data?
            $has_multi_company_data = false;
            $multi_company_data = $this->_getMultiCompanyData();
            if ( count($multi_company_data) > 0 ) $has_multi_company_data = true;

            // Do we have a runtime errror?
            $runtime_error = GetRuntimeError(null, $company_parent_id);

            // MultiCompany Management Widget
            $multi_company_widget = new UIWidget("multi_company_widget");
            $multi_company_widget->setHref(base_url("dashboard/parent/widget/multicompany"));
            $multi_company_widget->setCallback("InitMultiCompanyWidget");
            $multi_company_widget = $multi_company_widget->render();

            // Getting Started widget
            $getting_started_widget = new UIWidget("getting_started_widget");
            $getting_started_widget->setHref(base_url("widgettask/getting_started/COMPANYID"));
            $getting_started_widget = $getting_started_widget->render();

            // Skip Month Processing Widget
            $skip_month_widget = new UIWidget("skip_month_widget");
            $skip_month_widget->setHref(base_url("widgettask/parent/skip_month/COMPANYIDS"));
            $skip_month_widget = $skip_month_widget->render();

            // Add Company Form
            $add_form_widget = new UIWidget("add_company_widget");
            $add_form_widget->setHref(base_url("companies/widget/add"));
            $add_form_widget = $add_form_widget->render();

            // Edit Company Form
            $edit_company_widget = new UIWidget("edit_company_widget");
            $edit_company_widget->setHref(base_url("companies/widget/edit"));
            $edit_company_widget = $edit_company_widget->render();

            // Change To Company Form
            $changeto_company_widget = new UIWidget("changeto_company_widget");
            $changeto_company_widget->setHref(base_url("companies/widget/changeto"));
            $changeto_company_widget = $changeto_company_widget->render();

            // Review Downloadable Reports
            $download_list_widget = new UIWidget("download_report_list_widget");
            $download_list_widget->setHref(base_url("reports/list/COMPANYID/CARRIER/DATE"));
            $download_list_widget = $download_list_widget->render();

            // Finalization
            // Add the finalization confirmation widget if needed.
            $finalization_widget = new UIWidget("finalize_reports_widget");
            $finalization_widget->setHref(base_url("reports/finalize/COMPANYID/{$company_parent_id}"));
            $finalization_widget = $finalization_widget->render();

            // Warnings
            // Add the warnings confirmation widget if needed.
            $warnings_widget = new UIWidget("warnings_reports_widget");
            $warnings_widget->setHref(base_url("reports/warnings/TYPE/COMPANYID"));
            $warnings_widget = $warnings_widget->render();

            $add_company_button = "";
            if ( IsAuthenticated('parent_company_write') )
            {
                $view_array = array();
                $view_array['id'] = "add_company_btn";
                $view_array['name'] = "add_company_btn";
                $view_array['right'] = true;
                $view_array['label'] = "Add Company";
                $view_array['dropdown'] = array();
                $add_company_button = RenderViewAsString("dashboardparent/multi_company_button", $view_array);
            }

            $action_button = "";
            if ( $has_multi_company_data )
            {
                $view_array = array();
                $view_array['id'] = "action_btn";
                $view_array['name'] = "action_btn";
                $view_array['right'] = false;
                $view_array['label'] = "Bulk Actions";
                $view_array['dropdown'] = ['FINALIZE' => 'Finalize Reports', 'SKIP_MONTH' => 'Skip Month'];
                $action_button = RenderViewAsString("dashboardparent/multi_company_button_dropdown", $view_array);
            }

            $wf_parent_import_csv_widget = new UIWidget("wf_parent_import_csv_widget");
            $wf_parent_import_csv_widget->setHref(base_url("widgettask/workflow/parent_import_csv/{$company_parent_id}/companyparent"));
            $wf_parent_import_csv_widget->setBody(WorkflowWidget('parent_import_csv', $company_parent_id, 'companyparent'));
            $wf_parent_import_csv_widget = $wf_parent_import_csv_widget->render();


            // Page Header
            $header = new UIFormHeader("Parent Dashboard");
            $header->addLink("Quick Look");
            $header_html = $header->render();


            // Draw the dashboard.
            $view_array = array();
            $view_array = array_merge($view_array, array("company_parent_name" => $company_parent_name));
            $view_array = array_merge($view_array, array("company_parent_id" => $company_parent_id));
            $view_array = array_merge($view_array, array("multi_company_widget" => $multi_company_widget));
            $view_array = array_merge($view_array, array("edit_company_widget" => $edit_company_widget));
            $view_array = array_merge($view_array, array("changeto_company_widget" => $changeto_company_widget));
            $view_array = array_merge($view_array, array("getting_started_widget" => $getting_started_widget));
            $view_array = array_merge($view_array, array("download_list_widget" => $download_list_widget));
            $view_array = array_merge($view_array, array("add_company_form_widget" => $add_form_widget));
            $view_array = array_merge($view_array, array("finalization_widget" => $finalization_widget));
            $view_array = array_merge($view_array, array("warnings_widget" => $warnings_widget));
            $view_array = array_merge($view_array, array("wf_parent_import_csv_widget" => $wf_parent_import_csv_widget));
            $view_array = array_merge($view_array, array('has_multi_company_data' => $has_multi_company_data));
            $view_array = array_merge($view_array, array("add_company_button" => $add_company_button));
            $view_array = array_merge($view_array, array("action_button" => $action_button));
            $view_array = array_merge($view_array, array("dashboard_task" => $this->_dashboardTask() ));
            $view_array['skip_month_widget'] = $skip_month_widget;
            $view_array['header_html'] = $header_html;

            $page_template = array();
            $page_template = array_merge($page_template, array("error_message" => $runtime_error));
            $page_template = array_merge($page_template, array("companyparent_id" => $company_parent_id));
            $page_template = array_merge($page_template, array("view" => "dashboardparent/quick_look"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("dashboardparent/js_assets")));


            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

    public function widget_multi_company()
    {
        try
        {
            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_read") ) throw new SecurityException("Missing required write permission.");

            $array = array();
            $array['responseText'] = $this->_multi_company_table();
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }

    public function ajax_multi_company_row_details( $company_id )
    {
        try
        {

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_read") ) throw new SecurityException("Missing required write permission.");

            // Validate our data.
            if ( GetStringValue($company_id) === '' ) throw new Exception("Missing required input: company_id");

            // Grab all of the data for the multi company table.
            $payload = $this->_getMultiCompanyWidgetDetails($company_id);

            // Let's assume that this call is happening because of an EVENT that was triggered
            // by a company running a workflow.  If we notice that the message corresponds to
            // a state in the multi company widget that shows "extra" data, we are not going to
            // send that data back.  Rather, we will tell the widget it should refresh and pick up
            // those changes.

            $payload['refresh'] = false;
            $message = GetArrayStringValue('message', $payload);
            if ( StartsWith($message, 'Draft Reports Generated') )
            {
                $payload['refresh'] = true;
            }


            $array = array();
            $array['responseText'] = json_encode($payload);
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }

    private function _multi_company_table()
    {
        $data = $this->_getMultiCompanyData();
        return RenderViewAsString("dashboardparent/multi_company_table", array("data" => $data));
    }
    private function _getMultiCompanyData()
    {
        $companyparent_id = GetSessionValue("companyparent_id");
        $data = $this->CompanyParent_model->get_companies_by_parent($companyparent_id);

        $updated = array();
        foreach($data as $item)
        {
            $company_id = GetArrayStringValue('company_id', $item);

            // What should we display on this row?  The recent activity or the
            // planned description for each state?
            $busy = true;
            $wizard_description = GetRecentWizardStatus($company_id);
            if ( $wizard_description === '' )
            {
                $busy = false;
                $wizard_description = $this->_getMultiCompanyWidgetDescription($company_id);
            }

            $row = array();
            $row['CompanyId'] = $company_id;
            $row['Enabled'] = GetArrayStringValue('enabled', $item);
            $row['CompanyName'] = GetArrayStringValue('company_name', $item);
            $row['WizardDescription'] = $wizard_description;
            $row['DraftReportsReady'] = false;
            $row['Landing'] = $this->_getMultiCompanyWidgetContinueLink($company_id);
            if ( StartsWith($row['WizardDescription'], 'Draft Reports Generated') )
            {
                $row['DraftReportsReady'] = true;
            }
            $busy ? $row['Busy'] = 't' : $row['Busy'] = 'f';
            $busy ? $row['Status'] = 'working' : $row['Status'] =  $this->_getMultiCompanyWidgetStatus($company_id, $busy);



            if ( IsAuthenticated('parent_company_write,company_write', 'company', $company_id ) )
            {
                $updated[] = $row;
            }

        }

        // Okay, now let's sort our data.  We want it to be sorted alphabetically by company name.
        // However, we want enabled on top and disabled on bottom.
        $disabled = array();
        $enabled = array();
        foreach($updated as $item)
        {
            $item["Sort"] = GetArrayStringValue("CompanyName", $item);
            if ( GetArrayStringValue('Enabled', $item) === 't' ) $enabled[] = $item;
            if ( GetArrayStringValue('Enabled', $item) === 'f' ) $disabled[] = $item;

        }

        uasort($enabled, 'AssociativeArraySortFunction_Sort');
        uasort($disabled, 'AssociativeArraySortFunction_Sort');
        return array_merge($enabled, $disabled);
    }

    /**
     * _getMultiCompanyWidgetDetails
     *
     * This function returns a JSON object that will tell you about the
     * current state of the running or waiting wizard.  The details are
     * specific to the state of the wizard and what text we display
     * on the parent widget.
     *
     * @param $company_id
     * @return mixed
     */
    private function _getMultiCompanyWidgetDetails($company_id)
    {
        $message = $this->_getMultiCompanyWidgetDescription($company_id);
        $error = GetRuntimeError($company_id);
        $recent_activity = GetRecentWizardActivity($company_id);
        $status = $this->_getMultiCompanyWidgetStatus($company_id);
        $href = $this->_getMultiCompanyWidgetContinueLink($company_id);
        $buttons_html = $this->_getMultiCompanyWidgetButtons($company_id);

        if ( $error !== "" )
        {
            $payload['message'] = $error;
            $payload['busy'] = false;
            $payload['error'] = true;
            $payload['status'] = "attention";
            $payload['href'] = "";
            $payload['buttons'] = $buttons_html;
        }
        else if ( $recent_activity !== '' )
        {
            $payload['message'] = $recent_activity;
            $payload['busy'] = true;
            $payload['error'] = false;
            $payload['status'] = "working";
            $payload['href'] = "";
            $payload['buttons'] = $buttons_html;
        }
        else
        {
            $payload['message'] = $message;
            $payload['busy'] = false;
            $payload['error'] = false;
            $payload['status'] = $status;
            $payload['href'] = $href;
            $payload['buttons'] = $buttons_html;
        }

        return $payload;
    }

    private function _getMultiCompanyWidgetStatus($company_id, $busy=null)
    {
        // STARTUP
        if ( ! IsStartupStepComplete($company_id) ) return "";

        // Upload Step
        if ( ! IsUploadStepComplete($company_id) ) return "";

        // Parsing Step
        if ( ! IsParsingStepComplete($company_id) ) return "working";

        // Match Step
        if ( ! IsMatchStepComplete($company_id) ) return "attention";

        // Validation Step
        if ( ! IsValidationStepComplete($company_id) ) return "working";

        // Correction Step
        if ( ! IsCorrectStepComplete($company_id) ) return "attention";

        // Saving Step
        if ( ! IsSavingStepComplete($company_id) ) return "working";

        // Relationship Step
        if ( ! IsRelationshipStepComplete($company_id) ) return "attention";

        // Life Compare Step
        if ( ! IsLivesStepComplete($company_id) ) return "attention";

        // Review Step
        if ( ! IsPlanReviewStepComplete($company_id) ) return "attention";

        // Life Compare Step
        if ( ! IsClarificationsStepComplete($company_id) ) return "attention";

        // Adjustment Step
        if ( ! IsAdjustmentStepComplete($company_id) ) return "attention";

        // Generating Reports
        if ( ! IsReportGenerationStepComplete($company_id) ) return "working";

        // Finalizing
        if ( ! IsFinalizingReports($company_id) ) return "attention";
        if ( IsFinalizingReports($company_id) ) return "working";

        if ( IsWizardComplete($company_id) ) return "";

        return "";
    }

    private function _getMultiCompanyWidgetButtons($company_id)
    {
        // Get information about this company
        $company = $this->Company_model->get_company($company_id);

        // Get the widget description, this is used to make decisions on the buttons.
        $wizard_description = GetRecentWizardStatus($company_id);
        if ( $wizard_description === '' )
        {
            $wizard_description = $this->_getMultiCompanyWidgetDescription($company_id);
        }

        // Get the carriers associated with this company.
        $carriers = array();
        $report_review_data = ReportingReviewData($company_id);
        if ( ! empty($report_review_data) )
        {
            foreach($report_review_data as $item)
            {

                $carrier = GetArrayStringValue("Carrier", $item);
                $carrier_id = GetArrayStringValue("CarrierId", $item);
                $carriers = Array($carrier => $carrier_id);

            }
        }

        // Get the import date for the company
        $import_date = replaceFor(GetUploadDate($company_id), '/', '-');

        // Create the HTML for the row buttons.
        $view_array = array();
        $view_array['company_id'] = $company_id;
        $view_array['enabled'] = GetArrayStringValue('enabled', $company);
        $view_array['description'] = $wizard_description;
        $view_array['landing'] = $this->_getMultiCompanyWidgetContinueLink($company_id);
        $view_array['import_date'] = $import_date;
        $view_array['carriers'] = $carriers;
        return RenderViewAsString('dashboardparent/multi_company_table_row_buttons', $view_array);

    }
    private function _getMultiCompanyWidgetContinueLink($company_id)
    {

        // STARTUP
        if ( ! IsStartupStepComplete($company_id) ) return "";

        // Upload Step
        if ( ! IsUploadStepComplete($company_id) ) return "";

        // Parsing Step
        if ( ! IsParsingStepComplete($company_id) ) return "";

        // Match Step
        if ( ! IsMatchStepComplete($company_id) ) return base_url("wizard/match");

        // Validation Step
        if ( ! IsValidationStepComplete($company_id) ) return "";

        // Correction Step
        if ( ! IsCorrectStepComplete($company_id) ) return base_url("wizard/correct");

        // Saving Step
        if ( ! IsSavingStepComplete($company_id) ) return "";

        // Relationship Step
        if ( ! IsRelationshipStepComplete($company_id) ) return base_url("relationships");

        // Life Compare Step
        if ( ! IsLivesStepComplete($company_id) ) return base_url("lives");

        // Review Step
        if ( ! IsPlanReviewStepComplete($company_id) ) return base_url("wizard/review/plans");

        // Life Compare Step
        if ( ! IsClarificationsStepComplete($company_id) ) return base_url("clarifications");

        // Adjustment Step
        if ( ! IsAdjustmentStepComplete($company_id) ) return base_url("wizard/navigate/adjustments");

        // Generating Reports
        if ( ! IsReportGenerationStepComplete($company_id) ) return "";

        // Finalizing
        if ( ! IsFinalizingReports($company_id) ) return "";// TODO: Maybe return the deep link.
        if ( IsFinalizingReports($company_id) ) return "";

        if ( IsWizardComplete($company_id) ) return "";

        return "";
    }
    /**
     * _getMultiCompanyWidgetDescription
     *
     * Given a company, return a textual description of the wizard state
     * as we would show on the multi_company_widget.
     * @param $company_id
     * @return string
     */
    private function _getMultiCompanyWidgetDescription($company_id) {




        // STARTUP
        if ( ! IsStartupStepComplete($company_id) )
        {
            $upload_date = GetUploadDate($company_id);
            if ( $upload_date === '' )
            {
                return "Ready for initial import.";
            }
        }

        // Upload Step
        if ( ! IsUploadStepComplete($company_id) )
        {
            $starting_date = $this->_getStartingDate($company_id);
            $upload_date = GetUploadDate($company_id);
            $upload_desc = GetUploadDateDescription($company_id);
            return "Ready to import {$upload_desc}.";
        }

        // Parsing Step
        if ( ! IsParsingStepComplete($company_id) ) return "Parsing " . GetUploadDateDescription($company_id);

        // Match Step
        if ( ! IsMatchStepComplete($company_id) ) return "Additional info needed for " . GetUploadDateDescription($company_id);

        // Validation Step
        if ( ! IsValidationStepComplete($company_id) ) return "Validating " . GetUploadDateDescription($company_id);

        // Correction Step
        if ( ! IsCorrectStepComplete($company_id) ) return "Additional info needed for " . GetUploadDateDescription($company_id);

        // Saving Step
        if ( ! IsSavingStepComplete($company_id) ) return "Saving " . GetUploadDateDescription($company_id);

        // Relationship Step
        if ( ! IsRelationshipStepComplete($company_id) ) return "Additional info needed for " . GetUploadDateDescription($company_id);

        // Life Compare Step
        if ( ! IsLivesStepComplete($company_id) ) return "Additional info needed for " . GetUploadDateDescription($company_id);

        // Review Step
        if ( ! IsPlanReviewStepComplete($company_id) ) return "Additional info needed for " . GetUploadDateDescription($company_id);

        // Life Compare Step
        if ( ! IsClarificationsStepComplete($company_id) ) return "Additional info needed for " . GetUploadDateDescription($company_id);

        // Adjustment Step
        if ( ! IsAdjustmentStepComplete($company_id) ) return "Additional info needed for " . GetUploadDateDescription($company_id);

        // Generating Reports
        if ( ! IsReportGenerationStepComplete($company_id) ) return "Generating " . GetUploadDateDescription($company_id);

        // Finalizing
        if ( ! IsFinalizingReports($company_id) ) return "Draft Reports Generated: " . GetUploadDateDescription($company_id);
        if ( IsFinalizingReports($company_id) ) return "Finalizing: " . GetUploadDateDescription($company_id);

        if ( IsWizardComplete($company_id) ) return "Ready For " . GetUploadDateDescription($company_id);

        return "";

    }
    private function _dashboardTask() {
        $task_config = $this->Widgettask_model->task_config('dashboard_task');
        $task = new UIBackgroundTask('dashboard_task');
        $task->setHref(base_url("widgettask/dashboard_task"));
        $task->setRefreshMinutes(getArrayIntValue("refresh_minutes", $task_config));
        $task->setDebug(getArrayStringValue("debug", $task_config));
        $task->setInfo(getArrayStringValue("info", $task_config));
        $task = $task->render();
        return $task;
    }
    private function _getStartingDate($company_id)
    {
        $month = $this->Company_model->get_company_preference( $company_id, "starting_date", "month" );
        $month = getArrayStringValue("value", $month);
        if ( $month == "" ) return "";

        $year = $this->Company_model->get_company_preference( $company_id, "starting_date", "year" );
        $year = getArrayStringValue("value", $year);
        if ( $year == "" ) return "";

        $starting_date = "{$month}/01/{$year}";
        return $starting_date;
    }
}
