<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ValidateCSVUpload extends A2PWizardStep
{
    protected $encryption_key;

	public function __construct()
    {
        // Construct our parent class
        parent::__construct(true);

        $this->load->helper("s3");
        $this->load->helper("wizard");

        ini_set('auto_detect_line_endings',TRUE);
		$this->debug = false;
    }
    public function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
		// index
        //
        // TODO
        // ---------------------------------------------------------------

        try {

            parent::index($user_id, $company_id, $companyparent_id, $job_id);
            $this->debug("VALIDATING CSV UPLOAD ....");

            if (getStringValue($user_id) == "") throw new Exception("Invalid input user_id.");
            if (getStringValue($company_id) == "") throw new Exception("Invalid input company_id.");

            // Get our import date and start our support timer.
            $import_date = GetUploadDate($company_id);
            SupportTimerStart($company_id, $import_date, __CLASS__, null);

            $this->upload_prefix = replaceFor("companies/company_COMPANYID/uploads", "COMPANYID", $company_id);
            $this->parsed_prefix = replaceFor("companies/company_COMPANYID/parsed", "COMPANYID", $company_id);

            if ($this->debug) print "upload_prefix [{$this->upload_prefix}]\n";
            if ($this->debug) print "parsed_prefix [{$this->parsed_prefix}]\n";


            // Collect the companyparent_id for the given company_id
            $companyparent_id = GetCompanyParentId($company_id);


            // beneficiary.info
            // Create the beneficiary.info file so that when we validate our
            // data we will know to ignore rows that have been flagged as containing beneficiary data.
            // If the feature is not enabled, the control file will note no records contain beneficiary data.
            // We do this here because this is a server task and the user has finished messing with
            // their columns which could impact this feature.
            SupportTimerStart($company_id, $import_date, "CreateBeneficiaryInfoFile", __CLASS__);
            CreateBeneficiaryInfoFile($company_id, 'company');
            SupportTimerEnd($company_id, $import_date, "CreateBeneficiaryInfoFile", __CLASS__);


            // default_plan.info
            // Create the default_plan.info file so that we know which rows should have their
            // plan data replaced with the default_plan code set via the cooresponding feature.
            SupportTimerStart($company_id, $import_date, "CreateDefaultPlanInfoFile", __CLASS__);
            InfoFileCreate($company_id, 'company', 'default_plan', [ 'plan' ], [ 'plan' => [''] ]);
            SupportTimerEnd($company_id, $import_date, "CreateDefaultPlanInfoFile", __CLASS__);

            // Universal Employee Id
            // Has the universal employee id data been invalidated based
            // on the current mapped columns?  If so, we need to restore
            // all of the generated UEIDs that were made on a previous run.
            $obj = new GenerateUniversalEmployeeId();
            if (!$obj->validate($company_id, $companyparent_id)) $obj->restore($company_id);

            // Look for our upload file and make sure we can see it.
            $client = S3GetClient();

            // Review the companies preferences and see if the upload file
            // contains header or not.
            $has_headers = DoesUploadContainHeaderRow($company_id);

            // Pull a list of the required mapped columns
            $mapping_columns = $this->Mapping_model->get_mapping_columns($company_id, $companyparent_id);

            // Remove any previous mapping attempts.
            $this->Validation_model->delete_validation_errors($company_id, 'company');

            // Loop the columns
            foreach ($mapping_columns as $column_data) {

                $column_name = getArrayStringValue("name", $column_data);
                $column_display = GetArrayStringValue('display', $column_data);
                $class_name = ucfirst(strtolower($column_name));
                $mapped_column = $this->Mapping_model->get_upload_column_for_mapping($company_id, 'company', $column_name); // col#

                // No mapped column?  Okay, move on.
                if ($mapped_column == "") continue;

                $this->debug("");
                $this->debug("column_name[{$column_name}]");
                $this->debug("class_name[{$class_name}]");
                $this->debug("mapped_column[{$mapped_column}]");

                $column_no = getIntValue(replaceFor($mapped_column, "col", ""));
                if (file_exists(APPPATH . "libraries/mapping/{$class_name}.php")) {

                    // notify staff what column we are validating.
                    $this->debug("validating {$column_display}. ");

                    $this->notify_status_update("VALIDATING_COLUMN", array('{COLUMN_NAME}' => strtolower($column_display)));

                    // YES!  We know how to validation this mapping type.
                    // Evaluate the data provided and keep track of any errors.
                    $this->load->library("mapping/{$class_name}");
                    $object = new $class_name($company_id, 'company', $column_no);
                    $object->encryption_key = $this->encryption_key;
                    $is_valid = $object->validate($has_headers);
                    if ($is_valid) $this->debug("valid");
                    if (!$is_valid) $this->debug("not valid");

                }

            }


            // Create an error report on S3.
            $this->load->helper("validation");
            $verbiage_group = strtolower(get_called_class());
            ProcessErrors($company_id, 'company', $verbiage_group, $this->job_id, $this->debug);


            if (!$this->Validation_model->is_upload_file_valid($company_id, 'company')) {

                // We are done validating.
                $this->Wizard_model->validation_step_complete($company_id);

                // Notify the user we need their input.
                SendDataValidationFailedEmail($user_id, $company_id);


            } else {

                // Yep, we are done with validation.  Collect some more data
                // and then move on.

                // PLAN TYPE ASSIGNMENT
                // Collect information about the plantypes found in this file
                // which will be used later in the upload workflow.
                $this->_process_plan_types( $company_id );

                $this->Wizard_model->validation_step_complete($company_id);
                $this->Wizard_model->correction_step_complete($company_id);

                $this->schedule_next_step("GenerateImportFiles");
            }

        }
        catch(Exception $e)
        {
            // Write the error to stdout so that the queue manager can see
            // and detect that something bad has happened.
            print $e->getMessage() . PHP_EOL;

            // Rollback the user's attempt so they can start over.
            RollbackWizardAttempt($company_id);

            // Notify the user via email.
			SendDataValidationFailedEmail($user_id, $company_id);
        }

        // Update the UI, notifying anyone watching that this
        // step is complete.
        NotifyStepComplete($company_id);
        SupportTimerEnd($company_id, $import_date, __CLASS__, null);

    }
    private function _process_plan_types( $company_id ) {

        //
        // Every user defined plan type must be mapped to an A2P plan type.
        // This function will check the uploaded data for new plan types
        // that have never been assigned before.
        //
        // Return true if all plan types found have been mapped, else false.
        // ---------------------------------------------------------------

        $fh = null;
        try{

            $this->notify_status_update('SCANNING');

            // Required Check.
            if ( getStringValue($company_id) == "" ) throw new Exception("Missing requried input company_id");

            // REMOVE -- We are creating a plan_types preference.  Remove the old before we start.
            $this->Company_model->remove_company_preference( $company_id, "plan_types", "json" );

            // LOCATE - Figure out which column has our plan type data.
            $companyparent_id = GetCompanyParentId($company_id);
            $column_no = $this->Mapping_model->get_mapped_column_no($company_id, $companyparent_id, "plan_type");
            if ( $column_no === FALSE ) throw new Exception("Unable to locate the mapping for the plan type column.");

            // VERITFY - Make sure we can see the plan type data.
            $client = S3GetClient();
            $parsed_prefix = GetS3Prefix('parsed', $company_id, 'company');
            $filename = "col{$column_no}.txt";
            $full_path = "s3://" . S3_BUCKET . "/{$parsed_prefix}/{$filename}";
            if ( ! S3DoesFileExist( S3_BUCKET, $parsed_prefix, $filename) ) throw new Exception("Unable to locate the plan type file.");

            // PARSE - Read the plan type file while creating a unique list of plan types.
            $lookup = array();
            $has_headers = DoesUploadContainHeaderRow( $company_id );
            $fh = fopen($full_path, "r");
            if ($fh) {
                $index = 0;
                while (($line = fgets($fh)) !== false)
                {

                    // keep track of our line number.
                    $index++;

                    // grab a line of data.
                    $line = trim($line);

                    // If this is the first row and we know there are headers, skip this line.
                    if ( $index == 1 && $has_headers ) continue;

                    // Save the line to our lookup.
                    if ( getStringValue($line) != "" ) {
                        $lookup[$line] = $line;
                    }

                }
                fclose($fh);
            }else{
                throw new Exception("Unable to open the plan type file.");
            }

            // Create a data collection with the plan types for this upload.
            $output = array();
            $keys = array_keys($lookup);
            foreach($keys as $plan_type){
                $data = array();
                $data["name"] = strtoupper($plan_type);
                $data["display"] = $plan_type;
                $output[] = $data;
            }

            // SAVE - Save the plan_types company preference which outlines all known plan_types
            // for the given upload and their mapping status.
            $this->Company_model->save_company_preference( $company_id, "plan_types", "json", json_encode($output) );


        }catch(Exception $e) {
            if( $fh ) fclose($fh);
            throw $e;
        }

    }



}

/* End of file ParseUpload.php */
/* Location: ./application/controllers/cli/ParseUpload.php */
