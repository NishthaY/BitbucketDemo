<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SplitCompanyCSVUpload extends WorkflowLibrary
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

    protected $fh_company;                  // Company column file handle.  File that holds the company data from the upload file.
    protected $fh_upload;                   // CompanyParent upload file handle.
    protected $handles;                     // Output file handles.  Associative array indexed by company_id
    protected $normalized_lookup;           // Lookup, by normalized text, for each mapped company.
    protected $output_filename_identifier;  // unique identifier that links the files we are creating to the master upload file.
    protected $unique_companies;            // List of unique company ids that we will be mapping from the companyparent to company.

    public function __construct()
    {
        parent::__construct();

        $this->fh_company = null;
        $this->fh_upload = null;
        $this->handles = array();
        $this->normalized_lookup = array();
        $this->output_filename_identifier = "";
        $this->unique_companies = array();
    }

    public function execute()
    {
        try
        {
            $CI = $this->ci;
            $this->debug("SplitCompanyCSVUpload starting.");

            // Rollback the previous attempt so old files don't mix with new.
            $this->rollback();

            $selected_company_id = GetPreferenceValue($this->identifier, $this->identifier_type, 'companyparentmap', 'selected_company_id');
            if ( $selected_company_id === '' )
            {
                $this->debug("Splitting parent CSV file into company files.");
                $this->_split_multiple_companies();
            }
            else
            {
                $company = $CI->Company_model->get_company($selected_company_id);
                $company_name = GetArrayStringValue('company_name', $company);
                if ( $company_name === '' ) throw new Exception("Did not find the company we are targeting.");

                $this->debug("Using the upload file for a single company. [{$company_name}]");
                $this->_split_single_company($selected_company_id);
            }


            // Start the company wizard jobs.
              $this->_startCompanyJobs();

            // SNAPSHOTS
            // This is the end of the line.  Grab the snapshots here as there is no next step.
            $this->snapshot();

            // No need to keep the customers upload file around.  Delete it.
            $this->debug("Removing customers upload.");
            $this->upload_prefix = GetS3Prefix('upload', $this->identifier, $this->identifier_type);
            S3DeleteBucketContent( S3_BUCKET, $this->upload_prefix );

        } catch(Exception $e) {
            $this->_closeHandles();
            throw $e;
        }
    }

    public function snapshot()
    {
        try
        {
            // Since this workflow step has no waiting UI step, I just captured the snapshot
            // data as we went through the execute function.  Just take the snapshot.
            // Had there been a UI component, I could have captured all of the the snapshot data
            // in this function before I took the snapshot.  That way I could call this function
            // from the controller.

            $this->takeSnapshot();
        }
        catch(Exception $e)
        {

        }
    }

    /**
     * rollback
     *
     * Undo function for all data writes and modifications for this workflow step.
     */
    public function rollback( )
    {
        try
        {
            $CI = $this->ci;
            $split_prefix = GetS3Prefix('split', $this->identifier, $this->identifier_type);
            S3DeleteBucketContent(S3_BUCKET, $split_prefix);

        }
        catch(Exception $e)
        {

        }
    }

    /**
     * _split_single_company
     *
     * The user indicated that the file they uploaded contains data for only one company.
     * When this happens, there is no reason to do anything fancy.  Just copy the file from the
     * upload folder into the split folder, renaming it in a format that will allow this
     * library to load it into the designated company.
     * @param $company_id
     * @throws Exception
     */
    private function _split_single_company($company_id)
    {
        // Find the unique filename identifier that will "link" the upload to the spawned child files.
        $this->output_filename_identifier = $this->_getOutputFilenameTag();
        $this->debug("output_filename_identifier. [{$this->output_filename_identifier}]");

        // Find the source filename stored in the upload folder.
        $upload_prefix = GetS3Prefix('upload', $this->identifier, $this->identifier_type);
        $source_filename = $this->_getUploadFilename();
        $this->debug("source_filename. [{$source_filename}]");

        // Construct the output filename
        $output_filename = "{$company_id}_{$this->output_filename_identifier}";
        $this->debug("output_filename. [{$source_filename}]");

        // Copy the upload file into the split folder, keeping the corresponding split filename for the target company.
        $split_prefix = GetS3Prefix('split', $this->identifier, $this->identifier_type);
        S3EncryptExistingFile(S3_BUCKET, $upload_prefix, $source_filename, $split_prefix, $output_filename);
        $this->debug("Staged the file into the split folder on S3.");

        // Audit it.
        $payload = array();
        $payload['SplitFilename'] = GetWorkflowProgressProperty($this->companyparent_id, 'companyparent', $this->wf_name, "OriginalFilename");
        $payload['UploadedFilename'] = $output_filename;
        AuditIt('File uploaded.', $payload, $this->user_id, $company_id);
    }

    private function _split_multiple_companies()
    {
        try
        {
            // Create a lookup so we can find the company based on the normalized
            // user input.
            $this->normalized_lookup = $this->_getNormalizedCompanyMappingLookup();
            $this->debug("creating normalized company mapping lookup");

            // Get a list of unique companies ids we will be mapping from the companyparent file
            // into the various company files.
            $this->unique_companies = $this->_getUniqueCompanyIds();
            $this->debug("finding unique mapped companies");

            // Find the unique filename identifier that will "link" the upload to the spawned child files.
            $this->output_filename_identifier = $this->_getOutputFilenameTag();
            $this->debug("output_filename_identifier[{$this->output_filename_identifier}]");

            // Open the upload file.
            $this->fh_upload = $this->_openUploadFile();
            $this->debug("opened file handle to the upload file.");

            // Open the company column file.
            $this->fh_company = $this->_openCompanyColumnFile();
            $this->debug("opened file handle company column file.");

            // Open output files.
            $this->_openCompanyOutputFiles();
            $this->debug("opened file handles to all output files");


            // Create an array called "line_to_company" that holds a single row for every
            // line in the file.  The array row will contain either the empty string or
            // the company_id that the upload file line number should be moved into.
            $line_to_company = array();
            $iterator = $this->_readTheFile($this->fh_company);
            foreach ($iterator as $iteration)
            {
                $iteration = A2PDecryptString($iteration, $this->encryption_key);
                $normalized = trim(strtoupper($iteration));
                if( isset($this->normalized_lookup[$normalized] ) )
                {
                    $line_to_company[] = GetArrayStringValue('CompanyId', $this->normalized_lookup[$normalized]);
                }
                else
                {
                    $line_to_company[] = '';
                }
            }
            $this->debug("created line2company array.  It contains [".count($line_to_company)."] records.");


            // Walk the upload file line by line.  Each line will be written to the
            // corresponding company output file based on the mappings built up in the
            // line_mappings array.  NOTE:  The header, or line zero, will be written
            // to all company files.
            $line = 0;
            $iterator = $this->_readTheFile($this->fh_upload);
            foreach ($iterator as $iteration)
            {
                if ($line === 0)
                {
                    // Write the HEADER to every output file.
                    foreach ($this->unique_companies as $company_id)
                    {
                        if (isset($this->handles[$company_id] )) {
                            $handle = $this->handles[$company_id];
                            if ( is_resource($handle) )
                            {
                                fwrite($handle, $iteration . "\n") ;
                            }
                        }

                    }
                }else
                {
                    $final_line = false;
                    if ( $line == count($line_to_company) -1 ) $final_line = true;

                    // In the off chance we dropped a line for some reason on parse, since we are
                    // walking the length of the original upload, we could try and access a line
                    // in the parse file that is not there.  If we are beyond scope, skip it rather
                    // than cause a PHP error.
                    if ( $line >= count($line_to_company) ) continue;

                    // Write the LINE to the output file corresponding to the mapped company.
                    $company_id = $line_to_company[$line];
                    if ( GetStringValue($company_id) !== '' )
                    {
                        if (isset($this->handles[$company_id] )) {
                            $handle = $this->handles[$company_id];
                            if ( is_resource($handle) )
                            {
                                if ( ! $final_line) fwrite($handle, $iteration . "\n") ;
                                if ( $final_line) fwrite($handle, $iteration ) ;
                            }
                        }
                    }

                }
                $line++;
            }


            // Audit it.
            foreach ($this->unique_companies as $company_id)
            {
                $filename = $company_id . "_" . $this->_getOutputFilenameTag();

                $payload = array();
                $payload['SplitFilename'] = GetWorkflowProgressProperty($this->companyparent_id, 'companyparent', $this->wf_name, "OriginalFilename");
                $payload['UploadedFilename'] = $filename;
                AuditIt('File uploaded.', $payload, $this->user_id, $company_id);
            }

            // Shut it down.
            $this->_closeHandles();

        } catch(Exception $e) {
            $this->_closeHandles();
            throw $e;
        }

    }

    /**
     * _closeHandles
     *
     * Attempt to close all possible file handles that may have been opened
     * during execution.  Make sure this is safe so it can be ran even if
     * the files are not open.
     *
     */
    private function _closeHandles()
    {
        // Close all open file handles in our handles lookup.
        if ( ! empty($this->handles) )
        {
            foreach($this->handles as $normalized=>$handle)
            {
                if ( is_resource($handle) ) fclose($handle);
            }
        }

        // Close individual file handles.
        if ( is_resource($this->fh_company) ) fclose($this->fh_company);
        if ( is_resource($this->fh_upload) ) fclose($this->fh_upload);
    }

    /**
     * _readTheFile
     *
     * Helper function to QUICKLY read a file into memory line by line.
     * @param $handle
     * @return Generator
     */
    private function _readTheFile($handle)
    {
        while(!feof($handle)) {
            yield trim(fgets($handle));
        }
    }

    /**
     * _openUploadFile
     *
     * Open a file handle to the companyparent upload file found in the companyparent
     * upload folder on S3.  Returns a READ file handle.
     *
     * @return bool|resource|null
     * @throws Exception
     */
    private function _openUploadFile()
    {
        // Open the upload file.
        $upload_prefix = GetS3Prefix('upload', $this->identifier, $this->identifier_type);
        $source_filename = $this->_getUploadFilename();
        $this->debug("Processing: [{$source_filename}]");
        return S3OpenFile(S3_BUCKET, $upload_prefix, $source_filename, 'r');
    }

    /**
     * _getUploadFilename
     *
     * Get the filename for the companyparent upload file found in the companyparent upload
     * folder on S3.  Returns STRING of filename.
     *
     * @return string
     * @throws Exception
     */
    private function _getUploadFilename()
    {
        // Open the upload file.
        $upload_prefix = GetS3Prefix('upload', $this->identifier, $this->identifier_type);
        $files = S3ListFiles(S3_BUCKET, $upload_prefix);
        if ( count($files) != 1 ) throw new Exception("Did not find exactly one file when reviewing uploads on S3.");
        $file = $files[0];
        $source_filename = fRightBack(GetArrayStringValue("Key", $file), "/");
        if ( $source_filename === '' ) throw new Exception("Unable to locate the source filename.");
        return GetStringValue($source_filename);
    }

    /**
     * _openCompanyColumnFile
     *
     * Look for the "Company" column file that was collected from the upload file.
     * This file contains only the data found in the upload file in the Company column.
     * Return a READ file handle for this file.
     *
     * @return bool|resource|null
     * @throws Exception
     */
    private function _openCompanyColumnFile()
    {
        $CI = $this->ci;

        // What column is the company column?
        $company_col_no = $CI->Mapping_model->get_mapped_column_no(null, $this->companyparent_id, 'company');

        // Find the file in the parsed folder that corresponds to the company column.
        // Once we find it, open a file handle to it.
        $prefix = GetS3Prefix('parsed', $this->identifier, $this->identifier_type);
        $files = S3ListFiles(S3_BUCKET, $prefix);
        if ( count($files) === 0 ) throw new Exception("Found no parsed files.");
        foreach($files as $file)
        {
            // If this file does not look like a column file, skip it.
            $filepath = GetArrayStringValue('Key', $file);
            $filename = fRightBack($filepath, '/');
            if ( ! StartsWith($filename, 'col') ) continue;

            // Figure out which column this is.
            $tag = fLeft($filename, '.');
            $col_no = replaceFor($tag, "col", "");

            // Open a handle, if this is the COMPANY column.
            if ( GetStringValue($col_no) === GetStringValue($company_col_no) )
            {
                return S3OpenFile(S3_BUCKET, $prefix, $filename, 'r');
                break;
            }
        }
    }

    /**
     * _openCompanyOutputFiles
     *
     * Open a WRITE file handle for each of the possible company files we will
     * be generating and place it into an associative array called "handles" indexed
     * by company_id.
     */
    private function _openCompanyOutputFiles()
    {

        S3MakeBucketPrefix(S3_BUCKET, 'split');

        // Find all of the companies that we need to create files for.
        if ( $this->unique_companies === null ) $this->unique_companies = $this->_getUniqueCompanyIds();

        // Loop each of the companies and create a unique file in the split folder on S3.
        foreach($this->unique_companies as $company_id)
        {
            // time to create the split file!
            $split_prefix = GetS3Prefix('split', $this->identifier, $this->identifier_type);
            if ( ! isset($this->handles[$company_id] ) )
            {
                $filename = "{$company_id}_{$this->output_filename_identifier}";
                $this->handles[$company_id] = S3OpenFile(S3_BUCKET, $split_prefix, $filename, 'w');
            }
        }
    }

    /**
     * _getOutputFilenameTag
     *
     * Find the UNIQUE string portion of the companyparent upload file.  Return that
     * so it can be used in the company files we will be generating.
     *
     * @return bool|string
     * @throws Exception
     */
    private function _getOutputFilenameTag()
    {
        $upload_prefix = GetS3Prefix('upload', $this->identifier, $this->identifier_type);
        $files = S3ListFiles(S3_BUCKET, $upload_prefix);
        if ( count($files) != 1 ) throw new Exception("Did not find exactly one file when reviewing uploads on S3.");
        $file = $files[0];
        $upload_filename = fRightBack(GetArrayStringValue("Key", $file), "/");
        $tag = fRight($upload_filename, "_");
        return $tag;
    }

    /**
     * _getNormalizedCompanyMappingLookup
     *
     * Create an associative lookup, by NORMALIZED COMPANY NAME, that returns
     * the mapping information the user elected to tell us which company this
     * the normalized data maps to.
     *
     * @return array
     */
    private function _getNormalizedCompanyMappingLookup()
    {
        $CI = $this->ci;

        $lookup = array();
        $company_mappings = $CI->CompanyParentMap_model->select_mappings($this->companyparent_id);
        foreach($company_mappings as $company)
        {
            $normalized = GetArrayStringValue('CompanyNormalized', $company);
            $lookup[$normalized] = $company;
        }

        return $lookup;
    }

    /**
     * _getUniqueCompanyIds
     *
     * Return an array of company ids that represents the full set of companies we will
     * be generating upload files for.
     *
     * @return array
     */
    private function _getUniqueCompanyIds()
    {
        $CI = $this->ci;

        $lookup = array();
        $data = $CI->CompanyParentMap_model->select_importdata($this->companyparent_id);
        foreach($data as $item)
        {
            $company_id = GetArrayStringValue('CompanyId', $item);
            $lookup[$company_id] = true;
        }
        return array_keys($lookup);
    }

    /**
     * _startCompanyJobs
     *
     * For each file found in the parent split folder, take that folder and move it
     * into the corresponding company folder and then kicks start the company background
     * process.
     *
     * If one or more of the files cannot be processed, an error message with the details
     * will be written to the log.
     *
     * @throws Exception
     */
    private function _startCompanyJobs()
    {
        $CI = $this->ci;
        $CI->load->helper("parentmapuploadcompanies");

        // For each company file we just created, move them into the company
        // folders and start the background task to process them.
        $split_prefix = GetS3Prefix('split', $this->identifier, $this->identifier_type);
        $files = S3ListFiles(S3_BUCKET, $split_prefix);
        foreach ( $files as $file )
        {
            $filename = fRightBack(GetArrayStringValue('Key', $file), '/');
            $company_id = fLeft($filename, '_');
            $company = $CI->Company_model->get_company($company_id);
            $import_date = GetPreferenceValue($this->companyparent_id, 'companyparent', 'companyparentmap', 'import_date');
            $first_import = false;
            try
            {
                // Make sure it still looks like the company is available to take on an import right now.
                $available = IsCompanyAvailableForParentMap($company_id);
                if ( ! $available ) throw new UIException("{$company_id}:The company had already started processing a different import before the request could be processed.");

                // Move the company file from the parent to the child.
                $this->debug("+ Moving file: " . $filename);
                $upload_prefix = GetS3Prefix('upload', $company_id, 'company');
                S3MakeBucketPrefix(S3_BUCKET, $upload_prefix);          // Make sure we HAVE a bucket.
                S3DeleteBucketContent(S3_BUCKET , $upload_prefix);
                S3EncryptExistingFile(S3_BUCKET, $split_prefix, $filename, $upload_prefix, $filename);
                if ( ! S3DoesFileExist(S3_BUCKET, $upload_prefix, $filename) ) throw new UIException("{$company_id}:Unable to move the uploaded file to the designated company.");

                // First Time Import
                // Set the start date in the company preferences if this is the first time the company has been loaded.
                $start_month = GetPreferenceValue($company_id, 'company', 'starting_date', 'month');
                if ( $start_month === '' )
                {
                    // Find the import date specified by the user.
                    $selected_import_date = GetPreferenceValue($this->companyparent_id, 'companyparent', 'companyparentmap', 'import_date');
                    $start_month = fLeft($selected_import_date, '/');
                    $start_year = fRightBack($selected_import_date, '/');

                    if ( $selected_import_date === '' || $start_month === '' || $start_year === '' )
                    {
                        throw new UIException("{$company_id}:This company has never uploaded a file before and we were unable to set the start month.");
                    }

                    // Set the companies start date to match the import date because this is the very first import we are doing for this company.
                    SavePreference($company_id, 'company', 'starting_date', 'month', $start_month);
                    SavePreference($company_id, 'company', 'starting_date', 'year', $start_year);
                    $first_import = true;
                    $this->debug("+ Set the Start Date: m[{$start_month}], y[{$start_year}]");

                    // Audit this transaction
                    $payload = array();
                    $payload["Month"] = $start_month;
                    $payload["Year"] = $start_year;
                    AuditIt('Set start month.', $payload, $this->user_id, $company_id, null);

                }

                // Archive the 'company' file that was uploaded for the support tool.  If the file ends in our
                // generic .upload file extension, replace that with a .csv extension.  This will cause less
                // confusion when someone downloads the file via the support tool.
                if ( EndsWith($filename, ".upload") )
                {
                    $archive_filename = ReplaceFor($filename, '.upload', '.csv');
                    SavePreference($company_id, 'company', 'upload', 'original_filename', $archive_filename);
                }
                ArchiveUpload($this->user_id, $company_id, 'company');


                // In development, if you want to turn off the background jobs for
                // testing, set below to false.
                if ( true )
                {

                    // Init Wizard
                    // Add a wizard record and mark the items complete that we have take care of already in the parent workflow.
                    if ( $CI->Wizard_model->does_wizard_record_exist($company_id) ) throw new UIException("{$company_id}:This company is already running a wizard.");
                    $CI->Wizard_model->create_wizard_record( $company_id, $this->user_id );
                    $CI->Wizard_model->startup_step_complete( $company_id );
                    $CI->Wizard_model->upload_step_complete( $company_id, $filename );
                    $this->debug("+ Initializing the Wizard");

                    // Parse Company File
                    // Kick off the file parse step for the company.
                    $group_id = $this->companyparent_id;
                    $controller = "ParseCSVUpload";
                    $function = "index";
                    $job_id = $CI->Queue_model->add_grouped_worker_job($this->companyparent_id, $company_id, $this->user_id, $group_id, $controller, $function);
                    if (GetStringValue($job_id) === '') throw new UIException("{$company_id}:Unable to start a background task.");
                    $this->debug("+ Queueing the job. job_id[{$job_id}]");

                    $snap = array();
                    $snap['CompanyId'] = $company_id;
                    $snap['CompanyName'] = GetArrayStringValue('company_name', $company);
                    $snap['Status'] = "Company import started.";
                    $snap['JobId'] = $job_id;
                    $snap['UploadFilename'] = $filename;
                    $snap['ImportDate'] = FormatDateMonthYYYY($import_date);
                    $snap['FirstImport'] = $first_import;
                    $this->addSnapshotData($snap);
                }


            }
            catch(UIException $e)
            {
                $company_id = fLeft($e->getMessage(), ":");
                $message = fRight($e->getMessage(), ":");

                if ( GetStringValue($company_id) !== '' )
                {
                    // If we know what company this is, write a more robust log message.
                    LogIt(__CLASS__, "ERROR: " . $message, $company, $this->user_id, $company_id, $this->companyparent_id);
                }
                else
                {
                    // Write an error message
                    LogIt(__CLASS__, "ERROR: " . $e->getMessage());
                }

                // Write a debug message letting us know we didn't do this.
                $this->debug(" - Skipping file: " . $filename . ".\n   " . $e->getMessage());

                $snap = array();
                $snap['CompanyId'] = $company_id;
                $snap['CompanyName'] = GetArrayStringValue('company_name', $company);
                $snap['Status'] = "Company import failed.";
                $snap['JobId'] = null;
                $snap['UploadFilename'] = $filename;
                $snap['ImportDate'] = FormatDateMonthYYYY($import_date);
                $snap['FirstImport'] = $first_import;
                $this->addSnapshotData($snap);
            }

        }
    }

}
