<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SampleWaiting extends A2PWorkflowStepController
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


    public function index($wf_name, $wf_stepname) {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Init
            // initialize this screen as a company or companyparent screen by passing in
            // the identifier for the entity type that will be using this wait screen.
            $this->init(GetSessionValue('company_id'), '');

            // Properties
            // Set the global properties on this class based on the workflow name passed in.
            $this->setWorkflowProperties($wf_name, $wf_stepname);

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            // FIXME: Add any additional security checks that are needed.
            //if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");
            //if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            // Navigation Check!
            // Make sure users don't jump forwards in a workflow.
            if ( ! IsWorkflowWaiting( $this->wf_name, $this->wf_stepname, $this->identifier, $this->identifier_type) ) throw new UIException("Workflow not ready.");


            // BUSINESS LOGIC
            // Write your custom junk here for the page.


            $page_header = new UIFormHeader();
            $page_header->setTitle("Sample Workflow Waiting Screen");
            $page_header = $page_header->render();

            // Generate the form for this workflow step.
            $wform = new UIWizardForm("sample_waiting_form");
            $wform->setAction(base_url("waitforit/continue"));              // This is where the continue button will post.

            // Add workflow navigation buttons to the wizard form
            $wform->addTopWizardButton($wform->button("complete_button", "Continue", "btn-primary", true));
            $wform->addTopWizardButton($wform->button("workflow_start_over_btn", "Start Over", " btn-wf-rollback btn-default pull-left m-l-0", false, array("href" => base_url("workflow/rollback/{$this->wf_name}"))));

            // MOVE-TO BUTTONS
            // For this example, allow the user to move backwards in the workflow to every step
            // before this one on this waiting page.  In reality, you could remove the ones that
            // skip programmatically or you could just add the ones you want.
            $wf = $this->Workflow_model->get_wf_by_name($this->wf_name);
            $wf_id = GetArrayStringValue("Id", $wf);
            $steps = $this->Workflow_model->get_wf_states($wf_id);
            foreach($steps as $step)
            {

                $name = GetArrayStringValue('Name', $step);
                $uri = GetWorkflowStateProperty($wf_name, $name, 'WaitingURI');
                if( $name === $wf_stepname )
                {
                    break;
                }

                // You can only move to a state that has a waiting uri defined.
                if ( $uri !== '' ) $wform->addTopWizardButton($wform->button("workflow_{$name}_btn", $name, " btn-wf-moveto btn-default pull-left m-l-0", false, array("href" => base_url("workflow/moveto/{$wf_name}/{$name}"))));
            }


            $wform->addElement($wform->top_buttons());
            $wform->addElement($wform->hiddenInput("wf_name", $this->wf_name));
            $wform->addElement($wform->hiddenInput("wf_stepname", $this->wf_stepname));
            $wform = $wform->render();

            $view_array = array();
            $view_array = array_merge($view_array, array("page_header" => $page_header));
            $view_array = array_merge($view_array, array("validation_form" => $wform));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString('sample/sample_js_assets')));
            $page_template = array_merge($page_template, array("view" => "sample/start"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch ( UIException $e ) { redirect(base_url("dashboard")); }
        catch( SecurityException $e ) { AccessDenied($e->getMessage()); }
        catch( Exception $e ) { Error404( $e ); }
    }
    public function validate() {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            // FIXME: Add any additional security checks that are needed.
            //if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");
            //if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            // Collect interesting post data.
            $wf_name = GetArrayStringValue('wf_name', $_POST);
            $wf_stepname = GetArrayStringValue('wf_stepname', $_POST);

            // Validate the post data.
            if ( GetStringValue($wf_name) === '' ) throw new Exception("Missing required input: wf_name");
            if ( GetStringValue($wf_stepname) === '' ) throw new Exception("Missing required input: wf_stepname");

            // Properties
            // Set the global properties on this class based on the workflow name passed in.
            $this->setWorkflowProperties($wf_name, $wf_stepname);

            // Business logic.  Here I just set an app options so the
            // background task knows I continued and we can move forward.
            SetAppOption('WaitForIt', 'continue');
            //throw new UIException("No, that didn't work.");

            // SNAPSHOT
            // Take a snapshot of the data as it stands so we know what they elected.
            $this->takeSnapshot();

            // CASE1: I can just move forward because we collected the data
            //WorkflowStateMoveForward($this->identifier, $this->identifier_type, $wf_name);
            //WorkflowStartBackgroundJob($this->identifier, $this->identifier_type, $wf_name, GetSessionValue('user_id'));
            //AJAXSuccess("Good news Everybody!   ", base_url('dashboard') );

            // CASE2: I need to 'rerun' the task to complete the
            WorkflowStateRetry( $this->identifier, $this->identifier_type, $wf_name );


            // Head on back to the landing location so the user can monitor the workflow progress.
            $landing = GetWorkflowProperty($wf_name, 'LandingURI');
            AJAXSuccess("Good news Everybody!   ", base_url($landing) );

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }



}
