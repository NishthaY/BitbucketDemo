<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends SecureController {

	function __construct(){
		parent::__construct();

		$this->load->model('Company_model','company_model',true);
		$this->load->model('mapping_model','mapping_model',true);
        $this->load->model('Wizard_model','wizard_model',true);
		$this->load->model('Queue_model','queue_model',true);
		$this->load->helper("wizard");

        // Protect against multiple users working the wizard at the same
        // time.  If the wizard state no longer matches this step, push
        // them to the dashboard.
        if ( IsUploadStepComplete() )
        {
            redirect( base_url("dashboard") );
        }

	}


    // SCREENS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+

    /**
     * restore
     *
     * This function accepts UI POST requests to restore from the archive the previous
     * months data file and then use it as if it was uploaded for the current
     * month.
     *
     * This function supports one to many requests by accepting the "companies" input
     * which can be a single company id or a "-" delimited list of company ids.
     *
     */
    public function restore($url_identifier) {
	    try
        {
            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required permission.");

            // VALIDATE
            // Make sure we have a valid company id out of the session.
            $companies = GetArrayStringValue('companies', $_POST);
            if ( $companies === '' ) throw new Exception("Missing required input");
            $companies = explode('-',$companies);
            foreach($companies as $company_id)
            {
                if ( $company_id === A2P_COMPANY_ID ) throw new Exception("Unsupported company.");
            }

            // EXECUTE
            // Execute the Skip Month Processing for this company on the latest import month.
            $this->load->library('SkipMonthProcessing');
            $obj = new SkipMonthProcessing();

            $success = 0;
            $failed = [];
            foreach($companies as $company_id)
            {

                $import_date = GetUploadDate($company_id);
                try
                {
                    $grouped = false;
                    if ( strtolower($url_identifier) === 'parent' ) $grouped = true;

                    // Execute the skip month logic that will move last months file into place as an
                    // upload for this month.  Once done, trigger the same logic we would have used if
                    // the had actually uploaded it.
                    $upload_pathname = $obj->execute($company_id);
                    $this->_upload_complete($company_id, $upload_pathname, $grouped);
                    $success++;
                }
                catch(Exception $e)
                {
                    $obj->rollback($company_id, $import_date);
                    $failed[] = $e;
                }
            }

            // If we had a failure and NO successes report an error.
            if ( $success === 0 && count($failed) > 0 )
            {
                throw($failed[0]);
            }

            AJAXSuccess("Upload complete using archived file.");
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) {
	        AccessDenied();
	    }
        catch( Exception $e ) { Error404(); }

    }


    /**
     * save
     *
     * This function will accept UI POST requests to start the company workflow
     * process using the file stored in the upload file for the current import
     * month.
     *
     */
    public function save() {

        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required permission.");


            // Get the company id off the session.
            $company_id = GetSessionValue("company_id");

            // Find our upload file based on the POST data.
            $upload_prefix = replaceFor(GetConfigValue("upload_prefix"), "COMPANYID", $company_id);
            $upload_path =  $upload_prefix . "/";
            $upload_filename = GetArrayStringValue("upload_filename", $_POST);
            $upload_filename = fRight($upload_filename, $upload_path);
            if ( $upload_filename === '' ) throw new UIException("Missing required input: upload_filename");

            // Note the upload is complete for this company and file.
            // Start the workflow.
            $this->_upload_complete($company_id, $upload_filename);

            AJAXSuccess("File uploaded successfully.");

        }
        catch ( UIException $e ) {
            AjaxDanger($e->getMessage());
        }
        catch(SecurityException $e ) {
            AccessDenied();
        }
        catch( Exception $e ) {
            Error404();
        }
    }




    public function save_parent_upload($wf_name="") {

        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            $user_id = GetSessionValue("user_id");
            $companyparent_id = GetSessionValue('companyparent_id');
            $upload_prefix = GetS3Prefix('upload', $companyparent_id, 'companyparent');
            $upload_path =  $upload_prefix . "/";

            $upload_filename = getArrayStringValue("upload_filename", $_POST);
            $upload_filename = fRight($upload_filename, $upload_path);
            S3DeleteBucketContent(S3_BUCKET, $upload_prefix, $upload_filename);

            // Check S3 to make sure the file we uploaded has arrived.
            if ( ! S3DoesFileExist( S3_BUCKET, $upload_path, $upload_filename ) )
            {
                throw new UIException("There was a problem importing your file, please try again.");
            }



            // Remove all files EXCEPT for the the file we just uploaded in the uploads folder.
            // Oh, and do not delete the wizard record, just empty it.
            // TODO: Rollback the workflow.
            //RollbackWizardAttempt($company_id, $upload_filename, "START");

            // Ensure that we have exactly ONE upload file.
            // TODO: Comment this back in after you have rollback working.
            //$items = S3ListFiles(S3_BUCKET, $upload_path);
            //if ( count($items) != 1 ) {
            //    throw new UIException("There was a problem importing you file, please try again.");
           // }

            // Note that this step in the wizard is now complete.
            $payload = array();
            $payload['OriginalFilename'] = GetPreferenceValue($companyparent_id, 'companyparent', 'upload', 'original_filename');
            AuditIt('File uploaded.', $payload);

            // Archive the upload file.
            ArchiveUpload($user_id, $companyparent_id, 'companyparent');

            WorkflowStart($companyparent_id, 'companyparent', $wf_name);
            WorkflowStartBackgroundJob($companyparent_id, 'companyparent', $wf_name, GetSessionValue('user_id'));
            AJAXSuccess("File uploaded successfully.");

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }



    // RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-


    /**
     * _upload_complete
     *
     * Given a company id and an upload filename, this function will look for the file
     * specified in the current import month for the given company and start the
     * company import wizard process.
     *
     * @param $company_id
     * @param $upload_filename
     * @throws SecurityException
     * @throws UIException
     */
    private function _upload_complete($company_id, $upload_filename, $grouped=false)
    {
        try
        {
            if ( GetStringValue($company_id) === '' ) throw new UIException("Missing required input: company_id");
            if ( GetStringValue($upload_filename) === '' ) throw new UIException("Missing required input: upload_filename");

            $user_id = GetSessionValue("user_id");
            $companyparent_id = GetCompanyParentId($company_id);
            $upload_prefix = replaceFor(GetConfigValue("upload_prefix"), "COMPANYID", $company_id);
            $upload_path =  $upload_prefix . "/";

            // If the filename name came in with a path, strip it off.
            if ( strpos($upload_filename, "/") !== FALSE )
            {
                $upload_filename = fRightBack($upload_filename, "/");
            }

            // Check S3 to make sure the file we uploaded has arrived.
            if ( ! S3DoesFileExist( S3_BUCKET, $upload_path, $upload_filename ) )
            {
                throw new UIException("There was a problem importing your file, please try again.");
            }

            // Remove all files EXCEPT for the the file we just uploaded in the uploads folder.
            // Oh, and do not delete the wizard record, just empty it.
            RollbackWizardAttempt($company_id, $upload_filename, "START");

            // Ensure that we have exactly ONE upload file.
            $items = S3ListFiles(S3_BUCKET, $upload_path);
            if ( count($items) != 1 ) {
                throw new UIException("There was a problem importing you file, please try again.");
            }

            // Note that this step in the wizard is now complete.
            $exists = $this->Wizard_model->does_wizard_record_exist($company_id);
            if ( ! $exists ) $this->Wizard_model->create_wizard_record($company_id, $user_id);
            $this->Wizard_model->startup_step_complete($company_id, $upload_filename);  // BAH: I added this.  Is this a problem?
            $this->Wizard_model->upload_step_complete($company_id, $upload_filename);

            ArchiveUpload($user_id, $company_id, 'company');

            if ( $grouped )
            {
                $this->queue_model->add_grouped_worker_job($companyparent_id, $company_id, $user_id, $companyparent_id, "ParseCSVUpload", "index");
            }
            else
            {
                $this->queue_model->add_worker_job($companyparent_id, $company_id, $user_id, "ParseCSVUpload", "index");
            }

            return;
        }
        catch ( UIException $e ) {
            throw $e;
        }
        catch(SecurityException $e ) {
            throw $e;
        }
        catch( Exception $e ) {
            throw $e;
        }
    }
}
