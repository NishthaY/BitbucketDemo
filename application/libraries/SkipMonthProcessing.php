<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class SkipMonthProcessing
{

    public function execute( $company_id )
    {
        $CI =& get_instance();
        $CI->load->model("SkipMonthProcessing_model");

        $identifier = $company_id;
        $identifier_type = 'company';
        $import_date = GetUploadDate($identifier);
        $archive_datetag = date("Ym", strtotime("-1 months", strtotime($import_date)));
        //LogIt("SkipMonthProcessing", "identifier", $identifier);
        //LogIt("SkipMonthProcessing", "identifier_type", $identifier_type);
        //LogIt("SkipMonthProcessing", "import_date", $import_date);
        //LogIt("SkipMonthProcessing", "archive_datetag", $archive_datetag);

        // Find the location of the archive.
        $archive_prefix = GetS3Prefix('archive', $identifier, $identifier_type);
        $archive_prefix = replaceFor($archive_prefix, "COMPANYID", $identifier);
        $archive_prefix = replaceFor($archive_prefix, "COMPANYPARENTID", $identifier);
        $archive_prefix = replaceFor($archive_prefix, "DATE", $archive_datetag);
        $archive_prefix .= "/upload";
        //LogIt("SkipMonthProcessing", "archive_prefix", $archive_prefix);

        // Find the location where we are going to place the file.
        $upload_prefix = GetS3Prefix('upload', $identifier, $identifier_type);
        //LogIt("SkipMonthProcessing", "upload_prefix", $upload_prefix);

        // Find the filename of the file we are going to restore from the archive.
        $filename = "";
        $files = S3ListFiles(S3_BUCKET, $archive_prefix);
        if ( count($files) == 0 ) throw new Exception("File not found.");
        if ( count($files) > 1 ) throw new Exception("Found too many files.");
        foreach($files as $file)
        {
            $filename = getArrayStringValue("Key", $file);
            $file = fRightBack($filename, "/");
        }
        //LogIt("SkipMonthProcessing", "file", $file);
        //LogIt("SkipMonthProcessing", "filename", $filename);

        // Copy the file from one location to the other, cleaning out the landing location first.
        S3DeleteBucketContent(S3_BUCKET, $upload_prefix);
        S3EncryptExistingFile( S3_BUCKET, $archive_prefix, $file, $upload_prefix, $file );
        //LogIt("SkipMonthProcessing", "file", $file);

        // Record that we have processed this month using the skip month processing logic.
        $CI->SkipMonthProcessing_model->insert_record($identifier, $import_date);

        // Return the full path and file that we just created.
        return "{$upload_prefix}/{$file}";
    }

    public function rollback($company_id, $import_date='')
    {
        $CI =& get_instance();
        $CI->load->model("SkipMonthProcessing_model");

        // What is our import date?
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) throw new Exception("Invalid import_date. How is that possible?");

        $CI->SkipMonthProcessing_model->remove_record($company_id, $import_date);
    }

}
