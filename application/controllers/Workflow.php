<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Workflow extends SecureController {

    function __construct()
    {
        parent::__construct();
    }

    public function dashboard($workflow_name)
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");

            $wf = $this->Workflow_model->get_wf_by_name($workflow_name);
            $wf_id = GetArrayStringValue("Id", $wf);
            if ( $wf_id === '' ) throw new Exception("Unknown workflow.  Nothing to show.");


            // Collect the workflow data so we can build a workflow menu.
            $selected = array();
            $wf_menu = $this->Workflow_model->get_workflows();
            for($i=0;$i<count($wf_menu);$i++)
            {
                $item = $wf_menu[$i];
                $name = GetArrayStringValue('Name', $item);
                $wf_menu[$i]['Link'] = base_url("dashboard/workflow/{$name}");
                if ( $name === $workflow_name ) $selected = $item;
            }
            uasort($wf_menu, 'AssociativeArraySortFunction_Name');

            $workflow_steps = $this->_getWorkflowStepsForDisplay($workflow_name);

            // Collect the workflow properties.
            $workflow_properties = $this->_getWorkflowPropertiesForDisplay($wf_id);

            // Workflow Widget - sample
            $sample_widget = new UIWidget("wf_sample_widget");
            $sample_widget->setHref(base_url("widgettask/workflow/sample/168/company"));
            $sample_widget->setBody(WorkflowWidget('sample', '168', 'company'));
            $sample_widget = $sample_widget->render();

            // Draw the dashboard.
            $view_array = array();
            $view_array['workflow_name'] = $workflow_name;
            $view_array['workflow_steps'] = $workflow_steps;
            $view_array['workflow_properties'] = $workflow_properties;
            $view_array['wf_menu'] = $wf_menu;
            $view_array['selected'] = $selected;

            // This sample widget, if placed on page at the company level will allow you
            // to test the workflow.  Taking this off the support page, because it does not
            // work, by design, for the A2P company.
            //$view_array['sample_widget'] = $sample_widget;

            $page_template = array();
            $page_template = array_merge($page_template, array("view" => "workflows/workflow_viewer"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("workflows/workflow_viewer_js_assets")));


            RenderView('templates/template_body_default', $page_template);
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }

    public function rollback($wf_name, $wf_statename=null) {

        try
        {
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            // Collect our identifiers from the session.
            $identifier = GetSessionValue('company_id');
            $identifier_type = 'company';
            if ( $identifier === '' )
            {
                $identifier = GetSessionValue('companyparent_id');
                $identifier_type = 'companyparent';
            }

            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( $identifier === 'company' && ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");
            if ( $identifier === 'companyparent' && ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            // What file are we rolling back?
            $original_filename = GetWorkflowProgressProperty($identifier, $identifier_type, $wf_name, "OriginalFilename");

            // Reset Workflow
            WorkflowRollback( $identifier, $identifier_type, $wf_name, $wf_statename );

            // Audit this transaction.
            $payload = array();
            $payload['OriginalFilename'] = $original_filename;
            AuditIt('Start over.', $payload);

            // Decide where we should go next and return.
            if ( $wf_statename === '' )
            {
                // If we had a state name, attempt to move the waiting uri for that state.
                $uri = GetWorkflowStateProperty($wf_name, $wf_statename, 'WaitingURI');
                if ( $uri !== '' )
                {
                    $uri = "{$uri}/{$wf_name}";
                    AJAXSuccess("Moving to workflow state", base_url($uri));
                }
            }
            // If we get here, navigate to the dashboard.
            $uri = "dashboard";
            if ( $identifier_type === 'companyparent') $uri = 'dashboard/parent';
            AJAXSuccess("Moving to dashboard", base_url($uri));
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }



    public function moveto($wf_name, $wf_state_name) {

        try
        {
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            // Collect our identifiers from the session.
            $identifier = GetSessionValue('company_id');
            $identifier_type = 'company';
            if ( $identifier === '' )
            {
                $identifier = GetSessionValue('companyparent_id');
                $identifier_type = 'companyparent';
            }

            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( $identifier === 'company' && ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");
            if ( $identifier === 'companyparent' && ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            // If we can't fine the workflow, bail.
            $wf = WorkflowFind($wf_name);
            if ( empty($wf) ) throw new Exception("Unknown workflow.");

            // If we can't find the state, bail.
            $state = WorkflowStateFind($wf_name, 'Name', $wf_state_name);
            if ( empty($state) ) throw new Exception("Unknown workflow state.");



            // You can only move somewhere if there is a WaitingURI defined.
            $uri = GetWorkflowStateProperty($wf_name, $wf_state_name, 'WaitingURI');
            if ( $uri !== '' )
            {
                // Move to said workflow state.
                WorkflowMoveToState($identifier, $identifier_type, $wf_name, $wf_state_name);
                WorkflowStateSetWaiting($identifier, $identifier_type, $wf_name);

                $uri = "{$uri}/{$wf_name}";
            }
            else
            {
                // Yeah, don't know where to put them.  So, don't move and push them
                // to the dashboard.  That will lead them back to where they need to be.
                $uri = "dashboard";
                if ( $identifier_type === 'companyparent') $uri = 'dashboard/parent';
            }

            AJAXSuccess('moving!', base_url($uri));

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }

    private function _getWorkflowStepsForDisplay($workflow_name)
    {
        // WF_ID
        $wf = $this->Workflow_model->get_wf_by_name($workflow_name);
        $wf_id = GetArrayStringValue("Id", $wf);
        if ( $wf_id === '' ) throw new Exception("Unknown workflow.  Nothing to show.");


        $workflow_steps = array();
        $ordered_states = $this->Workflow_model->get_wf_states($wf_id);
        foreach($ordered_states as $step)
        {
            $state_name = GetArrayStringValue("Name", $step);

            $item = array();
            $item['step_id'] = getArrayStringValue("Id", $step);
            $item['step_name'] = getArrayStringValue("Name", $step);
            $item['step_description'] = getArrayStringValue("Description", $step);

            // Here are the descriptions for the known WorkflowStateProperties.
            $hints = [];
            $hints['Controller']        = 'This is the CLI controller class name that will catch requests to start a background task.  This controller extends the \'WorkflowBackgroundTaskController\' controller and lives in the controllers/cli folder.';
            $hints['WaitingURI']        = 'If a background task is unable to complete on it\'s own, it will throw a A2PWorkflowWaitingException.  When this happens, the user will be directed to new screen.  This property outlines the route to the screen that will be shown when the background task is unable to complete and has entered the waiting state.';
            $hints['VerbiageGroup']     = 'As a background task is processing, it will status events.  Each of those status events contain a verbiage code indicating which status it is in.  This property outlines the verbiage group that contains all possible status messages the background task can generate.';
            $hints['Library']           = 'This is the class name of background task.  This class extends the \'WorkflowLibrary\' class and lives in the libraries/workflow folder.';

            // Add all properties defined in the database.
            $state_properties = array();
            $results = $this->Workflow_model->get_wf_state_properties($wf_id, getArrayStringValue("Id", $step));
            foreach($results as $state_property)
            {
                $state_name = GetArrayStringValue("Name", $step);
                $property_name = GetArrayStringValue("Name", $state_property);

                $property = array();
                $property['name'] = $property_name;
                $property['value'] = GetWorkflowStateProperty($workflow_name,$state_name, $property_name);
                $property['desc'] = GetArrayStringValue($property['name'], $hints);
                $state_properties[] = $property;
            }

            // If the user did not define one of our known properties, add it in.
            foreach($hints as $key=>$description)
            {
                $index = ArrayMultiSearchIndexOf('name', $key, $state_properties);
                if ( $index === FALSE )
                {
                    $p = array();
                    $p['name'] = GetStringValue($key);
                    $p['value'] = GetWorkflowStateProperty($workflow_name,$state_name, $key);
                    $p['desc'] = GetStringValue($description);
                    $state_properties[] = $p;
                }
            }

            // Sort the item's properties.
            uasort($state_properties, 'AssociativeArraySortFunction_Name_lowercase');
            $item['properties'] = $state_properties;


            // Attach the properties to the workflow step.
            $workflow_steps[] = $item;
        }

        return $workflow_steps;
    }
    private function _getWorkflowPropertiesForDisplay($wf_id)
    {

        $wf = $this->Workflow_model->get_wf_by_id($wf_id);
        $workflow_name = GetArrayStringValue("Name", $wf);
        if ( $workflow_name === '' ) return array();

        // Here are the UI property descriptions for the known workflow properties.
        $hints = array();
        $hints['WidgetJSLibrary'] = "Name of the workflow widget javascript library, sourced from assets/&lt;workflow_name&gt;.";
        $hints['IdentifierType'] = "Specifies what entity the workflow is operating against.  Possible values are company or companyparent.";
        $hints['WidgetRefreshCallback'] = "Javascript function called when the workflow widget is refreshed.";
        $hints['WidgetName'] = "Name of the UI widget that allows you to interact with the workflow.";
        $hints['LandingURI'] = "Workflow waiting steps will redirect to this route while the background task is running.";

        // Add all properties defined in the database.
        $workflow_properties = array();
        $results = $this->Workflow_model->get_wf_properties($wf_id);
        foreach($results as $workflow_property) {
            $item = array();
            $item['Name'] = GetArrayStringValue("Name", $workflow_property);
            $item['Value'] = GetArrayStringValue("Value", $workflow_property);
            $item['Description'] = GetArrayStringValue($item['Name'], $hints);

            // If the value was not set in the database OR the set it as blank, then we need
            // to get the default value if there is one.
            if ( GetArrayStringValue('Value', $item) === '' )
            {
                $item['Value'] = GetWorkflowProperty($workflow_name, GetArrayStringValue('Name', $item));
            }

            $workflow_properties[] = $item;
        }

        // If the user did not define one of our known properties, add it in.
        foreach($hints as $key=>$description)
        {
            $index = ArrayMultiSearchIndexOf('Name', $key, $workflow_properties);
            if ( $index === FALSE )
            {
                $item = array();
                $item['Name'] = GetStringValue($key);
                $item['Value'] = GetWorkflowProperty($workflow_name, $key);
                $item['Description'] = GetStringValue($description);
                $workflow_properties[] = $item;
            }
        }

        // Sort the result alphabetically by key.
        uasort($workflow_properties, 'AssociativeArraySortFunction_Name');

        return $workflow_properties;
    }

}
