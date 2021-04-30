<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class DeleteCompany
 *
 * This is a Worker class that can be placed on the queue and processed
 * on the server as time permits.  Specifically, it will delete the
 * company specified on the job request.
 *
 *
 *
 */
class DeleteCompany extends CI_Controller
{
    protected $warnings;
    protected $audit;

    function __construct()
    {
        parent::__construct();

        // Don't allow this to be ran from the web.
        if ( ! is_cli() )
        {
            Error404();
            return;
        }

        $this->warnings = array();
        $this->audit = array();
    }
    function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
        ob_start();

        // Validation.
        if ( APP_NAME == "a2p-prod" ) trigger_error("Company delete is not supported for the ".APP_NAME." application.", E_USER_ERROR);
        if ( $company_id === A2P_COMPANY_ID )
        {
            trigger_error("Company delete is not supported for Advice2Pay customer.", E_USER_ERROR);
            exit;
        }

        $company = $this->Company_model->get_company($company_id);
        $company_name = GetArrayStringValue('company_name', $company );
        $company_users = $this->User_model->get_all_users($company_id);

        // First, delete any users associated with this company.
        $this->audit[] = "Removing users associated with company.";
        foreach( $company_users as $user)
        {
            $company_user_id = getArrayStringValue("Id", $user);
            $this->User_model->hard_delete_user($company_user_id, $user_id);
        }

        // There are a few tables that do not have the "CompanyId" column but
        // inferred via another table.  That sucks, but nothing can be done about
        // it now.  These will have to be removed first before we start
        // removing data from other tables.
        $this->audit[] = "Removing company data from database relationship tables.";
        $this->Ageband_model->delete_all_bands_by_company( $company_id );                               // AgeBand
        $this->GenerateOriginalEffectiveDateData_model->delete_all_items_by_company( $company_id );     // LifeOriginalEffectiveDate

        // Now start the fun stuff.  Delete everything associated with this
        // company.
        $this->audit[] = "Removing company data from database tables.";
        $this->Company_model->hard_delete_company($company_id, $user_id);

        // Clean up S3
        $this->audit[] = "Removing files on S3.";
        $prefix =  GetConfigValue("root_prefix", "aws");
        $prefix = replaceFor($prefix, "COMPANYID", $company_id);
        S3DeleteBucketContent( S3_BUCKET,  $prefix);

        // Clean up KMS
        $this->audit[] = "Scheduling the removal of company encryption key.";
        $this->retire_security_key($company_id, $user_id);

        // Tell the database to clean up after itself.
        $this->audit[] = "Doing a full vacuum now that we have deleted a bunch of data.";
        $this->Tuning_model->vacuum_full(true);

        // Did any output leak out?  We don't want that on a one-off background job
        // as that output could impact customers.  Capture and issue it as a warning on
        // the report.
        $output = trim(ob_get_contents());
        ob_end_clean();
        if ( GetStringValue($output) !== '' ) $this->warnings[] = $output;

        $view_array = array();
        $view_array['identifier_name'] = $company_name;
        SendBackgroundJobReportEmail($company_id, 'company', $job_id, $user_id, $this->warnings, $this->audit, $view_array);

    }

    /**
     * This function will schedule for deletion the security keys that
     * are associated with the company.  The keys will be removed in
     * 7 days.
     *
     * @param $company_id
     * @param $authenticated_user_id
     * @throws Exception
     */
    private function retire_security_key($company_id, $authenticated_user_id)
    {

        if ( APP_NAME === 'a2p-prodcopy' )
        {
            // Security key retirement is not allowed in PRODCOPY.
            // You will need to manually remove them from KMS.
            $this->warnings[] = "Security key retirement is not allowed in PRODCOPY.  You will need to manually remove them from KMS.";
            return;
        }

        $days = 7;
        $items = KMSGetAliases();
        foreach($items as $item)
        {
            $alias_name = GetArrayStringValue('AliasName', $item);
            $key = KMSGetAlias($alias_name);
            $key_id = GetArrayStringValue('TargetKeyId', $key);
            if ( strpos($alias_name, APP_NAME . "/company_{$company_id}") !== FALSE )
            {
                KMSScheduleAliasForDeletion($alias_name, $days);

                $payload = array();
                $payload['RetiredKeyAlias'] = $alias_name;
                $payload['RetiredKeyId'] = $key_id;
                $payload['DaysTillRemoval'] = $days;
                AuditIt("Retired customer master key.", $payload, $authenticated_user_id, A2P_COMPANY_ID);

                $this->audit[] = "Scheduled [{$alias_name}] for removed in [{$days}] days.";
            }
        }
    }
}

/* End of file DeleteCompany.php */
/* Location: ./application/controllers/cli/DeleteCompany.php */
