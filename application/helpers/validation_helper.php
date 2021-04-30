<?php
/**
 * ProcessErrors
 *
 * This function will examine the ValidationErrors table and it will
 * create a collection of up to MAX_ROWS of errors that we can use
 * to generate an error report.  Each row will contain a list of columns
 * that are bad AND the file data for the mapped columns.
 *
 * This function will only pull PAGE_SIZE records at a time and will
 * keep going until all data has been exhaused or we have hit our MAX_ROWS.
 *
 * The end result is an errors/errors.json file on S3 that contains
 * a JSON object of the the bad data.
 *
 * @param $company_id
 * @throws Exception
 */
function ProcessErrors( $identifier, $identifier_type, $verbiage_group, $job_id, $debug=false )
{

    $CI = &get_instance();

    $company_id = $identifier;
    $companyparent_id = GetCompanyParentId($company_id);
    if( $identifier_type === 'companyparent' )
    {
        $company_id = null;
        $companyparent_id = $identifier;
    }

    $max_rows = 100;
    $page_size = 50;
    $last_id = 0;

    NotificationSetStatusMessage($verbiage_group, 'CHECKING', $job_id, $identifier, $identifier_type);


    // Create an array of row numbers and keep track of all invalid columns
    // in that row.  Review as many errors as you can until we have our max_rows
    // worth of data.
    $out = array();
    $errors = $CI->Validation_model->get_validation_errors( $identifier, $identifier_type, $last_id, $page_size );

    // If we have errors, update our status.
    if ( ! empty($errors) ) NotificationSetStatusMessage($verbiage_group, 'PREPARING', $job_id, $identifier, $identifier_type);

    while( ! empty($errors) ) {

        foreach($errors as $error){

            $row_no = getArrayIntValue("RowNumber", $error);
            $column = getArrayStringValue("ColumnName", $error);
            $last_id = getArrayIntValue("Id", $error);
            $message = getArrayStringValue("ErrorMessage", $error);

            if ( ! isset($out[$row_no]) )
            {
                if ( count($out) < $max_rows )
                {
                    $lookup = array();
                    $lookup[$column] = $column;
                    $lookup["messages"] = array();
                    $lookup["messages"][] = $message;
                    $out[$row_no] = $lookup;
                }
            }
            else
            {
                $lookup = $out[$row_no];
                $lookup[$column] = $column;
                $lookup["messages"][] = $message;
                $out[$row_no] = $lookup;
            }
        }
        $errors = $CI->Validation_model->get_validation_errors( $identifier, $identifier_type, $last_id, $page_size );
    }


    // Using the error structure we just created, we will now open all the
    // mapped column files on S3 and read them all in one line at a time.
    // We will consolidate the file back together, but only for the rows
    // referenced in the error structure.
    if ( $debug ) print_r($out, true);
    if ( $debug ) print "company_id[{$company_id}], companyparent_id[{$companyparent_id}]\n";
    $mapped_columns = $CI->Mapping_model->get_mapped_columns($company_id, $companyparent_id);
    $fh = array();

    // Open each of the mapped files for reading.
    foreach($mapped_columns as $mapped_column)
    {
        $filename = getArrayStringValue("GroupCode", $mapped_column);

        $prefix = GetS3Prefix('parsed', $identifier, $identifier_type);
        $url = "s3://".S3_BUCKET."/".$prefix."/{$filename}.txt";
        if ( $debug ) print("opening {$url}\n");
        $f = fopen($url, 'r');
        if ( $f ) $fh[] = $f;
        if ( ! $f ) throw new Exception("Yikes!  I could not open one of the parsed files.");

    }

    if ( empty($fh) ) throw new Exception("Yikes!  I found no files to validate.");

    $index = 0;
    $data = array();
    while ( ($line = fgets($fh[0]) ) !== false ) {

        $index++;

        // Read each row in all of the files.
        $row = array($line);
        $count = count($fh);
        for($i=1;$i<$count;$i++)
        {
            $row[] = fgets($fh[$i]);
        }

        // If the row in this file exists in our collection of errors
        // then add it to the data collection, else don't bother.
        if ( isset($out[$index]) )
        {
            if ( $debug ) print "row [{$index}] exists in our collection. Added the upload data.\n";
            $ref = &$out[$index];
            $ref["data"] = $row;
        }

    }

    // Close all of the files.
    foreach($fh as $f)
    {
        if ( $f ) fclose($f);
    }


    // WOOT!  We now have a data structure that contains X rows of
    // data errors and the full row of mapped column data.  Let's save
    // this to S3 so we can use it on the correction page.
    if ( $debug ) print_r($out, true);
    $prefix = GetS3Prefix('errors', $identifier, $identifier_type);
    S3MakeBucketPrefix(S3_BUCKET, $prefix);
    S3SaveEncryptedFile(S3_BUCKET, $prefix, "errors.json", json_encode($out));
}

