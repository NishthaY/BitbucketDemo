<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/workflow/WorkflowBackgroundTaskController.php';

class ParentUploadParseCSV extends WorkflowBackgroundTaskController {

    public function __construct( $cli=true )
    {
        // Construct our parent class
        parent::__construct($cli);

        $this->wf_name = 'parent_import_csv';
        $this->wf_stepname = 'parse';
        $this->verbiage_group = 'ParseCSVUpload';
        $this->failed_notification_function = 'SendParentUploadParseCSVFailed';

    }

}

/* End of file ParentUploadImport.php */
/* Location: ./application/controllers/cli/ParentUploadImport.php */
