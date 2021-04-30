<?php
class Queue_model extends CI_Model{


    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
        $this->load->helper('phperror');

    }
    function get_job_age($job_id)
    {
        $file = "database/sql/queue/ProcessQueueSELECT_RunningJobAge.sql";
        $vars = Array(
            getIntValue($job_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 1 ) return GetArrayStringValue("Age", $results[0]);
        return "";
    }
    function add_grouped_worker_job( $companyparent_id, $company_id, $user_id, $group_id, $controller, $function, $exec_time=null )
    {
        $payload = array();
        $payload[] = GetStringValue($user_id);
        $payload[] = GetStringValue($company_id);
        $payload[] = GetStringValue($companyparent_id);

        // Set the execute time to NOW or the date passed in.
        $exec_time === null ? $timestamp = date('Y-m-d H:i:s') : $timestamp = date('Y-m-d H:i:s', strtotime($exec_time));

        $file = "database/sql/queue/AddGroupedWorkerJob.sql";
        $vars = Array(
            getStringValue($controller),
            getStringValue($function),
            json_encode($payload),
            getStringValue($timestamp),
            GetStringValue($company_id) === '' ? null : getIntValue($company_id),
            getIntValue($user_id),
            getIntValue($group_id),
            GetStringValue($companyparent_id) === '' ? null : getIntValue($companyparent_id)
        );
        $results = ExecuteSQL( $this->db, $file, $vars );

        // Return the job id that was inserted.
        $job_id = null;
        if ( count($results) == 1 )
        {
            $job_id = getArrayIntValue("Id", $results[0]);
        }
        return $job_id;
    }
    function add_worker_job( $companyparent_id, $company_id, $user_id, $controller, $function, $exec_time=null )
    {
        $payload = array();
        $payload[] = GetStringValue($user_id);
        $payload[] = GetStringValue($company_id);
        $payload[] = GetStringValue($companyparent_id);

        // Set the execute time to NOW or the date passed in.
        $exec_time === null ? $timestamp = date('Y-m-d H:i:s') : $timestamp = date('Y-m-d H:i:s', strtotime($exec_time));

        $file = "database/sql/queue/AddWorkerJob.sql";
        $vars = Array(
            getStringValue($controller),
            getStringValue($function),
            json_encode($payload),
            getStringValue($timestamp),
            GetStringValue($company_id) === '' ? null : getIntValue($company_id),
            getIntValue($user_id),
            GetStringValue($companyparent_id) === '' ? null : getIntValue($companyparent_id)
        );
        $results = ExecuteSQL( $this->db, $file, $vars );

        // Return the job id that was inserted.
        $job_id = null;
        if ( count($results) == 1 )
        {
            $job_id = getArrayIntValue("Id", $results[0]);
        }
        return $job_id;
    }
    function add_job($controller,$function,$payload,$exec_time=null) {

        // Convert our timestamp into a date.  If we have null, then
        // the timestamp will be 'now'.
        if ( $exec_time === null )
        {
            $timestamp = date('Y-m-d H:i:s');
        }
        else
        {
            $timestamp = date('Y-m-d H:i:s', strtotime($exec_time));
        }

        $file = "database/sql/queue/AddJob.sql";
        $vars = Array(
            getStringValue($controller),
            getStringValue($function),
            json_encode($payload),
            getStringValue($timestamp)
        );
        $results = ExecuteSQL( $this->db, $file, $vars );


        // Return the job id that was inserted.
        $job_id = null;
        if ( count($results) == 1 )
        {
            $job_id = getArrayIntValue("Id", $results[0]);
        }
        return $job_id;

    }

    function start_job($job_id) {

        $job = $this->get_job($job_id);
        if (!empty($job['StartTime'])) {
            throw new Exception ('Job has already been started.');
            return false;
        }

        $file = "database/sql/queue/StartJob.sql";
        $vars = Array(
            intval($job_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

    function end_job($job_id) {

        $file = "database/sql/queue/EndJob.sql";
        $vars = Array(
            intval($job_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function reset_job($job_id) {
        $file = "database/sql/queue/ResetJob.sql";
        $vars = Array(
            intval($job_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function set_process_id($job_id, $process_id) {
        $file = "database/sql/queue/SetProcessId.sql";
        $vars = Array(
            getStringValue($process_id)
            , intval($job_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function get_long_running_jobs() {

        $file = "database/sql/queue/LongRunningJobs.sql";
        $vars = Array( );
        $results = GetDBResults( $this->db, $file, $vars );
        return $results;
    }
    function get_job($job_id) {

        $file = "database/sql/queue/GetJob.sql";
        $vars = Array(
            getIntValue($job_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) )
        {
            throw new Exception('job ID not found.');
        }
        if ( count($results) == 1 ) $results = $results[0];
        return $results;
    }

    function get_next_job($start=false) {
        $file = "database/sql/queue/GetNextJob.sql";
        $vars = Array();
        $results = GetDBResults( $this->db, $file, $vars );

        if ( ! empty($results) && count($results) == 1 ) {
            $results = $results[0];
            if ($start === true)
            {
                $this->start_job(getArrayIntValue("Id", $results));
            }
        }else{
            return false;
        }
        return $results;
    }

    function fail_job($job_id, $reason) {
        $file = "database/sql/queue/FailJob.sql";
        $vars = Array(
            getStringValue($reason)
            ,getIntValue($job_id)

        );
        ExecuteSQL( $this->db, $file, $vars );
    }

    function get_failed_jobs() {
        $file = "database/sql/queue/ProcessQueueSELECT_FindFailedJobs.sql";
        $vars = Array( );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }

    function get_running_jobs() {
        $file = "database/sql/queue/ProcessQueueSELECT_FindRunningJobs.sql";
        $vars = Array( );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function does_company_have_running_or_pending_jobs($company_id) {

        $replacefor = array();
        $replacefor = array_merge($replacefor, array("COMPANY_ID" => $company_id));

        $file = "database/sql/queue/ProcessQueueSELECT_CompanyJobRunning.sql";
        $vars = Array( );
        $results = GetDBResults( $this->db, $file, $vars, $replacefor );
        if ( count($results) != 1 ) throw new Exception("Unexpected results");
        $results = $results[0];
        $results = getArrayStringValue("PendingOrRunningJobs", $results);
        if ( $results == "t" ) return true;
        if ( $results == "f" ) return false;
        throw new Exception("Unexpected results");
    }

    function does_companyparent_have_running_or_pending_jobs($companyparent_id) {

        $replacefor = array();
        $replacefor = array_merge($replacefor, array("COMPANYPARENT_ID" => $companyparent_id));

        $file = "database/sql/queue/ProcessQueueSELECT_CompanyParentJobRunning.sql";
        $vars = Array( );
        $results = GetDBResults( $this->db, $file, $vars, $replacefor );
        if ( count($results) != 1 ) throw new Exception("Unexpected results");
        $results = $results[0];
        $results = getArrayStringValue("PendingOrRunningJobs", $results);
        if ( $results == "t" ) return true;
        if ( $results == "f" ) return false;
        throw new Exception("Unexpected results");
    }
    function get_similar_job_pending_or_running($controller,$function)
    {
        $file = "database/sql/queue/ProcessQueueSELECT_FindSimilarPendingOrRunningJobs.sql";
        $vars = Array(
            getStringValue($controller),
            getStringValue($function)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) $results = array();
        return $results;
    }
    function is_similar_job_pending_or_running($controller,$function)
    {
        $file = "database/sql/queue/ProcessQueueBOOLEAN_FindSimilarPendingOrRunningJobs.sql";
        $vars = Array(
            getStringValue($controller)
            , getStringValue($function)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        $results = $results[0];
        if ( getArrayStringValue("PendingOrRunningJobs", $results) == "t" ) return true;
        if ( getArrayStringValue("PendingOrRunningJobs", $results) == "f" ) return false;
        throw new Exception("Unexpected results");
    }
    function is_similar_job_pending($controller,$function)
    {
        $file = "database/sql/queue/ProcessQueueSELECT_SimilarPendingJobs.sql";
        $vars = Array(
            getStringValue($controller)
            , getStringValue($function)
        );
        return GetDBExists( $this->db, $file, $vars );
    }
    function get_similar_pending_jobs($controller,$function)
    {
        $file = "database/sql/queue/ProcessQueueSELECT_SimilarPendingJobs.sql";
        $vars = Array(
            getStringValue($controller)
            , getStringValue($function)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function delete_job( $job_id )
    {
        $file = "database/sql/queue/ProcessQueueDELETE.sql";
        $vars = Array(
            GetIntValue($job_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function is_scheduled_job_running($job_name)
    {
        $file = "database/sql/queue/ProcessQueueSELECT_IsScheduledJobRunning.sql";
        $vars = Array(
            GetSTringValue($job_name)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) $results = $results[0];
        if ( getArrayStringValue("IsRunning", $results) == "t" ) return true;
        if ( getArrayStringValue("IsRunning", $results) == "f" ) return false;
        throw new Exception("Unexpected results");
    }
    function get_most_recent_job()
    {
        $file = "database/sql/queue/ProcessQueueSELECT_FindRecentJob.sql";
        $vars = Array( );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results[0];
    }

}

/* End of file queue_model.php */
/* Location: ./application/models/queue_model.php */
