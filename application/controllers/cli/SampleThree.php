<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/workflow/WorkflowBackgroundTaskController.php';


class SampleThree extends WorkflowBackgroundTaskController
{
    public function __construct( $cli=true )
    {
        // Construct our parent class
        parent::__construct($cli);

        $this->wf_name = 'sample';
        $this->wf_stepname = 'three';
        $this->verbiage_group = '';
        $this->failed_notification_function = '';
    }
}

/* End of file SampleThree.php */
/* Location: ./application/controllers/cli/SampleThree.php */
