<?php defined('BASEPATH') OR exit('No direct script access allowed');

class GenerateSecurityKey extends A2PWorker
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


    function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
        parent::index($user_id, $company_id, $companyparent_id, $job_id);

        $this->timers = false;
        $this->_generate($user_id, $company_id);

    }

    private function _generate( $user_id, $company_id )
    {
        try
        {
            // Create a new security key in the pool.
            CreateSecurityKey($company_id, $user_id);
        }
        catch(Exception $e)
        {
            $view_array = array();
            $view_array['key_count'] = $this->Support_model->count_ready_keypool_keys();
            $view_array['message'] = $e->getMessage();
            $view_array = array_merge($view_array, array("hostname" => HOSTNAME));

            $message = "Automated systems were unable to generate a new key to replaced a consumed key from the keypool.  ";
            $message .= "This is not a critical issue, yet.  At your first convenience, please check the status of the ";
            $message .= "keypool and add an addition key as needed. ";
            $message .= "<BR><BR>";
            $message .= $e->getMessage();
            $image = "";
            $body = RenderViewAsString("emails/keypool_generation_failed", $view_array);

            SendSupportEmail($company_id, null, $message, "", $user_id);
        }
    }

}

/* End of file GenerateSecurityKey.php */
/* Location: ./application/controllers/cli/GenerateSecurityKey.php */
