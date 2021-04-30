<?php

/**
 * pprint_r
 *
 * Pretty Print some data in HTML format for debugging.
 * @param $data
 */
function pprint_r($data, $title='pprint_r')
{
    RenderViewSTDOUT('debug', [ 'data' => $data, 'title' => $title ]);
}

/**
 * TimeIt
 *
 * This function just writes a log message to the LogTimer table.  It is
 * recording how long something took and ties it to a company and import date.
 *
 * The timer value saved is the $time passed in.  That time is in some number
 * of units specified by $type which can be SECONDS, MINUTES or HOURS.
 *
 * @param $company_id
 * @param $import_date
 * @param $message
 * @param $time
 * @param string $type
 */
function TimeIt( $company_id, $import_date, $message, $time, $type='SECONDS' )
{
    $CI = &get_instance();
    $CI->load->model('Log_model', 'log_model');
    $type = strtoupper($type);
    switch( $type )
    {
        case 'SECONDS':
            $CI->log_model->insert_log_timer($company_id, $import_date, $message, $time);
            break;
        case 'MINUTES':
            $CI->log_model->insert_log_timer($company_id, $import_date, $message, 0, $time);
            break;
        case 'HOURS':
            $CI->log_model->insert_log_timer($company_id, $import_date, $message, 0, 0, $time);
            break;
    }
}

/**
 * LogIt
 *
 * This function will insert a record into the "Log" table.  It takes a short and long description
 * and you can optionally pass in an object as $payload.  The payload will be JSON encoded before
 * being saved to the database.
 *
 * The user, company and parent ids may be provided.  If they are not, we will attempt to pull
 * them from the session, if the session is available.
 *
 * @param $short
 * @param string $long
 * @param string $payload
 * @param null $user_id
 * @param null $company_id
 * @param null $company_parent_id
 */
function LogIt( $short, $long="", $payload="", $user_id=null, $company_id=null, $company_parent_id=null )
{
    $CI = &get_instance();
    $CI->load->model('Log_model', 'log_model');
    $CI->log_model->log_it($short, $long, $payload, $user_id, $company_id, $company_parent_id);
}

/**
 * AuditIt
 *
 * This function will insert a record into the "Audit" table.
 * - desc: human readable thing being audited.
 * - payload: associated data at the time of audit.
 *
 * The user, company and parent ids maybe be passed in.  If they are not then
 * the values will be pulled from the session if available.
 *
 * @param $desc
 * @param $payload
 * @param null $user_id
 * @param null $company_id
 * @param null $company_parent_id
 */
function AuditIt( $desc, $payload, $user_id=null, $company_id=null, $company_parent_id=null )
{
    $CI = &get_instance();
    $CI->load->model('Log_model', 'log_model');
    $CI->log_model->audit_it($desc, $payload, $user_id, $company_id, $company_parent_id);
}

/**
 * GetRuntimeError
 *
 * This function will return an error message that was encountered while a
 * background process was running.  This is an "unhandled" or "critical" error
 * that means the background process failed and the step was rolled back.
 * The message will be obfuscated if we can tell it contains internal information
 * that is of no use the client.
 *
 * @param $company_id
 * @return string
 */
function GetRuntimeError($company_id=null, $companyparent_id=null)
{
    $CI = &get_instance();

    // Look for a runtime error.  If we have one that we recognize as internal, we will
    // obfuscate it.
    $runtime_error = $CI->Wizard_model->select_recent_error($company_id, $companyparent_id);
    $runtime_error = getArrayStringValue("ErrorMessage", $runtime_error);


    $obfuscate = false;
    $haystack = strtoupper($runtime_error);
    if ( strpos($haystack, "A PHP ERROR WAS ENCOUNTERED") !== FALSE  ) $obfuscate = true;
    if ( strpos($haystack, "A DATABASE ERROR WAS ENCOUNTERED") !== FALSE  ) $obfuscate = true;
    if ( strpos($haystack, "AN UNCAUGHT EXCEPTION WAS ENCOUNTERED") !== FALSE ) $obfuscate = true;
    if ( strpos($haystack, "A2P-INTERNAL") !== FALSE ) $obfuscate = true;
    if ( $obfuscate )
    {
        $runtime_error = "An unexpected situation has occurred and support has been notified. Please try again later.";
    }
    return $runtime_error;
}

/**
 * HandleAJAXRuntimePHPError
 *
 * If we detect a runtime PHP error, we will create an automated support ticket
 * for the user/company that triggered it.
 *
 * @param $job_id
 */
function HandleAJAXRuntimePHPError( $job_id )
{

    $CI = &get_instance();
    $CI->config->load("app");

    // Get information about the AJAX job that has failed.
    $job = $CI->Queue_model->get_job($job_id);
    $job_id = getArrayStringValue("Id", $job);
    $controller = getArrayStringValue("Controller", $job);
    $payload = json_decode(getArrayStringValue("Payload", $job));
    $user_id = getArrayStringValue("0", $payload);
    $company_id = getArrayStringValue("1", $payload);
    $companyparent_id = getArrayStringValue("2", $payload);
    $error = getArrayStringValue("ErrorMessage", $job);
    $reason = getArrayStringValue("ErrorMessage", $job);

    $critical_error = false;
    if ( strpos(strtoupper($error), "A PHP ERROR WAS ENCOUNTERED") !== FALSE ) $critical_error = true;
    if ( strpos(strtoupper($error), "A DATABASE ERROR WAS ENCOUNTERED") !== FALSE ) $critical_error = true;
    if ( strpos(strtoupper($error), "AN UNCAUGHT EXCEPTION WAS ENCOUNTERED") !== FALSE ) $critical_error = true;

    if ( $critical_error )
    {
        if ( $company_id !== '' ) CreateSupportTicket($company_id, $user_id, $reason, $job_id);
        if ( $company_id === '' && $companyparent_id !== '' ) CreateParentSupportTicket($companyparent_id, $user_id, $reason, $job_id);
    }


}
/* End of file debug_helper.php */
/* Location: ./application/helpers/debug_helper.php */
