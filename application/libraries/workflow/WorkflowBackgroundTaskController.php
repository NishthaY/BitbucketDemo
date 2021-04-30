<?php defined('BASEPATH') OR exit('No direct script access allowed');

class WorkflowBackgroundTaskController extends A2PWorkflowStep {

    public $wf_name;
    public $wf_stepname;
    public $verbiage_group;
    public $failed_notification_function;

    public function __construct( $cli=true )
    {
        // Construct our parent class
        parent::__construct($cli);

        $this->wf_name = '';
        $this->wf_stepname = '';
        $this->verbiage_group = '';
        $this->failed_notification_function = '';
    }
    function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
        parent::index($user_id, $company_id, $companyparent_id, $job_id);
    }
    public function execute()
    {
        $task = null;
        try
        {
            // Fail in a critical way that will trigger an A2P red alert message if these are missing.
            // If they are missing, you have a coding issue that needs to be corrected.
            if ( GetStringValue($this->wf_name) === '' ) { print "Workflow background task missing required input: wf_name"; exit; }
            if ( GetStringValue($this->wf_stepname) === '' ) { print "Workflow background task missing required input: wf_stepname"; exit; }

            NotificationSetStatusMessage($this->verbiage_group, 'STARTING', $this->job_id, $this->identifier, $this->identifier_type);

            // Test critical failure.
            //print "Headlee says let's test a runtime error.";
            //exit;

            // Test wait for feedback
            //throw new A2PWorkflowWaitingException("Get help.");

            // Test runtime failure
            //throw new Exception("We got an exception. Do something with that information.");

            // BUSINESS LOGIC
            // Do business logic.  When done, clear the status update messages.

            $task_name = GetWorkflowStateProperty($this->wf_name, $this->wf_stepname, 'Library');
            if ( file_exists(APPPATH."libraries/workflow/{$task_name}.php") )
            {
                $this->load->library("workflow/{$task_name}");
                $task = new $task_name();
                $task->identifier = $this->identifier;
                $task->identifier_type = $this->identifier_type;
                $task->job_id = $this->getJobId();
                $task->user_id = $this->getUserId();
                $task->verbiage_group = $this->verbiage_group;
                $task->debug = $this->debug;
                $task->wf_name = $this->wf_name;
                $task->wf_stepname = $this->wf_stepname;

                $task->execute();
                $task = null;

            }
            else
            {
                throw new Exception("Unable to locate task [{$task_name}]");
            }

            $this->debug("done.");

            // Update status notification.
            NotificationSetStatusMessage($this->verbiage_group, 'EMPTY_STRING', $this->job_id, $this->identifier, $this->identifier_type);

        }
        catch(A2PWorkflowWaitingException $e)
        {
            // Mark this workflow step waiting.
            WorkflowStateSetWaiting($this->identifier, $this->identifier_type, $this->wf_name);

            // Send an email to the user that spawned this task.
            if ( $this->getJobId() !== '' )
            {
                // Yes, we have a job_id which means that was executed from the queue.
                $function_name = $e->getTag();
                if ( $function_name !== '' && function_exists($function_name) )
                {
                    // Yes!  We have identified a function based on the information provided
                    // in the exception.  Send it!
                    $function_name($this->getUserId(), $this->getCompanyId(), $this->getCompanyParentId());
                }
            }

            // Log why we are waiting.
            $payload = array();
            $payload['wf'] = $this->wf_name;
            $payload['wf_step'] = $this->wf_stepname;
            $payload['user'] = $this->getJobId();
            $payload['identifier'] = $this->identifier;
            $payload['identifier_type'] = $this->identifier_type;
            LogIt("Workflow Waiting", $e->getMessage(), $payload);

            // Update status notification.
            NotificationSetStatusMessage($this->verbiage_group, 'EMPTY_STRING', $this->job_id, $this->identifier, $this->identifier_type);

        }
        catch(Exception $e)
        {
            // Write to STDOUT so the queue processor can deal with it as an error.
            print $e->getMessage();

            // This step has failed.  Rollback.
            WorkflowStateRollback($this->identifier, $this->identifier_type, $this->wf_name, $this->wf_stepname);

            // Notify the user via email.
            if ( GetStringValue($this->failed_notification_function) !== '' )
            {
                $fn_name = $this->failed_notification_function;
                $fn_name($this->getUserId(), $this->getCompanyId(), $this->getCompanyParentId());
            }


            // Log why we failed.
            $payload = array();
            $payload['wf'] = $this->wf_name;
            $payload['wf_step'] = $this->wf_stepname;
            $payload['user'] = $this->getJobId();
            $payload['identifier'] = $this->identifier;
            $payload['identifier_type'] = $this->identifier_type;
            LogIt("Workflow Failed", $e->getMessage(), $payload);

            // Update status notification.
            NotificationSetStatusMessage($this->verbiage_group, 'EMPTY_STRING', $this->job_id, $this->identifier, $this->identifier_type);

        }

    }


}

/* End of file WorkflowTask.php */
/* Location: ./application/controllers/cli/WorkflowTask.php */
