<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class QueueDirector
 *
 * This class is responsible for starting background tasks.
 *
 */
class QueueDirector extends CI_Controller
{
	// Set debug to true and we will no longer review the output of the
	// jobs, but rather echo them to the output conosle.
	private $debug = false;						// Write to STDOUT as we process.

	private $max_job_runtime;					// How long can a job run before we kill it off as failed.
	private $max_running_jobs;					// How many jobs can we run at the exact same time.
	private $processor_sleep;					// How long between heartbeats.
	private $failure_check_timestamp;			// Last time we checked on the status of the running jobs.
	private $failure_check;						// How long do we wait between checking on the status of running jobs.
	private $delay_processor = null;			// If we have trouble launching a job, how long do we delay processing before we try again.

	private $reboot_timestamp;					// Last time we checked on the status of rebooting the worker.
	private $reboot_check;						// How long do we pause between checking to see if we should reboot worker.1
	private $reboot_window_start;				// What time of the day does our reboot window start.
	private $reboot_window_end;					// What time of the day does our reboot window end.

	private $ao_delay_queue_until;				// Cache this App Option locally here while running.
	private $ao_date_of_last_worker_reboot;		// Cache this App Option locally here while running.

    private $log_at_level;                      // If we are running at this release level, add a little extra logging.


    /**
     * QueueDirector constructor.
     *
     * Initialize the director and set our running properties
     * to values found in the config file.
     *
     */
	function __construct()
    {
        // Construct our parent class
        parent::__construct();

        // The queue director can only be executed from command line.
		if ( ! is_cli() ) {
		    Error404("QueueDirector may not be accessed from the web layer.");
			return;
		}

		// Pull the queue settings from a config file.
		$this->max_job_runtime      = GetConfigValue("max_job_runtime", "queue");
		$this->processor_sleep      = GetConfigValue("processor_sleep", "queue");
		$this->failure_check        = GetConfigValue("failure_check", "queue");
		$this->delay_processor      = GetConfigValue("delay_processor", "queue");
		$this->reboot_check         = GetConfigValue("reboot_check", "queue");
		$this->reboot_window_start  = GetConfigValue("reboot_window_start", "queue");
		$this->reboot_window_end    = GetConfigValue("reboot_window_end", "queue");

		// MAX ASYNC JOBS
		// How many background jobs can we run at once?  No less than zero I imagine.
		$this->max_running_jobs = MAX_ASYNC_JOBS;
		if ( getIntValue($this->max_running_jobs) < 0 ) $this->max_running_jobs = 0;

		// Initialize the failure check timestamp as of right now when we boot.
		$this->failure_check_timestamp = time();
		$this->reboot_timestamp = time();

		// Load our application options into memory on boot to cut down on db calls.
		$this->ao_delay_queue_until = GetAppOption(DELAY_QUEUE_UNTIL);
		$this->ao_date_of_last_worker_reboot = GetAppOption(DATE_OF_LAST_WORKER_REBOOT);

		// Turn on additional logging if we are here.
        $this->log_at_level = '';
    }

    /**
     * index
     *
     * This is the main "game loop" for the director.  When it wakes up it will
     * monitor the background jobs specified in the ProcessQueue table.  It will
     * to through the  DELAY, CHECK, START, SLEEP life cycle.
     */
    function index()
    {
		$job_id = null;

		// Detect HEROKU and set the application options to activate
        // heroku integration if available.
		$this->_initDynoSupport();


		// Loop Forever.
		while ( true ) {

			// DELAY: Delay the processor if need be.
			$this->_delay_queue();

			// CHECK: Review data and take special action as needed.
			$job_count = $this->_review_running_jobs();	// Fail any jobs that have ended in life, but not in the queue.
			$this->_review_reboot_window();				// Check to see if time and safe to reboot the worker.

			// START: Start more jobs if and only if we have not hit max_running_jobs
			if ( $job_count < $this->max_running_jobs)
			{
				// Start up a new jobs until we hit max_running_jobs.
				while ($job = $this->Queue_model->get_next_job(true)) {

					try
					{
						// What is the ID number of the job we are running.
						$job_id = getArrayIntValue("Id", $job);

						// Execute the QueueProcessor for this job.
						if ( HasOneOffDynoSupport() ) $this->_start_heroku_job($job_id);
						if ( ! HasOneOffDynoSupport() ) $this->_start_local_job($job_id);

						if ( $this->debug ) print "[{$job_id}]+";
						sleep($this->processor_sleep);

					}
					catch(Exception $e)
					{

						// I don't know how we would ever get here, but just to be safe.
						$message = getStringValue($e->getMessage());
						$message = "A2P-INTERNAL: An exception has been detected and job [".getStringValue($job_id)."] will be shutdown and failed because: " . $message;
						LogIt(get_class() . ": exception: ". $message );
						if ( $this->debug ) print "exception! [".$message."]\n";
						if ( $this->debug ) print "[{$job_id}]x";
						FailJob($job_id, $message);
						StopProcess($job_id);

					}

					// If we reached our max running jobs, break out of
					// the "start" loop into the "wait" loop.
					$job_count = $this->Queue_model->get_running_jobs();
					if ( $job_count > $this->max_running_jobs ) break;

				}

			}
			if ( $this->debug ) print ".";
			sleep($this->processor_sleep); // rest a bit before we check again.
		}

    }

    /**
     * verbose
     *
     * If you start the QueueDirector with "verbose" rather than "index"
     * the director will tell you the running operating values and
     * write characters to the screen as it processes so you can see
     * what it's doing live.
     *
     */
    public function verbose()
    {
		// Turn on debugging to STDOUT
        $this->debug = true;

		// Write some helpful information before we start
		print_r("\n");
		print_r("QUEUE DIRECTOR\n");
		print_r(" Background jobs may execute no longer than [".( $this->max_job_runtime / SECONDS_PER_MINUTE )."] minutes else they will be failed.\n");
		print_r(" No more than [{$this->max_running_jobs}] background job may run simultaniously.\n");
		print_r(" The processor runs every [{$this->processor_sleep}] seconds.\n");
		print_r(" Running jobs will be reviewed every [". ($this->failure_check / SECONDS_PER_MINUTE)."] minutes.\n");
		print_r("\n");
		print_r("\n");
		print_r("LEGEND\n");
		print_r(" . clock tick\n");
		print_r(" + job started\n");
		print_r(" _ processor delayed\n");
		print_r(" * running job review\n");
		print_r(" x job failed\n");
		print_r(" v reboot check\n");

		// Run the director
		$this->index();

    }

    /**
     * _delay_queue
     *
     * There is an AppOption indicating that the queue should be delayed until
     * a certain time.  Loop until that time has passed or the delay indicator
     * has been removed.
     *
     * This function is used to slow down the game loop if we find ourselves
     * in a situation where we are having communication problems with external
     * services.
     *
     */
	private function _delay_queue()
    {

		while( $this->ao_delay_queue_until != "" && time() <= getIntValue($this->ao_delay_queue_until) )
		{
			if ( $this->debug ) print "_";
			sleep($this->processor_sleep);
		}
		if ( $this->ao_delay_queue_until != "" )
		{
			$this->ao_delay_queue_until = "";
			RemoveAppOption(DELAY_QUEUE_UNTIL);
		}

	}
	private function _start_heroku_job( $job_id ) {

		// _start_heroku_job
		//
		// Start a specific job inside a one-off heroku dyno.
		// ------------------------------------------------------------

		// Locate our runtime copy of php.
		$php = `which php`;
		$php = trim($php);
        if ( APP_NAME === $this->log_at_level) LogIt(get_class() . ": which php.", $php);

		// Build a command that will start the QueueProcessor for this job.
		$cmd = "{$php} -q /app/index.php cli/QueueProcessor index {$job_id}";

		// Start a new dyno and run the processor.
		$results = $this->HerokuDynoRequest_model->create_oneoff_dyno(APP_NAME, $cmd);
        if ( APP_NAME === $this->log_at_level) LogIt(get_class() . ": create results.", json_encode($results));
		$dyno_name = getArrayStringValue("DynoName", $results);
        if ( APP_NAME === $this->log_at_level) LogIt(get_class() . ": dyno_name", $dyno_name);
		$this->Queue_model->set_process_id($job_id, $dyno_name);

		// Report we are starting a dyno.
        $this->notify_dyno_status( 'init', $job_id);

		// Wait one second and check the dyno status.
        // If it's not done in one second, wait two more.
        // After that, check every three seconds.
        $wait_seconds = 1;
        $total_wait_seconds = 0;
		$state = "starting";
        while($state != "up")
        {
            sleep($wait_seconds);
            $total_wait_seconds = $total_wait_seconds + $wait_seconds;
            if ( $wait_seconds === 2 ) $wait_seconds = 3;
            if ( $wait_seconds === 1 ) $wait_seconds = 2;

            // Attempt to get the state of the dyno.
            $results = $this->HerokuDynoRequest_model->get_dyno_info(APP_NAME, $dyno_name);
            $state = strtolower(getArrayStringValue("state", $results));
            if ( APP_NAME === $this->log_at_level) LogIt(get_class() . ": State after [{$total_wait_seconds}] second(s)", $state);

            // It's possible that the job finished while we were waiting to ask about it again.
            // If that happened, logically change the state to 'up' so we can break out of the loop.
            if ( $state === '' || $state === 'complete' )
            {
                $job = $this->Queue_model->get_job($job_id);
                $end_time = GetArrayStringValue("EndTime", $job);
                if ( $end_time !== '' )
                {
                    if ( APP_NAME === $this->log_at_level) LogIt(get_class() . ": The ProcessQueue table says this job is over.  We should exit.");
                    $state = 'up';
                }
            }

            // If we are not done in four minutes, we had better put the breaks on.
            if ( $total_wait_seconds > 240 ) break;
        }

		// If we could not start the job, delay the queue.
		if ( strtolower($state) !== 'up' )
		{
			LogIt(get_class() . ": Unable to start dyno.", json_encode($results));

			// Stop the dyno, just to be sure it's not running.
            if ( APP_NAME === $this->log_at_level) LogIt(get_class() . ": Stopping dyno.", $dyno_name);
            $this->HerokuDynoRequest_model->stop_dyno(APP_NAME, $dyno_name);


            // We will now pause the queue director for a bit due to this failure.
            if ( APP_NAME === $this->log_at_level) LogIt(get_class() . ": Delaying the queue director.");
			$retry = time() + $this->delay_processor;
			$this->ao_delay_queue_until = $retry;
			SetAppOption(DELAY_QUEUE_UNTIL, $retry);

			// Reset this job so it can try again in a bit.
            if ( APP_NAME === $this->log_at_level) LogIt(get_class() . ": Reset JobId", $job_id);
			$this->Queue_model->reset_job($job_id);

		}


	}

    /**
     * _restart_heroku_worker
     *
     * This function will restart all Heroku workers on the
     * running application.  There should only be one worker which
     * is the queue director.
     */
	private function _restart_heroku_worker( )
    {
		$apps = $this->HerokuDynoRequest_model->get_dynos(APP_NAME);
		foreach($apps as $app)
		{
			$type = strtolower(getArrayStringValue("type", $app));
			if ( $type == "worker" )
			{
				$name = getArrayStringValue("name", $app);
				$this->HerokuDynoRequest_model->restart_dyno(APP_NAME, $name);
			}
		}
	}

    /**
     * _start_local_job
     *
     * Start a specific job as a background process on the
     * current server running the director.
     * @param $job_id
     */
	private function _start_local_job( $job_id )
    {
		// Locate our runtime copy of php.
		$php = `which php`;
		$php = trim($php);

		// Start a new shell background process with the QueueProcessor in it.
		$cmd = "nohup " . $php . " " . FCPATH . "index.php cli/QueueProcessor index {$job_id}  1>/dev/null 2>/dev/null &";
		exec($cmd);

	}

    /**
     * _review_reboot_window
     *
     * Heroku will reboot our application every 24 hours.  We would rather
     * do that ourselves than have them do it.  If we do it, we can do it
     * when we have nothing mid-process.  This function will look for a good
     * time to do the reboot.
     *
     *  - Only do the reboot check every so often.  ( 10 minutes or so rather than every 3 seconds. )
     *  - Only consider doing a reboot if we are our reboot window.  ( example 1am-4am )
     *  - Only reboot once per hour during the reboot window.
     *  - Never reboot if we are processing a job on the queue.
     *
     * @throws Exception
     */
	private function _review_reboot_window()
    {

		// Throttle how often we do a reboot check.
		$diff = time() - $this->reboot_timestamp;
		if ( $diff < $this->reboot_check ) return;
		$this->reboot_timestamp = time();
		if ( $this->debug ) print "v";

		// We must be in our reboot window.
		$currentTime = new DateTime(null, new DateTimeZone(PREFERED_TIMEZONE));
		$startTime = new DateTime($this->reboot_window_start, new DateTimeZone(PREFERED_TIMEZONE));
		$endTime = new DateTime($this->reboot_window_end, new DateTimeZone(PREFERED_TIMEZONE));
		if ($currentTime < $startTime || $currentTime > $endTime) { /*print "\nnot in window\n";*/ return; }

		// Compare the current timestamp with the last reboot timestamp.
		// If they match, then we have alrady checked this hour of the window.
		$last_restart = $this->ao_date_of_last_worker_reboot;
		$current_hour = new DateTime(null, new DateTimeZone(PREFERED_TIMEZONE));
		$current_hour = date_format($current_hour, 'm/d/Y H:00:00');
		if ( $last_restart == $current_hour ) { /*print "\nalready ran this hour\n";*/ return; }

		// If there are running jobs, wait for the next pass.
		$jobs = $this->Queue_model->get_running_jobs();
		if ( count($jobs) != 0 ) { /*print "\nthere were jobs running\n";*/ return; }

		if ( ! HasDynoSupport() ) { /*print "\nlocal reboots not supported\n";*/ return; }

		// This is it people!  Reboot the director.
		$this->ao_date_of_last_worker_reboot = $current_hour;
		SetAppOption( DATE_OF_LAST_WORKER_REBOOT, $current_hour  );

		// good bye
		if ( HasDynoSupport() ) $this->_restart_heroku_worker();

	}

    /**
     * _review_running_jobs
     *
     * This function will take a look at the queue and find jobs that
     * are currently still running.  We will then see how long they
     * have been running.  If they have exceeded our max runtime, then we
     * will gracefully shut them down.
     *
     * @return int
     */
	private function _review_running_jobs()
    {
		// Decide if a running job that has not exceeded the max run time
		// should be investigated.  Only do this every X minutes.
		$confirm_job_running = false;
		$diff = time() - $this->failure_check_timestamp;
		if ( $diff > $this->failure_check ) $confirm_job_running = true;
		if ( $confirm_job_running && $this->debug ) print "*";

		// Grab the jobs that are "running" per the queue and evaluate them.
		$jobs = $this->Queue_model->get_running_jobs();
		foreach($jobs as $job)
		{
			$job_id = getArrayStringValue("JobId", $job);
			$pid = getArrayStringValue("ProcessId", $job);
			$minutes_running = getArrayStringValue("Minutes Running", $job);

			// Make sure we don't run past our cut off point.
			if ( getIntValue($minutes_running) > ( $this->max_job_runtime / SECONDS_PER_MINUTE ) )
			{
				// Oh, well that's not good.  We have been running longer than
				// the timeframe we think any job should last.  Best be
				// killing it.
				if ( IsProcessRunning($pid) )
				{
					$message = "A2P-INTERNAL: Max runtime exceeded.  Job is still running, but we must shut it down. [{$job_id}/{$pid}] ";
					if ( $this->debug ) print "[{$job_id}]x";
					FailJob($job_id, $message);
					StopProcess($pid);
				}
				else
				{
					$message = "A2P-INTERNAL: Max runtime exceeded.  Job is was not running and it did not finish gracefully. [{$job_id}/{$pid}]";
					if ( $this->debug ) print "[{$job_id}]x";
					FailJob($job_id, $message);
				}

				// Move on to the next job.
				continue;
			}

			if ( $confirm_job_running )
			{
				// We have not checked to see if a job is still running in a while, let's do that now.
				if ( ! IsProcessRunning($pid) )
				{
					// wait just a spell.  Then see if the job is ended.  If it's not, then we error.
					// If it has ended, don't do anything.

					sleep(1);

					$job = $this->Queue_model->get_job($job_id);
				    if ( getArrayStringValue("EndTime", $job) == "" )
					{
						$message = "A2P-INTERNAL: Queue indicates the job is still processing, but it's no longer running.";
						if ( $this->debug ) print "[{$job_id}]x";
						FailJob($job_id, $message);
						continue;
					}

				}
			}
		}

		// If we checked to see that the running jobs were really active, then
		// reset our timestamp so we wait a bit longer before the next check.
		if ( $confirm_job_running ) $this->failure_check_timestamp = time();

		return count($jobs);
	}


    /**
     * _initDynoSupport
     *
     * This function will double check to see if we can or cannot pull a list of
     * Dynos from Heroku.  If we can, we will override the DYNO_SUPPORT_ENABLED
     * App Option.
     *
     */
	private function _initDynoSupport() {

		if ( $this->debug ) print "Initializing Dyno Support. ";

        $dynos = $this->HerokuDynoRequest_model->get_dynos( APP_NAME );

        // Update the DYNO_SUPPORT_ENABLED app option based on if we can or
        // cannot pull a list of dynos.
        if ( ! empty($dynos) )
        {
            SetAppOption(DYNO_SUPPORT_ENABLED, "TRUE");
        }
        else
        {
            SetAppOption(DYNO_SUPPORT_ENABLED, "FALSE");
        }

		if ( $this->debug )
		{
			if ( HasDynoSupport() ) print "HasDynoSupport [on]\n";
			if ( ! HasDynoSupport() ) print "HasDynoSupport [off]\n";
			if ( HasOneOffDynoSupport() ) print "HasOneOffDynoSupport [on]\n";
			if ( ! HasOneOffDynoSupport() ) print "HasOneOffDynoSupport [off]\n";
		}

	}

    /**
     * notify_dyno_status
     *
     * Send a notification indicating the status of the heroku dyno we
     * are trying to spawn.
     *
     * @param $status
     * @param $job_id
     */
    private function notify_dyno_status($status, $job_id)
    {
        // Just exit if we don't have all the data we need.
        if ( getStringValue($job_id) === '' ) return;
        if ( getStringValue($status) === '' ) return;

        // Get the company_id out of the payload on the job.  It's the second parameter.
        $job = $this->Queue_model->get_job($job_id);
        $payload = json_decode(getArrayStringValue("Payload", $job));
        $company_id = GetArrayStringValue("1", $payload);
        $companyparent_id = GetArrayStringValue("2", $payload);

        $notification_key = "DYNO_STATUS";
        if ( $status === 'init' ) $notification_key = "DYNO_INITIALIZING";

        $group = strtolower(get_called_class());
        $words = $this->Verbiage_model->get($group, $notification_key);
        $words = replacefor($words, "{STATUS}", $status);
        $age = $this->Queue_model->get_job_age($job_id);

        $payload = array();
        $payload['JobId'] = $job_id;
        $payload['VerbiageGroup'] = $group;
        $payload['VerbiageKey'] = $notification_key;
        $payload['Age'] = $age;
        $payload['Words'] = $words;
        if ($company_id !== '' )
        {
            $payload['CompanyId'] = $company_id;
            NotifyCompanyChannelUpdate($company_id, 'dashboard_task', 'BackgroundTaskStatusMessageEventHandler', $payload);
        }
        else if ($company_id === '' && $companyparent_id !== '' )
        {
            $payload['CompanyParentId'] = $companyparent_id;
            NotifyCompanyParentChannelUpdate($companyparent_id, 'dashboard_task', 'BackgroundTaskStatusMessageEventHandler', $payload);
        }
    }
}

/* End of file QueueDirector.php */
/* Location: ./application/controllers/cli/QueueDirector.php */
