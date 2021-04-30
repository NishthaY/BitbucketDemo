<?php
function DisableA2PAutoColumnMapping($identifier, $identifier_type)
{
    SavePreference($identifier, $identifier_type,"mapping", "a2p_suggestions", "DISABLED");

}
function EnableA2PAutoColumnMapping($identifier, $identifier_type)
{
    SavePreference($identifier, $identifier_type,"mapping", "a2p_suggestions", "ENABLED");
}
function IsA2PAutoColumnMappingEnabled($identifier, $identifier_type)
{
    $CI = &get_instance();

    // If we have a preference indicating that the auto mapping feature
    // has been disabled, then return FALSE.
    $value = GetPreferenceValue($identifier, $identifier_type, 'mapping', 'a2p_suggestions');
    if ( strtoupper($value) === 'DISABLED' ) return false;


    // Always enable automapping on the company parent.
    if ( $identifier_type === 'companyparent' ) return true;

    //  If it was not disabled, but we have finalized data for this company,
    // then disabled it.
    if ( $identifier_type === 'company' )
    {
        if ( $CI->Reporting_model->does_company_have_finalized_data($identifier) )
        {
            DisableA2PAutoColumnMapping($identifier, $identifier_type);
            return false;
        }
    }

    // If the value is not DISABLED, then we will consider it enabled.
    return true;

}
function ArchiveColumnMappings( $company_id, $user_id ) {

    // ArchiveColumnMappings
    //
    // This function will collect all of the information set on the Mappings
    // screen and save a snapshot for future reference.
    // ---------------------------------------------------------------------

    $CI = &get_instance();

    // Organize our Snapshot Data
    $data = $CI->Archive_model->select_column_mappings_for_archive($company_id, 'company');
    ArchiveHistoricalData($company_id, 'company', "column_mappings", $data, array(), $user_id);
}

function AllRequireColumnsMatched($identifier, $identifier_type) {

    // Initialize Singleton
    $CI = &get_instance();
    $CI->load->model("Mapping_model", "mapping_model", true);

    $company_id = $identifier;
    if ( $identifier_type === 'company' )
    {
        $company_id = $identifier;
        $companyparent_id = GetCompanyParentId($company_id);
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $company_id = null;
        $companyparent_id = $identifier;
    }
    else
    {
        throw new Exception(__FUNCTION__ . ": Unknown identifier type.");
    }


    // Grab a list of all required columns, Verify that we have a mapping
    // for each one.  If not, error.
    $items = $CI->Mapping_model->get_required_mapping_columns($company_id, $companyparent_id);

    foreach($items as $item)
    {
        $column_name = getArrayStringValue("name", $item);
        $mapped_column_no = $CI->Mapping_model->get_mapped_column_no($company_id, $companyparent_id, $column_name);
        if ( $mapped_column_no === FALSE ) {
            $column_info = $CI->Mapping_model->get_mapping_column_by_name($column_name, $company_id,  $companyparent_id);
            $display = getArrayStringValue("display", $column_info);
            $display = strtolower($display);
            return "The column {$display} must be matched before you can continue.";
        }
    }

    return true;
}
function QuickScanPreviewFile($identifier, $identifier_type, $encryption_key){

    // QuickScanPreviewFile
    //
    // This function will scan the company preview file for obvious
    // validation errors.  This is intended to be q quick scan so the
    // complicated items will be skipped.  If there is a problem,
    // we will redirect back to the correction page.  If not, we will
    // move forward in the wizard.
    // ---------------------------------------------------------------

    try {

        // Initialize Singleton
        $CI = &get_instance();
        $CI->load->model("Wizard_model", "wizard_model", true);
        $CI->load->model("Mapping_model", "mapping_model", true);
        $CI->load->helper('s3');
        $CI->load->helper('wizard');


        // We must have an identifier.
        if ( getStringValue($identifier) == "" ) throw new Exception("Missing required input identifier");
        if ( getStringValue($identifier_type) == "" ) throw new Exception("Missing required input identifier_type");

        // Organize the company and companyparent ids based on the identifier.
        if ( $identifier_type === 'company')
        {
            $company_id = $identifier;
            $companyparent_id = GetCompanyParentId($company_id);
        }
        else if ( $identifier_type === 'companyparent' )
        {
            $company_id = null;
            $companyparent_id = $identifier;
        }
        else
        {
            throw new Exception(__FUNCTION__ . ": Unknown identifier type.");
        }

        // for debugging, you can control how many lines we will process.
        // Make this high enough that it's always larger than the preview file length.
        $max_lines = 1000;

        // Look for our upload file and make sure we can see it.
        $client = S3GetClient();
        $errors_prefix = GetS3Prefix('errors', $identifier, $identifier_type);
        $parsed_prefix = GetS3Prefix('parsed', $identifier, $identifier_type);

        // Review the companies preferences and see if the upload file
        // contains header or not.
        $has_headers = DoesUploadContainHeaderRow( $company_id, $companyparent_id );

        // FIXME: This was not used.  Should it be pulled or is there a bug?
        // Pull a list of the required mapped columns
        //$mapping_columns = $CI->Mapping_model->get_mapping_columns($company_id, $companyparent_id);

        // Remove any previous mapping attempts.
        $CI->Validation_model->delete_validation_errors($company_id, 'company');
        S3DeleteBucketContent( S3_BUCKET, $errors_prefix );

        // Open a connection to the preview file.
        $filename = "s3://" . S3_BUCKET . "/{$parsed_prefix}/preview.csv";


        // Read the preview file line by line and parse the mapped columns
        // looking for errors.
        $fh = fopen($filename, "r");
        if ($fh) {

            $output = array();
            $line_no = 1;

            // Skip line 1 if there are headers.
            if ( $has_headers ) $line = fgets($fh);
            if ( $has_headers ) $line_no = 2;

            // Process the preview file.
            while ( $line_no < $max_lines && ($line = fgets($fh)) !== false) {

                $line = trim($line);
                $line = A2PDecryptString($line, $encryption_key);
                $csv = str_getcsv($line);

                // Look at each field in the line.
                $col_no = 0;
                $bad_columns = array();		// Keep a lookup of each column that is no good for this row.
                $error_messages = array();	// Keep a list of errors we encounter on this row.
                $sample = array();			// Save a copy of the row, but only the mapped fields.
                foreach($csv as $item)
                {
                    $item = trim($item);
                    $mapped_column_name = GetPreferenceValue($identifier, $identifier_type, 'column_map', "col{$col_no}");

                    // If the column is mapped, save that to our sample row which
                    // holds only mapped columns.
                    if( $mapped_column_name !== FALSE )
                    {
                        $sample[] = $item;
                    }

                    // There are a few "complex" columns.  We can not fast check these so just
                    // skip them.  We will catch them on the deep validation.
                    $skip = false;
                    if ($mapped_column_name === FALSE ) $skip = true;
                    if (!$skip && $mapped_column_name == "coverage_tier" ) $skip = true;
                    if (!$skip && $mapped_column_name == "plan" )  $skip = true;
                    if (!$skip && $mapped_column_name == "volume" )  $skip = true;

                    if ( $skip ) {
                        $col_no++;
                        continue;
                    }


                    // Excellent.  We have a mapped column.  Let's check the field for errors.
                    $class_name = ucfirst($mapped_column_name);
                    if ( file_exists(APPPATH."libraries/mapping/{$class_name}.php") )
                    {
                        // YES!  We know how to validation this mapping type.
                        // Evaluate the data provided and keep track of any errors.
                        $CI->load->library("mapping/{$class_name}");
                        $object = new $class_name($identifier, $identifier_type, $col_no);
                        $object->encryption_key = $encryption_key;
                        $normalized = $object->normalize($item);

                        if ( $normalized === FALSE ) // BAH: Must be exactly false, else empty string will be caught as invalid.
                        {
                            $column_data = $CI->Mapping_model->get_mapping_column_by_name($mapped_column_name, $company_id, $companyparent_id);
                            $column_display = getArrayStringValue("display", $column_data);
                            $column_display = strtolower($column_display);
                            $message = "The {$column_display} column on row {$line_no} could not be validated.";
                            $object->save_upload_error($line_no, $object->validation_error_type, $message);
                            $bad_columns[] = $mapped_column_name;
                            $error_messages[] = $message;
                        }
                    }
                    $col_no++;

                }


                // If we have bad columns, generate an output array that matches
                // the deep validation structure.
                if ( ! empty($bad_columns) )
                {
                    $lookup = array();
                    foreach($bad_columns as $bad_column)
                    {
                        $lookup[$bad_column] = $bad_column;
                    }
                    $lookup["data"] = $sample;
                    $lookup["messages"] = $error_messages;
                    $output[$line_no] = $lookup;

                }
                $line_no++;
            }

            fclose($fh);
        }

        if ( ! empty($output) ) {

            // Errors were found.  Save the output file to S3 and mark the
            // review and validation complete so we can get to the error page.
            $prefix = GetS3Prefix('errors', $identifier, $identifier_type);
            S3MakeBucketPrefix(S3_BUCKET, $prefix);
            S3SaveEncryptedFile(S3_BUCKET, $prefix, "errors.json", json_encode($output));

            return false;

        }


    }
    catch ( UIException $e ) { return false; }
    catch( SecurityException $e ) { return false; }
    catch( Exception $e ) { return false; }


    return true;
}

/**
 * OpenSeekableInfoFiles
 *
 * Open a hash lookup of file pointers for each parsed column info file.
 * Each pointer will be a seekable connection to the file.
 * These files are used to answer business logic questions about an import row.
 *
 * @param $identifier
 * @param $identifier_type
 * @return array
 */
function OpenSeekableInfoFiles($identifier, $identifier_type)
{
    $handles = array();
    $prefix = GetS3Prefix('parsed', $identifier, $identifier_type);

    // beneficiaries.info
    if ( S3DoesFileExist(S3_BUCKET, $prefix, 'beneficiaries.info') )
    {
        $handles['beneficiaries'] = S3OpenSeekableFile(S3_BUCKET, $prefix, 'beneficiaries.info', 'r');
    }

    // default_plan.info
    if ( S3DoesFileExist(S3_BUCKET, $prefix, 'default_plan.info') )
    {
        $handles['default_plan'] = S3OpenSeekableFile(S3_BUCKET, $prefix, 'default_plan.info', 'r');
    }

    return $handles;
}

/**
 * CloseSeekableInfoFiles
 *
 * Close all files referenced in the provided hashed lookup of file pointers.
 *
 * @param $handles
 */
function CloseSeekableInfoFiles($handles)
{
    $keys = array_keys($handles);
    foreach($keys as $key)
    {
        if ( is_resource($handles[$key]) ) fclose($handles[$key]);
    }
}

/**
 * IsIgnoredImportLine
 *
 * This function will tell us, by line number, if the data found on the import
 * for the corresponding row will be ignored by our system or not.  If it is
 * ignored, we do not want to do any validation and/or other possible business logic.
 *
 * The first "line" of a file is line #1.  ( not zero indexed )
 *
 * @param $line_no
 * @param $handles
 * @param string $tag
 * @return bool
 */
function IsIgnoredImportLine( $line_no, $handles, $tag='' )
{
    $ignore = false;

    // BENEFICIARIES
    if ( IsBeneficiaryImportLine($line_no, $handles, $tag) ) $ignore = true;

    return $ignore;
}

/**
 * IsBeneficiaryImportLine
 *
 * This function will tell us if we think the line number in question contains
 * beneficiary data or not.
 *
 * The first "line" of a file is line #1.  ( not zero indexed )
 *
 * @param $line_no
 * @param $handles
 * @param string $tag
 * @return bool
 */
function IsBeneficiaryImportLine( $line_no, $handles, $tag='' )
{
    // BENEFICIARIES
    // No beneficiaries file handle, you may not ignore the line.
    if ( isset($handles['beneficiaries']) )
    {
        if ( is_resource($handles['beneficiaries']) )
        {
            // DEBUG: set to true.
            if ( false )
            {
                $seek = $line_no - 1;
                fseek( $handles['beneficiaries'], $seek);
                $char = fgetc($handles['beneficiaries']);
                if( getStringValue($tag) != '' ) LogIt('IsIgnoredImportLine', "tag[$tag], line_no[$line_no], seek[$seek], char[$char]");
            }

            // Does this info file have a "1" character in it's line number place in the beneficiaries.info file?
            // If so, this is a beneficiary line and we are going to ignore validation on those.
            fseek( $handles['beneficiaries'], $line_no - 1);
            if ( fgetc($handles['beneficiaries']) == '1' ) {
                return TRUE;
            }
        }
    }
    return FALSE;
}

/**
 * IsDefaultPlanLine
 *
 * The decision to use the default plan specified on the feature, vs the default
 * inheritance behavior is decided at import time, line by line.  That decision is
 * stored in an info file.
 *
 * Check to see if the we should replace the plan value with the default
 * plan code.  Returns TRUE/FALSE.
 *
 * @param $line_no
 * @param $handles
 * @return bool
 */
function IsDefaultPlanLine($line_no, $handles)
{
    $is_default_plan_row = FALSE;
    if ( isset($handles['default_plan']) )
    {
        $is_default_plan_row = InfoIsLineActivated($line_no, $handles['default_plan'] );
    }
    return $is_default_plan_row;
}


/* End of file match_helper.php */
/* Location: ./application/helpers/match_helper.php */
