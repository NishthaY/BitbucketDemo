<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/*
|--------------------------------------------------------------------------
| Amazon AWS
|--------------------------------------------------------------------------
|
| Misc settings for AWS
|
*/
$config['aws_region'] = S3_BUCKET_REGION;
$config['aws_s3_php_version']   = "2006-03-01";
$config['aws_kms_php_version']  = "2014-11-01";

/*
|--------------------------------------------------------------------------
| Amazon S3 public/private keys
|--------------------------------------------------------------------------
|
| The key/value pair below grants us access to make s3 calls against the
| AWS S3.
|
*/
$config['aws_key']	= AWS_ACCESS_KEY_ID;
$config['aws_secret'] = AWS_SECRET_ACCESS_KEY;


/*
|--------------------------------------------------------------------------
| Amazon KMS public/private keys
|--------------------------------------------------------------------------
|
| The key/value pair below grants us access to make s3 calls against the
| AWS KSM.
|
*/
$config['kms_key']	= KMS_ACCESS_KEY_ID;
$config['kms_secret'] = KMS_SECRET_ACCESS_KEY;

/*
|--------------------------------------------------------------------------
| S3 bucket
|--------------------------------------------------------------------------
|
| This is the bucket name that we will read and write files from in AWS.
|
*/
$config['aws_bucket'] = S3_BUCKET;


/*
|--------------------------------------------------------------------------
| S3 bucket prefixs
|--------------------------------------------------------------------------
|
| This is the location where we store certain types of files in a bucket.
|
*/
$config['root_prefix']          = "companies/company_COMPANYID";
$config['upload_prefix']        = "companies/company_COMPANYID/uploads";
$config['parsed_prefix']        = "companies/company_COMPANYID/parsed";
$config['errors_prefix']        = "companies/company_COMPANYID/errors";
$config['import_prefix']        = "companies/company_COMPANYID/import";
$config['reporting_prefix']     = "companies/company_COMPANYID/reports/DATE/TYPE";
$config['archive_prefix']       = "companies/company_COMPANYID/archive/DATE";
$config['support_prefix']       = "companies/company_COMPANYID/support/TICKETID";
$config['export_prefix']        = "companies/company_COMPANYID/export";

$config['deploy_prefix']        = "deploy";

$config['parent_prefix']               = "parents/parent_COMPANYPARENTID";
$config['parent_upload_prefix']        = "parents/parent_COMPANYPARENTID/uploads";
$config['parent_parsed_prefix']        = "parents/parent_COMPANYPARENTID/parsed";
$config['parent_errors_prefix']        = "parents/parent_COMPANYPARENTID/errors";
$config['parent_split_prefix']         = "parents/parent_COMPANYPARENTID/split";
$config['parent_archive_prefix']       = "parents/parent_COMPANYPARENTID/archive/DATE";
$config['parent_support_prefix']       = "parents/parent_COMPANYPARENTID/support/TICKETID";
$config['parent_export_prefix']        = "parents/parent_COMPANYPARENTID/export";
