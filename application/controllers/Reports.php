<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends SecureController {

    protected $route;

	function __construct()
    {
		parent::__construct();
        $this->load->model('User_model','user_model',true);
        $this->load->model('Company_model','company_model',true);
        $this->load->helper('Companies_helper');
        $this->load->library('form_validation');
    }

    // SCREENS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function index()
    {
	    try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            $company_id = GetSessionValue("company_id");

            // Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_read") ) throw new SecurityException("Missing required permission.");
            if ( getStringValue($company_id) == "" ) throw new SecurityException("Missing required input company_id.");

            // Page Header
            $header = new UIFormHeader("Reports");
            $header->addLink("Reports", base_url("reports"));
            $header->addLink("Settings", base_url("reports/settings"));
            $header_html = $header->render();
            
            // Pull our data.
            $draft = $this->Reporting_model->select_draft_reports($company_id);
            $finalized = $this->Reporting_model->select_report_history($company_id);

            // If report generation is not complete, we wil hide the draft section
            // event if there appear to be draft reports.
            if ( ! IsReportGenerationStepComplete() ) $draft = array();

            $view = "reports/reports";
            if ( empty($draft) && empty($finalized) ) $view = "reports/reports_noresults";

            $view_array = array();
            $view_array = array_merge($view_array, array( "finalized" => $finalized));
            $view_array = array_merge($view_array, array( "draft" => $draft));
            $view_array = array_merge($view_array, array( "form_header" => $header_html));
            $view_array = array_merge($view_array, array( "company_id" => $company_id));

			$page_template = array();
			$page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("reports/js_assets")));
			$page_template = array_merge($page_template, array("view" => $view));
			$page_template = array_merge($page_template, array("view_array" => $view_array));
			RenderView('templates/template_body_default', $page_template);

		}
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }

	}



    /**
     * settings
     *
     * Render the report settings screen.  This will outline the various data the user
     * selected when processing a companies monthly report.  This can be used to verify
     * settings in bulk.
     *
     * @param null $year_mo
     */
    public function settings( $year_mo=null )
    {
	    try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            $company_id = GetSessionValue("company_id");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_read") ) throw new SecurityException("Missing required permission.");
            if ( getStringValue($company_id) == "" ) throw new SecurityException("Missing required input company_id.");

            // Find or Default the selected carrier.
            // We no longer take carrier_id in as an input.  Thus, we will be defaulting to the first
            // carrier in the report list.  This is acceptable because the settings are global per month.
            // Which carrier does not matter.
            $carriers = $this->_getCarrierMenuData($company_id);
            if ( empty($carriers['menu']) ) throw new NoResultsException();
            $selected_carrier = $carriers['menu'][$carriers['selected_index']];
            $carrier_id = $carriers['menu'][$carriers['selected_index']]['value'];

            // Find or Default the selected report.
            $reports = $this->_getReportsMenuData($company_id, $carrier_id, $year_mo);
            if ( empty($reports['menu']) ) throw new NoResultsException();
            $selected_report = $reports['menu'][$reports['selected_index']];
            $year_mo = $reports['menu'][$reports['selected_index']]['value'];

            $header = new UIFormHeader("Report Settings");
            $header->addLink("Reports", base_url("reports"));
            $header->addLink("Settings", base_url("reports/settings"));

            // YEARMO HEADER DROPDOWN
            // Since we had results, add all the possible dates to the dropdown link.
            $selected_text = $selected_report['display'];
            foreach($reports['menu'] as $menu_item)
            {
                $display = GetArrayStringValue('display', $menu_item);
                $link = GetArrayStringValue('link', $menu_item);
                $header->addLinkDropdown('Reports', $display, $link, $selected_text);
            }

            // Relationship Settings.
            $view_array = array();
            $view_array['title'] = "Relationship Settings";
            $view_array['data'] = $this->_getRelationshipSettings($company_id, 'company', $year_mo);
            ! empty($view_array['data']) ? $relationship_settings = RenderViewAsString("settings/settings_widget", $view_array) : $relationship_settings = "";

            // Column Mapping Settings.
            $view_array = array();
            $view_array['title'] = "CSV Column Settings";
            $view_array['data'] = $this->_getColumnMappingSettings($company_id, 'company', $year_mo);
            ! empty($view_array['data']) ? $column_settings = RenderViewAsString("settings/settings_widget", $view_array) : $column_settings = "";

            // Plan Settings.
            $view_array = array();
            $view_array['title'] = "Plan Settings";
            $view_array['data'] = $this->_getPlanSettings($company_id, 'company', $year_mo);
            ! empty($view_array['data']) ? $plan_settings = RenderViewAsString("settings/settings_widget", $view_array) : $plan_settings = "";

            // Review each of the widgets we are about to display.  If we found
            // nothing, show no results.
            $found_results = false;
            if ( $relationship_settings !== '' ) $found_results = true;
            if ( $column_settings !== '' ) $found_results = true;
            if ( $plan_settings !== '' ) $found_results = true;

            // Did not find anything on AWS!
            if ( ! $found_results )
            {
                $view_array = array();
                $view_array = array_merge($view_array, array( "form_header" => $header->render()));

                $page_template = array();
                $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("reports/js_assets")));
                $page_template = array_merge($page_template, array("view" => 'reports/report_settings_no_results'));
                $page_template = array_merge($page_template, array("view_array" => $view_array));
                RenderView('templates/template_body_default', $page_template);
                return;
            }



            $view_array = array();
            $view_array = array_merge($view_array, array( "form_header" => $header->render()));
            $view_array['relationship_settings'] = $relationship_settings;
            $view_array['column_settings'] = $column_settings;
            $view_array['plan_settings'] = $plan_settings;

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("reports/js_assets")));
            $page_template = array_merge($page_template, array("view" => 'reports/report_settings'));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( NoResultsException $e )
        {
            $header = new UIFormHeader("Report Settings");
            $header->addLink("Reports", base_url("reports"));
            $header->addLink("Settings", base_url("reports/settings"));

            $view_array = array();
            $view_array = array_merge($view_array, array( "form_header" => $header->render()));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("reports/js_assets")));
            $page_template = array_merge($page_template, array("view" => 'reports/report_settings_no_results'));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);
            return;
        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }

    }


    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    public function finalize() {
        try
        {
            // Make sure you are logged in.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            $company_ids = explode(":", GetArrayStringValue("company_ids", $_POST));
            $user_id = GetSessionValue('user_id');
            $group_id = GetArrayStringValue('group_id', $_POST);

            foreach ($company_ids as $company_id)
            {

                // Do not allow the user to try and finalize unless they are far enough along in the wizard to allow it.
                $ready = true;
                if ( ! IsStartupStepComplete($company_id) ) $ready = false;
                if ( ! IsUploadStepComplete($company_id) )  $ready = false;
                if ( ! IsMatchStepComplete($company_id) ) $ready = false;
                if ( ! IsCorrectStepComplete($company_id) ) $ready = false;
                if ( ! IsPlanReviewStepComplete($company_id) ) $ready = false;
                if ( ! IsReportGenerationStepComplete($company_id) ) $ready = false;
                if ( ! IsAdjustmentStepComplete($company_id) ) $ready = false;
                if ( IsFinalizingReports($company_id) ) $ready = false;


                if ( $ready )
                {
                    // Since the finalization step is user driven, when you land here the
                    // 'step' where the user confirms the reports are good is done.  Issue a
                    // step complete event here so the dashboard knows and can draw itself accordingly.
                    NotifyStepComplete($company_id);

                    $companyparent_id = GetCompanyParentId($company_id);

                    $this->Wizard_model->finalization_started($company_id);
                    if ( $group_id == '' ) $this->Queue_model->add_worker_job($companyparent_id, $company_id, $user_id, "FinalizeReports", "index");
                    if ( $group_id != '' ) $this->Queue_model->add_grouped_worker_job($companyparent_id, $company_id, $user_id, $group_id, "FinalizeReports", "index");

                }


            }

            AJAXSuccess("Report finalization complete.", base_url("dashboard"));

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }
    public function warnings() {
        try
        {
            // Make sure you are logged in.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");


            AJAXSuccess("Report warnings complete.");

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }

    // RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function render_downloadable_reports_form($company_id, $carrier_id, $target_date)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) == "GET" ) $inputs = $_GET;
            if ( getStringValue($this->input->server('REQUEST_METHOD')) == "POST" ) $inputs = $_POST;

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write,parent_company_read", 'company', $company_id) ) throw new SecurityException("Missing required write permission.");

            // organize inputs.
            if ( GetStringValue($company_id) === '' ) GetArrayStringValue("company_id", $inputs);
            if ( GetStringValue($carrier_id) === '' ) GetArrayStringValue("carrier_id", $inputs);

            // validate required inputs.
            if ( $company_id == "" ) throw new Exception("Invalid input company_id");
            if ( $carrier_id == "" ) throw new Exception("Invalid input carrier_id");
            if ( $target_date == "" ) throw new Exception("Invalid input target_date");

            $target_date = ReplaceFor($target_date, "-", "/");
            $form_html = $this->_downloadable_reports_form($company_id, $carrier_id, $target_date);

            $array = array();
            $array['responseText'] = $form_html;
            AJAXSuccess("", null, $array);


        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_finalize_reports_form($company_ids, $group_id="") {
		try
		{
			// Check method.
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new Exception("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            // We must have been given at least one company id.  the input is a string of company ids delimited by :
            $items = explode(':', $company_ids);
            if ( count($items) == 0 ) throw new Exception("Missing required input company_id(s)");

            // Check each company_id and see if they are authenticated to write.  Throw if no authenticated company ids.
			$authenticated = array();
			foreach( $items as $company_id)
            {
                if ( IsAuthenticated("parent_company_write,company_write", 'company', $company_id) )
                {
                    $authenticated[] = $company_id;
                }
            }
			if ( count($authenticated) == 0 ) throw new SecurityException("Missing required authentication.");



			// organize inputs.
			$company_id = getStringValue($company_id);

			// validate required inputs.
			if ( $company_id == "" ) throw new Exception("Invalid input company_id");

			$finalize_reports_form = $this->_finalize_reports_form($authenticated, $group_id);

			$array = array();
			$array['responseText'] = $finalize_reports_form;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
    public function render_reports_warning_form($company_id, $type) {
        try
        {
            // Check method.
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new Exception("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write,company_write", 'company', $company_id) );


            $form = $this->_report_warnings_form($company_id, $type);

            $array = array();
            $array['responseText'] = $form;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    private function _report_warnings_form( $company_id, $type )
    {
        if ( GetStringValue($company_id) === '' ) return "";

        if ( $type === 'critical' )
        {
            $title = "Critical Warnings";
            $warnings = $this->Reporting_model->select_report_review_warnings_confirmation($company_id);
            $message = "The following critical warnings should be reviewed and corrected prior to finalization.";
            $critical = true;
        }
        else
        {
            $title = "Review Notices";
            $warnings = $this->Reporting_model->select_report_review_warnings_not_confirmation($company_id);
            $message = "The following notices may indicate items to review and/or correct in your import data prior to finalization.";
            $critical = false;
        }

        $view_array = array();
        $view_array['company_id'] = $company_id;
        $view_array['warnings'] = $warnings;
        $view_array['message'] = $message;
        $view_array['critical'] = $critical;

        $form = new UIModalForm("report_warnings_form", "report_warnings_form", base_url("reports/warnings"));
        $form->setTitle($title);
        $form->addElement($form->htmlView("reports/table_of_warnings", $view_array));
        $form->addElement($form->hiddenInput("company_id", getStringValue($company_id)));
        $form->addElement($form->submitButton("yes_btn", "Close", "btn-primary pull-right"));
        //$form->addElement($form->button("no_btn", "No", "btn-default pull-right"));
        $html = $form->render();
        return $html;
    }
    private function _finalize_reports_form( $company_ids, $group_id="" ) {

	    if ( count($company_ids) == 0 ) return "";

	    $form_html = "";
	    if ( count($company_ids) == 1 )
        {
            $company_id = $company_ids[0];
            // Select warnings that require confirmation.  From those, limit them to a small number
            // as there should not be very many.  If there are, then do not exceed the max and tell
            // them how many more there are.
            $critical = false;
            $max = 5;
            $warnings = $this->Reporting_model->select_report_review_warnings_confirmation($company_id);
            if ( count($warnings) > $max )
            {
                $extra = count($warnings) - $max;
                $leftovers = array_slice($warnings, $max);
                $warnings[] = array("Issue" => "<div class='text-center'>Plus {$extra} more!</div>");
            }

            // Do not allow the reports to be finalized if there are critical warnings.
            $disabled = false;
            if ( count($warnings) !== 0 ) $disabled = true;
            if ( count($warnings) !== 0 ) $critical = true;

            $form = new UIModalForm("finalize_reports_form", "finalize_reports_form", base_url("reports/finalized"));
            $form->setTitle("Finalize Data");
            $form->addElement($form->htmlView("reports/confirm_finalization", array('warnings' => $warnings, 'critical' => $critical)));
            $form->addElement($form->hiddenInput("company_id", getStringValue($company_id)));
            $form->addElement($form->hiddenInput("company_ids", implode(':', $company_ids)));
            $form->addElement($form->hiddenInput("group_id", getStringValue($group_id)));

            if( $disabled )
            {
                $form->addElement($form->button("no_btn", "Okay", "btn-default pull-right"));
            }
            else
            {
                $form->addElement($form->submitButton("yes_btn", "Yes", "btn-primary pull-right"));
                $form->addElement($form->button("no_btn", "No", "btn-default pull-right"));
            }

            $form_html = $form->render();
        }
        else
        {

            $data = array();
            $companies_with_warnings = 0;
            foreach( $company_ids as $company_id )
            {
                $company = $this->Company_model->get_company($company_id);
                $company_id = GetArrayStringValue('company_id', $company);
                $upload_date = GetUploadDate($company_id);
                $upload_desc = GetUploadDateDescription($company_id);

                // Select warnings that require confirmation.  From those, limit them to a small number
                // as there should not be very many.  If there are, then do not exceed the max and tell
                // them how many more there are.
                $max = 5;
                $warnings = $this->Reporting_model->select_report_review_warnings_confirmation($company_id);
                if ( count($warnings) > $max )
                {
                    $extra = count($warnings) - $max;
                    $leftovers = array_slice($warnings, $max);
                    $warnings[] = array("Issue" => "<div class='text-center'>Plus {$extra} more!</div>");
                }

                if ( count($warnings) > 0 ) $companies_with_warnings++;

                $dto = array();
                $dto["CompanyId"] = GetArrayStringValue('company_id', $company);
                $dto["CompanyName"] = GetArrayStringValue('company_name', $company);
                $dto['UploadDate'] = $upload_date;
                $dto['UploadDescription'] = $upload_desc;
                $dto["Warnings"] = $warnings;
                $dto['critical'] = true; // Only showing critical items for multiple companies.
                $data[] = $dto;
            }

            $form = new UIModalForm("finalize_reports_form", "finalize_reports_form", base_url("reports/finalized"));
            $form->setTitle("Finalize Data - Multiple Companies");
            $form->addElement($form->htmlView("reports/confirm_finalizations", array('data' => $data)));
            $form->addElement($form->hiddenInput("company_id", getStringValue($company_id)));
            $form->addElement($form->hiddenInput("company_ids", implode(':', $company_ids)));
            $form->addElement($form->hiddenInput("group_id", getStringValue($group_id)));
            if ( $companies_with_warnings === count($company_ids) )
            {
                // No companies can be finalized at this time.  Just give them the cancel button.
                $form->addElement($form->button("no_btn", "Okay", "btn-default pull-right"));
            }
            else
            {
                // At least one company can be finalized.  Show both buttons.
                $form->addElement($form->submitButton("yes_btn", "Yes", "btn-primary pull-right"));
                $form->addElement($form->button("no_btn", "No", "btn-default pull-right"));
            }

            $form_html = $form->render();
        }
		return $form_html;
	}
    private function _downloadable_reports_form($company_id, $carrier_id, $target_date)
    {
        // Does this company have a company parent?
	    $companyparent_id = GetCompanyParentId($company_id);

	    // Get the human readable description of the carrier.
	    $carrier = $this->Company_model->get_company_carrier($company_id, $carrier_id);
	    $carrier_code = GetArrayStringValue("CarrierCode", $carrier);
	    $carrier = GetArrayStringValue("UserDescription", $carrier);

	    $draft_reports = false;
	    if ( strtotime($target_date) == strtotime(GetUploadDate($company_id)) ) $draft_reports = true;



	    $title = "{REPORT_TYPE} - {CARRIER} - {DATE_DESCRIPTION}";
	    if ( $draft_reports ) $title = ReplaceFor($title, "{REPORT_TYPE}", "Draft Reports");
        if ( ! $draft_reports ) $title = ReplaceFor($title, "{REPORT_TYPE}", "Reports");
        $title = ReplaceFor($title, "{CARRIER}", $carrier);
        $title = ReplaceFor($title, "{DATE_DESCRIPTION}", FormatDateMonthYYYY($target_date));


        // Create the form.
        $form = new UIModalForm("download_report_list_form", "download_report_list_form", "");
        $form->setTitle( $title );
        $form->setAction( base_url("download/all/{$company_id}/{$carrier_id}") );

        $reports = $this->Reporting_model->get_downloadable_reports($company_id, $carrier_id, $target_date);
        $count = 0;
        foreach($reports as $report)
        {
            $report_id = GetArrayStringValue("ReportId", $report);
            $report_type = GetArrayStringValue("ReportCode", $report);
            $link = base_url("download/{$report_type}/{$company_id}/{$report_id}");
            $display = GetArrayStringValue("ReportDisplay", $report);

            // If the report we are trying to display contains PII data and the user
            // does not have pii_download, then skip it.
            if( ReportContainsPII($report_type) )
            {
                if ( ! IsAuthenticated("pii_download",'company', $company_id) ) continue;
            }

            // Do not add the Transamerica Eligibility report if it is disabled.
            if ( $report_type === 'transamerica_eligibility' )
            {
                $enabled = $this->Feature_model->is_feature_enabled($company_id, 'TRANSAMERICA_ELIGIBILITY_REPORT');
                if ( ! $enabled ) continue;
            }

            // Do not add the Transamerica Commission report if it is disabled.
            if ( $report_type === 'transamerica_commission' )
            {
                $enabled = $this->Feature_model->is_feature_enabled($company_id, 'TRANSAMERICA_COMMISSION_REPORT');
                if ( ! $enabled ) continue;
            }

            // Do not add the Transamerica Actuarial report if it is disabled.
            if ( $report_type === 'transamerica_actuarial' )
            {
                $enabled = $this->Feature_model->is_feature_enabled($company_id, 'TRANSAMERICA_ACTUARIAL_REPORT');
                if ( ! $enabled ) continue;
            }

            // DOWNLOAD BUTTON
            // Here is the standard download button for the dialog.
            $download_button = RenderViewAsString("reports/reports_download_btn", [ 'link' => $link] );

            $user_id = GetSessionValue('user_id');
            $user = $this->User_model->get_user_by_id($user_id);
            if ( GetArrayStringValue('company_id', $user) !== '' )
            {
                $sftp_enabled = $this->Feature_model->is_feature_enabled($company_id, 'FILE_TRANSFER');
            }
            else if ( GetArrayStringValue('companyparent_id', $user) !== '' )
            {
                $sftp_enabled = $this->Feature_model->is_feature_enabled_for_companyparent(GetArrayStringValue('companyparent_id', $user), 'FILE_TRANSFER');
            }


            // MULTI-OPTION BUTTON
            // Depending on the enabled features, other delivery options might open up.  Create an array of
            // possible options and create a multi-option button for each of them, with download being the
            // default behavior.
            $options = array();
            $options['Download'] = 'download';

            // Show the SFTP option in the multi-option button based on which entity the user belongs to.  If
            // the user belongs to the parent, then the feature is tied to the parent FILE_TRANSFER feature.  If
            // the user belongs to the company, then the feature is tied to the company FILE_TRANSFER feature.
            $user_id = GetSessionValue('user_id');
            $user = $this->User_model->get_user_by_id($user_id);
            if ( GetSessionValue('_company_id') === GetStringValue(A2P_COMPANY_ID) )
            {
                // If the user belongs to company and the company has the feature enabled, allow the election.
                $sftp_enabled = $this->Feature_model->is_feature_enabled($company_id, 'FILE_TRANSFER');
                if ( $sftp_enabled ) $options['SFTP Company'] = 'deliver/company';

                // If the user belongs to the companyparent and the companyparent has the feature enabled, allow the election.
                $sftp_enabled = $this->Feature_model->is_feature_enabled_for_companyparent('FILE_TRANSFER', $companyparent_id );
                if ( $sftp_enabled ) $options['SFTP Parent'] = 'deliver/parent';
            }
            else if ( GetArrayStringValue('company_id', $user) !== '' )
            {
                // If the user belongs to company and the company has the feature enabled, allow the election.
                $sftp_enabled = $this->Feature_model->is_feature_enabled(GetArrayStringValue('company_id', $user), 'FILE_TRANSFER');
                if ( $sftp_enabled ) $options['SFTP'] = 'deliver/company';
            }
            else if ( GetArrayStringValue('company_parent_id', $user) !== '' )
            {
                // If the user belongs to the companyparent and the companyparent has the feature enabled, allow the election.
                $sftp_enabled = $this->Feature_model->is_feature_enabled_for_companyparent('FILE_TRANSFER', GetArrayStringValue('company_parent_id', $user) );
                if ( $sftp_enabled ) $options['SFTP'] = 'deliver/parent';
            }

            if ( count($options) > 1 )
            {
                $download_button = new MultiOptionButton();
                $download_button->size = "small";
                $download_button->addClass('pull-right');
                $download_button->addClass('disabled');
                $download_button->success_label = 'Requested';
                $download_button->failed_label = 'Request Failed';
                $download_button->callback_onclick = "DownloadReportButtonClickHandler";
                foreach($options as $label=>$code)
                {
                    $key = strtolower($code) . "/{$report_type}/{$company_id}/{$report_id}";
                    $download_button->addItem($key, $label);
                    if ( $code === 'download' ) $download_button->selected = $key;
                }
                $download_button = $download_button->render();
            }


            if ( $report_id === "" )
            {
                if ( strpos($report_type, 'transamerica') !== FALSE && $carrier_code === 'TRANSAMERICA' )
                {
                    // We don't have a report, but this is a transamerica report and they have
                    // that carrier.  In this case we will promote the missing report.
                    $view_array = array();
                    $view_array = array_merge($view_array, array('display' => $display));
                    $form->addElement($form->htmlView( "reports/download_descriptions/{$report_type}_promote", $view_array, "" ));
                    $count++;
                }
                else
                {
                    // Nothing to se here.
                    continue;
                }
            }
            else
            {
                // We have a report_id.  Show it.
                $view_array = array();
                $view_array = array_merge($view_array, array('display' => $display));
                $view_array = array_merge($view_array, array('download_button' => $download_button));
                $form->addElement($form->htmlView( "reports/download_descriptions/{$report_type}", $view_array, "" ));
                $count++;
            }
        }

        if ($count == 0)
        {
            $form->addElement($form->htmlView( "reports/download_descriptions/no_results", array(), "" ));

        }

        $form->addElement($form->button("cancel_btn", "Close", "btn-default pull-right m-t-30"));
        $form = $form->render();

        return $form;
    }

    /**
     * _getSnapshotData
     *
     * Look for the specified snapshot, by tag, and return the raw data.
     *
     * @param $identifier
     * @param $identifier_type
     * @param $date_tag
     * @param $snapshot_tag
     * @return array|mixed
     * @throws Exception
     */
    private function _getSnapshotData($identifier, $identifier_type, $date_tag, $snapshot_tag)
    {
        // ARCHIVE location on S3.
        $prefix = GetS3Prefix('archive', $identifier, $identifier_type);
        $prefix = replaceFor($prefix, "COMPANYID", $identifier);
        $prefix = replaceFor($prefix, "COMPANYPARENTID", $identifier);
        $prefix = replaceFor($prefix, "DATE", $date_tag);
        $prefix .= "/json";

        // Create a collection of all json files.
        $snapshot_details = array();
        $files = S3ListFiles(S3_BUCKET, $prefix);
        foreach($files as $file)
        {
            $filename = getArrayStringValue("Key", $file);
            $tag = fRightBack(fLeftBack($filename, "."), "/");

            if ( $tag === $snapshot_tag )
            {
                $snapshot_details['path'] = $filename;
                $snapshot_details['filename'] = fRightBack($filename,"/");
                $snapshot_details['tag'] = $tag;
                $snapshot_details['description'] = ucwords(replaceFor($tag, "_", " "));
                break;
            }
        }
        if ( empty($snapshot_details) ) return array();


        // If the user selected a file, load that json file.
        $json = file_get_contents("s3://" . S3_BUCKET . "/" . getArrayStringValue("path", $snapshot_details) );
        $decode = json_decode($json, true);

        // If we have data, decrypt it.
        if ( ! empty($decode['data'] ) )
        {
            if ( IsAuthenticated('pii_download', $identifier_type, $identifier) )
            {
                // Find the encryption key for the identifier passed in.
                $encryption_key = GetEncryptionKey($identifier, $identifier_type);

                $decode['data'] = A2PDecryptArray($decode['data'], $encryption_key);
                $decode['data'] = ArrayRemoveKeyStartWith("Encrypted", $decode['data']);
            }
        }

        // Clean up other data items in the object.
        ( ! empty($decode['metadata'] ) ) ? $metadata = $decode['metadata'] : $metadata = array();
        ( ! empty($decode['list'] ) ) ? $list = $decode['list'] : $list = array();
        ( ! empty($decode['data'] ) ) ? $json = json_encode($decode['data'], JSON_PRETTY_PRINT) : $json = "{}";

        // Convert our metadata timestamp to a display string, also convert
        // it to the prefered timezone if we know it.
        $timestamp = getArrayStringValue("timestamp", $metadata);
        if ( ! empty($metadata['timestamp']['date']) && !empty($metadata['timestamp']['timezone']) )
        {
            $date = $metadata['timestamp']['date'];
            $zone = $metadata['timestamp']['timezone'];
            $prefered_zone = GetConfigValue("timezone_display");
            $d = new DateTime($date, new DateTimeZone($zone));
            if ( $prefered_zone != "" ) $d->setTimezone(new DateTimeZone($prefered_zone));
            $metadata['timestamp'] = $d->format("c");
        }

        return $decode;


    }

    /**
     * _getColumnMappingSettings
     *
     * Read the column mapping snapshot and convert it into an array structure that
     * can be consumed by the settings widget.
     *
     * @param $identifier
     * @param $identifier_type
     * @param $date_tag
     * @return array
     * @throws Exception
     */
    private function _getColumnMappingSettings($identifier, $identifier_type, $date_tag)
    {
        $data = array();

        // Grab our snapshot data for our column mappings.
        $snapshot = $this->_getSnapshotData($identifier, $identifier_type, $date_tag, 'column_mappings');
        $mapping_columns = $this->Mapping_model->get_mapping_columns($identifier);

        if ( isset($snapshot['data'] ) )
        {
            // COLUMN NUMBERS
            // Show the mappings for the column numbers.  We have that in the snapshot, so that is very
            // easy to do.

            // Order the snapshot data by column number.
            $ordered_data = array();
            for($i=0;$i<count($snapshot['data']);$i++)
            {
                $item = $snapshot['data'][$i];
                $item['SortOrder'] = GetArrayIntValue('Column Number', $item);
                $ordered_data[] = $item;
            }
            uasort($ordered_data, 'AssociativeArraySortFunction_SortOrder_numeric');


            $parent_title = "Column Number Mappings";
            $children = array();
            foreach($ordered_data as $item)
            {
                $column_no = GetArrayStringValue('Column Number', $item);
                $mapped_code = GetArrayStringValue('Column Mapping', $item);

                // Grab the user friendly A2P column name for display.
                $mapped_display = $mapped_code;
                $index = ArrayMultiSearchIndexOf('name', $mapped_code, $mapping_columns);
                if (  $index !== FALSE )
                {
                    $mapped_display = GetArrayStringValue('display', $mapping_columns[$index]);
                }

                if ($mapped_code !== '' )
                {
                    $row = array();
                    $row['icon'] = '';
                    $row['text'] = "Column #{$column_no}";
                    $row['value'] = $mapped_display;
                    $row['parent_title'] = $parent_title;
                    $row['is_parent'] = false;
                    $children[] = $row;
                }
            }
            if ( count($children) > 0 )
            {
                $row = array();
                $row['icon'] = '';
                $row['text'] = $parent_title;
                $row['value'] = $children;
                $row['parent_title'] = '';
                $row['is_parent'] = true;
                $data[] = $row;
            }


            // COLUMN HEADERS
            // We did not snapshot the actual file headers in the snapshot.  Thus, we have to look them
            // up by column number.  To do this, we first have to get the headers into an array.  This
            // can be done by pulling them from the archive upload file OR off the preview file if we are
            // dealing with draft reports.
            if ( $identifier_type === 'company' )
            {
                // Does this company support "headers" on their CSV file?
                $has_headers = DoesUploadContainHeaderRow( $identifier, GetCompanyParentId($identifier) );
                if ( $has_headers )
                {
                    // YES!  There should be headers.  Let's pull down the first line of the upload file.
                    $headers = "";

                    // First, try and pull it off the upload file in archive storage.  This is where it will
                    // be for finalized reports.
                    $upload_prefix = GetS3Prefix('archive', $identifier, $identifier_type) . "/upload";
                    $upload_prefix = ReplaceFor($upload_prefix, "DATE", $date_tag);
                    $files = S3ListFiles(S3_BUCKET, $upload_prefix);

                    if ( count($files) === 1 )
                    {
                        $file = $files[0];
                        $source_filename = fRightBack(GetArrayStringValue("Key", $file), "/");
                        if ( $source_filename !== '' )
                        {
                            $filename = "s3://" . S3_BUCKET . "/{$upload_prefix}/{$source_filename}";
                            try{
                                set_error_handler('error_to_exception',E_WARNING);
                                if ( file_exists($filename) )
                                {
                                    $fh = fopen($filename, "r");
                                    if (is_resource($fh))
                                    {
                                        $headers = fgets($fh);
                                        while ( IsEncryptedStringComment($headers) )
                                        {
                                            $headers = fgets($fh);
                                        }
                                        fclose($fh);
                                    }
                                }
                                restore_error_handler();
                            }catch(Exception $e)
                            {
                                if ( is_resource($fh) ) fclose($fh);
                            }
                        }
                    }

                    //  If we could not find the headers on the archive file, then we are working with
                    // draft reports and we can pull the headers off the preview file.
                    $fh = null;
                    $parsed_prefix = GetS3Prefix('parsed', $identifier, $identifier_type);
                    $filename = "s3://" . S3_BUCKET . "/{$parsed_prefix}/preview.csv";
                    try
                    {
                        set_error_handler('error_to_exception',E_WARNING);
                        if ( file_exists($filename) )
                        {
                            $fh = fopen($filename, "r");
                            if (is_resource($fh)) {
                                $headers = fgets($fh);
                                fclose($fh);
                            }
                        }
                        restore_error_handler();
                    }catch(Exception $e)
                    {
                        if ( is_resource($fh) ) fclose($fh);
                    }


                    // If we have headers, we had better decrypt them so we can read them.
                    $csv = array();
                    if ( ! empty($headers) )
                    {
                        // Find the encryption key for the identifier passed in.
                        if ( IsEncryptedString($headers) )
                        {
                            $encryption_key = GetEncryptionKey($identifier, $identifier_type);

                            $line = trim($headers);
                            $line = A2PDecryptString($line, $encryption_key);
                            $csv = str_getcsv($line);
                        }
                    }

                    // We have the CSV headers in an array now.  Go ahead and create the object we need
                    // for the setting widget.
                    if ( $csv )
                    {
                        $parent_title = "Column Header Mappings";
                        $children = array();
                        foreach($snapshot['data'] as $item)
                        {
                            $column_no = GetArrayStringValue('Column Number', $item);
                            $mapped_code = GetArrayStringValue('Column Mapping', $item);

                            // Grab the user friendly A2P column name for display.
                            $mapped_display = $mapped_code;
                            $index = ArrayMultiSearchIndexOf('name', $mapped_code, $mapping_columns);
                            if (  $index !== FALSE )
                            {
                                $mapped_display = GetArrayStringValue('display', $mapping_columns[$index]);
                            }

                            if ($mapped_code !== '' )
                            {
                                $row = array();
                                $row['icon'] = '';
                                $row['text'] = $csv[$column_no];
                                $row['value'] = $mapped_display;
                                $row['parent_title'] = $parent_title;
                                $row['is_parent'] = false;
                                $children[] = $row;
                            }
                        }
                        if ( count($children) > 0 )
                        {
                            $row = array();
                            $row['icon'] = '';
                            $row['text'] = $parent_title;
                            $row['value'] = $children;
                            $row['parent_title'] = '';
                            $row['is_parent'] = true;
                            $data[] = $row;
                        }
                    }

                }
            }
        }
        return $data;
    }

    /**
     * _getPlanSettings
     *
     * Read the plan settings snapshot and convert it into an array structure that
     * can be consumed by the settings widget.
     *
     * @param $identifier
     * @param $identifier_type
     * @param $date_tag
     * @return array
     * @throws Exception
     */
    private function _getPlanSettings($identifier, $identifier_type, $date_tag)
    {
        $data = array();

        // Grab the plan settings snapshot.
        $snapshot = $this->_getSnapshotData($identifier, $identifier_type, $date_tag, 'plan_settings');

        // Grab "dropdowns" for plan settings.  We mostly store the selected codes, not the user friendly display.
        // Get this in order now so we can change the selected code to the human readable version.
        $plantype_mappings = $this->Mapping_model->get_plan_types_for_user_dopdown();
        $retro_rules = $this->Wizard_model->get_retrorules();
        $wash_rules = $this->Wizard_model->get_washrules();
        $plan_anniversary_months = ['00' => "No Anniversary", '01' => "January", '02' => "February", '03' => "March", '04' => "April", '05' => "May", '06' => "June", '07' => "July", '08' => "August", '09' => "September", '10' => "October", '11' => "November", '12' => "December"];

        // The data section in this snapshot is just a bunch of key value pairs.  We did not have "list"
        // at the time it was created.  Here we will pull out each of the key value pairs and tag it with
        // carrier, plantype, plan, coveragetier or unknown.  We will then display the key/value settings in
        // that order.
        $data = array();
        if ( isset($snapshot['data'] ) )
        {
            foreach ($snapshot['data'] as $item)
            {
                // Grab the coverage key elements so we can make a 'title' for each unique section.
                $carrier = GetArrayStringValue('Carrier', $item);
                $plan_type = GetArrayStringValue('PlanType', $item);
                $plan = GetArrayStringValue('Plan', $item);
                $coverage_tier = GetArrayStringValue('CoverageTier', $item);

                // Ignore these recorded settings.
                $ignored = ['CarrierId', 'PlanTypeId', 'PlanId', 'CoverageTierId', 'Age Type Id', 'CarrierCode', 'Tobacco Column Mapped' ];

                // If agebands or tobacco are not mapped, we will suppress the
                // settings for those items.
                $ageband_capable = true;
                $tobacco_capable = true;


                $parent_title = "{$carrier} / {$plan_type} / {$plan} / {$coverage_tier}";

                $carrier = array();
                $plantype = array();
                $plan = array();
                $coveragetier = array();
                $unknown = array();
                foreach ($item as $key => $value)
                {
                    // Some items we don't want to display.
                    if ( in_array($key, $ignored) ) continue;

                    // Add a 'type' to each item.  Blank for unknown.
                    $item['type'] = '';

                    if ($key === 'Carrier' ) $item['type'] = 'carrier';
                    if ($key === 'PlanType' ) $item['type'] = 'plantype';
                    if ($key === 'Plan' ) $item['type'] = 'plan';
                    if ($key === 'CoverageTier' ) $item['type'] = 'coveragetier';
                    if ($key === 'Age Bands' ) $item['type'] = 'coveragetier';
                    if ($key === 'PlanTypeIgnored')
                    {
                        $key = "Was this PlanType ignored?";
                        if (GetStringValue($value) === 'FALSE') $value = "Yes";
                        else $value = "No";
                        $item['type'] = 'plantype';
                    }
                    if ($key === 'PlanTypeCode')
                    {
                        $key = "PlanType Mapping";
                        $index = ArrayMultiSearchIndexOf('name', $value, $plantype_mappings);
                        if ($index !== FALSE) $value = GetArrayStringValue('display', $plantype_mappings[$index]);
                        $item['type'] = 'plantype';
                    }
                    if ($key === 'RetroRule')
                    {
                        $index = ArrayMultiSearchIndexOf('Name', $value, $retro_rules);
                        if ($index !== FALSE) $value = GetArrayStringValue('Display', $retro_rules[$index]);
                        $item['type'] = 'plantype';
                    }
                    if ($key === 'WashRule')
                    {
                        $index = ArrayMultiSearchIndexOf('Name', $value, $wash_rules);
                        if ($index !== FALSE) $value = GetArrayStringValue('Display', $wash_rules[$index]);
                        $item['type'] = 'plantype';
                    }
                    if ($key === 'PlanAnniversaryMonth')
                    {
                        $plan_anniversary_month = $value;
                        $plan_anniversary_month = ($plan_anniversary_month == "" ? "00" : substr(str_pad($plan_anniversary_month, 2, "0", STR_PAD_LEFT), -2));
                        if ( isset($plan_anniversary_months[$plan_anniversary_month]) ) $value = $plan_anniversary_months[$plan_anniversary_month];
                        $item['type'] = 'plantype';
                    }
                    if ($key === 'Is Ageband Capable')
                    {
                        $key = "Does the coverage tier support age bands?";
                        if ($value === 'f') $value = "No";
                        else $value = "Yes";
                        $item['type'] = 'coveragetier';
                        if ( $value === "No" ) $ageband_capable = false;
                    }
                    if ($key === 'Ageband Is Ignored')
                    {
                        if ( ! $ageband_capable ) continue;
                        $key = "Are age bands being ignored on this coverage tier?";
                        if ($value === 'f') $value = "No";
                        else $value = "Yes";
                        $item['type'] = 'coveragetier';
                    }
                    if ($key === 'Age Calculation')
                    {
                        if ( ! $ageband_capable ) continue;
                        $key = 'Age Calculation Type';
                        $item['type'] = 'coveragetier';
                    }
                    if ($key === 'Anniversary Month')
                    {
                        if ( ! $ageband_capable ) continue;
                        if ( $value === '' ) continue;
                        $key = "Age Calculation Anniversary Month";
                        $t = strtotime("{$value}/1/2020");
                        $value = date('F',$t);
                        $item['type'] = 'coveragetier';
                    }
                    if ($key === 'Anniversary Day')
                    {
                        if ( ! $ageband_capable ) continue;
                        if ( $value === '' ) continue;
                        $key = "Age Calculation Anniversary Month";
                        $t = strtotime("1/{$value}/2020");
                        $value = date('dS',$t);
                        $value = ltrim($value, '0');
                        $item['type'] = 'coveragetier';
                    }
                    if ($key === 'Is Tobacco Capable') {
                        $key = "Does the coverage tier support tobacco?";
                        if ($value === 'f') $value = "No";
                        else $value = "Yes";
                        $item['type'] = 'coveragetier';
                        if ( $value === "No" ) $tobacco_capable = false;
                    }
                    if ($key === 'Tobacco Is Ignored') {
                        if ( ! $tobacco_capable ) continue;
                        $key = "Is tobacco calculation being ignored on this coverage tier?";
                        if ($value === 'f') $value = "No";
                        else $value = "Yes";
                        $item['type'] = 'coveragetier';
                    }

                    // Create the settings row for this item.
                    $row = array();
                    $row['icon'] = '';
                    $row['text'] = $key;
                    $row['value'] = $value;
                    $row['parent_title'] = $parent_title;
                    $row['is_parent'] = false;

                    // Skip items that are empty for these settings.
                    if ( $value === '' ) continue;

                    // Store the item in the array that matches it's type.
                    if ( GetArrayStringValue('type', $item) === 'carrier' ) $carrier[] = $row;
                    if ( GetArrayStringValue('type', $item) === 'plantype' ) $plantype[] = $row;
                    if ( GetArrayStringValue('type', $item) === 'plan' ) $plan[] = $row;
                    if ( GetArrayStringValue('type', $item) === 'coveragetier' ) $coveragetier[] = $row;
                    if ( GetArrayStringValue('type', $item) === '' ) $unknown[] = $row;
                }

                // Collect all of the children together in this order.
                $children = array_merge($carrier, $plantype, $plan, $coveragetier, $unknown);

                // Add the children to the data collection by the parent title.
                $row = array();
                $row['icon'] = '';
                $row['text'] = $parent_title;
                $row['value'] = $children;
                $row['parent_title'] = '';
                $row['is_parent'] = true;
                $data[] = $row;
            }
        }
        return $data;
    }

    /**
     * _getRelationshipSettings
     *
     * Read the relationship snapshot and convert it into an array structure that
     * can be consumed by the settings widget.
     *
     * @param $identifier
     * @param $identifier_type
     * @param $date_tag
     * @return array
     * @throws Exception
     */
    private function _getRelationshipSettings($identifier, $identifier_type, $date_tag)
    {
        $data = array();

        // Grab the snapshot.
        $snapshot = $this->_getSnapshotData($identifier, $identifier_type, $date_tag, 'relationship_settings');

        // If there is a LIST, place those key values at the top.
        if ( isset($snapshot['list'] ) )
        {
            $list = $snapshot['list'];
            $mapped = GetArrayStringValue('Column Mapped', $list);
            $pricing_model = GetArrayStringValue('Pricing Model', $list);

            // If the column is not mapped, don't show anything.
            if ( $mapped !== 'TRUE' ) return array();

            // Map the Pricing Model into the display value.  Available models do not
            // appear to be in the database.  Just duplicate them here and if we miss
            // one, show the code.
            $known_pricing_models = array();
            $known_pricing_models['individual'] = 'Individual Pricing';
            $known_pricing_models['grouped_family'] = 'Grouped Family Pricing';
            $known_pricing_models['grouped'] = 'Grouped Dependent Pricing';
            if ( isset($known_pricing_models[$pricing_model] ) )
            {
                $pricing_model = $known_pricing_models[$pricing_model];
            }

            $row = array();
            $row['icon'] = '';
            $row['text'] = 'Selected pricing model.';
            $row['value'] = $pricing_model;
            $row['is_parent'] = false;
            $data[] = $row;
        }

        // This data section holds the mapping relationships between the
        // user supplied relationships tagged by the relationship column and
        // how they mapped them to A2P relationships.
        if ( isset($snapshot['data'] ) )
        {
            $parent_title = "Relationship Mappings";
            $children = array();
            foreach($snapshot['data'] as $item)
            {
                $user_description = GetArrayStringValue('UserDescription', $item);
                $relationship = GetArrayStringValue('Relationship', $item);

                $row = array();
                $row['icon'] = '';
                $row['text'] = $user_description;
                $row['value'] = $relationship;
                $row['parent_title'] = $parent_title;
                $row['is_parent'] = false;
                $children[] = $row;
            }
            if ( count($children) > 0 )
            {
                $row = array();
                $row['icon'] = '';
                $row['text'] = $parent_title;
                $row['value'] = $children;
                $row['parent_title'] = '';
                $row['is_parent'] = true;
                $data[] = $row;
            }
        }
        return $data;
    }

    /**
     * _getCarrierMenuData
     *
     * Create a dropdown header menu collection.  There will be two items in the
     * returning array.
     *  - selected_index ( int )
     *  - menu ( array )
     *
     * The selected index points to the item in the menu that is considered selected.
     * That will be either the carrier passed in or it will be the first item in the
     * menu.  If we can find no reports for the specified carrier, the empty array
     * will be returned.
     *
     * The menu array will contain everything you need to construct the breadcrumb
     * menu.
     *  - value
     *  - display
     *  - link
     *  - selected ( boolean )
     *
     * @param $company_id
     * @param string $carrier_id
     * @return array
     */
    private function _getCarrierMenuData($company_id, $carrier_id='')
    {
        $selected_index = FALSE;
        $menu           = array();

        // Collect report data so we know what settings we can show.
        $draft = $this->Reporting_model->select_draft_reports($company_id);
        $finalized = $this->Reporting_model->select_report_history($company_id);
        $reports = array_merge($draft, $finalized);

        // Remove any reports that are not tied to a carrier, such as the Process Report.
        $filtered = array();
        foreach($reports as $report)
        {
            if ( GetArrayStringValue('carrier_id', $report) !== '' ) $filtered[] = $report;
        }
        $reports = $filtered;

        // No reports?  Stop now.
        if ( empty($reports) ) return ['selected_index'=>$selected_index, 'menu'=>$menu];

        // If the selected carrier does not exist, then auto select the first carrier in the list
        $selected_index = ArrayMultiSearchIndexOf('carrier_id', $carrier_id, $reports);
        if ( $selected_index === FALSE ) $selected_index = 0;

        $found_carriers = [];
        for($i=0;$i<count($reports);$i++)
        {
            $report = $reports[$i];
            $report_carrier_id = GetArrayStringValue('carrier_id', $report);

            if ( in_array($report_carrier_id, $found_carriers) ) continue;
            if ( in_array($report_carrier_id, $found_carriers) ) continue;

            $menu_item = array();
            $menu_item['value'] = $report_carrier_id;
            $menu_item['display'] = GetArrayStringValue('carrier', $report);
            $menu_item['link'] = base_url('reports/settings/' . $menu_item['value']);
            $selected_index === $i ? $menu_item['selected'] = true : $menu_item['selected'] = false;
            $menu[] = $menu_item;

            // keep track of the carriers you have added to the list.
            $found_carriers[] = $report_carrier_id;

        }

        return ['selected_index'=>$selected_index, 'menu'=>$menu];

    }

    /**
     * _getReportsMenuData
     *
     * Create a dropdown header menu collection.  There will be two items in the
     * returning array.
     *  - selected_index ( int )
     *  - menu ( array )
     *
     * The selected index points to the item in the menu that is considered selected.
     * That will be either the carrier report passed in or it will be the first item in the
     * menu.  If we can find no reports, the empty array will be returned.
     *
     * The menu array will contain everything you need to construct the breadcrumb
     * menu.
     *  - value
     *  - display
     *  - link
     *  - selected ( boolean )
     *
     * @param $company_id
     * @param string $carrier_id
     * @return array
     */
    private function _getReportsMenuData($company_id, $carrier_id, $yearmo='')
    {
        $selected_index = FALSE;
        $menu           = array();

        // Collect report data so we know what settings we can show.
        $draft = $this->Reporting_model->select_draft_reports($company_id);
        $finalized = $this->Reporting_model->select_report_history($company_id);
        $reports = array_merge($draft, $finalized);

        // Filter out all reports that do not belong to the carrier.
        $filtered = array();
        foreach($reports as $report)
        {
            if ( GetArrayStringValue('carrier_id', $report) === GetStringValue($carrier_id) )
            {
                $filtered[] = $report;
            }
        }
        $reports = $filtered;

        // No reports?  Stop now.
        if ( empty($reports) ) return ['selected_index'=>$selected_index, 'menu'=>$menu];

        // Convert the yearmo to an import date.
        $import_date = '';
        if ( ! empty($yearmo) ) $import_date = substr($yearmo, 0, 4) . "-" . substr($yearmo, -2) . '-01';

        // If the selected report does not exist, then auto select the first report in the list
        $selected_index = ArrayMultiSearchIndexOf('import_date', $import_date, $reports);
        if ( $selected_index === FALSE ) $selected_index = 0;

        for($i=0;$i<count($reports);$i++)
        {
            $report = $reports[$i];
            $report_yearmo = FormatDateYYYYMM(GetArrayStringValue('import_date', $report));
            $menu_item = array();
            $menu_item['value'] = $report_yearmo;
            $menu_item['display'] = GetArrayStringValue('display_date', $report);
            $menu_item['link'] = base_url('reports/settings/' . $menu_item['value']);
            $report_yearmo === GetStringValue($yearmo) ? $menu_item['selected'] = true : $menu_item['selected'] = false;
            $menu[] = $menu_item;
        }

        return ['selected_index'=>$selected_index, 'menu'=>$menu];

    }
}
