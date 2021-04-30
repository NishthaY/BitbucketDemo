<?php

/**
 * InfoFileDump
 *
 * If the info file exists, print it's content to STDOUT
 *
 * @param $identifier
 * @param $identifier_type
 * @param $info_type
 */
function InfoFileDump($identifier, $identifier_type, $info_type)
{
    $filename = "{$identifier}_{$identifier_type}_{$info_type}.info";
    $filepath = APPPATH . "../copy/" . $filename;
    if ( file_exists($filepath) )
    {
        $content = file_get_contents($filepath);
        print $content . "\n";
    }
}

/**
 * InfoFileRename
 *
 * Rename an info file where the "to" file will replace the other file.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $info_type
 * @param $to_info_type
 */
function InfoFileRename($identifier, $identifier_type, $info_type, $to_info_type)
{
    $filename = "{$identifier}_{$identifier_type}_{$info_type}.info";
    $filepath = APPPATH . "../copy/" . $filename;

    $to_filename = "{$identifier}_{$identifier_type}_{$to_info_type}.info";
    $to_filepath = APPPATH . "../copy/" . $to_filename;
    rename($filepath, $to_filepath);
}

/**
 * InfoFileExists
 *
 * TRUE/FALSE, does the specified info file exist?
 *
 * @param $identifier
 * @param $identifier_type
 * @param $info_type
 * @return bool
 */
function InfoFileExists($identifier, $identifier_type, $info_type)
{
    $filename = "{$identifier}_{$identifier_type}_{$info_type}.info";
    $filepath = APPPATH . "../copy/" . $filename;
    if ( file_exists($filepath) ) return TRUE;
    return FALSE;
}

/**
 * InfoFileOpen
 *
 * Open an info file and return a file handle.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $info_type
 * @param $mode
 * @return bool|resource
 */
function InfoFileOpen($identifier, $identifier_type, $info_type, $mode)
{
    $filename = "{$identifier}_{$identifier_type}_{$info_type}.info";
    $filepath = APPPATH . "../copy/" . $filename;
    return fopen($filepath, $mode);
}

/**
 * InfoFileDelete
 *
 * Delete an info file.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $info_type
 */
function InfoFileDelete($identifier, $identifier_type, $info_type)
{
    $filename = "{$identifier}_{$identifier_type}_{$info_type}.info";
    $filepath = APPPATH . "../copy/" . $filename;
    if ( file_exists($filepath) )
    {
        unlink($filepath);
    }
}

/**
 * InfoIsLineActivated
 *
 * Given a line number and a filepointer to an info file, return
 * true if the line is ACTIVE, else false.
 *
 * @param $tag
 * @param $line_no
 * @param $handle
 * @return bool
 */
function InfoIsLineActivated( $line_no, $handle )
{
    if ( is_resource($handle) )
    {
        // Does this info file have a "1" character in it's line number place in the info file?
        // If so, this is an activated line.
        fseek( $handle, $line_no - 1);
        if ( fgetc($handle) == '1' ) {
            return TRUE;
        }
    }
    return FALSE;
}

/**
 * InfoFileCreate
 *
 * Create a $tag.info file for the specified identifier.  Scan each column specified by
 * column code in the $column_list.  When scanning, if we find the text specified by
 * column code in an array of search tokens in the lookup file, the info bit will be
 * set to "1", else "0.
 *
 * $tag = 'bah';
 * $column_list = [ 'plan', 'relationship' ];
 * $search_lookup = [ 'plan' => ['money'], 'relationship' => ['money', 'gold'] ]
 *
 * @param $identifier
 * @param $identifier_type
 * @param $tag
 * @param $column_list
 * @param $search_lookup
 */
function InfoFileCreate($identifier, $identifier_type, $tag, $column_list, $search_lookup)
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


        // LOOKUP: COLUMN -> UPLOADED COLUMN FILE
        // Create a lookup of column code to column token which is basically the column filename.
        $files_lookup = array();
        $mapped_columns = $CI->Mapping_model->get_mapped_columns($company_id, $companyparent_id);
        if( ! empty($column_list) )
        {
            foreach($column_list as $column_name)
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



        // DEFAULT INFO FILE
        // First, create a file that indicates OFF for every row being imported.  This is a column file that is
        // the same height as the file, but every row in that file has "zero" in it.
        $count = 0;
        $fh_source = S3OpenFile(S3_BUCKET, $prefix, "col0.txt", 'r');
        InfoFileDelete( $identifier, $identifier_type, $tag);
        $fh_info = InfoFileOpen($identifier, $identifier_type, $tag, 'w');
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
        if ( $debug ) LogIt(__FUNCTION__, "Created the empty $tag.info file with X bits.  -->", $line_count);


        // BUSINESS LOGIC REVIEW
        // Look at each column that could have data that indicates beneficiary data.
        // If we see something that indicates the row is a beneficiary row, then update
        // the row to a "1".  Here we are creating a unique file for each beneficiary column.
        foreach($files_lookup as $column_name=>$filename)
        {
            $searchtokens = $search_lookup[$column_name];

            InfoFileDelete($identifier, $identifier_type, "$tag-{$column_name}");

            $count = 0;
            $fh_source  = S3OpenFile(S3_BUCKET, $prefix, $filename, 'r');
            $fh_info    = InfoFileOpen($identifier, $identifier_type, "$tag-{$column_name}", 'w');
            $iterator   = ReadTheFile($fh_source);
            foreach($iterator as $iteration)
            {
                if ( IsEncryptedString($iteration) ) $iteration = A2PDecryptString($iteration, $encryption_key);
                $iteration = trim($iteration);
                $iteration = strtoupper($iteration);

                if ( $count === 0 ) fwrite($fh_info, "X");
                else if ( in_array($iteration, $searchtokens) ) fwrite($fh_info,  "1");
                else fwrite($fh_info,  "0");
                $count++;

            }
            if ( is_resource($fh_source) ) fclose($fh_source);
            if ( is_resource($fh_info) ) fclose($fh_info);
            if ( $debug ) LogIt(__FUNCTION__, "Created the $tag-$column_name.info file.", $column_name);
        }


        // MERGE COLUMN FILES
        // Now that we know which rows are important rows by column, we want to merge those column
        // files into the master info file.  We want to preserve the "1s" in each column file in the
        // master file.
        foreach($files_lookup as $column_name=>$filename)
        {
            $fh_source  = InfoFileOpen($identifier, $identifier_type, "$tag", 'r+');
            $fh_info    = InfoFileOpen($identifier, $identifier_type, "$tag-{$column_name}", 'r+');
            $fh_temp    = InfoFileOpen($identifier, $identifier_type, "$tag-temp", 'w');

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
            InfoFileDelete($identifier, $identifier_type, "$tag");
            InfoFileRename($identifier, $identifier_type, "$tag-temp", "$tag");
            if ( $debug ) LogIt(__FUNCTION__, "Merged $tag-$column_name.info file into the master file.", $column_name);

        }


        // MOVE TO CLOUD
        // Now that we have the beneficiary info file, move it to the cloud.
        $fh_temp = S3OpenFile( S3_BUCKET, $prefix, "$tag.info",'w' );
        $fh_source  = InfoFileOpen($identifier, $identifier_type, "$tag", 'r+');
        for($i=0;$i<$line_count;$i++)
        {

            fseek($fh_source, $i);
            $source = fgetc($fh_source);

            fwrite($fh_temp, $source,1);
        }
        if ( is_resource($fh_source) ) fclose($fh_source);
        if ( is_resource($fh_temp) ) fclose($fh_temp);
        if ( $debug ) LogIt(__FUNCTION__, "Moved $tag.info to the cloud.");


        // REMOVE LOCAL COPY
        // Now that we have the info file on S3, remove it locally.
        InfoFileDelete($identifier, $identifier_type, $tag);

    }
    catch(Exception $e)
    {
        if( is_resource($fh_info) ) fclose($fh_info);
        if( is_resource($fh_temp) ) fclose($fh_temp);
        if( is_resource($fh_source) ) fclose($fh_source);
        if ( $debug ) LogIt(__FUNCTION__, "Got an exception!", $e->getMessage());
    }
}