<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );

// This class will remove all companies that are not hard coded
// into it's logic.  IE.  Keep A2P, Transamerica and all Transamerica children.

class RemoveCompanies extends Tool
{

    protected $a2p_company;
    protected $removing;
    protected $warn;

    public function __construct()
    {
        parent::__construct();
        $this->a2p_company = array();
        $this->removing = array();
        $this->warn = true;
    }


    public function execute()
    {
        try
        {
            print "starting\n";

            // ADVICE2PAY
            // Find the master company and store it on the class.  We will need to reference
            // this a few times just to make sure we never delete it.
            $this->a2p_company = $this->Company_model->get_company(A2P_COMPANY_ID);

            // WHO IS DOING THIS
            $authenticated_user_id = getArrayStringValue("user_id", $this->authenticated_user);



            // Do not delete these companies!
            $saved_companies = array('Advice2Pay');

            // Do not delete these company parents AND their children.
            $saved_companyparents = array( 'Transamerica' );

            // No, really.  Delete these companies even if they were excluded because we kept their parent.
            $delete_companies = array();



            // Find all company parent children and add them to the saved companies list if they are
            // not already in there.
            $companyparents = $this->CompanyParent_model->get_all_parents();
            foreach($companyparents as $companyparent)
            {
                $companyparent_name = GetArrayStringValue('Name', $companyparent);
                $companyparent_id = GetArrayStringValue('Id', $companyparent);
                if ( in_array($companyparent_name, $saved_companyparents) )
                {
                    $companies = $this->CompanyParent_model->get_companies_by_parent($companyparent_id);
                    foreach($companies as $company)
                    {
                        $company_name = GetArrayStringValue('company_name', $company);
                        if ( $company_name !== "" )
                        {
                            if ( ! in_array($company_name, $saved_companies) )
                            {
                                $saved_companies[] = $company_name;
                            }
                        }
                    }
                }
            }

            // Time to start removing.

            // Remove these companies.
            $companies = $this->Company_model->get_all_companies();
            foreach ($companies as $company)
            {
                $company_name = GetArrayStringValue('company_name', $company);
                $this_company_id = GetArrayStringValue('company_id', $company);
                if ( ! in_array($company_name, $saved_companies) )
                {
                    if ( $this->_is_allowed($company_name, 'company'))
                    {
                        $this->removing[] = ['identifier'=>$this_company_id, 'identifier_type' =>'company', 'identifier_name'=>$company_name];
                    }
                }
            }

            // Remove the "really do it" companies.
            foreach ($delete_companies as $company_name)
            {
                $company = $this->Company_model->get_company_by_name($company_name);
                $this_company_id = GetArrayStringValue('company_id', $company);
                if ( $this->_is_allowed($company_name, 'company'))
                {
                    $this->removing[] = ['identifier'=>$this_company_id, 'identifier_type' =>'company', 'identifier_name'=>$company_name];
                }
            }

            // Remove the parents.
            $companyparents = $this->CompanyParent_model->get_all_parents();
            foreach($companyparents as $companyparent) {
                $companyparent_name = GetArrayStringValue('Name', $companyparent);
                $this_companyparent_id = GetArrayStringValue('Id', $companyparent);
                if (!in_array($companyparent_name, $saved_companyparents))
                {
                    if ( $this->_is_allowed($companyparent_name, 'companyparent'))
                    {
                        $this->removing[] = ['identifier'=>$this_companyparent_id, 'identifier_type' =>'companyparent', 'identifier_name'=>$companyparent_name];
                    }
                }
            }

            // Tell the user what is about to happen.
            $this->_confirm_and_warn();

            // Do it
            foreach($this->removing as $item)
            {
                $identifier_type = GetArrayStringValue('identifier_type', $item);
                $identifier = GetArrayStringValue('identifier', $item);
                if ( $identifier_type === 'company' )
                {
                    $this->Queue_model->add_worker_job('', $identifier, $authenticated_user_id, 'DeleteCompany','index', 'now + 5 minutes');
                }
                else if ( $identifier_type === 'companyparent' )
                {
                    $this->Queue_model->add_worker_job($identifier, '', $authenticated_user_id, 'DeleteCompanyParent','index', 'now + 5 minutes');
                }
            }

            print "done.\n";
        }
        catch(Exception $e)
        {
            print "Exception! " . $e->getMessage() . "\n";
        }
    }

    private function _is_allowed($identifier_name, $identifier_type)
    {
        if ( $identifier_type === 'company' )
        {
            if ( GetArrayStringValue('company_name', $this->a2p_company) === $identifier_name ) return FALSE;
            $company = $this->Company_model->get_company_by_name($identifier_name);
            if ( empty($company) ) return FALSE;
            return TRUE;
        }
        if ( $identifier_type === 'companyparent' )
        {
            $companyparent = $this->CompanyParent_model->get_parent_by_name($identifier_name);
            if ( empty($companyparent) ) return FALSE;
            return TRUE;
        }
        return FALSE;
    }

    private function _confirm_and_warn()
    {
        // If we have already warned the user, don't do it again.
        if ( ! $this->warn ) return;

        // CONFIRM THE CRAP OUT OF THIS
        system("clear");
        print "\n";
        print "WARNING: Please pay attention.\n";
        print "\n";
        print "You are about to destroy all data related to the companies\n";
        print "and or company parents listed below.  This includes users\n";
        print "and archival data on S3 as well.\n";
        print "\n";
        foreach($this->removing as $item)
        {
            $identifier_name = GetArrayStringValue('identifier_name', $item);
            $identifier_type = GetArrayStringValue('identifier_type', $item);
            $identifier = GetArrayStringValue('identifier', $item);
            if ( $identifier_type === 'company' ) $identifier_type = "Company";
            if ( $identifier_type === 'companyparent' ) $identifier_type = "CompanyParent";
            print "{$identifier_type}: $identifier_name ( $identifier )\n";
        }
        print "\n";
        print "Furthermore, this operation will issue a VACUUM FULL command.\n";
        print "This may take a long time and it could lock database tables \n";
        print "which in turn will impact the running application.\n";
        print "It's recommended, but not required, that you take the application\n";
        print "down for maintenance while you do this.\n";
        print "\n";
        print "These removals will be executed as background tasks and they will\n";
        print "start processing in 5 minutes once you proceed.  An email report \n";
        print "will be delivered for each item removed once complete.\n";
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

/* End of file RemoveCompanies.php */
/* Location: ./application/controllers/cli/RemoveCompanies.php */
