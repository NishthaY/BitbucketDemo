<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends SecureController {

	protected $route;

	function __construct(){
		parent::__construct();
		$this->load->model('User_model','user_model',true);
		$this->load->library('form_validation');
		$this->route = base_url("settings");


	}


	public function account_save() {

		// account_save ( POST )
		//
		// Save account data.
		// ------------------------------------------------------------

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new SecurityException("Javascript is required.");

			// Validate our inputs.
            $this->load->library('form_validation');
            $this->form_validation->set_rules('firstname', 'First Name:', 'required');
            $this->form_validation->set_rules('lastname', 'Last Name:', 'required');
			$this->form_validation->set_rules('email_address','email address','required');
            if ( $this->form_validation->run() == FALSE ) throw new UIException("Invalid or missing inputs.");

			// Collect our data
			$user_id = GetSessionValue("user_id");
			$user = $this->user_model->get_user_by_id( $user_id );
			$email_address = getArrayStringValue("email_address", $_POST);
            $firstname = getArrayStringValue("firstname", $_POST);
            $lastname = getArrayStringValue("lastname", $_POST);

			// The email address must be available to continue.
			if ( getArrayStringValue("email_address", $user) != $email_address )
			{
				if ( ! IsUsernameAvailable( $email_address ) )
				{
					throw new UIException("Email address already in use.");
				}
			}

			// Save updated data.
			$this->user_model->update_user_by_id($user_id, $email_address, $firstname, $lastname );
			SetSessionValue("display_name", "{$firstname} {$lastname}");
            AJAXSuccess("Account settings updated.");

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
	}

	function enterprise_banner_deactivate()
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new SecurityException("Javascript is required.");

            if ( GetSessionValue('is_logged') === 'TRUE' )
            {
                SetSessionValue('show_enterprise_banner', 'FALSE');
            }
            AJAXSuccess("Account settings updated.");

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

}
