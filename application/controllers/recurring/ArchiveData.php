<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ArchiveData extends A2PRecurringJob
{

    function __construct()
    {
        // Construct our parent class
        parent::__construct();

        //CLI ONLY! GO AWAY!
        if ( ! $this->input->is_cli_request() ) {
            Error404();
            return;
        }

    }

    public function schedule($minutes=0, $hours=0, $days=0, $months=0, $years=0)
    {
        try
        {
            $this->restartJob($minutes,$hours,$days,$months,$years);
            sleep(3600);
            $this->_archive_encryption_keys();

        }
        catch(Exception $e)
        {
            $this->reportJobFailure($e->getMessage());
        }
    }


    /**
     * _archive_encryption_keys
     *
     * This function will collect all of the encryption keys and then
     * offsite archive them in a way that will allow us to recover
     * them over time.
     *
     */
    private function _archive_encryption_keys()
    {
        $keys = array();

        // For each company, collect their encryption key.
        $companies = $this->Company_model->get_all_companies();
        foreach($companies as $company)
        {
            $company_id = GetArrayStringValue("company_id", $company);
            $encryption_key = GetCompanyEncryptionKey($company_id);

            $item = array();
            $item['app_name']           = APP_NAME;
            $item['company_id']         = $company_id;
            $item['company_name']       = GetArrayStringValue("company_name", $company);
            $item['encryption_key']     = $encryption_key;
            $keys[] = $item;
        }

        // For each companyparent, collect their encryption key.
        $parents = $this->CompanyParent_model->get_all_parents();
        foreach($parents as $parent)
        {
            $companyparent_id = GetArrayStringValue("Id", $parent);
            $encryption_key = GetCompanyParentEncryptionKey($companyparent_id);

            $item = array();
            $item['app_name']           = APP_NAME;
            $item['companyparent_id']   = $companyparent_id;
            $item['company_name']       = GetArrayStringValue("Name", $parent);
            $item['encryption_key']     = $encryption_key;
            $keys[] = $item;
        }

        $payload = A2PEncryptString(json_encode($keys), A2PGetEncryptionKey());

        //TODO: push this data somewhere.
    }
}

/* End of file ArchiveData.php */
/* Location: ./application/controllers/cli/ArchiveData.php */
