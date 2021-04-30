<?php


class Workflow_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    public function get_workflows()
    {
        $file = "database/sql/workflow/WorkflowSELECT.sql";
        $results = GetDBResults($this->db, $file, []);
        if ( empty($results) ) return array();
        return $results;
    }
    public function get_wf_progress_by_identifier( $identifier, $identifier_type )
    {
        $file = "database/sql/workflow/WorkflowProgressSELECT_ByIdentifier.sql";
        $vars = array(
            GetIntValue($identifier),
            GetStringValue($identifier_type)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }
    public function exists_wf_progress_property( $workflow_id, $identifier, $identifier_type, $property_name )
    {
        $file = "database/sql/workflow/WorkflowProgressPropertySELECT.sql";
        $vars = array(
            GetIntValue($workflow_id),
            GetIntValue($identifier),
            GetStringValue($identifier_type),
            GetStringValue($property_name)
        );
        return GetDBExists($this->db, $file, $vars);
    }
    public function select_wf_progress_property( $workflow_id, $identifier, $identifier_type, $property_name )
    {
        $file = "database/sql/workflow/WorkflowProgressPropertySELECT.sql";
        $vars = array(
            GetIntValue($workflow_id),
            GetIntValue($identifier),
            GetStringValue($identifier_type),
            GetStringValue($property_name)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( empty($results) ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many records.  Expected no more than one.");
        return $results[0];
    }
    public function upsert_wf_progress_property( $workflow_id, $identifier, $identifier_type, $property_name, $property_value )
    {
        if ( $this->exists_wf_progress_property($workflow_id, $identifier, $identifier_type, $property_name) )
        {
            $this->update_wf_progress_property($workflow_id, $identifier, $identifier_type, $property_name, $property_value);
        }
        else
        {
            $this->insert_wf_progress_property($workflow_id, $identifier, $identifier_type, $property_name, $property_value);
        }
    }
    public function insert_wf_progress_property( $workflow_id, $identifier, $identifier_type, $property_name, $property_value )
    {
        $file = "database/sql/workflow/WorkflowProgressPropertyINSERT.sql";
        $vars = array(
            GetIntValue($workflow_id),
            GetIntValue($identifier),
            GetStringValue($identifier_type),
            GetStringValue($property_name),
            GetStringValue($property_value)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function update_wf_progress_property( $workflow_id, $identifier, $identifier_type, $property_name, $property_value )
    {
        $file = "database/sql/workflow/WorkflowProgressPropertyUPDATE.sql";
        $vars = array(
            GetStringValue($property_value),
            GetIntValue($workflow_id),
            GetIntValue($identifier),
            GetStringValue($identifier_type),
            GetStringValue($property_name),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function delete_wf_progress_property( $workflow_id, $identifier, $identifier_type, $property_name )
    {
        $file = "database/sql/workflow/WorkflowProgressPropertyDELETE.sql";
        $vars = array(
            GetIntValue($workflow_id),
            GetIntValue($identifier),
            GetStringValue($identifier_type),
            GetStringValue($property_name),
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function delete_all_wf_progress_property( $workflow_id, $identifier, $identifier_type )
    {
        $file = "database/sql/workflow/WorkflowProgressPropertyDELETE_all.sql";
        $vars = array(
            GetIntValue($workflow_id),
            GetIntValue($identifier),
            GetStringValue($identifier_type),
        );
        ExecuteSQL($this->db, $file, $vars);
    }

    public function delete_workflow_progress_by_id( $id )
    {
        $file = "database/sql/workflow/WorkflowProgressDELETE_ById.sql";
        $vars = array(
            GetIntValue($id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }

    public function get_wf_by_id($wf_id)
    {
        $file = "database/sql/workflow/WorkflowSELECT_ById.sql";
        $vars = array(
            getIntValue($wf_id)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( empty($results) ) return array();
        if (count($results) === 1)
        {
            $workflow = $results[0];
            $wf_name = GetArrayStringValue("Name", $workflow);
            return $this->get_wf_by_name($wf_name);
        }
        throw new Exception("Found too many workflows.");
    }
    public function get_wf_by_name($workflow_name)
    {
        $file = "database/sql/workflow/WorkflowSELECT_ByName.sql";
        $vars = array(
            getStringValue($workflow_name)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if (empty($results)) return array();
        if (count($results) === 1)
        {
            $workflow = $results[0];
            $wf_id = GetArrayStringValue("Id", $workflow);

            // Look for Workflow Properties and add them to the workflow.
            $properties = $this->get_wf_properties($wf_id);
            if ( ! empty($properties) )
            {
                foreach($properties as $property)
                {
                    $name = GetArrayStringValue("Name", $property);
                    $value = GetArrayStringValue("Value", $property);

                    if ( $name !== '' )
                    {
                        $workflow[$name] = $value;
                    }
                }
            }

            return $workflow;
        }
        throw new Exception("Found too many workflows.");
    }

    public function get_wf_by_job_id( $job_id )
    {
        try
        {
            $job = $this->Queue_model->get_job($job_id);
        }catch(Exception $e)
        {
            return array();
        }

        $payload = GetArrayStringValue('Payload', $job);
        $params = json_decode($payload);
        $user_id = GetArrayStringValue("0", $params);
        $company_id = GetArrayStringValue("1", $params);
        $companyparent_id = GetArrayStringValue("2", $params);

        $identifier = $company_id;
        $identifier_type = 'company';
        if ( $company_id === '' )
        {
            $identifier = $companyparent_id;
            $identifier_type = 'companyparent';
        }

        $file = "database/sql/workflow/WorkflowProgressSELECT_ByIdentifier.sql";
        $vars = array(
            getIntValue($identifier),
            getStringValue($identifier_type)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }
    public function get_wf_current_state($identifier, $identifier_type, $wf_id)
    {
        $file = "database/sql/workflow/WorkflowStateSELECT_CurrentState.sql";
        $vars = array(
            getIntValue($identifier),
            getStringValue($identifier_type),
            getIntValue($wf_id)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if (empty($results)) return array();
        if (count($results) === 1)
        {
            // Call the get state by id so we can pick up the properties.
            $progress = $results[0];
            $state_id = GetArrayStringValue("WorkflowStateId", $progress);
            $current_state = $this->get_wf_state_by_id($wf_id, $state_id);
            $current_state['Running'] = GetArrayStringValue('Running', $progress);
            $current_state['Complete'] = GetArrayStringValue('Complete', $progress);
            $current_state['Waiting'] = GetArrayStringValue('Waiting', $progress);
            return $current_state;
        }
        throw new Exception("Found too many workflow states.");
    }

    public function get_wf_start_state($wf_id)
    {
        $file = "database/sql/workflow/WorkflowStateSELECT_StartState.sql";
        $vars = array(
            getIntValue($wf_id)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if (empty($results)) return array();
        if (count($results) === 1) return $results[0];
        if (count($results) === 1)
        {
            // Call the get state by id so we can pick up the properties.
            $state_id = GetArrayStringValue("Id", $results[0]);
            return $this->get_wf_state_by_id($wf_id, $state_id);
        }
        throw new Exception("Found too many workflow states.");
    }

    public function get_wf_states($wf_id)
    {
        $file = "database/sql/workflow/WorkflowStateSELECT_AllStatesForWorkflowOrdered.sql";
        $vars = array(
            getStringValue($wf_id)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if (empty($results)) return array();

        $items = array();
        foreach($results as $state)
        {
            $state_id = GetArrayStringValue("Id", $state);
            $state_with_properties = $this->get_wf_state_by_id($wf_id, $state_id);
            if ( ! empty($state_with_properties) )
            {
                $items[] = $state_with_properties;
            }
        }
        return $items;

    }

    public function get_wf_states_with_progress($wf_id, $identifier, $identifier_type)
    {
        $file = "database/sql/workflow/WorkflowStateSELECT_AllStatesForWorkflowOrderedWithProgressFlags.sql";
        $vars = array(
            getStringValue($identifier),
            GetStringValue($identifier_type),
            getStringValue($wf_id)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if (empty($results)) return array();

        $items = array();
        foreach($results as $state)
        {
            $state_id = GetArrayStringValue("StateId", $state);
            $state_with_properties = $this->get_wf_state_by_id($wf_id, $state_id);
            if ( ! empty($state_with_properties) )
            {
                $state_with_properties["Complete"] = GetArrayStringValue('Complete', $state);
                $state_with_properties["Running"] = GetArrayStringValue('Running', $state);
                $state_with_properties["Waiting"] = GetArrayStringValue('Waiting', $state);
                $items[] = $state_with_properties;
            }
        }
        return $items;

    }

    public function get_wf_state_by_id($wf_id, $state_id)
    {
        // Collect the State
        $file = "database/sql/workflow/WorkflowStateSELECT_ByStateId.sql";
        $vars = array(
            getIntValue($wf_id),
            getIntValue($state_id)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if (count($results) > 1) throw new Exception("Found too many workflow states.");
        if (empty($results)) return array();
        if (count($results) === 1) $state = $results[0];

        // Look for State Properties and add them to the state.
        $properties = $this->get_wf_state_properties($wf_id, $state_id);
        if ( ! empty($properties) )
        {
            foreach($properties as $property)
            {
                $name = GetArrayStringValue("Name", $property);
                $value = GetArrayStringValue("Value", $property);

                if ( $name !== '' )
                {
                    $state[$name] = $value;
                }
            }
        }
        return $state;
    }

    public function get_wf_state_by_name($wf_id, $state_name)
    {
        $file = "database/sql/workflow/WorkflowStateSELECT_ByStateName.sql";
        $vars = array(
            getIntValue($wf_id),
            getStringValue($state_name)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( count($results) > 1 ) throw new Exception("Found too many workflow states.");
        if (empty($results)) return array();
        if (count($results) === 1) $state = $results[0];

        // Call the get state by ID so we pick up the state properties.
        $state_id = GetArrayStringValue("Id", $state);
        return $this->get_wf_state_by_id($wf_id, $state_id);

    }

    public function get_wf_state_properties($wf_id, $state_id)
    {
        $file = "database/sql/workflow/WorkflowStatePropertySELECT.sql";
        $vars = array(
            getIntValue($wf_id),
            getIntValue($state_id)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        return $results;
    }

    public function get_wf_properties( $wf_id )
    {
        $file = "database/sql/workflow/WorkflowPropertySELECT.sql";
        $vars = array(
            getIntValue($wf_id)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        return $results;
    }

    public function get_wf_property( $wf_id, $property_name )
    {
        $file = "database/sql/workflow/WorkflowPropertySELECT_ByName.sql";
        $vars = array(
            GetIntValue($wf_id),
            GetStringValue($property_name)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( count($results) == 1 ) return GetArrayStringValue("Value", $results[0]);
        return "";
    }

    public function insert_wf_progress( $identifier, $identifier_type, $wf_id, $state_id, $user_id=null )
    {
        if ( GetStringValue($user_id) === '' ) $user_id = GetSessionValue("user_id");

        $file = "database/sql/workflow/WorkflowProgressINSERT.sql";
        $vars = array(
            getIntValue($identifier),
            getStringValue($identifier_type),
            getIntValue($user_id),
            getIntValue($wf_id),
            getIntValue($state_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function exists_wf_progress( $identifier, $identifier_type, $wf_id, $state_id )
    {
        $file = "database/sql/workflow/WorkflowProgressEXISTS.sql";
        $vars = array(
            getIntValue($identifier),
            getStringValue($identifier_type),
            getIntValue($wf_id),
            getIntValue($state_id)
        );
        return GetDBExists( $this->db, $file, $vars );
    }
    public function delete_wf_progress_by_state( $identifier, $identifier_type, $wf_id, $state_id )
    {
        $file = "database/sql/workflow/WorkflowProgressDELETE.sql";
        $vars = array(
            getIntValue($identifier),
            getStringValue($identifier_type),
            getIntValue($wf_id),
            getIntValue($state_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function delete_wf_progress_all_states( $identifier, $identifier_type, $wf_id )
    {
        $file = "database/sql/workflow/WorkflowProgressDELETE_AllStates.sql";
        $vars = array(
            getIntValue($identifier),
            getStringValue($identifier_type),
            getIntValue($wf_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function select_wf_progress( $identifier, $identifier_type, $wf_id)
    {
        $file = "database/sql/workflow/WorkflowProgressSELECT_AllStates.sql";
        $vars = array(
            getIntValue($identifier),
            getStringValue($identifier_type),
            getStringValue($wf_id)
        );
        $results = ExecuteSQL( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    public function select_recent_wf_progress( $identifier, $identifier_type )
    {
        $file = "database/sql/workflow/WorkflowProgressSELECT_AllStates.sql";
        $vars = array(
            getIntValue($identifier),
            getStringValue($identifier_type)
        );
        $results = ExecuteSQL( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    public function update_workflow_state_running($identifier, $identifier_type, $wf_id, $state_id, $running=true)
    {
        if ( $running ) $file = "database/sql/workflow/WorkflowProgressUPDATE_SetRunning.sql";
        if ( ! $running ) $file = "database/sql/workflow/WorkflowProgressUPDATE_SetNotRunning.sql";
        $vars = array(
            getIntValue($identifier),
            getStringValue($identifier_type),
            getIntValue($wf_id),
            getIntValue($state_id),
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function update_workflow_state_failed($identifier, $identifier_type, $wf_id, $state_id, $failed=true)
    {
        if ( $failed ) $file = "database/sql/workflow/WorkflowProgressUPDATE_SetFailed.sql";
        if ( ! $failed ) $file = "database/sql/workflow/WorkflowProgressUPDATE_SetNotFailed.sql";
        $vars = array(
            getIntValue($identifier),
            getStringValue($identifier_type),
            getIntValue($wf_id),
            getIntValue($state_id),
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function update_workflow_state_complete($identifier, $identifier_type, $wf_id, $state_id, $completed=true)
    {
        if ( $completed ) $file = "database/sql/workflow/WorkflowProgressUPDATE_SetCompleted.sql";
        if ( ! $completed ) $file = "database/sql/workflow/WorkflowProgressUPDATE_SetNotCompleted.sql";
        $vars = array(
            getIntValue($identifier),
            getStringValue($identifier_type),
            getIntValue($wf_id),
            getIntValue($state_id),
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function update_workflow_state_waiting($identifier, $identifier_type, $wf_id, $state_id, $waiting=true)
    {
        if ( $waiting ) $file = "database/sql/workflow/WorkflowProgressUPDATE_SetWaiting.sql";
        if ( ! $waiting ) $file = "database/sql/workflow/WorkflowProgressUPDATE_SetNotWaiting.sql";
        $vars = array(
            getIntValue($identifier),
            getStringValue($identifier_type),
            getIntValue($wf_id),
            getIntValue($state_id),
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function select_in_progress_workflow_items()
    {
        $file = "database/sql/workflow/WorkflowProgressSELECT_AllRunningProcesses.sql";
        $vars = array();
        $draft = GetDBResults( $this->db, $file, $vars );
        if ( empty($draft) ) return array();
        return $draft;
    }


}


/* End of file Workflow_model.php */
/* Location: ./system/application/models/Workflow_model.php */
