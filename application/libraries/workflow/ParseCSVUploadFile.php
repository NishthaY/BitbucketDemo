<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ParseCSVUploadFile extends WorkflowLibrary
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
    //protected $verbiage_group;            // See parent class for more information.
    //protected $wf_name;                   // See parent class for more information.
    //protected $wf_stepname;               // See parent class for more information.

    public function execute()
    {
        try
        {
            $CI = $this->ci;

            // ROLLBACK
            // Do not rollback this step on init!  Rollback will delete the file we are trying to parse
            // in the cloud.  On init, we want to process that file so rolling it back as a start would
            // be counter productive.
            //$this->rollback();

            // Some data was stored in properties on upload because the workflow had not
            // yet started.  Now that we are about to process the upload, organize our data
            // so that everything is referenced by the SupportTag and stored in the Workflow
            // progress properties table.
            $this->_organizeUploadProperties($this->identifier, $this->identifier_type, $this->wf_name);

            // Create mapping data for each active company associated with this parent AND stub in the
            // mapping column for the company parent.
            GenerateColumnDataForCompanyParent($this->companyparent_id);

            // Split the upload file into seperate encrypted files.
            $this->debug("SplitCSVUpload ( before )    : " . FormatBytes(memory_get_usage()));
            $csv_headers = SplitCSVUpload($this->identifier, $this->identifier_type, $this->debug);
            $this->debug("SplitCSVUpload ( after )     : " . FormatBytes(memory_get_usage()));
            $this->debug("SplitCSVUpload ( after MAX ) : " . FormatBytes(memory_get_usage()));

            // Examine the headers in detail.  We will create hash lookups so we can easily access data.
            // Also, while we are making these, we will be able to tell if the file columns changed between
            // this time and last.
            $this->file_columns_changed = CreateHeaderLookupFiles($this->identifier, $this->identifier_type, $csv_headers);

            // Secure the parsed folder.
            NotificationSetStatusMessage($this->verbiage_group, 'SECURING', $this->job_id, $this->identifier, $this->identifier_type);
            $this->debug("Making files 'at rest' encrypted.");
            $this->parsed_prefix = GetS3Prefix('parsed', $this->identifier, $this->identifier_type);
            //$this->parsed_prefix  = replaceFor("parents/parent_COMPANYPARENTID/parsed", "COMPANYPARENTID", $this->identifier );
            S3EncryptAllFiles(S3_BUCKET, $this->parsed_prefix);

            if ( $this->file_columns_changed )
            {
                // If our columns have changed, use the user defined header to
                // shift mappings around to make it easier on the user.
                $this->debug("Columns have been reorganized, shifting things to keep up.");
                ShiftColumnMappings($this->identifier, $this->identifier_type);
            }

            // Clean up any mappings that have been invalidated by the most recent upload.
            $this->debug("pruning previously mapped columns now missing.");
            PruneMappedColumns($this->user_id, $this->identifier, $this->identifier_type, $this->encryption_key);

            // Make sure the mapping preferences have no duplicates, caused by shift and prune.
            $this->debug("deduping mapping preferences");
            DedupeMappingPreferences($this->identifier, $this->identifier_type);

            // Calculate the best mapping for each column
            $this->debug("calculating the best mapping for each column.");
            CalculateBestMappings($this->identifier, $this->identifier_type, $this->encryption_key, $this->debug);

            // Check and see if we have column match data for our parsed file.
            $this->debug("look and see if all required columns have matched");
            $all_matched = AllRequireColumnsMatched($this->identifier, $this->identifier_type);
            if ( $all_matched !== TRUE ) $all_matched = false;

            // Check and see if the preview file is free of errors.
            NotificationSetStatusMessage($this->verbiage_group, 'SCANNING', $this->job_id, $this->identifier, $this->identifier_type);
            $this->debug("doing a quick scan.");
            $quick_scan = true;
            if ( $all_matched && ! QuickScanPreviewFile($this->identifier, $this->identifier_type, $this->encryption_key) )
            {
                // The quick scan has failed.
                $quick_scan = false;
            }

            // FIXME: We have to have a file to split later on.  For now, we will
            // FIXME: keep this file around and let SPLIT clean it up.  Later, we
            // FIXME: might want to consider using the archive copy as source for
            // FIXME: the split.
            // No need to keep the customers upload file around.  Delete it.
            //$this->debug("parse complete, removing customers upload.");
            //$this->upload_prefix = GetS3Prefix('upload', $this->identifier, $this->identifier_type);
            //S3DeleteBucketContent( S3_BUCKET, $this->upload_prefix );

            // When we reviewed the differences between columns of the original
            // file and the new file, did we find changes?
            if ( $this->file_columns_changed )
            {
                // There has been a change in the upload between this time and last
                // that requires the user to look at the mapped columns again.
                $message = "There has been a change in the upload between this time and last that requires the user to look at the columns again.";
                $tag = "SendUploadCompleteEmail";
                throw new A2PWorkflowWaitingException($message, $tag);
                return;
            }

            // If Auto Column Mapping is enabled, we will always stop
            // and ask the user to take a look.  Until they hit continue
            // and disable auto-mapping, we can't continue.
            if ( IsA2PAutoColumnMappingEnabled($this->identifier, $this->identifier_type) )
            {
                $message = "A2P auto-mapping has never been approved by a user.  Ask someone to take a look.";
                $tag = "SendUploadCompleteEmail";
                throw new A2PWorkflowWaitingException($message, $tag);
                return;
            }

            if ( $all_matched && ! $quick_scan )
            {
                // We did a quick scan of the preview file and it has failed!  Note this fact
                // so that the next step can quickly deal.
                $message = "Quick scan of sample file has failed!  Move directly to validate step.";
                $tag = "SendDataValidationFailedEmail";
                throw new A2PWorkflowWaitingException($message, $tag);
                SavePreference($this->identifier, $this->identifier_type, 'uploads', 'quickscan', 'FAILED');
                return;
            }



        } catch(Exception $e) {
            throw $e;
        }
    }

    public function snapshot()
    {
        try
        {

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

            // Clean up S3.
            $upload_prefix = GetS3Prefix('upload', $this->identifier, $this->identifier_type);
            $parsed_prefix = GetS3Prefix('parsed', $this->identifier, $this->identifier_type);
            if( getStringValue($upload_prefix) != "" && GetStringValue($parsed_prefix) != "" )
            {
                try
                {
                    $client = S3GetClient();
                    S3DeleteBucketContent( S3_BUCKET, $upload_prefix );
                    S3DeleteBucketContent( S3_BUCKET, $parsed_prefix );
                }catch(Exception $e){ }
            }

        }
        catch(Exception $e)
        {

        }
    }


    /**
     * _organizeUploadProperties
     *
     * During the upload process, some data was tossed into preferences.  Here we will "commit"
     * that data into the running workflow as WorkflowProgressProperties.  We want to access
     * and organize all of the data for this workflow run via these properties so that
     * all other steps can rely on them.
     *
     * - Move the original archive file into a folder that matches the support tag.
     * - Capture the original upload file name.
     *
     * @param $identifier
     * @param $identifier_type
     * @throws Exception
     */
    private function _organizeUploadProperties($identifier, $identifier_type, $wf_name)
    {
        // ARCHIVED UPLOAD
        // First, the file uploaded by the user was archived before the start of the workflow.
        // At this point, since we are parsing the upload, we want to bring the archive folder name
        // into sync with the "SupportTag" for this workflow run.  Move the archived upload to
        // a new folder matching the support tag and clean up the original upload and it's various
        // data bits.
        $folder_name = GetPreferenceValue($identifier, $identifier_type, 'upload', 'archive_folder');
        $support_tag = GetWorkflowProgressProperty($identifier, $identifier_type, $this->wf_name, 'SupportTag');
        $original_filename = GetPreferenceValue($identifier, $identifier_type, 'upload', 'original_filename');

        S3GetClient();
        $archive_prefix = GetS3Prefix('archive', $identifier, $identifier_type);

        $from = $archive_prefix;
        $from = replaceFor($from, 'COMPANYPARENTID', $identifier);
        $from = replaceFor($from, "DATE", $folder_name);
        $from .= '/upload';

        $to = $archive_prefix;
        $to = replaceFor($to, 'COMPANYPARENTID', $identifier);
        $to = replaceFor($to, "DATE", $support_tag);
        $to .= '/upload';

        // Move the file from the old folder to the new.
        if ( ! S3DoesFileExist(S3_BUCKET, $from, $original_filename) )
        {
            throw new Exception("Unable to locate the original upload file.");
        }
        S3EncryptExistingFile( S3_BUCKET, $from, $original_filename, $to, $original_filename );

        // Remove the old folder.
        $cleanup = $archive_prefix;
        $cleanup = replaceFor($cleanup, 'COMPANYPARENTID', $identifier);
        $cleanup = replaceFor($cleanup, "DATE", $folder_name);
        S3DeleteBucketContent(S3_BUCKET, $cleanup);

        RemovePreference($identifier, $identifier_type, 'upload', 'archive_folder');

        // ORIGINAL FILENAME
        // During the upload process, we tossed the original filename into a property.  Now that we are
        // entered the workflow, move it into a WorkflowProgressProperty so we don't junk up the
        // preferences.
        SetWorkflowProgressProperty($identifier, $identifier_type, $wf_name, "OriginalFilename", $original_filename);
        RemovePreference($identifier, $identifier_type, 'upload', 'original_filename');

    }



}
