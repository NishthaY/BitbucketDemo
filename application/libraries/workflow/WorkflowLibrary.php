<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class WorkflowLibrary {

    protected $ci;
    protected $cli;
    protected $company_id;
    protected $companyparent_id;
    protected $database_logging_enabled;
    protected $debug;
    protected $encryption_key;
    protected $identifier;
    protected $identifier_type;
    protected $job_id;
    protected $user_id;
    protected $verbiage_group;
    protected $wf_name;
    protected $wf_stepname;

    private $_snapshot_list;
    private $_snapshot_data;


    public function __construct()
    {
        $this->ci                           = &get_instance();
        $this->cli                          = is_cli();
        $this->company_id                   = '';
        $this->companyparent_id             = '';
        $this->database_logging_enabled     = false;
        $this->debug                        = false;
        $this->encryption_key               = '';
        $this->identifier                   = '';
        $this->identifier_type              = '';
        $this->job_id                       = '';
        $this->user_id                      = '';
        $this->verbiage_group               = '';

        $this->_snapshot_data               = array();
        $this->_snapshot_list               = array();

    }

    public function __set ( $property , $value )
    {
        $this->$property = $value;

        // As soon as we have both the identifier and the identifier_type, we can automatically
        // set some other values for the end user.
        if ( strtolower($property) === 'identifier' || strtolower($property) === 'identifier_type' )
        {
            if ( GetStringValue($this->identifier) !== '' && GetStringValue($this->identifier_type) !== '' )
            {
                // Set the company_id and the companyparent_id now.
                if ($this->identifier_type === 'companyparent' )
                {
                    $this->company_id = null;
                    $this->companyparent_id = $this->identifier;
                    $this->encryption_key = GetCompanyParentEncryptionKey($this->companyparent_id);
                }
                else if ( $this->identifier_type === 'company' )
                {
                    $this->company_id = $this->identifier;
                    $this->companyparent_id = null;
                    $this->encryption_key = GetCompanyEncryptionKey($this->company_id);
                }
            }
        }

    }

    public function __toString()
    {
        $retval = "";
        $this->cli  ? $retval .= 'cli[true]\n' : $retval .= 'cli[false]\n';
        $retval .= "company_id[{$this->company_id}]\n";
        $retval .= "companyparent_id[{$this->companyparent_id}]\n";
        $this->database_logging_enabled  ? $retval .= 'database_logging_enabled[true]\n' : $retval .= 'database_logging_enabled[false]\n';
        $retval .= "identifier[{$this->identifier}]\n";
        $retval .= "identifier_type[{$this->identifier_type}]\n";
        $retval .= "user_id[{$this->user_id}]\n";
        return $retval;
    }


    protected function debug( $message ) {

        // Keep our debug messages if so configured.
        if ( $this->database_logging_enabled )
        {
            LogIt( get_called_class(), $message, "", $this->user_id, $this->company_id, $this->companyparent_id );
        }

        if ( $this->debug && $this->cli) { print "{$message}\n"; }
        if ( $this->debug && ! $this->cli) { pprint_r("{$message}\n"); }

        // Only write simple output to recent activity log.
        $type = gettype($message);
        if ( $type === 'boolean' || $type === 'integer' || $type === 'double' || $type === 'string' )
        {
            UpdateWorkflowProgressProperty($this->identifier,$this->identifier_type,$this->wf_name,'recent_activity',$message);
        }
    }

    protected function addSnapshotList($key, $value)
    {
        if ( GetStringValue($key) === '' ) return;
        $this->_snapshot_list[$key] = GetStringValue($value);
    }

    protected function addSnapshotData($row_of_data = array())
    {
        if ( empty($row_of_data)) return;
        $this->_snapshot_data[] = $row_of_data;
    }
    public function takeSnapshot()
    {
        $controller = GetWorkflowStateProperty($this->wf_name, $this->wf_stepname, 'Controller');
        $library = GetWorkflowStateProperty($this->wf_name, $this->wf_stepname, 'Library');
        $this->addSnapshotList('wf_name', $this->wf_name);
        $this->addSnapshotList('wf_stepname', $this->wf_stepname);
        $this->addSnapshotList('wf_controller', $controller);
        $this->addSnapshotList('wf_library', $library);
        $this->addSnapshotList('wf_route', GetWorkflowStateProperty($this->wf_name, $this->wf_stepname, 'WaitingURI'));
        ArchiveHistoricalData($this->identifier, $this->identifier_type, $this->wf_stepname, $this->_snapshot_data, $this->_snapshot_list, $this->user_id);
    }

    /**
     * skip
     *
     * Sometimes we might need to run a workflow step, but the task the step does
     * is already complete.  If we spawn a background task, that could add a lot of
     * unneeded time to the process to spawn something up just to exit.
     *
     * If this function returns FALSE, then the background task needs to be started.
     * If this function returns TRUE, the data indicates the background task has already
     * been completed and does not need to be started.  In fact, it can be skipped.
     *
     * As a user implements their background task, override this function to add custom
     * logic which will decide if the should just mark the task as done and move on or
     * actually allow the background task to run.
     *
     * @return bool
     */
    public function skip()
    {
        return FALSE;
    }


    /**
     * rollback
     *
     * If this workflow step needs to rollback because of failure or if the
     * user elected too, implement this function.
     *
     */
    public function rollback()
    {

    }

    /**
     * snapshot
     *
     * If a workflow step enters a waiting state, then the user is going to
     * do something.  Maybe they will just confirm something or maybe they will
     * collect and organize data.  They are doing something.  Implement this
     * function to capture that data for support by using the snapshot helper functions
     * defined on this class.
     *
     */
    public function snapshot()
    {
        /*
        $this->addSnapshotList('author', 'Brian Headlee');

        $data = [];
        $data[] = ['delivery_boy'=>'Fry', 'doctor'=>'Zoidberg'];
        $data[] = ['grandpa'=>'Rick', 'grandson'=>'Morty'];
        $this->addSnapshotData($data);

        $this->takeSnapshot();
        */
    }


}
