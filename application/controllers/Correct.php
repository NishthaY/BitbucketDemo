<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Correct extends SecureController {

	function __construct(){
		parent::__construct();

		$this->load->model('Company_model','company_model',true);
		$this->load->model('mapping_model','mapping_model',true);
        $this->load->model('Wizard_model','wizard_model',true);
		$this->load->model('Queue_model','queue_model',true);
		$this->load->helper("wizard");

        // Protect against multiple users working the wizard at the same
        // time.  If the wizard state no longer matches this step, push
        // them to the dashboard.
        if ( IsCorrectStepComplete() )
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
            if ( ! IsMatchStepComplete() ) redirect( base_url() . "wizard/match" );

            $company_id = GetSessionValue("company_id");
            $this->init($company_id);

            // companyparent_id
            $companyparent_id = GetCompanyParentId( $company_id );

            // WIDGET: Show Upload Data Error Modal
            $show_error_widget = new UIWidget("data_error_widget");
            $show_error_widget->setBody( $this->_data_error_form(null, null) );
            $show_error_widget->setHref(base_url("wizard/widget/error"));
            $show_error_widget = $show_error_widget->render();

            $page_header = new UIFormHeader();
            $page_header->setTitle("File Corrections");
            $page_header = $page_header->render();


            // Generate the form for this step.
            $form = new UIWizardForm("wizard_correct_form");
            $upload = GetUploadFormData();
            $form->setUploadAttributes($upload["attributes"]);
            $form->setUploadInputs($upload["inputs"]);
			$form->addTopWizardButton($form->button("wizard_start_over_btn", "Start Over", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/cancel"))));
			$form->addTopWizardButton($form->button("wizard_rematch_btn", "Match Columns", " btn-default pull-left m-l-0", false, array("href" => base_url("wizard/rematch"))));
            $form->addElement($form->top_buttons());
            $form = $form->render();

            // Read the error report from S3
            $client = S3GetClient();
            $prefix = replaceFor(GetConfigvalue("errors_prefix"), "COMPANYID", $company_id);
            $errors = array();
            if ( S3DoesFileExist( S3_BUCKET, $prefix, "errors.json" ) )
            {
                $url = "s3://".S3_BUCKET."/".$prefix."/errors.json";
                $errors = file_get_contents($url);
                $errors = json_decode($errors, true);
            }

            // Decrypt the error data.
            foreach($errors as $key=>$detail)
            {
                if ( isset($detail['data']))
                {
                    for($i=0;$i<count($detail['data']);$i++)
                    {
                        $errors[$key]['data'][$i] = A2PDecryptString(trim($detail['data'][$i]), $this->encryption_key) . PHP_EOL;
                    }
                }
            }

            // Figure out if the user said there were column headers or not.
            $has_headers = DoesUploadContainHeaderRow( $company_id );

            // Grab the orignal header/column mappings.
            $orig = $this->company_model->get_company_preference($company_id, "headers", "user_names");
            $orig = getArrayStringValue("value", $orig);
            $orig = json_decode($orig, true);
            $orig_lookup = array();
            if ( isset($orig['col_lookup']) ) $orig_lookup = $orig['col_lookup'];


            // Read in the mapped columns
            $mapped_columns = $this->mapping_model->get_mapped_columns($company_id, 'company');
            $headers = array();
            $index = 0;
            foreach($mapped_columns as $mapped_column)
            {
                $column_name = getArrayStringValue("Value", $mapped_column);
                if( $column_name !== '' )
                {
                    // This is a mapped column.
                    $display = $this->mapping_model->get_mapping_column_by_name($column_name, $company_id, $companyparent_id);
                    $column_no = $this->mapping_model->get_mapped_column_no($company_id, $companyparent_id, $column_name);
                    $column_display = getArrayStringValue("display", $display);
                    if ( $has_headers ) $orig_column_display = getArrayStringValue("col{$column_no}", $orig_lookup);
                    if ( ! $has_headers ) $orig_column_display = " Column #{$column_no}";
                    $headers[$index] = array("mapped" => true, "column_name" => $column_name, "column_display" => $column_display, "orig_column_display" => $orig_column_display);
                }
                else
                {
                    // The user has not mapped this column.
                    // Do not add it to the header list so the unmapped columns will now be visible.
                }
                $index++;
            }

            $view_array = array();
            $view_array = array_merge($view_array, array("page_header" => $page_header));
            $view_array = array_merge($view_array, array("form" => $form));
            $view_array = array_merge($view_array, array("errors" => $errors));
            $view_array = array_merge($view_array, array("headers" => $headers));
            $view_array = array_merge($view_array, array("widget" => $show_error_widget));
            $view_array = array_merge($view_array, array("href" => base_url("wizard/widget/upload/error/ROW/COLUMN")));
            $view_array = array_merge($view_array, array("identifier" => $company_id));
            $view_array = array_merge($view_array, array("identifier_type" => 'company'));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("correct/correct_js_assets")));
            $page_template = array_merge($page_template, array("view" => "correct/correct"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);


        }
        catch ( UIException $e ) {
			AjaxDanger($e->getMessage());
		}
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }

    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    public function rematch() {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");

            $company_id = GetSessionValue("company_id");

            // Remove the validation errors.
            $this->Validation_model->delete_validation_errors( $company_id, 'company' );

            // Remove Error prefix.
            try
            {
                $client = S3GetClient();
                S3DeleteBucketContent( S3_BUCKET, replaceFor(GetConfigValue("errors_prefix"), "COMPANYID", $company_id) );
            }
            catch(Exception $e){ }

            // Clear the wizard columns that will allow us to move back to the match step.
            $this->Wizard_model->reset_wizard_to_match($company_id);

            AJAXSuccess("Ready to re-match.", base_url("wizard/match"));


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }


    // VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    // RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function render_data_error_form($row_no, $column_name) {
        try
        {
        	// Check method.
        	if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

        	// Check Security
        	if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

        	// organize inputs.
        	$company_id = GetSessionValue("company_id");

        	// validate required inputs.
        	if ( $company_id == "" ) throw new Exception("Invalid input company_id");
        	if ( $row_no == "" ) throw new Exception("Invalid input row_no");
        	if ( $column_name == "" ) throw new Exception("Invalid input column_name");
        	$output = $this->_data_error_form($row_no, $column_name);
        	$array = array();
        	$array['responseText'] = $this->_data_error_form($row_no, $column_name);
        	AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
        	AJAXDanger($e->getMessage());
        }
    }
    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    private function _data_error_form( $row, $column ) {

		$company_id = GetSessionValue("company_id");

		if ( getStringValue($row) == "" ) return "";
		if ( getStringValue($column) == "" ) return "";
		if ( $company_id == "" ) return "";

		$error_details = $this->Validation_model->get_validation_error($company_id, 'company', $row, $column);

		$view_array = array();
		$view_array = array_merge( $view_array, array("row" => $row) );
		$view_array = array_merge( $view_array, array("column" => $column) );
		$view_array = array_merge( $view_array, array("details" => $error_details) );
        $view_array = array_merge( $view_array, array("company_id" => $company_id) );

		$form = new UIModalForm("wizard_error_form", "wizard_error_form", base_url("wizard/upload/error"));
		$form->setTitle("Error Information");
		$form->setCollapsable(true);
		$form->addElement($form->htmlView("correct/correct_data_cell", $view_array));
		$form->addElement($form->button("no_btn", "Okay", "btn-primary pull-right"));
		$form_html = $form->render();
		return $form_html;
	}

}
