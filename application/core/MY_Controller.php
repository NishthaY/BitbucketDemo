<?php defined('BASEPATH') OR exit('No direct script access allowed');

class A2PRecurringJob extends CI_Controller
{
    public $ci;

    public function __construct( )
    {
        // Construct our parent class
        parent::__construct();

        //CLI ONLY! GO AWAY!
        if ( ! $this->input->is_cli_request() ) {
            Error404();
            return;
        }

        $this->ci           = &get_instance();

    }
    public function __destruct()
    {
        unset($this->ci);
    }

    /**
     * reportJobFailure
     *
     * Email the support staff about this recurring job failure.
     * @param $message
     */
    protected function reportJobFailure($message)
    {
        $job_name = get_called_class();
        if ( ! $this->Queue_model->is_similar_job_pending(get_class(), "schedule") )
        {
            $message = "There is no need to restart this job, it will run again at it's next scheduled execution time.<BR><BR>";
        }
        else
        {
            $message = "ACTION REQUIRED: This job failed and was unable to restart itself.  You must manually restart this job.<BR><BR>";
        }
        $message .= "The scheduled job [{$job_name}] has failed with the following message: <BR><BR>" . GetSessionValue($message);

        SendSupportEmail(1, null, $message, "", null);
    }

    /**
     * restartJob
     *
     * This function will evaluate the processing queue and will add the
     * current job onto the queue.  There can be only one copy of this job
     * so if there is one schedule in the future, it will be removed.  A
     * new one will be added with the time specifications provided.
     *
     * If the "total minutes" to wait before execution is zero, no job
     * will be added to the queue.  Existing jobs will removed.  This
     * effectilly executes and stop the job.
     *
     * @param $minutes
     * @param $hours
     * @param $days
     * @param $months
     * @param $years
     */
    protected function restartJob($minutes, $hours, $days, $months, $years)
    {

        // TODO: If this job is currently RUNNING, do not allow it to start.
        // You can't run if you are already running.
        //if ( $this->Queue_model->is_scheduled_job_running( get_called_class() ))
        //{
        //    throw new Exception("This job is already running!  Aborting this run and and waiting for the previous one to finish.");
        //}

        // If there are copies of this job on the queue already, delete them.
        // We are about to run and create another.
        if ( $this->Queue_model->is_similar_job_pending(get_called_class(), "schedule") )
        {
            $jobs = $this->Queue_model->get_similar_pending_jobs(get_called_class(), "schedule");
            foreach($jobs as $job)
            {
                $job_id = GetArrayStringValue("Id", $job);
                $this->Queue_model->delete_job($job_id);
            }
        }


        // Calculate the total number of minutes we were asked to wait
        // before we run this again.
        $h = $hours * 60;
        $d = $days * 1440;
        $m = $months * 43800;
        $y = $years * 525600;
        $total_minutes = $minutes + $h + $d + $m + $y;

        // Turn the go date into a string we can read.
        $go = "+{$total_minutes} minutes";
        $timestamp = date('Y-m-d H:i:s', strtotime($go));

        // re-queue this job. if the time is in the future.
        if ( GetIntValue($total_minutes) !== 0 )
        {
            $controller = get_called_class();
            $function = "index";
            $payload = array($minutes,$hours,$days,$months,$years);
            $this->Queue_model->add_job(get_called_class(),"schedule",$payload,$timestamp);
        }

    }

}
class A2PWorkflowStepController extends SecureController
{
    protected $wf_stepname;
    protected $wf_name;
    protected $identifier;
    protected $identifier_type;
    protected $company_id;
    protected $companyparent_id;

    public function __construct()
    {
        parent::__construct();

    }

    protected function setWorkflowProperties($wf_name, $wf_stepname)
    {
        if ( GetStringValue($wf_name) === '' ) throw new Exception("Missing required input.  wf_name");
        if ( GetStringValue($wf_stepname) === '' ) throw new Exception("Missing required input.  wf_stepname");

        $wf = WorkflowFind($wf_name);
        if ( empty($wf) ) throw new Exception("Unable to locate specified workflow.");

        // IDENTIFIER
        // A workflow is specific to a singular identifier type, company or companyparent for example.
        // Find the identifier and identifier type for the workflow associated with this workflow.
        $identifer_type = GetWorkflowProperty($wf_name, "IdentifierType");
        if ( $identifer_type === 'company' ) $identifer = GetSessionValue('company_id');
        if ( $identifer_type === 'companyparent' ) $identifer = GetSessionValue('companyparent_id');
        if ( GetStringValue($identifer) === '' ) throw new Exception("Unable to locate workflow identifier");

        if ( GetStringValue($identifer) === '' ) throw new Exception("Unable to set identifier.");

        $this->wf_name = $wf_name;
        $this->wf_stepname = $wf_stepname;
        $this->identifier = $identifer;
        $this->identifier_type = $identifer_type;

        // Set the company_id and companyparent_id properties based on the identifier.
        if ( $this->identifier_type === 'company' )
        {
            $this->company_id = $this->identifier;
            $this->companyparent_id = GetCompanyParentId($this->company_id);
            $this->encryption_key = GetCompanyEncryptionKey($this->company_id);
        }
        else if ( $this->identifier_type === 'companyparent' )
        {
            $this->company_id = null;
            $this->companyparent_id = $this->identifier;
            $this->encryption_key = GetCompanyParentEncryptionKey($this->companyparent_id);
        }
        else throw new Exception("Unsupported identifier_type.");

    }
    public function takeSnapshot()
    {
        $library_name = GetWorkflowStateProperty($this->wf_name, $this->wf_stepname, 'Library');
        $this->load->library("workflow/{$library_name}");
        $obj = new $library_name();
        $obj->identifier = $this->identifier;
        $obj->identifier_type = $this->identifier_type;
        $obj->wf_name = $this->wf_name;
        $obj->wf_stepname = $this->wf_stepname;
        $obj->user_id = GetSessionValue('user_id');
        $obj->snapshot();
        $obj = null;
    }

}
class A2PWorkflowStep extends A2PWorker
{
    protected $wf_stepname;
    protected $wf_name;
    protected $identifier;
    protected $identifier_type;

    public function __destruct()
    {
        parent::__destruct(); // TODO: Change the autogenerated stub
    }


    protected function setWorkflowProperties($wf_name, $wf_stepname, $company_id, $companyparent_id)
    {
        if ( GetStringValue($wf_name) === '' ) throw new Exception("Missing required input.  wf_name");
        if ( GetStringValue($wf_stepname) === '' ) throw new Exception("Missing required input.  wf_stepname");

        $wf = WorkflowFind($wf_name);
        if ( empty($wf) ) throw new Exception("Unable to locate specified workflow.");

        // IDENTIFIER
        // A workflow is specific to a singular identifier type, company or companyparent for example.
        // Find the identifier and identifier type for the workflow associated with this workflow.
        $identifer_type = GetWorkflowProperty($wf_name, "IdentifierType");
        if ( $identifer_type === 'company' ) $identifer = $company_id;
        if ( $identifer_type === 'companyparent' ) $identifer = $companyparent_id;
        if ( GetStringValue($identifer) === '' ) throw new Exception("Unable to locate workflow identifier");

        $this->wf_name = $wf_name;
        $this->wf_stepname = $wf_stepname;
        $this->identifier = $identifer;
        $this->identifier_type = $identifer_type;

    }

    public function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
        try
        {

            if ( GetStringValue($this->wf_name) === '' ) throw new Exception("Please set the wf_name before you call the parent index function.");
            if ( GetStringValue($this->wf_stepname) === '' ) throw new Exception("Please set the wf_stepname before you call the parent index function.");

            if ( strtoupper(GetStringValue($user_id)) === 'NULL' ) $user_id = "";
            if ( strtoupper(GetStringValue($company_id)) === 'NULL' ) $company_id = "";
            if ( strtoupper(GetStringValue($companyparent_id)) === 'NULL' ) $companyparent_id = "";
            if ( strtoupper(GetStringValue($job_id)) === 'NULL' ) $job_id = "";

            // Since this is a "Workflow Step", send out a notification that
            // we are starting a wizard step before we get going.
            NotifyStepStart($company_id, $companyparent_id);
            parent::index($user_id, $company_id, $companyparent_id, $job_id);

            $this->setWorkflowProperties($this->wf_name, $this->wf_stepname, $company_id, $companyparent_id);
            $this->execute();
        }
        catch(Exception $e)
        {
            print "Unhandled exception. " . $e->getMessage();
        }

    }


    protected function execute()
    {
        throw new Exception("You must override and implement the execute function.");
    }

}
class A2PWizardStep extends A2PWorker
{

    public function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {

        if ( strtoupper(GetStringValue($user_id)) === 'NULL' ) $user_id = "";
        if ( strtoupper(GetStringValue($company_id) === 'NULL' )) $company_id = "";
        if ( strtoupper(GetStringValue($companyparent_id) === 'NULL' )) $companyparent_id = "";
        if ( strtoupper(GetStringValue($job_id) === 'NULL' )) $job_id = "";

        // Since this is a "Wizard Step", send out a notification that
        // we are starting a wizard step before we get going.
        NotifyStepStart($company_id, $companyparent_id);
        parent::index($user_id, $company_id, $companyparent_id, $job_id);
    }


    /**
     * schedule_next_step
     *
     * This function will place a job in the queue for the step specified.
     * If the current step is "Grouped" then the next step will carry forward
     * the group_id.
     *
     * Grouped jobs are single threaded and will not run if another job in the
     * group is currently running.
     *
     * @param $step_name
     */
    protected function schedule_next_step( $step_name )
    {
        if ( getStringValue($step_name) === '' ) return;

        // If we have a job_id AND that job as a group id, span the next job as
        // a grouped job and exit.
        if ( getStringValue($this->getJobId()) !== '' )
        {
            $job = $this->Queue_model->get_job($this->getJobId());
            $group_id = GetArrayStringValue("GroupId", $job);
            if ($group_id !== '') {
                $this->Queue_model->add_grouped_worker_job($this->getCompanyParentId(), $this->getCompanyId(), $this->getUserId(), $group_id, $step_name, "index");
                return;
            }
        }

        // If we made it here, just create a normal job on the queue.
        $this->Queue_model->add_worker_job($this->getCompanyParentId(), $this->getCompanyId(), $this->getUserId(), $step_name, "index");
    }
    protected function schedule_next_workflow_step( $class_name )
    {
        if ( GetStringValue($class_name) === '' ) return;

        // Find the next step in the workflow based on the name of our current background class.
        $search = WorkflowStateFind('sample', 'Controller', $class_name);
        $next_id = GetArrayStringValue("NextStateId", $search);
        $next = WorkflowStateFind('sample', 'Id', $next_id);
        $step_name = GetArrayStringValue('Controller', $next);

        // No next class name?  Okay, nothing to schedule then.
        if ($step_name === '' ) return;

        $this->schedule_next_step($step_name);
    }
    protected function schedule_workflow_step( $workflow_name, $state_name )
    {

        $state = WorkflowStateFind($workflow_name, "Name", $state_name);
        $step_name = GetArrayStringValue('Controller', $state);

        // No next class name?  Okay, nothing to schedule then.
        if ($step_name === '' ) return;

        $this->schedule_next_step($step_name);

    }
}
class A2PWorker extends CI_Controller
{
    protected   	$debug;         // Turn this on in development so have stuff write to console.
    protected       $timers;        // Turn timers on or off.
    protected 	    $timer_array;   // Collection of timers, if they are on.
    protected       $log_debug_messages;
    protected       $encryption_key;
    private         $cli;
    private         $company_id;
    private         $user_id;
    private         $companyparent_id;
    private         $ci;
    protected       $job_id;
    protected       $send_notifications;

    public function __construct( $cli=true )
    {
        // Construct our parent class
        parent::__construct();

        //CLI ONLY! GO AWAY!
        if ( $cli )
        {
            if ( ! $this->input->is_cli_request() ) {
                Error404();
                return;
            }
        }

        $this->ci           = &get_instance();
        $this->cli          = $cli;
        $this->debug        = false;
        $this->timers       = false;
        $this->timer_array  = array();
        $this->log_debug_messages = false;
        $this->send_notifications = true;

        // In my local development, my memory limit is set to -1 which means unlimited.
        // In the Heroku world, we will have no less than 128M.  Apply that limit here
        // so that in development I have the same restrictions as on the heroku server.
        $memory_limit = getStringValue(ini_get('memory_limit'));
        if ( $memory_limit === '-1' )
        {
            LogIt(get_called_class(), "No memory limit detected.  Manually setting it to default value.");
            ini_set('memory_limit', '512M');
            $memory_limit = getStringValue(ini_get('memory_limit'));
        }
        LogIt(get_called_class(), "Memory Limit: [{$memory_limit}]");

    }
    public function __destruct()
    {
        $this->ci           = &get_instance();
        $this->ci->load->model('Tuning_model');

        // Before we stop this offline task.  Vacuum the database.
        $this->Tuning_model->vacuum();

        $this->debug("Worker [".get_called_class()."] complete.");
        if ( $this->send_notifications) $this->notify_status_update('EMPTY_STRING');
        $this->ci->Wizard_model->update_activity($this->company_id, '');

        unset($this->debug);
        unset($this->timers);
        unset($this->timer_array);
        unset($this->log_debug_messages);
        unset($this->encryption_key);
        unset($this->cli);
        unset($this->company_id);
        unset($this->user_id);
    }

    public function getCompanyParentId() { return $this->companyparent_id; }
    public function getCompanyId() { return $this->company_id; }
    public function getUserId() { return $this->user_id; }
    public function getJobId() { return $this->job_id; }

    /**
     * index
     *
     * execute this business action.
     *
     * @param $user_id
     * @param $company_id
     * @throws Exception
     */
    public function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
        if ( strtoupper(GetStringValue($user_id)) === 'NULL' ) $user_id = "";
        if ( strtoupper(GetStringValue($company_id)) === 'NULL' ) $company_id = "";
        if ( strtoupper(GetStringValue($companyparent_id)) === 'NULL' ) $companyparent_id = "";
        if ( strtoupper(GetStringValue($job_id)) === 'NULL' ) $job_id = "";

        $this->companyparent_id = GetStringValue($companyparent_id);
        $this->company_id = GetStringValue($company_id);
        $this->user_id = GetStringValue($user_id);
        $this->job_id = GetStringValue($job_id);

        LogIt("A2PWorker", "companyparent_id[{$companyparent_id}] [".$this->companyparent_id."]");
        LogIt("A2PWorker", "company_id[{$company_id}] [".$this->company_id."]");
        LogIt("A2PWorker", "user_id[{$user_id}] [".$this->user_id."]");
        LogIt("A2PWorker", "job_id[{$job_id}] [".$this->job_id."]");

        // Make sure we have our min amount of data to run.
        if ( $this->companyparent_id === '' && $this->company_id === '' )
            throw new Exception("Background job must have company_id or companyparent_id to execute.");

        // Fill in the companyparent_id if we don't have one.
        if ( GetStringValue($company_id) !== '' ) {
            if (GetStringValue($companyparent_id) === '') {
                $this->companyparent_id = GetCompanyParentId($this->company_id);
            }
        }

        if ( $this->send_notifications) $this->notify_status_update('STARTING');

        // Ensure we have the encryption key in the cache
        if ( GetStringValue($this->company_id) !== '' )
        {
            // Load the company encryption key if we have a company id.
            $this->ci->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
            $this->encryption_key = $this->ci->cache->get("crypto_{$this->company_id}");
            if ( GetStringValue($this->encryption_key) === 'FALSE' )
            {
                $this->encryption_key = GetCompanyEncryptionKey($this->company_id);
                $this->ci->cache->save("crypto_{$this->company_id}", $this->encryption_key, 300);
            }
        }
        if ( GetStringValue($this->company_id) === '' )
        {
            if ( GetStringValue($this->companyparent_id) !== '' )
            {
                // Load the companyparent encryption key if we have no company_id, but but we do have
                // a companyparent_id.
                $this->ci->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
                $this->encryption_key = $this->ci->cache->get("crypto_{$this->companyparent_id}");
                if ( GetStringValue($this->encryption_key) === 'FALSE' )
                {
                    $this->encryption_key = GetCompanyParentEncryptionKey($this->companyparent_id);
                    $this->ci->cache->save("crypto_{$this->companyparent_id}", $this->encryption_key, 300);
                }
            }
        }



        if ( strtoupper(GetAppOption(LOG_DEBUG_MESSAGES)) === 'TRUE' ) $this->log_debug_messages = true;

        $this->debug("Worker [".get_called_class()."] starting.");

        // Tell postgres how much memory we want it to allocated while running this worker.
        if ( GetAppOption(PSQL_WORK_MEM) !== '' )
        {
            $work_mem = $this->Tuning_model->work_mem();
            $this->debug("Worker [".get_called_class()."] PSQL_WORK_MEM [{$work_mem}] at launch.");
            if ( $work_mem !== GetAppOption(PSQL_WORK_MEM) )
            {
                $this->Tuning_model->set_work_mem(GetAppOption(PSQL_WORK_MEM));
                $work_mem = $this->Tuning_model->work_mem();
            }
            $this->debug("Worker [".get_called_class()."] PSQL_WORK_MEM [{$work_mem}] at run.");
        }

        // Tell PHP how much memory we want it to allocate while running this worker.
        // If we have set a value.
        if ( GetAppOption(ONE_OFF_DYNO_PHP_MEMORY_LIMIT) !== '' )
        {
            $current_memory_limit = getStringValue(ini_get('memory_limit'));
            $desired_memory_limit = GetAppOption(ONE_OFF_DYNO_PHP_MEMORY_LIMIT);
            LogIt("The memory limit is [{$current_memory_limit}] right now.");
            LogIt("current[{$current_memory_limit}] wanted[{$desired_memory_limit}]");

            if ( $current_memory_limit !== $desired_memory_limit )
            {
                ini_set('memory_limit', $desired_memory_limit);
                $current_memory_limit = getStringValue(ini_get('memory_limit'));
                LogIt("current[{$current_memory_limit}] wanted[{$desired_memory_limit}]");
            }
        }

        // Notify any users logged in as the A2P company that someone is starting a
        // background task.
        if ( $this->send_notifications ) NotifyCompanyChannel(A2P_COMPANY_ID, 'admin_dashboard_task');

    }

    /**
     * verbose
     *
     * Run this action and allow it to write debug output to the screen.
     *
     * @param $user_id
     * @param $company_id
     * @throws Exception
     */
    public function verbose( $user_id, $company_id='', $companyparent_id='' )
    {
        try
        {
            $this->debug = true;
            $this->index( $user_id, $company_id, $companyparent_id);
        }catch(Exception $e)
        {
            throw $e;
        }
    }

    /**
     * debug
     *
     * if debug is enabled, print the message to stdout.
     * @param $message
     */
    protected function debug($message ) {

        // debug
        //
        //
        // ------------------------------------------------------------

        // Keep our debug messages if so configured.
        if ( $this->log_debug_messages && GetStringValue($this->company_id) !== '' )
        {
            LogIt( get_called_class(), $message, "", $this->user_id, $this->company_id, null );
        }

        if ( $this->debug && $this->cli) { print "{$message}\n"; }
        if ( $this->debug && ! $this->cli) { pprint_r("{$message}\n"); }

        // Only write simple output to recent activity log.
        $type = gettype($message);
        if ( $type === 'boolean' || $type === 'integer' || $type === 'double' || $type === 'string' )
        {
            $this->ci->Wizard_model->update_activity($this->company_id, trim($message));
        }
    }

    /**
     * timer
     *
     * Each time this function is called, it will report how long
     * the previous item had been running.  If you pass in "end" then
     * then you will get a summary of the full runtime from first timer
     * to "end".
     *
     * Works independently from DEBUG.  Debug does not imply timers.
     * timers will write to STDOUT even if debug is off.
     *
     * @param $code
     */
    protected function timer($code, $stdout=false)
    {

        // timer
        //
        // Each time this function is called, it will report how long
        // the previous item had been running.  If you pass in "end" then
        // then you will get a summary of the full runtime from first timer
        // to "end".
        //
        // Works independently from DEBUG.  Debug does not imply timers.
        // timers will write to STDOUT even if debug is off.
        // ------------------------------------------------------------

        if (! $this->timers) return;

        if ( ! $this->timer_array ) $this->timer_array = array();
        if ( count($this->timer_array) === 0 ) {
            $this->timer_array[$code] = time();
        }elseif ( $code === 'end' ) {

            if ( ! empty($this->timer_array) )
            {
                $keys = array_keys($this->timer_array);
                $first_key = $keys[0];

                $output = "";

                $seconds = round(abs(time() - $this->timer_array[$first_key]),2);
                $minutes = round(abs(time() - $this->timer_array[$first_key]) / 60,2);
                if ( $minutes < 1 ) {


                    if ( $seconds < 0 ) $output = "< 1 second";
                    if ( $seconds > 0 ) $output =  "{$seconds} second(s)";


                }else{
                    $output = "{$minutes} minute(s)";
                }
                if ( $output !== '' && $stdout ) print $output . PHP_EOL;

                // The user told us to end.  smoke the timer array.
                $this->timer_array = array();

                return trim($output);
            }

        }
        else
        {

            $keys = array_keys($this->timer_array);
            $last_key_index = count($keys) - 1;
            $last_key = $keys[$last_key_index];

            $seconds = round(abs(time() - $this->timer_array[$last_key]),2);
            $minutes = round(abs(time() - $this->timer_array[$last_key]) / 60,2);

            $output = "";

            if ( $minutes < 1 ) {
                if ( $seconds <= 0 ) $output = "Timer [{$code}]: < 1 second";
                if ( $seconds > 0 ) $output = "Timer [{$code}]: {$seconds} second(s)";
            }else{
                $output  = "Timer [{$code}]: {$minutes} minute(s)";
            }

            if ( $output !== '' && $stdout ) print $output . PHP_EOL;

            $this->timer_array[$code] = time();
        }
    }

    /**
     * notify_status_update
     *
     * This function will send a text message and job id to all A2P users
     * currently connected to the corresponding company channel.  What that
     * browser does with the notification is up to the receiving browser.
     *
     * The js_function will be executed, if available, and will receive the
     * payload as JSON encoded string.
     *
     * @param $notification_key
     * @param null $job_id
     */
    protected function notify_status_update($notification_key, $replacefor=array(), $job_id=null )
    {
        if ( $job_id === null ) $job_id = $this->job_id;



        // Just exit if we don't have all the data we need.
        if ( getStringValue($this->company_id) === '' ) return;
        if ( getStringValue($job_id) === '' ) return;
        if ( getStringValue($notification_key) === '' ) return;


        $group = strtolower(get_called_class());
        $words = $this->Verbiage_model->get($group, $notification_key);
        $age = $this->Queue_model->get_job_age($job_id);

        // Do a replace for on the words if we have a replacefor array.
        if ( ! empty($replacefor) )
        {
            foreach($replacefor as $key=>$value)
            {
                $words = replacefor($words, $key, $value);
            }
        }

        // Save the status notification to the wizard table so we can use it elsewhere.
        SetWizardStatusUpdate($this->company_id, $words);

        // Good lord, this ate an hour of my time.  Maybe logging when a notification
        // has no verbiage I will catch it sooner next time.
        if ( GetStringValue($words) )
        {
            LogIt("NOTICE", "Missing verbiage record for notification_key[{$notification_key}] group[{$group}].");
        }

        $payload = array();
        $payload['JobId'] = $job_id;
        $payload['VerbiageGroup'] = $group;
        $payload['VerbiageKey'] = $notification_key;
        $payload['Age'] = $age;
        $payload['Words'] = $words;
        $payload['CompanyId'] = $this->company_id;
        NotifyCompanyChannelUpdate($this->company_id, 'dashboard_task', 'BackgroundTaskStatusMessageEventHandler', $payload);
    }

    /**
     * notify_parent_status_update
     *
     * Send a background task status update to dashboards listening for
     * their companyparent channel.
     *
     * @param $notification_key
     * @param array $replacefor
     * @param null $job_id
     */
    protected function notify_parent_status_update($notification_key, $replacefor=array(), $job_id=null )
    {
        if ( $job_id === null ) $job_id = $this->job_id;

        // Just exit if we don't have all the data we need.
        if ( getStringValue($this->getCompanyParentId()) === '' ) return;
        if ( getStringValue($this->getJobId()) === '' ) return;
        if ( getStringValue($notification_key) === '' ) return;

        LogIt('BAH:'.__FUNCTION__, "notification_key[{$notification_key}], job_id[{$job_id}]");

        $group = strtolower(get_called_class());
        $words = $this->Verbiage_model->get($group, $notification_key);
        $age = $this->Queue_model->get_job_age($job_id);

        // Do a replace for on the words if we have a replacefor array.
        if ( ! empty($replacefor) )
        {
            foreach($replacefor as $key=>$value)
            {
                $words = replacefor($words, $key, $value);
            }
        }

        // Good lord, this ate an hour of my time.  Maybe logging when a notification
        // has no verbiage I will catch it sooner next time.
        if ( GetStringValue($words) )
        {
            LogIt("NOTICE", "Missing verbiage record for notification_key[{$notification_key}] group[{$group}].");
        }

        $payload = array();
        $payload['JobId'] = $job_id;
        $payload['VerbiageGroup'] = $group;
        $payload['VerbiageKey'] = $notification_key;
        $payload['Age'] = $age;
        $payload['Words'] = $words;
        $payload['CompanyParentId'] = $this->getCompanyParentId();
        NotifyCompanyParentChannelUpdate($this->getCompanyParentId(), 'dashboard_task', 'BackgroundTaskStatusMessageEventHandler', $payload);
    }

}
class SecureController extends CI_Controller {

    protected       $timers;        // Turn timers on or off.
    protected 	    $timer_array;   // Collection of timers, if they are on.
    protected       $encryption_key;

    public function __construct() {
        parent::__construct();

        $this->timers       = true;        // Want to run timers?  Turn this on.
        $this->timer_array  = array();

        // If the user is not logged in, kindly show them the door.
        if ( ! IsLoggedIn() ) AccessDenied();

        // If the user is logged in, but their password is weak force them to reset it.
        if ( GetSessionValue('weak_password') === 'TRUE') WeakPassword();

        // Keep track of human activity and honor the configured session timeout.
        $this->_check_recent_activity();

    }
    protected function init($company_id='', $companyparent_id='')
    {
        $this->timers       = false;        // Want to run timers?  Turn this on.
        $this->timer_array  = array();

        if ( $company_id !== '' )
        {
            // Ensure we have the encryption key in the cache
            $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
            $this->encryption_key = $this->cache->get("crypto_{$company_id}");
            if ( GetStringValue($this->encryption_key) === 'FALSE' )
            {
                $this->encryption_key = GetCompanyEncryptionKey($company_id);
                $this->cache->save("crypto_{$company_id}", $this->encryption_key, 300);
            }
        }else if ( $companyparent_id !== '' )
        {
            // Ensure we have the encryption key in the cache
            $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
            $this->encryption_key = $this->cache->get("crypto_parent_{$companyparent_id}");
            if ( GetStringValue($this->encryption_key) === 'FALSE' )
            {
                $this->encryption_key = GetCompanyParentEncryptionKey($companyparent_id);
                $this->cache->save("crypto_parent_{$companyparent_id}", $this->encryption_key, 300);
            }
        }



    }
    protected function timer($code)
    {

        // timer
        //
        // Each time this function is called, it will report how long
        // the previous item had been running.  If you pass in "end" then
        // then you will get a summary of the full runtime from first timer
        // to "end".
        //
        // Works independently from DEBUG.  Debug does not imply timers.
        // timers will write to STDOUT even if debug is off.
        // ------------------------------------------------------------

        if (! $this->timers) return;

        if ( ! $this->timer_array ) $this->timer_array = array();
        if ( count($this->timer_array) === 0 ) {
            $this->timer_array[$code] = time();
        }elseif ( $code === 'end' ) {

            if ( ! empty($this->timer_array) )
            {
                $keys = array_keys($this->timer_array);
                $first_key = $keys[0];

                $seconds = round(abs(time() - $this->timer_array[$first_key]),2);
                $minutes = round(abs(time() - $this->timer_array[$first_key]) / 60,2);
                if ( $minutes < 1 ) {
                    if ( $seconds < 0 ) LogIt('< 1 second');
                    if ( $seconds > 0 ) LogIt("{$seconds} second(s)");
                }else{
                    LogIt("{$minutes} minute(s)");
                }
            }

        }else{

            $keys = array_keys($this->timer_array);
            $last_key_index = count($keys) - 1;
            $last_key = $keys[$last_key_index];

            $seconds = round(abs(time() - $this->timer_array[$last_key]),2);
            $minutes = round(abs(time() - $this->timer_array[$last_key]) / 60,2);

            if ( $minutes < 1 ) {
                if ( $seconds <= 0 ) LogIt("Timer [{$code}]: < 1 second");
                if ( $seconds > 0 ) LogIt("Timer [{$code}]: {$seconds} second(s)");
            }else{
                LogIt("Timer [{$code}]: {$minutes} minute(s)");
            }

            $this->timer_array[$code] = time();

        }

    }
    private function _check_recent_activity() {

        // _check_recent_activity
        //
        // This function will keep track of "human" activity while ignoring
        // background/widget task activity.  If human activity becomes stale,
        // we will log the user out.
        // ---------------------------------------------------------------

        // What is our last true activity?  If we don't have one, set one now.
        $last_human_activity = GetSessionValue('last_human_activity');
        if ( $last_human_activity === '' )
        {
            SetSessionValue('last_human_activity', time());
            $last_human_activity = GetSessionValue('last_human_activity');
        }

        // When would we like our session to expire?
        // Get the session length in minutes and make sure it's some size larger than
        // what we would expect a background task repeat rate.
        $this->config->load('config');
        $session_expiration_in_seconds = $this->config->item('sess_expiration');
        $session_expiration_in_seconds = GetIntValue($session_expiration_in_seconds);
        if ( $session_expiration_in_seconds < 600 ) $session_expiration_in_seconds = 600; // Default 10 minutes!
        $session_expiration_in_minutes = ( $session_expiration_in_seconds / 60 );

        // How long have we been inactive?  Log the user out if it has been too long.
        $now = time();
        $diff = ($now - $last_human_activity) / 60;  // minutes since last activity.
        if ( $diff > $session_expiration_in_minutes )
        {
            // You have been active too long!  Kick them out.
            $this->session->sess_destroy();
            redirect( base_url() . 'auth/login');
            exit;
        }

        // Note human activity.  Human activity is any activity not happening
        // on the WIDGETTASK controller.
        if ( strtoupper(get_called_class()) !== 'WIDGETTASK' )
        {
            SetSessionValue('last_human_activity', time());
        }
    }


}
