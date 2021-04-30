<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );

class Commissions extends Tool
{

    protected $delay;
    private $_debug;
    private $audit;


    public function __construct()
    {
        parent::__construct();
        $this->_debug = true;   // Write detailed information to STDOUT
        $this->timers       = true;
        $this->timer_array  = array();
        $this->audit = array();
    }

    public function migrate()
    {
        try
        {
            $user_id = $this->_initUser();
            $company_id = $this->_initCompany();
            $companyparent_id = GetCompanyParentId($company_id);

            // STOP!
            // Do not migrate if the company has a data file up for review and not yet finalized.
            if ( ! $this->_allDataFinalized($company_id) )
            {
                if ( ! IsReportGenerationStepComplete($company_id) )
                {
                    throw new Exception("Job not started.  Customer data is not finalized nor ready to be finalized.");
                }
            }

            $payload = array();
            $payload[] = GetStringValue($user_id);
            $payload[] = GetStringValue($company_id);
            $this->Queue_model->add_worker_job($companyparent_id, $company_id, $user_id, 'MigrateCommissions','index');

            $this->debug("Job started.  Please check admin dashboard for updates.");

        }catch(Exception $e)
        {
            $this->debug("Exception!!");
            $this->debug($e->getMessage());
        }
    }


    /**
     * debug
     *
     * Write detailed messages about our progress to STDOUT.
     * @param $message
     */
    protected function debug($message)
    {
        if ( $this->_debug )
        {
            $type = gettype($message);
            if ( $type === 'boolean' || $type === 'integer' || $type === 'double' || $type === 'string' )
            {
                $message = trim($message);
                print $message . "\n";
            }
        }
    }

    private function _allDataFinalized($company_id)
    {
        // Get a list of import dates for this company over all of their data.
        $import_dates = $this->Reporting_model->select_import_dates($company_id);
        foreach($import_dates as $item)
        {
            $finalized = GetArrayStringValue("Finalized", $item);
            if ( $finalized === 'f' ) return false;
        }
        return true;
    }

    /*
     * _initUser
     *
     * Returns the user_id for the authenticated user.
     *
     */
    private function _initUser()
    {
        // Who you?
        $user_id = GetArrayStringValue("user_id", $this->authenticated_user);
        if ( $user_id === '' ) throw new Exception("Missing required input user_id.");
        return $user_id;
    }

    /**
     * _initCompany
     *
     * Sets the "company" property on the class and returns the company_id.
     * Will ask the user interactively for the company, if no company_id is provided.
     *
     * @param null $company_id
     * @return int|null
     */
    private function _initCompany($company_id=null)
    {
        // If we don't have a customer_id, get one.
        if ( GetStringValue($company_id) === '' )
        {
            while ( empty($this->company) )
            {
                $this->getCompany();
            }
            $company_name = GetArrayStringValue("company_name", $this->company);
            $company_id = GetArrayIntValue("company_id", $this->company);
            print "You have selected company [{$company_name}] ({$company_id}).\n";


            // REVIEW
            // Tell the user what the current settings are for the company in question.
            $commission_tracking_enabled = $this->Feature_model->is_feature_enabled($company_id, 'COMMISSION_TRACKING');
            $commission_effective_date_type = GetCommissionEffectiveDateType($company_id);
            $commission_type = GetCommissionType($company_id);

            $message = "";
            if ( $commission_tracking_enabled ) $message .= "COMMISSION_TRACKING feature is currently ENABLED.\n";
            if ( ! $commission_tracking_enabled ) $message .= "COMMISSION_TRACKING feature is currently DISABLED.\n";

            if ( $commission_tracking_enabled )
            {
                $message .= "Commission Type: [{$commission_type}]\n";
                $message .= "Commission Effective Date Type: [{$commission_effective_date_type}]\n";
            }
            $message .= "\nYou are about to recalculate commission for this company.";
            $this->confirm($message );
        }
        else
        {
            // go get the company.
            $this->company = $this->Company_model->get_company( $company_id );
            $company_name = GetArrayStringValue("company_name", $this->company);
            $company_id = GetArrayIntValue("company_id", $this->company);
        }

        return $company_id;

    }


    protected function getCompany()
    {
        system('clear');
        print "\n";
        print "Review the list of companies below and then at the command\n";
        print "prompt type in the company of your choice.\n";
        print "\n";
        $companies = $this->Company_model->get_all_companies();

        // Filter the full company list to include just companies that have commissions enabled.
        $filtered = array();
        foreach($companies as $company)
        {
            $company_id = GetArrayStringValue('company_id', $company);
            if ( $this->Feature_model->is_feature_enabled($company_id, 'COMMISSION_TRACKING') )
            {
                $filtered[] = $company;
            }
        }


        uasort($filtered, 'AssociativeArraySortFunction_company_name');
        foreach($filtered as $company)
        {
            print "  " . GetArrayStringValue("company_name", $company) . "\n";
        }
        $selected_company_name = readline("Company Name: ");

        $company = $this->Company_model->get_company_by_name($selected_company_name);
        $this->company = $company;
        return $company;
    }

}
/* End of file RotateKey.php */
/* Location: ./application/controllers/cli/RotateKey.php */
