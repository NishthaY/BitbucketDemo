<?php
function GetWorkflowProgressByIdentifier($identifier, $identifier_type)
{
    $CI = &get_instance();
    return $CI->Workflow_model->get_wf_progress_by_identifier( $identifier, $identifier_type );
}
function GetWorkflowProgressProperty($identifier, $identifier_type, $workflow_name, $property_name)
{
    $CI = &get_instance();

    if ( GetStringValue($workflow_name) === '' )
    {
        // If the use did not supply us with a workflow_name, help them out by looking for the active
        // workflow for the identifier.  If we find one, then pull the name from that.
        $progress = GetWorkflowProgressByIdentifier($identifier, $identifier_type);
        if ( ! empty($progress) )
        {
            if ( empty($progress) ) $workflow_name = "";
            if ( !empty($property_name) ) $workflow_name = GetArrayStringValue("WorkflowName", $progress[0]);
        }
    }

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return false;

    $results = $CI->Workflow_model->select_wf_progress_property($wf_id, $identifier, $identifier_type, $property_name);
    if ( empty($results) ) return "";
    return GetArrayStringValue('Value', $results);

}
function SetWorkflowProgressProperty( $identifier, $identifier_type, $workflow_name, $property_name, $property_value)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return false;

    $CI->Workflow_model->upsert_wf_progress_property($wf_id, $identifier, $identifier_type, $property_name, $property_value);
}
function UpdateWorkflowProgressProperty($identifier, $identifier_type, $workflow_name, $property_name, $property_value )
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return false;

    $CI->Workflow_model->update_wf_progress_property($wf_id, $identifier, $identifier_type, $property_name, $property_value);
}
function InsertWorkflowProgressProperty($identifier, $identifier_type, $workflow_name, $property_name, $property_value )
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return false;

    $CI->Workflow_model->insert_wf_progress_property($wf_id, $identifier, $identifier_type, $property_name, $property_value);
}
function DeleteWorkflowProgressProperty( $identifier, $identifier_type, $workflow_name, $property_name=null)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return false;

    if ( GetStringValue($property_name) === '' )
    {
        // Delete all properties
        $CI->Workflow_model->delete_all_wf_progress_property($wf_id, $identifier, $identifier_type, $property_name);
    }
    else
    {
        // Delete the specific property by name.
        $CI->Workflow_model->delete_wf_progress_property($wf_id, $identifier, $identifier_type, $property_name);
    }
}

/**
 * WorkflowJobStarting
 *
 * This is the hook function for when a background job is starting.
 * Anything we need to do in this transition phase is done in this function.
 *
 * - Note the job is running
 * - Tell the widget to refresh
 *
 * @param $job_id
 */
function WorkflowJobStarting( $job_id )
{
    $CI = &get_instance();

    $progress = $CI->Workflow_model->get_wf_by_job_id($job_id);
    if ( empty($progress) ) return;

    $wf_name = GetArrayStringValue('WorkflowName', $progress[0]);
    $identifier = GetArrayIntValue('Identifier', $progress[0]);
    $identifier_type = GetArrayStringValue('IdentifierType', $progress[0]);

    // RUNNING
    // Tell the workflow that we are running the current state.
    WorkflowStateSetRunning($identifier, $identifier_type, $wf_name);

    // Notify the workflow widget associated with this workflow that it's time to refresh.
    NotifyWorkflowWidgetRefresh($identifier, $identifier_type, $wf_name);
}

/**
 * WorkflowJobStopping
 *
 * This is the hook function for when a background job is stopping.
 * Anything we need to do in this transition phase is done in this function.
 *
 * @param $job_id
 * @param string $output
 */
function WorkflowJobStopping( $job_id, $output='' )
{
    $CI = &get_instance();

    $progress = $CI->Workflow_model->get_wf_by_job_id($job_id);
    if ( empty($progress) ) return;

    $job = $CI->Queue_model->get_job($job_id);
    $payload = GetArrayStringValue('Payload', $job);
    $payload = json_decode($payload, true);
    $user_id = GetArrayIntValue('0', $payload);

    $wf_name = GetArrayStringValue('WorkflowName', $progress[0]);
    $identifier = GetArrayIntValue('Identifier', $progress[0]);
    $identifier_type = GetArrayStringValue('IdentifierType', $progress[0]);

    WorkflowJobComplete( $wf_name, $identifier, $identifier_type, $output, $user_id );
}

/**
 * WorkflowJobComplete
 *
 * This function is executed once the workflow job has completed it's transition
 * from running to stopped.  This is responsible for reviewing what happened and then
 * deciding what to do next in the workflow such as continue, end or fail.
 *
 * @param $workflow_name
 * @param $identifier
 * @param $identifier_type
 * @param string $output
 * @param null $user_id
 */
function WorkflowJobComplete( $workflow_name, $identifier, $identifier_type, $output='', $user_id=null )
{
    $CI = &get_instance();

    // Construct the company_id and companyparent_id from the identifier.
    $company_id = null;
    $companyparent_id = null;
    if ( $identifier_type === 'company' ) $company_id = $identifier;
    if ( $identifier_type === 'companyparent' ) $companyparent_id = $identifier;

    // If we don't have a user_id, try and get it from the session.
    if( GetStringValue($user_id) === '' ) $user_id = GetSessionValue('user_id');

    // FAILED
    // If we have any output, we need to stop.  That indicates a critical runtime failure.
    if ( trim($output) !== '' )
    {
        WorkflowStateSetFailed($identifier, $identifier_type, $workflow_name);
        NotifyWorkflowWidgetRefresh($identifier, $identifier_type, $workflow_name);
        NotifyStepComplete($company_id, $companyparent_id);

        // Okay, this is bad.  We did not complete the workflow successfully,
        // but we are done!  Report that so the UI will refresh.
        NotifyWizardComplete($company_id, $companyparent_id);

        return;
    }

    // WAITING
    // If we were unable to complete the step, then just issue a refresh to the
    // widget and be done.
    if ( IsWorkflowStateWaiting($identifier, $identifier_type, $workflow_name) )
    {
        NotifyWorkflowWidgetRefresh($identifier, $identifier_type, $workflow_name);
        return;
    }

    // Collect details about our current state.
    $state = WorkflowStateGetCurrentState($identifier, $identifier_type, $workflow_name);
    $next_state_id = GetArrayStringValue("NextStateId", $state);

    // Mark this step completed, then move forward.
    WorkflowStateMoveForward($identifier, $identifier_type, $workflow_name);

    if ( $next_state_id === '' )
    {
        // Shutdown the workflow;
        NotifyStepComplete($company_id, $companyparent_id);
        WorkflowDelete($identifier, $identifier_type, $workflow_name);
        NotifyWizardComplete($company_id, $companyparent_id);
    }
    else if ( IsWorkflowStateSkippable($workflow_name, $identifier, $identifier_type, $user_id) )
    {
        // The step we just moved to can be skipped, so mark this guy done and move on.
        WorkflowStateSetCompleted($identifier, $identifier_type, $workflow_name);
        WorkflowJobComplete($workflow_name, $identifier, $identifier_type, '', $user_id);
    }
    else
    {
        // Move forward in the workflow.
        NotifyWorkflowWidgetRefresh($identifier, $identifier_type, $workflow_name);
        NotifyStepComplete($company_id, $companyparent_id);
        WorkflowStartBackgroundJob($identifier, $identifier_type, $workflow_name, $user_id);
    }
}

/**
 * IsWorkflowStateSkippable
 *
 * This function will check to see if the current workflow step is skippable or
 * not.  This is determined by the "skip" function defined on each background task.
 * If the task reports the step is skippable, this function will return TRUE, else
 * FALSE.
 * 
 * @param $workflow_name
 * @param $identifier
 * @param $identifier_type
 * @param $user_id
 * @return bool
 */
function IsWorkflowStateSkippable($workflow_name, $identifier, $identifier_type, $user_id)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return false;

    $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
    $state_name = GetArrayStringValue('Name', $current_state);

    $task_name = GetWorkflowStateProperty($workflow_name, $state_name, 'Library');
    if ( file_exists(APPPATH."libraries/workflow/{$task_name}.php") )
    {
        $CI->load->library("workflow/{$task_name}");
        $task = new $task_name();
        $task->identifier = $identifier;
        $task->identifier_type = $identifier_type;
        $task->user_id = $user_id;
        $task->wf_name = $workflow_name;
        $task->wf_stepname = $state_name;

        // Returns TRUE if we should skip this step.
        return $task->skip();
    }

    // Never skip a step unless the background task
    // indicates the step has already been completed.
    return FALSE;
}

function GetWorkflowProperty($workflow_name, $property_name)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return false;


    // Default the WidgetJSLibrary property if not set.
    if ( $property_name === 'WidgetJSLibrary' )
    {
        // Use the javascript library defined for this workflow in the WorkflowPropertes table.
        // If we can't find the property, or if the property does not reference a javascript file
        // point it to our default version.

        $wf_jslibrary = $CI->Workflow_model->get_wf_property($wf_id, $property_name);
        if( empty($wf_jslibrary) ) $wf_jslibrary = "../widget.js";
        if ( ! file_exists(APPPATH . "../assets/custom/js/workflows/{$workflow_name}/{$wf_jslibrary}") ) $wf_jslibrary = "../widget.js";
        return $wf_jslibrary;
    }

    // Default the LandingURI if not set.
    if ( $property_name === 'LandingURI' )
    {
        $wf_landinguri = $CI->Workflow_model->get_wf_property($wf_id, $property_name);
        if ( empty($wf_landinguri) )
        {
            $identifier_type = $CI->Workflow_model->get_wf_property($wf_id, 'IdentifierType');
            if( $identifier_type === 'company' ) $wf_landinguri = 'dashboard';
            if( $identifier_type === 'companyparent' ) $wf_landinguri = 'dashboard/parent';
        }
        return $wf_landinguri;
    }

    // Return what ever property we have on hand.
    return $CI->Workflow_model->get_wf_property($wf_id, $property_name);

}
function GetWorkflowProgressByJobId($job_id)
{
    $CI = &get_instance();

    $results = $CI->Workflow_model->get_wf_by_job_id($job_id);
    return $results;
}
function GetWorkflowStateProperty($workflow_name, $state_name, $property_name )
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return "";

    $state = $CI->Workflow_model->get_wf_state_by_name( $wf_id, $state_name);
    $value = GetArrayStringValue($property_name, $state);

    // Default the Controller property
    if ( $property_name === 'Controller' && $value === '' )
    {
        // Construct the default controller name, since one was not provided.
        // example: workflow_name=wf_first, state_name=step_one --> WfFirstStepOne
        $state = $CI->Workflow_model->get_wf_state_by_name($wf_id, $state_name);
        $state_name = GetArrayStringValue('Name', $state);

        $part1 = replaceFor($workflow_name, '_', ' ');
        $part1 = ucwords($part1);
        $part1 = replaceFor($part1, ' ', '');

        $part2 = replaceFor($state_name, '_', ' ');
        $part2 = ucwords($part2);
        $part2 = replaceFor($part2, ' ', '');

        $value = "{$part1}{$part2}";
    }

    // Default the Library property
    if ( $property_name === 'Library' && $value === '' )
    {
        $value = 'Sleep';
    }

    return $value;


}
function HasWorkflowStarted( $identifier, $identifier_type, $workflow_name )
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return FALSE;

    // CURRENT_STATE
    $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
    if ( empty($current_state) ) return FALSE;

    return true;

}
function IsWorkflowStateFailed( $identifier, $identifier_type, $workflow_name )
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return false;

    $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
    if ( empty($current_state) ) return FALSE;

    $failed = GetArrayStringValue('Failed', $current_state);
    if ( $failed === 'TRUE' ) return true;
    if ( $failed === 't' ) return true;
    return false;

}
function IsWorkflowWaiting($workflow_name, $workflow_state, $identifier, $identifier_type)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return false;

    // The current state of the workflow must match the state passsed in.
    $current_state = WorkflowStateGetCurrentState( $identifier, $identifier_type, $workflow_name);
    if ( GetArrayStringValue('Name', $current_state) !== $workflow_state ) return false;

    // The current state must be waiting.
    if ( ! IsWorkflowStateWaiting($identifier,$identifier_type, $workflow_name) ) return false;

    // All prior states must be complete.
    $states = $CI->Workflow_model->get_wf_states_with_progress($wf_id, $identifier, $identifier_type);
    foreach($states as $state)
    {
        // As soon as we find our current state, we know we are good to go.
        $state_name = GetArrayStringValue("Name", $state);
        if ( $state_name === $workflow_state ) return true;

        // Anytime we find a "running" or "waiting" prior to our current state, we are bad.
        if ( GetArrayStringValue('Running', $state) === 't' ) return false;
        if ( GetArrayStringValue('Waiting', $state) === 't' ) return false;

    }
    return false;
}
function IsWorkflowStateWaiting($identifier, $identifier_type, $workflow_name)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;

    // If we have not started the workflow, start it.
    $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
    if ( GetArrayStringValue('Waiting', $current_state) === 't' ) return true;
    return false;
}

function WorkflowDelete( $identifier, $identifier_type, $workflow_name )
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;

    $CI->Workflow_model->delete_wf_progress_all_states($identifier, $identifier_type, $wf_id);

}
function WorkflowFailedBackgroundJob( $job_id )
{
    // This job has failed!
    $CI = &get_instance();


    // Find the job that has failed.
    $job = $CI->Queue_model->get_job($job_id);
    if ( empty($job) ) return;

    // Extract the job payload and parse the values.
    $payload = GetArrayStringValue('Payload', $job);
    $payload = json_decode($payload, true);
    $user_id = GetArrayStringValue("0", $payload);
    $company_id = GetArrayStringValue("1", $payload);
    $companyparent_id = GetArrayStringValue("2", $payload);

    // Calculate the identifier for this job.
    $identifier = $company_id;
    $identifier_type = 'company';
    if ( $company_id === '' )
    {
        $identifier = $companyparent_id;
        $identifier_type = 'companyparent';
    }


    // Grab the most recent progress associated with the job_id.
    $progress = $CI->Workflow_model->get_wf_by_job_id( $job_id );
    if ( empty($progress) ) return;

    // Walk that progress from start to finish.  Once we find the state that
    // matches the failed job, we will mark the state failed.  All subsequent
    // steps will be removed from the progress list as we will never make it there.
    $controller = GetArrayStringValue('Controller', $job );
    foreach($progress as $item)
    {
        $progress_id = GetArrayStringValue('Id', $item);
        $workflow_name = GetArrayStringValue('WorkflowName', $item);
        $state_name = GetArrayStringValue('WorkflowStateName', $item);
        $state_background_controller = GetWorkflowStateProperty($workflow_name, $state_name, 'Controller');
        if ( $state_background_controller !== '' )
        {
            if ( $state_background_controller === $controller )
            {
                WorkflowStatePrune($identifier, $identifier_type, $workflow_name, $state_name);
                WorkflowStateSetFailed($identifier, $identifier_type, $workflow_name);
                break;
            }
        }
    }

}
function WorkflowStatePrune( $identifier, $identifier_type, $workflow_name, $state_name )
{
    $CI = &get_instance();

    if ( GetStringValue($identifier) === '' ) return;
    if ( GetStringValue($identifier_type) === '' ) return;
    if ( GetStringValue($workflow_name) === '' ) return;
    if ( GetStringValue($state_name) === '' ) return;

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;

    $prune = false;
    $progress = $CI->Workflow_model->select_wf_progress($identifier, $identifier_type, $wf_id);
    foreach($progress as $item)
    {
        $id = GetArrayStringValue('Id', $item);
        $item_state_name = GetArrayStringValue('WorkflowStateName', $item);
        if ( $state_name == $item_state_name ) $prune = true;

        if ( $prune )
        {
            $CI->Workflow_model->delete_workflow_progress_by_id($id);
            continue;
        }
    }
}
function WorkflowFind($workflow_name)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return array();
    return $wf;

}
function WorkflowMoveToState( $identifier, $identifier_type, $workflow_name, $state_name )
{
    // move to next state and mark curren state done.

    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;

    // TARGET WORKFLOW STATE
    $target_state = $CI->Workflow_model->get_wf_state_by_name($wf_id, $state_name);
    if ( empty($target_state) ) return;

    // BUILD WORKFLOW
    // Loop through the workflow.  Each state before the target state will be marked as
    // completed.  The target state will be added to the workflow but not yet started.
    // Any states after the target workflow state will be removed.
    $found_it = false;
    $states = $CI->Workflow_model->get_wf_states($wf_id);
    foreach($states as $state)
    {
        $state_id = GetArrayStringValue("Id", $state);

        // FOUND IT!
        if ( $state_name === GetArrayStringValue("Name", $state) )
        {
            $found_it = true;

            // Create the state if it does not exist.
            $exists = $CI->Workflow_model->exists_wf_progress($identifier, $identifier_type, $wf_id, $state_id);
            if ( ! $exists )
            {
                $CI->Workflow_model->insert_wf_progress( $identifier, $identifier_type, $wf_id, $state_id );
            }

            // Set the Running and Complete flags to FALSE
            $CI->Workflow_model->update_workflow_state_running($identifier, $identifier_type, $wf_id, $state_id, false);
            $CI->Workflow_model->update_workflow_state_complete($identifier, $identifier_type, $wf_id, $state_id, false);
            $CI->Workflow_model->update_workflow_state_waiting($identifier, $identifier_type, $wf_id, $state_id, false);
            continue;
        }

        // This state is BEFORE the target state.  Mark this as complete.
        if ( ! $found_it )
        {
            $exists = $CI->Workflow_model->exists_wf_progress($identifier, $identifier_type, $wf_id, $state_id);
            if ( ! $exists )
            {
                $CI->Workflow_model->insert_wf_progress( $identifier, $identifier_type, $wf_id, $state_id );
            }

            // Set our activity flags for the state.
            $CI->Workflow_model->update_workflow_state_running($identifier, $identifier_type, $wf_id, $state_id, false);
            $CI->Workflow_model->update_workflow_state_complete($identifier, $identifier_type, $wf_id, $state_id);
            $CI->Workflow_model->update_workflow_state_waiting($identifier, $identifier_type, $wf_id, $state_id, false);
            continue;
        }

        // This state is AFTER the target state.  Remove it.
        if ( $found_it )
        {
            $exists = $CI->Workflow_model->exists_wf_progress($identifier, $identifier_type, $wf_id, $state_id);
            if ( $exists )
            {
                $CI->Workflow_model->delete_wf_progress_by_state( $identifier, $identifier_type, $wf_id, $state_id );
            }
            continue;
        }
    }

}

/**
 * WorkflowStart
 *
 * This function will start a workflow.  If the workflow specified is
 * not already processing, it will locate the first state in the workflow
 * and add it to the WorkflowProgress table triggering the 'start' phase.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $workflow_name
 * @throws Exception
 */
function WorkflowStart($identifier, $identifier_type, $workflow_name)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;

    // If we have not started the workflow, start it.
    $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
    if ( empty($current_state) )
    {
        // Remove any progress properties that might have been a hold over from the last run.
        $CI->Workflow_model->delete_all_wf_progress_property( $wf_id, $identifier, $identifier_type );

        // Create a support tag for this workflow run.
        $support_tag = new DateTime();
        $support_tag = $support_tag->format('YmdHis');    // CCYYMMHHMMSS
        SetWorkflowProgressProperty($identifier, $identifier_type, $workflow_name, 'SupportTag', $support_tag);

        // Insert a 'recent activity' tag now, so we can just update it later.
        InsertWorkflowProgressProperty($identifier,$identifier_type,$workflow_name,'recent_activity', '');

        // Get the start state_id for the starting state of the workflow.
        $start = $CI->Workflow_model->get_wf_start_state($wf_id);
        $state_id = GetArrayStringValue("Id", $start);

        // Insert the state into the progress table.
        $CI->Workflow_model->insert_wf_progress( $identifier, $identifier_type, $wf_id, $state_id );

        // Check to see if the first step in the workflow is skippable or not.  If it is, then
        // just mark the job complete so we start moving forward right away.
        $user_id = GetSessionValue('user_id');
        if ( IsWorkflowStateSkippable($workflow_name, $identifier, $identifier_type, $user_id) )
        {
            WorkflowStateSetCompleted($identifier, $identifier_type, $workflow_name);
            WorkflowJobComplete($workflow_name, $identifier, $identifier_type, '', $user_id);
        }
    }
}

/**
 * WorkflowStartBackgroundJob
 *
 * This function will locate all the materials needed to run a background task
 * for the current workflow step.  If the step is not already running a background
 * task, it will add the task to the "ProcessQueue" table to be worked at the next
 * available time slot.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $workflow_name
 * @param $user_id
 * @param string $group_id
 * @return bool|void
 */
function WorkflowStartBackgroundJob($identifier, $identifier_type, $workflow_name, $user_id, $group_id='')
{
    $CI = &get_instance();


    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;

    // CURRENT STATE
    $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
    $state_name = GetArrayStringValue('Name', $current_state);

    // WORKFLOW STATE CONTROLLER
    $controller = GetWorkflowStateProperty($workflow_name, $state_name, 'Controller');
    if( $controller === '' ) $controller = GetWorkflowProperty($workflow_name, $state_name, 'Controller');

    // At this point, the controller had better exist else we can't do anything.
    $file = APPPATH . "controllers/cli/{$controller}.php";
    if ( ! file_exists( $file) )
    {
        print "Unable to find the CLI class [{$controller}].  Please create this class or update the 'Controller' property in the WorkflowStateProperty table with the correct value.";
        LogIt("WARNING", "Workflow helper cannot find the cli/{$controller} file.");
        exit;
    }


    if ( GetArrayStringValue("Running", $current_state) === 't' ) return;
    if ( GetArrayStringValue("Complete", $current_state) === 't' ) return true;

    // Calculate our "ids" based on the identifier provided.
    $company_id = "";
    $companyparent_id = "";
    if ( $identifier_type === 'company' ) {
        $company_id = $identifier;
        $companyparent_id = GetCompanyParentId($company_id);
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $companyparent_id = $identifier;
    }

    // SIMILAR JOB RUNNING.
    // If we are already running this job, don't start it.\
    $is_already_running = $CI->Queue_model->is_similar_job_pending_or_running($controller,"index");
    if ( $is_already_running ) return;

    // START THE BACKGROUND JOB
    if ($group_id !== '')
    {
        $CI->Queue_model->add_grouped_worker_job($companyparent_id, $company_id, $user_id, $group_id, $controller, "index", 'now + 5 seconds');
    }
    else
    {
        $CI->Queue_model->add_worker_job($companyparent_id, $company_id, $user_id, $controller, "index", 'now + 5 seconds');
    }
}
function WorkflowStateFind($workflow_name, $key, $value)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return array();

    // Get all of the workflow states
    $wf_states = $CI->Workflow_model->get_wf_states($wf_id);

    // Walk through them looking for a key and value match.
    // If you find one, return it.  This will always return
    // the first one if there are duplicates.
    $search = array();
    foreach($wf_states as $state)
    {
        if ( isset($state[$key] ) )
        {
            if ( GetArrayStringValue($key, $state) === GetStringValue($value) )
            {
                $search = $state;
                break;
            }
        }
    }
    return $search;
}
function WorkflowStateGetCurrentState($identifier, $identifier_type, $workflow_name)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;

    // If we have not started the workflow, start it.
    $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
    return $current_state;
}
function WorkflowStateMoveForward($identifier, $identifier_type, $workflow_name)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;

    // CURRENT_STATE
    $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
    if ( empty($current_state) ) return;

    // NEXT_STATE
    $next_state_id = GetArrayStringValue("NextStateId", $current_state);
    if ( $next_state_id === '' ) return;
    $next_state = $CI->Workflow_model->get_wf_state_by_id( $wf_id, $next_state_id );
    if ( empty($next_state) ) return;
    $next_state_name = GetArrayStringValue("Name", $next_state);

    // MOVE IT
    WorkflowMoveToState($identifier, $identifier_type, $workflow_name, $next_state_name);
}

function WorkflowStateRetry( $identifier, $identifier_type, $workflow_name )
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ($wf_id === '') return;

    if (IsWorkflowStateWaiting($identifier, $identifier_type, $workflow_name))
    {
        // CURRENT_STATE
        $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
        if (empty($current_state)) return false;
        $state_id = GetArrayStringValue('Id', $current_state);

        $CI->Workflow_model->update_workflow_state_waiting($identifier, $identifier_type, $wf_id, $state_id, false);
        WorkflowStartBackgroundJob($identifier, $identifier_type, $workflow_name, GetSessionValue('user_id'));
        return true;
    }
    return false;


}
function WorkflowStateSetCompleted($identifier, $identifier_type, $workflow_name)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;


    // CURRENT STATE
    // Grab the current state and then take action on it.
    $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
    if ( ! empty($current_state) )
    {
        $state_id = GetArrayStringValue("Id", $current_state);
        $CI->Workflow_model->update_workflow_state_running($identifier, $identifier_type, $wf_id, $state_id, true);
        $CI->Workflow_model->update_workflow_state_complete($identifier, $identifier_type, $wf_id, $state_id, true);
        $CI->Workflow_model->update_workflow_state_waiting($identifier, $identifier_type, $wf_id, $state_id, false);
    }
}
function WorkflowStateSetFailed($identifier, $identifier_type, $workflow_name, $state_name="")
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;



    if ( $state_name === '' )
    {
        // CURRENT STATE
        // Grab the current state and then take action on it.
        $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
        if ( ! empty($current_state) )
        {
            $state_id = GetArrayStringValue("Id", $current_state);
            $CI->Workflow_model->update_workflow_state_running($identifier, $identifier_type, $wf_id, $state_id, false);
            $CI->Workflow_model->update_workflow_state_complete($identifier, $identifier_type, $wf_id, $state_id, false);
            $CI->Workflow_model->update_workflow_state_waiting($identifier, $identifier_type, $wf_id, $state_id, false);
            $CI->Workflow_model->update_workflow_state_failed($identifier, $identifier_type, $wf_id, $state_id, true);
        }
    }
    else
    {
        // SPECIFIED STATE
        // Find the state specified and mark it failed.
        $state = $CI->Workflow_model->get_wf_state_by_name($wf_id, $state_name);
        $state_id = GetArrayStringValue('Id', $state);

        $CI->Workflow_model->update_workflow_state_running($identifier, $identifier_type, $wf_id, $state_id, false);
        $CI->Workflow_model->update_workflow_state_complete($identifier, $identifier_type, $wf_id, $state_id, false);
        $CI->Workflow_model->update_workflow_state_waiting($identifier, $identifier_type, $wf_id, $state_id, false);
        $CI->Workflow_model->update_workflow_state_failed($identifier, $identifier_type, $wf_id, $state_id, true);
    }
}
function WorkflowStateSetRunning($identifier, $identifier_type, $workflow_name)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;


    // CURRENT STATE
    // Grab the current state and then take action on it.
    $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
    if ( ! empty($current_state) )
    {
        $state_id = GetArrayStringValue("Id", $current_state);
        $CI->Workflow_model->update_workflow_state_running($identifier, $identifier_type, $wf_id, $state_id, true);
        $CI->Workflow_model->update_workflow_state_complete($identifier, $identifier_type, $wf_id, $state_id, false);
        $CI->Workflow_model->update_workflow_state_waiting($identifier, $identifier_type, $wf_id, $state_id, false);
    }
}
function WorkflowStateSetWaiting($identifier, $identifier_type, $workflow_name)
{
    $CI = &get_instance();

    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;


    // CURRENT STATE
    // Grab the current state and then take action on it.
    $current_state = $CI->Workflow_model->get_wf_current_state($identifier, $identifier_type, $wf_id);
    if ( ! empty($current_state) )
    {
        $state_id = GetArrayStringValue("Id", $current_state);
        $CI->Workflow_model->update_workflow_state_running($identifier, $identifier_type, $wf_id, $state_id, false);
        $CI->Workflow_model->update_workflow_state_complete($identifier, $identifier_type, $wf_id, $state_id, false);
        $CI->Workflow_model->update_workflow_state_waiting($identifier, $identifier_type, $wf_id, $state_id, true);
    }
}


function WorkflowStateRollback($identifier, $identifier_type, $workflow_name, $state_name)
{
    $CI = &get_instance();

    try
    {
        // WORKFLOW
        // Get the details about this workflow.
        $wf = $CI->Workflow_model->get_wf_by_name($workflow_name);
        $wf_id = GetArrayStringValue("Id", $wf);
        if ( $wf_id === '' ) return;

        $task_name = GetWorkflowStateProperty($workflow_name, $state_name, 'Library');
        if ( file_exists(APPPATH."libraries/workflow/{$task_name}.php") )
        {


            $CI->load->library("workflow/{$task_name}");
            $task = new $task_name();

            $task->identifier = $identifier;
            $task->identifier_type = $identifier_type;
            //$task->job_id = $this->getJobId();
            //$task->user_id = $this->getUserId();
            $task->rollback();
            $task = null;

            WorkflowStatePrune($identifier, $identifier_type, $workflow_name, $state_name);
            return TRUE;
        }
        else
        {
            throw new Exception("Did not find the file. [".APPPATH."workflow/{$task_name}.php"."]");
        }
    }
    catch(Exception $e)
    {
        //pprint_r($e->getMessage());
    }
    return FALSE;
}
function WorkflowRollback( $identifier, $identifier_type, $wf_name='', $wf_statename ='' )
{
    $CI = &get_instance();

    // If the user did not supply us with a workflow_name, help them out by looking for the active
    // workflow for the identifier.  If we find one, then pull the name from that.
    if ( GetStringValue($wf_name) === '' )
    {
        $progress = GetWorkflowProgressByIdentifier($identifier, $identifier_type);
        if ( empty($progress) ) $wf_name = "";
        if ( !empty($property_name) ) $wf_name = GetArrayStringValue("WorkflowName", $progress[0]);

        if ( GetStringValue($wf_name) === '' ) return FALSE;
    }



    // WORKFLOW
    // Get the details about this workflow.
    $wf = $CI->Workflow_model->get_wf_by_name($wf_name);
    $wf_id = GetArrayStringValue("Id", $wf);
    if ( $wf_id === '' ) return;


    // Grab the most recent progress associated with the job_id.
    $progress = $CI->Workflow_model->select_wf_progress( $identifier, $identifier_type, $wf_id );
    if ( empty($progress) ) return;

    // Let's rollback backwards.
    $reversed = array_reverse($progress);

    $stop = false;
    foreach($reversed as $item)
    {
        if ( $stop ) break;

        $workflow_name = GetArrayStringValue('WorkflowName', $item);
        $state_name = GetArrayStringValue('WorkflowStateName', $item);
        if ( GetStringValue($wf_statename) !== '' && $state_name === $wf_statename ) $stop = true;

        // FIXME: What if we don't have this property set?  This needs to default.
        $state_background_controller = GetWorkflowStateProperty($workflow_name, $state_name, 'Controller');
        if ( $state_background_controller !== '' )
        {
            WorkflowStateRollback($identifier, $identifier_type, $workflow_name, $state_name);
        }
    }

    // In the case where the user is rolling back and they did not specify a state, then
    // they are rolling back the whole thing.  In this case, we want do extra cleanup.
    if ( GetStringValue($wf_statename) === '' )
    {
        // Remove the archived data on S3.
        $support_tag = GetWorkflowProgressProperty($identifier, $identifier_type, $wf_name, 'SupportTag');
        if ( $support_tag !== '' )
        {
            $prefix = GetS3Prefix('archive', $identifier, $identifier_type);
            $prefix = replaceFor($prefix, "COMPANYID", $identifier);
            $prefix = replaceFor($prefix, "COMPANYPARENTID", $identifier);
            $prefix = replaceFor($prefix, "DATE", $support_tag);
            S3DeleteBucketContent(S3_BUCKET, $prefix);
        }

        // Remove any progress properties associated with this run.
        DeleteWorkflowProgressProperty($identifier, $identifier_type, $wf_name);
    }

    return TRUE;

}

/* End of file workflow_helper.php */
/* Location: ./application/helpers/workflow_helper.php */
