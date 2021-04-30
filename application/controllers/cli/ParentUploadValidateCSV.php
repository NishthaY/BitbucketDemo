<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/workflow/WorkflowBackgroundTaskController.php';

class ParentUploadValidateCSV extends WorkflowBackgroundTaskController {

    public function __construct( $cli=true )
    {
        // Construct our parent class
        parent::__construct($cli);

        $this->load->model('Validation_model');
        $this->load->helper('validation');
        $this->load->library('workflow/ValidateCSVUploadFile');

        $this->wf_name = 'parent_import_csv';
        $this->wf_stepname = 'validate';
        $this->verbiage_group = 'ValidateCSVUpload';
        $this->failed_notification_function = 'SendParentUploadValidateCSVFailed';
    }

}

/* End of file ParentUploadValidateCSV.php */
/* Location: ./application/controllers/cli/ParentUploadImport.php */
