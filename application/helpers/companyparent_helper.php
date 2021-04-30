<?php

function IsCompanyParentNameAvailable( $parent_name ) {
    $CI = &get_instance();
    $parent = $CI->CompanyParent_model->get_parent_by_name($parent_name);
    if ( empty($parent) ) return true;
    return false;
}

/**
 * GetCompanyParentId
 *
 * Given a company id, return the associated companyparent_id.
 *
 * @param $company_id
 * @return string
 */
function GetCompanyParentId($company_id )
{
    if ( GetStringValue($company_id) === '' ) return '';

    // NOTE: Right now, a company can only have one parent.  If that
    // were to change, following this function through the code would
    // help you understand what would have to change.

    $CI = &get_instance();
    $companyparent_id = $CI->CompanyParent_model->get_companyparent_by_company($company_id);
    $companyparent_id = GetArrayStringValue("CompanyParentId", $companyparent_id);
    return $companyparent_id;
}

/**
 * GetCompanyParentEncryptionKey
 *
 * This function will return the A2P Encryption Key for the
 * specified CompanyParent.
 *
 * Throws if we can't get the key.
 *
 * @param $companyparent_id
 * @return mixed
 * @throws Exception
 */
function GetCompanyParentEncryptionKey($companyparent_id )
{
    $CI = &get_instance();
    $CI->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));

    $alias_name = "alias/" . APP_NAME . "/companyparent_" . $companyparent_id;
    $cache_name = "crypto_{$alias_name}";

    // PRODCOPY
    // When in prod copy, we use the production keys.
    if ( APP_NAME === 'a2p-prodcopy')
    {
        $alias_name = "alias/a2p-prod/companyparent_" . $companyparent_id;
        $cache_name = "crypto_{$alias_name}";
    }

    $encryption_key = $CI->cache->get($cache_name);
    if ( GetStringValue($encryption_key) === 'FALSE' )
    {
        // No.  You can't create an encryption key for a companyparent that does not exist.
        $parent = $CI->CompanyParent_model->get_companyparent($companyparent_id);
        if ( empty($parent) ) throw new Exception("Could not locate the companyparent.");

        // Fail if we can't find the companyparent encryption key.
        $encryption_key = $CI->CompanyParent_model->select_companyparent_encryption_key($companyparent_id);
        if ( $encryption_key === '' ) throw new Exception("CompanyParent has not been assigned an encryption key yet.");

        // Fail if the key does not appear to be an AWS KMS key.
        if ( ! StartsWith($encryption_key, "{a2p-aws-kms}:" ) ) throw new Exception("CompanyParent encryption key is malformed.");

        // Decrypt the encryption_key using the CMK.
        $encryption_key = KMSDecrypt($alias_name, $encryption_key);

        // Store the key into the cache.
        $CI->cache->save($cache_name, $encryption_key, 300);
    }
    return $encryption_key;
}

/**
 * CreateCompanyParentEncryptionKey
 *
 * This function will create an A2P Encryption key for
 * a CompanyParent.
 *
 * @param $companyparent_id
 * @throws Exception
 */
function CreateCompanyParentEncryptionKey($companyparent_id )
{
    $CI = &get_instance();

    // No.  You can't create an encryption key for a companyparent that does not exist.
    $parent = $CI->CompanyParent_model->get_companyparent($companyparent_id);
    if ( empty($parent) ) throw new Exception("Unable to find companyparnet specified.");

    // No no.  You can't create a new encryption key if you already have one.
    $encryption_key = $CI->CompanyParent_model->select_companyparent_encryption_key($companyparent_id);
    if( $encryption_key !== '' ) throw new Exception("We already have an encryption key.  You can't make a new one.");

    // No no no!  You can't create a key if you are in prodcopy.
    if ( APP_NAME === 'a2p-prodcopy' ) throw new Exception("You may not create new encryption keys in a2p-prodcopy.");


    // Check to see if the key pool has any keys in it, if it does grab one.
    $available_keys = $CI->Support_model->count_ready_keypool_keys();
    if ( $available_keys < 1 ) throw new Exception("Pool exhausted.  Please contact support to expand the security key pool to continue.");
    $key_id = $CI->Support_model->keypool_getnext();


    // Find the key in AWS that is associated with the key in the pool we just selected.
    $reserved_alias = "alias/" . APP_NAME . "/keypool_" . $key_id;
    $cmk = KMSGetAlias($reserved_alias);
    if ( empty($cmk) ) throw new Exception("Unable to find reserved security key.  Please contact support for assistance.");
    $cmk_id = GetArrayStringValue('TargetKeyId', $cmk);

    // Create a new ALIAS that ties the CMK to the parentcompany, then remove the alias that tied it to the pool
    $kms = KMS();
    $alias_name = "alias/" . APP_NAME . "/companyparent_" . $companyparent_id;
    $alias_description = APP_NAME . ": CompanyParent ( {$companyparent_id} )";
    $payload = array();
    $payload['AliasName'] = $alias_name;
    $payload['AliasDescription'] = $alias_description;
    $payload['TargetKeyId'] = $cmk_id;
    $kms->createAlias($payload);
    $kms->deleteAlias( array('AliasName'=>$reserved_alias) );

    // Update the company so it now contains the encryption key.
    $key = $CI->Support_model->select_keypool_by_id($key_id);
    $encryption_key = GetArrayStringValue('EncryptionKey', $key);
    if ( $encryption_key === '' ) throw new Exception("Unable to create encryption key for company.  Please contact support for assistance");
    $CI->CompanyParent_model->update_companyparent_encryption_key($companyparent_id, $encryption_key);

    // Remove the key from the pool
    $CI->Support_model->delete_keypool_by_id($key_id);

    // Create a new key to replace the one we just used.
    // TODO: Add a security key here.  However, we can't do that until after parent dashboard is done.
    //$CI->queue_model->add_worker_job($company_id, GetSessionValue('user_id'), "GenerateSecurityKey", "index");

    // Audit this transaction.
    $payload = array();
    $payload['AlaisName'] = $alias_name;
    $payload['AliasDescription'] = $alias_description;
    $payload['KeyId'] = $cmk_id;
    $payload['CompanyParentId'] = $companyparent_id;
    $payload['CompanyParentName'] = GetArrayStringValue('Name', $parent);
    AuditIt("Created customer master key.", $payload, GetSessionValue('user_id'), null, GetSessionValue('customerparent_id'));

}
/* End of file companyparent_helper.php */
/* Location: ./application/helpers/companyparent_helper.php */
