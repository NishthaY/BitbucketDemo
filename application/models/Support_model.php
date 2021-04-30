<?php

class Support_model extends CI_Model {

    function __construct()
    {
        parent::__construct();

        $this->db = $this->load->database('default', TRUE);

    }

    public function recent_changeto_list( $user_id, $max=5 )
    {
        if ( GetStringValue($user_id) === '' ) $user_id = GetSessionValue('user_id');
        if ( GetIntValue($max) < 1 ) $max = 5;
        if ( GetStringValue($user_id) === '' ) return;

        $file = "database/sql/history/SELECT_RecentChangeToList.sql";
        $vars = array(
            GetIntValue($user_id),
            GetIntValue($user_id),
            GetIntValue($max)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }

    public function keypool_getnext()
    {
        // Get an available key and get it's id.
        $file = "database/sql/keypool/KeyPoolSELECT.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 0 ) throw new Exception("Key pool has been drained.");
        $key_id = GetArrayIntValue('Id', $results[0]);

        // Disable the key in the keypool.  It has now been taken.
        $file = "database/sql/keypool/KeyPoolUPDATE_Disabled.sql";
        $vars = array(
            GetIntValue($key_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        return $key_id;
    }
    public function delete_keypool_by_id( $id )
    {
        if ( GetStringValue($id) === '' ) return;

        // Create a key called "reserved
        $file = "database/sql/keypool/KeyPoolDELETE.sql";
        $vars = array(
            GetIntValue($id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function update_keypool($key_id, $name, $encryption_key, $enabled)
    {
        if ( GetStringValue($enabled) === 'TRUE' ) $enabled = 't';
        if ( GetStringValue($enabled) === 'FALSE' ) $enabled = 'f';

        // Create a key called "reserved
        $file = "database/sql/keypool/KeyPoolUPDATE.sql";
        $vars = array(
            $name,
            $encryption_key,
            $enabled,
            $key_id
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function reserve_slot_in_keypool()
    {
        // Create a key called "reserved
        $file = "database/sql/keypool/KeyPoolINSERT.sql";
        $vars = array(
            'reserved',
            null,
            'f'
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Get the reserved key id.
        $keys = $this->select_keypool_get_available();
        if ( empty($keys) ) throw new Exception("Unable to create a new key in the pool.");
        $key = $keys[0];
        $key_id = getArrayIntValue("Id", $key);

        return $key_id;
    }
    public function select_keypool_get_available()
    {
        $file = "database/sql/keypool/KeyPoolSELECT_Available.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 0 ) return array();
        return $results;
    }
    public function select_keypool_by_id($id)
    {
        $file = "database/sql/keypool/KeyPoolSELECT_ById.sql";
        $vars = array(
            GetIntValue($id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when getting key by id.");
        return $results[0];
    }
    public function count_ready_keypool_keys()
    {
        $file = "database/sql/keypool/KeyPoolCOUNT_Ready.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) !== 1 ) throw new Exception("Unable to count keys in keypool");
        return getArrayIntValue('count', $results[0]);
    }


    public function create_snapshot_schema()
    {
        $file = "database/sql/support/SnapshotSchemaCREATE.sql";
        $vars = array( );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function remove_snapshot_schema()
    {
        $file = "database/sql/support/SnapshotSchemaREMOVE.sql";
        $vars = array( );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function backup_snapshot_sandboxdata()
    {
        $file = "database/sql/support/SnapshotSchemaBACKUP_SandboxDataSet.sql";
        $vars = array( );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function restore_snapshot_sandboxdata()
    {
        $file = "database/sql/support/SnapshotSchemaRESTORE_SandboxDataSet.sql";
        $vars = array( );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function select_job_details($job_id) {

        if ( getStringValue($job_id) == "" ) return array();

        $file = "database/sql/support/ProcessingQueueSELECT_JobDetails.sql";
        $vars = array(
            getIntValue($job_id),
            getIntValue($job_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) return $results[0];
        return array();

    }
    public function select_job_details_by_pid($pid) {

        if ( getStringValue($pid) == "" ) return array();

        $file = "database/sql/support/ProcessingQueueSELECT_JobDetailsByPID.sql";
        $vars = array(
            getStringValue($pid)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) return $results[0];
        return array();

    }
    public function select_failed_jobs()
    {
        // Since the ProcessingQueueSELECT queries do crazy string manipulation on the
        // payload column, if there are no records in the ProcessQueue table, performance
        // is bad.  Address this by not runnning the query if there are no records in the
        // ProcessQueue table.
        $exists = GetDBExists($this->db, 'select * from "ProcessQueue"', array());
        if ( ! $exists ) return array();

        $user_id = GetSessionValue("user_id");
        if ( getStringValue($user_id) == "" ) return;

        $file = "database/sql/support/ProcessingQueueSELECT_FailedJobs.sql";
        $vars = array(
            getIntValue($user_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        return $results;
    }
    public function count_failed_jobs() {

        $user_id = GetSessionValue("user_id");
        if ( getStringValue($user_id) == "" ) return;

        $file = "database/sql/support/ProcessingQueueSELECT_CountFailedJobs.sql";
        $vars = array(
            getIntValue($user_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) return getArrayStringValue("Count", $results[0]);
        return "";
    }
    public function select_waiting_jobs()
    {
        // Since the ProcessingQueueSELECT queries do crazy string manipulation on the
        // payload column, if there are no records in the ProcessQueue table, performance
        // is bad.  Address this by not runnning the query if there are no records in the
        // ProcessQueue table.
        $exists = GetDBExists($this->db, 'select * from "ProcessQueue"', array());
        if ( ! $exists ) return array();

        $file = "database/sql/support/ProcessingQueueSELECT_WaitingJobs.sql";
        $vars = array( );
        $results = GetDBResults( $this->db, $file, $vars );
        return $results;
    }
    public function count_waiting_jobs() {
        $file = "database/sql/support/ProcessingQueueSELECT_CountWaitingJobs.sql";
        $vars = array( );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) return getArrayStringValue("Count", $results[0]);
        return "";
    }
    public function select_running_jobs() {

        // Since the ProcessingQueueSELECT queries do crazy string manipulation on the
        // payload column, if there are no records in the ProcessQueue table, performance
        // is bad.  Address this by not runnning the query if there are no records in the
        // ProcessQueue table.
        $exists = GetDBExists($this->db, 'select * from "ProcessQueue"', array());
        if ( ! $exists ) return array();

        $file = "database/sql/support/ProcessingQueueSELECT_RunningJobs.sql";
        $vars = array( );
        $results = GetDBResults( $this->db, $file, $vars );
        return $results;
    }
    public function count_running_jobs() {
        $file = "database/sql/support/ProcessingQueueSELECT_CountRunningJobs.sql";
        $vars = array( );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) return getArrayStringValue("Count", $results[0]);
        return "";
    }
    public function exists_support_timer($company_id, $import_date, $tag, $parent_tag) {
        $file = "database/sql/support/SupportTimerEXISTS.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date),
            getStringValue($tag),
            getStringValue($parent_tag),
        );
        $results = GetDBExists($this->db, $file, $vars);
        return $results;
    }
    public function delete_support_timer($company_id, $import_date="")
    {
        if ( $import_date === '' ) $import_date = GetUploadDate($company_id);
        if ( $import_date === '' ) return;

        $file = "database/sql/support/SupportTimerDELETE.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function select_support_timer_report($company_id, $import_date)
    {
        // Get just the parent records.
        $file = "database/sql/support/SupportTimerSELECT_Report.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date),
        );
        $results = GetDBResults($this->db, $file, $vars);

        $depth = 0;

        $retval = array();
        foreach($results as $item)
        {
            $parent_tag = GetArrayStringValue("Tag", $item);
            $start = GetArrayStringValue("Start", $item);
            $end = GetArrayStringValue("End", $item);

            // Just stop trying to show timers if we don't have an end timer.
            if ( $end === '' ) return array();

            // Save this to the output.
            $item['depth'] = $depth;
            unset($item['Start']);
            unset($item['End']);
            $retval[] = $item;

            $child_results = $this->select_support_timer_report_children($company_id, $import_date, $parent_tag, $start, $end, $depth);
            if ( ! empty($child_results) )
            {
                $retval = array_merge($retval, $child_results);
            }
        }

        return $retval;
    }
    public function select_support_timer_report_children($company_id, $import_date, $parent_tag, $start, $end, $depth)
    {
        $replacefor = array();
        $replacefor['{START}'] = GetStringValue($start);
        $replacefor['{END}'] = GetStringValue($end);

        // Get just the parent records.
        $file = "database/sql/support/SupportTimerSELECT_ReportChildren.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date),
            getStringValue($parent_tag)
        );
        $results = GetDBResults($this->db, $file, $vars, $replacefor);
        if ( empty($results) ) return array();

        $depth = $depth + 1;

        $retval = array();
        foreach($results as $item)
        {
            $parent_tag = GetArrayStringValue("Tag", $item);
            $start = GetArrayStringValue("Start", $item);
            $end = GetArrayStringValue("End", $item);

            // Just stop trying to show timers if we don't have an end timer.
            if ( $end === '' ) return array();

            $item['depth'] = $depth;
            unset($item['Start']);
            unset($item['End']);
            $retval[] = $item;

            $child_results = $this->select_support_timer_report_children($company_id, $import_date, $parent_tag, $start, $end, $depth);
            if ( ! empty($child_results) )
            {
                $retval = array_merge($retval, $child_results);
            }


        }
        return $retval;
    }
    public function select_support_timer_download_report($company_id, $import_date)
    {
        $depth = 0;

        // Get just the parent records.
        $file = "database/sql/support/SupportTimerSELECT_DownloadReport.sql";
        $vars = array(
            getIntValue($depth),
            getIntValue($company_id),
            getStringValue($import_date),
        );
        $results = GetDBResults($this->db, $file, $vars);

        $retval = array();
        foreach($results as $item)
        {
            // Save the parent to the results.
            $retval[] = $item;

            $parent_tag = GetArrayStringValue("Tag", $item);
            $start = GetArrayStringValue("Start", $item);
            $end = GetArrayStringValue("End", $item);

            $child_results = $this->select_support_timer_download_report_children($company_id, $import_date, $parent_tag, $start, $end, $depth);
            if ( ! empty($child_results) )
            {
                $retval = array_merge($retval, $child_results);
            }
        }

        $estimated_runtime = $this->select_estimated_runtime($company_id, $import_date);
        $company = $this->Company_model->get_company($company_id);
        $company_name = GetArrayStringValue("company_name", $company);

        $retval = $this->add_summary_line_to_results($retval);
        $retval = $this->add_summary_line_to_results($retval, "Estimated Runtime:", $estimated_runtime);
        $retval = $this->add_summary_line_to_results($retval, "Company:", $company_name);
        $retval = $this->add_summary_line_to_results($retval, "Import Date:", $import_date);

        return $retval;
    }
    public function select_support_timer_download_report_children($company_id, $import_date, $parent_tag, $start, $end, $depth)
    {
        $depth = $depth + 1;

        $replacefor = array();
        $replacefor['{START}'] = GetStringValue($start);
        $replacefor['{END}'] = GetStringValue($end);

        // Get just the parent records.
        $file = "database/sql/support/SupportTimerSELECT_DownloadReportChildren.sql";
        $vars = array(
            getIntValue($depth),
            getIntValue($company_id),
            getStringValue($import_date),
            getStringValue($parent_tag)
        );
        $results = GetDBResults($this->db, $file, $vars, $replacefor);
        if ( empty($results) ) return array();


        $retval = array();
        foreach($results as $item)
        {
            // Save the parent to the results.
            $retval[] = $item;

            $parent_tag = GetArrayStringValue("Tag", $item);
            $start = GetArrayStringValue("Start", $item);
            $end = GetArrayStringValue("End", $item);

            $child_results = $this->select_support_timer_download_report_children($company_id, $import_date, $parent_tag, $start, $end, $depth);
            if ( ! empty($child_results) )
            {
                $retval = array_merge($retval, $child_results);
            }


        }
        return $retval;
    }
    public function add_summary_line_to_results($results, $label="", $value="")
    {
        $count = 2;
        if ( ! empty($results) ) $count = count($results[0]);

        $row = array();
        $row[] = getStringValue($label);
        $row[] = getStringValue($value);
        for( $i=2;$i<$count;$i++ )
        {
            $row[] = "";
        }
        $results[] = $row;
        return $results;
    }
    public function insert_support_timer($company_id, $import_date, $tag, $parent_tag)
    {

        $file = "database/sql/support/SupportTimerINSERT.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date),
            getStringValue($tag),
            GetStringValue($parent_tag) === '' ? "" : GetStringValue($parent_tag)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function update_support_timer($company_id, $import_date, $tag, $parent_tag="", $start=null, $end=null)
    {
        if ( $start != null )
        {
            $file = "database/sql/support/SupportTimerUPDATE_Start.sql";
            $vars = array(
                getStringValue($start),
                getIntValue($company_id),
                getStringValue($import_date),
                getStringValue($tag),
                GetStringValue($parent_tag) === '' ? "" : GetStringValue($parent_tag)
            );
            ExecuteSQL($this->db, $file, $vars);
        }
        if ( $end != null )
        {
            $file = "database/sql/support/SupportTimerUPDATE_End.sql";
            $vars = array(
                getStringValue($end),
                getIntValue($company_id),
                getStringValue($import_date),
                getStringValue($tag),
                GetStringValue($parent_tag) === '' ? "" : GetStringValue($parent_tag)
            );
            ExecuteSQL($this->db, $file, $vars);
        }
    }
    public function select_estimated_runtime($company_id, $import_date="")
    {

        if ( GetStringValue($import_date) === '' )
        {
            // Look for the most recent import date.
            $file = "database/sql/support/SupportTimerSELECT_MostRecentImportDate.sql";
            $vars = array(
                getIntValue($company_id),
            );
            $results = GetDBResults($this->db, $file, $vars);
            if ( count($results) === 1 )
            {
                $results = $results[0];
                $import_date = GetArrayStringValue("ImportDate", $results);
            }
        }

        if ( $import_date === '' ) return "";


        $file = "database/sql/support/SupportTimerSELECT_EstmatedRunTime.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date),
        );
        $results = GetDBResults($this->db, $file, $vars);
        if (count($results) === 1 )
        {
            return getArrayStringValue("EstimatedRunTime", $results[0]);
        }
        return "";
    }
    function select_support_timer_report_list( $company_id ) {
        $file = "database/sql/support/SupportTimerSELECT_ReportList.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }




}


/* End of file Support_model.php */
/* Location: ./system/application/models/Support_model.php */
