<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/workflow/WorkflowBackgroundTaskController.php';

class ParentUploadSplitCSV extends WorkflowBackgroundTaskController
{
    public function __construct( $cli=true )
    {
        // Construct our parent class
        parent::__construct($cli);
        $this->wf_name = 'parent_import_csv';
        $this->wf_stepname = 'split';
        $this->verbiage_group = 'SplitCompanyCSVUpload';
        $this->failed_notification_function = 'SendParentUploadSplitCSVFailed';
    }
}

/* End of file ParentUploadSplitCSV.php */
/* Location: ./application/controllers/cli/ParentUploadSplitCSV.php */
