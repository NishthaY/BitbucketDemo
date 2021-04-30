<?php

use Twilio\Rest\Client;

/**
 * NotificationSetStatusMessage
 *
 * Change the background task/job status message.  Take a verbiage key and the
 * verbiage group ( background class name ) and issue the appropriate notification
 * type based on the identifier and identifier type.
 *
 * @param $verbiage_group
 * @param $verbiage_key
 * @param $job_id
 * @param $identifier
 * @param $identifier_type
 */
function NotificationSetStatusMessage($verbiage_group, $verbiage_key, $job_id, $identifier, $identifier_type, $replacefor=array() )
{

    $CI = &get_instance();

    if ( GetStringValue($verbiage_group) === '' ) return;
    if ( GetStringValue($verbiage_key) === '' ) return;
    if ( GetStringValue($job_id) === '' ) return;
    if ( GetStringValue($identifier) === '' ) return;
    if ( GetStringValue($identifier_type) === '' ) return;


    $verbiage_group = strtolower($verbiage_group);
    $words = $CI->Verbiage_model->get($verbiage_group, $verbiage_key);
    $age = $CI->Queue_model->get_job_age($job_id);

    // Do a replace for on the words if we have a replacefor array.
    if ( ! empty($replacefor) )
    {
        foreach($replacefor as $key=>$value)
        {
            $words = replacefor($words, $key, $value);
        }
    }

    // Good lord, this ate an hour of my time.  Maybe logging when a notification
    // has no verbiage I will catch it sooner next time.
    if ( GetStringValue($words) )
    {
        LogIt("NOTICE", "Missing verbiage record for notification_key[{$verbiage_key}] group[{$verbiage_group}].");
    }

    if ( $identifier_type === 'company' )
    {
        $payload = array();
        $payload['JobId'] = $job_id;
        $payload['VerbiageGroup'] = $verbiage_group;
        $payload['VerbiageKey'] = $verbiage_key;
        $payload['Age'] = $age;
        $payload['Words'] = $words;
        $payload['CompanyId'] = $identifier;
        NotifyCompanyChannelUpdate($identifier, 'dashboard_task', 'BackgroundTaskStatusMessageEventHandler', $payload);
    }
    if ( $identifier_type === 'companyparent')
    {
        $payload = array();
        $payload['JobId'] = $job_id;
        $payload['VerbiageGroup'] = $verbiage_group;
        $payload['VerbiageKey'] = $verbiage_key;
        $payload['Age'] = $age;
        $payload['Words'] = $words;
        $payload['CompanyParentId'] = $identifier;
        NotifyCompanyParentChannelUpdate($identifier, 'dashboard_task', 'BackgroundTaskStatusMessageEventHandler', $payload);
    }
}


function NotifyCompanyParentChannel($companyparent_id, $event_name, $payload=array())
{
    $CI = &get_instance();
    $CI->load->library('CI_Pusher', 'ci_pusher');

    if ( GetStringValue($companyparent_id) === "" ) return;
    if ( GetStringValue($event_name) === "" ) return;

    $pusher = $CI->ci_pusher->get_pusher();

    if ( GetStringValue($companyparent_id) !== '' )
    {
        $channel = "private-".APP_NAME."-companyparent-{$companyparent_id}";
        $payload['companyparent_id'] = $companyparent_id;
        LogIt("NotifyCompanyParentChannel", "channel[{$channel}] ");
        $pusher->trigger($channel, $event_name, $payload);
    }
}
function NotifyCompanyChannel($company_id, $event_name, $payload=array())
{
    $CI = &get_instance();
    $CI->load->library('CI_Pusher', 'ci_pusher');

    if ( GetStringValue($company_id) === "" ) return;
    if ( GetStringValue($event_name) === "" ) return;

    $pusher = $CI->ci_pusher->get_pusher();
    $pusher->trigger("private-".APP_NAME."-company-{$company_id}", $event_name, $payload);

    // If there is a parent company associated with this company, we will also tell the
    // parent.  They may or may not be interested.  It is up to them.
    $companyparent_id = GetCompanyParentId($company_id);
    if ( GetStringValue($companyparent_id) !== '' )
    {

        $payload['company_id'] = $company_id;
        NotifyCompanyParentChannel($companyparent_id, $event_name, $payload);
    }
}
function NotifyCompanyChannelUpdate($company_id, $task_name, $js_function, $js_data=array())
{
    $CI = &get_instance();
    $CI->load->library('CI_Pusher', 'ci_pusher');

    if ( GetStringValue($company_id) === "" )   return;
    if ( GetStringValue($task_name) === "" )    return;
    if ( GetStringValue($js_function) === "" )  return;
    if ( ! isset($js_data) ) $js_data = array();

    $pusher = $CI->ci_pusher->get_pusher();

    $channel = "private-".APP_NAME."-company-{$company_id}";
    $event_name = $task_name . "-update";

    $payload = array();
    $payload['js_function'] = $js_function;
    $payload['js_data'] = json_encode($js_data);

    $pusher->trigger($channel, $event_name, $payload);

    // Notify the company parent, if there is one.
    $companyparent_id = GetCompanyParentId($company_id);
    NotifyCompanyParentChannelUpdate($companyparent_id, $task_name, $js_function, $js_data);


}
function NotifyCompanyParentChannelUpdate($companyparent_id, $task_name, $js_function, $js_data=array())
{
    $CI = &get_instance();
    $CI->load->library('CI_Pusher', 'ci_pusher');

    if ( GetStringValue($companyparent_id) === "" )   return;
    if ( GetStringValue($task_name) === "" )    return;
    if ( GetStringValue($js_function) === "" )  return;
    if ( ! isset($js_data) ) $js_data = array();

    $payload = array();
    $payload['js_function'] = $js_function;
    $payload['js_data'] = json_encode($js_data);

    // Notify the company parent, if there is one.
    if ( GetStringValue($companyparent_id) !== '' )
    {
        $pusher = $CI->ci_pusher->get_pusher();
        $channel = "private-".APP_NAME."-companyparent-{$companyparent_id}";
        $event_name = $task_name . "-update";
        LogIt(__FUNCTION__, "event[{$event_name}], channel[{$channel}], payload: " . json_encode($payload));
        $pusher->trigger($channel, $event_name, $payload);
    }
}
function SendEmail( $to, $to_address, $subject, $body, $from=null, $from_address=null, $user_id=null, $company_id=null, $company_parent_id=null ) {

    // SendEmail
    //
    // Send an email using SendGrid
    // ---------------------------------------------------------------------

    $CI =& get_instance();

    // Divert email if needed.
    if ( GetConfigValue("divert_enabled") == "t" )
    {
        $to = GetConfigValue("divert_display");
        $to_address = GetConfigValue("divert_email");
    }

    // No from address, us the no-reply address on the config.
    if ( getStringValue($from_address) == "" )
    {
        $from_address = GetConfigValue("noreply_email");
        if ( getStringValue($from) == "" ) $from = GetConfigValue("noreply_display");
    }

    // Add missing display names.
    if ( getStringValue($from) == "" ) $from = $from_address;
    if ( getStringValue($to) == "" ) $to = $to_address;

    // Load the SendGrid library.
    $path = APPPATH . "../vendor/sendgrid/sendgrid/lib/SendGrid.php";
    if ( ! file_exists( $path ) ) throw new Exception("You are missing the SendGrid library.  Do you need to do a composer update?");
    require_once($path);

    // Construct the SendGrid object.
    $sg_from = new SendGrid\Email($from, $from_address);
	$sg_to = new SendGrid\Email($to, $to_address);
	$sg_content = new SendGrid\Content("text/html", $body);
	$mail = new SendGrid\Mail($sg_from, $subject, $sg_to, $sg_content);
    $sg = new \SendGrid(SENDGRID_API_KEY);

    // Send the email.
    $response = $sg->client->mail()->send()->post($mail);

    if ( empty($response) )
    {
        LogIt("SendGrid Issue", "Malformed response object from SendGrid");
        return FALSE;
    }

    $status_code = $response->statusCode();
    if ( GetStringValue($status_code) === '' )
    {
        LogIt("SendGrid Issue", "Unable to find the status code in the SendGrid response object.", $response->body());
        return FALSE;
    }

    // If we could not send it, stop.
    $status_code = GetIntValue($status_code);
    if ( $status_code < 200 || $status_code >= 300 ) {
        LogIt("SendGrid Issue", "Status code was not in the success range.", $response->body());
        return FALSE;
    }

	// Activate this during the development process if you need to see what is happening.
    if ( false )
    {
        if ( $CI->input->is_cli_request() )
        {
            print_r("from: {$from_address} ( {$from} )" . PHP_EOL);
            print_r("to: {$to_address} ( {$to} )" . PHP_EOL);
            print_r("subject: {$subject}" . PHP_EOL);
            print_r("body: {$body}" . PHP_EOL);
            print_r("RESPONSE:" . PHP_EOL);
            print_r($response );
        }
        else
        {
            pprint_r("from: {$from_address} ( {$from} )");
            pprint_r("to: {$to_address} ( {$to} )");
            pprint_r("subject: {$subject}");
            pprint_r("body: {$body}");
            pprint_r($response );
        }

        exit;
    }

    // Secure this data with our A2P Encryption Key.
    $encryption_key = A2PGetEncryptionKey();
    $encrypted_to = A2PEncryptString($to, $encryption_key, true);
    $encrypted_to_address = A2PEncryptString($to_address, $encryption_key, true);
    $encrypted_from = A2PEncryptString($from, $encryption_key, true);
    $encrypted_from_address = A2PEncryptString($from_address, $encryption_key, true);
    $encrypted_subject = A2PEncryptString($subject, $encryption_key, true);
    $encrypted_body = A2PEncryptString($body, $encryption_key, true);

    // Archive this transaction.
    $CI->Archive_model->archive_email_transaction( $company_parent_id, $company_id, $user_id, $encrypted_to, $encrypted_to_address, $encrypted_from, $encrypted_from_address, $encrypted_subject, $encrypted_body);

    // Audit the transaction.
    $transaction_id = $CI->Archive_model->get_archive_email_transaction_id($encrypted_to_address);
    $payload = array();
    $payload = array_merge($payload, array('HistoryEmailId' => $transaction_id));
    $payload = array_merge($payload, array('ToAddress' => $to_address));
    $payload = array_merge($payload, array('Subject' => $subject));
    AuditIt("Email sent.", $payload);


    return TRUE;
}
function SendFYISupportEmail( $title, $message ) {
    $CI =& get_instance();

    // What is our LevelTag?
    $tag = LevelTag();

    $body = $message;

    // Add the level tag to the email subject for everywhere except prod.
    if ( $tag != "PROD") $tag = "({$tag}) ";
    if ( $tag == "PROD" ) $tag = "";
    $subject = $tag . "[Advice2Pay] " . $title;


    // Send it.
    if ( ! IsDevelopment() )
    {
        // Deliver the notification to all super users, if not in development.
        $addresses = $CI->User_model->super_user_email_address_list();
        foreach($addresses as $address )
        {
            if ( getStringValue($address) != "" ) SendEmail( 'Advice2Pay Support', $address, $subject, $body, GetConfigValue("noreply_display"), GetConfigValue("noreply_email"));
        }
    }
    else
    {
        // Deliver the notification to the email address specified in the DEV_SUPPORT_EMAIL_ADDRESS environment
        // variable when running in development.
        if ( getStringValue(DEV_SUPPORT_EMAIL_ADDRESS) != "" ) SendEmail( 'Advice2Pay Support', DEV_SUPPORT_EMAIL_ADDRESS, $subject, $body, GetConfigValue("noreply_display"), GetConfigValue("noreply_email"));
    }


}
function SendBackgroundJobReportEmail( $identifier, $identifier_type, $job_id, $user_id, $warnings=array(), $audit=array(), $view_array=array() ) {
    $CI =& get_instance();

    if ( $identifier_type === 'company' )
    {
        $company_id = $identifier;
        $companyparent_id = GetCompanyParentId($company_id);
        $company = $CI->Company_model->get_company($identifier);
        $identifier_name = GetArrayStringValue("company_name", $company);
    }
    else if ( $identifier_type === 'companyparent')
    {
        $company_id = null;
        $companyparent_id = $identifier;
        $companyparent = $CI->CompanyParent_model->get_companyparent($identifier);
        $identifier_name = getArrayStringValue("Name", $companyparent);
    }
    else
    {
        return;
    }

    // What is our LevelTag?
    $tag = LevelTag();

    // Get info about the company that opened the ticket.
    if ( GetStringValue($company_id) !== '' )
    {
        $company = $CI->Company_model->get_company($company_id);
        $company_name = getArrayStringValue("company_name", $company);
    }
    else
    {
        $company = $CI->CompanyParent_model->get_companyparent($companyparent_id);
        $company_name = getArrayStringValue("Name", $company);
    }

    // Collect info on the job.
    $job = $CI->Queue_model->get_job($job_id);


    // Draw a special message?
    if ( GetArrayStringValue('hostname', $view_array) === '' )          $view_array['hostname'] = HOSTNAME;
    if ( GetArrayStringValue('identifier', $view_array) === '' )        $view_array['identifier'] = $identifier;
    if ( GetArrayStringValue('identifier_type', $view_array) === '' )   $view_array['identifier_type'] = $identifier_type;
    if ( GetArrayStringValue('identifier_name', $view_array) === '' )   $view_array['identifier_name'] = $identifier_name;
    if ( GetArrayStringValue('warnings', $view_array) === '' )          $view_array['warnings'] = $warnings;
    if ( GetArrayStringValue('audit', $view_array) === '' )             $view_array['audit'] = $audit;
    if ( GetArrayStringValue('job', $view_array) === '' )               $view_array['job'] = $job;
    $message = RenderViewAsString("emails/background_task_report", $view_array);

    $view_array = array();
    $view_array = array_merge($view_array, array("title" => "Background Task Report"));
    $view_array = array_merge($view_array, array("message" => $message));
    $view_array = array_merge($view_array, array("button_label" => "Go To Advice2Pay"));
    $view_array = array_merge($view_array, array("email_images_url" => EMAIL_IMAGES_URL));
    $view_array = array_merge($view_array, array("hostname" => GetURLDomainName()));
    $view_array = array_merge($view_array, array("salutation" => "Hello"));


    // What will the email look like?
    $body = RenderViewAsString("templates/template_email", $view_array);

    // Add the level tag to the email subject for everywhere except prod.
    if ( $tag != "PROD") $tag = "({$tag}) ";
    if ( $tag == "PROD" ) $tag = "";
    $subject = $tag . "[Advice2Pay] Background Job Report";

    // Send it.
    if ( ! IsDevelopment() )
    {
        // Deliver the notification to all super users, if not in development.
        $addresses = $CI->User_model->super_user_email_address_list();
        foreach($addresses as $address )
        {
            if ( getStringValue($address) != "" ) SendEmail( 'Advice2Pay Support', $address, $subject, $body, GetConfigValue("noreply_display"), GetConfigValue("noreply_email"), $user_id, $company_id);
        }
    }
    else
    {
        // Deliver the notification to the email address specified in the DEV_SUPPORT_EMAIL_ADDRESS environment
        // variable when running in development.
        if ( getStringValue(DEV_SUPPORT_EMAIL_ADDRESS) != "" ) SendEmail( 'Advice2Pay Support', DEV_SUPPORT_EMAIL_ADDRESS, $subject, $body, GetConfigValue("noreply_display"), GetConfigValue("noreply_email"), $user_id, $company_id);
    }

}
function SendSupportEmail( $company_id, $companyparent_id, $reason, $ticket_id, $user_id ) {
    $CI =& get_instance();

    // What is our LevelTag?
    $tag = LevelTag();

    // Get info about the company that opened the ticket.
    if ( GetStringValue($company_id) !== '' )
    {
        $company = $CI->Company_model->get_company($company_id);
        $company_name = getArrayStringValue("company_name", $company);
    }
    else
    {
        $company = $CI->CompanyParent_model->get_companyparent($companyparent_id);
        $company_name = getArrayStringValue("Name", $company);
    }

    $title = "A2P Support Issue";

    // Draw a special message?
    $view_array = array();
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));
    $view_array = array_merge($view_array, array("message" => $reason));
    $view_array = array_merge($view_array, array("ticket_id" => $ticket_id));
    $view_array = array_merge($view_array, array("company_name" => $company_name));
    $message = RenderViewAsString("emails/support_ticket", $view_array);


    // View values.
    $view_array = array();
    $view_array = array_merge($view_array, array("title" => $title));
    $view_array = array_merge($view_array, array("message" => $message));
    $view_array = array_merge($view_array, array("button_label" => "Go To Advice2Pay"));

    // Default Values.
    $view_array = array_merge($view_array, array("email_images_url" => EMAIL_IMAGES_URL));
    $view_array = array_merge($view_array, array("hostname" => GetURLDomainName()));
    $view_array = array_merge($view_array, array("salutation" => "Red Alert!"));


    // What will the email look like?
    $body = RenderViewAsString("templates/template_email", $view_array);

    // Add the level tag to the email subject for everywhere except prod.
    if ( $tag != "PROD") $tag = "({$tag}) ";
    if ( $tag == "PROD" ) $tag = "";
    $subject = $tag . "[Advice2Pay] " . $title;

    // Send it.
    if ( ! IsDevelopment() )
    {
        // Deliver the notification to all super users, if not in development.
        $addresses = $CI->User_model->super_user_email_address_list();
        foreach($addresses as $address )
        {
            if ( getStringValue($address) != "" ) SendEmail( 'Advice2Pay Support', $address, $subject, $body, GetConfigValue("noreply_display"), GetConfigValue("noreply_email"), $user_id, $company_id);
        }
    }
    else
    {
        // Deliver the notification to the email address specified in the DEV_SUPPORT_EMAIL_ADDRESS environment
        // variable when running in development.
        if ( getStringValue(DEV_SUPPORT_EMAIL_ADDRESS) != "" ) SendEmail( 'Advice2Pay Support', DEV_SUPPORT_EMAIL_ADDRESS, $subject, $body, GetConfigValue("noreply_display"), GetConfigValue("noreply_email"), $user_id, $company_id);
    }

}
function SendUserEmail( $user_id, $company_id, $companyparent_id, $title, $image, $message, $button_label="Return to Site" ) {

    // SendUserEmail
    //
    // Send a templitized Advice2Pay email to a user.
    // ---------------------------------------------------------------------

    $CI =& get_instance();

    // Who are we going to send this email to?
    $user = $CI->User_model->get_user_by_id($user_id);
    $email_address = getArrayStringValue("email_address", $user);
    $display_name = getArrayStringValue("first_name", $user) . " " . getArrayStringValue("last_name", $user);

    // View values.
    $view_array = array();
    $view_array = array_merge($view_array, array("title" => $title));
    $view_array = array_merge($view_array, array("message" => $message));
    $view_array = array_merge($view_array, array("icon_image" => $image));
    $view_array = array_merge($view_array, array("button_label" => $button_label));

    // Default Values.
    $view_array = array_merge($view_array, array("email_images_url" => EMAIL_IMAGES_URL));
    $view_array = array_merge($view_array, array("hostname" => GetURLDomainName()));
    $view_array = array_merge($view_array, array("salutation" => "Hello " . getArrayStringValue("first_name", $user)));

    // What will the email look like?
    $body = RenderViewAsString("templates/template_email", $view_array);

    // Add the level tag to the email subject for everywhere except prod.
    $tag = LevelTag();
    if ( $tag != "PROD") $tag = "({$tag}) ";
    if ( $tag == "PROD" ) $tag = "";
    $subject = $tag . "[Advice2Pay] " . $title;

    // Send it.
    return SendEmail( $display_name, $email_address, $subject, $body, GetConfigValue("noreply_display"), GetConfigValue("noreply_email"), $user_id, $company_id, $companyparent_id);

}
function SendWelcomeEmail( $user_id, $single_use_auth_code, $name ) {

    // SendWelcomeEmail
    //
    // Send a welcome email.
    // ---------------------------------------------------------------------

    $view_array = array();
    $view_array = array_merge($view_array, array("single_use_auth_code" => $single_use_auth_code));
    $view_array = array_merge($view_array, array("name" => $name));
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));

    $title = "Welcome to Advice2Pay";
    $image = "";
    $body = RenderViewAsString("emails/welcome", $view_array);
    SendUserEmail($user_id, null, null, $title, $image, $body, "Login Here");

}
function SendPasswordResetEmail( $user_id, $single_use_auth_code ) {

    // SendPasswordResetEmail
    //
    // Send a password reset email.
    // ---------------------------------------------------------------------

    $view_array = array();
    $view_array = array_merge($view_array, array("single_use_auth_code" => $single_use_auth_code));
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));

    $title = "Password Reset";
    $image = "";
    $body = RenderViewAsString("emails/password_reset", $view_array);
    return SendUserEmail($user_id, null, null, $title, $image, $body);

}

function SendDataValidationCompleteEmail( $user_id, $company_id=null, $companyparent_id=null ) {

    // SendDataValidationCompleteEmail
    //
    // Send a data validation complete email.
    // ---------------------------------------------------------------------

    if ( GetStringValue($company_id) !== '' )
    {
        $identifier = $company_id;
        $identifier_type = 'company';
    }
    else
    {
        $identifier = $companyparent_id;
        $identifier_type = 'companyparent';
    }
    $identifier_name = GetIdentifierName($identifier, $identifier_type);


    $view_array = array();
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));
    $view_array['identifier'] = $identifier;
    $view_array['identifier_type'] = $identifier_type;
    $view_array['identifier_name'] = $identifier_name;

    $title = "Data Validation Complete";
    $image = "validation.png";
    $body = RenderViewAsString("emails/data_validation_complete", $view_array);
    SendUserEmail($user_id, $company_id, $companyparent_id, $title, $image, $body);
}
function SendParentUploadValidateCSVFailed( $user_id, $company_id=null, $companyparent_id=null )
{
    $CI =& get_instance();
    $companyparent = $CI->CompanyParent_model->get_companyparent($companyparent_id);
    $companyparent_name = GetArrayStringValue('Name', $companyparent);

    $view_array = array();
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));
    $view_array = array_merge($view_array, array("companyparent_name" => $companyparent_name));

    $title = "Data Validation Complete";
    $image = "validation.png";
    $body = RenderViewAsString("emails/parent_data_validation_failed", $view_array);
    SendUserEmail($user_id, $company_id, $companyparent_id, $title, $image, $body);
}
function SendDataValidationFailedEmail( $user_id, $company_id=null, $companyparent_id=null ) {

    // SendDataValidationFailedEmail
    //
    // Send a data validation failed email.
    // ---------------------------------------------------------------------

    $CI =& get_instance();

    $identifier_description = "";
    if ( GetStringValue($company_id) !== '' )
    {
        $company = $CI->Company_model->get_company($company_id);
        $identifier_description = GetArrayStringValue('company_name', $company);
    }
    else
    {
        $companyparent = $CI->CompanyParent_model->get_companyparent($companyparent_id);
        $identifier_description = GetArrayStringValue('Name', $companyparent);
    }

    $view_array = array();
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));
    $view_array = array_merge($view_array, array("company_name" => $identifier_description));

    $title = "Data Validation Failed";
    $image = "validation.png";
    $body = RenderViewAsString("emails/data_validation_failed", $view_array);
    SendUserEmail($user_id, $company_id, null, $title, $image, $body);
}
function SendUploadCompleteEmail( $user_id, $company_id=null, $companyparent_id=null ) {

    // SendUploadCompleteEmail
    //
    // Send a upload complete email.
    // ---------------------------------------------------------------------

    $CI =& get_instance();

    $identifier_description = "";
    if ( GetStringValue($company_id) !== '' )
    {
        $company = $CI->Company_model->get_company($company_id);
        $identifier_description = GetArrayStringValue('company_name', $company);
    }
    else
    {
        $companyparent = $CI->CompanyParent_model->get_companyparent($companyparent_id);
        $identifier_description = GetArrayStringValue('Name', $companyparent);
    }


    $view_array = array();
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));
    $view_array = array_merge($view_array, array("company_name" => $identifier_description));

    $title = "File Upload Complete";
    $image = "upload.png";
    $body = RenderViewAsString("emails/upload_complete", $view_array);
    SendUserEmail($user_id, $company_id, $companyparent_id, $title, $image, $body);
}
function SendUploadFailedEmail( $user_id, $company_id=null, $companyparent_id=null ) {

    // SendUploadFailedEmail
    //
    // Send a upload failed email.
    // ---------------------------------------------------------------------

    $view_array = array();
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));

    $title = "File Upload Failed";
    $image = "upload.png";
    $body = RenderViewAsString("emails/upload_failed", $view_array);
    SendUserEmail($user_id, $company_id,$companyparent_id, $title, $image, $body);
}
function SendDraftReportsGeneratedEmail( $user_id, $company_id ) {

    // SendDraftReportsGeneratedEmail
    //
    // Send a draft report success email.
    // ---------------------------------------------------------------------

    $CI =& get_instance();

    $company = $CI->Company_model->get_company($company_id);
    $view_array = array();
    $view_array = array_merge($view_array, array("upload_date" => GetUploadDateDescription($company_id)));
    $view_array = array_merge($view_array, array("company_name" => getArrayStringValue("company_name", $company)));
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));

    $title = "Draft Reports Generated";
    $image = "reviewreports.png";
    $body = RenderViewAsString("emails/draft_reports_generated", $view_array);
    SendUserEmail($user_id, $company_id, null, $title, $image, $body);

}
function SendDraftReportsFailedEmail( $user_id, $company_id ) {

    // SendDraftReportsFailedEmail
    //
    // Send a draft report failed email.
    // ---------------------------------------------------------------------

    $CI =& get_instance();

    $company = $CI->Company_model->get_company($company_id);

    $view_array = array();
    $view_array = array();
    $view_array = array_merge($view_array, array("upload_date" => GetUploadDateDescription($company_id)));
    $view_array = array_merge($view_array, array("company_name" => getArrayStringValue("company_name", $company)));
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));

    $title = "Additional Info Required";
    $image = "reviewreports.png";
    $body = RenderViewAsString("emails/draft_reports_failed", $view_array);
    SendUserEmail($user_id, $company_id, null, $title, $image, $body);

}

/**
 * SendParentUploadParseCSVFailed
 *
 * Notify a parent company that their upload has failed and they should
 * return to the application.
 *
 * @param $user_id
 * @param null $company_id
 * @param null $companyparent_id
 */
function SendParentUploadParseCSVFailed( $user_id, $company_id=null, $companyparent_id=null )
{
    if ( GetStringValue($companyparent_id) === '' ) return;
    if ( GetStringValue($user_id) === '' ) return;

    $view_array = array();
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));

    $title = "File Upload Failed";
    $image = "upload.png";
    $body = RenderViewAsString("emails/parent_upload_failed", $view_array);
    SendUserEmail($user_id, null, $companyparent_id, $title, $image, $body);
}
function SendParentUploadMapCompaniesWaiting( $user_id, $company_id=null, $companyparent_id=null ) {
    $view_array = array();
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));

    $title = "Company Mapping";
    $image = "upload.png";
    $body = RenderViewAsString("emails/parent_map_waiting", $view_array);
    SendUserEmail($user_id, $company_id,$companyparent_id, $title, $image, $body);
}
function SendParentUploadMapCompaniesFailed( $user_id, $company_id=null, $companyparent_id=null ) {
    $view_array = array();
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));

    $title = "Company Mapping";
    $image = "upload.png";
    $body = RenderViewAsString("emails/parent_map_failed", $view_array);
    SendUserEmail($user_id, $company_id,$companyparent_id, $title, $image, $body);
}
function SendParentUploadSplitCSVFailed( $user_id, $company_id=null, $companyparent_id=null ) {
    $view_array = array();
    $view_array = array_merge($view_array, array("hostname" => HOSTNAME));

    $title = "Company Uploads";
    $image = "upload.png";
    $body = RenderViewAsString("emails/parent_split_failed", $view_array);
    SendUserEmail($user_id, $company_id,$companyparent_id, $title, $image, $body);
}
/**
 * Attempts to deliver a message to a phone number.
 *
 * @param $phone
 * @param $message
 * @return bool
 */
function SendSMSMessage($phone, $message)
{

    $phone = StripNonNumeric($phone);
    $message = getStringValue($message);
    $twilio_sid = getStringValue(TWILIO_SID);
    $twilio_token = getStringValue(TWILIO_ACCESS_TOKEN);
    $twilio_reply_to = getStringValue(TWILIO_REPLY_TO);

    if ( strlen($phone) !== 10) return FALSE;
    if ( $message === '' ) return FALSE;
    if ( $twilio_sid === '' ) return FALSE;
    if ( $twilio_token === '' ) return FALSE;
    if ( $twilio_reply_to === '' ) return FALSE;


    $client = new Client($twilio_sid, $twilio_token);

    // Use the client to do fun stuff like send text messages!
    $client->messages->create(
    // the number you'd like to send the message to
        "+1{$phone}",
        array(
            'from' => "+1{$twilio_reply_to}"
            , 'body' => $message
        )
    );

    return TRUE;
}

/* End of file notification_helper.php */
/* Location: ./application/helpers/notification_helper.php */
