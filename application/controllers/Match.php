<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Match extends SecureController {

	function __construct(){
		parent::__construct();

		$this->load->model('Company_model','company_model',true);
		$this->load->model('mapping_model','mapping_model',true);
        $this->load->model('Wizard_model','wizard_model',true);
		$this->load->model('Queue_model','queue_model',true);
		$this->load->helper("wizard");
		$this->load->helper("match");

		// We need to turn this on since we will be parsing the preview CSV file.
		ini_set('auto_detect_line_endings',TRUE);


        // Protect against multiple users working the wizard at the same
        // time.  If the wizard state no longer matches this step, push
        // them to the dashboard.
        if ( IsMatchStepComplete() )
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
			if ( ! IsStartupStepComplete() ) redirect(base_url());
			if ( ! IsUploadStepComplete() ) redirect(base_url());

			$user_id = GetSessionValue("user_id");
			$company_id = GetSessionValue("company_id");
			$this->init($company_id);

			// Collect the company parent id, if possible.
            $companyparent_id = GetCompanyParentId( $company_id );

			$page_header = new UIFormHeader();
            $page_header->setTitle("Match Data");
            $page_header = $page_header->render();

			// Collect data needed for mapping table.
			$mapping_columns = $this->mapping_model->get_mapping_columns($company_id, $companyparent_id);
            $sample_mapping_data = $this->_get_sample_csv_data( );

			// what are the required columns?
			$required_list = array();
			$columns = $this->mapping_model->get_required_mapping_columns($company_id, $companyparent_id);
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
			$has_headers = DoesUploadContainHeaderRow( $company_id );

            $attributes = array();
            $attributes['save'] = base_url('wizard/match/save');
            $attributes['identifier'] = GetSessionValue('company_id');
            $attributes['identifier_type'] = "company";

			// Generate the form for this step.
			$validation_form = new UIWizardForm("validate_upload_form");
			$validation_form->setAction(base_url("wizard/validate"));
			$validation_form->addElement($validation_form->mapping_table_missing_matches($required_list, $conditional_list));
			$validation_form->addTopWizardButton($validation_form->button("upload_complete_button", "Continue", "btn-primary", true));
			$validation_form->addTopWizardButton($validation_form->button("wizard_start_over_btn", "Start Over", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/cancel"))));
			$validation_form->addElement($validation_form->top_buttons());
			$validation_form->addElement($validation_form->mapping_table($sample_mapping_data, $mapping_columns, $required_list, $conditional_list, $has_headers, $attributes));
			$validation_form = $validation_form->render();

			$view_array = array();
			$view_array = array_merge($view_array, array("page_header" => $page_header));
			$view_array = array_merge($view_array, array("validation_form" => $validation_form));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("match/match_js_assets")));
            $page_template = array_merge($page_template, array("view" => "match/match"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

		}
		catch ( UIException $e ) {
			// TODO: This is not an ajax page.  You need to redirect back to the dashboard with error.
			AjaxDanger($e->getMessage());
		}
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404( $e ); }
	}

    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	public function cancel() {
		try{

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");


			$company_id = GetSessionValue("company_id");
			RollbackWizardAttempt($company_id);


			if ( getStringValue($this->input->server('REQUEST_METHOD')) == "POST" )
			{
				AJAXSuccess("Rollback complete.");
			}else{
				redirect(base_url("dashboard"));
				exit;
			}


		}
		catch ( UIException $e ) {
			if ( getStringValue($this->input->server('REQUEST_METHOD')) == "POST" )
			{
				AjaxDanger($e->getMessage());
			}else{
				redirect(base_url("dashboard"));
			}
			exit;
		}
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
	}



	public function validate() {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");

			$company_id = GetSessionValue("company_id");
			$user_id = GetSessionValue("user_id");
			$companyparent_id = GetCompanyParentId($company_id);
            $this->init($company_id);

			// Everything must be matched, else you may not continue.
			$all_matched = AllRequireColumnsMatched($company_id, 'company');
			if ( $all_matched !== TRUE )
			{
				AJAXDanger($all_matched);
			}

            // BENEFICIARY_MAPPING
            // If the beneficiary mapping feature is enabled, you can't quickscan because the
            // lookup file may have been created before the user mapped or change the mapping.
            // We need to rebuild it, but it's a big process so it has to be done on the sever.
            $quickscan_enabled = true;
            if ( IsAtLeastOneFeatureEnabledForCompany($company_id, 'BENEFICIARY_MAPPING') ) $quickscan_enabled = false;

            // Quick scan
            if ( $quickscan_enabled )
            {
                if ( ! QuickScanPreviewFile($company_id, 'company', $this->encryption_key) )
                {
                    $this->Wizard_model->match_step_complete($company_id);
                    $this->Wizard_model->validation_step_complete($company_id);
                    AJAXDanger("Quick scan found a few issues.", base_url("wizard/correct") );
                }
            }
            else
            {
                sleep(3);
            }


			// Auto Mapping disabled.
            // Now that the user has hit "continue" and we don't see any
            // problems with their mappings ... do not apply the A2P first time
            // suggestions.
			DisableA2PAutoColumnMapping($company_id, 'company');

			// Audit this transaction
            AuditIt("Reviewed column mappings.", array());

			// Okay, this step in the wizard is now complete.
            $this->Wizard_model->match_step_complete($company_id);
			$this->queue_model->add_worker_job($companyparent_id, $company_id, $user_id, "ValidateCSVUpload", "index");

			AJAXSuccess("All required columns reviewed.", base_url() );

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
		AJAXDanger("Unexpected situation.  Please try again later.");


	}
    public function save_match() {
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

			// Pull out the match data.
			$company_id = GetSessionValue("company_id");
			$has_headers = GetArrayStringValue("has_headers", $_POST);
			$headers = array(); if ( isset($_POST["headers"] ) ) $headers = $_POST["headers"];
			$mappings = array(); if ( isset($_POST["mappings"] ) ) $mappings = $_POST["mappings"];
			$columns = array(); if ( isset($_POST["columns"], ) ) $columns = $_POST["columns"];

			// Validation
			if ( $company_id == "" ) throw new Exception("Missing required input company_id.");
			if ( $has_headers == "" ) throw new Exception("Missing required input has_headers");
			if ( empty($headers) ) throw new Exception("Missing required input headers");
			if ( empty($mappings) ) throw new Exception("Missing required input mappings");
			if ( empty($columns) ) throw new Exception("Missing required input columns");

			// Remove all of our preferences.  We are about to make them again.
			$this->company_model->remove_company_preference_group($company_id, "column_map");
			$this->company_model->remove_company_preference_group($company_id, "user_column_label_map");
			$this->company_model->remove_company_preference_group($company_id, "upload_contains_header_row");

			// Remember if we have headers or not.
			$this->company_model->save_company_preference($company_id, "upload_contains_header_row", "boolean", $has_headers);

			$index = 0;
			foreach($mappings as $mapping)
			{
				$mapping = getStringValue($mapping);
				$column_no = getStringValue($columns[$index]);
				$header = getStringValue($headers[$index]);

                $this->company_model->save_company_preference($company_id, "column_map", "col{$column_no}", $mapping);
                if ( $has_headers == "t" && $header != "" )
                {
                    $this->company_model->save_company_preference($company_id, "user_column_label_map", strtoupper($header), $mapping);
                }

				$index++;
			}

			AJAXSuccess("Preference saved.");

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }

	}






    // VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    // RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-


    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

	private function _get_sample_csv_data ( ) {

        $company_id = GetSessionValue("company_id");
		if ( $company_id == "" ) throw new Exception("Missing required input company_id.");

		$client = S3GetClient();
        $parsed_prefix = replaceFor(GetConfigValue("parsed_prefix"), "COMPANYID", $company_id);
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
