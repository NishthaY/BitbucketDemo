<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );

class CreateApplicationEncryptionKey extends Tool
{
    public function create( $app_name )
    {
        try
        {
            if ( GetStringValue($app_name) === '' ) throw new Exception("Missing required input app_name.");

            // Here are some known environments that already exist.  Those would not be valid names.
            $invalid = ['a2p-dev', 'a2p-uat', 'a2p-qa', 'a2p-sandbox', 'a2p-prodcopy', 'a2p-demo', 'a2p-prod'];
            if ( in_array(strtolower($app_name), $invalid) ) throw new Exception("That app name is already in use.");

            $encrypted_key = A2PCreateEncryptionKey_APP($app_name);
            $encrypted_key = "money for nothing.";
            print "A2P_ENCRYPTION_KEY for {$app_name}.\n";
            print $encrypted_key;
            print "\n";
        }catch(Exception $e)
        {
            print "Exception! " . $e->getMessage() . "\n";
        }
    }
}

/* End of file CreateAppEncryptionKey.php */
/* Location: ./application/controllers/cli/CreateAppEncryptionKey.php */
