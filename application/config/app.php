<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*|--------------------------------------------------------------------------
| Release Tag
|--------------------------------------------------------------------------
|
| If you are releasing software for production, we will be updating this
| config variable to match the tag.  Set the value to TIMESTAMP to force JS
| to always reload.
*/
$config['build_tag'] = "TIMESTAMP"; //"20160322-01";

/*
|--------------------------------------------------------------------------
| Dependency Versions
|--------------------------------------------------------------------------
|
| If a referenced library does not exist in composer, then we will need
| to reference from our source code.  Store the version number here so we
| can easily switch between other versions later.
|
*/
$config['vJqueryValidate'] = "-1.15.0";
$config['vPusher'] = "-7.0.0";

/*
|--------------------------------------------------------------------------
| Marketing Site
|--------------------------------------------------------------------------
|
| When you log out of the application, you have to go somewhere.  Go here.
|
*/
$config['marketing_site'] = base_url() . "auth/login";


/*
|--------------------------------------------------------------------------
| Emails
|--------------------------------------------------------------------------
|
| Email settings.
|
*/
$config['divert_enabled'] = "f";                        // Divert all emails to a test address. ( "t" will divert the emails. )
$config['divert_email'] = "brian@advice2pay.com";       // Test address to recieve all emails when enabled.
$config['divert_display'] = "Brian Headlee";            // Test email display name for diverted emails.
$config['noreply_email'] = "no-reply@advice2pay.com";   // No-Reply from address ( must be real domain )
$config['noreply_display'] = "Advice2Pay (No Reply)";   // No-Reply email display name.

/*
|--------------------------------------------------------------------------
| File UPloads
|--------------------------------------------------------------------------
|
| The following settings configure the details with the file
| upload feature.
|
*/
$config['temp_upload_path']= APPPATH . '../uploads/';
$config['allowed_types']='csv';
$config['max_size']='51200';

/*
|--------------------------------------------------------------------------
| Timezone Display
|--------------------------------------------------------------------------
|
| When we display a date/time what timezone does the user get to see it
| in?
|
*/
$config['timezone_display'] = "America/Chicago";

defined('MAX_INSERTS_PER_IMPORT') or define('MAX_INSERTS_PER_IMPORT', 0);   // Zero wil turn off max inserts per import
defined('MAX_VALIDATION_ERRORS') OR define('MAX_VALIDATION_ERRORS', 5000);  // Zero will turn off max validation check.
defined('PREVIEW_FILE_LENGTH') OR define('PREVIEW_FILE_LENGTH', 25);


/*
|--------------------------------------------------------------------------
| AJAX Wizard Controllers
|--------------------------------------------------------------------------
|
| This array holds the name of the various CLI controllers that execute
| offline jobs for the Wizard Process specifically.
|
*/
$config['wizard_controllers'] = array(
    "GenerateImportFiles"
    ,"GenerateReports"
    ,"LoadImportFiles"
    ,"ParseCSVUpload"
    ,"ValidateCSVUpload"
);




/* End of file app.php */
/* Location: ./system/application/config/app.php */
