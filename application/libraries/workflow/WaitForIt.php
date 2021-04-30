<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class WaitForIt extends WorkflowLibrary
{
    //protected $ci;                        // See parent class for more information.
    //protected $cli;                       // See parent class for more information.
    //protected $company_id;                // See parent class for more information.
    //protected $companyparent_id;          // See parent class for more information.
    //protected $database_logging_enabled;  // See parent class for more information.
    //protected $debug;                     // See parent class for more information.
    //protected $encryption_key;            // See parent class for more information.
    //protected $identifier;                // See parent class for more information.
    //protected $identifier_type;           // See parent class for more information.
    //protected $job_id;                    // See parent class for more information.
    //protected $user_id;                   // See parent class for more information.
    //protected $verbiage_group;            // See parent class for more information.
    //protected $wf_name;                   // See parent class for more information.
    //protected $wf_stepname;               // See parent class for more information.

    public function execute()
    {
        try
        {
            LogIt(__FUNCTION__, "Sleeping just for a bit.");
            sleep(5);

            $value = GetAppOption('WaitForIt');
            if ( $value === 'continue' )
            {
                // We have already waited once, simulate this time
                // we got the information we were looking for and we can
                // continue.  Dump the app option so we make them
                // wait if they try again.
                $this->takeSnapshot();
                RemoveAppOption('WaitForIt');
            }
            else
            {
                // This is how you trigger the waiting screen if the background
                // task can't complete.
                throw new A2PWorkflowWaitingException("Ask for help!");
            }

        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function snapshot()
    {
        $this->addSnapshotList('app variable', GetAppOption('WaitForIt'));

        $data = [];
        $data[] = ['delivery_boy'=>'Fry', 'doctor'=>'Zoidberg'];
        $data[] = ['grandpa'=>'Rick', 'grandson'=>'Morty'];
        $this->addSnapshotData($data);

        $this->takeSnapshot();
    }

}
