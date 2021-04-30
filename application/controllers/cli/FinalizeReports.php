<?php defined('BASEPATH') OR exit('No direct script access allowed');

use \phpseclib\Net\SFTP;
use \phpseclib\Crypt\RSA;

class FinalizeReports extends A2PWizardStep
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        // init other things we need on this step
        //$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }



    /**
     * index
     *
     * Default functionality.
     *
     * @param $user_id
     * @param $company_id
     * @param string $job_id
     */
    public function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
        parent::index($user_id, $company_id, $companyparent_id, $job_id);

        try
        {

            sleep(1);
            $this->notify_status_update('FINALIZING');
            sleep(2);


            // Remove the plans completed flg.
            $this->Reporting_model->finalize_upload_data($company_id);
            $this->Life_model->delete_companylife_disabled($company_id); // Clean up abandoned lives.

            // Audit this transaction.
            $user = $this->User_model->get_user_by_id($user_id);
            $company = $this->Company_model->get_company($company_id);
            $payload = array();
            $payload = array_merge($payload, array('ImportDate'=>GetRecentDateDescription($company_id)));
            $payload = array_merge($payload, array('UserId'=>GetArrayStringValue('user_id', $user)));
            $payload = array_merge($payload, array('FirstName' => GetArrayStringValue('first_name', $user)));
            $payload = array_merge($payload, array('LastName' => GetArrayStringValue('last_name', $user)));
            $payload = array_merge($payload, array('EmailAddress' => GetArrayStringValue('email_address', $user)));
            $payload = array_merge($payload, array('CompanyId'=>GetArrayStringValue('company_id', $company)));
            $payload = array_merge($payload, array('CompanyName' => GetArrayStringValue('company_name', $company)));

            // Always write a finalization audit record to the company.
            AuditIt( "Reports finalized.", $payload, $user_id, $company_id );

            // If the associated job has a group_id, then this request came from the parent dashboard
            // and we will want to write the audit trail on the parent as well to make things easier
            // to research.
            if ( GetStringValue($job_id) !== '' )
            {
                $job = $this->Queue_model->get_job($job_id);
                $group_id = GetArrayStringValue('GroupId', $job);

                if ( $group_id !== '' )
                {
                    if ( GetStringValue($companyparent_id) !== '' )
                    {
                        AuditIt( "Reports finalized.", $payload, $user_id, null, $companyparent_id );
                    }
                }
            }

            sleep(2);

            // The wizard is now complete.  Remove the workflow row so they can start again.
            $this->Wizard_model->delete_wizard($company_id);

            // Start a background job to transfer files to an external site, if configured.
            $this->Queue_model->add_worker_job($companyparent_id, $company_id, $user_id, 'FileTransfer', 'index');
            NotifyWizardComplete($company_id);

        }
        catch(Exception $e)
        {
            // Write the error to stdout so that the queue manager can see
            // and detect that something bad has happened.
            print $e->getMessage() . PHP_EOL;
            NotifyStepComplete($company_id);
        }
    }

}

/* End of file FinalizeReports.php */
/* Location: ./application/controllers/cli/FinalizeReports.php */
