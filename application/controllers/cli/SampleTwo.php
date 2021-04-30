<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/workflow/WorkflowBackgroundTaskController.php';


class SampleTwo extends WorkflowBackgroundTaskController
{
    public function __construct( $cli=true )
    {
        // Construct our parent class
        parent::__construct($cli);

        $this->wf_name = 'sample';
        $this->wf_stepname = 'two';
        $this->verbiage_group = '';
        $this->failed_notification_function = '';
    }
}

/* End of file ParentUploadMapCompanies.php */
/* Location: ./application/controllers/cli/ParentUploadMapCompanies.php */
