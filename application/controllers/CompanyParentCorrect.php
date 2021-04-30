<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CompanyParentCorrect extends A2PWorkflowStepController {

    //protected $wf_stepname;             // See parent class for more details
    //protected $wf_name;                 // See parent class for more details
    //protected $identifier;              // See parent class for more details
    //protected $identifier_type;         // See parent class for more details
    //protected $timers;                  // See parent class for more details
    //protected $timer_array;             // See parent class for more details
    //protected $encryption_key;          // See parent class for more details
    //protected $company_id;              // See parent class for more details
    //protected $companyparent_id;        // See parent class for more details

    public function index($wf_name='')
    {
        try
        {
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "GET") throw new SecurityException("Unexpected request method.");

            // Init
            // Setup core properties on this class.
            $this->init('', GetSessionValue('companyparent_id'));

            // Properties
            // Set the global properties on this class based on the workflow name passed in.
            $this->setWorkflowProperties($wf_name, 'validate');

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if (!IsLoggedIn()) throw new SecurityException("You must be logged into access this function.");
            if (!IsAuthenticated("parent_company_write")) throw new SecurityException("Missing required permission.");

            // Navigation Check!
            // Make sure users don't jump forwards in a workflow.
            if (!IsWorkflowWaiting($this->wf_name, $this->wf_stepname, $this->identifier, $this->identifier_type)) throw new UIException("Workflow not ready.");




            // WIDGET: Show Upload Data Error Modal
            $show_error_widget = new UIWidget("data_error_widget");
            $show_error_widget->setBody( $this->_data_error_form(null, null) );
            $show_error_widget->setHref(base_url("wizard/widget/error"));
            $show_error_widget = $show_error_widget->render();

            // page_header
            // Define the HTML that forms the page header.
            $page_header = new UIFormHeader();
            $page_header->setTitle("File Corrections");
            $page_header = $page_header->render();

            $attributes = array();
            $attributes['save'] = base_url('parent/match/save');
            $attributes['identifier'] = GetSessionValue('companyparent_id');
            $attributes['identifier_type'] = "companyparent";

            $form = new UIWizardForm("wizard_correct_form");
            $form->addTopWizardButton($form->button("workflow_start_over_btn", "Start Over", " btn-wf-rollback btn-default pull-left m-l-0", false, array("href" => base_url("workflow/rollback/{$this->wf_name}"))));
            $form->addTopWizardButton($form->button("workflow_rematch_btn", "Match Columns", " btn-wf-moveto btn-default pull-left m-l-0", false, array("href" => base_url("workflow/moveto/{$this->wf_name}/parse"))));
            $form->addElement($form->top_buttons());
            $form = $form->render();

            // Read the error report from S3
            $client = S3GetClient();
            $prefix = GetS3Prefix('errors', $this->identifier, $this->identifier_type);
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
                if ( isset($detail['data']) )
                {
                    for($i=0;$i<count($detail['data']);$i++)
                    {
                        $errors[$key]['data'][$i] = A2PDecryptString(trim($detail['data'][$i]), $this->encryption_key) . PHP_EOL;
                    }
                }
            }

            // Figure out if the user said there were column headers or not.
            $has_headers = DoesUploadContainHeaderRow( null, $this->identifier );

            // Grab the orignal header/column mappings.
            $orig = GetPreferenceValue($this->identifier, $this->identifier_type, 'headers', 'user_names');
            $orig = json_decode($orig, true);
            $orig_lookup = array();
            if ( isset($orig["col_lookup"]) ) $orig_lookup = $orig['col_lookup'];



            // Read in the mapped columns
            $mapped_columns = $this->Mapping_model->get_mapped_columns($this->company_id, $this->companyparent_id   );
            $headers = array();
            $index = 0;
            foreach($mapped_columns as $mapped_column)
            {
                $column_name = getArrayStringValue("Value", $mapped_column);
                if( $column_name !== '' )
                {
                    // This is a mapped column.
                    $display = $this->Mapping_model->get_mapping_column_by_name($column_name, null, $this->identifier);
                    $column_no = $this->Mapping_model->get_mapped_column_no(null, $this->identifier, $column_name);
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
            $view_array = array_merge($view_array, array("href" => base_url("parent/correct/upload/error/ROW/COLUMN")));
            $view_array = array_merge($view_array, array("error_href" => base_url("parent/correct/upload/error/ROW/COLUMN")));
            $view_array = array_merge($view_array, array("identifier" => $this->identifier));
            $view_array = array_merge($view_array, array("identifier_type" => $this->identifier_type));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("companyparentcorrect/correct_js_assets")));
            $page_template = array_merge($page_template, array("view" => "correct/correct"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);
        } catch (UIException $e) {
            redirect(base_url("dashboard/parent"));
        } catch (SecurityException $e) {
            AccessDenied($e->getMessage());
        } catch (Exception $e) {
            Error404($e);
        }
    }

    public function render_data_error_form($row_no, $column_name) {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            // organize inputs.
            $companyparent_id = GetSessionValue("companyparent_id");

            // validate required inputs.
            if ( $companyparent_id == "" ) throw new Exception("Invalid input companyparent_id");
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


    private function _data_error_form( $row, $column ) {

        $identifier = GetSessionValue('companyparent_id');
        $identifier_type = 'companyparent';

        if ( getStringValue($row) == "" ) return "";
        if ( getStringValue($column) == "" ) return "";
        if ( $identifier == "" ) return "";

        $error_details = $this->Validation_model->get_validation_error($identifier, $identifier_type, $row, $column);

        $view_array = array();
        $view_array = array_merge( $view_array, array("row" => $row) );
        $view_array = array_merge( $view_array, array("column" => $column) );
        $view_array = array_merge( $view_array, array("details" => $error_details) );
        $view_array = array_merge( $view_array, array("identifier" => $identifier) );
        $view_array = array_merge( $view_array, array("identifier_type" => $identifier_type) );

        $form = new UIModalForm("wizard_error_form", "wizard_error_form", base_url("wizard/upload/error"));
        $form->setTitle("Error Information");
        $form->setCollapsable(true);
        $form->addElement($form->htmlView("correct/correct_data_cell", $view_array));
        $form->addElement($form->button("no_btn", "Okay", "btn-primary pull-right"));
        $form_html = $form->render();
        return $form_html;
    }

}
