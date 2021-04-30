<?php
function WizardDashboardWidget() {

    // WizardDashboardWidget
    //
    // Little chunk of dynamic HTML on the dashboard that is used to starts
    // the upload process.  If you are in the middle of the process, it will
    // be used to notify you of information or grant you access to your Current
    // step in the wizard process.
    // -----------------------------------------------------------------------

    $CI = &get_instance();
    $CI->load->helper('wizard');
    $CI->load->model('Wizard_model', 'wizard_model');
    $CI->load->model('Company_model', 'company_model');
    $user_id = GetSessionValue("user_id");
    $company_id = GetSessionValue("company_id");

    // If you are not authenticated, return no content for the widget.
    if ( ! IsAuthenticated("company_write") ) return "";

    // STARTUP
    if ( ! IsStartupStepComplete($company_id) )
    {

        // Grab existing upload data.
        $info = $CI->Wizard_model->get_upload_date_info($company_id);
        if ( ! empty($info) )
        {
            // We have some!  Just mark this step complete.
            $CI->Wizard_model->startup_step_complete($company_id);
        }else{
            // Nope, first time user, show the first time view.
            $view_array = array();
            $html = RenderViewAsString("wizard/widget_dashboard_first_time", $view_array);
            return $html;
        }
    }

    // Upload Step
    if ( ! IsUploadStepComplete($company_id) )
    {
        $upload = GetUploadFormData();
        $view_array = array();
        $view_array['identifier'] = $company_id;
        $view_array['identifier_type'] = 'company';
        $view_array = array_merge($view_array, array("upload_attributes" => $upload['attributes']));
        $view_array = array_merge($view_array, array("upload_inputs" => $upload['inputs']));
        $html = RenderViewAsString("wizard/widget_dashboard_start", $view_array);
        return $html;
    }

    // Parsing Step
    if ( ! IsParsingStepComplete($company_id) )
    {
        $view_array = array();
        $view_array = array_merge($view_array, array("href" => ""));
        $view_array = array_merge($view_array, array("message" => "Parsing " . GetUploadDateDescription()));
        $html = RenderViewAsString("wizard/widget_wizard", $view_array);
        return $html;
    }

    // Match Step
    if ( ! IsMatchStepComplete($company_id) )
    {
        $view_array = array();
        $view_array = array_merge($view_array, array("href" => base_url("wizard/match")));
        $view_array = array_merge($view_array, array("message" => "Continue " . GetUploadDateDescription()));
        $html = RenderViewAsString("wizard/widget_wizard", $view_array);
        return $html;
    }

    // Validation Step
    if ( ! IsValidationStepComplete($company_id) )
    {
        //base_url("wizard/validation")
        $view_array = array();
        $view_array = array_merge($view_array, array("href" => ""));
        $view_array = array_merge($view_array, array("message" => "Validating " . GetUploadDateDescription()));
        $html = RenderViewAsString("wizard/widget_wizard", $view_array);
        return $html;
    }

    // Correction Step
    if ( ! IsCorrectStepComplete($company_id) )
    {
        $view_array = array();
        $view_array = array_merge($view_array, array("href" => base_url("wizard/correct")));
        $view_array = array_merge($view_array, array("message" => "Continue " . GetUploadDateDescription()));
        $html = RenderViewAsString("wizard/widget_wizard", $view_array);
        return $html;
    }

    // Saving Step
    if ( ! IsSavingStepComplete($company_id) )
    {
        $view_array = array();
        $view_array = array_merge($view_array, array("href" => ""));
        $view_array = array_merge($view_array, array("message" => "Saving " . GetUploadDateDescription()));
        $html = RenderViewAsString("wizard/widget_wizard", $view_array);
        return $html;
    }

    // Relationship Step
    if ( ! IsRelationshipStepComplete($company_id) )
    {
        $view_array = array();
        $view_array = array_merge($view_array, array("href" => base_url("relationships")));
        $view_array = array_merge($view_array, array("message" => "Continue " . GetUploadDateDescription()));
        $html = RenderViewAsString("wizard/widget_wizard", $view_array);
        return $html;
    }

    // Life Compare Step
    if ( ! IsLivesStepComplete($company_id) )
    {
        $view_array = array();
        $view_array = array_merge($view_array, array("href" => base_url("lives")));
        $view_array = array_merge($view_array, array("message" => "Continue " . GetUploadDateDescription()));
        $html = RenderViewAsString("wizard/widget_wizard", $view_array);
        return $html;
    }

    // Review Step
    if ( ! IsPlanReviewStepComplete($company_id) )
    {
        $view_array = array();
        $view_array = array_merge($view_array, array("href" => base_url("wizard/review/plans")));
        $view_array = array_merge($view_array, array("message" => "Continue " . GetUploadDateDescription()));
        $html = RenderViewAsString("wizard/widget_wizard", $view_array);
        return $html;
    }

    // Life Compare Step
    if ( ! IsClarificationsStepComplete($company_id) )
    {
        $view_array = array();
        $view_array = array_merge($view_array, array("href" => base_url("clarifications")));
        $view_array = array_merge($view_array, array("message" => "Continue " . GetUploadDateDescription()));
        $html = RenderViewAsString("wizard/widget_wizard", $view_array);
        return $html;
    }

    // Adjustment Step
    if ( ! IsAdjustmentStepComplete($company_id) )
    {
        $view_array = array();
        $view_array = array_merge($view_array, array("href" => base_url("wizard/navigate/adjustments")));
        $view_array = array_merge($view_array, array("message" => "Continue " . GetUploadDateDescription()));
        $view_array = array_merge($view_array, array("id" => "wizard_adjustments_btn"));
        $html = RenderViewAsString("wizard/widget_wizard", $view_array);
        return $html;
    }

    // Generating Reports
    if ( ! IsReportGenerationStepComplete($company_id) )
    {
        $view_array = array();
        $view_array = array_merge($view_array, array("href" => ""));
        $view_array = array_merge($view_array, array("message" => "Generating " . GetUploadDateDescription()));
        $html = RenderViewAsString("wizard/widget_wizard", $view_array);
        return $html;
    }

    // Finalizing
    if ( IsFinalizingReports($company_id) )
    {
        $view_array = array();
        $view_array = array_merge($view_array, array("href" => ""));
        $view_array = array_merge($view_array, array("message" => "Finalizing " . GetUploadDateDescription()));
        $html = RenderViewAsString("wizard/widget_wizard", $view_array);
        return $html;
    }

    return "<span class='hidden'>SHUTDOWN</span>";


}
function EditProfileWidget() {

    $CI = &get_instance();
    $CI->load->model('User_model', 'user_model');
    $user = $CI->user_model->get_user_by_id(GetSessionValue("user_id"));
    $user_id = getArrayStringValue("user_id", $user);
    $login_details = $CI->Login_model->get_login_details($user_id);

    $form = new UIModalForm("edit_account_form", "edit_account_form", base_url("settings/account/save"));
    $form->setTitle("Edit Account ( ".getArrayStringValue("email_address", $user)." )");
    $form->addElement($form->textInput("company", "Company Name", getArrayStringValue("company", $user), "Company Name", null , true));

    $phone_nbr = getArrayStringValue("TwoFactorPhoneNumber", $login_details);
    if ( $phone_nbr !== '' )
    {
        $inline = $form->inlineInput(
            "phone"
            , "Clear"
            , DisplayPhoneNumber($phone_nbr)
            , base_url("users/reset/phone/{$user_id}")
            , "UserPhoneResetSuccess"
            , "SMS Capable Phone Number"
            , "UserPhoneResetFailed"
        );
        $form->addElement($inline);
    }


    $form->addElement($form->emailInput("email_address", "Email Address", getArrayStringValue("email_address", $user), "Ex: manager@company.com"));
    $form->addElement($form->textInput("firstname", "First Name", getArrayStringValue("first_name", $user), "John"));
    $form->addElement($form->textInput("lastname", "Last Name", getArrayStringValue("last_name", $user), "Smith"));
    $form->addElement($form->hiddenInput("original_email_address", getArrayStringValue("email_address", $user)));
    $form->addElement($form->submitButton("edit_account_btn", "Save Changes", "btn-primary pull-right"));
    $form->addElement($form->button("reset_button", "Cancel", "btn-default pull-right"));
    $form_html = $form->render();

    return $form_html;
}
function EditPasswordWidget() {


    $CI = &get_instance();
    $CI->load->model('User_model', 'user_model');
    $user = $CI->user_model->get_user_by_id(GetSessionValue("user_id"));

    $form = new UIModalForm("edit_password_form", "edit_password_form", base_url("auth/password/save"));
    $form->setTitle("Change Password ( ".getArrayStringValue("email_address", $user)." )");
    $form->addElement($form->passwordInput("old_password", "Current Password", null, "Current Password"));
    $form->addElement($form->passwordInput("new_password", "New Password", null, "New Password"));
    $form->addElement($form->passwordInput("confirm_password", "Confirm Password", null, "Confirm Password"));
    $form->addElement($form->submitButton("edit_password_btn", "Save Password", "btn-primary pull-right"));
    $form->addElement($form->button("reset_button", "Cancel", "btn-default pull-right"));

    $form_html = $form->render();

    return $form_html;
}
function TopBarWidget() {

    $out = RenderViewAsString("templates/top_bar", array());
    return $out;

}
function WorkflowWidget($workflow_name, $identifier, $identifier_type, $action='')
{
    $CI = &get_instance();
    $CI->load->model('Workflow_model', 'wf_model');

    if ( $action === 'start' )
    {
        WorkflowStart($identifier, $identifier_type, $workflow_name);
    }

    // WORKFLOW
    // Find the workflow.
    $wf = WorkflowFind($workflow_name);

    // CURRENT STATE
    // What is it's most recent state?
    $state = WorkflowStateGetCurrentState($identifier, $identifier_type, $workflow_name);

    $wf_id = GetArrayIntValue("Id", $wf);
    $workflow_name = GetArrayStringValue("Name", $wf);
    $state_name = GetArrayStringValue("Name", $state);
    $view = "workflows/widget";

    if ( empty($wf) )
    {
        // I don't know the workflow.  Can't show anything.
        return "";
    }
    else if ( empty($state) )
    {
        // We have a workflow, but no active state.  That means the
        // widget is in the "not started" state.
        $search_view = "workflows/{$workflow_name}/not_started";
        if ( file_exists(APPPATH . "views/{$search_view}.php") ) $view = $search_view;
    }
    else
    {
        // Render the widget based on the active state.
        $search_view = "workflows/{$workflow_name}/{$state_name}";
        if ( file_exists(APPPATH . "views/{$search_view}.php") ) $view = $search_view;
    }

    // If the Workflow has a JSLibrary specified as a property, go ahead
    // and add the HTML that will trigger the load once the page has been rendered.
    $properties = "";
    if ( $workflow_name !== '' && $wf_id !== '' )
    {
        $wf_jslibrary = GetWorkflowProperty($workflow_name, 'WidgetJSLibrary');

        $view_array = array();
        $view_array['workflow_name'] = $workflow_name;
        $view_array['wf_jslibrary'] = $wf_jslibrary;
        $view_array['properties'] = $CI->wf_model->get_wf_properties( $wf_id );
        $properties = RenderViewAsString('workflows/widget_properties', $view_array);

    }




    $view_array = array();
    $view_array['workflow'] = $wf;
    $view_array['state'] = $state;
    $view_array['identifier'] = $identifier;
    $view_array['identifier_type'] = $identifier_type;
    $view_array['properties'] = $properties;

    return RenderViewAsString($view, $view_array);


}
function DeveloperToolsWidget() {
    $out = RenderViewAsString("developer_tools", array());
    return $out;
}
function ReportReviewWidget() {

    $CI = &get_instance();
    $output = RenderViewAsString("dashboard/review_draft_reports");

    return $output;
}
function DashboardWelcomeWidget() {

    $CI = &get_instance();
    $company_id = GetSessionValue("company_id");
    $wizard = $CI->Wizard_model->select_wizard_data($company_id);
    $output = RenderViewAsString("dashboard/welcome", array("data" => $wizard));

    return $output;
}
function SkipMonthProcessingWidget($companyids=array(), $url_identifier='company')
{
    if ( empty($companyids) ) return false;

    // Initialize our data.
    $data = array();
    $company_id = "";
    $company_name = "";
    $date_description = "";
    $companies = "";

    // Use different URLs for when this requests comes from the company vs the parent.
    // There is a slight difference in what we need to do at save time.  Having the
    // shared from post to a different location will allow us to determine the requesting location.
    $url = "dashboard/company/save/skip_month";
    if ( strtolower($url_identifier) === 'parent' ) $url = "dashboard/parent/save/skip_month";

    // Filter out any companies that are not qualified for skip month processing.
    // Keep track of the ones that are not eligible and why.
    $filtered = [];
    $borked = [];
    if ( ! empty($companyids) )
    {
        foreach($companyids as $company_id)
        {
            $eligible = IsSkipMonthProcessingAllowed($company_id, 'company');
            if ( $eligible === TRUE )
            {
                $filtered[] = $company_id;
            }
            else
            {
                $borked[$company_id] = GetStringValue($eligible);
            }
        }
    }
    $companyids = $filtered;


    // When we filtered the companies above, we kept track of the ones that
    // were not eligible in the borked array.  Turn that into something useful.
    $not_eligible = [];
    foreach($borked as $company_id=>$reason)
    {
        $item = array();
        $item['company_id'] = $company_id;
        $item['company_name'] = GetIdentifierName($company_id, 'company');
        $item['date_description'] = GetUploadDateDescription($company_id);
        $item['reason'] = $reason;
        $not_eligible[] = $item;
    }

    // How many total companies are we working with?  Both eligible and not.
    $total_companies = count($companyids) + count($not_eligible);

    if ( count($companyids) === 0 )
    {
        // Generate a form dialog that indicates nothing can be processed at this time.
        $view_array = [];
        $view_array['not_eligible'] = $not_eligible;

        // If the user's selection resulted in no eligible companies, tell them
        // that and do not allow them to start the processes.
        $form = new UIModalForm("skip_month_processing_form", "skip_month_processing_form", base_url($url));
        $form->setTitle( 'Skip Month Processing' );
        $form->addElement($form->htmlView("dashboard/skip_month_processing_help_no_results", $view_array));
        $form->addElement($form->button("cancel_skip_month_processing_button", "Okay", "btn-default pull-right"));
        $form = $form->render();
        return $form;
    }
    else if ( $total_companies > 1 )
    {
        // Create a form dialog that will allow the user to confirm multiple companies
        $data = array();
        foreach($companyids as $company_id)
        {
            if ( IsSkipMonthProcessingAllowed($company_id, 'company') )
            {
                $item = array();
                $item['company_id'] = $company_id;
                $item['company_name'] = GetIdentifierName($company_id, 'company');
                $item['date_description'] = GetUploadDateDescription($company_id);
                $data[] = $item;

                $companies .= $company_id . "-";
            }
        }
    }
    else
    {
        // Show a form dialog that will allow the user to confirm a single company.
        $company_id = $companyids[0];
        $company_name = GetIdentifierName($company_id, 'company');
        $date_description = GetUploadDateDescription($company_id);

        $companies .= $company_id . "-";
    }
    $companies = fLeftBack($companies, "-");


    $view_array = [];
    $view_array['data'] = $data;
    $view_array['company_id'] = $company_id;
    $view_array['company_name'] = $company_name;
    $view_array['date_description'] = $date_description;
    $view_array['not_eligible'] = $not_eligible;

    $title = "Skip Month Processing - ";
    if ( count($data) === 0 ) {
        $title .= $company_name;
    }
    else
    {
        $title .= "Multiple Companies";
    }

    // Create the form.
    $form = new UIModalForm("skip_month_processing_form", "skip_month_processing_form", base_url($url));
    $form->setTitle( $title );
    $form->addElement($form->htmlView("dashboard/skip_month_processing_help", $view_array));
    $form->addElement($form->hiddenInput("companies", $companies));
    $form->addElement($form->submitButton("skip_month_processing_button", "Yes", "btn-primary pull-right"));
    $form->addElement($form->button("cancel_skip_month_processing_button", "No", "btn-default pull-right"));
    $form = $form->render();
    return $form;


}
function GettingStartedWidget($company_id=null) {

    $CI = &get_instance();
    $CI->load->helper("dashboard");

    if ( GetStringValue($company_id) === '' ) GetSessionValue('company_id');

    if ( ! IsAuthenticated("parent_company_write,company_read", 'company', $company_id) ) return "";

    $suggested = getdate(strtotime("+1 months"));
    $suggested_month = getArrayStringValue("mon", $suggested);
    $suggested_month = str_pad($suggested_month,2,"0",STR_PAD_LEFT);
    $suggested_year = getArrayStringValue("year", $suggested);

    // Create the form.
    $form = new UIModalForm("getting_started_form", "getting_started_form", base_url("dashboard/save/getting_started"));
    $form->setTitle( "Getting Started" );
    $form->setLead("Welcome to Advice2Pay");
    $form->setDescription("Let's get started!  To build your custom reports, we will walk you through a few steps.  The first time through, you will upload a data file and we will investigate that file and ask you for input on what we find.  The next time, we will only ask you for help if things look different.  Some steps may take a few minutes to complete depending on how large your upload file is.  You can wait if you want, but we will send you an email when we need your input during the upload process.");
    $form->addElement($form->dropdown("month", "Upload File Month", null, DropdownMonths(), $suggested_month, "", "", false, true));
    $form->addElement($form->dropdown("year", "Upload File Year", null, GettingStartedYears(), $suggested_year, "", "", false, true));
    $form->addElement($form->htmlView("dashboard/first_month_help", array()));
    $form->addElement($form->hiddenInput("company_id", $company_id));
    $form->addElement($form->submitButton("save_getting_started_button", "Save", "btn-primary pull-right"));
    $form->addElement($form->button("cancel_getting_started_button", "Cancel", "btn-default pull-right"));
    $form = $form->render();

    return $form;
}
function ManualAdjustmentWidget( $id="" ) {

    $CI = &get_instance();
    $CI->load->helper("dashboard");


    if ( ! IsAuthenticated("company_write") ) return "";

    $company_id = GetSessionValue("company_id");
    $carrier = "";
    $type = "credit";
    $description = "";
    $amount = "";

    // Update the details if we were given an id.
    if( getStringValue($id) != "" )
    {
        $adjustment = $CI->Adjustment_model->select_manual_adjustment($company_id, $id);
        $carrier = getArrayStringValue("CarrierId", $adjustment);
        $type = getArrayStringValue("Type", $adjustment);
        $description = getArrayStringValue("Memo", $adjustment);
        $amount =  getArrayStringValue("Amount", $adjustment);

        $amount = getMoneyValue($amount);
        $amount = replaceFor($amount, "$", "");
        $type = strtolower($type);

    }



    // Create the form.
    $form = new UIModalForm("manual_adjustment_form", "manual_adjustment_form", base_url("adjustments/save/adjustment"));
    $form->setTitle(GetUploadDateDescription($company_id) . " Manual Adjustments");
    $form->addElement($form->dropdown("carrier_id", "Carrier", null, DropdownCarriers($company_id), $carrier, ""));
    $form->addElement($form->textInput("description", "Description", $description, "", null , false));
    $form->addElement($form->dropdown("type_id", "Transaction Type", null, DropdownTransactionType(), $type, ""));
    $form->addElement($form->moneyInput("amount", "Amount", $amount, "0.00", null , false));
    $form->addElement($form->submitButton("save_manual_adjustment_button", "Save", "btn-primary pull-right"));
    $form->addElement($form->button("cancel_manual_adjustment_button", "Cancel", "btn-default pull-right"));
    $form->addElement($form->hiddenInput("adjustment_id", $id));
    $form = $form->render();

    return $form;
}
function DropdownTransactionType() {
    $types = array();
    $types['credit'] = "Credit";
    $types['debit'] = "Debit";
    return $types;
}
function DropDownCarriers($company_id){
    $CI = &get_instance();
    $data = $CI->Reporting_model->select_summary_report_carriers($company_id);

    $carriers = array();
    if ( ! empty($data) )
    {
        foreach($data as $carrier)
        {
            $carrier_id = getArrayStringValue("CarrierId", $carrier);
            $carrier_name = getArrayStringValue("CarrierDescription", $carrier);
            if ( getArrayStringValue("PremiumEquivalentFlg", $carrier) == "t" ) continue;
            $carriers[$carrier_id] = $carrier_name;
        }
    }
    return $carriers;
}

/* End of file widget_task_helper.php */
/* Location: ./application/helpers/widget_task_helper.php */
