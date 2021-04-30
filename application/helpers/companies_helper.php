<?php
function IsCompanynameAvailable( $company_name ) {
    $CI = &get_instance();
    $CI->load->model('Company_model', 'company_model');
    $company = $CI->company_model->get_company_by_name($company_name);
    if ( empty($company) ) return true;
    return false;
}
function GetCompanyCarrierDescription( $company_id, $carrier_id ) {
    $CI = &get_instance();
    $CI->load->model('Company_model', 'company_model');
    $data = $CI->company_model->get_company_carrier( $company_id, $carrier_id );
    return getArrayStringValue("UserDescription", $data);
}
function GetCompanyName( $company_id ) {
    $CI = &get_instance();
    $CI->load->model('Company_model', 'company_model');
    $data = $CI->company_model->get_company( $company_id );
    return getArrayStringValue("company_name", $data);
}

function ArchiveCompanyFeatures($company_id, $user_id)
{
    $CI = &get_instance();

    $data = array();
    $features = $CI->Feature_model->get_company_features($company_id);
    foreach($features as $feature)
    {
        $companyparent_id = GetCompanyParentId($company_id);

        $code = GetArrayStringValue("Code", $feature);
        $enabled = GetArrayStringValue("Enabled", $feature);
        $child_flg = GetArrayStringValue("ChildFlg", $feature);

        // Check to see if the companyparent is overriding this company feature.
        $parent_override = false;
        if ( $child_flg === 't' )
        {
            if ($companyparent_id !== '' )
            {
                $parent_override = $CI->Feature_model->is_feature_enabled_for_companyparent($code, $companyparent_id);
                if ( $parent_override ) $enabled = true;
            }
        }

        $row = array();
        $row['FeatureCode'] = $code;
        $row['Enabled'] = $enabled;
        $row['AdditionalInfo'] = "";
        if ( $parent_override ) $row['AdditionalInfo'] = "Parent settings forced this feature to be enabled.";
        $data[] = $row;

    }

    // Organize our Snapshot Data
    ArchiveHistoricalData($company_id, 'company', "company_features", $data, array(), $user_id, 1);
}

/**
 * GetCompanyEncryptionKey
 *
 * This function will collect the stored AWS KMS encryption key from
 * the company table and decrypt it into the A2P Encryption Key.
 *
 * If at anytime this is not possible, an error will be thrown.
 *
 * @param $company_id
 * @return mixed
 * @throws Exception
 */
function GetCompanyEncryptionKey($company_id )
{
    $CI = &get_instance();
    $CI->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));

    $alias_name = "alias/" . APP_NAME . "/company_" . $company_id;
    $cache_name = "crypto_{$alias_name}";

    if ( APP_NAME === 'a2p-prodcopy')
    {
        $alias_name = "alias/a2p-prod/company_" . $company_id;
        $cache_name = "crypto_{$alias_name}";
    }

    $encryption_key = $CI->cache->get($cache_name);
    if ( GetStringValue($encryption_key) === 'FALSE' )
    {
        // No.  You can't create an encryption key for a company that does not exist.
        $company = $CI->Company_model->get_company($company_id);
        if ( empty($company) ) throw new Exception("Could not locate the company.");

        // Fail if we can't find the company encryption key.
        $encryption_key = $CI->Company_model->select_company_encryption_key($company_id);
        if ( $encryption_key === '' ) throw new Exception("Company has not been assigned an encryption key yet.");

        // Fail if the key does not appear to be an AWS KMS key.
        if ( ! StartsWith($encryption_key, "{a2p-aws-kms}:" ) ) throw new Exception("Company encryption key is malformed.");

        // Decrypt the encryption_key using the CMK.
        $encryption_key = KMSDecrypt($alias_name, $encryption_key);

        // Store the key into the cache.
        $CI->cache->save($cache_name, $encryption_key, 300);
    }
    return $encryption_key;

}


/**
 * CreateCompanyEncryptionKey
 *
 * Create an A2P Encryption Key for this customer.
 * This will throw if we can't do it for any reason.
 *
 * @param $company_id
 * @throws Exception
 */
function CreateCompanyEncryptionKey($company_id )
{
    $CI = &get_instance();

    // No.  You can't create an encryption key for a company that does not exist.
    $company = $CI->Company_model->get_company($company_id);
    if ( empty($company) ) throw new Exception("Unable to find company specified.");

    // No no.  You can't create a new encryption key if you already have one.
    $encryption_key = $CI->Company_model->select_company_encryption_key($company_id);
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

    // Create a new ALIAS that ties the CMK to the company, then remove the alias that tied it to the pool
    $kms = KMS();
    $alias_name = "alias/" . APP_NAME . "/company_" . $company_id;
    $alias_description = APP_NAME . ": Company ( {$company_id} )";
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
    $CI->Company_model->update_company_encryption_key($company_id, $encryption_key);

    // Remove the key from the pool
    $CI->Support_model->delete_keypool_by_id($key_id);

    // Create a new key to replace the one we just used.
    $CI->Queue_model->add_worker_job(GetCompanyParentId($company_id), $company_id, GetSessionValue('user_id'), "GenerateSecurityKey", "index");

    // Audit this transaction.
    $payload = array();
    $payload['AlaisName'] = $alias_name;
    $payload['AliasDescription'] = $alias_description;
    $payload['KeyId'] = $cmk_id;
    $payload['CompanyId'] = $company_id;
    $payload['CompanyName'] = GetArrayStringValue('company_name', $company);
    AuditIt("Created customer master key.", $payload, GetSessionValue('user_id'), GetSessionValue('customer_id'));
}
function ActivateColumnNormalizationRegExFeature( $company_id, $column_code )
{
    $CI = &get_instance();

    $feature = $CI->Feature_model->get_company_feature($company_id, 'COLUMN_NORMALIZATION_REGEX', 'mapping_column', $column_code);
    if ( empty($feature) ) return;

    GetArrayStringValue("Enabled", $feature) === 't' ? $enabled = true : $enabled = false;
    GetArrayStringValue("ChildFlg", $feature) === 't' ? $child_flg = true : $child_flg = false;

    $enabled = GetArrayStringValue("Enabled", $feature);
    if ( $enabled === "t" )
    {
        EnableColumnNormalizationRegExFeature($company_id, $column_code);
    }
    else
    {
        DisableColumnNormalizationRegExFeature($company_id, $column_code);
    }
}
function DisableColumnNormalizationRegExFeature($company_id, $column_code)
{
    $CI = &get_instance();

    $feature = $CI->Feature_model->get_company_feature($company_id, 'COLUMN_NORMALIZATION_REGEX', 'mapping_column', $column_code);
    if ( empty($feature) ) return;

    $target = GetArrayStringValue('Target', $feature);
    $CI->Company_model->disable_custom_normalization($company_id, $target);
}
function EnableColumnNormalizationRegExFeature($company_id, $column_code)
{
    $CI = &get_instance();

    $feature = $CI->Feature_model->get_company_feature($company_id, 'COLUMN_NORMALIZATION_REGEX', 'mapping_column', $column_code);
    if ( empty($feature) ) return;

    $target_type = GetArrayStringValue('TargetType', $feature);
    $target = GetArrayStringValue('Target', $feature);
    GetArrayStringValue("ChildFlg", $feature) === 't' ? $child_flg = true : $child_flg = false;
    $companyparent_id = GetCompanyParentId($company_id);

    if ( $child_flg && $companyparent_id !== '' )
    {
        // If this feature supports "Child" relationships AND the company has an associated
        // companyparent_id, pull the configuration details for the feature from the
        // parent, not the company.
        $pref = $CI->CompanyParent_model->get_companyparent_preference( $companyparent_id, "column_normalization", "{$target_type}_{$target}_pattern" );
        $pattern = GetArrayStringValue("value", $pref);

        $pref = $CI->CompanyParent_model->get_companyparent_preference( $companyparent_id, "column_normalization", "{$target_type}_{$target}_replace" );
        $replace = GetArrayStringValue("value", $pref);

        $pref = $CI->CompanyParent_model->get_companyparent_preference( $companyparent_id, "column_normalization", "{$target_type}_{$target}_description" );
        $description = GetArrayStringValue("value", $pref);
    }
    else
    {
        // This does not not appear to be a feature that is related to a companyparent.
        // Use the feature data stored on the company.
        $pref = $CI->Company_model->get_company_preference( $company_id, "column_normalization", "{$target_type}_{$target}_pattern" );
        $pattern = GetArrayStringValue("value", $pref);

        $pref = $CI->Company_model->get_company_preference( $company_id, "column_normalization", "{$target_type}_{$target}_replace" );
        $replace = GetArrayStringValue("value", $pref);

        $pref = $CI->Company_model->get_company_preference( $company_id, "column_normalization", "{$target_type}_{$target}_description" );
        $description = GetArrayStringValue("value", $pref);
    }


    // Wait!  If the pattern is the empty string, then don't enable this.
    // Just disable it.  No patter means disabled, but allows us to keep the
    // other attribues as prefrences.
    if ( $pattern === '' )
    {
        DisableColumnNormalizationRegExFeature($company_id, $feature);
        return;
    }

    $rule = array();
    $rule['pattern'] = $pattern;
    $rule['replace'] = $replace;
    $rule['description'] = $description;

    $rules = array();
    $rules[] = $rule;

    $CI->Company_model->enable_custom_normalization($company_id, $target, $rules);

}

/* End of file companies_helper.php */
/* Location: ./application/helpers/companies_helper.php */
