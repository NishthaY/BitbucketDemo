<?php
function GenerateWeakPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < 6; $i++) {
        $n = random_int(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}
function ValidatePasswordStrength($password) {

    // The passowrd must be at least 7 characters in length.
    if(strlen($password) < 7)return FALSE;


    // Count the number of special characters of each classification.
    preg_match_all('/[0-9]/', $password, $numbers);
    preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^¨\\\]/', $password, $symbols);
    preg_match_all('/[a-z]/', $password, $lowercase);
    preg_match_all('/[A-Z]/', $password, $uppercase);

    $tests = 0;
    if(count($numbers[0]) > 0)
    {
        $tests++;
    }
    if(count($symbols[0]) > 0)
    {
        $tests++;
    }
    if(count($lowercase[0]) > 0)
    {
        $tests++;
    }
    if(count($uppercase[0]) > 0)
    {
        $tests++;
    }

    // The password must contain at least three of the required classifications.
    if($tests >= 3) return true;
    return false;

}
function ValidatePassword( $user_id, $password ) {
    $CI = &get_instance();
    $user = $CI->User_model->get_user_by_id($user_id);
    $stored_password = getArrayStringValue("password", $user);

    return password_verify($password,$stored_password);


}
function ChangeBack()
{
    $is_parent = false;
    if ( GetSessionValue("_companyparent_id") !== '' ) $is_parent = true;

    $is_standard = false;
    if ( GetSessionValue("_company_id") !== '' ) $is_standard = true;

    if ( $is_parent )
    {
        ChangeBackCompanyParent();
        return;
    }else if ( $is_standard )
    {
        ChangeBackCompany();
        return;
    }
}
function ChangeBackCompany()
{
    $CI = &get_instance();
    if ( ! IsLoggedIn() ) throw new Exception("You must be logged in to change back.");
    $user_id = GetSessionValue("user_id");
    $CI->load->model('User_model','user_model',true);

    SetSessionValue("company_id", GetSessionValue("_company_id"));
    SetSessionValue("_company_id", "");
    SetSessionValue("companyparent_id", "");
    SetSessionValue("_companyparent_id", "");

    $acls = $CI->user_model->get_user_acls_by_id($user_id);
    SetSessionValue("acls", $acls);
}
function ChangeBackCompanyParent()
{
    $CI = &get_instance();
    if ( ! IsLoggedIn() ) throw new Exception("You must be logged in to change back.");
    $user_id = GetSessionValue("user_id");
    $CI->load->model('User_model','user_model',true);
    $acls = $CI->user_model->get_user_acls_by_id($user_id);

    $_companyparent_id = GetSessionValue("_companyparent_id");

    // Does the user asking for this permission have the pii_download action?
    // If they do, they will get that permission on the change, else not.
    $pii_download = false;
    if ( IsAuthenticated('pii_download', "companyparent", $_companyparent_id) ) $pii_download = true;


    SetSessionValue("companyparent_id", $_companyparent_id);
    SetSessionValue("_companyparent_id", "");
    SetSessionValue("company_id", "");
    SetSessionValue("acls", $acls);

    // If you are an AP2 User and you are not returning the the A2P company, then
    // you do not get to keep your user permissions.  Instead, you will be come a parent
    // manager.
    $user = $CI->User_model->get_user_by_id($user_id);
    if ( GetArrayIntValue('company_id', $user) === A2P_COMPANY_ID && $_companyparent_id !== '' )
    {
        $acls = array();
        $acls[] = "Parent Manager";
        if ( $pii_download ) $acls[] = "PII Download";
        SetSessionValue("acls", $acls);
    }


}
function ChangeToCompany( $company_id )
{

    // Are you allowed to call this function?
    if ( ! IsLoggedIn() ) throw new Exception("You must be logged in to change customers.");
    if ( getStringValue($company_id) == "" ) throw new Exception("Invalid company_id.");
    if ( getStringValue($company_id) == "1" ) throw new Exception("You may not change to the master account.");

    // Collect information about the company you are about to join.
    $CI = &get_instance();
    $company = $CI->company_model->get_company( $company_id );
    if ( empty($company) ) throw new Exception("Unknown company.");
    if ( getArrayStringValue("company_id", $company) == "1") throw new Exception("Changing to this company is now allowed."); // No one changes to Advice2Pay!

    // Collect information about your current parent.
    $orig_companyparnet_id = GetCompanyParentId($company_id);

    // You must have permission to write against the target company AND you must have
    // enough permissions to write on the parent to be here.
    $allowed = false;
    if ( IsAuthenticated( "parent_company_write,company_write", "company", $company_id ) ) $allowed = true;
    if ( ! $allowed ) throw new Exception("You are not allowed to change to other customers.");

    // DISABLED
    // If the target company is disabled, do not allow them access.
    if ( getArrayStringValue("enabled", $company) != "t" ) throw new Exception("You are now allowed to change to a disabled customer.");

    // Does the user asking for this permission have the pii_download action?
    // If they do, they will get that permission on the change, else not.
    $pii_download = false;
    if ( IsAuthenticated('pii_download', "company", $company_id) ) $pii_download = true;

    // Craft the session.  Keep track of the parent if you are coming from one so we
    // know how to return.
    if ( GetSessionValue("companyparent_id") != ""  )
    {
        // Parent->Company
        SetSessionValue("_companyparent_id", GetSessionValue("companyparent_id"));
        SetSessionValue("companyparent_id", "");
        SetSessionValue("company_id", $company_id);
    }else{
        // ->Company
        SetSessionValue("_company_id", GetSessionValue("company_id"));
        SetSessionValue("company_id", $company_id);
    }

    // Create the new ACL permissions the user will have with the
    // new company.  Only add PII Download if they had that permission
    // in the old company.
    $acls = array();
    $acls[] = "Manager";
    if ( $pii_download ) $acls[] = "PII Download";
    SetSessionValue("acls", $acls);

    // Record this transaction so we can know when the last time this users
    // switched to this company.
    $CI->History_model->insert_history_changeto($company_id);

    // Audit this transaction.
    // Note: We thought it would be helpful to log the "Changed to Company" against both the parent and the
    // child to make it easier to see which company a parent user was moving to while doing support.
    $payload = array();
    $payload['CompanyId'] = GetArrayStringValue("company_id", $company);
    $payload['CompanyName'] = GetArrayStringValue("company_name", $company);
    $payload['Permissions'] = implode(",", $acls);
    AuditIt("Changed to company.", $payload, null, null, $orig_companyparnet_id);

}
function ChangeToCompanyParent( $company_parent_id )
{

    if ( ! IsLoggedIn() ) throw new Exception("You must be logged in to change parents.");
    if ( getStringValue($company_parent_id) == "" ) throw new Exception("Invalid company_parent_id.");

    $CI = &get_instance();
    $parent = $CI->CompanyParent_model->get_companyparent( $company_parent_id );

    // Do you have enough permissions to do this?
    $allowed = false;
    if ( IsAuthenticated( "parent_company_write" ) ) $allowed = true;
    if ( ! $allowed ) throw new Exception("You are not allowed to change to other parents.");

    // DISABLED
    // If the target companyparent is disabled, do not allow them access.
    if ( getArrayStringValue("Enabled", $parent) != "t" ) throw new Exception("You are now allowed to change to a disabled companyparent.");

    // Does the user asking for this permission have the pii_download action?
    // If they do, they will get that permission on the change, else not.
    $pii_download = false;
    if ( IsAuthenticated('pii_download', "companyparent", $company_parent_id) ) $pii_download = true;

    // Craft the session.  Move to the new companyparent while keeping track of the old one.
    SetSessionValue("_companyparent_id", GetSessionValue("companyparent_id"));
    SetSessionValue("companyparent_id", $company_parent_id);
    SetSessionValue("_company_id", GetSessionValue("company_id"));
    SetSessionValue("company_id", "");

    $acls = array();
    $acls[] = "Parent Manager";
    if ( $pii_download ) $acls[] = "PII Download";
    SetSessionValue("acls", $acls);

    // Record this transaction so we can know when the last time this users
    // switched to this company.
    $CI->History_model->insert_history_changeto_parent($company_parent_id);

    // Audit this transaction.
    $payload = array();
    $payload['CompanyParentId'] = GetArrayStringValue("Id", $parent);
    $payload['CompanyParentName'] = GetArrayStringValue("Name", $parent);
    AuditIt("Changed to parent.", $payload);

}
function IsActingAs()
{
    if ( GetSessionValue("_company_id") != "" ) return true;
    if ( GetSessionValue("_companyparent_id") != "" ) return true;
    return false;
}
function IsLoggedIn()
{
    if ( GetSessionValue("is_logged") == "TRUE" ) return true;
    return false;
}
function IsAuthenticated( $actions="", $target="", $target_id="" )
{

    // You must be logged in else you are not authenticated.
    if ( ! IsLoggedIn() ) return false;


    // Turn our packed string into an array.
    if ( empty($actions) )
    {
        // No actions.
        $actions = array();
    }
    else if ( strpos($actions, ",") === FALSE )
    {
        // Only one action.
        $actions = array(strtolower($actions));
    }
    else
    {
        // Multiple actions
        $array = explode(",", $actions);
        foreach($array as &$item)
        {
            $item = strtolower(trim($item));
        }
        $actions = $array;
    }

    // You must have the "acls" session object, else you are not authenticated.
    $acls = GetSessionObject( "acls" );
    if ( ! isset($acls) ) return FALSE;
    if ( ! is_array($acls) ) return FALSE;
    if ( empty($acls) ) return FALSE;
    $acls = array_map("strtolower", $acls); // Upper case all ACLs so case no longer matters.


    // SPECIAL ACLs
    // If the user has the all acl, then they are authenticated.
    if ( in_array( "all", $acls) ) return TRUE;

    // If the user did not provide any actions to check against, then
    // they are not authorized.
    if ( empty($actions) ) return FALSE;

    // Check to see if the user belongs to an Access Control List that allows
    // the list of actions provided.
    $user_id = GetSessionObject('user_id');
    $CI = &get_instance();
    $authenticated = $CI->User_model->is_authenticated($acls, $actions);
    if ( $authenticated ) return TRUE;

    // Not authenticated yet?  Okay, see if they are authenticated for a
    // specific target.
    if ( GetStringValue($target) !== '' && GetStringValue($target_id) !== '' )
    {
        $authenticated = $CI->User_model->is_authenticated_by_target($acls, $actions, $target, $target_id, $user_id);
        if ( $authenticated ) return TRUE;
    }


    return FALSE;

}

function SendAuthSMSCode($user_id, $phone=null)
{
    $CI = &get_instance();

    if ( getStringValue($phone) === '' )
    {
        $details = $CI->Login_model->get_login_details($user_id);
        $phone = getArrayStringValue("TwoFactorPhoneNumber", $details);
    }

    if ( $phone !== '' )
    {

        $characters = '0123456789';
        $code = RandomString(6,$characters);
        $code = strtoupper($code);
        $hashed_code = A2PHashClearText($code);

        //$message = "Your Advice2Pay verification code is: {$code}";
        $level = "advice2pay.com";
        if ( GetStringValue(HOSTNAME) !== '' ) $level = HOSTNAME;
        $message = "{$code} is your authentication code.\n@{$level} #{$code}";

        $sent = SendSMSMessage($phone, $message);
        if ( $sent )
        {
            $CI->Login_model->update_hash($user_id, $hashed_code);
        }
    }



}


/* End of file auth_helper.php */
/* Location: ./application/helpers/auth_helper.php */
