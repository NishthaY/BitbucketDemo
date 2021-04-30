<?php

/**
 * FailJob
 *
 * Anytime we have a job that has failed, this function will clean
 * up the queue and take any action needed.
 *
 * @param $job_id
 * @param $reason
 */
function FailJob( $job_id, $reason )
{
    $CI = &get_instance();

    $job = $CI->Queue_model->get_job($job_id);
    if ( getArrayStringValue("EndTime", $job) == "" )
    {
        $CI->Queue_model->fail_job($job_id, $reason);
    }

    // When a job fails, we need to handle it.  Notify the function that
    // handles PHP errors.
    HandleAJAXRuntimePHPError($job_id);
}

/**
 * HasDynoSupport
 *
 * Check our application options and return TRUE or FALSE if the running
 * release level is configured to use Heroku dynos.
 *
 * @param null $pid
 * @return bool
 */
function HasDynoSupport( $pid=null )
{
    if ( strtoupper(GetAppOption(DYNO_SUPPORT_ENABLED)) == "TRUE" ) return true;
    return false;
}

/**
 * HasOneOffDynoSupport
 *
 * Check our application options and return TRUE or FALSE if the running
 * release level is configured to use Heroku one-off Dynos.
 *
 * @return bool
 */
function HasOneOffDynoSupport() {
    if ( strtoupper(GetAppOption(ONE_OFF_DYNO_SUPPORT_ENABLED)) == "TRUE" ) return true;
    return false;
}

/**
 * GetDirectorStatus
 *
 * This function will return the status of the queue director application.
 * The status may be 'success', 'danger' or 'warning'
 *
 * If the director is running just fine, it will return success.  If the
 * director is not running, you will get danger.
 *
 * If we can't figure out if it is running or not, you will get warning.
 *
 * @return string
 */
function GetDirectorStatus() {

    $CI = &get_instance();

    if ( HasDynoSupport() )
    {
        $results = $CI->HerokuDynoRequest_model->get_dynos(APP_NAME);
        foreach($results as $result)
        {
            $type = strtolower(getArrayStringValue("type", $result));
            $command = strtolower(getArrayStringValue("command", $result));

            if ( $type == "worker" && strpos($command, "queuedirector") !== FALSE )
            {
                return "success";
            }
        }
        return "danger";
    }
    if ( ! HasDynoSupport() )
    {
        $cmd = "ps -ef | grep QueueDirector | grep -v grep";
        $output = shell_exec($cmd);
        $output = trim($output);
        if ( getStringValue($output) == "" ) return "danger";
        $lines = explode("\n", $output);
        if ( count($lines) == 1 ) return "success";
    }
    return "warning";
}

/**
 * IsProcessRunning
 *
 * This function will check to see if a process is running or not by PID
 * It is smart enough to know the difference between a dyno name vs a
 * pid and will determine if the process is running on the platform
 * that matches the input.
 *
 * This will return TRUE if running.
 *
 * @param $pid_or_dyno_name
 * @return bool
 */
function IsProcessRunning( $pid_or_dyno_name ) {

    $CI = &get_instance();

    // Input Validation.
    $pid_or_dyno_name = getStringValue($pid_or_dyno_name);
    if ( $pid_or_dyno_name == "" ) return false;

    // Use the format of the PID to decide if we are
    // checking a dyno or a local process.
    $dynos = false;
    if ( strpos($pid_or_dyno_name, ".") !== FALSE ) $dynos = true;

    if ( $dynos )
    {
        // HEROKU
        // Check with Heroku to see if the dyno is running or not.
        $results = $CI->HerokuDynoRequest_model->get_dyno_info(APP_NAME, $pid_or_dyno_name);
        if ( empty($results) ) return false;
        return true;
    }
    else
    {
        // NO HEROKU
        // We are running locally, use the bash shell to decide.
        $command = "ps -ef | grep -v grep | tr -s \" \" | cut -d \" \" -f 2 | grep \"{$pid_or_dyno_name}\"";
        $result = `{$command}`;
        $result = trim($result);
        $result = getStringValue($result);
        if ( $result == "" ) return false;
        return true;
    }
}

/**
 * StartProcess
 *
 * This function will execute a background task off of the queue.  It should get the
 * general information off the queue table and that data will be transformed into a
 * CodeIgniter CLI command.  It will then be executed on the running shell.
 *
 * It is assumed that if the job produces output, an error has occurred.  When output
 * is captured, the end results is processed as a failure.
 *
 * @param $job_id
 * @param $controller
 * @param $function
 * @param string $payload
 * @throws Exception
 */
function StartProcess( $job_id, $controller, $function, $payload="{}" )
{

    $CI = &get_instance();
    if ( getStringValue($controller) == "" ) throw new Exception("Missing required input controller");
    if ( getStringValue($function) == "" ) throw new Exception("Missing required input function");
    if ( getStringValue($payload) == "" ) $payload = "{}";

    // Look for PHP and error if we can't find it.
    $php = `which php`;
    $php = trim($php);
    if ( getStringValue($php) == "" ) throw new Exception("Unable to locate PHP.");

    // A2P Background jobs expect the following four inputs in this order.
    // [ user_id, company_id, companyparent_id, job_id ]

    // Take the payload string and turn it into an object.  We expect it to
    // be an flat array with three parameters.  If the array contains an empty parameter,
    // replace the empty string with "NULL" as CodeIgniters cli does not support blank
    // parameters.  Last, we will escape any characters that would make a shell command blow up.
    $payload = json_decode($payload, true);
    $args = "cli/{$controller} {$function}";
    if ( ! empty($payload) )
    {
        foreach ($payload as $param)
        {
            if (strpos($param,'/')) throw new Exception("Payload contains invalid characters.");
            if ( $param === '' ) $param = "NULL";
            StripNonNumeric($param) == $param ? $args .= ' ' . $param : $args .= ' ' . escapeshellarg($param);
        }
    }
    $args .= ' ' . $job_id;     // Attach the job id as the final parameter.

    // Run the job and capture the output.
    $cmd = FCPATH . "index.php {$args}";
    ob_start();

    // Run any HOOKS we can identify before we start.
    StartingProcess($job_id);

    passthru("{$php} -q {$cmd}",$exitstatus);
    $output = trim(ob_get_clean());

    // buildpacks will write to STDOUT when used by Heroku.  Capture the
    // output from buildpacks we use and remove them from our output.
    $output = RemoveLinesContaining($output, "buildpack");

    // Run any HOOKS we can identifiy before we stop.
    StoppingProcess($job_id, $output);

    // THROW EXCEPTION
    // If we have any output, then this job is a failure.  Handle a few known
    // scenarios on the way out.
    if ( ! empty($output) )
    {
        if ( strpos(strtoupper($output), "TEMPLATE='ADVICE2PAY") !== FALSE ) $output = "Check your routes!  We are getting back an HTML template page!";
        throw new Exception($output);
    }

}

/**
 * StopProcess
 *
 * Find the running process or dyno and stop it's execution.
 *
 * @param $pid_or_dyno_name
 * @return bool
 */
function StopProcess( $pid_or_dyno_name )
{

    $CI = &get_instance();

    // Input Validation.
    $pid_or_dyno_name = getStringValue($pid_or_dyno_name);
    if ( $pid_or_dyno_name == "" ) return false;

    // Use the format of the PID to decide if we are
    // checking a dyno or a local process.
    $dynos = false;
    if ( strpos($pid_or_dyno_name, ".") !== FALSE ) $dynos = true;

    if ( $dynos )
    {
        // HEROKU
        // Check with Heroku to see if the dyno is running or not.
        $results = $CI->HerokuDynoRequest_model->stop_dyno(APP_NAME, $pid_or_dyno_name);
        sleep(2);
        $results = $CI->HerokuDynoRequest_model->get_dyno_info(APP_NAME, $pid_or_dyno_name);
        if ( ! empty($results) ) return false;
        return true;
    }
    else
    {
        // Grab the child process for the
        //ps -ef                | # get all processes
        //grep -v grep          | # exclude grep procs
        //tr -s " "             | # normalize space
        //grep "{$pid}"           # now the target pid
        $cmd = "ps -ef | grep -v grep | tr -s ' ' | grep  ' {$pid_or_dyno_name} '";
        $output = shell_exec($cmd);
        $output = trim($output);

        // We just got both the parent and the child.  Find the child_pid.
        $items = explode("\n", $output);
        foreach($items as $item)
        {
            $parts = explode(" ", $item);
            $child_pid = $parts[1];
            $parent_pid = $parts[2];
            if ( $parent_pid == $pid_or_dyno_name ) break;
        }

        // We found a numeric pid that belongs to the child process spawned
        // the processor.
        if ( StripNonNumeric($child_pid) == $child_pid )
        {
            // Kill off the child.
            $command = "kill {$child_pid}";
            $result = `{$command}`;
            sleep(1);
            if ( IsProcessRunning($child_pid) ) return false;
            sleep(1);
        }

        // Is the parent process still running.
        if ( IsProcessRunning($pid_or_dyno_name) )
        {
            // Kill the parents too.  * no witnesses ;-) *
            $command = "kill {$pid_or_dyno_name}";
            $result = `{$command}`;
            sleep(1);
            if ( IsProcessRunning($pid_or_dyno_name) ) return false;
        }
        return true;
    }

}

/**
 * StartingProcess
 *
 * This function is executed right before a process starts.  This is a hook
 * function that will trigger any application level functionality for this
 * event.
 *
 */
function StartingProcess( $job_id )
{
    // Run starting logic if this job is a workflow job.
    WorkflowJobStarting($job_id);
}

/**
 * Stopping Process
 *
 * This function is execute right before a process stops.  This is a hook
 * function that will trigger any application level functionality for this
 * event.
 *
 */
function StoppingProcess($job_id, $output="")
{
    // Run stopping logic if this job is a workflow job.
    WorkflowJobStopping($job_id, $output);
}

/* End of file queue_helper.php */
/* Location: ./application/helpers/queue_helper.php */
