<?php

/**
 * IsEncryptedString
 *
 * Returns TRUE if the input string appears to be something we
 * could decrypt.
 *
 * @param $str
 * @return bool
 */
function IsEncryptedString($str)
{
    if ( StartsWith($str, "{aes-256-cbc}:") ) return TRUE;
    return FALSE;
}

/**
 * IsEncryptedStringComment
 *
 * Returns TRUE if the input string appears to be an internal comment
 * that has been added to an encrypted file.
 *
 * @param $str
 * @return bool
 */
function IsEncryptedStringComment($str )
{
    if ( StartsWith($str, "{a2p-comment}:") ) return TRUE;
    return FALSE;
}

/**
 * A2PEncryptString
 *
 * Take a string and encrypt it.
 *
 * allow_identical ( true) If you run this function twice with the same input
 * then the resulting encoded data will be the same both times.
 *
 * allow_identical ( false ) If you run this function twice with the same input
 * then the resulting encoded data will be different each time but yet still
 * decode to the initial input.
 *
 * @param $str
 * @param $encryption_key
 * @param bool $allow_identical
 * @return string
 * @throws Exception
 */
function A2PEncryptString($str, $encryption_key, $allow_identical=false )
{

    if ( getStringValue($str) == "" || $encryption_key === EMPTY_ENCRYPTION_KEY ) {
        unset($str);
        unset($encryption_key);
        unset($allow_identical);
        return "";
    }

    // Already encrypted?
    if ( strpos($str, "aes-256-cbc") !== FALSE ) {
        // Woah!  You were already handed an encrypted string.  Just
        // spit it back out.
        unset($encryption_key);
        unset($allow_identical);
        return $str;
    }

    $encryption_key = hex2bin($encryption_key);
    $iv = null;

    // Initialization Vector.
    if ( $allow_identical )
    {
        // Not using an iv is not best practice.  However, sometimes we need
        // the encrypted data to be the same for the same input for business
        // reasons.  Not using an iv will do this.  However, PHP will issue
        // a warning if you try.  Use ob_start and ob_clean to suppress this
        // warngin.
        ob_start();
        $encrypted = openssl_encrypt($str, "aes-256-cbc", $encryption_key, 0);
        ob_end_clean();
        unset($encryption_key);
        unset($str);
        unset($allow_identical);
        return '{aes-256-cbc}:' . $encrypted . ":";
    }
    else
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length("aes-256-cbc"));
        $encrypted = openssl_encrypt($str, "aes-256-cbc", $encryption_key, 0, $iv);
        $iv = bin2hex($iv); // Turn the iv into a string.
        $encrypted = getStringValue('{aes-256-cbc}:' . $encrypted . ":" . $iv);
        unset($encryption_key);
        unset($str);
        unset($iv);
        unset($allow_identical);
        return $encrypted;
    }

    unset($encryption_key);
    unset($iv);
    unset($str);
    unset($allow_identical);

    throw new Exception("Unable to encrypt your string.");

}

/**
 * A2PDecryptString
 *
 * Take a string and decrypt it.
 *
 * If this encrypted string does not appear to be an a2p encrypted string
 * then this function will just return the string it was given.
 *
 * @param $encrypted_string
 * @param $encryption_key
 * @return string
 */
function A2PDecryptString( $encrypted_string, $encryption_key )
{

    // If the encryption_key is our literal that means no key, just return the encrypted string.
    if ( $encryption_key === EMPTY_ENCRYPTION_KEY ) return $encrypted_string;

    $encryption_key = hex2bin($encryption_key);

    if ( ! IsEncryptedString($encrypted_string) ) return $encrypted_string;

    $method = fBetween(fLeft($encrypted_string, ":"), "{", "}");
    $iv = fRightBack($encrypted_string, ":");
    $encrypted    = fLeftBack(fRight($encrypted_string, ":"), ":");
    switch ( $method )
    {
        case "aes-256-cbc":
            if ( getStringValue($iv) == "" ) $decrypted = openssl_decrypt($encrypted, $method, $encryption_key, 0);
            if ( getStringValue($iv) != "" )
            {
                $iv = hex2bin($iv); // turn the iv into binary data.
                $decrypted = openssl_decrypt($encrypted, $method, $encryption_key, 0, $iv);
            }
            break;
        default:
            return $encrypted_string;
            break;
    }
    return getStringValue($decrypted);

}

/**
 * A2PDecryptArray
 *
 * Investigate the array passed in looking for a2p encrypted strings.
 * Return same array structure, but with the following differences.
 *
 * - Any encrypted values will be decrypted.
 * - An additional node in the array will be added for each encrypted
 *   value uncovered.  The key for that value will be the original key
 *   with "Encrypted" prepended to the front of it.
 *
 * Example:
 * [ "FirstName" => "<encrypted value>" ]
 * becomes
 * [ "FirstName" => "Brian", "EncryptedFirstName" => "<encrypted value>" ]
 *
 * This is a recursive function so this function will work on an array of arrays
 * as well.
 *
 * [ [ "FirstName" => "<encrypted value>" ], [ "FirstName" => "<encrypted value>" ] ]
 * becomes
 * [
 *   [ "FirstName" => "Brian", "EncryptedFirstName" => "<encrypted value>" ],
 *   [ "FirstName" => "Jason", "EncryptedFirstName" => "<encrypted value>" ]
 * ]
 *
 * @param $array
 * @param $encryption_key
 * @return array
 */
function A2PDecryptArray($array, $encryption_key)
{
    if ( empty($array) ) return array();
    if ( isset($array['0']) && is_array($array["0"]) )
    {
        // You have a collection of key/value pair arrays.
        $rows = array();
        foreach($array as $row)
        {
            $rows[] = A2PDecryptArray($row, $encryption_key);
        }
        return $rows;
    }
    else
    {
        // You have a key/value pair array.
        $output = array();
        foreach($array as $key=>$value)
        {
            $output[$key] = $value;
            if ( ! is_array($value) && IsEncryptedString($value) )
            {
                $encrypted_value = $value;
                $value = A2PDecryptString($value, $encryption_key);
                $output[$key] = $value;
                $output["Encrypted{$key}"] = $encrypted_value;
            }
        }
        return $output;
    }
}

/**
 * A2PCreateEncryptionKey
 * Generates a pseudo random key and turns it into a string.
 *
 * @return string
 */
function A2PCreateEncryptionKey() {
    $encryption_key = openssl_random_pseudo_bytes(32);	    // Encryption Key ( binary )
    $hex = bin2hex($encryption_key);								// Encryption Key ( text )
    return $hex;
}

/**
 * A2PHashClearText
 *
 * This function will, given a string of text, hash that string
 * into a cryptographically secure one way hash.  We will provide
 * SALT to prevent it from being hacked by lookup tables.
 *
 * To verify clear text matches a given hash, use the php password_verify
 * function.  That function knows how to identify the salted hash
 * and do the verification.
 *
 * Will return the empty string if we can't create the SALT.
 *
 * @param $input
 * @return string
 */
function A2PHashClearText($input )
{
    $options = [
        'cost' => 10
    ];
    $hash = password_hash($input, PASSWORD_BCRYPT, $options);
    return $hash;
}


/**
 * A2PCreateEncryptionKey_APP
 *
 * This function will create the KMS secured A2P Encryption Key
 * for a given level in AWS.  It will also output the keys so you
 * can save them off to their appropriate locations.
 *
 * Use this to setup security for a new application manually.
 *
 * @param $app_name
 * @param string $encryption_key
 * @return string
 * @throws Exception
 */
function A2PCreateEncryptionKey_APP($app_name, $encryption_key="")
{
    // If we do not have an encryption key, generate one.
    if ( getStringValue($encryption_key) === '' ) $encryption_key = A2PCreateEncryptionKey();

    // If we do not already have a CMS for this application, make one.
    $alias_name = "alias/{$app_name}";
    $key = KMSGetAlias($alias_name);
    if ( empty($key) ) $key = KMSCreateKey($alias_name, "{$app_name}: Application Key");

    // Create the secured encryption key using the CMS key for the application.
    $kms_encryption_key = KMSEncrypt($alias_name, $encryption_key);
    if ( is_cli() ) return $kms_encryption_key;

    pprint_r($app_name);
    pprint_r($alias_name);
    pprint_r("EncryptionKey: [{$encryption_key}]");
    pprint_R("SecuredEncryptionKey: [{$kms_encryption_key}]");

}


/**
 * A2PGetEncryptionKey
 *
 * Return the decoded global application encryption key for this installation
 * of the application.
 *
 * @return mixed
 * @throws Exception
 */
function A2PGetEncryptionKey()
{
    $CI =& get_instance();
    $CI->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));

    // The APP_NAME is always the value set in the ENV, unless you are
    // prodcopy.  In that case, on a GET, you get the prod key.
    $app_name = APP_NAME;
    if ( APP_NAME === 'a2p-prodcopy' ) $app_name = 'a2p-prod';

    $encryption_key = EMPTY_ENCRYPTION_KEY;
    try
    {
        $alias_name = "alias/".$app_name;
        $cache_name = "crypto_{$alias_name}";

        $encryption_key = $CI->cache->get($cache_name);
        if ( GetStringValue($encryption_key) === 'FALSE' )
        {
            // Get the key from AWS.
            $key = KMSGetAlias($alias_name);
            if ( empty($key) ) throw new Exception("Unable to find Master Key for application.");
            $encryption_key = KMSDecrypt($alias_name, A2P_ENCRYPTION_KEY);
            $CI->cache->save($cache_name, $encryption_key, 300);
        }


    }catch(Exception $e)
    {
        // RETRY!
        // If we don't get it.  Try one more time.
        sleep(1);
        $key = KMSGetAlias($alias_name);
        if ( empty($key) ) throw new Exception("Unable to find Master Key for application on retry.");
        $encryption_key = KMSDecrypt($alias_name, A2P_ENCRYPTION_KEY);
    }
    return $encryption_key;


}

/**
 * GetEncryptionKey
 *
 * Get the encryption key specifically for the identifier specified.
 * Identifier Types: company, companyparent, a2p
 *
 * @param $identifier
 * @param $identifier_type
 * @return mixed
 * @throws Exception
 */
function GetEncryptionKey($identifier, $identifier_type)
{
    if ( $identifier_type === 'company' ) return GetCompanyEncryptionKey($identifier);
    if ( $identifier_type === 'companyparent' ) return GetCompanyParentEncryptionKey($identifier);
    if ( $identifier_type === 'a2p' ) return A2PGetEncryptionKey();
    throw new Exception("Unable to find encryption key for specified type.");
}

/**
 * A2PGetEncryptedFileComment
 *
 * A2P encrypted files might have comments at the top of the file.  This function
 * will look for the comment with the matching tag.  If found, the value is returned.
 * If not found, the empty string is returned.  This function will return FALSE if
 * there was a problem, such as reading the file.
 *
 * @param $prefix
 * @param $filename
 * @param $tag
 * @return bool|string
 */
function A2PGetEncryptedFileComment( $prefix, $filename, $tag )
{
    $fh 	    = null;
    $comment    = "";
    try
    {
        // Grab the file we are going to parse.
        S3GetClient();
        $filename = "s3://" . S3_BUCKET . "/{$prefix}/{$filename}";

        // If the file does not exist, we will need to do something.
        if ( ! file_exists($filename) ) return FALSE;

        try {
            $fh = fopen($filename, "r");
            if ($fh) {
                while (($line = fgets($fh)) !== false)
                {
                    if ( IsEncryptedStringComment($line) )
                    {
                        $this_tag = fBetween($line, ":", "[");
                        if ( strtoupper($tag) === strtoupper($this_tag) )
                        {
                            return fLeftBack(fRight($line, "["), "]");
                        }
                    }
                    else
                    {
                        // All comments are at the top of the file.  Once we see something
                        // that is not a comment, we can break out and stop.  There will be
                        // no more comments to find.
                        break;
                    }
                }
                fclose($fh);
            }
        }catch(Exception $e) {
            if ( is_resource($fh) ) fclose($fh);
        }
    }
    catch( Exception $e )
    {
       if ( is_resource($fh) ) fclose($fh);
       return FALSE;
    }
    return $comment;

}