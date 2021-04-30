<?php
/**
 * Before we begin...
 *
 * bucket - Name of the S3 bucket will be operating in.
 * prefix - folder path to follow in the specified bucket.
 * filename - the name of the file in the bucket.
 * options - Any possible options you can pass the PHP f-series of functions, like fopen
 *
 */

require('vendor/autoload.php');  // Include AWS into application.
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

/**
 * S3Debug
 * Developers, set the return value to TRUE in order to get verbose
 * output from the function in this helper file.  You do not want
 * to commit this file set to true.
 *
 * @return false
 */
function S3Debug()
{
    return false;
}

/**
 * S3Config
 * This function will return an array of the various config values required
 * to connect to AWS and S3.  This structure is determined by the requirements
 * for the factory function that generates an S3 client.
 *
 * @return array
 */
function S3Config()
{
    return array(
        "region" => GetConfigValue("aws_region")
        , "version" => GetConfigValue("aws_s3_php_version")
        ,'credentials' => array(
            'key'    => GetConfigValue("aws_key")
            ,'secret' => GetConfigValue("aws_secret")
        )
    );
}

/**
 * S3GetClient
 * Create a client connection to S3 using the configuration
 * values specified in the application configuration.
 *
 * @return mixed
 */
function S3GetClient() {
    $s3 = Aws\S3\S3Client::factory( S3Config() );
    $s3->registerStreamWrapper();
    return $s3;
}

/**
 * S3OpenFile
 * Open a file handle to a file located on S3.  If the file cannot be opened
 * then FALSE is returned.
 *
 * bucket - name of the S3 bucket to look in.
 * prefix - folder path to follow in the specified bucket.
 * filename - the name of the file to be opened.
 * options - Any possible options you can pass the PHP fopen command.
 *
 * @param $bucket
 * @param $prefix
 * @param $filename
 * @param string $options
 * @return false|resource
 */
function S3OpenFile( $bucket, $prefix, $filename, $options='r' )
{
    $client = S3GetClient();

    $filename = "s3://" . $bucket . "/" . $prefix . "/" . $filename;
    $fp = null;
    $fp = fopen($filename, $options);
    return $fp;
}

/**
 * S3OpenSeekableFile
 * Open a file resource to a file on S3, but make the file seekable so
 * we can jump around to specific locations in the file rather than reading
 * it from start to finish.
 *
 * bucket - name of the S3 bucket to look in.
 * prefix - folder path to follow in the specified bucket.
 * filename - the name of the file to be opened.
 * options - Any possible options you can pass the PHP fopen command.
 *
 * @param $bucket
 * @param $prefix
 * @param $filename
 * @param string $options
 * @return false|resource
 */
function S3OpenSeekableFile( $bucket, $prefix, $filename, $options='r' )
{
    $context = stream_context_create(['s3' => ['seekable' => true]]);

    $client = S3GetClient();

    $filename = "s3://" . $bucket . "/" . $prefix . "/" . $filename;
    $fp = null;
    $fp = fopen($filename, $options, false, $context);
    return $fp;
}

/**
 * S3SaveEncryptedFile
 * Create a file on S3 with the given content and then flag the file to
 * be encrypted while at rest on the server.
 *
 * bucket - name of the S3 bucket to look in.
 * prefix - folder path to follow in the specified bucket.
 * filename - the name of the file to be opened.
 *
 * @param $bucket
 * @param $prefix
 * @param $filename
 * @param $content
 */
function S3SaveEncryptedFile( $bucket, $prefix, $filename, $content ) {

    $client = S3GetClient();

    $payload = array(
        'Bucket'               => $bucket,
        'Key'                  => "{$prefix}/{$filename}",
        'Body'                 => $content,
        'ServerSideEncryption' => 'AES256',
    );
    $result = $client->putObject($payload);

}

/**
 * S3EncryptAllFiles
 * Given a bucket and prefix, find all files in that folder and set the
 * server side encryption flag to true.
 *
 * @param $bucket
 * @param $prefix
 * @return bool
 */
function S3EncryptAllFiles( $bucket, $prefix ) {

    try {
        $client = S3GetClient();

        // Remove any trailing slashes on the prefix if needed.
        if ( substr($prefix, -1) == "/") $prefix = fLeftBack($prefix, "/");

        $list = array();
        $objects = $client->getIterator(
            'ListObjectsV2',
            [
                'Bucket'    => $bucket,
                'Prefix'    => $prefix,
            ]
        );
        foreach ($objects as $object) {

            $key = getArrayStringValue("Key", $object); // Grab the key from S3
            $key = replaceFor($key, $prefix, "");       // Remove the prefix from the front.

            if ( $key != "/" ) // Skip the folder
            {
                // Remove the leading "/" character.
                if ( substr($key, 0, 1) == "/") $key = fRight($key, "/");
                S3EncryptExistingFile( $bucket, $prefix, $key, $prefix, $key );
            }
        }

        $s3=null;

    } catch (S3Exception $e) {
        $s3 = null;
        return false;
    }

    return true;
}

/**
 * S3EncryptExistingFile
 * Update an existing file on S3 so that it will be encrypted at rest.
 * There are both a source and a target to this function so this can double as
 * a document move as well.  If you provide the same name for the source and
 * target, the existing file will be encrypted at rest and remain in the same
 * place.
 *
 * @param $bucket
 * @param $source_prefix
 * @param $source_filename
 * @param $target_prefix
 * @param $target_filename
 * @return mixed
 */
function S3EncryptExistingFile( $bucket, $source_prefix, $source_filename, $target_prefix, $target_filename ) {

    // S3EncryptExistingFile
    //
    // Copy an existing file on S3 to a new location and set the target file
    // to have server side encryption enabled.
    // ---------------------------------------------------------------------
    $client = S3GetClient();

    $options = array(
        'Bucket'               => $bucket,
        'Key'                  => "{$target_prefix}/{$target_filename}",
        'CopySource'           => $bucket . "/{$source_prefix}/{$source_filename}",
        'ServerSideEncryption' => 'AES256',
    );
    return $client->copyObject($options);

}

/**
 * S3StreamFileAsZipArchive
 * Download an S3 document and stream it to a browser.
 *
 * NOTE: This function is not used today because the content
 * on S3 is encrypted.  Thus there is little practical use for
 * this function.
 *
 * @param $bucket
 * @param $prefix
 * @param $filename
 * @param $zip_filename
 */
function S3StreamFileAsZipArchive( $bucket, $prefix, $filename, $zip_filename ) {

    $zip = new ZipStream\ZipStream( $zip_filename );
    $fp = S3OpenFile( $bucket, $prefix, $filename);
    try{
        if ( $fp )
        {
            $zip->addFileFromStream($filename, $fp);
            fclose($fp);
            $zip->finish();
        }
    }catch(Exception $e){
        if ($fp) fclose($fp);
    }

}

/**
 * S3MakeBucketPrefix
 * This function will make a bucket prefix on S3.  This is like
 * making a folder in a bucket.
 *
 * @param $bucket
 * @param $prefix
 */
function S3MakeBucketPrefix( $bucket, $prefix ) {
    $client = S3GetClient();
    $url = "s3://{$bucket}/{$prefix}";
    if ( ! file_exists($url) )
    {
        mkdir($url);
    }
}

/**
 * S3ListFile
 * Return the AWS S3 meta data associated with a file as an array.
 *
 * @param $bucket
 * @param $prefix
 * @param $filename
 * @return array|mixed
 * @throws Exception
 */
function S3ListFile( $bucket, $prefix, $filename )
{
    $files = S3ListFiles( S3_BUCKET, $prefix );

    $search = "{$prefix}/{$filename}";
    foreach($files as $file)
    {
        $key = getArrayStringValue("Key", $file);
        if ( $key == $search)
        {
            return $file;
        }
    }
    return array();
}

/**
 * S3ListDirectories
 *
 * List all directories found in the specified bucket and prefix.
 * By default, this will only show you the top level directories.
 * You may optionally specify that want to show all sub directories too
 * if you want.
 *
 * @param $bucket
 * @param string $prefix
 * @param bool $recursive
 * @return array
 * @throws Exception
 */
function S3ListDirectories($bucket, $prefix="", $top_level_only=true ) {

    $list = array();
    try{

        // Remove any trailing slashes on the prefix if needed.
        if ( substr($prefix, -1) == "/") $prefix = fLeftBack($prefix, "/");

        $client = S3GetClient();
        $objects = $client->getIterator(
            'ListObjectsV2',
            [
                'Bucket'    => $bucket,
                'Prefix'    => $prefix,
            ]
        );
        foreach ($objects as $object)
        {

            $key = getArrayStringValue("Key", $object);
            if ( $key != "{$prefix}/" )
            {
                $last_char = trim(substr($key, -1));
                if( $last_char === '/')
                {
                    if ( $top_level_only )
                    {
                        // Here we look at the key and decide how many slashes it has.  If there are more
                        // than two, then this directory is a sub directory of a top level directory.
                        // since the top_level_only flag is true, we will show
                        if ( substr_count ( replaceFor($key, $prefix, "") , "/" ) === 2 )
                        {
                            $list[] = $object;
                        }
                    }
                    else
                    {
                        $list[] = $object;
                    }


                }

            }
        }

    } catch (S3Exception $e) {
        if ( S3Debug() ) pprint_r($e->getMessage());
        throw new Exception("Unable to list bucket[${bucket}], prefix[{$prefix}]");
    }
    return $list;
}

/**
 * S3ListFiles
 * List all files found in the specified bucket and prefix.
 * This will return a collection of arrays where each array holds the AWS S3
 * metadata for the file.
 *
 * Directories are ignored.
 *
 * @param $bucket
 * @param string $prefix
 * @return array
 * @throws Exception
 */
function S3ListFiles( $bucket, $prefix="" ) {

    $list = array();
    try{

        // Remove any trailing slashes on the prefix if needed.
        if ( substr($prefix, -1) == "/") $prefix = fLeftBack($prefix, "/");

        $client = S3GetClient();
        $objects = $client->getIterator(
            'ListObjectsV2',
            [
                'Bucket'    => $bucket,
                'Prefix'    => $prefix,
            ]
        );
        foreach ($objects as $object) {
            //print_r($object);
            $key = getArrayStringValue("Key", $object);
            if ( $key != "{$prefix}/" ) {
                $list[] = $object;
            }
        }

    } catch (S3Exception $e) {
        if ( S3Debug() ) pprint_r($e->getMessage());
        throw new Exception("Unable to list bucket[${bucket}], prefix[{$prefix}]");
    }
    return $list;
}

/**
 * S3DeleteFile
 *
 * This function will delete a file from S3 if it exists.
 *
 * @param $bucket
 * @param $prefix
 * @param $filename
 * @return bool
 * @throws Exception
 */
function S3DeleteFile($bucket, $prefix, $filename )
{
    $client = null;
    try
    {
        $bucket = getStringValue($bucket);
        $prefix = getStringValue($prefix);
        $filename = getStringValue($filename);

        if ( getStringValue($bucket) == "" ) throw new Exception("Missing required value bucket.");
        if ( getStringValue($prefix) == "" ) throw new Exception("Missing required value prefix.");
        if ( getStringValue($filename) == "" ) throw new Exception("Missing required value filename.");

        // Remove any trailing slashes on the prefix if needed.
        if ( substr($prefix, -1) == "/") $prefix = fLeftBack($prefix, "/");

        if ( S3DoesFileExist( $bucket, $prefix, $filename ) )
        {
            $client = S3GetClient();
            $client->DeleteObject(["Bucket" => $bucket, "Key" => "{$prefix}/{$filename}",]);
            $client = null;

            return TRUE;
        }
    }
    catch (S3Exception $e)
    {
        $client = null;
        return FALSE;
    }

    return FALSE;
}

/**
 * S3DeleteBucketContent
 *
 * This function will delete everything in a bucket/prefix.  If
 * the optional keep_filename is provided, a file in the bucket/prefix
 * matching that key will remain.
 *
 * @param $bucket
 * @param $prefix
 * @param string $keep_filename
 * @return bool
 * @throws Exception
 */
function S3DeleteBucketContent( $bucket, $prefix, $keep_filename="" ) {

    $result = null;
    try{

        $bucket = getStringValue($bucket);
        $prefix = getStringValue($prefix);
        $keep_filename = getStringValue($keep_filename);

        if ( getStringValue($bucket) == "" ) throw new Exception("Missing required value bucket.");
        if ( getStringValue($prefix) == "" ) throw new Exception("Missing required value prefix.");
        $keep_filename = getStringValue($keep_filename);

        // Remove any trailing slashes on the prefix if needed.
        if ( substr($prefix, -1) == "/") $prefix = fLeftBack($prefix, "/");

        $client = S3GetClient();

        $list = array();
        $objects = $client->getIterator(
            'ListObjectsV2',
            [
                'Bucket'    => $bucket,
                'Prefix'    => $prefix,
            ]
        );
        foreach ($objects as $object) {

            $key = getArrayStringValue("Key", $object); // Grab the key from S3
            $key = replaceFor($key, $prefix, "");       // Remove the prefix from the front.

            if ( $key != "/" ) // Skip the folder
            {
                // Remove the leading "/" character.
                if ( substr($key, 0, 1) == "/") $key = fRight($key, "/");

                // If we don't have a filename, we will delete!
                if ( $keep_filename == "" ) $client->DeleteObject( ["Bucket" => $bucket,"Key" => "{$prefix}/{$key}",]);

                // If we have a filename, don't delete the keep file.
                if ( $keep_filename != "" &&  $key != $keep_filename ) $client->DeleteObject( ["Bucket" => $bucket,"Key" => "{$prefix}/{$key}",]);

            }

        }

        $s3=null;

    } catch (S3Exception $e) {
        $s3 = null;
        if ( S3Debug() ) pprint_r($result);
        return false;
    }

    return true;
}

/**
 * S3DoesFileExist
 * This function will return the boolean true or false if the
 * file exists in the specified bucket and prefix.
 *
 * @param $bucket
 * @param $prefix
 * @param $filename
 * @return bool
 * @throws Exception
 */
function S3DoesFileExist( $bucket, $prefix, $filename ) {
    $result = null;
    try{

        $bucket = getStringValue($bucket);
        $prefix = getStringValue($prefix);
        $filename = getStringValue($filename);

        if ( getStringValue($bucket) == "" ) throw new Exception("Missing required value bucket.");
        if ( getStringValue($filename) == "" ) throw new Exception("Missing required value filename.");

        // Remove any trailing slashes on the prefix if needed.
        if ( substr($prefix, -1) == "/") $prefix = fLeftBack($prefix, "/");

        $client = S3GetClient();
        $bucket = $bucket;

        $list = array();
        $objects = $client->getIterator(
            'ListObjectsV2',
            [
                'Bucket'    => $bucket,
                'Prefix'    => $prefix,
            ]
        );
        foreach ($objects as $object) {
            $key = getArrayStringValue("Key", $object);

            if ( $key != "{$prefix}/" ) // Skip the folder
            {
                if ( replaceFor($key, "{$prefix}/", "") == $filename ) return true;
            }

        }

        $s3=null;

    } catch (S3Exception $e) {
        $s3 = null;
        if ( S3Debug() ) pprint_r($result);
    }

    return false;
}

/**
 * GetS3Prefix
 * This function will build an A2P S3 prefix given the various inputs.
 * The tag is the top most folder.  Possible values today follow and can
 * be identified by reviewing the prefix values in the aws config file.
 *
 * TAGS: root, upload, parsed, errors, import, reporting, archive, support,
 * export, deploy, split
 *
 * The identifier type is either 'company' or 'companyparent'.
 * The identifier is the numeric unique key for the identifier_type
 *
 * What you get back is the S3 prefix for the given tab specific to the
 * identifier_type provided with the identifier replaced in the results.
 *
 * @param $tag
 * @param $identifier
 * @param $identifier_type
 * @return string
 */
function GetS3Prefix( $tag, $identifier, $identifier_type )
{
    S3GetClient();
    if ( $identifier_type === 'company' )
    {
        return replaceFor(GetConfigValue("{$tag}_prefix"), "COMPANYID", $identifier);
    }
    else if ( $identifier_type === 'companyparent' )
    {
        if ( $tag === '' )
            return replaceFor(GetConfigValue("parent_prefix"), "COMPANYPARENTID", $identifier);
        else
            return replaceFor(GetConfigValue("parent_{$tag}_prefix"), "COMPANYPARENTID", $identifier);
    }
    return GetConfigValue("{$tag}_prefix");
}

/* End of file s3_helper.php */
/* Location: ./application/helpers/s3_helper.php */
