<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/workflow/WorkflowBackgroundTaskController.php';


class ParentUploadMapCompanies extends WorkflowBackgroundTaskController
{
    public function __construct( $cli=true )
    {
        // Construct our parent class
        parent::__construct($cli);

        $this->wf_name = 'parent_import_csv';
        $this->wf_stepname = 'map';
        $this->verbiage_group = 'MapCompanyCSVUpload';
        $this->failed_notification_function = 'SendParentUploadMapCompaniesFailed';
    }
}

/* End of file ParentUploadMapCompanies.php */
/* Location: ./application/controllers/cli/ParentUploadMapCompanies.php */
