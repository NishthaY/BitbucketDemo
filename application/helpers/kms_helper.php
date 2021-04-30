<?php
require('vendor/autoload.php');  // Include AWS into application.
use Aws\Kms\KmsClient;

function KMSDebug() { return false; }
function KMSConfig()
{
    return array(
        'region' => GetConfigValue("aws_region"),
        "version" => GetConfigValue("aws_kms_php_version"),
        'credentials' => array(
            'key'    => GetConfigValue("kms_key"),
            'secret' => GetConfigValue("kms_secret")
        ),
    );
}
function KMS()
{
    $config = KMSConfig();
    return KmsClient::factory($config);
}

/**
 * IsKMSEncryptedString
 *
 * Returns TRUE if the input string appears to be an A2P KMS encrypted string.
 * @param $str
 * @return bool
 */
function IsKMSEncryptedString($str)
{
    if ( StartsWith($str, "{a2p-aws-kms}:") ) return TRUE;
    return FALSE;
}

/**
 * KMSGetAlias
 *
 * This function will return the Customer Master Key ( CMS ) by the
 * specified alias.  If it's not found, the empty array will be returned.
 *
 * Throws on error trying to look for the key.
 * @param $search
 * @param bool $retry
 * @return array|mixed
 * @throws Exception
 */
function KMSGetAlias($search, $retry=false)
{
    try
    {
        $aliases = KMSGetAliases();
        foreach($aliases as $alias)
        {
            $name = GetArrayStringValue("AliasName", $alias);
            if ( strtoupper($name) === strtoupper($search) )
            {
                return $alias;
            }
        }
    }
    catch(Exception $e)
    {
        if ( ! $retry )
        {
            sleep(1);
            return KMSGetAlias($search, true);
        }
        else
        {
            if ( KMSDebug() ) pprint_r($e->getMessage());

            $payload = array();
            $payload['search'] = GetStringValue($search);
            $payload['error'] = $e->getMessage();
            LogIt('ERROR', __FUNCTION__, $payload);

            throw new Exception("Unable to look for alias at this time.");
        }
    }

    return array();
}

/**
 * KMSGetAliases
 *
 * Get all known Customer Master Keys.
 *
 * @return array
 * @throws Exception
 */
function KMSGetAliases($retry=false)
{
    $retval = array();
    try
    {
        $kms = KMS();

        $limit = 100;
        $marker = "";
        $truncated = TRUE;

        while ($truncated)
        {
            $payload = array();
            $payload['Limit'] = $limit;
            if ( $marker !== '' ) $payload['Marker'] = $marker;
            $result = $kms->listAliases($payload);

            $aliases = $result->get("Aliases");
            $truncated = $result->get("Truncated");
            $marker = $result->get("NextMarker");

            foreach($aliases as $alias)
            {
                $retval[] = $alias;
            }
        }

    }
    catch(Exception $e)
    {
        if (! $retry)
        {
            sleep(1);
            return KMSGetAlias(true);
        }
        else
        {
            if ( KMSDebug() ) pprint_r($e->getMessage());

            $payload = array();
            $payload['error'] = $e->getMessage();
            LogIt('ERROR', __FUNCTION__, $payload);

            throw new Exception("Unable to collect aliases at this time.");
        }
    }
    return $retval;
}

/**
 * KMSScheduleAliasForDeletion
 *
 * Looks for the Customer Master Key by alias name.  If found it will
 * schedule the key for deletion in X days.
 *
 * Days may be any number between 7 and 30.
 *
 * @param $alias_name
 * @param int $days
 * @throws Exception
 */
function KMSScheduleAliasForDeletion($alias_name, $days=30 )
{
    try
    {
        if ( APP_NAME === 'a2p-prodcopy' ) throw new Exception("You are not allowed to schedule keys for deletion on prodcopy");

        $kms = KMS();

        // Find the key we are going to delete.
        $alias = KMSGetAlias($alias_name);
        if ( empty($alias) ) throw new Exception("Unable to find key to delete.");

        $days = GetIntValue(StripNonNumeric($days));
        if ( $days < 7 || $days > 30 ) throw new Exception("You may schedule a key for deletion between 7 and 30 days only.");

        // Mark the key for deletion.
        $ksm = KMS();
        $payload = array();
        $payload['KeyId'] = GetArrayStringValue('TargetKeyId', $alias);
        $payload['PendingWindowInDays'] = $days;
        $kms->scheduleKeyDeletion($payload);
    }
    catch(Exception $e)
    {
        if ( KMSDebug() ) pprint_r($e->getMessage());

        $payload = array();
        $payload['alias_name'] = GetStringValue($alias_name);
        $payload['error'] = $e->getMessage();
        LogIt('ERROR', __FUNCTION__, $payload);

        throw new Exception("Unable to schedule alias for deletion at this time.");
    }

}

/**
 * KMSRotateKey
 *
 * Look for the Customer Master Key by alias name.  If found, we will create a new
 * key.  The alias will then be pointed to the new key and the old key will get a
 * new alias rotated-<timestamp> prepended to the front.
 *
 * @param $alias_name
 * @param $description
 * @param string $usage
 * @return mixed
 * @throws Exception
 */
function KMSRotateKey($alias_name, $description, $usage='ENCRYPT_DECRYPT')
{
    try
    {
        if ( GetStringValue($alias_name) === '' ) throw new Exception("Missing required input alias_name");
        if ( ! StartsWith($alias_name, "alias/") ) throw new Exception("Alias names must start with 'alias/'");
        if ( GetStringValue($description) === '' ) $description = "Key created by " . APP_NAME . '.';
        if ( APP_NAME === 'a2p-prodcopy' ) throw new Exception("You are not allowed to rotate keys on prodcopy");

        $kms = KMS();

        // Find the key we are going to rotate.
        $alias = KMSGetAlias($alias_name);
        if ( empty($alias) ) throw new Exception("Unable to find key to rotate.");
        $original_cmk_id = GetArrayStringValue('TargetKeyId', $alias);

        // Create a brand new key.
        $payload = array();
        $payload['Description'] = $description;
        $payload['KeyUsage'] = $usage;
        $result = $kms->createKey($payload);
        $meta_data = $result->get("KeyMetadata");

        // Collect the Customer Master Key ID for the new key.
        $cmk_id = GetArrayStringValue("KeyId", $meta_data);
        if ( $cmk_id === '' ) throw new Exception("Unable to generate key.");

        // Update the alias from the old key to the new key.
        $payload = array();
        $payload['AliasName'] = $alias_name;
        $payload['TargetKeyId'] = $cmk_id;
        $kms->updateAlias($payload);

        // Create an alias that points to the old key that denotes it has been retired.
        $timestamp = date('YmdHis');
        $retired_alias_name = replaceFor($alias_name, "alias/", "alias/rotated-{$timestamp}/");
        $payload = array();
        $payload['AliasName'] = $retired_alias_name;
        $payload['TargetKeyId'] = $original_cmk_id;
        $kms->createAlias( $payload );

        // Return the name of the key we retired.
        return $retired_alias_name;
    }
    catch(Exception $e)
    {
        if ( KMSDebug() ) pprint_r($e->getMessage());

        $payload = array();
        $payload['alias_name'] = GetStringValue($alias_name);
        $payload['description'] = GetStringValue($description);
        $payload['usage'] = GetStringValue($usage);
        $payload['error'] = $e->getMessage();
        LogIt('ERROR', __FUNCTION__, $payload);

        throw new Exception("Unable to rotate key at this time.");
    }
}

/**
 * KMSCreateKey
 *
 * This function will create a new Customer Master Key that is allowed to
 * encrypt and decrypt data.  Once created an alias will be made and pointed to
 * the new key.
 *
 * @param $alias_name
 * @param $description
 * @param string $usage
 * @return array|mixed
 * @throws Exception
 */
function KMSCreateKey($alias_name, $description, $usage='ENCRYPT_DECRYPT' )
{
    try
    {
        if ( GetStringValue($alias_name) === '' ) throw new Exception("Missing required input alias_name");
        if ( GetStringValue($description) === '' ) $description = "Key created by " . APP_NAME . '.';
        if ( APP_NAME === 'a2p-prodcopy' ) throw new Exception("You are not allowed to create keys on prodcopy");

        // Check to see if we have a key associated with this alias
        // yet or not.  If we do, bail.
        $kms = KMS();
        $alias = KMSGetAlias($alias_name);
        if ( ! empty($alias) ) throw new Exception("Alias by the name [{$alias_name}] already exists.");

        // Create a new key.
        $payload = array();
        $payload['Description'] = $description;
        $payload['KeyUsage'] = $usage;
        $result = $kms->createKey($payload);
        $meta_data = $result->get("KeyMetadata");

        // Collect the Customer Master Key ID.
        $cmk_id = GetArrayStringValue("KeyId", $meta_data);
        if ( $cmk_id === '' ) throw new Exception("Unable to generate key.");

        // Create an alias that points to the new key.
        $payload = array();
        $payload['AliasName'] = $alias_name;
        $payload['TargetKeyId'] = $cmk_id;
        $kms->createAlias( $payload );

        return KMSGetAlias($alias_name);

    }catch(Exception $e)
    {
        if ( KMSDebug() ) pprint_r($e->getMessage());

        $payload = array();
        $payload['alias_name'] = GetStringValue($alias_name);
        $payload['description'] = GetStringValue($description);
        $payload['usage'] = GetStringValue($usage);
        $payload['error'] = $e->getMessage();
        LogIt('ERROR', __FUNCTION__, $payload);

        throw new Exception("Unable to create key at this time.");
    }

}

/**
 * KMSEncrypt
 *
 * Find the Customer Master Key for the specified alias.  Once found, encrypt
 * the text provided, then base64 encode it and add a tag to the front indicating
 * the changes we made to the encrypted string.
 *
 * @param $alias_name
 * @param $plain_text
 * @return string
 * @throws Exception
 */
function KMSEncrypt($alias_name, $plain_text, $retry=false)
{
    try
    {
        if ( GetStringValue($alias_name) === '' ) throw new Exception("Missing required input alias_name.");

        $alias = KMSGetAlias($alias_name);
        if ( empty($alias) ) throw new Exception("No alias by that name found.");

        $kms = KMS();
        $payload = array();
        $payload['KeyId'] = $alias_name;
        $payload['Plaintext'] = $plain_text;
        $result = $kms->encrypt( $payload );
        $blob = $result->get("CiphertextBlob");
        return "{a2p-aws-kms}:" . base64_encode($blob);

    }
    catch (Exception $e)
    {
        if ( ! $retry )
        {
            sleep(1);
            return KMSEncrypt($alias_name, $plain_text, true);
        }
        else
        {
            if (KMSDebug()) pprint_r($e->getMessage());

            $payload = array();
            $payload['alias_name'] = GetStringValue($alias_name);
            $payload['error'] = $e->getMessage();
            LogIt('ERROR', __FUNCTION__, $payload);

            throw new Exception("Unable to encrypt text.");
        }

    }
}

/**
 * KMSDecrypt
 *
 * This function will find the Customer Master key associated with the alias_name
 * and then use it to decrypt the cipher text.  This function will only decrypt
 * text that was encrypted with the KMSEncrypt function.
 *
 * @param $alias_name
 * @param $cipher_text
 * @return mixed
 * @throws Exception
 */
function KMSDecrypt($alias_name, $cipher_text, $retry=false)
{
    try
    {
        if ( GetStringValue($alias_name) === '' ) throw new Exception("Missing required input alias_name.");

        $alias = KMSGetAlias($alias_name);
        if ( empty($alias) ) throw new Exception("No alias by that name found.");

        // If this looks like an A2P AWS KMS encrypted string, fiddle with
        // it and turn it back into a KMS blob.
        if ( StartsWith($cipher_text, "{a2p-aws-kms}:") )
        {
            $cipher_text = FRight($cipher_text, "{a2p-aws-kms}:");
            $cipher_text = base64_decode($cipher_text);
        }

        $kms = KMS();
        $payload = array();
        $payload['KeyId'] = $alias_name;
        $payload['CiphertextBlob'] = $cipher_text;
        $result = $kms->decrypt( $payload );
        return $result->get("Plaintext");

    }
    catch (Exception $e)
    {
        if ( ! $retry )
        {
            sleep(1);
            return KMSDecrypt($alias_name, $cipher_text, true);
        }
        else
        {
            if (KMSDebug()) pprint_r($e->getMessage());

            $payload = array();
            $payload['alias_name'] = GetStringValue($alias_name);
            $payload['error'] = $e->getMessage();
            LogIt('ERROR', __FUNCTION__, $payload);

            throw new Exception("Unable to decrypt text.");
        }

    }
}

/* End of file kms_helper.php */
/* Location: ./application/helpers/kms_helper.php */
