<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );

class PusherTool extends Tool
{

    public function __construct()
    {
        parent::__construct();
    }
    public function push( )
    {
        try
        {

            $user_id = GetArrayStringValue("user_id", $this->authenticated_user);
            if ( $user_id === '' )
            {
                print "You must be authenticated to use this tool.\n";
                exit;
            }

            system("clear");
            print "This tool will trigger a test alert message to all customers currently viewing\n";
            print "the dashboard for the selected company.\n";
            print "Press any key to continue or <ctrl-c> to quit.\n";
            readline("");

            system("clear");
            print "Processing ...\n";

            $company = $this->getCompany();
            $company_id = getArrayStringValue("company_id", $company);

            NotifyCompanyChannelUpdate($company_id, "dashboard_task", "PusherAlert");

            print "done.\n";
        }
        catch(Exception $e)
        {
            print "Exception! " . $e->getMessage() . "\n";
        }
    }
}

/* End of file PusherTool.php */
/* Location: ./application/controllers/cli/PusherTool.php */
