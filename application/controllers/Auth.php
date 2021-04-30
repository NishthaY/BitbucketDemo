<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    protected $route;

	public function __construct(){
		parent::__construct();
        $this->load->model('User_model','user_model',true);
        $this->load->model('Company_model','company_model',true);
        $this->load->library('form_validation');

        $this->route = base_url('auth');
    }

    // SCREENS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function login() {
        try{

            if ( getStringValue($this->input->server('REQUEST_METHOD')) !== 'GET' ) throw new SecurityException('Unexpected request method.');

            // Login Form
			$form = new UISimpleForm("login_form", "login_form", base_url("auth/authenticate"));
			$form->setCollapsable(false);
			$form->addElement($form->emailInput("email_address", null, null, "Email Address"));
			$form->addElement($form->passwordInput("password", null, null, "Password"));
			$form->addElement($form->submitButton("submit_button", "Login", "btn-primary"));
            $form->addLink("Forgot Password", base_url("auth/forgot"), "fa fa-lock");
            $form->addElement($form->formLink());
			$login_form_html = $form->render();

            $view_array = array();
            $view_array = array_merge($view_array, array("form" => $login_form_html));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("auth/js_assets")));
            $page_template = array_merge($page_template, array("short_message" => "Welcome!  Please log in to continue."));
            $page_template = array_merge($page_template, array("bottom_message" => "Want to learn more? <a href='".GetConfigValue("marketing_site")."' class='text-primary m-l-5'><b>Contact Us</b></a>"));
            $page_template = array_merge($page_template, array("flash_message" => getStringValue($this->session->flashdata('error'))));
            $page_template = array_merge($page_template, array("view" => "auth/form"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_simple', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }

    }
    public function phone() {
        try{
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != 'GET' ) throw new SecurityException("Unexpected request method.");
            if ( GetSessionValue("is_logged") !== '2FACTOR' ) throw new SecurityException("Not ready to start the multi-factor process.");

            // Login Form
            $form = new UISimpleForm("update_phone_form", "update_phone_form", base_url("auth/phone/save"));
            $form->setCollapsable(false);
            $form->addElement($form->phoneInput("phone"));
            $form->addElement($form->submitButton("submit_button", "Continue", "btn-primary"));
            $form->addLink('Start Over', base_url('auth/logout'), 'fa fa-lock');
            $form->addElement($form->formLink());
            $form_html = $form->render();

            $view_array = array();
            $view_array = array_merge($view_array, array("form" => $form_html));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("auth/js_assets")));
            $page_template = array_merge($page_template, array("short_message" => "Two-Factor Authentication"));
            $page_template = array_merge($page_template, array("short_description" => "To prevent unauthorized access to your account, we will send a verification code at time of login. Please enter your SMS-capable phone number to continue."));
            $page_template = array_merge($page_template, array("bottom_message" => ""));
            $page_template = array_merge($page_template, array("flash_message" => getStringValue($this->session->flashdata('error'))));
            $page_template = array_merge($page_template, array("view" => "auth/form"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_simple', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function code() {
        try{
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");
            if ( GetSessionValue("is_logged") !== '2FACTOR' ) throw new SecurityException("Not ready to start the multi-factor process.");

            // Login Form
            $form = new UISimpleForm("verify_code_form", "verify_code_form", base_url("auth/code/verify"));
            $form->setCollapsable(false);
            $form->addElement($form->codeInput("code"));
            $form->addElement($form->submitButton("submit_button", "Continue", "btn-primary"));
            $form->addLink('Start Over', base_url('auth/logout'), 'fa fa-lock');
            $form->addLink('Send Token Again', base_url("auth/code/resend"), 'fa fa-share-square-o', array('ajax' => true));
            $form->addElement($form->formLink());
            $form_html = $form->render();

            $view_array = array();
            $view_array = array_merge($view_array, array("form" => $form_html));


            // Get the last 4 digits of the phone number we delivered the token to.
            $phone = GetSessionValue("2factor_phone");
            if ( $phone === '' )
            {
                $user_id = GetSessionValue("user_id");
                $login_details = $this->Login_model->get_login_details($user_id);
                $phone = getArrayStringValue("TwoFactorPhoneNumber", $login_details);
            }
            $phone = substr($phone,6,4);

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("auth/js_assets")));
            $page_template = array_merge($page_template, array("short_description" => "A one-time security code has been sent to your phone ending in {$phone}. Please enter the code below."));
            $page_template = array_merge($page_template, array("flash_message" => getStringValue($this->session->flashdata('error'))));
            $page_template = array_merge($page_template, array("view" => "auth/form"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_simple', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function logout() {
        $this->session->sess_destroy();
		header('Location: ' . GetConfigValue("marketing_site"));
    }
    public function forgot() {

        try{
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Reset Password Form
            $form = new UISimpleForm("reset_password_form", "reset_password_form", base_url("auth/password/reset"));
            $form->setCollapsable(false);
            $form->addElement($form->emailInput("email_address", null, null, "Email Address"));
            $form->addElement($form->submitButton("submit_button", "Reset My Password", "btn-primary"));
            $forgot_form_html = $form->render();

            $view_array = array();
            $view_array = array_merge($view_array, array("form" => $forgot_form_html));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("auth/js_assets")));
            //$page_template = array_merge($page_template, array("short_title" => "Password Reset") );
            $page_template = array_merge($page_template, array("short_message" => "Forgot your password?  That's okay.  Enter your email address and we can help you out with that."));
            $page_template = array_merge($page_template, array("bottom_message" => "Ready to try again? <a href='".base_url()."auth' class='text-primary m-l-5'><b>Login Now</b></a>"));
            $page_template = array_merge($page_template, array("view" => "auth/forgot_form"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_simple', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }

    }
    public function password() {

        try{
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            if ( !IsLoggedIn() ) throw new Exception("This page does not exist unless you are logged in.");
            if ( GetSessionValue("weak_password") != "TRUE" ) throw new Exception("This page does not exist unless the weak_password session is set.");


            // Reset Password Form
            $form = new UISimpleForm("edit_password_form", "edit_password_form", base_url("auth/password/save"));
            $form->setCollapsable(false);
            $form->addElement($form->passwordInput("old_password", null, null, "Current Password"));
            $form->addElement($form->passwordInput("new_password", null, null, "New Password"));
            $form->addElement($form->passwordInput("confirm_password", null, null, "Confirm Password"));
            $form->addElement($form->hiddenInput("landing", base_url("dashboard")));
            $form->addElement($form->submitButton("submit_button", "Change Password", "btn-primary"));
            $form_html = $form->render();

            $view_array = array();
            $view_array = array_merge($view_array, array("form" => $form_html));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("auth/js_assets")));
            //$page_template = array_merge($page_template, array("short_title" => "Change Password") );
            $page_template = array_merge($page_template, array("short_message" => "Please update your password before continuing."));
            $page_template = array_merge($page_template, array("bottom_message" => "Maybe later. <a href='".base_url()."auth/logout' class='text-primary m-l-5'><b>Logout Now</b></a>"));
            $page_template = array_merge($page_template, array("view" => "auth/form"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_simple', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function permission_error() {

        $message = $this->session->flashdata('error');
        if ( GetStringValue($message) == "" ) $message = "You don't have permission to view this page.";

        $form = new UISimpleForm("permission_error", "permission_error", "#");
        $form->setCollapsable(false);
        if ( IsLoggedIn() )
        {
            $form->addElement($form->buttonbar("Return To Site", base_url("dashboard"), "Logout", base_url("auth/logout")));
        }
        else
        {
            $form->addElement($form->buttonbar("Login Now", base_url("auth/login")));
        }

        $form_html = $form->render();

        $page_template = array();
        //$page_template = array_merge($page_template, array("short_title" => "Access Denied") );
		$page_template = array_merge($page_template, Array("short_message" => $message));
        $page_template = array_merge($page_template, array("view" => "auth/error"));
        $page_template = array_merge($page_template, array("form" => $form_html));
        $page_template = array_merge($page_template, array("bottom_message" => "Need some help? <a href='".GetConfigValue("marketing_site")."' class='text-primary m-l-5'><b>Contact Us</b></a>"));

        RenderView('templates/template_body_simple', $page_template);

    }
    public function error_404() {

        $form = new UISimpleForm("error_404", "error_404", "#");
        $form->setCollapsable(false);
        //$form->addElement($form->buttonbar("Return To Site", base_url("dashboard")));
        $form->addElement($form->buttonbar("Return To Site", base_url("dashboard"), "Logout", base_url("auth/logout")));
        $form_html = $form->render();

        $page_template = array();
        //$page_template = array_merge($page_template, array("short_title" => "Page Not Found") );
		$page_template = array_merge($page_template, array("short_message" => "The page you are looking for is not available."));
        $page_template = array_merge($page_template, array("bottom_message" => "Need more help? <a href='".GetConfigValue("marketing_site")."' class='text-primary m-l-5'><b>Contact Us</b></a>"));
        $page_template = array_merge($page_template, array("form" => $form_html));
        $page_template = array_merge($page_template, array("view" => "auth/error"));
        RenderView('templates/template_body_simple', $page_template);

    }

    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    public function login_authenticate() {
	    $user = array();
        try{

            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new SecurityException("Javascript is required.");

            // Validate our inputs.
            $this->load->library('form_validation');
            $this->form_validation->set_rules('email_address', 'Email Address:', 'required');
            $this->form_validation->set_rules('password', 'Password:', 'required');
            if ( $this->form_validation->run() == FALSE ) throw new UIException("Invalid or missing inputs.");

            $email_address = getArrayStringValue("email_address", $_POST);
            $password = getArrayStringValue("password", $_POST);

            // Collect this user from the database and error if they do not exist.
            $user = $this->user_model->get_user($email_address);
            if ( empty($user) ) throw new UIException("Invalid email address or password.");
            if ( getArrayStringValue("deleted", $user) == "t" ) throw new UIException("Invalid email address or password.");

            // Validate the user's login password.
            if ( ! ValidatePassword( getArrayStringValue("user_id", $user), $password ) ) throw new UIException("Invalid email address or password.");

            // Enabled security checks
            if ( getArrayStringValue("enabled", $user) != "t" ) throw new UIException("User has been disabled.");
            if ( getArrayStringValue("company_id", $user) != "" )
            {
                $company = $this->company_model->get_company(getArrayIntValue("company_id", $user));
                if ( getArrayStringValue("enabled", $company) != "t" ) throw new UIException("Company has been disabled.");
            }
            if ( getArrayStringValue("company_parent_id", $user) != "" )
            {
                $parent = $this->CompanyParent_model->get_companyparent(getArrayIntValue("company_parent_id", $user));
                if ( getArrayStringValue("Enabled", $parent) != "t" ) throw new UIException("Parent has been disabled.");
            }

            // Build the user's SetSessionValue
            $acls = $this->user_model->get_user_acls_by_id(getArrayStringValue("user_id", $user));

            SetSessionValue("user_id", getArrayStringValue("user_id", $user));
            SetSessionValue("email_address", getArrayStringValue("email_address", $user));
            SetSessionValue("acls", $acls);
            SetSessionValue("display_name", getArrayStringValue("first_name", $user) . " " . getArrayStringValue("last_name", $user));



            if ( getArrayStringValue("company_id", $user)  != "" ) SetSessionValue("company_id", getArrayStringValue("company_id", $user));
            if ( getArrayStringValue("company_parent_id", $user)  != "" ) SetSessionValue("companyparent_id", getArrayStringValue("company_parent_id", $user));


            // Check the password they used to log in with.  If we consider it a weak
            // password, note that in the session.  The security layer will handle it from there.
            if ( ! ValidatePasswordStrength($password) ) SetSessionValue("weak_password", "TRUE");


            // Collect our login details.
            $user_id = getArrayStringValue("user_id", $user);
            $login_details = $this->Login_model->get_login_details($user_id);
            if (empty($login_details))
            {
                // No details were found.  Create and load them now with default settings.
                $this->Login_model->insert_details($user_id, true);
                $login_details = $this->Login_model->get_login_details($user_id);
            }
            $phone = getArrayStringValue("TwoFactorPhoneNumber", $login_details);
            $two_factor_enabled = getArrayStringValue("TwoFactorEnabled", $login_details);

            // When a user logs in, set the PSQL Work Mem value.
            if ( GetAppOption(PSQL_WORK_MEM) !== '' )
            {
                $work_mem = $this->Tuning_model->work_mem();
                if ( $work_mem !== GetAppOption(PSQL_WORK_MEM) )
                {
                    $this->Tuning_model->set_work_mem(GetAppOption(PSQL_WORK_MEM));
                }
            }
            
            // AUTH ROUTING
            // Username/Password has been authenticated.
            // Let's route the user to the next step in the authentication process based
            // on our database settings.
            if ( $two_factor_enabled === 'f' )
            {
                // Audit this transaction.
                $payload = array();
                $payload['details'] = "Without 2-factor verification.";
                AuditIt("Login successful.", $payload);

                // Two Factor Authentication is off.
                // The user is now logged in and may move to the dashboard.
                SetSessionValue("is_logged", "TRUE");
                AJAXSuccess("", base_url("dashboard"));
            }
            else if ( $phone === '' )
            {
                // Two Factor Enabled.
                // We need to collect the phone number initially.
                SetSessionValue("is_logged", "2FACTOR");
                AJAXSuccess("", base_url("auth/phone"));
            }
            else
            {
                // Two Factor Enabled.
                // We need to collect the phone number initially.
                SetSessionValue("is_logged", "2FACTOR");
                SendAuthSMSCode($user_id);
                AJAXSuccess("", base_url("auth/code"));
            }

        }
        catch ( UIException $e )
        {
            if ( ! empty($user) )
            {
                // Audit this transaction.
                $payload = array();
                $payload['details'] = $e->getMessage();
                $payload['type'] = "UI Exception";
                AuditIt("Login unsuccessful.", $payload, GetArrayStringValue('user_id', $user), GetArrayStringValue('company_id', $user));
            }

            AjaxDanger($e->getMessage());
        }
        catch( SecurityException $e )
        {
            if ( ! empty($user) )
            {
                // Audit this transaction.
                $payload = array();
                $payload['details'] = $e->getMessage();
                $payload['type'] = "Security Exception";
                AuditIt("Login unsuccessful.", $payload, GetArrayStringValue('user_id', $user), GetArrayStringValue('company_id', $user));
            }

            AccessDenied();
        }
        catch( Exception $e )
        {
            if ( ! empty($user) )
            {
                // Audit this transaction.
                $payload = array();
                $payload['details'] = $e->getMessage();
                $payload['type'] = "Exception";
                AuditIt("Login unsuccessful.", $payload, GetArrayStringValue('user_id', $user), GetArrayStringValue('company_id', $user));
            }

            Error404();
        }


    }
    public function phone_save() {
        try
        {

            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new SecurityException("Javascript is required.");
            if ( GetSessionValue("is_logged") !== '2FACTOR' ) throw new SecurityException("Not ready to start the multi-factor process.");


            // PHONE TWEAK
            // The JS Mask that we use will send us a string like (999) 999-9999.  We will strip
            // out all the goop so we have just 10 digits before validation.
            $phone = getArrayStringValue("phone", $_POST);
            $phone = StripNonNumeric($phone);
            $_POST['phone'] = $phone;

            // Do not allow a user to try and send the text messages from the number that sends them
            // text messages.  :P
            if ( $phone === TWILIO_REPLY_TO )
            {
                throw new UIException("Unsupported phone number.  Please use a different number.");
            }

            // VALIDATION
            //$this->load->library('form_validation');
            $this->form_validation->set_rules('phone', 'PHONE', 'required|max_length[10]');
            if ( $this->form_validation->run() === FALSE)
            {

                throw new UIException( strip_tags(validation_errors() . " {$phone} " . strlen) );
            }

            $email_address = GetSessionValue("email_address");
            $user = $this->user_model->get_user($email_address);
            $user_id = getArrayStringValue("user_id", $user);

            SetSessionValue("2factor_phone", $phone);
            //$this->Login_model->update_phone($user_id, $phone);

            SendAuthSMSCode($user_id, $phone);
            AJAXSuccess("", base_url("auth/code"));

        }
        catch(UIException $e) { AJAXDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function code_resend() {
        try
        {

            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( GetSessionValue("is_logged") !== '2FACTOR' ) throw new SecurityException("Not ready to start the multi-factor process.");

            // Collect information about this user based on the session data.
            $email_address = GetSessionValue("email_address");
            $user = $this->user_model->get_user($email_address);
            $user_id = getArrayStringValue("user_id", $user);
            $login_details = $this->Login_model->get_login_details($user_id);

            // VALIDATION
            // Make sure we have a valid user in hand as well as an existing hash.
            if ( empty($user) ) throw new SecurityException("Unable to find the user in question.");
            if ( $user_id === '' ) throw new SecurityException("User data appears to be invalid");
            if ( empty($login_details) ) throw new SecurityException("Login process has not been followed!");

            // Cancel the existing hash.
            $this->Login_model->update_hash($user_id, null);

            // Send a new hash.
            SendAuthSMSCode($user_id, GetSessionValue("2factor_phone"));

            // Continue on to the application.
            SetSessionValue("is_logged", "2FACTOR");
            AJAXSuccess("New token has been sent.");

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function verify_code() {
	    $user = array();
        try
        {

            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new SecurityException("Javascript is required.");
            if ( GetSessionValue("is_logged") !== '2FACTOR' ) throw new SecurityException("Not ready to start the multi-factor process.");


            // Collect information about this user based on the session data.
            $email_address = GetSessionValue("email_address");
            $user = $this->user_model->get_user($email_address);
            $user_id = getArrayStringValue("user_id", $user);
            $login_details = $this->Login_model->get_login_details($user_id);

            // VALIDATION
            // Make sure we have a valid code from the user.
            $code = getArrayStringValue("code", $_POST);
            if ( empty($user) ) throw new SecurityException("Unable to find the user in question.");
            if ( $user_id === '' ) throw new SecurityException("User data appears to be invalid");
            if ( empty($login_details) ) throw new SecurityException("Login process has not been followed!");
            if ( getArrayStringValue("TwoFactorHash", $login_details) === '' ) throw new SecurityException("Missing hash.");
            if ( $code === '' ) throw new UIException('Missing required input. code');
            $code = strtoupper($code);

            // Validate the code
            $stored_hash = getArrayStringValue("TwoFactorHash", $login_details);
            if ( ! password_verify($code, $stored_hash) ) throw new UIException("Invalid code.");

            // Has the code expired?
            if ( $this->Login_model->has_two_factor_code_expired($user_id) ) throw new UIException("Code has expired.");


            // All done!  empty out the hash as it was a one time use code.
            $this->Login_model->update_hash($user_id, null);

            // We just validated the user phone number for the FIRST time.
            // Save this phone as the two factor authentication phone number.
            $phone = GetSessionValue("2factor_phone");
            if ( $phone !== '' )
            {
                $this->Login_model->update_phone($user_id, $phone);
                RemoveSessionValue("2factor_phone");
            }

            // Audit this transaction.
            $payload = array();
            $payload['details'] = "With 2-factor verification.";
            AuditIt("Login successful.", $payload);

            // Continue on to the application.
            SetSessionValue("is_logged", "TRUE");
            AJAXSuccess("", base_url("dashboard"));

        }
        catch ( UIException $e )
        {
            if ( ! empty($user) )
            {
                // Audit this transaction.
                $payload = array();
                $payload['details'] = $e->getMessage();
                $payload['type'] = "UI Exception";
                AuditIt("Login unsuccessful.", $payload, GetArrayStringValue('user_id', $user), GetArrayStringValue('company_id', $user));
            }

            AjaxDanger($e->getMessage());
        }
        catch( SecurityException $e )
        {
            if ( ! empty($user) )
            {
                // Audit this transaction.
                $payload = array();
                $payload['details'] = $e->getMessage();
                $payload['type'] = "Security Exception";
                AuditIt("Login unsuccessful.", $payload, GetArrayStringValue('user_id', $user), GetArrayStringValue('company_id', $user));
            }

            AccessDenied();
        }
        catch( Exception $e )
        {
            if ( ! empty($user) )
            {
                // Audit this transaction.
                $payload = array();
                $payload['details'] = $e->getMessage();
                $payload['type'] = "Exception";
                AuditIt("Login unsuccessful.", $payload, GetArrayStringValue('user_id', $user), GetArrayStringValue('company_id', $user));
            }

            Error404();
        }
    }
    public function password_save() {

		// password_save ( POST )
		//
		// Save password data.
		// ------------------------------------------------------------

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new SecurityException("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

			// Validate our inputs.
			$this->form_validation->set_rules('old_password','current passowrd','required|callback_validateOldPass');
			$this->form_validation->set_rules('new_password', 'new password', 'required|min_length[7]|max_length[80]|callback_validatePassStrength');
			$this->form_validation->set_rules('confirm_password', 'confirm password', 'required|callback_newPasswordValidator');
            if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}

			$old_password = getArrayStringValue("old_password", $_POST);
			$new_password = getArrayStringValue("new_password", $_POST);

			// Save this information to the database.
			$this->user_model->update_user_password_by_id( GetSessionValue("user_id"), $new_password );
            SetSessionValue("weak_password", "FALSE");

			// If the landing variable is set, redirect to the specified location with no message.
			if ( getArrayStringValue("landing", $_POST) != "") {
				AJAXSuccess("", getArrayStringValue("landing", $_POST) );
			}
            AJAXSuccess("Password changed.");

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
	}
    public function password_reset() {

		// password_save ( POST )
		//
		// Save password data.
		// ------------------------------------------------------------
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new SecurityException("Javascript is required.");

			// Validate our inputs.
			$this->form_validation->set_rules('email_address','email address','required');
            if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}

			$email_address = getArrayStringValue("email_address", $_POST);
            $user = $this->user_model->get_user($email_address);
            if ( empty($user) )
            {
                AJAXDanger("Unknown email address.");
            }

            // New passowrd, but weak.
            $new_password = GenerateWeakPassword();
            $this->user_model->update_user_password_by_id(getArrayStringValue("user_id", $user), $new_password);

			$user_id = getArrayStringValue("user_id", $user);
            $email_sent = SendPasswordResetEmail($user_id, $new_password);
            if ( ! $email_sent )
            {
                throw new UIException("Unable to deliver temporary password.  Please try again later.");
            }

            // Make sure they are no longer logged in, if they were.
            if ( GetSessionValue("__ci_last_regenerate") != "" )
            {
                $this->session->sess_destroy();
            }

            AJAXSuccess("Email sent.  Please check your mail for information on how to finish the password reset process.");


		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
	}


    /**
     * pusher
     *
     * This function will authenticate PUSHER subscription requests
     * for access to private channels.
     *
     * When a user subscribes to a channel, this function is called by
     * the PUSHER web application.  If we believe the currently authenticated
     * user should have access to the channel they are requesting, then we
     * return the response PUSHER expected for success.  If not, we return a 403.
     *
     * https://pusher.com/docs/auth_signatures
     *
     */
    public function pusher() {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( ! IsLoggedIn() ) throw new Exception("Not logged in.");

            // Build out all the bits we will need to authenticate this pusher request.
            $key = GetPusherAPIKey();
            $secret = GetPusherAPISecret();
            $channel_name = GetArrayStringValue("channel_name", $_POST);
            $socket_id = GetArrayStringValue("socket_id", $_POST);
            $signature = hash_hmac('sha256', "{$socket_id}:{$channel_name}", $secret);

            LogIt("Pusher", $channel_name, $_POST);

            // VALIDATION: private-<app-name>-<type>-<type_id>
            $parts = explode("-", $channel_name);

            if ( count($parts) != "5" ) throw new Exception("Malformed channel.");

            if ( GetArrayStringValue("0", $parts) !== 'private' ) throw new Exception("Malformed channel.");

            $channel_app_name = GetArrayStringValue("1", $parts) . "-" . GetArrayStringValue("2", $parts);
            if ( $channel_app_name !== APP_NAME ) throw new Exception("Channel does not match application.");

            $channel_types = array('company', 'companyparent');
            $channel_type = GetArrayStringValue("3", $parts);
            if ( ! in_array($channel_type, $channel_types)) throw new Exception("Unknown channel type.");

            $id = GetArrayStringValue("4", $parts);
            if ( $channel_type === 'company' )
            {
                $channel_company_id = $id;
                if ( ! IsAuthenticated('company_read', 'company', $channel_company_id) ) throw new Exception("Channel does not belong to authenticated user company.");
            }
            if ( $channel_type === 'companyparent' )
            {
                $channel_companyparent_id = $id;
                if ( ! IsAuthenticated('parent_company_read') ) throw new Exception("User not authorized to subscribe to this channel.");
                if ( ! GetSessionValue('companyparent_id') == $channel_companyparent_id ) throw new Exception("Channel does not belong to authenticated user companyparent.");
            }

            // Okay, the user is authenticated to use this channel for socket communications.
            $payload = array();
            $payload['auth'] = "{$key}:{$signature}";
            print json_encode($payload);

        }
        catch( Exception $e )
        {
            LogIt("PUSHER ERROR", $e->getMessage());


            // WALK OFF
            // If the user walked away from their computer and we get a pusher
            // event that indicates the user is not logged in, log the user out
            // of their session and redirect them to the login page.
            $message = $e->getMessage();
            $message = strtoupper($message);
            if ( strpos($message, "NOT LOGGED IN") !== FALSE )
            {
                redirect( base_url("auth/logout") );
                exit;
            }

            // Any other error, return a 403
            http_response_code(403);
        }
    }

    // VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    public function password_validate() {
        $old_password = getArrayStringValue("old_password", $_POST);
        if (!ValidatePassword(GetSessionValue("user_id"), $old_password)) {
            //$this->form_validation->set_message('validateOldPass','Current password is incorrect.');
            echo '{ "validation": 0 }';
            return false;
        }
        echo '{ "validation": 1 }';
        return true;
    }
    public function validateOldPass($old_password = '') {
        if ( !IsLoggedIn() ) return false;
        if (!ValidatePassword(GetSessionValue("user_id"), $old_password) ) {
            $this->form_validation->set_message('validateOldPass','Current password is incorrect.');
            return false;
        }
        return true;
    }
    public function validatePassStrength($password) {
        if ( !IsLoggedIn() ) return false;
        if (!ValidatePasswordStrength($password) ) {
            $this->form_validation->set_message('validatePassStrength','Your password does is not strong enough.');
            return false;
        }
        return true;
    }
    public function newPasswordValidator() {
        if ( !IsLoggedIn() ) return false;

        $new_password = getArrayStringValue('new_password', $_POST);
        $confirm_password = getArrayStringValue('confirm_password', $_POST);

        ($new_password != $confirm_password) ? $valid = FALSE : $valid = TRUE;
        if (! $valid ) {
            $this->form_validation->set_message('newPasswordValidator', 'The password fields do not match.');
            return FALSE;
        }
        return TRUE;
    }

}
