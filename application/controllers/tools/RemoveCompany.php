<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );

class RemoveCompany extends Tool
{

    protected $warn;

    public function __construct()
    {
        parent::__construct();
        $this->warn = true;
    }

    /**
     * remove
     *
     * This function will allow you to remove a company and all of it's
     * data from the A2P system.  This is an interactive function that
     * will allow you to select the company you want to remove.  You will
     * be required to authenticate, as well as confirm this action.
     *
     */
    public function remove()
    {
        try {

            $authenticated_user_id = getArrayStringValue("user_id", $this->authenticated_user);

            // What company are we working with.
            $company = $this->getCompany();
            if ( empty($company) )
            {
                print "Operation canceled.\n";
                exit;
            }
            $company_id = getArrayStringValue('company_id', $company);
            $this->_remove_company($company_id);

            print "done.\n";
        } catch (Exception $e) {
            print "Exception! " . $e->getMessage() . "\n";
        }
    }

    /**
     * remove_all
     *
     * This function will remove ALL customer data from the A2P
     * environment that is making the request.  You will be required
     * to authenticate as well as confirm this action.
     *
     * The intent of this function is to clean up all data on an
     * environment we are about to refresh with a data wipe.
     *
     */
    public function remove_all()
    {

        $app_name = APP_NAME;

        // CONFIRM THE CRAP OUT OF THIS
        if ( $this->warn )
        {
            system("clear");
            print "\n";
            print "WARNING: Please pay attention.\n";
            print "\n";
            print "You are about to destroy all company data found on {$app_name}.\n";
            print "This not only includes company data but associated users and all\n";
            print "archival data on S3 as well.\n";
            print "\n";
            print "Furthermore, this operation will issue a VACUUM FULL command.\n";
            print "This may take a long time and it could lock database tables \n";
            print "which in turn will impact the running application.\n";
            print "It's recommended, but not required, that you take the application\n";
            print "down for maintenance while you do this.\n";
            print "\n";

            $input = readline("Type the application name {$app_name} to proceed: ");
            if ( $input === $app_name )
            {
                $this->warn = false;
                print "proceeding!\n";
            }
            else
            {
                print "Operation cancelled.\n";
                exit;
            }
        }


        $companies = $this->Company_model->get_all_companies();
        uasort($companies, 'AssociativeArraySortFunction_company_name');
        foreach($companies as $company)
        {
            $selected_company_name = GetArrayStringValue("company_name", $company);
            $company = $this->Company_model->get_company_by_name($selected_company_name);
            $this->company = $company;
            $company_id = GetArrayStringValue('company_id', $this->company);

            print "Removing: " . GetArrayStringValue("company_name", $company) . "\n";
            $this->_remove_company($company_id);
        }


    }

    /**
     * _remove_company
     *
     * This function will remove the specified company from the A2P environment.
     * This includes not only database records, but also S3 files and IAM security
     * keys.
     *
     * @param $company_id
     */
    private function _remove_company($company_id)
    {
        try
        {
            $this->company = $this->Company_model->get_company($company_id);
            $authenticated_user_id = getArrayStringValue("user_id", $this->authenticated_user);

            if ( $company_id === A2P_COMPANY_ID )
            {
                print "Operation canceled.\n";
                exit;
            }
            $company_users = $this->User_model->get_all_users($company_id);

            // Make sure they understand what they are doing.
            $this->_confirm_and_warn($this->company, $company_users);

            print "Removal of company : " . GetArrayStringValue('company_name', $this->company) . " will begin in 5 minutes.\n";
            $this->Queue_model->add_worker_job('', $company_id, $authenticated_user_id, 'DeleteCompany','index', 'now + 5 minutes');

            print "done.\n";
        } catch (Exception $e) {
            print "Exception! " . $e->getMessage() . "\n";
        }
    }

    /**
     * _confirm_and_warn
     *
     * This function will ask the user if they really want to remove the
     * company in question and will outline the company and users that
     * will be impacted.
     *
     * @param $company
     * @param $company_users
     */
    private function _confirm_and_warn($company, $company_users)
    {
        // If we have already warned the user, don't do it again.
        if ( ! $this->warn ) return;

        // CONFIRM THE CRAP OUT OF THIS
        system("clear");
        print "\n";
        print "WARNING: Please pay attention.\n";
        print "\n";
        print "You are about to destroy all data related to the company\n";
        print "you have selected.  This not only includes company data but\n";
        print "associated users and all archival data on S3 as well.\n";
        print "\n";
        print "Company: ".getArrayStringValue("company_name", $company)."\n";
        foreach($company_users as $user)
        {
            $first = getArrayStringValue("FirstName", $user);
            $last = getArrayStringValue("LastName", $user);
            $email = getArrayStringValue("EmailAddress", $user);
            print "User: {$first} {$last} ( {$email} )\n";
        }
        print "\n";
        print "Furthermore, this operation will issue a VACUUM FULL command.\n";
        print "This may take a long time and it could lock database tables \n";
        print "which in turn will impact the running application.\n";
        print "It's recommended, but not required, that you take the application\n";
        print "down for maintenance while you do this.\n";
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

/* End of file RemoveCompany.php */
/* Location: ./application/controllers/cli/RemoveCompany.php */
