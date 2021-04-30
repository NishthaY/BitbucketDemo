<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CompanyParentMatch extends A2PWorkflowStepController
{
    //protected $timers;                  // See parent class for more details
    //protected $timer_array;             // See parent class for more details
    //protected $encryption_key;          // See parent class for more details
    //protected $wf_stepname;             // See parent class for more details
    //protected $wf_name;                 // See parent class for more details
    //protected $identifier;              // See parent class for more details
    //protected $identifier_type;         // See parent class for more details
    //protected $timers;                  // See parent class for more details
    //protected $timer_array;             // See parent class for more details
    //protected $encryption_key;          // See parent class for more details


    public function index($wf_name) {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Init
            // Setup core properties on this class.
            $this->init('', GetSessionValue('companyparent_id'));

            // Properties
            // Set the global properties on this class based on the workflow name passed in.
            $this->setWorkflowProperties($wf_name, 'parse');

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            // Navigation Check!
            // Make sure users don't jump forwards in a workflow.
            if ( ! IsWorkflowWaiting( $this->wf_name, $this->wf_stepname, $this->identifier, $this->identifier_type) ) throw new UIException("Workflow not ready.");


            $page_header = new UIFormHeader();
            $page_header->setTitle("Match Data");
            $page_header = $page_header->render();



            // Collect data needed for mapping table.
            $mapping_columns = $this->Mapping_model->get_mapping_columns(null, $this->identifier);
            $sample_mapping_data = $this->_get_sample_csv_data( );


            // what are the required columns?
            $required_list = array();
            $columns = $this->Mapping_model->get_required_mapping_columns(null, $this->identifier);
            foreach($columns as $column)
            {
                $required_list[] = getArrayStringValue("name", $column);
            }

            // Create the conditional list structure that the validation page can consume.
            // This is a dictionary with the column name as the key.  The value is an array of
            // columns the conditional rule apply to.
            $conditional_list = array();
            foreach($mapping_columns as $item)
            {
                if ( GetArrayStringValue('conditional_list', $item) !== '' )
                {
                    $key = GetArrayStringValue('name', $item);
                    $value = GetArrayStringValue('conditional_list', $item);
                    $list = explode(',', $value);
                    sort($list);
                    $conditional_list[$key] = $list;
                }
            }

            // Review this companies preferences and see if the last time they
            // uploaded the file if they included headers or not.
            $has_headers = DoesUploadContainHeaderRow( null, $this->identifier );

            $attributes = array();
            $attributes['save'] = base_url('parent/match/save');
            $attributes['identifier'] = GetSessionValue('companyparent_id');
            $attributes['identifier_type'] = "companyparent";

            // Generate the form for this step.
            $validation_form = new UIWizardForm("validate_upload_form");
            $validation_form->setAction(base_url("parent/match/validate"));
            $validation_form->addElement($validation_form->mapping_table_missing_matches($required_list, $conditional_list));
            $validation_form->addTopWizardButton($validation_form->button("upload_complete_button", "Continue", "btn-primary", true));
            $validation_form->addTopWizardButton($validation_form->button("workflow_start_over_btn", "Start Over", " btn-wf-rollback btn-default pull-left m-l-0", false, array("href" => base_url("workflow/rollback/{$this->wf_name}"))));
            $validation_form->addElement($validation_form->top_buttons());
            $validation_form->addElement($validation_form->hiddenInput("wf_name", $this->wf_name));
            $validation_form->addElement($validation_form->mapping_table($sample_mapping_data, $mapping_columns, $required_list, $conditional_list, $has_headers, $attributes));
            $validation_form = $validation_form->render();



            $view_array = array();
            $view_array = array_merge($view_array, array("page_header" => $page_header));
            $view_array = array_merge($view_array, array("validation_form" => $validation_form));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("companyparentmatch/match_js_assets")));
            $page_template = array_merge($page_template, array("view" => "match/match"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch ( UIException $e ) { redirect(base_url("dashboard/parent")); }
        catch( SecurityException $e ) { AccessDenied($e->getMessage()); }
        catch( Exception $e ) { Error404( $e ); }
    }
    public function validate() {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");

            $wf_name = GetArrayStringValue('wf_name', $_POST);
            $this->setWorkflowProperties($wf_name, 'parse');

            // BUSINESS LOGIC

            // Everything must be matched, else you may not continue.
            $all_matched = AllRequireColumnsMatched($this->identifier, $this->identifier_type);
            if ( $all_matched !== TRUE )
            {
                AJAXDanger($all_matched);
            }

            // Audit this transaction
            AuditIt("Reviewed column mappings.", array());

            // Auto Mapping disabled.
            // Now that the user has hit "continue" and we don't see any
            // problems with their mappings ... do not apply the A2P first time
            // suggestions.
            DisableA2PAutoColumnMapping($this->identifier, $this->identifier_type);

            // QUICK SCAN
            // If the quick scan fails, move te workflow forward one step and set into it's
            // waiting step.  It just so happens that the next step is validation and it's
            // waiting screen is the correct screen.  We know this is where we will land if
            // we push this to the background process, but there is no reason to make the user wait.
            if ( ! QuickScanPreviewFile($this->identifier, $this->identifier_type, $this->encryption_key) )
            {
                WorkflowMoveToState($this->identifier, $this->identifier_type, $wf_name, 'validate');
                WorkflowStateSetWaiting($this->identifier, $this->identifier_type, $wf_name);
                AJAXDanger("Quick scan found a few issues.", base_url("parent/correct/".$this->wf_name) );
            }

            // Note the data as it stands now and then move the workflow forward.
            $this->takeSnapshot();
            WorkflowStateMoveForward($this->identifier, $this->identifier_type, $wf_name);
            WorkflowStartBackgroundJob($this->identifier, $this->identifier_type, $wf_name, GetSessionValue('user_id'));
            AJAXSuccess("Good news Everybody!   ", base_url('dashboard/parent') );
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }


    }

    /**
     * save_match ( POST )
     *
     * This function will update preferences and store mapping preferences.
     *   - column_map
     *   - user_column_label_map
     *   - upload_contains_header_row
     */
    public function save_match()
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException(("Missing required permission: parent_company_write"));

            // Pull out the match data.
            $companyparent_id = GetSessionValue("companyparent_id");
            $has_headers = GetArrayStringValue("has_headers", $_POST);
            $headers = array(); if ( isset($_POST["headers"] ) ) $headers = $_POST["headers"];
            $mappings = array(); if ( isset($_POST["mappings"] ) ) $mappings = $_POST["mappings"];
            $columns = array(); if ( isset($_POST["columns"] ) ) $columns = $_POST["columns"];

            // Validation
            if ( $companyparent_id == "" ) throw new Exception("Missing required input: companyparent_id");
            if ( $has_headers == "" ) throw new Exception("Missing required input: has_headers");
            if ( empty($headers) ) throw new Exception("Missing required input: headers");
            if ( empty($mappings) ) throw new Exception("Missing required input: mappings");
            if ( empty($columns) ) throw new Exception("Missing required input: columns");

            // Remove all of our preferences.  We are about to make them again.
            RemovePreferences($companyparent_id, 'companyparent', 'column_map');
            RemovePreferences($companyparent_id, 'companyparent', 'user_column_label_map');
            RemovePreferences($companyparent_id, 'companyparent', 'upload_contains_header_row');

            // Remember if we have headers or not.
            SavePreference($companyparent_id, 'companyparent', "upload_contains_header_row", "boolean", $has_headers );

            $index = 0;
            foreach($mappings as $mapping)
            {
                $mapping = getStringValue($mapping);
                $column_no = getStringValue($columns[$index]);
                $header = getStringValue($headers[$index]);

                SavePreference($companyparent_id, 'companyparent', "column_map", "col{$column_no}", $mapping);
                if ( $has_headers == "t" && $header != "" )
                {
                    SavePreference($companyparent_id, 'companyparent', "user_column_label_map", strtoupper($header), $mapping);
                }

                $index++;
            }

            AJAXSuccess("Preference saved.");

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }


    private function _get_sample_csv_data ( ) {

        $companyparent_id = GetSessionValue("companyparent_id");
        if ( $companyparent_id == "" ) throw new Exception("Missing required input companyparent_id.");

        $client = S3GetClient();
        $parsed_prefix = GetS3Prefix('parsed', $companyparent_id, 'companyparent');
        $url = "s3://".S3_BUCKET."/{$parsed_prefix}/preview.csv";
        if ( ! file_exists($url) ) throw new Exception("Could not find sample data for display! [".$url."]");

        $csv = array();
        $fh = fopen($url, 'r');
        if ( $fh ) {
            while (($line = fgets($fh)) !== false) {
                $line = trim($line);
                $line = A2PDecryptString($line, $this->encryption_key);
                $csv[] = str_getcsv($line);
            }
            fclose($fh);
        }
        return $csv;

    }
}
