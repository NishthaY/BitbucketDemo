<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );

class RemoveLostKeys extends Tool
{

    protected $confirm_action;

    public function __construct()
    {
        parent::__construct();
        $this->confirm_action = true;
    }

    public function report($wait_period_in_days=7)
    {
        $this->confirm_action = false;
        print "+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-\n";
        print "REPORT\n";
        print "No keys are being removed.  This is just a report of what would \n";
        print "have happened if this was executed with the remove function.\n";
        print "If there are no keys listed below this message, then no keys would\n";
        print "have been scheduled for removal.\n";
        print "+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-\n";
        $this->remove($wait_period_in_days);
    }
    public function remove($wait_period_in_days=7)
    {
        if ( APP_NAME === 'a2p-prodcopy' )
        {
            print "Security key rotation not allowed in PRODCOPY.\n";
            print "No keys have been rotated.\n";
            return;
        }

        $this->_confirm_and_warn();


        $keys = KMSGetAliases();
        foreach($keys as $key)
        {
            // Pull out the key alias and id.
            $alias_name = getArrayStringValue('AliasName', $key);
            $alias_key_id = GetArrayStringValue("TargetKeyId", $key);

            try
            {
                // Only look at aliases that belong to this application.
                if ( strpos($alias_name, APP_NAME) !== FALSE )
                {
                    // TYPE_CODE
                    // What type is this key, company or parent?
                    $type = fRightBack($alias_name, "/");
                    $type_code = "UNKNOWN";
                    if ( strpos($type, 'company_') !== FALSE ) $type_code = "COMPANY";
                    else if ( strpos($type, 'companyparent_') !== FALSE ) $type_code = "COMPANYPARENT";

                    // COMPANY
                    // Foreach company, look it up by ID.  If it no longer exists in our
                    // database, then we need to remove the key.
                    if ( $type_code === 'COMPANY' )
                    {
                        $company_id = fRightBack($type, "_" );
                        $company = $this->Company_model->get_company($company_id);
                        if ( empty($company) )
                        {
                            print "Scheduling Alias for Deletion: days[{$wait_period_in_days}] company_id[{$company_id}] key[{$alias_name}] key_id[{$alias_key_id}]\n";
                            if ( $this->confirm_action ) KMSScheduleAliasForDeletion($alias_name, $wait_period_in_days);
                        }
                    }

                    // PARENT
                    // Foreach parent, look it up by ID.  If it no longer exists in our
                    // database, then we need to remove the key.
                    if ( $type_code === 'COMPANYPARENT' )
                    {
                        $companyparent_id = fRightBack($type, "_" );
                        $parent = $this->CompanyParent_model->get_companyparent($companyparent_id);
                        if ( empty($parent) )
                        {
                            print "Removing lost key. companyparent_id[{$company_id}] key[{$alias_name}] key_id[{$alias_key_id}]\n";
                            if ( $this->confirm_action ) KMSScheduleAliasForDeletion($alias_name, $wait_period_in_days);
                        }
                    }
                }
            }
            catch(Exception $e)
            {
                // If a key is already pending deletion or disabled, you
                // will land here.  Ignore failures.
            }
        }
    }

    private function _confirm_and_warn()
    {

        system("clear");
        print "\n";
        print "WARNING: Please pay attention.\n";
        print "\n";
        print "You are about to search AWS for KMS keys that are no longer attached\n";
        print "to a company or company parent.  These keys will be scheduled for \n";
        print "removal.  You may at any time go into KMS and cancel the key removal\n";
        print "as long as you do it before the wait period expires.\n";
        print "\n";
        print "If you would like to see what keys will be scheduled for removal BEFORE\n";
        print "you run this operation, hit ctrl-c to cancel this tool and run it again\n";
        print "but this time so it will 'report' the keys targeted.\n";
        print "\n";

        $app_name = APP_NAME;
        $input = readline("Type the application name {$app_name} to proceed: ");
        if ( $input === $app_name )
        {
            print "proceeding!\n";
        }
        else
        {
            print "Operation cancelled.\n";
            exit;
        }
    }
}

/* End of file RemoveLostKeys.php */
/* Location: ./application/controllers/tools/RemoveLostKeys.php */
