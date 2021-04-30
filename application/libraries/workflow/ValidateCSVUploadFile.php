<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ValidateCSVUploadFile extends WorkflowLibrary
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
    //protected $verbiage_group             // See parent class for more information.

    public function execute()
    {
        try
        {
            $CI = $this->ci;

            $this->debug("VALIDATING CSV UPLOAD ....");

            //TODO: Add Support timers?

            $this->upload_prefix = GetS3Prefix('upload', $this->identifier, $this->identifier_type);
            $this->parsed_prefix = GetS3Prefix('parsed', $this->identifier, $this->identifier_type);

            if ($this->debug) print "upload_prefix [{$this->upload_prefix}]\n";
            if ($this->debug) print "parsed_prefix [{$this->parsed_prefix}]\n";

            // Make sure any data from a previous run has been cleaned up.
            $this->rollback();

            // Review the companies preferences and see if the upload file
            // contains header or not.
            $has_headers = DoesUploadContainHeaderRow($this->company_id, $this->companyparent_id);

            // Pull a list of the required mapped columns
            $mapping_columns = $CI->Mapping_model->get_mapping_columns($this->company_id, $this->companyparent_id);

            // Remove any previous mapping attempts.
            $CI->Validation_model->delete_validation_errors($this->identifier, $this->identifier_type);

            // Loop the columns
            foreach ($mapping_columns as $column_data) {

                $column_name = GetArrayStringValue("name", $column_data);
                $column_display = GetArrayStringValue('display', $column_data);
                $class_name = ucfirst(strtolower($column_name));
                $mapped_column = $CI->Mapping_model->get_upload_column_for_mapping($this->identifier, $this->identifier_type, $column_name); // col#

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

                    NotificationSetStatusMessage($this->verbiage_group, 'VALIDATING_COLUMN', $this->job_id, $this->identifier, $this->identifier_type, array('{COLUMN_NAME}' => strtolower($column_display)));

                    // YES!  We know how to validation this mapping type.
                    // Evaluate the data provided and keep track of any errors.
                    $CI->load->library("mapping/{$class_name}");
                    $object = new $class_name($this->identifier, $this->identifier_type, $column_no);
                    $object->encryption_key = $this->encryption_key;
                    $object->debug = $this->debug;

                    $is_valid = $object->validate($has_headers);
                    if ($is_valid) $this->debug("valid");
                    if (!$is_valid) $this->debug("not valid");

                }

            }

            // Create an error report on S3.
            $CI->load->helper('validation');
            ProcessErrors($this->identifier, $this->identifier_type, $this->verbiage_group, $this->job_id, $this->debug);

            if (!$CI->Validation_model->is_upload_file_valid($this->identifier, $this->identifier_type))
            {
                $message = "Errors where discovered in the validation file.";
                $tag = "SendDataValidationFailedEmail";
                throw new A2PWorkflowWaitingException($message, $tag);
            }

            // If we did not stop to ask the user for clarification, then
            // we need to take a snapshot.
            $this->takeSnapshot();

        } catch(Exception $e) {
            throw $e;
        }
    }

    public function snapshot()
    {
        try
        {
            $CI = $this->ci;

            // Capture all of the mapped columns that was validated.
            $data = $CI->Archive_model->select_column_mappings_for_archive($this->identifier, $this->identifier_type);
            foreach($data as $item) {
                $column_mapping = GetArrayStringValue('Column Mapping', $item);
                if ($column_mapping !== '')
                {
                    $this->addSnapshotData($item);
                }
            }

            // SNAP
            $this->takeSnapshot();
        }
        catch(Exception $e)
        {

        }

    }

    public function rollback( )
    {
        try
        {
            $CI = $this->ci;

            // Remove the data stored in the validation table.
            $CI->Validation_model->delete_validation_errors($this->identifier, $this->identifier_type);

            // Clean up S3.
            $prefix = GetS3Prefix('errors', $this->identifier, $this->identifier_type);
            if( getStringValue($prefix) != "" )
            {
                try
                {
                    $client = S3GetClient();
                    S3DeleteFile(S3_BUCKET, $prefix, "errors.json");
                }catch(Exception $e){ }
            }
        }
        catch(Exception $e)
        {

        }
    }




}
