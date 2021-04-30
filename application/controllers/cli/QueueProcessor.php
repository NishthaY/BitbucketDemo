<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class QueueProcessor
 *
 * This class is responsible for running a single background task.
 *
 */
class QueueProcessor extends CI_Controller
{
	// Set debug to true and we will no longer review the output of the
	// jobs, but rather echo them to the output console.
	private $debug = false;

	function __construct()
    {
        // Construct our parent class
        parent::__construct();

        // The queue processor can only be executed from command line.
        if ( ! is_cli() ) {
            Error404("QueueProcessor may NOT be accessed from the web layer.");
            return;
        }
    }

    function index( $job_id )
    {
		try {
            $job = $this->Queue_model->get_job($job_id);
            $payload = getArrayStringValue("Paylaod", $job);
            $controller = getArrayStringValue("Controller", $job);
            $function = getArrayStringValue("Function", $job);
            $start_time = getArrayStringValue("StartTime", $job);
            $end_time = getArrayStringValue("EndTime", $job);

            if ( $controller == "" )    throw new Exception("Missing required input controller");
            if ( $function == "" )      throw new Exception("Missing required input function");
            if ( $start_time == "" )    throw new Exception("Job not yet started by QueueDirector.");
            if ( $end_time != "" )
			{
				// Job already completed, get out of here.
				exit;
			}

			if ( ! HasOneOffDynoSupport() )
			{
				// If we are running locally, we will need to capture the PID of
				// this process before we start the child process.
				$this->Queue_model->set_process_id($job_id, getmypid());
			}

			// Run a CLI application.
			// We will wait for it to complete before we continue which is cool because
			// we are already in our own background process/dyno.
			$controller = getArrayStringValue("Controller", $job);
			$function = getArrayStringValue("Function", $job);
			$payload = getArrayStringValue("Payload", $job);
			StartProcess($job_id, $controller, $function, $payload);

			// Okay, we are all done here.
			$this->Queue_model->end_job($job_id);
			if ( $this->debug ) print "job complete [{$job_id}]\n";

			// A job just completed!  Release the queue, if it was being delayed.
			RemoveAppOption(DELAY_QUEUE_UNTIL);

		}
		catch(Exception $e)
		{
			$message = getStringValue($e->getMessage());
			if ( getStringValue($message) == "" ) $message = "A2P-INTERNAL: An exception has been detected and job [{$job_id}] will be shutdown.";
			LogIt(get_class() . ": exception. " . $message);

			if ( $this->debug ) print "exception! [".$message."]\n";
			FailJob($job_id, $message);
			StopProcess($job_id);
		}


    }

    /**
     * verbose
     *
     * Start the processor, but turn on verbose mode before you start.
     *
     * @param $job_id
     */
    public function verbose( $job_id )
    {
        $this->debug = true;
        $this->index($job_id);
    }

}

/* End of file QueueProcessor.php */
/* Location: ./application/controllers/cli/QueueProcessor.php */
