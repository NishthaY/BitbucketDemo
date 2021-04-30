<?php defined('BASEPATH') OR exit('No direct script access allowed');

class GenerateImportFiles extends A2PWizardStep {

	private 	$handles;			// Array of file handles used to read our data.
    private     $sql_handle;        // Singe file handle to the file that holds the SQL inserts.
    private     $b_sql_handle;      // Singe file handle to the file that holds the SQL inserts for beneficiary records.
    private     $info_handles;      // Array of file handles to the seekable info files for this company.
    private     $template;			// Read our template in once, and save it here.
    private     $b_template;	    // Read our template in once, and save it here for beneficiary records
	private 	$import_index;		// Index of current import file.
	private 	$row_index;			// Index of current row of the current import.
	private 	$mapping_objects;	// Collection of mapping objects.
	private 	$mapping_columns;	// Collection of mapping object names.
	private 	$replacement_keys;	// Collection of keys we will replace in our template.
	private		$import_prefix;		// Path on S3 to the import files.
	private		$parsed_prefix;		// Path on S3 to the parsed files.
	private 	$import_date;		// Montly date range associated with import data.
    private     $_default_plan;     // Default plan to be used if the plan column needs defaulted.


	function __construct()
    {
        // Construct our parent class
        parent::__construct(true);

		// Toggle these for Research and Debugging.
		$this->timers 	= false;
		$this->debug 	= false;

		// Include any shared items.
		$this->load->helper("wizard");
        $this->db = $this->load->database('default', TRUE);

        $this->_default_plan = null;

    }
    function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
		// index
        //
		// This controller is responsible for creating N number of import
		// files that contain insert statement for all of the mapped data.
		// The data is read from the parsed column files, normalized and then
		// turned into an insert statement.  The statement is stored in an
		// SQL file found in the import folder for the customer for later
		// import.
		// ---------------------------------------------------------------

        parent::index($user_id, $company_id, $companyparent_id, $job_id);
        $this->import_prefix  = "";
        $this->parsed_prefix  = "";

        try {

            // Record when we start.
            $this->timer("start");

			// Validate our input.
            if ( getStringValue($user_id) == "" ) throw new Exception("Invalid input user_id.");
            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");

            // Get our import date and start our support timer.
            $import_date = GetUploadDate($company_id);
            SupportTimerStart($company_id, $import_date, __CLASS__, null);

            $companyparent_id = GetCompanyParentId( $company_id );

			// Calculate our S3 bucket prefixes that we will need.
            $this->import_prefix  = replaceFor(GetConfigValue("import_prefix"), "COMPANYID", $company_id);
            $this->parsed_prefix  = replaceFor(GetConfigValue("parsed_prefix"), "COMPANYID", $company_id);

			// Create our import prefix on S3 and/or empty the existing one.
            S3MakeBucketPrefix( S3_BUCKET, $this->import_prefix );
            S3DeleteBucketContent( S3_BUCKET, $this->import_prefix );

			// Check and see if this file has a header row.
			$has_headers = DoesUploadContainHeaderRow($company_id);

			// Pull a list of the required mapped columns
			$this->mapping_columns = $this->Mapping_model->get_mapping_columns($company_id, $companyparent_id);

			// For every mapped column, open a file handle to the parsed file.
			$this->handles = array();
            foreach($this->mapping_columns as $column_data)
            {
				// Organize some data about this column we are processing.
                $column_name = getArrayStringValue("name", $column_data);
                $class_name = ucfirst(strtolower($column_name));
                $mapped_column  = $this->Mapping_model->get_upload_column_for_mapping($company_id, 'company', $column_name); // col#

				// Only take action if the column is mapped.
				if ( getStringValue($mapped_column) != "" ) {

					// Create an array with all the details about this column.
					$row = array();
					$row['column_name'] = $column_name;
					$row['class_name'] = $class_name;
					$row['mapped_column'] = $mapped_column;
                    $row['encrypt_data'] = GetArrayStringValue("encrypted", $column_data);
					$row['column_no'] = replaceFor($mapped_column, "col", "");



					// Now add an opened file pointer to the column file.
					$filename = "{$mapped_column}.txt";
					if ( S3DoesFileExist(S3_BUCKET, $this->parsed_prefix, "{$filename}") )
					{
						$filepath = "s3://" . S3_BUCKET . "/" . $this->parsed_prefix . "/{$filename}";
						$row["fp"] = fopen($filepath, 'r');
						$this->handles[] = $row;
						$this->debug("{$filepath} OPENED.");

					}
				}
			}
			$this->timer("S3 files Opened.");


            // Open pointers to the info files associated with this import and companyl.
            $this->info_handles = OpenSeekableInfoFiles($company_id, 'company');
            $this->timer("Info files Opened.");


			// Figure out what the import date is for this data set.
			$this->import_date = GetUploadDate($company_id);


			// Which columns are the beneficiary columns?
            $this->beneficiary_columns = GetDistinctTargetsByFeatureCodeForCompany($company_id, 'BENEFICIARY_MAPPING');


			// We will now read each of the column files, one after the other,
			// resulting in a line read of the file for just the columns that are mapped.
			$index = 0;
			$row_number = 1;
			$temp = array();
			$this->mapping_objects = array();
			while (($line = fgets($this->handles[0]["fp"])) !== false)
			{
				$index++;

                // Use the info files to decide if we should or should not conduct business logic while doing
                // the data imports by column.
                $is_beneficiary_row = IsBeneficiaryImportLine($index, $this->info_handles);
                $is_default_plan_row = IsDefaultPlanLine($index, $this->info_handles);


				// Clean out our storage array.  ( Don't make a new one!!! That is too memory intensive. )
				foreach( $temp as $key=>$value ) { $temp[$key] = null; }

				// Read the "line" from each file and store it into the temp array.
				for($i=0;$i<count($this->handles);$i++)
				{
					// Pull the line out of this file.
					if ( $i != 0 ) $line = fgets($this->handles[$i]["fp"]);

                    // Normalize the line.
                    $class_name = $this->handles[$i]["class_name"];
                    $column_no = $this->handles[$i]["column_no"];

					// clean the line.
					$line = trim($line);

					// DEFAULT PLAN
                    // If this is the PLAN column we are processing and the default_plan.info file
                    // indicates we should overwrite and replace the plan with the default plan value
                    // do that as we process the data.
					if ( $class_name == 'Plan' && $is_default_plan_row )
					{
					    // If we have never lookup up the default plan before, do that now.
					    if ( $this->_default_plan === null )
                        {
                            $feature_type = $this->feature_type = $this->Feature_model->get_feature_type('DEFAULT_PLAN');
                            if ( $feature_type === 'company feature') $this->_default_plan = GetPreferenceValue($company_id, 'company', 'plan', 'default_plan_code');
                            else if ( $feature_type === 'companyparent feature') $this->_default_plan = GetPreferenceValue(GetCompanyParentId($company_id), 'companyparent', 'plan', 'default_plan_code');
                            else if ( $feature_type === 'company feature with parent override')
                            {
                                $this->_default_plan = GetPreferenceValue(GetCompanyParentId($company_id), 'companyparent', 'plan', 'default_plan_code');
                                if ( $this->_default_plan === '' ) $this->_default_plan = GetPreferenceValue($company_id, 'company', 'plan', 'default_plan_code');
                            }
                            else $this->_default_plan = '';
                        }
					    $line = $this->_default_plan;
                    }


					// Decrypt the data, unless told not to.
					if ( $this->handles[$i]["encrypt_data"] === 'f' )
                    {
                        if ( IsEncryptedString($line) )
                        {
                            if ( substr_count($line, ":") !== 2)
                            {
                                $payload = array();
                                $payload["line"] = $line;
                                $payload["class_name"] = $class_name;
                                $payload["column_no"] = $column_no;
                                $payload["index"] = $index;
                                LogIt("Encryption Error", "Malformed encryption string.", $payload);
                                throw new Exception("Malformed encryption string.  Panic.");
                            }
                        }
                        $line = A2PDecryptString( $line, $this->encryption_key);
                    }


					// Create the mapping object if we have never seen it before.
					if ( ! isset($this->mapping_objects[$class_name] ) ) {
						if ( file_exists(APPPATH."libraries/mapping/{$class_name}.php") )
						{
							$this->load->library("mapping/{$class_name}");
							$object = new $class_name($company_id, 'company', $column_no);
							$object->encryption_key = $this->encryption_key;
							$this->mapping_objects[$class_name] = $object;
						}
					}

					// Use the existing mapping object if we have already created it.
					if ( isset($this->mapping_objects[$class_name] ) )
					{
						$object = $this->mapping_objects[$class_name];
						$line = $object->normalize($line);
						if ( $line === FALSE ) $line = null;
					}

					// Store it.
					if ( $has_headers && $index == 1 )
					{
						// Skip the header line if needed.
					}
					else
					{
						$temp[ $this->handles[$i]["column_name"] ] = $line;
					}


				}

				// Default Grouping
				// We must have a plan_type, plan, coverage tier.  They had to map
				// plantype, but could optionaly map plan and coverage tier.  If the
				// user did not specify plan and coverage_tier then we will infer
				// that data from the parent association.
				if ( getArrayStringValue("plan", $temp) == "" ) $temp['plan'] = getArrayStringValue("plan_type", $temp);
				if ( getArrayStringValue("coverage_tier", $temp) == "" ) $temp['coverage_tier'] = getArrayStringValue("plan", $temp);


				// Insert the record.
				if ( getArrayStringValue("plan_type", $temp) != "" )
				{
					// Record this ... if it's not empty.

                    $this->_write_insert_record($company_id, $this->import_date, $temp, $row_number, $is_beneficiary_row);
				}
				$row_number++;



			}
			$this->timer("import file created");

			// Close all of the file handles.
			$this->_close_files();
			$this->timer("S3 files Closed.");


			// Secure the import folder
            $this->timer("encrypting all files");
            $this->debug("encrypting all files");
			S3EncryptAllFiles(S3_BUCKET, $this->import_prefix);


			// Import Generation Complete!
			// Throw the next step onto the queue.
            $this->schedule_next_step("LoadImportFiles");

            // Record when we start.
            $this->timer("end");

        }
        catch(Exception $e)
        {

            // We need to see this error in the process queue.  Write to STDOUT.
            print "Exception! " . $e->getMessage() . "\n";

			// If we have an exception, we must attempt to close the files.
			$this->_close_files();

			// Report the failure to the user.
			SendDataValidationFailedEmail($user_id, $company_id);

            // Record when we end.
            $this->timer("end");
        }

        // Update the UI, notifying anyone watching that this
        // step is complete.
        NotifyStepComplete($company_id);
        SupportTimerEnd($company_id, $import_date, __CLASS__, null);
    }
	private function _close_files() {

		// _close_files
		//
		// Look at all of the file handles we have and if any are open,
		// close them down.
		// --------------------------------------------------------------

		// Loop the input handles array.  Every handle found in the array, close it.
        if ( ! empty($this->handles) )
        {
            foreach($this->handles as $item)
    		{
    			$handle = null;
    			if ( isset($item["fp"]) ) $handle = $item["fp"];

    			if($handle) {
    				$key = getArrayStringValue("column_name", $item);
    				$this->debug("closing file for column {$key}.");
    				fclose($handle);
                    $this->debug("closed file for column {$key}.");
    			}
    		}
        }

		// Close down the import file handle too.  ( output )
        $this->debug("closing file for original import.");
        if ( is_resource($this->sql_handle) ) fclose($this->sql_handle);
        if ( is_resource($this->b_sql_handle) ) fclose($this->b_sql_handle);

        // Close down the info file handles
        CloseSeekableInfoFiles($this->info_handles);

        $this->debug("closed file for original import.");
	}
	private function _start_new_import_file() {

		// _start_new_import_file
		//
		// Create a new indexed output folder.  If we have an output file
		// open, close it.  Then create a new output file with an incramented
		// index.  Return the S3 file url.
		// ------------------------------------------------------------------

		// What is the next index number for this file?
		if ( ! $this->import_index ) $this->import_index = 0;
		$this->import_index++;

		// If we already have an open file, close it.
		if ( is_resource($this->sql_handle) )
		{
			fclose($this->sql_handle);
			$this->timer("import file created");
		}

		// Create the sql file.
		$filename = "s3://" . S3_BUCKET . "/" . $this->import_prefix . "/import{$this->import_index}.sql";
		$this->sql_handle = fopen($filename, 'w');
        if ( ! is_resource($this->sql_handle) ) throw new Exception("Could not create {$filename}");
		fclose($this->sql_handle);

        // Open the sql file.
		$this->sql_handle = fopen($filename, 'a');
        if ( ! is_resource($this->sql_handle) ) throw new Exception("Could not append to {$filename}");

		// Reset our row counter
		$this->row_index = 0;


        // BENEFICIARY
        // We are going to shunt off the beneficiary data into a different import table.
        // Create another import file that is called something else.
        if ( is_resource($this->b_sql_handle) )
        {
            fclose($this->b_sql_handle);
        }

        // Create the beneficiary sql file.
        $b_filename = "s3://" . S3_BUCKET . "/" . $this->import_prefix . "/beneficiary_import{$this->import_index}.sql";
        $this->b_sql_handle = fopen($b_filename, 'w');
        if ( ! is_resource($this->b_sql_handle) ) throw new Exception("Could not create {$b_filename}");
        fclose($this->b_sql_handle);

        // Open the beneficiary sql file.
        $this->b_sql_handle = fopen($b_filename, 'a');
        if ( ! is_resource($this->b_sql_handle) ) throw new Exception("Could not append to {$b_filename}");



		// return the S3 filename.
		$this->debug("writing {$filename}");
		return $filename;

	}
    private function _write_insert_record($company_id, $import_date, &$data, $row_number, $is_beneficiary_row) {

		// _write_insert_record
		//
		// Write a new insert sql line for the given input.  Move to a new file
		// if needed.  Make sure you escape ( by type ) the data as you Create
		// the insert statement.
		//
		// Note, do your string work with native PHP commands.  Update the
		// data in the $data array by reference so we can keep a single Array
		// in memory.
		// ---------------------------------------------------------------

		if ( $this->import_index == 0 ) $this->_start_new_import_file();
		if ( MAX_INSERTS_PER_IMPORT != 0 && $this->row_index >= MAX_INSERTS_PER_IMPORT ) $this->_start_new_import_file();

		// We must insert all mapping columns.  Fill in any that are missing.
        $data['company_id'] = intval($company_id);
        $data['import_date'] = $this->db->escape(getStringValue($import_date));
        $data['finalized'] = $this->db->escape("f");
		$data['row_number'] = intval($row_number);
		foreach($this->mapping_columns as $mapping )
		{
			$column_name = $mapping["name"];
			if ( empty($data[$column_name]) ) $data[$column_name] = null;
		}

		// Now we must clean each of the mappings
		foreach($this->mapping_columns as $mapping )
		{

			$column_name = $mapping["name"];
			switch($column_name)
			{
				case "annual_salary":
				case "employer_cost":
					// Money columns need to be formatted a float and/or go in as null.
					$data[$column_name] = ( $data[$column_name] == null || $data[$column_name] == "" ? "null" : floatval( $data[$column_name] ));
					break;
				case "company_id":
				case "age":
					// Integer columns need to be formatted an int and/or go in as null.
					$data[$column_name] = ( $data[$column_name] == null || $data[$column_name] == "" ? "null" : intval( $data[$column_name] ));
					break;
				case "ssn":
                case "employee_ssn":

					// We are going to tack on an ssn_display column.
					// This feels the the most effecient time to do so.
                    $column_name_display = $column_name . "_display";
					$ssn = $data[$column_name];
					$ssn = trim($ssn);
					if ( $ssn == null || $ssn == "" )
					{
						$data[$column_name] = "null";
						$data[$column_name_display] = "null";
					}
					else
					{
                        $data[$column_name] = $this->db->escape( $ssn );
                        if ( IsEncryptedString($ssn) ) $ssn = A2PDecryptString($ssn, $this->encryption_key);
						$data[$column_name_display] = $this->db->escape( "###-##-" . substr($ssn, -4) );
					}
					break;
				default:
					$data[$column_name] = ( $data[$column_name] == null || $data[$column_name] == "" ? "null" : $this->db->escape( $data[$column_name] ));
					break;
			}

		}


		// Create an array of replacement tags we will used to generate the sql statement.
		if ( ! $this->replacement_keys ) {
			$this->replacement_keys = array();
			foreach(array_keys($data) as $key)
			{
				$this->replacement_keys[] = "{".$key."}";
			}
		}


        // Output the row to the appropriate file.
        if( $is_beneficiary_row )
        {
            // Collect our template from disk if we have not already done so.
            if ( $this->b_template == null ) {
                $this->b_template = file_get_contents(APPPATH . "../database/template/CompanyBeneficiaryImportINSERT.sql");
                $this->b_template = trim($this->b_template);
            }

            // PHP, do your thing.  Replace things in the template that match our keys.
            $sql = $this->b_template;
            $output = str_replace( $this->replacement_keys, $data, $this->b_template ) ;

            // Write the row.
            fputs ($this->b_sql_handle, "{$output}\n");
        }
        else
        {
            // Collect our template from disk if we have not already done so.
            if ( $this->template == null )
            {
                $this->template = file_get_contents(APPPATH . "../database/template/ImportDataINSERT.sql");
                $this->template = trim($this->template);
            }

            // PHP, do your thing.  Replace things in the template that match our keys.
            $sql = $this->template;
            $output = str_replace( $this->replacement_keys, $data, $this->template ) ;

            // Write the row.
            fputs ($this->sql_handle, "{$output}\n");
        }

		$this->row_index++;

    }

}

/* End of file GenerateImportFiles.php */
/* Location: ./application/controllers/cli/GenerateImportFiles.php */
