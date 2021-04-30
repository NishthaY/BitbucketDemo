<?php


/**
 * CreateBeneficiaryInfoFile
 *
 * We need to know, very early on in the upload process, which rows in a file
 * contain beneficiary data.  This function is responsible for figuring out which
 * columns in the input file contain the data needed to make that decision.  Then
 * evaluate the data in those columns and build a beneficiary.info file.
 *
 * An info file is a collection of 1s and 0s.  One character per line in the file.
 * if the data is a column header, it will be X.  If it contains beneficiary data it
 * will be 1 else 0.
 *
 * This file will be used when processing the column data to quickly and easily
 * answer beneficiary data questions with a simple single character read.
 *
 * The beneficiary.info file is stored in the customers PARSE folder next to the
 * columns it holds data against.  We use the local "copy" folder to build this file
 * but when done, no data is stored locally.
 *
 * @param $identifier
 * @param $identifier_type
 */
function CreateBeneficiaryInfoFile($identifier, $identifier_type)
{
    $CI = &get_instance();

    // Write log files to help you make sure the info file
    // is getting created correctly.
    $debug = false;

    $fh_info = null;
    $fh_source = null;
    $fh_temp = null;
    try
    {

        // COLLECT & ORGANIZE DATA
        // We will need a few datapoints collected before we start.
        $company_id = $identifier;
        $companyparent_id = GetCompanyParentId($company_id);
        if ( $identifier_type === 'companyparent' )
        {
            $company_id = null;
            $companyparent_id = $identifier_type;
        }
        $encryption_key = GetEncryptionKey($identifier, $identifier_type);
        $prefix = GetS3Prefix('parsed', $identifier, $identifier_type);

        if ( $debug ) LogIt(__FUNCTION__, 'company_id', $company_id);
        if ( $debug ) LogIt(__FUNCTION__, 'companyparent_id', $companyparent_id);
        if ( $debug ) LogIt(__FUNCTION__, 'identifier', $identifier);
        if ( $debug ) LogIt(__FUNCTION__, 'identifier_type', $identifier_type);
        if ( $debug ) LogIt(__FUNCTION__, 'encryption_key', $encryption_key);



        // BENEFICIARY COLUMNS
        // Find all columns that contain data that may indicate a beneficiary row.
        $maps_lookup = array();

        $beneficiary_columns = GetDistinctTargetsByFeatureCodeForCompany($company_id, 'BENEFICIARY_MAPPING', true);
        if ( $debug ) LogIt(__FUNCTION__, 'beneficiary_columns', $beneficiary_columns);

        // BENEFICIARY COLUMN MAPS
        // When we review the beneficiary columns, each has a collection of MAPS that we are going to look
        // for.  If we find a MAP in the the column, then it indicates the row contains beneficiary data.
        foreach($beneficiary_columns as $column_code)
        {
            // Find a collection of MAPS that indicate the data in this row indicates the row contains beneficiary data.

            $feature_identifier = $company_id;
            $feature_identifier_type = GetFeatureIdentifierTypeForTargetableFeature($company_id, 'BENEFICIARY_MAPPING', 'mapping_column', $column_code);
            if ( $feature_identifier_type === 'companyparent' ) $feature_identifier = GetCompanyParentId($company_id);

            if ( $debug ) LogIt(__FUNCTION__, 'feature identifier', "column_code[{$column_code}], identifier[{$feature_identifier}], identifier_type[{$feature_identifier_type}]");
            $maps_lookup[$column_code] = $CI->Beneficiary_model->list_maps_for_column( $feature_identifier, $feature_identifier_type, $column_code );
        }
        if ( $debug ) LogIt(__FUNCTION__, 'maps_lookup', $maps_lookup);


        // LOOKUP: BENEFICIARY COLUMN -> UPLOADED COLUMN FILE
        // Create a lookup of beneficiary column code to column token which is basically the column filename.
        $files_lookup = array();
        $mapped_columns = $CI->Mapping_model->get_mapped_columns($company_id, $companyparent_id);
        if( ! empty($beneficiary_columns) )
        {
            foreach($beneficiary_columns as $column_name)
            {
                $index = array_search($column_name, array_column($mapped_columns, 'Value') );
                if ( $index !== FALSE )
                {
                    $files_lookup[$column_name] = GetArrayStringValue('GroupCode', $mapped_columns[$index]) . ".txt";
                    if ( $debug ) LogIt(__FUNCTION__, "files_lookup[$column_name]", $files_lookup[$column_name]);
                }
                else
                {
                    // Okay, column mapping has not yet happened!  We will just stop here as there is not much we
                    // can do at this point.  We will refresh the beneficiary.info file later in the process.
                    if ( $debug ) LogIt(__FUNCTION__, "Did not find the mapped column!", $column_name);
                    return;
                }
            }
        }

        // NO BENEFICIARIES
        // First, create a file that indicates there are no beneficiaries at all.  This is a
        // column file that is the same height as the file, but every row in that file has "zero" in
        // it indicating not a beneficiary row.
        $count = 0;
        $fh_source = S3OpenFile(S3_BUCKET, $prefix, "col0.txt", 'r');
        InfoFileDelete( $identifier, $identifier_type, 'beneficiaries');
        $fh_info = InfoFileOpen($identifier, $identifier_type, 'beneficiaries', 'w');
        $iterator = ReadTheFile($fh_source);
        foreach($iterator as $iteration)
        {
            if ( $count === 0 ) fwrite($fh_info,  "X");
            else fwrite($fh_info,  "0");
            $count++;
        }
        if ( is_resource($fh_source) ) fclose($fh_source);
        if ( is_resource($fh_info) ) fclose($fh_info);
        $line_count = $count;
        if ( $debug ) LogIt(__FUNCTION__, "Created the empty beneficiaries.info file with X bits.  -->", $line_count);


        // BENEFICIARY REVIEW
        // Look at each column that could have data that indicates beneficiary data.
        // If we see something that indicates the row is a beneficiary row, then update
        // the row to a "1".  Here we are creating a unique file for each beneficiary column.
        foreach($files_lookup as $column_name=>$filename)
        {
            $maps = $maps_lookup[$column_name];

            InfoFileDelete($identifier, $identifier_type, "beneficiaries-{$column_name}");

            $count = 0;
            $fh_source  = S3OpenFile(S3_BUCKET, $prefix, $filename, 'r');
            $fh_info    = InfoFileOpen($identifier, $identifier_type, "beneficiaries-{$column_name}", 'w');
            $iterator   = ReadTheFile($fh_source);
            foreach($iterator as $iteration)
            {
                if ( IsEncryptedString($iteration) ) $iteration = A2PDecryptString($iteration, $encryption_key);
                $iteration = trim($iteration);
                $iteration = strtoupper($iteration);

                if ( $count === 0 ) fwrite($fh_info, "X");
                else if ( in_array($iteration, $maps) ) fwrite($fh_info,  "1");
                else fwrite($fh_info,  "0");
                $count++;

            }
            if ( is_resource($fh_source) ) fclose($fh_source);
            if ( is_resource($fh_info) ) fclose($fh_info);
            if ( $debug ) LogIt(__FUNCTION__, "Created the beneficiary-$column_name.info file.", $column_name);
        }


        // MERGE COLUMN FILES
        // Now that we know which rows are beneficiary rows by column, we want to merge those column
        // files into the master info file.  We want to preserve the "1s" in each column file in the
        // master file.
        foreach($files_lookup as $column_name=>$filename)
        {
            $fh_source  = InfoFileOpen($identifier, $identifier_type, "beneficiaries", 'r+');
            $fh_info    = InfoFileOpen($identifier, $identifier_type, "beneficiaries-{$column_name}", 'r+');
            $fh_temp    = InfoFileOpen($identifier, $identifier_type, "beneficiaries-temp", 'w');

            // Walk the two files, one character at a time.  Write the source character to the
            // temp file unless the info character happens to be a 1.  If it's a 1, write that instead.
            // This will "merge" source and info into temp keeping all the "1s" from both files.
            for($i=0;$i<$line_count;$i++)
            {

                fseek($fh_source, $i);
                $source = fgetc($fh_source);

                fseek($fh_info, $i);
                $info = fgetc($fh_info);

                if ( $info === '1' ) fwrite($fh_temp, $info,1);
                else fwrite($fh_temp, $source,1);

            }
            if ( is_resource($fh_source) ) fclose($fh_source);
            if ( is_resource($fh_info) ) fclose($fh_info);
            if ( is_resource($fh_temp) ) fclose($fh_temp);

            // The temp file now becomes the source file.
            InfoFileDelete($identifier, $identifier_type, "beneficiaries");
            InfoFileRename($identifier, $identifier_type, "beneficiaries-temp", "beneficiaries");
            if ( $debug ) LogIt(__FUNCTION__, "Merged beneficiary-$column_name.info file into the master file.", $column_name);

        }


        // MOVE TO CLOUD
        // Now that we have the beneficiary info file, move it to the cloud.
        $fh_temp = S3OpenFile( S3_BUCKET, $prefix, "beneficiaries.info",'w' );
        $fh_source  = InfoFileOpen($identifier, $identifier_type, "beneficiaries", 'r+');
        for($i=0;$i<$line_count;$i++)
        {

            fseek($fh_source, $i);
            $source = fgetc($fh_source);

            fwrite($fh_temp, $source,1);
        }
        if ( is_resource($fh_source) ) fclose($fh_source);
        if ( is_resource($fh_temp) ) fclose($fh_temp);
        if ( $debug ) LogIt(__FUNCTION__, "Moved beneficiary.info to the cloud.");


        // REMOVE LOCAL COPY
        // Now that we have the info file on S3, remove it locally.
        InfoFileDelete($identifier, $identifier_type, 'beneficiary');

    }
    catch(Exception $e)
    {
        if( is_resource($fh_info) ) fclose($fh_info);
        if( is_resource($fh_temp) ) fclose($fh_temp);
        if( is_resource($fh_source) ) fclose($fh_source);
        if ( $debug ) LogIt(__FUNCTION__, "Got an exception!", $e->getMessage());
    }
}


/**
 * CreateHeaderLookupFiles
 *
 * This function is called with "line #1" of the file.  We will assume
 * for now that line #1 has headers.  This function will parse line
 * one into lookups so we can quickly map the user column names to
 * our internal code.  We will also make a "default" version where we
 * name the columns "Column #X" which we will use if the file has no
 * headers.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $line1
 */
function CreateHeaderLookupFiles( $identifier, $identifier_type, $line1 ) {

    $CI = &get_instance();

    $headers = array();
    $headers['col_lookup'] = array();
    $headers['name_lookup'] = array();
    $headers['headers'] = array();

    $default = array();
    $default['col_lookup'] = array();
    $default['name_lookup'] = array();
    $default['headers'] = array();

    $col_no = 0;
    foreach($line1 as $item)
    {
        $headers['col_lookup']["col{$col_no}"] = getStringValue($item);
        $headers['name_lookup'][$item] = "col{$col_no}";
        $headers['headers'] = $item;

        $default_name = "Column #" . getStringValue( $col_no );
        $default['col_lookup']["col{$col_no}"] = getStringValue($default_name);
        $default['name_lookup'][$default_name] = "col{$col_no}";
        $default['headers'] = $default_name;

        $col_no++;
    }

    // Has the file columns changed between now and last time?  If so, note it.
    $changed = HaveFileColumnsChanged($identifier, $identifier_type, $headers['col_lookup']);

    if ( $identifier_type === 'company' )
    {
        $CI->Company_model->save_company_preference($identifier, "headers", "user_names", json_encode($headers));
        $CI->Company_model->save_company_preference($identifier, "headers", "default_names", json_encode($default));
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $CI->CompanyParent_model->save_companyparent_preference($identifier, "headers", "user_names", json_encode($headers));
        $CI->CompanyParent_model->save_companyparent_preference($identifier, "headers", "default_names", json_encode($default));
    }

    return $changed;


}

/**
 * HaveFileColumnsChanged
 *
 * This function will take a look at the old columns vs the new columns.
 * If the mappings from the original file have been preserved, then we return
 * true.  Else false.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $new_column_lookup
 * @return bool
 */
function HaveFileColumnsChanged( $identifier, $identifier_type, $new_column_lookup )
{

    $CI = &get_instance();

    $changes = false;

    // Grab the col_lookup from the company preference table.
    if ( $identifier_type === 'company') $preference = $CI->Company_model->get_company_preference($identifier, "headers", "user_names");
    if ( $identifier_type === 'companyparent') $preference = $CI->CompanyParent_model->get_companyparent_preference($identifier, "headers", "user_names");
    $preference = getArrayStringValue("value", $preference);
    $value = json_decode($preference, true);
    if ( ! is_array($value) ) return true;  // Had no columns saved. First time?
    if ( ! isset($value["col_lookup"] ) ) return true;
    $orig_column_lookup = $value['col_lookup'];

    // The original file may not be larger than the new file, else review.
    if ( count($orig_column_lookup) > count($new_column_lookup) ) return true;

    // Check the new file against the original file column mappings.  If there
    // not not a shift, meaning they only added new file columns to the right, then allow
    // it.  Else bail.
    foreach($orig_column_lookup as $key=>$value)
    {
        $user_description = getStringValue($value);
        $orig_column_name = strtoupper($user_description);
        $new_column_name = strtoupper(getArrayStringValue($key, $new_column_lookup));

        // Nope.  The original columns do not match the new columns.
        if ( $orig_column_name != $new_column_name ) {
            $changes = true;
        }

    }
    return $changes;
}

/**
 * ShiftColumnMappings
 *
 * This function will identify columns that have been relocated in the
 * latest upload, based on the user defined header value.  If we can
 * successfully identify a column move this function will update the
 * customer preferences so that the user does not need to remap those columns.
 *
 * @param $company_id
 */
function ShiftColumnMappings( $identifier, $identifier_type )
{
    $CI = &get_instance();

    // Grab the col_lookup from the company preference table.
    $preference = GetPreferenceValue($identifier, $identifier_type, "headers", "user_names");
    $value = json_decode($preference, true);
    if ( ! isset($value["name_lookup"] ) ) return;
    if ( ! isset($value["col_lookup"] ) ) return;

    // Here are the new columns that were uploaded.
    $name_lookup = $value['name_lookup'];
    $name_lookup = array_change_key_case($name_lookup,CASE_UPPER);

    // Grab a snapshot of all mappings and the columns they used to map to
    // before we start swaping things around.  ( or the user starts making changes. )
    $history_lookup = array();
    $results = GetPreferences($identifier, $identifier_type, "column_map");

    foreach($results as $item)
    {
        $column_code = getArrayStringValue("group_code", $item);
        $mapping = getArrayStringValue("value", $item);
        $history_lookup[$mapping] = $column_code;
    }

    $swap = array();

    // Look at each column in the file that was just uploaded.
    foreach($name_lookup as $description=>$new_columncode)
    {
        // User Description of Column
        $description = getStringValue($description);
        if ( $description == "" ) continue;

        // New Column Code
        $new_columncode = getStringValue($new_columncode);
        if ( $new_columncode == "" ) continue;

        // Column Mapping
        $mapping = GetPreferenceValue($identifier, $identifier_type, "user_column_label_map", strtoupper($description));
        if ( $mapping == "" ) continue;

        // Old Column Code
        $old_columncode = "";
        if ( isset($history_lookup[$mapping] ) ) $old_columncode = $history_lookup[$mapping];
        if ( $old_columncode == "" ) continue;

        // We have identified a column move.
        if ( $old_columncode != $new_columncode )
        {
            RemovePreferenceByValue($identifier, $identifier_type, "column_map", $mapping);
            SavePreference($identifier, $identifier_type, 'column_map', $new_columncode, $mapping);
        }
    }

}


/**
 * PruneMappedColumns
 *
 * Okay, so let's say the user uploaded a file with two columns in it.
 * The then use the UI to map column #2 to SSN.  Then the later upload
 * a file with only one column in it.  They map column #1 to SSN.  This
 * will fail down the road because we think there are two SSN mapped
 * columns.  One at col2 from the first file and then one at col1 from
 * the second file.
 *
 * This function will identify any saved mappings that are beyond the file
 * uploaded by the customer and delete them to prevent the situation
 * described above.
 *
 * @param $user_id
 * @param $identifier
 * @param $identifier_type
 */
function PruneMappedColumns( $user_id, $identifier, $identifier_type, $encryption_key )
{
    $CI = &get_instance();

    if ( $identifier_type === 'company' ) $prefix = replaceFor(GetConfigvalue("parsed_prefix"), "COMPANYID", $identifier);
    if ( $identifier_type === 'companyparent' ) $prefix = replaceFor(GetConfigvalue("parent_parsed_prefix"), "COMPANYPARENTID", $identifier);
    $preview_filename = "s3://" . S3_BUCKET . "/" . $prefix . "/preview.csv";

    $column_count = null;

    // count the number of columns in the file we parsed.
    $fh = fopen($preview_filename, 'r');
    if ( $fh )
    {
        $line = fgets($fh);
        $line = trim($line);
        $line = A2PDecryptString($line, $encryption_key);
        $csv = str_getcsv($line);
        $column_count = count($csv);
        fclose($fh);
    }
    if ( $column_count == null ) return;

    $prefs = GetPreferences($identifier, $identifier_type, 'column_map');
    foreach($prefs as $pref)
    {
        $group_code = getArrayStringValue("group_code", $pref);
        $group_code = replaceFor($group_code, "col", "");
        if ( StripNonNumeric($group_code) == $group_code )
        {
            $column_no = getIntValue($group_code);
            if ( $column_no >= $column_count )
            {
                // Grab the mapping we are going to delete.
                $mapping = getArrayStringValue("value", $pref);

                // Remove the column_map preference.
                RemovePreference($identifier, $identifier_type, "column_map", "col{$column_no}");
            }
        }
    }
}

/**
 * DedupeMappingPreferences
 *
 * This function will remove any duplicates from the column_map and
 * user_column_label_map prefs.  This can happen via the prune or shift
 * process.  Maybe some other way I have not considered.  Either way
 * this will ensure we don't have any duplicates.  From there the user
 * can correct things on the match screen.  If we have duplicates the
 * match screen cannot render.
 *
 * @param $company_id
 */
function DedupeMappingPreferences($identifier, $identifier_type)
{

    // MAPPING CUSTOMER PREFERENCE VALIDATION PASS
    // Pull all of the column_map prefs into a lookup so duplicates are squished out.
    $column_map = array();
    $prefs = GetPreferences($identifier, $identifier_type, 'column_map');
    foreach($prefs as $pref)
    {
        $column_code = getArrayStringValue("group_code", $pref);
        $mapping_code = getArrayStringValue("value", $pref);
        $column_map[$column_code] = $mapping_code;
    }

    // Pull all of the user_column_label_map prefs into a lookup so duplicates are squished out.
    $user_column_label_map = array();
    $prefs = GetPreferences($identifier, $identifier_type, 'user_column_label_map');
    foreach($prefs as $pref)
    {
        $normalized_user_description = getArrayStringValue("group_code", $pref);
        $mapping_code = getArrayStringValue("value", $pref);
        $user_column_label_map[$mapping_code] = $normalized_user_description;
    }

    // Delete column_map and user_column_label_map preferences.
    RemovePreferences($identifier, $identifier_type, 'column_map' );
    RemovePreferences($identifier, $identifier_type, 'user_column_label_map' );

    // Write column_map preferences back to the DB.
    foreach($column_map as $group_code=>$value)
    {
        SavePreference($identifier, $identifier_type, 'column_map', $group_code, $value);
    }

    // Write user_column_label_map preferences back to the DB.
    foreach($user_column_label_map as $value=>$group_code)
    {
        SavePreference($identifier, $identifier_type, 'user_column_label_map', $group_code, $value);
    }
}

/**
 * CalculateBestMappings
 *
 * Run our best mapping engine over the first two lines of the file that as been
 * uploaded and write note the best mapping for each column in a table in the
 * database.  We used to do this on the match screen live, but it takes too long
 * so now we are going to do it on the server.
 *
 * @param $identifier
 * @param $identifier_type
 */
function CalculateBestMappings( $identifier, $identifier_type, $encryption_key, $debug=false )
{
    $CI = &get_instance();

    // Setup our S3 Connection so we can pop the top two lines off the preview file.
    S3GetClient();

    if ( $identifier_type === 'company' ) $prefix = replaceFor(GetConfigvalue("parsed_prefix"), "COMPANYID", $identifier);
    if ( $identifier_type === 'companyparent' ) $prefix = replaceFor(GetConfigvalue("parent_parsed_prefix"), "COMPANYPARENTID", $identifier);
    $preview_filename = "s3://" . S3_BUCKET . "/" . $prefix . "/preview.csv";

    $line1 = array();
    $line2 = array();

    $fh = fopen($preview_filename, 'r');
    if ( $fh )
    {
        $line1 = fgets($fh);
        $line1 = trim($line1);
        $line1 = A2PDecryptString($line1, $encryption_key);
        $line1 = str_getcsv($line1);

        $line2 = fgets($fh);
        $line2 = trim($line2);
        $line2 = A2PDecryptString($line2, $encryption_key);
        $line2 = str_getcsv($line2);
        fclose($fh);
    }

    // The mapping columns already took both company_id and companyparent_id as inputs.
    // Thus, I'm going to set these variables now based on our identifiers and make the
    // calls.
    $company_id = $identifier;
    $companyparent_id = null;
    if ( $identifier_type === 'companyparent' ) {
        $company_id = null;
        $companyparent_id = $identifier;
    }

    $mapping_columns = $CI->Mapping_model->get_mapping_columns($company_id, $companyparent_id);


    $CI->Mapping_model->delete_customer_best_mapped_column($company_id, $companyparent_id);

    $column_no = 0;
    foreach($line1 as $column)
    {
        $match = BestMappingColumnMatch($line1, $line2, $mapping_columns, $column_no, $identifier, $identifier_type, $debug);
        $CI->Mapping_model->insert_customer_best_mapped_column( $company_id, $companyparent_id, $column, $match, $column_no);
        $column_no++;
    }





}

/**
 * GenerateColumnDataForCompanyParent
 *
 * This function will create the 'mapping meta data' for each active company
 * associated with the parent.
 *
 * @param $company_id
 */
function GenerateColumnDataForCompanyParent($companyparent_id)
{
    $CI = &get_instance();

    // Get a list of all ACTIVE companies associated with this parent.
    $companies = array();
    $data = $CI->CompanyParent_model->get_companies_by_parent($companyparent_id);
    foreach($data as $company)
    {
        if ( GetArrayStringValue('enabled', $company) )
        {
            $companies[] = GetArrayIntValue("company_id", $company);
        }
    }

    // Setup the mapping data for each of these companies.
    foreach($companies as $company_id)
    {
        GenerateColumnDataForCompany($company_id);
    }


    // NOTE: Later, we will change how "mapping" works for the parent where we will
    // have the parent map all of the columns for the child companies.  For now though,
    // we are just mapping the company column.  Upsert that record now.
    $CI->Mapping_model->delete_companyparentmappingcolumn($companyparent_id);
    $CI->Mapping_model->insert_companyparentmappingcolumn($companyparent_id, 'company', 'Company');

}


/**
 * GenerateColumnDataForCompany
 *
 * This function will create the 'mapping meta data' for the specified company.
 * The meta data is constructed based on control tables that define the A2P auto-mapping
 * columns and the features the company has activated.  These can change between each
 * run, so we re-create the mapping meta data each time.
 *
 * @param $company_id
 */
function GenerateColumnDataForCompany($company_id)
{
    $CI = &get_instance();

    // Get a list of all report types.
    $reports = $CI->Reporting_model->select_report_types();

    // Get the default information for all columns.
    $results = $CI->Mapping_model->select_mapping_columns();

    // If we need to move a mapping that was conditional to required,
    // the other items in the conditional list are no longer conditional.
    // They will become optional.  Create a place to hold items that
    // move from conditional -> required.
    $updated_conditionals = array();

    // Collect the detail about each possible mapping column.  As we review them,
    // investigate the currently enabled features on the Company and CompanyParent
    // and adjust mapping column settings as needed.
    foreach($results as $item) {

        // Default data for this column.
        $details = array();
        $details['name'] = GetArrayStringValue("Name", $item);
        $details['display'] = GetArrayStringValue("Display", $item);
        $details['required'] = 'f';  // Assume not required for now.
        $details['encrypted'] = GetArrayStringValue("Encrypted", $item);
        $details['default_value'] = GetArrayStringValue("DefaultValue", $item);
        $details['conditional'] = GetArrayStringValue("Conditional", $item);
        $details['conditional_list'] = GetArrayStringValue("ConditionalList", $item);
        $details['normalization_regex'] = GetArrayStringValue('NormalizationRegEx', $item);

        // Setup report based features based on what is activated.
        $column_code = GetArrayStringValue("Name", $item);
        foreach ($reports as $report)
        {
            $report_code = GetArrayStringValue("Name", $report);

            // Hey, this column is already required.  Nothing left to do here.
            if ( $details['required'] === 't' ) break;

            // transamerica eligibility.
            if ($report_code === 'transamerica_eligibility') {

                // This report must be "enabled" before we do a required check against it's fields.
                if ( $CI->Feature_model->is_feature_enabled($company_id, 'TRANSAMERICA_ELIGIBILITY_REPORT') )
                {
                    $is_required = $CI->Reporting_model->get_report_property_value($report_code, 'REQUIRED_COLUMN', $column_code);
                    if ($is_required === 'TRUE')
                    {
                        // Keep track of conditionals columns that become required.
                        $list = GetArrayStringValue('conditional_list', $details);
                        if ( $list !== '' ) $updated_conditionals[$list] = true;

                        $details['required'] = 't';
                        $details['conditional'] = 'f';
                        $details['conditional_list'] = '';

                        continue;
                    }
                }
            }
            else if ($report_code === 'transamerica_commission')
            {
                // This report must be "enabled" before we do a required check against it's fields.
                if ( $CI->Feature_model->is_feature_enabled($company_id, 'TRANSAMERICA_COMMISSION_REPORT') )
                {
                    $is_required = $CI->Reporting_model->get_report_property_value($report_code, 'REQUIRED_COLUMN', $column_code);
                    if ($is_required === 'TRUE')
                    {
                        // Keep track of conditionals columns that become required.
                        $list = GetArrayStringValue('conditional_list', $details);
                        if ( $list !== '' ) $updated_conditionals[$list] = true;

                        $details['required'] = 't';
                        $details['conditional'] = 'f';
                        $details['conditional_list'] = '';

                        continue;
                    }
                }
            }
            else if ($report_code === 'transamerica_actuarial')
            {
                // This report must be "enabled" before we do a required check against it's fields.
                if ( $CI->Feature_model->is_feature_enabled($company_id, 'TRANSAMERICA_ACTUARIAL_REPORT') )
                {
                    $is_required = $CI->Reporting_model->get_report_property_value($report_code, 'REQUIRED_COLUMN', $column_code);
                    if ($is_required === 'TRUE')
                    {
                        // Keep track of conditionals columns that become required.
                        $list = GetArrayStringValue('conditional_list', $details);
                        if ( $list !== '' ) $updated_conditionals[$list] = true;

                        $details['required'] = 't';
                        $details['conditional'] = 'f';
                        $details['conditional_list'] = '';

                        continue;
                    }
                }
            } else {
                // This report cannot be turned on or off.  Just return
                // the required detail for it.
                $is_required = $CI->Reporting_model->get_report_property_value($report_code, 'REQUIRED_COLUMN', $column_code);
                if ($is_required === 'TRUE')
                {
                    // Keep track of conditionals columns that become required.
                    $list = GetArrayStringValue('conditional_list', $details);
                    if ( $list !== '' ) $updated_conditionals[$list] = true;

                    $details['required'] = 't';
                    $details['conditional'] = 'f';
                    $details['conditional_list'] = '';

                    continue;
                }
            }

        } // Report Loop
        $output[] = $details;
    }// Column Loop


    // If we have conditional mappings that have been required, we want
    // to remove any other columns that have the same conditional lists.
    // Because one item in the list went required, all the other items in
    // the conditional list are now optional.
    $updated_conditionals = array_keys($updated_conditionals);
    if ( ! empty($updated_conditionals) )
    {
        $cleaned = array();
        foreach($output as $item)
        {
            $updated = array();
            $updated['name'] = GetArrayStringValue("name", $item);
            $updated['display'] = GetArrayStringValue("display", $item);
            $updated['required'] = GetArrayStringValue('required', $item);
            $updated['encrypted'] = GetArrayStringValue("encrypted", $item);
            $updated['default_value'] = GetArrayStringValue("default_value", $item);
            $updated['conditional'] = GetArrayStringValue("conditional", $item);
            $updated['conditional_list'] = GetArrayStringValue("conditional_list", $item);
            $details['normalization_regex'] = GetArrayStringValue('normalization_regex', $item);

            $conditional_list = GetArrayStringValue('conditional_list', $item);
            if ( $conditional_list !== '' )
            {
                if ( in_array($conditional_list, $updated_conditionals) )
                {
                    $updated['conditional'] = 'f';
                    $updated['conditional_list'] = '';
                }
            }
            $cleaned[] = $updated;
        }
        $output = $cleaned;
    }

    // Now that we have calculated all of the mapping column data based on the company
    // and parent company settings, we can save that data to the database for easy access.
    $CI->Mapping_model->delete_company_mapped_columns($company_id);
    foreach($output as $item)
    {
        $name = GetArrayStringValue("name", $item);
        $display = GetArrayStringValue('display', $item);
        $required = GetArrayStringValue('required', $item);
        $default_value = GetArrayStringValue('default_value', $item);
        $column_name = GetArrayStringValue('column_name', $item);
        $encrypted = GetArrayStringValue('encrypted', $item);
        $conditional = GetArrayStringValue('conditional', $item);
        $conditional_list = GetArrayStringValue('conditional_list', $item);
        $normalization_regex = GetArrayStringValue('normalization_regex', $item);

        $CI->Mapping_model->insert_company_mapping_column($company_id, $name, $display, $required, $default_value, $column_name, $encrypted, $conditional, $conditional_list, $normalization_regex );
    }



    // Now that we have the company mapping columns created, attempt to activate the
    // column normalization regex feature on each column.  This will fill out the
    // NormalizationRegEx column specific for the company if configured in the
    // feature settings.
    foreach($results as $item)
    {
        // Make sure we push the Column Normalization RegEx feature values into the
        // runtime environment as we setup the custom columns for this company.
        $column_name = GetArrayStringValue("Name", $item);
        ActivateColumnNormalizationRegExFeature($company_id, $column_name);
    }

}

/**
 * SplitCSVUpload
 *
 * This function will read the upload file on S3 for the specified
 * entity.  It will split the file out by column into individual files
 * where each data point in the split out files is encrypted specific to
 * the entity.
 *
 * A preview file will also be generated as well.  The preview file is a very
 * short version of the the import file, but with each full line of the file
 * encrypted specific to the entity.
 *
 * This function returns a CSV array of the first row of the import file
 * which contains the file headers and can be used to decide how wide
 * the upload file is.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $debug
 * @return array
 * @throws Exception
 */
function SplitCSVUpload( $identifier, $identifier_type, $debug=false )
{
    $prefix     = null;
    $col_count  = null;
    $fh_source  = null;
    $iterator   = null;
    $iteration  = null;
    $index      = null;
    $counter    = null;
    $handles    = null;
    $handle     = null;
    $headers    = null;
    $encryption_key = null;

    $headers = array();
    try
    {

        if ($debug ) print "identifier: [{$identifier}]\n";
        if ($debug ) print "identifier_type: [{$identifier_type}]\n";

        $encryption_key = GetEncryptionKey($identifier, $identifier_type);

        // Collect the source_filename from disk and open a file handle to it.
        $prefix = GetS3Prefix('upload', $identifier, $identifier_type);
        if ( $debug ) print "upload prefix: [{$prefix}]\n";

        // Open our source file.
        $files = S3ListFiles(S3_BUCKET, $prefix);
        if ( count($files) != 1 ) throw new Exception("Did not find exactly one file when reviewing uploads on S3.");
        $file = $files[0];
        $source_filename = fRightBack(GetArrayStringValue("Key", $file), "/");
        if ( $source_filename === '' ) throw new Exception("Unable to locate the source filename.");
        if ( $debug ) print "Processing: [{$source_filename}]\n";
        $fh_source = S3OpenFile(S3_BUCKET, $prefix, $source_filename, 'r');

        // Create our Parsed Prefix on S3.
        $prefix = GetS3Prefix('parsed', $identifier, $identifier_type);
        S3MakeBucketPrefix( S3_BUCKET, $prefix );

        // Start by creating our preview file.  This has the side effect
        // of telling us how many columns we are dealing with.
        $headers = CreatePreviewFile($identifier, $identifier_type, $source_filename);
        $col_count = count($headers);

        // Open a file handle for every column in the file.
        $handles = array();
        for($index=0;$index<$col_count;$index++)
        {
            $filename = "col{$index}.txt";
            if ( $debug ) print "Opening file: [{$prefix}/{$filename}]\n";
            S3DeleteFile( S3_BUCKET, $prefix, $filename );
            $handles[$index] = S3OpenFile(S3_BUCKET, $prefix, $filename, 'w');
        }
        unset($filename);
        $handle_count = count($handles);

        // Read each line of the source file.  Break the line into an array
        // assuming it is CSV data.  Write each column to it's own file and encrypt
        // the data as we write it.
        $encrypted = null;
        if ( $debug ) print "Parsing upload start. memory used: " . FormatBytes(memory_get_usage()) . "\n";
        $counter = 0;
        $iterator = ReadTheFile($fh_source);


        foreach ($iterator as $iteration)
        {
            // ENCRYPTED UPLOADS
            // If a staff member passed in an encrypted copy of the upload file, decrypt it before
            // we attempt to parse it.
            if( $encrypted === null )
            {
                $encrypted = IsEncryptedStringComment($iteration);
                if ( $encrypted )
                {
                    if ( GetStringValue($identifier) !== FBetween($iteration, "[", "]") )
                    {
                        throw new Exception("Unable to upload encrypted data. Ownership mismatch. [".GetStringValue($identifier)."] != [".FBetween($iteration, "[", "]")."]");
                    }
                }
            }
            if ( $encrypted && IsEncryptedStringComment($iteration) ) continue;
            if ( $encrypted ) $iteration = A2PDecryptString($iteration, $encryption_key);

            // EMPTY STRINGS
            // Throw out lines that are completely empty.
            if ( trim($iteration) === '' )
            {
                if ( $debug ) "We found a record that appears to be empty.  Skipping it.\n";
                continue;
            }

            // DATA
            // Collect the CSV data.
            $csv = str_getcsv($iteration);

            // TOO SMALL
            // If the array is less than the column size indicated by the header row, then pad it out.
            if ( count($csv) < $col_count ) $csv = array_pad($csv, $col_count, '');

            // BLANK ROW
            // If the string length match the column count, then we know this row contains no
            // data.  We can just throw this row out rather than issuing an error later in validation.
            if ( ( strlen(trim($iteration)) + 1 )  === $col_count ) continue;


            for($index=0;$index<count($csv);$index++)
            {

                // If we got a line with EXTRA data, just skip it.
                if ( $index >= $handle_count) continue;

                // UTF-8 CHECK
                // Check the datum to see that it is UTF-8.  If it's not, then we have to
                // convert it's encoding.  I don't want to do that all the time, so the if block.
                if ( preg_match('//u', $csv[$index]) )
                {
                    // UTF-8
                    fwrite($handles[$index],  A2PEncryptString(trim($csv[$index]), $encryption_key, true) . PHP_EOL);
                }
                else
                {
                    // not UTF-8
                    fwrite($handles[$index],  A2PEncryptString(trim(mb_convert_encoding($csv[$index], 'UTF-8', 'UTF-8')), $encryption_key, true) . PHP_EOL);
                }
            }

            // Rest and Garbage Collect along the way so that we don't
            // blow all our memory.
            $counter++;
            if ( $counter > 2000 )
            {
                $counter = 0;
                gc_collect_cycles();
                if ( $debug ) print "Parsing upload file. memory used: " . FormatBytes(memory_get_usage()) . "\n";
            }
        }
        if ( $debug ) print "Parsing upload end. memory peak: " . FormatBytes(memory_get_peak_usage()) . "\n";
        if ( $debug ) LogIt("ParseCSV_Helper", "Parsing upload end. memory peak: " . FormatBytes(memory_get_peak_usage()));

        // Shutdown each of the output files and then the source.
        if ( $debug ) print "Closing files start. memory used: " . FormatBytes(memory_get_usage()) . "\n";
        foreach($handles as $handle)
        {
            if ( is_resource($handle) ) fclose($handle);
        }
        if ( is_resource($fh_source) ) fclose($fh_source);
        gc_collect_cycles();
        if ( $debug ) print "Closing files end. memory used: " . FormatBytes(memory_get_usage()) . "\n";

    }
    catch(Exception $e)
    {
        if ( ! empty($handles) )
        {
            foreach($handles as $handle)
            {
                if ( is_resource($handle) ) fclose($handle);
            }
        }
        if ( is_resource($fh_source) ) fclose($fh_source);

        unset($prefix);
        unset($col_count);
        unset($fh_source);
        unset($iterator);
        unset($iteration);
        unset($index);
        unset($counter);
        unset($handles);
        unset($handle);
        unset($encryption_key);

        throw $e;

    }

    unset($prefix);
    unset($col_count);
    unset($fh_source);
    unset($iterator);
    unset($iteration);
    unset($index);
    unset($counter);
    unset($handles);
    unset($handle);
    unset($encryption_key);

    return $headers;


}

/**
 * CreatePreviewFile
 *
 * This function will create a "mini" version of the import file.  We call
 * this the "preview" file and it will be used to get a sample of the data
 * file.
 *
 * The file is stored on the remote serve in a file called preview.csv in
 * the parsed directory.
 *
 * Each individual line of the sample file is encrypted.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $source_filename
 * @return array|string
 * @throws Exception
 */
function CreatePreviewFile($identifier, $identifier_type, $source_filename )
{
    $prefix = null;
    $fh_source = null;
    $fh_target = null;
    $line_number = null;
    $iterator = null;
    $iteration = null;
    $encryption_key = null;

    try
    {
        $encryption_key = GetEncryptionKey($identifier, $identifier_type);

        $headers = array();
        $prefix = GetS3Prefix('upload', $identifier, $identifier_type);
        $fh_source = S3OpenFile(S3_BUCKET, $prefix, $source_filename, 'r');

        $identifier_name = GetIdentifierName($identifier, $identifier_type);

        $target_filename = "preview.csv";
        $prefix = GetS3Prefix('parsed', $identifier, $identifier_type);
        S3MakeBucketPrefix( S3_BUCKET, $prefix );
        S3DeleteFile(S3_BUCKET, $prefix, $target_filename);
        $fh_target = S3OpenFile(S3_BUCKET, $prefix, $target_filename, 'w');

        $encrypted = null;
        $line_number = 0;
        $col_count = null;
        $iterator = ReadTheFile($fh_source);
        foreach ($iterator as $iteration)
        {

            // ENCRYPTED UPLOADS
            // If a staff member passed in an encrypted copy of the upload file, decrypt it before
            // we attempt to parse it.
            if( $encrypted === null )
            {
                $encrypted = IsEncryptedStringComment($iteration);
                if ( $encrypted )
                {
                    if ( GetStringValue($identifier) !== FBetween($iteration, "[", "]") )
                    {
                        throw new Exception("Unable to upload encrypted data. Company file mismatch.");
                    }
                }
            }
            if ( $encrypted && IsEncryptedStringComment($iteration) ) continue;
            if ( $encrypted ) $iteration = A2PDecryptString($iteration, $encryption_key);

            // Trim the line ( iteration ) and if we have something process it.
            $iteration = trim($iteration);
            if ( $iteration === '' ) continue;
            $line_number++;

            // EMPTY STRINGS
            // Throw out lines that are completely empty.
            if ( trim($iteration) === '' )
            {
                continue;
            }

            // DATA
            // Collect the CSV data.
            $csv = str_getcsv($iteration);
            if ( $col_count === null ) $col_count = count($csv);

            // TOO SMALL
            // If the array is less than the column size indicated by the header row, then pad it out.
            if ( count($csv) < $col_count ) $csv = array_pad($csv, $col_count, '');

            // BLANK ROW
            // If the string length match the column count, then we know this row contains no
            // data.  We can just throw this row out rather than issuing an error later in validation.
            if ( ( strlen(trim($iteration)) + 1 )  === $col_count ) continue;

            // TOO BIG
            // If we have too many columns, trim them back ... for the preview file only.  In the real import, the
            // validation engine will most certainly catch you and give you the business.
            while ( count($csv) > $col_count )
            {
                array_pop($csv);
            }

            // Clean all of the individual items in the CSV string of the iteration.
            $bits = array_map('trim', $csv);
            $iteration = GetCSVString($bits) . PHP_EOL;
            if ( $iteration === FALSE ) throw new Exception("Unable to scrub preview file for processing.");
            
            // Grab the first line as the headers, for return.
            if ( empty($headers) ) $headers = str_getcsv($iteration);

            // Write the preview file to the cloud until we hit our max.
            fwrite($fh_target, A2PEncryptString($iteration, $encryption_key) . PHP_EOL);
            if ( $line_number > PREVIEW_FILE_LENGTH ) break;

        }
        if ( is_resource($fh_source) ) fclose($fh_source);
        if ( is_resource($fh_target) ) fclose($fh_target);
    }
    catch(Exception $e)
    {
        unset($prefix);
        unset($fh_source);
        unset($fh_target);
        unset($line_number);
        unset($iterator);
        unset($iteration);
        unset($headers);
        unset($encryption_key);
        throw $e;
    }

    gc_collect_cycles();

    unset($prefix);
    unset($fh_source);
    unset($fh_target);
    unset($line_number);
    unset($iterator);
    unset($iteration);
    unset($encryption_key);




    return $headers;

}