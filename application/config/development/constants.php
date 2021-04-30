<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// If we don't have a ENCRYPTION_KEY environment variable, then apache did not
// create our environment variables.  Do it ourselves.
if ( getenv("APP_NAME") == "" )
{
    // Expected line format of file:
    // export KEY=VALUE

    // If we are not on Heroku, then we need to manually setup our environment variables.
    // A developer will do that by creating a .env file in the document root.
    $filename = APPPATH . "../.env";
    if ( ! file_exists($filename) )
    {
        trigger_error("Unable to locate the local runtime environment variables.", E_USER_ERROR);
        exit;
    }

    $env = file( $filename );
    foreach($env as $line) {

        $line = trim($line);
        if ( $line == "" ) continue;

        $arr = explode(" ", $line);
        if ( ! is_array($arr) ) continue;
        if ( count($arr) != 2 ) continue;

        $arr = explode("=", $arr[1]);
        $key = $arr[0];
        $value = $arr[1];
        define($key, $value);
    }
}

// If we have not yet defined these constants, do it now.  There
// should be one for each item in the .env file.  ( Or Heroku Environment Setting )
defined('ENCRYPTION_KEY') OR define('ENCRYPTION_KEY', getenv("ENCRYPTION_KEY"));
defined('SENDGRID_API_KEY') OR define('SENDGRID_API_KEY', getenv("SENDGRID_API_KEY"));
defined('AWS_ACCESS_KEY_ID') OR define('AWS_ACCESS_KEY_ID', getenv("AWS_ACCESS_KEY_ID"));
defined('AWS_SECRET_ACCESS_KEY') OR define('AWS_SECRET_ACCESS_KEY', getenv("AWS_SECRET_ACCESS_KEY"));
defined('KMS_ACCESS_KEY_ID') OR define('KMS_ACCESS_KEY_ID', getenv("KMS_ACCESS_KEY_ID"));
defined('KMS_SECRET_ACCESS_KEY') OR define('KMS_SECRET_ACCESS_KEY', getenv("KMS_SECRET_ACCESS_KEY"));
defined('S3_BUCKET') OR define('S3_BUCKET', getenv("S3_BUCKET"));
defined('S3_BUCKET_REGION') OR define('S3_BUCKET_REGION', getenv("S3_BUCKET_REGION"));
defined('DATABASE_URL') OR define('DATABASE_URL', getenv("DATABASE_URL"));
defined('A2P_ENCRYPTION_KEY') OR define('A2P_ENCRYPTION_KEY', getenv("A2P_ENCRYPTION_KEY"));
defined('EMAIL_IMAGES_URL') OR define('EMAIL_IMAGES_URL', getenv("EMAIL_IMAGES_URL"));
defined('HOSTNAME') OR define('HOSTNAME', getenv("HOSTNAME"));
defined('MAX_ASYNC_JOBS') OR define('MAX_ASYNC_JOBS', getenv("MAX_ASYNC_JOBS"));
defined('APP_NAME') OR define('APP_NAME', getenv("APP_NAME"));
defined('TWILIO_SID') OR define('TWILIO_SID', getenv("TWILIO_SID"));
defined('TWILIO_ACCESS_TOKEN') OR define('TWILIO_ACCESS_TOKEN', getenv("TWILIO_ACCESS_TOKEN"));
defined('TWILIO_REPLY_TO') OR define('TWILIO_REPLY_TO', getenv("TWILIO_REPLY_TO"));
defined('PUSHER_URL') OR define('PUSHER_URL', getenv("PUSHER_URL"));
defined('PUSHER_CLUSTER') OR define('PUSHER_CLUSTER', getenv("PUSHER_CLUSTER"));
defined('HEROKU_SECRET_KEY') OR define('HEROKU_SECRET_KEY', getenv("HEROKU_SECRET_KEY"));
defined('DEV_SUPPORT_EMAIL_ADDRESS') OR define('DEV_SUPPORT_EMAIL_ADDRESS', getenv("DEV_SUPPORT_EMAIL_ADDRESS"));
defined('APP_PORT') OR define('APP_PORT', getenv("APP_PORT"));
