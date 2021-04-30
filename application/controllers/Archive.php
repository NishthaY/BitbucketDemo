<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Archive extends SecureController {

    protected $route;

	function __construct(){
		parent::__construct();
    }

    // SCREENS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-


    /**
     * export
     *
     * This is the "page" that allows you to create and manage existing imports
     * by the selected company or parent company.
     *
     * @param $identifier
     * @param $identifier_type
     */
    public function export($identifier, $identifier_type)
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");

            // Get the display name for the identifier passed in.
            $identifier_name = "";
            $url_identifier = "";
            if ( $identifier_type === 'company' )
            {
                $company = $this->Company_model->get_company($identifier);
                $identifier_name = getArrayStringValue("company_name", $company);
                $url_identifier = 'company';
            }
            else if ( $identifier_type === 'companyparent' )
            {
                $companyparent = $this->CompanyParent_model->get_companyparent($identifier);
                $identifier_name = getArrayStringValue("Name", $companyparent);
                $url_identifier = 'parent';
            }

            // Get the HTML for the export list and created form.
            $this->load->library('A2PExport');
            $obj = new A2PExport();
            $manage = $obj->RenderManageWidget($identifier, $identifier_type);
            $create = $obj->RenderCreateWidget($identifier, $identifier_type);
            $obj = null;

            // Create the widget that manages the list of exports.
            $widget1 = new UIWidget('available_exports');
            $widget1->setBody( $manage );
            $widget1->setHref( base_url("support/exports/{$url_identifier}/manage/{$identifier}"));
            $widget1->setTaskName('export_dashboard_task');
            $widget1 = $widget1->render();

            // Create the widget that creates a new export
            $widget2 = new UIWidget('create_exports');
            $widget2->setBody( $create );
            $widget2->setHref( base_url("support/exports/{$url_identifier}/create/{$identifier}"));
            $widget2 = $widget2->render();

            // Create the widget that will remove Confirmation
            $confirm_widget = new UIWidget("remove_export_widget");
            $confirm_widget->setHref(base_url("support/exports/confirm/delete/EXPORT_ID"));
            $confirm_widget = $confirm_widget->render();

            //TASK: export_dashboard_task
            // Add a background task so we can listen for export_dashboard_task events.
            $task_config = $this->Widgettask_model->task_config('export_dashboard_task');
            $background_task = new UIBackgroundTask('export_dashboard_task');
            $background_task->setHref(base_url("widgettask/export_dashboard_task"));
            $background_task->setRefreshMinutes(getArrayIntValue("refresh_minutes", $task_config));
            $background_task->setDebug(getArrayStringValue("debug", $task_config));
            $background_task->setInfo(getArrayStringValue("info", $task_config));
            $background_task = $background_task->render();

            // Display the screen.
            $view = "archive/export";
            $view_array = array();
            $view_array['identifier'] = $identifier;                // identifier
            $view_array['identifier_type'] = $identifier_type;      // identifier type
            $view_array['identifier_name'] = $identifier_name;      // Company or parent company name.
            $view_array['url_identifier'] = $url_identifier;        // url identifier
            $view_array['uri'] = 'support/manage';

            $view_array['title'] = "Export";
            $view_array['manage_widget'] = $widget1;
            $view_array['create_widget'] = $widget2;
            $view_array['confirm_widget'] = $confirm_widget;
            $view_array['background_task'] = $background_task;

            $custom_js = "archive/export_js_assets";

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString($custom_js)));
            $page_template = array_merge($page_template, array("view" => $view));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

    /**
     * report
     *
     * New generic support page that will display a report.  See list of supported
     * reports below.  This page shows a collection of reports by import date with
     * the ability to display a "details" section and a "summary" section.  The
     * unique business logic for the reports are stored in their detail and summary
     * functions allowing this to template to be reusable.
     *
     * @param $report_type
     * @param $identifier
     * @param $identifier_type
     * @param string $date_tag
     */
    public function report( $report_type, $identifier, $identifier_type, $date_tag='' )
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");

            // Get the display name for the identifier passed in.
            $identifier_name = "";
            $url_identifier = "";
            if ( $identifier_type === 'company' )
            {
                $company = $this->Company_model->get_company($identifier);
                $identifier_name = getArrayStringValue("company_name", $company);
                $url_identifier = 'company';
            }
            else if ( $identifier_type === 'companyparent' )
            {
                $companyparent = $this->CompanyParent_model->get_companyparent($identifier);
                $identifier_name = getArrayStringValue("Name", $companyparent);
                $url_identifier = 'parent';
            }

            $valid = [ 'invoice' ];
            if ( ! in_array($report_type, $valid) ) throw new UIException("Unsupported report type");

            // Based on the report code, collect the data needed to be shown in the
            // report template page.

            /*
             * Each supported report type will need to set the following values.
             *
             * - detail_widget: HTML shown in the main section of the page.
             * - summary_widget: HTML shown in the right most column of the page.
             * - uri: uri segment used to build the links in breadcrumbs.
             *        {$uri}/{$url_identifier}/{$identifier}/{$report_date_tag}
             * - custom_js: custom js view used for the report to load js libraries.
             *
             */

            $summary_widget = "";
            $detail_widget = "";
            $report_title = "";
            $custom_js = "";
            $reports = array();
            if ( $report_type === 'invoice' )
            {
                $this->load->library("A2PReportInvoice");
                $this->load->model('A2PReportInvoice_model');

                $reports = $this->A2PReportInvoice_model->select_invoice_report_list($identifier, $identifier_type);
                $date_tag = $this->_getDefaultDateTag($date_tag, $reports);
                $report_title = "Invoice Report";

                $obj = new A2PReportInvoice($identifier, $identifier_type, $date_tag);
                $detail_widget = $obj->RenderDetailsWidget($identifier, $identifier_type, $date_tag);

                $obj = new A2PReportInvoice($identifier, $identifier_type, $date_tag);
                $summary_widget = $obj->RenderSummaryWidget($identifier, $identifier_type, $date_tag);

                $uri = "support/invoice";
                $custom_js = "archive/invoice_report_js_assets";
            }

            // Auto-select the date tag, if not provided.
            if ( getStringValue($date_tag) == "" )
            {
                if ( !empty($reports) )
                {
                    $first = $reports[0];
                    $date_tag = getArrayStringValue("date_tag", $first);
                }
            }


            $view = "archive/report";
            $view_array = array();
            $view_array['identifier'] = $identifier;                // identifier
            $view_array['identifier_type'] = $identifier_type;      // identifier type
            $view_array['identifier_name'] = $identifier_name;      // Company or parent company name.
            $view_array['url_identifier'] = $url_identifier;        // url identifier
            $view_array['date_tag'] = $date_tag;                    // import date we are reporting on.
            $view_array['uri'] = $uri;                              // uri for links specific to report.
            $view_array['report_title'] = $report_title;            // report title.
            $view_array['summary_widget'] = $summary_widget;        // html in right column.
            $view_array['detail_widget'] = $detail_widget;          // html in main body of page.
            $view_array['reports'] = $reports;                      // List of available reports.

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString($custom_js)));
            $page_template = array_merge($page_template, array("view" => $view));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function timers( $company_id, $date_tag="" )
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");

            // What reports are available for this company?
            $reports = $this->Support_model->select_support_timer_report_list($company_id);
            if ( getStringValue($date_tag) == "" )
            {
                if ( !empty($reports) )
                {
                    $first = $reports[0];
                    $date_tag = getArrayStringValue("date_tag", $first);
                }
            }

            $import_date = GetUploadDate($company_id);
            if ( GetStringValue($import_date) === '' ) {
                $import_date = GetRecentDate($company_id);
            }
            if ($date_tag !== '')
            {
                $year = substr($date_tag, 0, 4);
                $mon = substr($date_tag, 4, 2);
                $import_date = $mon . "/01/" . $year;
            }

            $company = $this->Company_model->get_company($company_id);
            $company_name = GetArrayStringValue('company_name', $company);

            $timers_widget = new UIWidget("timers_widget");
            $timers_widget->setBody( $this->_timers_widget($company_id, $import_date) );
            $timers_widget = $timers_widget->render();

            $view_array = array();
            $view_array['estimated_runtime'] = $this->Support_model->select_estimated_runtime($company_id, $import_date);
            $view_array['show_button'] = false;
            $summary_widget = RenderViewAsString("archive/support_timers_widget", $view_array);

            $view = "archive/support_timers";
            $view_array = array();
            $view_array['company_id'] = $company_id;
            $view_array['company_name'] = $company_name;
            $view_array['timers_widget'] = $timers_widget;
            $view_array['summary_widget'] = $summary_widget;
            $view_array['date_tag'] = $date_tag;
            $view_array['reports'] = $reports;

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("archive/support_timers_js_assets")));
            $page_template = array_merge($page_template, array("view" => $view));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

    /**
     * lives
     *
     * From the support page, you can view / find lives.  Once located you can
     * move to other pages that support data filtered by life.  This is the
     * page that will allow you to list lives to try and locate the one you
     * are looking for.
     *
     * @param $company_id
     */
    public function lives( $company_id )
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");


            $lives_widget = new UIWidget("lives_widget");
            $lives_widget->setBody( $this->_lives_widget($company_id) );
            $lives_widget = $lives_widget->render();


            $view = "archive/lives";
            $view_array = array();
            $view_array['company_id'] = $company_id;
            $view_array['lives_widget'] = $lives_widget;



            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("commissions/js_assets")));
            $page_template = array_merge($page_template, array("view" => $view));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }


    public function support_company( $company_id=null ) {
        try {
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "GET") throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if (!IsLoggedIn()) throw new SecurityException("You must be logged into access this function.");
            if (!IsAuthenticated("support_read")) throw new SecurityException("Missing required permission.");


            // REDIRECT
            // Go to the support page you were last interacting with.
            if( GetStringValue($company_id) === '' )
            {
                if ( GetSessionValue('support_company_id') !== '' )
                {
                    SetSessionValue('support_companyparent_id', '');
                    redirect( base_url("support/manage/company/" . GetSessionValue('support_company_id')) );
                    exit;
                }
                if ( GetSessionValue('support_companyparent_id') !== '' )
                {
                    SetSessionValue('support_company_id', '');
                    redirect( base_url("support/manage/parent/" . GetSessionValue('support_companyparent_id')) );
                    exit;
                }
            }
            else
            {
                SetSessionValue('support_company_id', GetStringValue($company_id));
                SetSessionValue('support_companyparent_id', '');
            }


            if ( GetStringValue($company_id) === "" ) $company_id = GetSessionValue("company_id");
            SetSessionValue('support_company_id', $company_id);
            $company = $this->Company_model->get_company($company_id);
            $company_name = getArrayStringValue("company_name", $company);


            $recent_changes = $this->Archive_model->select_recent_company_changes($company_id);
            $recent_snapshots = $this->Archive_model->select_recent_company_snapshots($company_id);
            $recent_tickets = $this->Archive_model->select_recent_tickets($company_id, 'company');
            $recent_exports = $this->Archive_model->select_recent_exports($company_id, 'company');
            $in_process = $this->Archive_model->select_in_process_items();
            $life_count = $this->Archive_model->select_life_summary($company_id);
            $commission_count = $this->Archive_model->count_commission_validation_errors($company_id);
            $estimated_runtime = $this->Support_model->select_estimated_runtime($company_id);

            $this->load->library('A2PReportInvoice');
            $obj = new A2PReportInvoice();
            $invoice_report_summary_widget = $obj->RenderSummaryWidget($company_id, 'company');

            $view_array = array();
            $view_array = array_merge($view_array, array( "company_id" => $company_id));
            $view_array = array_merge($view_array, array( "company_name" => $company_name));
            $view_array = array_merge($view_array, array( "recent_changes" => $recent_changes));
            $view_array = array_merge($view_array, array( "recent_snapshots" => $recent_snapshots));
            $view_array = array_merge($view_array, array( "recent_tickets" => $recent_tickets));
            $view_array = array_merge($view_array, array( "recent_exports" => $recent_exports));
            $view_array = array_merge($view_array, array( "life_count" => $life_count));
            $view_array = array_merge($view_array, array( "commission_count" => $commission_count));
            $view_array = array_merge($view_array, array( "in_process" => $in_process));
            $view_array = array_merge($view_array, array( "estimated_runtime" => $estimated_runtime));
            $view_array = array_merge($view_array, array( "invoice_report_summary_widget" => $invoice_report_summary_widget));

			$page_template = array();
			$page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("archive/js_assets")));
			$page_template = array_merge($page_template, array("view" => "archive/support"));
			$page_template = array_merge($page_template, array("view_array" => $view_array));
			RenderView('templates/template_body_default', $page_template);

		}
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
    }
    public function support_parent( $company_parent_id=null ) {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");

            // REDIRECT
            // Go to the support page you were last interacting with.
            if( GetStringValue($company_parent_id) === '' )
            {
                if ( GetSessionValue('support_companyparent_id') !== '' )
                {
                    SetSessionValue('support_company_id', '');
                    redirect( base_url("support/manage/parent/" . GetSessionValue('support_companyparent_id')) );
                    exit;
                }
                if ( GetSessionValue('support_company_id') !== '' )
                {
                    SetSessionValue('support_companyparent_id', '');
                    redirect( base_url("support/manage/company/" . GetSessionValue('support_company_id')) );
                    exit;
                }
            }
            else
            {
                SetSessionValue('support_company_id', '');
                SetSessionValue('support_companyparent_id', GetStringValue($company_parent_id));
            }


            if ( getStringValue($company_parent_id) == "" ) $company_parent_id = GetSessionValue("companyparent_id");
            SetSessionValue('support_companyparent_id', $company_parent_id);
            $parent = $this->CompanyParent_model->get_companyparent($company_parent_id);
            $parent_name = getArrayStringValue("Name", $parent);

            $recent_changes = $this->Archive_model->select_recent_parent_changes($company_parent_id);
            $recent_tickets = $this->Archive_model->select_recent_tickets($company_parent_id, 'companyparent');
            $recent_snapshots = $this->Archive_model->select_recent_snapshots($company_parent_id, 'companyparent');
            $recent_exports = $this->Archive_model->select_recent_exports($company_parent_id, 'companyparent');
            $in_process = $this->Workflow_model->select_in_progress_workflow_items();


            $this->load->library('A2PReportInvoice');
            $obj = new A2PReportInvoice();
            $invoice_report_summary_widget = $obj->RenderSummaryWidget($company_parent_id, 'companyparent');

            $view_array = array();
            $view_array = array_merge($view_array, array( "company_parent_id" => $company_parent_id));
            $view_array = array_merge($view_array, array( "parent_name" => $parent_name));
            $view_array = array_merge($view_array, array( "recent_changes" => $recent_changes));
            $view_array = array_merge($view_array, array( "recent_tickets" => $recent_tickets));
            $view_array = array_merge($view_array, array( "recent_snapshots" => $recent_snapshots));
            $view_array = array_merge($view_array, array( "recent_exports" => $recent_exports));
            $view_array = array_merge($view_array, array( "in_process" => $in_process));
            $view_array = array_merge($view_array, array( "invoice_report_summary_widget" => $invoice_report_summary_widget));

            $view = "archive/support_readonly";
            if (IsAuthenticated("support_write")) $view = "archive/support";

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("archive/js_assets")));
            $page_template = array_merge($page_template, array("view" => $view));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

    /**
     * snapshot_company ( GET )
     *
     * Display a screen that shows you the selected snapshot and allows
     * you to flip between them.  The top right corner includes a download
     * button for the original upload file.
     *
     * @param $identifier
     * @param $identifier_type
     * @param null $date_tag
     * @param null $snapshot_tag
     */
    public function snapshot_viewer( $identifier, $identifier_type, $date_tag=null, $snapshot_tag=null )
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");
            if ( getStringValue($identifier) == "" ) throw new SecurityException("Missing required input identifier.");
            if ( getStringValue($identifier_type) == "" ) throw new SecurityException("Missing required input identifier_type.");


            // Get the display name for the identifier passed in.
            $identifier_name = "";
            if ( $identifier_type === 'company' )
            {
                $company = $this->Company_model->get_company($identifier);
                $identifier_name = getArrayStringValue("company_name", $company);
            }
            else if ( $identifier_type === 'companyparent' )
            {
                $companyparent = $this->CompanyParent_model->get_companyparent($identifier);
                $identifier_name = getArrayStringValue("Name", $companyparent);
            }

            // Find the encryption key for the identifier passed in.
            $encryption_key = GetEncryptionKey($identifier, $identifier_type);

            // Collect a list of snapshots the user will be able to select from.
            $reports = $this->Archive_model->select_snapshots($identifier, $identifier_type);
            if ( getStringValue($date_tag) == "" )
            {
                if ( !empty($reports) )
                {
                    $first = $reports[0];
                    $date_tag = getArrayStringValue("date_tag", $first);
                }
            }

            // ARCHIVE location on S3.
            $prefix = GetS3Prefix('archive', $identifier, $identifier_type);
            $prefix = replaceFor($prefix, "COMPANYID", $identifier);
            $prefix = replaceFor($prefix, "COMPANYPARENTID", $identifier);
            $prefix = replaceFor($prefix, "DATE", $date_tag);
            $prefix .= "/json";

            // Create a collection of all json files.
            $snapshots = array();
            $files = S3ListFiles(S3_BUCKET, $prefix);
            foreach($files as $file)
            {
                $filename = getArrayStringValue("Key", $file);
                $tag = fRightBack(fLeftBack($filename, "."), "/");

                $details = array();
                $details['path'] = $filename;
                $details['filename'] = fRightBack($filename,"/");
                $details['tag'] = $tag;
                $details['description'] = ucwords(replaceFor($tag, "_", " "));

                $snapshots[$tag] = $details;
            }

            // If the user did not elect a snapshot, and there are some to
            // choose from, pick the first one.
            if ( $snapshot_tag == "" && count($snapshots) != 0 ) {
                $keys = array_keys($snapshots);
                $snapshot_tag = $keys[0];
            }

            // If the user selected a file, load that json file.
            $json = "{}";
            if ( getStringValue($snapshot_tag) != "" )
            {
                if ( isset($snapshots[$snapshot_tag] ) )
                {
                    $json = file_get_contents("s3://" . S3_BUCKET . "/" . getArrayStringValue("path", $snapshots[$snapshot_tag]) );
                }
            }
            $decode = json_decode($json, true);

            // If we have data, decrypt it.
            if ( ! empty($decode['data'] ) )
            {
                if ( IsAuthenticated('pii_download', $identifier_type, $identifier) )
                {
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

            // Prefix for Source Uplaod
            $archive_prefix = GetS3Prefix('archive', $identifier, $identifier_type);
            $archive_prefix  = replaceFor($archive_prefix, "COMPANYID", $identifier);
            $archive_prefix  = replaceFor($archive_prefix, "COMPANYPARENTID", $identifier);
            $archive_prefix  = replaceFor($archive_prefix, "DATE", $date_tag);
            $archive_prefix .= "/upload";

            $source_upload = "";
            $files = S3ListFiles(S3_BUCKET, $archive_prefix);
            if ( count($files) == 1)
            {
                foreach($files as $file)
                {
                    $path = getArrayStringValue("Key", $file);
                    $source_upload = fRightBack($path, "/");
                }
            }

            $view_array = array();
            $view_array = array_merge($view_array, array( "json" => $json));
            $view_array = array_merge($view_array, array( "metadata" => $metadata));
            $view_array = array_merge($view_array, array( "list" => $list));
            $view_array = array_merge($view_array, array( "identifier_name" => $identifier_name));
            $view_array = array_merge($view_array, array( "identifier" => $identifier));
            $view_array = array_merge($view_array, array( "identifier_type" => $identifier_type));
            $view_array = array_merge($view_array, array( "snapshot_tag" => $snapshot_tag));
            $view_array = array_merge($view_array, array( "snapshots" => $snapshots));
            $view_array = array_merge($view_array, array( "source_upload" => $source_upload));
            $view_array = array_merge($view_array, array( "date_tag" => $date_tag));
            $view_array = array_merge($view_array, array( "reports" => $reports));

            $view = "archive/snapshots";
            if ( count($snapshots) == 0 ) $view = "archive/snapshots_noresults";

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("archive/js_assets")));
            $page_template = array_merge($page_template, array("view" => $view));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function audit_company( $company_id, $selected_view ) {
        try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");
            if ( getStringValue($company_id) == "" ) throw new SecurityException("Missing required input company_id.");
            if ( getStringValue($selected_view) == "" ) throw new SecurityException("Missing required input selected_view.");




            $company = $this->Company_model->get_company($company_id);
            $company_name = getArrayStringValue("company_name", $company);

            $view = array();
            $views['recent'] = array("tag" => "recent", "description" => "Recent");
            $views['week'] = array("tag" => "week", "description" => "1 Week");
            $views['month'] = array("tag" => "month", "description" => "1 Month");
            $views['months'] = array("tag" => "months", "description" => "6 Months");
            $views['year'] = array("tag" => "year", "description" => "1 Year");
            $views['all'] = array("tag" => "all", "description" => "Everything");
            $data = $this->Archive_model->select_audit_report($company_id, $selected_view);

            // Convert the encrypted results into readable material.
            $encryption_key = A2PGetEncryptionKey();
            foreach($data as $index=>$item)
            {
                if ( IsAuthenticated('pii_download', 'company', $company_id) )
                {
                    $payload = GetArrayStringValue("Payload", $item);
                    $payload = json_decode($payload, true);
                    $payload = A2PDecryptArray($payload, $encryption_key);
                    $payload = ArrayRemoveKeyStartWith('Encrypted', $payload);
                    $payload = json_encode($payload);
                    $item['Payload'] = $payload;
                    $data[$index] = $item;
                }
            }

            $view_array = array();
            $view_array = array_merge($view_array, array( "data" => $data));
            $view_array = array_merge($view_array, array( "company_name" => $company_name));
            $view_array = array_merge($view_array, array( "company_id" => $company_id));
            $view_array = array_merge($view_array, array( "selected_view" => $selected_view));
            $view_array = array_merge($view_array, array( "views" => $views));

            $view = "archive/changes";
            if ( empty($data) ) $view = "archive/changes_noresults";

			$page_template = array();
			$page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("archive/js_assets")));
			$page_template = array_merge($page_template, array("view" => $view));
			$page_template = array_merge($page_template, array("view_array" => $view_array));
			RenderView('templates/template_body_default', $page_template);

		}
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
    }
    public function audit_parent( $company_parent_id, $selected_view ) {
        try
		{

			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");
            if ( getStringValue($company_parent_id) == "" ) throw new SecurityException("Missing required input company_parent_id.");
            if ( getStringValue($selected_view) == "" ) throw new SecurityException("Missing required input selected_view.");

            $parent = $this->CompanyParent_model->get_companyparent($company_parent_id);
            $parent_name = getArrayStringValue("name", $parent);

            $view = array();
            $views['recent'] = array("tag" => "recent", "description" => "Recent");
            $views['week'] = array("tag" => "week", "description" => "1 Week");
            $views['month'] = array("tag" => "month", "description" => "1 Month");
            $views['months'] = array("tag" => "months", "description" => "6 Months");
            $views['year'] = array("tag" => "year", "description" => "1 Year");
            $views['all'] = array("tag" => "all", "description" => "Everything");
            $data = $this->Archive_model->select_parent_audit_report($company_parent_id, $selected_view);

            // Convert the encrypted results into readable material.
            $encryption_key = A2PGetEncryptionKey();
            foreach($data as $index=>$item)
            {
                if ( IsAuthenticated('pii_download', 'companyparent', $company_parent_id) )
                {
                    $payload = GetArrayStringValue("Payload", $item);
                    $payload = json_decode($payload, true);
                    $payload = A2PDecryptArray($payload, $encryption_key);
                    $payload = ArrayRemoveKeyStartWith('Encrypted', $payload);
                    $payload = json_encode($payload);
                    $item['Payload'] = $payload;
                    $data[$index] = $item;
                }
            }
            
            $view_array = array();
            $view_array = array_merge($view_array, array( "data" => $data));
            $view_array = array_merge($view_array, array( "parent_name" => $parent_name));
            $view_array = array_merge($view_array, array( "company_parent_id" => $company_parent_id));
            $view_array = array_merge($view_array, array( "selected_view" => $selected_view));
            $view_array = array_merge($view_array, array( "views" => $views));

            $view = "archive/changes";
            if ( empty($data) ) $view = "archive/changes_noresults";

			$page_template = array();
			$page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("archive/js_assets")));
			$page_template = array_merge($page_template, array("view" => $view));
			$page_template = array_merge($page_template, array("view_array" => $view_array));
			RenderView('templates/template_body_default', $page_template);

		}
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
    }

    public function support_ticket( $identifier, $identifier_type, $date_tag=null, $snapshot_tag=null ) {

        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");
            if ( getStringValue($identifier) == "" ) throw new SecurityException("Missing required input identifier.");
            if ( getStringValue($identifier_type) == "" ) throw new SecurityException("Missing required input identifier_type.");


            $identifier_name = "";
            if ( $identifier_type == 'company')
            {
                $company = $this->Company_model->get_company($identifier);
                $identifier_name = getArrayStringValue("company_name", $company);
            }
            else if ( $identifier_type === 'companyparent')
            {
                $companyparent = $this->CompanyParent_model->get_companyparent($identifier);
                $identifier_name = getArrayStringValue("Name", $companyparent);
            }
            else throw new Exception("Unsupported identifier type.");


            // What reports are available for this company?
            $reports = $this->Archive_model->select_tickets($identifier, $identifier_type);
            if ( getStringValue($date_tag) == "" )
            {
                if ( !empty($reports) )
                {
                    $first = $reports[0];
                    $date_tag = getArrayStringValue("date_tag", $first);
                }
            }

            // Prefix for Json files.
            $support_prefix = GetS3Prefix('support', $identifier, $identifier_type);
            $support_prefix = replaceFor($support_prefix, "COMPANYID", $identifier);
            $support_prefix = replaceFor($support_prefix, "COMPANYPARENTID", $identifier);
            $support_prefix = replaceFor($support_prefix, "TICKETID", $date_tag);
            //$support_prefix = fLeftBack($support_prefix, "/");
            $support_prefix .= "/json";

            // Create a collection of all json files.
            $snapshots = array();
            $files = S3ListFiles(S3_BUCKET, $support_prefix);
            foreach($files as $file)
            {
                $filename = getArrayStringValue("Key", $file);
                $tag = fRightBack(fLeftBack($filename, "."), "/");

                $details = array();
                $details['path'] = $filename;
                $details['filename'] = fRightBack($filename,"/");
                $details['tag'] = $tag;
                $details['description'] = ucwords(replaceFor($tag, "_", " "));

                $snapshots[$tag] = $details;
            }

            // If the user did not elect a snapshot, and there are some to
            // choose from, pick the first one.
            if ( $snapshot_tag == "" && count($snapshots) != 0 ) {
                $keys = array_keys($snapshots);
                $snapshot_tag = $keys[0];
            }

            // If the user selected a file, load that json file.
            $json = "{}";
            if ( getStringValue($snapshot_tag) != "" )
            {
                if ( isset($snapshots[$snapshot_tag] ) )
                {
                    $json = file_get_contents("s3://" . S3_BUCKET . "/" . getArrayStringValue("path", $snapshots[$snapshot_tag]) );
                }
            }
            $decode = json_decode($json, true);

            // If we have data, decrypt it.
            if ( ! empty($decode['data'] ) )
            {
                // FIXME: IsAuthenticated needs tweaked based on the identifier.
                /*
                if ( IsAuthenticated('pii_download', 'company', $company_id) )
                {
                    $decode['data'] = A2PDecryptArray($decode['data'], $this->encryption_key);
                    $decode['data'] = ArrayRemoveKeyStartWith("Encrypted", $decode['data']);
                }
                */
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

            // Prefix for Source Uplaod
            $prefix = GetS3Prefix('support', $identifier, $identifier_type);
            $prefix = replaceFor($prefix, "COMPANYID", $identifier);
            $prefix = replaceFor($prefix, "COMPANYPARENTID", $identifier);
            $prefix = replaceFor($prefix, "TICKETID", $date_tag);
            //$prefix = fLeftBack($prefix, "/");
            $prefix .= "/upload";

            $source_upload = "";
            $files = S3ListFiles(S3_BUCKET, $prefix);
            if ( count($files) == 1)
            {
                foreach($files as $file)
                {
                    $path = getArrayStringValue("Key", $file);
                    $source_upload = fRightBack($path, "/");
                }
            }

            // Prefix for error file.
            $support_prefix = GetS3Prefix('support', $identifier, $identifier_type);
            $support_prefix = replaceFor($support_prefix, "COMPANYID", $identifier);
            $support_prefix = replaceFor($support_prefix, "COMPANYPARENTID", $identifier);
            $support_prefix = replaceFor($support_prefix, "TICKETID", $date_tag);
            $support_prefix .= "/error";

            // Get the full path to the error text.
            $encryption_key = GetEncryptionKey($identifier, $identifier_type);
            if ( $encryption_key === '' ) throw new Exception("Unable to find encryption key.");

            $value = "";
            $files = S3ListFiles(S3_BUCKET, $support_prefix);
            foreach($files as $file)
            {
                $filename = getArrayStringValue("Key", $file);
                $value = file_get_contents("s3://" . S3_BUCKET . "/{$filename}");
                $value = A2PDecryptString($value, $encryption_key);
                if ( $value === 'FALSE' ) $value = "";
                break;
            }

            // Did we find the critical error?
            $critical_error_available = false;
            if ( $value != "" ) $critical_error_available = true;

            // Create the form.
            $more_info_form = new UIModalForm("more_info_form", "more_info_form");
            $more_info_form->setTitle( "Critical Error" );
            $more_info_form->addElement($more_info_form->textarea('more_textarea','Runtime Exception',$value,10, "", false));
            $more_info_form->addElement($more_info_form->submitButton("more_info_form_submit_button", "Okay", "btn-primary pull-right"));
            $more_info_form = $more_info_form->render();


            $view_array = array();
            $view_array = array_merge($view_array, array( "identifier" => $identifier));
            $view_array = array_merge($view_array, array( "identifier_type" => $identifier_type));
            $view_array = array_merge($view_array, array( "json" => $json));
            $view_array = array_merge($view_array, array( "metadata" => $metadata));
            $view_array = array_merge($view_array, array( "list" => $list));
            $view_array = array_merge($view_array, array( "company_name" => $identifier_name));
            $view_array = array_merge($view_array, array( "company_id" => $identifier));
            $view_array = array_merge($view_array, array( "snapshot_tag" => $snapshot_tag));
            $view_array = array_merge($view_array, array( "snapshots" => $snapshots));
            $view_array = array_merge($view_array, array( "source_upload" => $source_upload));
            $view_array = array_merge($view_array, array( "date_tag" => $date_tag));
            $view_array = array_merge($view_array, array( "reports" => $reports));
            $view_array = array_merge($view_array, array( "more_info_form" => $more_info_form));
            $view_array = array_merge($view_array, array( "critical_error_available" => $critical_error_available));

            $view = "archive/tickets";
            if ( count($snapshots) == 0 ) $view = "archive/tickets_noresults";

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("archive/js_assets")));
            $page_template = array_merge($page_template, array("view" => $view));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+

    /**
     * export_cancel
     *
     * This function will cancel the specified export.
     *
     * @param $export_id
     */
    public function export_cancel($export_id)
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");

            // Validation
            if ( $export_id == "" ) throw new Exception("Invalid input export_id");

            $this->load->library('A2PExport');
            $obj = new A2PExport();
            $obj->CancelExport($export_id);
            $obj = null;

            AJAXSuccess();  // No message.

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

    /**
     * export_delete
     *
     * This function will delete the specified export.
     *
     * @param $export_id
     */
    public function export_delete($export_id)
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");

            // Validation
            if ( $export_id == "" ) throw new Exception("Invalid input export_id");

            $this->load->library('A2PExport');
            $obj = new A2PExport();
            $obj->DeleteExport($export_id, GetSessionValue('user_id'));
            $obj = null;

            AJAXSuccess();  // No message.

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

    /**
     * export_create
     *
     * This function will create a new export.
     *
     */
    public function export_create()
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");

            // Validation
            $identifier = getArrayStringValue("identifier", $_POST);
            $identifier_type = getArrayStringValue("identifier_type", $_POST);
            if ( $identifier == "" ) throw new Exception("Invalid input identifier");
            if ( $identifier_type == "") throw new UIException("Invalid input identifier_type");

            // Create the company and companyparent_ids based on the identifier.
            if ( $identifier_type === 'company' )
            {
                $company_id = $identifier;
                $companyparent_id = GetCompanyParentId($identifier);
            }
            else if ( $identifier_type === 'companyparent')
            {
                $company_id = '';
                $companyparent_id = $identifier;
            }


            // Collect ALL of the reports that the user has requested into an
            // array.  Each item in the array is a report code.
            $reports = [];
            foreach($_POST as $key=>$value)
            {
                if ( StartsWith($key, 'cbox-') && $value =='on' )
                {
                    $reports[] = ReplaceFor($key, "cbox-", '');
                }
            }

            // Find all YEARS in which this entity has reports of some kind.
            $years = $this->Export_model->select_export_report_years_list($identifier, $identifier_type);

            // For each report, we want to create an individual export.
            foreach($reports as $report_code)
            {
                foreach($years as $year)
                {
                    // Create the export!
                    $this->Export_model->insert_export($identifier, $identifier_type, 'REQUESTED');
                    $export_id = $this->Export_model->get_recent_export_id($identifier, $identifier_type);

                    $this->Export_model->upsert_export_property($export_id, 'report_code', $report_code);
                    $this->Export_model->upsert_export_property($export_id, 'year', $year);

                    $user_id    = GetSessionValue('user_id');
                    $exec_time  = date('Y-m-d H:i:s', strtotime('+10 seconds'));
                    $job_id = $this->Queue_model->add_grouped_worker_job( $companyparent_id, $company_id, $user_id, A2P_COMPANY_ID, 'FileExport', 'index', $exec_time );

                    // Save the job id as a property on the export.
                    $this->Export_model->upsert_export_property($export_id, 'job_id', $job_id);
                }
            }

            AJAXSuccess();  // No message.

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

    /**
     * render_export_content
     *
     * This function will accept a user request to render an ajax UI object for
     * the export tool.  You can request the MANAGE widget, the CREATE widget or
     * the REMOVE widget.
     *
     * @param $identifier
     * @param $identifier_type
     * @param $type
     * @param string $export_id
     */
    public function render_export_content($identifier, $identifier_type, $type, $export_id='' )
    {
        try
        {
            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");

            // validate required inputs.
            if ( GetStringValue($identifier) == "" ) throw new Exception("Invalid input: identifier");
            if ( GetStringValue($identifier_type) == "" ) throw new Exception("Invalid input: identifier_type");
            if ( GetStringValue($type) == "" ) throw new Exception("Invalid input: type");

            $this->load->library('A2PExport');
            $obj = new A2PExport();

            if ( strtoupper($type) === 'MANAGE' )
            {
                $html = $obj->RenderManageWidget($identifier, $identifier_type);
            }
            else if ( strtoupper($type) === 'CREATE' )
            {
                $html = $obj->RenderCreateWidget($identifier, $identifier_type);
            }
            else if ( strtoupper($type) === 'REMOVE' )
            {
                if ( GetStringValue($export_id) == "" ) throw new Exception("Invalid input: export_id");
                $html = $obj->RenderRemoveWidget($export_id);
            }
            else
            {
                throw new Exception("Invalid Type!");
            }

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }

    public function take_snapshot($company_id, $user_id) {
        try{
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");

            // SNAPSHOTS
            TakeSnapshots($company_id, $user_id, $this->encryption_key);

            $recent_date = GetUploadDate($company_id);
            $parts = explode("/", $recent_date);
            $date_tag = getArrayStringValue("2", $parts) . getArrayStringVAlue("0", $parts);
            $url = base_url() . "support/snapshots/company/{$company_id}/{$date_tag}/column_mappings";
            AJAXSuccess("Snapshot created.", $url );


		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }

    }


    // RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function render_archive_download( $identifier, $identifier_type, $date_tag, $snapshot_tag ) {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_write") ) throw new SecurityException("Missing required write permission.");

            if ( $snapshot_tag != "upload" ) $this->_stream_snapshot($identifier, $identifier_type, $date_tag, $snapshot_tag);

        }
        catch (Exception $e)
        {
            Error404("File not found.");
        }
    }
    public function render_original_upload($identifier, $identifier_type, $date=null) {
        try
        {
            // Check Security
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new Exception("Unsupport method.");
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_write") ) throw new SecurityException("Missing required write permission.");

            if ( getStringValue($identifier) == "" ) throw new Exception("Missing required input identifier");
            if ( getStringValue($identifier_type) == "" ) throw new Exception("Missing required input identifier_type");
            if ( getStringValue($date) == "" ) throw new Exception("Missing required input date");

            // PII Download
            // Do now allow the original file to be downloaded if the user does not have permission to
            // download pii data.
            if ( ! IsAuthenticated('pii_download', $identifier_type, $identifier) )
            {
                throw new SecurityException("Missing required permission to download.");
            }


            $this->_stream_upload($identifier, $identifier_type, $date, false);
        }
        catch (Exception $e)
        {
            Error404("File not found.");
        }
    }
    public function render_original_encrypted_upload($identifier, $identifier_type, $date=null)
    {
        try
        {
            // Check Security
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new Exception("Unsupport method.");
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required write permission.");

            if ( getStringValue($identifier) == "" ) throw new Exception("Missing required input identifier");
            if ( getStringValue($identifier_type) == "" ) throw new Exception("Missing required input identifier_type");
            if ( getStringValue($date) == "" ) throw new Exception("Missing required input date");

            $this->_stream_upload($identifier, $identifier_type, $date, true);
        }
        catch (Exception $e)
        {
            Error404("File not found.");
        }
    }


    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

    /**
     * _stream_upload
     *
     * Write an upload file to standard out.  The upload file that will be
     * written belongs to the specified company for the given date.  The
     * encrypted flag indicates if it should just return the encrypted file
     * as it stands or if we should decrypt it first.
     *
     * @param null $company_id
     * @param null $date
     * @param bool $encrypted
     * @throws Exception
     */
    private function _stream_upload($identifier, $identifier_type, $date=null, $encrypted=false)
    {

        // Check to see if the file exists in the archive first.
        $prefix = GetS3Prefix('archive', $identifier, $identifier_type);
        $prefix = replaceFor($prefix, "COMPANYID", $identifier);
        $prefix = replaceFor($prefix, "COMPANYPARENTID", $identifier);
        $prefix = replaceFor($prefix, "DATE", $date);
        $prefix .= "/upload";

        if ( count(S3ListFiles(S3_BUCKET, $prefix)) !== 1 )
        {
            // Couldn't find it, okay, look in the support folder.
            // This looks like a timestamp.  That means we have this data stored
            // under the support folder, not the archive folder.
            $prefix = GetS3Prefix('support', $identifier, $identifier_type);
            $prefix = replaceFor($prefix, "COMPANYID", $identifier);
            $prefix = replaceFor($prefix, "COMPANYPARENTID", $identifier);
            $prefix = replaceFor($prefix, "TICKETID", $date);
            $prefix .= "/upload";
        }

        // Stream the file out as a zip file.
        $filename = "";
        $files = S3ListFiles(S3_BUCKET, $prefix);
        if ( count($files) == 0 ) throw new Exception("File not found.");
        if ( count($files) > 1 ) throw new Exception("Found too many files.");
        foreach($files as $file)
        {
            $filename = getArrayStringValue("Key", $file);
            $file = fRightBack($filename, "/");
        }

        // Audit what is about to happen.

        if ( $identifier_type === 'company' )
        {
            $company = $this->Company_model->get_company($identifier);
            $payload = array();
            $payload = array_merge($payload, array('ImportDate'=>$date));
            $payload = array_merge($payload, array('Filename'=>$filename));
            $payload = array_merge($payload, array('CompanyId'=>$identifier));
            $payload = array_merge($payload, array('CompanyName'=>GetArrayStringValue('company_name', $company)));
            $payload = array_merge($payload, array('Encrypted'=>GetStringValue($encrypted)));
            AuditIt("Downloaded original upload file.", $payload);
        }
        else if ( $identifier_type === 'companyparent' )
        {
            $companyparent = $this->CompanyParent_model->get_companyparent($identifier);
            $payload = array();
            $payload = array_merge($payload, array('ImportDate'=>$date));
            $payload = array_merge($payload, array('Filename'=>$filename));
            $payload = array_merge($payload, array('CompanyParentId'=>$identifier_type));
            $payload = array_merge($payload, array('CompanyParentName'=>GetArrayStringValue('Name', $companyparent)));
            $payload = array_merge($payload, array('Encrypted'=>GetStringValue($encrypted)));
            AuditIt("Downloaded original upload file.", $payload);
        }

        // Stream the file back as a CSV, decrypt each line as we go.
        $encryption_key = GetEncryptionKey($identifier, $identifier_type);
        $fh = S3OpenFile(S3_BUCKET, $prefix, $file);

        header("Content-type: text/csv");
        header('Content-Disposition: attachment; filename="'.$file.'"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: IE is too broken to support Content-Disposition properly');
        try {
            if ($fh) {
                while (($line = fgets($fh)) !== false)
                {
                    // If the user requested the encrypted file, just output each line.
                    if ( $encrypted )
                    {
                        print $line;
                        continue;
                    }

                    // If the user requested the decrypted file, do that on the way out.
                    if ( IsEncryptedString($line) )
                    {
                        print A2PDecryptString(trim($line), $encryption_key) . PHP_EOL;
                    }
                    else if ( IsEncryptedStringComment($line) )
                    {
                        // do nothing, exclude this line from the output.
                    }
                    else
                    {
                        print $line;
                    }

                }
                fclose($fh);
            }
        }catch(Exception $e) {
            if ( is_resource($fh) ) fclose($fh);
        }
        if ( is_resource($fh) ) fclose($fh);

    }
    private function _stream_snapshot($identifier, $identifier_type, $date=null, $snapshot_tag=null) {

        try {

            // Check method.
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "GET") throw new Exception("Unsupport method.");

            // Check Security
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "GET") throw new Exception("Unsupport method.");
            if (!IsLoggedIn()) throw new SecurityException("You must be logged into access this function.");
            if (!IsAuthenticated("support_write")) throw new SecurityException("Missing required write permission.");

            if (getStringValue($identifier) == "") throw new Exception("Missing required input identifier");
            if (getStringValue($identifier_type) == "") throw new Exception("Missing required input identifier_type");
            if (getStringValue($date) == "") throw new Exception("Missing required input date");
            if (getStringValue($snapshot_tag) == "") throw new Exception("Missing required input snapshot_tag");


            // PII Download
            // Do now allow the original file to be downloaded if the user does not have permission to
            // download pii data.
            if ( ! IsAuthenticated('pii_download', $identifier_type, $identifier) )
            {
                throw new SecurityException("Missing required permission to download.");
            }

            $encryption_key = GetEncryptionKey($identifier, $identifier_type);

            // Check to see if the file exists in the archive first.
            $prefix = GetS3Prefix('archive', $identifier, $identifier_type);
            $prefix = replaceFor($prefix, "COMPANYID", $identifier);
            $prefix = replaceFor($prefix, "COMPANYPARENTID", $identifier);
            $prefix = replaceFor($prefix, "DATE", $date);
            $prefix .= "/json";
            if ( ! S3DoesFileExist( S3_BUCKET, $prefix, "{$snapshot_tag}.json" ) )
            {
                // Couldn't find it, okay, look in the support folder.
                // This looks like a timestamp.  That means we have this data stored
                // under the support folder, not the archive folder.
                $prefix = GetS3Prefix('support', $identifier, $identifier_type);
                $prefix = replaceFor($prefix, "COMPANYID", $identifier);
                $prefix = replaceFor($prefix, "COMPANYPARENTID", $identifier);
                $prefix = replaceFor($prefix, "TICKETID", $date);
                $prefix .= "/json";
            }

            // Stream the file from S3 back to the user as a zip file.
            if ( ! S3DoesFileExist( S3_BUCKET, $prefix, "{$snapshot_tag}.json" ) ) throw new Exception("Snapshot not found.");


            $fh = null;
            $json = "";

            // Read the snapshot file and decrypt the data.
            try
            {
                $fh = S3OpenFile(S3_BUCKET, $prefix, "{$snapshot_tag}.json");
                $iterator = $this->_readTheFile($fh);
                foreach ($iterator as $iteration)
                {
                    $json .= $iteration;
                }
                if ( is_resource($fh) ) fclose($fh);
            }
            catch(Exception $e)
            {
                if ( is_resource($fh) ) fclose($fh);
            }

            // Decrypt the json file we are about to return to the end user.
            $snapshot = json_decode($json, TRUE);
            if ( isset($json["data"] ) )
            {
                $snapshot['data'] = A2PDecryptArray($json['data'], $encryption_key);
                $snapshot['data'] = ArrayRemoveKeyStartWith("Encrypted", $snapshot['data']);
            }

            // Stream it back.
            $filename = $date . "_" . $identifier_type . "_" . $identifier ."_{$snapshot_tag}.json";
            header("Content-type: text/json");
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: IE is too broken to support Content-Disposition properly');
            print_r($snapshot);






        }catch(Exception $e) {
            Error404($e->getMessage());
        }
    }
    private function _readTheFile($handle)
    {
        while(!feof($handle)) {
            yield trim(fgets($handle));
        }
    }
    /**
     * _lives_widget
     *
     * This is the table of data that shows a list of lives from
     * which you can jump to other pages.
     *
     * @param $company_id
     * @return string|void
     */
    private function _lives_widget($company_id)
    {
        $encryption_key = GetCompanyEncryptionKey($company_id);
        $lives = $this->Life_model->select_all_lives($company_id);
        $lives = A2PDecryptArray($lives, $encryption_key);

        $headings = array();
        if ( ! empty($lives) ) $headings = array_keys($lives[0]);


        $view_array = array();
        $view_array['data'] = $lives;
        $view_array['headings'] = $headings;
        $view_array['company_id'] = $company_id;
        return RenderViewAsString("archive/lives_widget", $view_array);
    }
    private function _timers_widget($company_id, $import_date)
    {
        $timers = $this->Support_model->select_support_timer_report($company_id, $import_date);

        $headings = array();
        if ( ! empty($timers) ) $headings = array_keys($timers[0]);

        $view_array = array();
        $view_array['data'] = $timers;
        $view_array['headings'] = $headings;
        $view_array['company_id'] = $company_id;
        $view_array['import_date'] = $import_date;
        return RenderViewAsString("archive/support_timers_report_widget", $view_array);
    }
    private function _getDefaultDateTag($date_tag, $reports)
    {
        if ( GetStringValue($date_tag) == "" )
        {
            if ( !empty($reports) )
            {
                $first = $reports[0];
                return GetArrayStringValue("date_tag", $first);
            }
        }
        return $date_tag;
    }
}
