<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ParseCSVUpload extends A2PWizardStep
{
    protected $upload_prefix;
    protected $parsed_prefix;
    protected $column_fhs;
    protected $upload_fh;
    protected $preview_fh;
    protected $identifier;
    protected $identifier_type;

    protected $file_columns_changed;

	function __construct()
    {
        // Construct our parent class
        parent::__construct(true);

        $this->load->helper("s3");
        $this->load->helper("match");

        ini_set('auto_detect_line_endings',TRUE);

        $this->upload_prefix  = "companies/company_COMPANYID/uploads/";
        $this->parsed_prefix  = "companies/company_COMPANYID/parsed/";
        $this->fh_columns     = array();
        $this->fh_upload      = null;
        $this->fh_preview     = null;
        $this->file_columns_changed = true;  // Assume the file has changed.

    }


    function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
		// index
        //
        // This function will read a CSV file that has been stored in the
        // companies upload bucket on S3.  The file will be split into
        // individual files, one for each column.  We will also create a
        // preview file that contains the first X rows of the file.
        // These new files will be stored in the company parsed bucket.
        //
        // If there is an error, the
		// ---------------------------------------------------------------

        parent::index($user_id, $company_id, $companyparent_id, $job_id);

        $this->upload_prefix  = "";
        $this->parsed_prefix  = "";
        $this->gc_enabled = true;           // Manually kick off Garbage Collection.
        $this->gc_delay_enabled = false;    // Wait a few seconds after you kick of GC to get a fair reading for analysis.

        try {

            if ( getStringValue($user_id) == "" ) throw new Exception("Invalid input user_id.");
            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");

            $this->notify_status_update('IMPORTING');

            $this->identifier = $company_id;
            $this->identifier_type = 'company';

            // Get our import date and start our support timer.
            $import_date = GetUploadDate($company_id);
            SupportTimerStart($company_id, $import_date, __CLASS__, null);

            // Before we get started, based on the enabled features, build out the columns that
            // will be required for this customer.
            $this->debug("Researching which columns will be required for this customer.");
            GenerateColumnDataForCompany($company_id);

            $this->notify_status_update('ORGANIZING');

            // Split the upload file into seperate encrypted files.
            $this->debug("SplitCSVUpload ( before )    : " . FormatBytes(memory_get_usage()));
            $csv_headers = SplitCSVUpload($company_id, 'company', $this->debug);
            $this->debug("SplitCSVUpload ( after )     : " . FormatBytes(memory_get_usage()));
            $this->debug("SplitCSVUpload ( after MAX ) : " . FormatBytes(memory_get_usage()));

            // Organize our headers for future tasks.
            $this->file_columns_changed = CreateHeaderLookupFiles($this->identifier, $this->identifier_type, $csv_headers);


            // Secure the parsed folder.
            $this->notify_status_update('SECURING');
            $this->debug("Making files 'at rest' encrypted.");
            $this->parsed_prefix  = replaceFor("companies/company_COMPANYID/parsed", "COMPANYID", $this->identifier );
            S3EncryptAllFiles(S3_BUCKET, $this->parsed_prefix);

            if ( $this->file_columns_changed )
            {
                // If our columns have changed, use the user defined header to
                // shift mappings around to make it easier on the user.
                $this->debug("Columns have been reorganized, shifting things to keep up.");
                ShiftColumnMappings($this->identifier, $this->identifier_type);
            }

            // Clean up any mappings that have been invalidated by the most recent upload.
            $this->debug("puruning previously mapped columsn now missing.");
            PruneMappedColumns($this->getUserId(), $this->identifier, $this->identifier_type, $this->encryption_key);

            // Make sure the mapping preferences have no duplicates, caused by shift and prune.
            $this->debug("deduping mapping preferences");
            DedupeMappingPreferences($this->identifier, $this->identifier_type);

            // Calculate the best mapping for each column
            $this->debug("calculating the best mapping for each column.");
            CalculateBestMappings($this->identifier, $this->identifier_type, $this->encryption_key);

            // Check and see if we have column match data for our parsed file.
            $this->debug("look and see if all required columns have matched");
            $all_matched = AllRequireColumnsMatched($this->identifier, $this->identifier_type);
            if ( $all_matched !== TRUE ) $all_matched = false;

            // Normally, we allow quick scan to happen.  However, if you have the beneficiary mapping feature
            // enabled, we don't want to do it.
            $quickscan_enabled = true;
            if ( IsAtLeastOneFeatureEnabledForCompany($company_id, 'BENEFICIARY_MAPPING') ) $quickscan_enabled = false;

            // Check and see if the preview file is free of errors.
            $this->notify_status_update('SCANNING');
            $this->debug("doing a quick scan.");
            $quick_scan = true;
            if ( $all_matched && $quickscan_enabled && ! QuickScanPreviewFile($this->identifier, $this->identifier_type, $this->encryption_key) )
            {
                //Nope.  We need to stop.  Set the wizard up so we land on the correction page.
                $this->Wizard_model->match_step_complete($company_id);
                $this->Wizard_model->validation_step_complete($company_id);
                $quick_scan = false;
            }

            // No need to keep the customers upload file around.  Delete it.
            $this->debug("parse complete, removing customers upload.");
            $this->upload_prefix = replaceFor(GetConfigvalue("upload_prefix"), "COMPANYID", $company_id);
            S3DeleteBucketContent( S3_BUCKET, $this->upload_prefix );

            // We are done parsing the file, move on.
            $this->debug("parse complete, routing to next step.");
            $this->Wizard_model->parsing_step_complete( $company_id );

            // When we reviewed the differences between columns of the original
            // file and the new file, did we find changes?
            if ( $this->file_columns_changed )
            {
                // There has been a change in the upload between this time and last
                // that requires the user to look at the mapped columns again.
                $this->Wizard_model->match_step_incomplete($company_id);
                SendUploadCompleteEmail($user_id, $company_id);
                NotifyStepComplete($company_id);
                SupportTimerEnd($company_id, $import_date, __CLASS__, null);
                return;
            }

            // If Auto Column Mapping is enabled, we will always stop
            // and ask the user to take a look.  Until they hit continue
            // and disable auto-mapping, we can't continue.
            if ( IsA2PAutoColumnMappingEnabled($this->identifier, $this->identifier_type) )
            {
                $this->Wizard_model->match_step_incomplete($company_id);
                SendUploadCompleteEmail($user_id, $company_id);
                NotifyStepComplete($company_id);
                SupportTimerEnd($company_id, $import_date, __CLASS__, null);
                return;
            }


            // We are matched and the preview file is valid.
            if ( $all_matched && $quick_scan )
            {
                // Mark match complete and move on to next step. ( Deep Scan!)
                $this->Wizard_model->match_step_complete($company_id);
                $this->schedule_next_step("ValidateCSVUpload");
                NotifyStepComplete($company_id);
                SupportTimerEnd($company_id, $import_date, __CLASS__, null);
                return;
            }

            if ( $all_matched && ! $quick_scan )
            {
                // Matched, but errors in preview file.
                $this->Wizard_model->match_step_complete($company_id);
            }

            // We need to match columns.  Upload complete, but need more info.
            SendUploadCompleteEmail($user_id, $company_id);
            NotifyStepComplete($company_id);
            SupportTimerEnd($company_id, $import_date, __CLASS__, null);





        }catch(Exception $e) {

            // Write the error to stdout so that the queue manager can see
            // and detect that something bad has happened.
            print $e->getMessage() . PHP_EOL;

            // Clean up S3.
            if( getStringValue($this->upload_prefix) != "" )
            {
                try {
                    $client = S3GetClient();
                    S3DeleteBucketContent( S3_BUCKET, $this->upload_prefix );
                    S3DeleteBucketContent( S3_BUCKET, $this->parsed_prefix );
                }catch(Exception $e){ }
            }

            // Remove Wizard record.
            if ( getStringValue($company_id) != "" ) {
                $this->Wizard_model->delete_wizard($company_id);
            }

            // Notify the user via email.
            if ( getStringValue($company_id) != "" && getStringValue($user_id) != "" )
            {
                SendUploadFailedEmail($user_id, $company_id);
            }

            NotifyStepComplete($company_id);
            SupportTimerEnd($company_id, $import_date, __CLASS__, null);

        }
    }
}

/* End of file ParseUpload.php */
/* Location: ./application/controllers/cli/ParseUpload.php */
