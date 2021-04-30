<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class DeleteCompanyParent
 *
 * This is a Worker class that can be placed on the queue and processed
 * on the server as time permits.  Specifically, it will delete the
 * company parent, but only if the parent has no children.
 */
class DeleteCompanyParent extends CI_Controller
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
    function index( $user_id, $company_id='', $companyparent_id='', $job_id='' ) {


        ob_start();

        // Validation.
        if ( APP_NAME == "a2p-prod" ) trigger_error("CompanyParent delete is not supported for the ".APP_NAME." application.", E_USER_ERROR);

        // Collect the human readable name for the company parent.
        $companyparent = $this->CompanyParent_model->get_companyparent($companyparent_id);
        $companyparent_name = GetArrayStringValue('Name', $companyparent );

        // First, delete any users associated with this company.
        $this->audit[] = "Removing users associated with the parent.";
        $companyparent_users = $this->User_model->get_all_users_for_parent($companyparent_id);
        if ( ! empty($companyparent_users) )
        {
            foreach( $companyparent_users as $user)
            {
                $companyparent_user_id = getArrayStringValue("user_id", $user);
                $this->User_model->hard_delete_user($companyparent_user_id, $user_id, false);
            }
        }

        // Remove data from tables that reference the CompanyParentId
        $this->audit[] = "Removing company data from database tables.";
        $this->CompanyParent_model->hard_delete_companyparent($companyparent_id, $user_id, false);

        // Clean up S3
        $this->audit[] = "Removing files on S3.\n";
        $prefix =  GetConfigValue("parent_prefix", "aws");
        $prefix = replaceFor($prefix, "COMPANYPARENTID", $companyparent_id);
        S3DeleteBucketContent( S3_BUCKET,  $prefix);

        // Clean up KMS
        $this->audit[] = "Scheduling the removal of company encryption key.\n";
        $this->retire_security_key($companyparent_id, $user_id);

        // Tell the database to clean up after itself.
        $this->audit[] = "Doing a full vacuum now that we have deleted a bunch of data.\n";
        $this->Tuning_model->vacuum_full(true);

        // Did any output leak out?  We don't want that on a one-off background job
        // as that output could impact customers.  Capture and issue it as a warning on
        // the report.
        $output = trim(ob_get_contents());
        ob_end_clean();
        if ( GetStringValue($output) !== '' ) $this->warnings[] = $output;

        $view_array = array();
        $view_array['identifier_name'] = $companyparent_name;
        SendBackgroundJobReportEmail($companyparent_id, 'companyparent', $job_id, $user_id, $this->warnings, $this->audit, $view_array);



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
    private function retire_security_key($companyparent_id, $authenticated_user_id)
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
            if ( strpos($alias_name, APP_NAME . "/companyparent_{$companyparent_id}") !== FALSE )
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

/* End of file DeleteCompanyParent.php */
/* Location: ./application/controllers/cli/DeleteCompanyParent.php */
