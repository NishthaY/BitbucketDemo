<?php


/**
 * GetSQL
 *
 * This function will return a chunk of SQL that is stored on disk.
 *
 * filename - string. This is the path to the file on disk that contains
 * the SQL we want to return.  The path should start with the database
 * folder located at the application root.  The file being read from
 * disk must end in ".sql".
 *
 * replacefor - key/value pairs.  If the key is found in the query then
 * the value will replace it before execution.
 *
 * May return an empty string.
 *
 * NOTE: You may pass SQL in in place of the filename!  This is a
 * convenience feature so you can pass in tiny sql without creating
 * a file.
 *
 * @param $filename
 * @param array $replacefor
 * @return bool|mixed|string
 */
function GetSQL($filename, $replacefor=array() )
{
    if ( EndsWith($filename, "sql" ) )
    {
        $sql = file_get_contents( $filename );
    }
    else
    {
        $sql = $filename;
    }

    if ( getStringValue($sql) == "" ) return "";

    // Prevent question marks in comments from being 'bound' by the database call.
    // this regex looks for   --   and anything until end of the input or line
    // it also matches   /*   and anything up to the ending    */
    // even if it spans lines
    $sql =  preg_replace('%--.*?$|\\/\\*.*?\\*\\/%sm', '', $sql);


    // Sometimes bind variables just won't work.  You can pass in
    // key/value pairs in the replacefor array.  We will then replace
    // the KEY with VALUE in the SQL before we execute it.
    if ( ! empty($replacefor) ){
        foreach($replacefor as $key=>$value)
        {
            $sql = replaceFor($sql, $key, $value);
        }
    }
    return $sql;
}

/**
 * GetDBResults
 *
 * This function returns database results in an indexed array.  One
 * row per record.  Each row is itself an array where the key is the
 * column and the value is the row data for that column.
 *
 * vars - array. Ordered list of variables that will be used in the
 * query as bind variables.  The query '?' will be replaced with these
 * inputs in the order they are found in the array.
 *
 * replacefor - key/value pairs.  If the key is found in the query then
 * the value will replace it before execution.
 *
 * May return the empty array.
 *
 * @param $db
 * @param $filename
 * @param $vars
 * @param array $replacefor
 * @return array
 */
function GetDBResults($db, $filename, $vars, $replacefor=array())
{
    // Turn on or off buffering when pulling data.  I have left the
    // standard method in place so it's clear we are choosing to use
    // the unbuffered row way of accessing the data.  You may toggle
    // back to the STANDARD method by setting the boolean below to false.
    $buffer = true;

    $output = array();
    if ( $buffer )
    {
        // OPTIMIZED: This is an optimized way to pull results from the
        // database that works much better when pulling large data sets.
        $sql = GetSQL( $filename, $replacefor );

        if ( $sql != "" )
        {
            $res = $db->query($sql, $vars);
            while( $row = $res->unbuffered_row('array'))
            {
                $output[] = $row;
            }
            $res->free_result();
        }

    }
    else
    {
        // STANDARD: This is the typical way you would pull results from
        // the database result.
        $sql = GetSQL( $filename, $replacefor );

        if ( $sql != "" )
        {
            $res = $db->query($sql, $vars);
            if ( ! empty($res) )
            {
                if( $res->num_rows() > 0) {
                    $output = $res->result_array();
                }
            }
            $res->free_result();
        }

    }


    return $output;
}

/**
 * ExecuteSQL
 *
 * This function will execute an SQL statement that modifies data
 * in some way.
 *
 * vars - array. Ordered list of variables that will be used in the
 * query as bind variables.  The query '?' will be replaced with these
 * inputs in the order they are found in the array.
 *
 * replacefor - key/value pairs.  If the key is found in the query then
 * the value will replace it before execution.
 *
 * @param $db
 * @param $filename
 * @param $vars
 * @param array $replacefor
 * @return array
 */
function ExecuteSQL($db, $filename, $vars, $replacefor=array() ) {

    $sql = GetSQL( $filename, $replacefor );

    // Make the call.
    $res = $db->query($sql, $vars);

    // Postgres can return the ID of the item created so we will
    // return an optional retval here!!!  Sweet.  Maybe we got it, Maybe
    // we dont'.
    $retval = array();

    if ( ! empty($res) && method_exists($res, "num_rows") )
    {
        if( $res->num_rows() > 0) {
            $retval = $res->result_array();
        }
    }
    return $retval;
}

/**
 * WriteDBSecureFile
 *
 * This function will execute a DB query and then write that file to the specified
 * file handle as an encrypted CSV.
 *
 * The file generated is an encrypted A2P data file.  The first three lines will be
 * in clear text and will out line the company the file belongs to, the release level
 * that generated the file and the date it was created.
 *
 * The selected data might be encrypted, so it is decrypted if needed.  Then the
 * CSV is generated and encrypted as a complete line before being written to the
 * file handle.
 *
 * The rules parameter may be a php function name that references a function that
 * takes two parameters.  The two parameters are the cell data value and the column
 * number.  That function does operations on the value and then the updated data is
 * what is stored in the output file.  This only happens if the rules parameter
 * is set to a known php function.
 *
 *
 *
 * @param $fh
 * @param $db
 * @param $filename
 * @param $vars
 * @param $company_id
 * @param $encryption_key
 * @param array $replacefor
 * @param bool $include_headers
 * @param string $rules
 * @return bool|int
 * @throws Exception
 */
function WriteDBSecureFile($fh, $db, $filename, $vars, $company_id, $encryption_key, $replacefor=array(), $include_headers=true, $rules="" )
{
    $fh_temp = null;
    try
    {

        // Open a memory "file" for read/write...
        $temp_filename = "tmp_".RandomString();
        $prefix = replaceFor(GetConfigvalue("upload_prefix"), "COMPANYID", $company_id);
        $fh_temp = S3OpenFile(S3_BUCKET, $prefix, $temp_filename, 'w');

        // Tag this file with the information about where and who it was encrypted for.
        fputs($fh, "{a2p-comment}:company_id[{$company_id}]" . PHP_EOL);
        fputs($fh, "{a2p-comment}:app_name[".APP_NAME."]" . PHP_EOL);
        fputs($fh, "{a2p-comment}:encrypted_on[".date("c")."]" . PHP_EOL);


        $counter = 0;
        $sql = GetSQL( $filename, $replacefor );

        $res = $db->query($sql, $vars);
        while( $row = $res->unbuffered_row('array'))
        {
            if ( $include_headers && $counter == 0 )
            {
                // Make sure we are at the start of our temp file.
                rewind($fh_temp);

                // Write the CSV data to a temp file.
                fputcsv($fh_temp, array_keys($row));

                // Move the pointer to the front of the file.
                rewind($fh_temp);

                // Read the line of CSV data and encrypt it as a string.
                $line = fgets($fh_temp);
                $line = A2PEncryptString(trim($line), $encryption_key);

                // Write the encrypted string as output.
                fputs($fh, $line.PHP_EOL);

            }

            // If the datum ( cell ) is encrypted, decrypt it.
            $column_count = 0;
            foreach($row as $key=>$value)
            {
                // Decrypt the datum, if it's encrypted.
                if ( IsEncryptedString($value) ) $value = A2PDecryptString($value, $encryption_key);

                // If we were supplied a RULES function name, run it.
                if ( $rules !== '' )
                {
                    $value = $rules($value, $column_count);
                }
                $column_count++;

                $row[$key] = $value;
            }



            // Make sure we are at the start of our temp file.
            rewind($fh_temp);

            // Write the CSV data to a temp file.
            fputcsv($fh_temp, array_values($row));

            // Move the pointer to the front of the file.
            rewind($fh_temp);

            // Read the line of CSV data and encrypt it as a string.
            $line = fgets($fh_temp);
            $line = A2PEncryptString(trim($line), $encryption_key);

            // Write the encrypted string as output.
            fputs($fh, $line.PHP_EOL);

            $counter++;
        }
        $res->free_result();
        if ( is_resource($fh_temp) ) fclose($fh_temp);
        S3DeleteFile(S3_BUCKET, $prefix, $temp_filename);
    }
    catch(Exception $e)
    {
        if ( is_resource($fh_temp) ) fclose($fh_temp);
        S3DeleteFile(S3_BUCKET, $prefix, $temp_filename);

        unset($fh_temp);

        return FALSE;
    }
    return $counter;
}


/**
 * GetDBExists
 *
 * Given a select statement, this function will return TRUE or FALSE if
 * the select will return records or not.
 *
 * @param $db
 * @param $filename
 * @param $vars
 * @param array $replacefor
 * @return bool
 * @throws Exception
 */
function GetDBExists($db, $filename, $vars, $replacefor=array())
{
    $sql = GetSQL( $filename, $replacefor );

    // look for the FROM in the query and dump everything before it and turn
    // it into a count statement that reuturns "remaining".
    $select_index = strpos(strtoupper($sql), 'SELECT');
    if ( $select_index === FALSE ) throw new Exception("Unable to successfully parse SQL statement.  Panic.");
    $sql = substr($sql, $select_index);
    $sql = "select exists({$sql})";

    $results = GetDBResults( $db, $sql, $vars );
    if ( count($results) !== 1 ) throw new Exception("Query to count remaining records did not return exactly one row.");

    $remaining = GetArrayStringValue("exists", $results[0]);
    if ( $remaining === 't' ) return TRUE;
    if ( $remaining === 'f' ) return FALSE;
    throw new Exception("Query to count remaining records did not return a boolean!");

}

/**
 * SelectIntoUpdate
 *
 * This function takes a select into update statement and modifies it
 * so that the select into update can be ran against a X number
 * of records at a time to prevent blowing out physical resources.
 *
 * @param $db
 * @param $filename
 * @param $vars
 * @param array $replacefor
 * @throws Exception
 */
function SelectIntoUpdate($db, $filename, $vars, $replacefor=array() )
{
    // Pull our SQL and look it over.  Does it look like a select into update?
    $sql = GetSQL( $filename, $replacefor );
    $normalized_sql = strtoupper($sql);
    if ( strpos($normalized_sql, "SELECT") === FALSE ) throw new Exception("Missing required select.");
    if ( strpos($normalized_sql, "UPDATE") === FALSE ) throw new Exception("Missing required update.");
    if ( strpos($normalized_sql, "LIMIT") !== FALSE ) throw new Exception("Query should not contain a limit.");
    if ( strpos($normalized_sql, "INSERT") !== FALSE ) throw new Exception("Query should not contain an insert.");

    // Extract the SELECT statement from the UPDATE
    $select_index = strpos($normalized_sql, "SELECT");
    $update_index = strpos($normalized_sql, "UPDATE");
    $select_sql = fLeftBack(substr($sql, $select_index, $update_index), ")");
    $select_sql_with_limit = "{$select_sql} limit ?";

    // Rebuild the query so that the original select statement
    // is replaced with the new select statement, leaving everything else the same.
    $before = substr($sql, 0, $select_index);
    $after = substr($sql, $update_index);
    $new_sql = "{$before} {$select_sql_with_limit} \n)\n {$after}";


    // How many rows should we process at a time?
    $chunk_size = GetAppOption(SELECT_INTO_CHUNCK_SIZE);
    if ( $chunk_size === "" ) $chunk_size = "1000";
    $chunk_size = getIntValue($chunk_size);

    // How long should we rest between chunks
    $rest_seconds = GetAppOption(REST_SECONDS_BETWEEN_QUERIES);
    if ( $rest_seconds === "" ) $rest_seconds = "0";
    $rest_seconds = getIntValue($rest_seconds);


    // Create a new vars array, but add to the end of it the limit parameter.
    // don't use the original one as we will need that for the remaining query.
    $vars_with_limit = array();
    foreach($vars as $var)
    {
        $vars_with_limit[] = $var;
    }
    $vars_with_limit[] = $chunk_size;


    // While we have remaining records to insert, keep working on it.
    $remaining = GetDBExists($db, $select_sql, $vars, $replacefor);
    while ( $remaining )
    {
        sleep($rest_seconds);
        ExecuteSQL( $db, $new_sql, $vars_with_limit );
        $remaining = GetDBExists($db, $select_sql, $vars, $replacefor);
    }

}

/**
 * CopyFromInto
 *
 * Use the postgres COPY command to move data from one table to another.
 * This function takes a select into insert statement.  That statement is
 * then broken into an individual select and insert statements.  The
 * standard DB Helper functionality is still provided.  You can use bind
 * variable and ReplaceFor tags.
 *
 * Once all the parts are broken out, the select statement is executed
 * using the copy command and produces a CSV file on disk.  That file
 * is then imported using the copy command into the target table specified
 * by the insert.
 *
 * As a result, you move data between two tables much faster than using
 * a SelectIntoInsert functionality.
 *
 * NOTE: The text {BIND#} is reserved.  You may not have that text in
 * your SQL.
 *
 *
 * @param $db
 * @param $filename
 * @param $vars
 * @param array $replacefor
 * @throws Exception
 */
function CopyFromInto($db, $filename, $vars, $replacefor=array() )
{

    $CI = &get_instance();

    $filepath = "";
    $export_script_filepath = "";
    $import_script_filepath = "";
    $sql_filename = "";
    $select_statement = "";
    try
    {

        $sql_filename = $filename;
        $sql = GetSQL($sql_filename, $replacefor);
        if ( strpos(strtoupper($sql), "SELECT") === FALSE ) throw new Exception("Missing required select.");
        if ( strpos(strtoupper($sql), "INSERT") === FALSE ) throw new Exception("Missing required insert.");
        if ( strpos(strtoupper($sql), "{BIND") !== FALSE ) throw new Exception("Query contains reserved replace for tag.");

        // We are going to create a few files on disk to pull this off.  We need to
        // find a unique filename first.
        $random = RandomString(25);
        $filepath = APPPATH . "../copy/" . $random . ".csv";
        if ( file_exists($filepath) )
        {
            $batch_label = RandomString(25);
            $filepath = APPPATH . "../copy/" . $random . ".csv";
            if ( file_exists($filepath) )
            {

                throw new Exception("Unable to create temp file for CopyFromInto for {$sql_filename}.");
            }
        }
        $export_script_filepath = ReplaceFor($filepath, ".csv", "_EXPORT.sh");
        $import_script_filepath = ReplaceFor($filepath, ".csv", "_IMPORT.sh");

        // Replace all bind indicators with a tag that looks like
        // {BIND1} or {BIND2} etc.
        $bind_index = 1;
        while( strpos($sql, "?") !== false )
        {
            $place = strpos($sql, "?");
            $left = substr($sql, 0, $place);
            $right = substr($sql, $place + 1);
            $sql = "{$left}{BIND{$bind_index}}{$right}";
            $bind_index++;
        }

        // Now we will replace the BIND tags with the items found
        // in the vars array.  When we are done, we have a ready
        // to run SQL statement.
        $bind_replaceFor = array();
        for($i=1;$i<$bind_index;$i++)
        {
            $key = "{BIND{$i}}";
            $var_index = $i - 1;
            is_string($vars[$var_index]) ? $bind_replaceFor[$key] = "'" . $vars[$var_index] . "'" :  $bind_replaceFor[$key] = $vars[$var_index];
        }
        $sql = GetSQL($sql, $bind_replaceFor);

        // Pop off INSERT
        $insert_start = strpos(strtoupper($sql), "INSERT");
        $sql = substr($sql, $insert_start + strlen("INSERT") + 1);

        // Pop off INTO
        $into_start = strpos(strtoupper($sql), "INTO");
        $sql = substr($sql, $into_start + strlen("INTO") + 1);

        // Grab the TABLE and COLUMNS
        $table_name = fLeft($sql, " ");
        $columns = fLeft(fRight($sql, " "), ")") . ")";

        // Reduce down to just the SELECT
        $select_start = strpos(strtoupper($sql), "SELECT");
        $select_statement = substr($sql, $select_start);

        // Create the SQL command that will copy the data into our CSV file.
        $filename = "database/template/CopyFromIntoEXPORT.sql";
        $export_replaceFor = array();
        $export_replaceFor['{SELECT_STATEMENT}'] = $select_statement;
        $export_replaceFor['{FILENAME}'] = $filepath;
        $sql = GetSQL($filename, $export_replaceFor);
        $sql = replaceFor($sql, "\"", "\\\"");

        // Create our EXPORT script.
        $filename = "database/template/CommandLineScript.sql";
        $script_replaceFor = array();
        $script_replaceFor['{USERNAME}'] = $db->username;
        $script_replaceFor['{HOSTNAME}'] = $db->hostname;
        $script_replaceFor['{DATABASE}'] = $db->database;
        $script_replaceFor['{PORT}'] = $db->port;
        $script_replaceFor['{SQL_STATEMENT}'] = $sql;
        $script = GetSQL($filename, $script_replaceFor);
        file_put_contents($export_script_filepath, $script);
        chmod($export_script_filepath,0755);

        // Export the data to disk.
        passthru($export_script_filepath, $exitstatus);
        if ( $exitstatus != 0 )
        {
            throw new Exception("Unable to generate an export file for the CopyFromInto for {$sql_filename}.");
        }

        // Create our COPY import statement.
        $filename = "database/template/CopyFromIntoIMPORT.sql";
        $import_replaceFor = array();
        $import_replaceFor['{TABLE_NAME}'] = $table_name;
        $import_replaceFor['{COLUMNS}'] = $columns;
        $import_replaceFor['{FILENAME}'] = $filepath;
        $sql = GetSQL($filename, $import_replaceFor);
        $sql = replaceFor($sql, "\"", "\\\"");


        // Create our IMPORT script.
        $filename = "database/template/CommandLineScript.sql";
        $script_replaceFor = array();
        $script_replaceFor['{USERNAME}'] = $db->username;
        $script_replaceFor['{HOSTNAME}'] = $db->hostname;
        $script_replaceFor['{DATABASE}'] = $db->database;
        $script_replaceFor['{PORT}'] = $db->port;
        $script_replaceFor['{SQL_STATEMENT}'] = $sql;
        $script = GetSQL($filename, $script_replaceFor);
        file_put_contents($import_script_filepath, $script);
        chmod($import_script_filepath,0755);


        // Import the data from disk.
        passthru($import_script_filepath,$exitstatus);
        if ( $exitstatus != 0 )
        {
            throw new Exception("Unable to import data via CopyFromInto for {$sql_filename}.");
        }

        // Remove our temp file.
        if ( file_exists( $filepath ) ) unlink($filepath);
        if ( file_exists( $import_script_filepath ) ) unlink($import_script_filepath);
        if ( file_exists( $export_script_filepath ) ) unlink($export_script_filepath);


        // This function is used to import LOTS of data.  To that end, we need to
        // tell postgres to collect stats on this table so it can quickly and consistently
        // select data from it moving forward.
        $CI->Tuning_model->analyse( $table_name );



    }
    catch(Exception $e)
    {
        if ( file_exists( $filepath ) ) unlink($filepath);
        if ( file_exists( $import_script_filepath ) ) unlink($import_script_filepath);
        if ( file_exists( $export_script_filepath ) ) unlink($export_script_filepath);

        // Do not throw if the select statement had no items to copy.
        if ( getStringValue($select_statement) !== '' )
        {
            if ( ! GetDBExists($db, $select_statement, array()) )
            {
                // Yea, this was an exception, but it was because the select
                // statement had no results.  Do not treat this as an error.
                return;
            }
        }

        // Add the database error tag that will hide this from customers.
        $message = "A database error was encountered. ";
        $message .= $e->getMessage() . "<BR>";
        $message .= "filepath [{$filepath}]<BR>";
        $message .= "export_script_filepath [{$export_script_filepath}]<BR>";
        $message .= "import_script_filepath [{$import_script_filepath}]<BR>";
        $message .= "sql_filename[{$sql_filename}]";

        throw new Exception($message);
    }





}
/**
 * SelectIntoInsert
 *
 * This function takes a select into insert statement and modifies it
 * so that the select into insert can be ran against a X number
 * of records at a time to prevent blowing out physical resources.
 *
 * @param $db
 * @param $filename
 * @param $vars
 * @param array $replacefor
 * @throws Exception
 */
function SelectIntoInsert($db, $filename, $vars, $replacefor=array() )
{
    // Just being lazy here.
    // I should probably replace all of the SelectIntoInsert calls with CopyFromInto
    // at some point.  For now, just do this.
    CopyFromInto($db, $filename,$vars,$replacefor);
    return;

}



/* End of file db_helper.php */
/* Location: ./application/helpers/db_helper.php */
