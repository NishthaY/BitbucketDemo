<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );

class DeveloperSetup extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * aws
     *
     * Create an AWS bucket for the application instance being created.
     *
     * @param $username
     * @throws Exception
     */
    public function aws($username)
    {
        $this->_validateUsername($username);

        try
        {
            // Create an AWS Bucket for this application, if one does not already exist.
            S3GetClient();
            $url = "s3://" . S3_BUCKET;
            if ( ! file_exists($url) ) {
                mkdir($url);

                print "\n";
                print "ACTION ITEMS:\n";
                print "The a2p-{$username} bucket was just created is open to the public!\n";
                print "The AWS administrator needs to apply the a2p-dev CORS file to the new \n";
                print "bucket AND remove the 'List' ACL from the 'Everyone' group.\n";
                print "\n";
            }
            else
            {
                print "ok. AWS bucket for a2p-{$username} exists.\n";
            }
        }catch(Exception $e)
        {
            print "ERROR: " . $e->getMessage();
        }
    }

    /**
     * encryption_key
     *
     * Generate a fresh master key for the new application instance.
     *
     * @param $username
     * @param string $slug
     * @throws Exception
     */
    public function encryption_key( $username, $slug='' )
    {
        $this->_validateUsername($username);

        try
        {
            // A2P_ENCRYPTION_KEY
            // Create and encrypt an A2P_ENCRYPTION_KEY if needed.
            $kms_encryption_key = A2PCreateEncryptionKey_APP("a2p-{$username}", $slug);
            print "\n";
            print "ACTION ITEMS:\n";
            print "Store the values below in a secure location!\n";
            print "Update your personal .env file so that the A2P_ENCRYPTION_KEY value\n";
            print "matches the 'Encrypted Encryption/Decryption Key' shown below.\n";
            print "\n";
            print "Encryption/Decryption Key:\n";
            print KMSDecrypt("alias/a2p-{$username}", $kms_encryption_key, true) . "\n";
            print "\n";
            print "Encrypted Encryption/Decryption Key:\n";
            print $kms_encryption_key . "\n";
            print "\n";
            print "\n";
            print "\n";



        }catch(Exception $e)
        {
            print "ERROR: " . $e->getMessage();
        }
    }

    /**
     * user
     *
     * Create the initial power user for the new application instance.
     *
     * @param $username
     * @throws Exception
     */
    public function user($username)
    {
        $this->_validateUsername($username);
        try
        {
            $email_address  = $this->_getInput("Email Address");
            $first_name     = $this->_getInput("First Name");
            $last_name      = $this->_getInput("Last Name");

            // Validate the user does not already exist.
            $new_user = $this->User_model->get_user( $email_address );
            if ( ! empty($new_user) ) throw new Exception("Abort: The user [{$email_address}] already exists.");

            // Create the new user.
            $token = GenerateWeakPassword();
            $this->User_model->create_user( $email_address, $first_name, $last_name, $token );
            $new_user = $this->User_model->get_user( $email_address );
            if ( empty($new_user) ) throw new Exception("Abort: Unable to create user [{$email_address}].");
            $new_user_id = getArrayStringValue("user_id", $new_user);

            // Link user to company.
            if ( ! $this->User_model->is_user_linked_to_company( $new_user_id, A2P_COMPANY_ID ) )
            {
                $this->User_model->link_user_to_company( $new_user_id, A2P_COMPANY_ID );
            }

            // Grant the user permissions.
            $this->User_model->grant_user_acl($new_user_id, "All");

            // Enable the user.
            $this->User_model->enable_user( $new_user_id );

            print "\n";
            print "ACTION ITEMS:\n";
            print "The user [{$email_address}] has been created!\n";
            print "Below you will find your initial password.  When ready, log into\n";
            print "https://dev.advice2pay.com using the password below.  You will be asked\n";
            print "to change it the first time you log in.\n";
            print "\n";
            print "Initial Password\n";
            print "{$token}\n";
            print "\n";
            print "\n";
            print "\n";
        }
        catch(Exception $e)
        {
            print "ERROR: " . $e->getMessage();
        }
    }

    /**
     * _validateUsername
     *
     * This function will review the username passed to make sure someone is not trying to
     * make a developer instance that matches one of our reserved release levels.
     *
     * Furthermore, there will be some minor validation to ensure the environment variables
     * match what we are being asked to do.
     *
     * @param $username
     * @throws Exception
     */
    private function _validateUsername($username)
    {
        // Do not allow anyone to try and setup a developer instance with a reserved application name.
        if ( strtoupper($username) === 'DEV' )      throw new Exception("The a2p-dev application name is reserved.");
        if ( strtoupper($username) === 'DEMO' )     throw new Exception("The a2p-demo application name is reserved.");
        if ( strtoupper($username) === 'UAT' )      throw new Exception("The a2p-uat application name is reserved.");
        if ( strtoupper($username) === 'SANDBOX' )  throw new Exception("The a2p-sandbox application name is reserved.");
        if ( strtoupper($username) === 'PRODCOPY' ) throw new Exception("The a2p-prodcopy application name is reserved.");
        if ( strtoupper($username) === 'PROD' )     throw new Exception("The a2p-prod application name is reserved.");

        // Make sure they are setting up a key for the application they are trying to build.
        if ( "a2p-{$username}" !== APP_NAME ) throw new Exception("Abort. Environment variable APP_NAME and a2p-{$username} do not match.");
        if ( "a2p-{$username}" !== S3_BUCKET ) throw new Exception("Abort. Environment variable S3_BUCKET and a2p-{$username} do not match.");
    }

    /**
     * _getInput
     *
     * Little helper function that we can use to ask the user for information.
     *
     * @param $message
     * @return string
     */
    private function _getInput($message)
    {
        $input = "";
        while ($input === '')
        {
            $input = readline("{$message}: ");
            $input = trim($input);
        }
        return $input;
    }
}

/* End of file DeveloperSetup.php */
/* Location: ./application/controllers/cli/DeveloperSetup.php */
