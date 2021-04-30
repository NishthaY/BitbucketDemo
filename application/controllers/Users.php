<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends SecureController {

    protected $route;

	public function __construct(){
		parent::__construct();
        $this->load->model('User_model','user_model',true);
        $this->load->model('Company_model','company_model',true);
        $this->load->library('form_validation');
    }

    /**
     * company_assignment
     *
     * This function renders the UI for the company assignment page.
     * In general the page is used to assign companies to a specific user.
     *
     * @param $user_id
     */
    public function company_assignment($user_id)
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            $company_parent_id = GetSessionValue("companyparent_id");


            // Collect USER data.
            $user = $this->User_model->get_user_by_id($user_id);

            // Assignment Widget
            $assignment_widget = "";
            $assignment_widget = new UIWidget("assignment_widget");
            $assignment_widget->setBody( $this->_assignment_table($user_id) );
            $assignment_widget->setHref(base_url("users/widget/assignments/{$user_id}"));
            $assignment_widget = $assignment_widget->render();

            // Get the assigned table.
            $assigned = "";
            //$view_array = array();
            //$view_array = array_merge($view_array, array("user_id" => $user_id));
            //$assigned = RenderViewAsString("users/assignment_table", $view_array);

            // Form header
            $header = new UIFormHeader("Company Assignment");
            $header->addLink("User List", base_url('users/manage'));
            $header->addLink(GetArrayStringValue("first_name", $user) . " " . GetArrayStringValue("last_name", $user));
            $header = $header->render();

            $view_array = array();
            $view_array = array_merge($view_array, array("header_html" => $header));
            $view_array = array_merge($view_array, array("assignment_widget" => $assignment_widget));
            $view_array = array_merge($view_array, array("user" => $user));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("users/user_js_assets")));
            $page_template = array_merge($page_template, array("view" => "users/assignment"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

    public function users_list() {

        // users_list ( GET )
        //
        // Create an manage users for a given company.
        // ------------------------------------------------------------
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required permission.");

            $company_id = GetSessionValue("company_id");
            $company = $this->company_model->get_company($company_id);
            $company_parent_id = GetSessionValue("companyparent_id");
            $companyparent = $this->CompanyParent_model->get_companyparent($company_parent_id);
            if( empty($company) && empty($companyparent) ) throw new Exception("Could not find company/parent.");

            // Form header
            $header = new UIFormHeader("Users");
            $header->addButton($header->button("add_user", "Add User", "add_user_form" ));
            $header->addLink("User List");
            $header_html = $header->render();

            // Add User Form
            $add_form_widget = new UIWidget("add_user_widget");
            $add_form_widget->setHref(base_url("users/widget/add"));
            $add_form_widget = $add_form_widget->render();

            // Edit User Form
            $edit_form_widget = new UIWidget("edit_user_widget");
            $edit_form_widget->setHref(base_url("users/widget/edit"));
            $edit_form_widget = $edit_form_widget->render();

            // User data widget.
            $widget = new UIWidget("users_widget");
            $widget->setBody($this->_users_table($company_id, $company_parent_id));
            $widget->setHref(base_url("users/widget/list"));
            $widget->setCallback("InitUsersTable");
            $table_html = $widget->render();

            // Delete User widget
            $delete_form_widget = new UIWidget("delete_user_widget");
            $delete_form_widget->setBody( $this->_user_delete_form(null) );
            $delete_form_widget->setHref(base_url("users/widget/delete"));
            $delete_form_widget = $delete_form_widget->render();


            $view_array = array();
            $view_array = array_merge($view_array, array("users_table" => $table_html));
            $view_array = array_merge($view_array, array("add_form" => $add_form_widget));
            $view_array = array_merge($view_array, array("edit_form" => $edit_form_widget));
            $view_array = array_merge($view_array, array("delete_form" => $delete_form_widget));
            $view_array = array_merge($view_array, array("form_header" => $header_html));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("users/user_js_assets")));
            $page_template = array_merge($page_template, array("view" => "users/users"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }

    }

    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	public function user_add() {

		// user_add ( POST )
		//
		// This function handles the user request to add a user to a specific company.
		// ------------------------------------------------------------
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required permission company_write.");

			// Validate our inputs.
			$this->form_validation->set_rules('email_address','email address','required');
			$this->form_validation->set_rules('first_name','first name','required');
			$this->form_validation->set_rules('last_name','last name','required');
            $this->form_validation->set_rules('entity_id','entity_id','required');
            $this->form_validation->set_rules('entity_type','entity_type','required');

			if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}

            $entity_type = getArrayStringValue("entity_type", $_POST);
            $entity_id = getArrayStringValue("entity_id", $_POST);
			$email_address = getArrayStringValue("email_address", $_POST);
			$first_name = getArrayStringValue("first_name", $_POST);
			$last_name = getArrayStringValue("last_name", $_POST);

            // The email address must be available to continue.
            if ( ! IsUsernameAvailable(getArrayStringValue("email_address", $_POST) ) )
            {
                throw new UIException("Email address already in use.");
            }

            // The entity_id must match the user's session to continue.
            if ( $entity_type == "company" && $entity_id != GetSessionValue("company_id") ) throw new SecurityException("User does not have permission to add users for requested company.");
            if ( $entity_type == "parent" && $entity_id != GetSessionValue("companyparent_id") ) throw new SecurityException("User does not have permission to add users for requested company parent.");

			// Create user.
			$password = GenerateWeakPassword();
			$this->user_model->create_user( $email_address, $first_name, $last_name, $password );

            // Collect info about the new user.
            $new_user = $this->user_model->get_user( $email_address );
            $new_user_id = getArrayStringValue("user_id", $new_user);

            if ( $entity_type == "company")
            {
                // Link user to company.
    			if ( ! $this->user_model->is_user_linked_to_company( $new_user_id, $entity_id ) )
    			{
    				$this->user_model->link_user_to_company( $new_user_id, $entity_id );
    			}

                // Grant ADMIN permission if the user is for Advice2Pay company.
                if ( GetSessionValue("company_id") == "1" && $entity_id == "1" )
                {
                    $this->user_model->grant_user_acl( $new_user_id, "Staff");
                    $company = $this->company_model->get_company($entity_id);
                    SendWelcomeEmail($new_user_id, $password, getArrayStringValue("company_name", $company));
                }
                else
                {
                    // Grant user company_manager rights.
                    if ( getArrayStringValue("manager", $_POST) == "on" )
        			{
                        $this->user_model->grant_user_acl($new_user_id, "Manager");
                        $this->user_model->grant_user_acl($new_user_id, "PII Download");
                    }else{
                        $this->user_model->grant_user_acl($new_user_id, "User");
                        $this->user_model->grant_user_acl($new_user_id, "PII Download");
                    }
                }





            }
            if ( $entity_type == "parent")
            {
                // Link user to company parent.
    			if ( ! $this->User_model->is_user_linked_to_parent( $new_user_id, $entity_id ) )
    			{
    				$this->User_model->link_user_to_parent( $new_user_id, $entity_id );
    			}

    			// Grant user company_manager rights.
                if ( getArrayStringValue("manager", $_POST) == "on" )
    			{
                    $this->User_model->grant_user_acl($new_user_id, "Parent Manager");
                    $this->user_model->grant_user_acl($new_user_id, "PII Download");
                }else{
                    $this->User_model->grant_user_acl($new_user_id, "Parent User");
                    $this->user_model->grant_user_acl($new_user_id, "PII Download");
                }
            }

            // Enable the Users
            $this->user_model->enable_user($new_user_id);

			// Send onboarding email.
			if ( getArrayStringValue("onboarding", $_POST) == "on" )
			{
                $parent = $this->CompanyParent_model->get_companyparent($entity_id);
                SendWelcomeEmail($new_user_id, $password, getArrayStringValue("name", $parent));
			}

			AJAXSuccess("User created.");


		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	public function user_edit_reset_phone($user_id) {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write,parent_company_write") ) {
                if ( $user_id !== GetSessionValue("user_id") )
                {
                    throw new SecurityException("Missing required permission company_write.");
                }
            }

            $email_address = GetSessionValue("email_address");
            $user = $this->user_model->get_user($email_address);
            $user_id = getArrayStringValue("user_id", $user);
            $login_details = $this->Login_model->get_login_details($user_id);
            $phone = getArrayStringValue("TwoFactorPhoneNumber", $login_details);
            if ( $phone !== '' )
            {
                $this->Login_model->update_phone($user_id, null);
            }

            AJAXSuccess("User updated.");


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function user_edit() {

		// user_add ( POST )
		//
		// This function handles the user request to edit a user.
		// ------------------------------------------------------------

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required permission company_write.");

			// Validate our inputs.
			$this->form_validation->set_rules('email_address','email address','required');
            $this->form_validation->set_rules('original_email_address','original email address','required');
			$this->form_validation->set_rules('first_name','first name','required');
			$this->form_validation->set_rules('last_name','last name','required');
            $this->form_validation->set_rules('user_id','user_id','required');

			if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}

            $user_id = getArrayStringValue("user_id", $_POST);
			$email_address = getArrayStringValue("email_address", $_POST);
			$first_name = getArrayStringValue("first_name", $_POST);
			$last_name = getArrayStringValue("last_name", $_POST);
            getArrayStringValue("manager", $_POST) == "on" ? $manager = true : $manager = false;
            $entity_type = getArrayStringValue("entity_type", $_POST);

            // Update the user properties.
            $this->user_model->update_user_by_id($user_id, $email_address, $first_name, $last_name, $manager);

            // When someone with the "All" or "Staff" acl is editing a user, their
            // user form does not have a manager checkbox.  Thus, we don't want to
            // change any permissions based on a manager flag that was defaulted, and not
            // set.  Just wrap things up instead.
            $acls = GetSessionValue("acls");
            if ( in_array( "All", $acls) ) AJAXSuccess("User updated.");
            if ( in_array( "Staff", $acls) ) AJAXSuccess("User updated.");

            // Editing the permissions for a company.  Make sure the permissions
            // are set for this user for a company based on the manager checkbox.
            if ( $entity_type == "company" )
            {
                if ( $manager ) $this->user_model->deny_user_acl($user_id, "User");
                if ( $manager ) $this->user_model->grant_user_acl($user_id, "Manager");
                if ( ! $manager ) $this->user_model->deny_user_acl($user_id, "Manager");
                if ( ! $manager ) $this->user_model->grant_user_acl($user_id, "User");

                $this->user_model->grant_user_acl($user_id, "PII_Download");

            }

            // Editing the permissions for a parent company.  Make sure the permissions
            // are set for this user for a parent company based on the manager checkbox.
            if ( $entity_type == "parent" )
            {
                if ( $manager ) $this->user_model->deny_user_acl($user_id, "Parent User");
                if ( $manager ) $this->user_model->grant_user_acl($user_id, "Parent Manager");
                if ( ! $manager ) $this->user_model->deny_user_acl($user_id, "Parent Manager");
                if ( ! $manager ) $this->user_model->grant_user_acl($user_id, "Parent User");

                $this->user_model->grant_user_acl($user_id, "PII_Download");
            }

			AJAXSuccess("User updated.");


		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
    public function user_delete() {

        // user_delete ( POST )
        //
        // This function handles the request to delete a user.
        // ------------------------------------------------------------

        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required permission.");

            $user_id = getArrayStringValue("user_id", $_POST);
            if ( $user_id == "" ) throw new Exception("Invalid input user_id");

            // disable user.
            $this->user_model->delete_user( $user_id );

            // If you deleted yourself, your out.
            if ( GetSessionValue("user_id") == $user_id )
            {
                redirect( base_url() . "auth/logout" );
                exit;
            }



            AJAXSuccess("User deleted.");

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function user_disable() {

        // user_disable ( POST )
        //
        // This function handles the request to disable a user.
        // ------------------------------------------------------------

        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required permission.");

            $user_id = getArrayStringValue("user_id", $_POST);
            if ( $user_id == "" ) throw new Exception("Invalid input company_id");

            // disable user.
            $this->user_model->disable_user( $user_id );
            AJAXSuccess("User deactivated.");

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function user_enable( ) {

        // user_enable ( POST )
        //
        // This function handles the request to enable a user.
        // ------------------------------------------------------------

        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required permission.");

            $user_id = getArrayStringValue("user_id", $_POST);
            if ( $user_id == "" ) throw new Exception("Invalid input user_id");

            // disable company.
            $this->user_model->enable_user( $user_id );
            AJAXSuccess("User activated.");

        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

    // VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    public function validate_username() {
        $username = GetArrayStringValue("username", $_POST );
        if ( !IsLoggedIn() )
        {
            echo '{ "validation": 0 }';
            return false;
        }

        if (! IsUsernameAvailable($username) ) {
            echo '{ "validation": 0 }';
            return false;
        }
        echo '{ "validation": 1 }';
        return true;
    }

    // RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	public function render_users_table() {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required write permission.");

			$array = array();
			$array['responseText'] = $this->_users_table(GetSessionValue("company_id"), GetSessionValue("companyparent_id"));
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}

	}
	public function render_add_user_form() {
	   try
		{
			// Check method.
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new Exception("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required write permission.");

			// organize inputs.
            $company_id = GetSessionValue("company_id");
            $company_parent_id = GetSessionValue("companyparent_id");

			// validate required inputs.
			if ( $company_id == "" && $company_parent_id == "" ) throw new Exception("Invalid input customer_id");

			$add_form_html = $this->_user_add_form($company_id, $company_parent_id);

			$array = array();
			$array['responseText'] = $add_form_html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
    public function render_edit_user_form($user_id) {

		try
		{
			// Check method.
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new Exception("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required write permission.");

			// organize inputs.
			$user_id = getStringValue($user_id);

			// validate required inputs.
			if ( $user_id == "" ) throw new Exception("Invalid input user_id");

			$edit_form_html = $this->_user_edit_form($user_id);

			$array = array();
			$array['responseText'] = $edit_form_html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}

	}
    public function render_delete_user_form($user_id) {

		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required write permission.");

			// organize inputs.
			$user_id = getStringValue($user_id);

			// validate required inputs.
			if ( $user_id == "" ) throw new Exception("Invalid input user_id");

			$form_html = $this->_user_delete_form($user_id);

			$array = array();
			$array['responseText'] = $form_html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}

	}
    public function render_whoami() {

        try
        {
            // Check method.
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new Exception("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

            // collect our user data and grab the first and last name.
            $user_id = GetSessionValue("user_id");
            $user = $this->user_model->get_user_by_id($user_id);
            $first = getArrayStringValue("first_name", $user);
            $last = getArrayStringValue("last_name", $user);

            // Depending on if the user is a parent or not, grab the company name.
            if ( GetSessionValue("company_id") != "" )
            {
                $company_id = GetSessionValue("company_id");
                $company = $this->company_model->get_company($company_id);
                $company = getArrayStringValue("company", $company);
            }
            else if ( GetSessionValue("companyparent_id") != "" )
            {
                $company_parent_id = GetSessionValue("companyparent_id");
                $parent = $this->CompanyParent_model->get_companyparent($company_parent_id);
                $company = getArrayStringValue("name", $parent);
            }

            // Gender the whoami description.
            $array = array();
            $array['responseText'] = "{$first} {$last}, {$company}";
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }

    }


    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    private function _users_table($company_id, $company_parent_id) {
        $users = array();

        $company_name = "";
        $users = array();

        if ( getStringValue($company_id) != "" ) {
            $users = $this->user_model->get_all_active_users($company_id);
            $company = $this->Company_model->get_company($company_id);
            $company_name = getArrayStringValue("company_name", $company);
        }
        if ( getStringValue($company_parent_id) != "" ) {
            $users = $this->user_model->get_all_users_for_parent($company_parent_id);
            $company = $this->CompanyParent_model->get_companyparent($company_parent_id);
            $company_name = getArrayStringValue("Name", $company);
        }

        $company = $this->Company_model->get_company($company_id);
        $company = getArrayStringValue("company_name", $company);
        return RenderViewAsString("users/users_table", array("data" => $users, "company" => $company_name));
    }
    private function _user_add_form( $company_id, $company_parent_id ) {

        if ( getStringValue($company_id) != "" )
        {
            $entity_id = $company_id;
            $entity_type = "company";
            $manager_desc = "This user is a company manager and can add other users.";
        }
        if ( getStringValue($company_parent_id) != "" ) {
            $entity_id = $company_parent_id;
            $entity_type = "parent";
            $manager_desc = "This user is a parent manager and can add other users and companies.";
        }

        $form = new UIModalForm("add_user_form", "add_user_form", base_url("users/add"));
        $form->setTitle("Add User");
        if ( GetSessionValue("company_id") == "1" )
        {
            $form->setLead("Advice2Pay Administrator");
            $form->setDescription("You are creating a new Advice2Pay team member that will have full administrative rights to the application.  If you are trying to create a user for a company other than Advice2Pay, please change to that customer first.");
        }
        $form->setCollapsable(true);
        $form->addElement($form->emailInput("email_address", "Email Address", null, "Ex: employee@company.com"));
        $form->addElement($form->textInput("first_name", "First Name", null, "Ex: Jane"));
        $form->addElement($form->textInput("last_name", "Last Name", null, "Ex: Smith"));
        if ( GetSessionValue("company_id") != "1" )
        {
            $form->addElement($form->checkbox("manager", "", $manager_desc, false));
            $form->addElement($form->checkbox("onboarding", "", "Notify this user of their new account via email.", true));
        }
        else
        {
            $form->addElement($form->checkbox("", "", "Notify this user of their new account via email.", true, true));
        }
        $form->addElement($form->hiddenInput("entity_id", $entity_id));
        $form->addElement($form->hiddenInput("entity_type", $entity_type));
        $form->addElement($form->submitButton("save_add_btn", "Add User", "btn-primary pull-right"));
        $form->addElement($form->button("cancel_add_btn", "Cancel", "btn-default pull-right"));
        $form_html = $form->render();
        return $form_html;
    }
    private function _user_edit_form( $user_id=null ) {

        if ( $user_id == "" ) return "";

        $email_address = null;
        $first_name = null;
        $last_name = null;
        $is_manager = null;

        $user = $this->user_model->get_user_by_id($user_id);
        if ( ! empty($user) )
        {
            $email_address = getArrayStringValue("email_address", $user);
            $first_name = getArrayStringValue("first_name", $user);
            $last_name = getArrayStringValue("last_name", $user);
            getArrayStringValue("is_manager", $user) == "t" ? $is_manager = true : $is_manager = false;
        }


        $manager_desc = "";
        if ( getArrayStringValue("company_id", $user) != "" )
        {
            $entity_id = getArrayStringValue("company_id", $user);
            $entity_type = "company";
            $manager_desc = "This user is a company manager and can add other users.";
        }
        if ( getArrayStringValue("company_parent_id", $user) != "" ) {
            $entity_id = getArrayStringValue("company_parent_id", $user);
            $entity_type = "parent";
            $manager_desc = "This user is a parent manager and can add other users and companies.";
        }

        $edit_form = new UIModalForm("edit_user_form", "edit_user_form", base_url("users/edit"));
        $edit_form->setTitle("Edit User ( {$email_address} )");
        $edit_form->setCollapsable(true);
        $edit_form->addElement($edit_form->emailInput("email_address", "Email Address", $email_address, "Ex: manager@company.com"));
        $edit_form->addElement($edit_form->textInput("first_name", "First Name", $first_name, "Ex: John"));
        $edit_form->addElement($edit_form->textInput("last_name", "Last Name", $last_name, "Ex: Smith"));

        $login_details = $this->Login_model->get_login_details($user_id);
        $phone_nbr = getArrayStringValue("TwoFactorPhoneNumber", $login_details);
        if ( $phone_nbr !== '' )
        {
            $inline = $edit_form->inlineInput(
                "phone"
                , "Clear"
                , DisplayPhoneNumber($phone_nbr)
                , base_url("users/reset/phone/{$user_id}")
                , "UserPhoneResetSuccess"
                , "SMS Capable Phone Number"
                , "UserPhoneResetFailed"
            );
            $edit_form->addElement($inline);
        }

        if ( GetSessionValue("company_id") != "1" && getStringValue($manager_desc) != "" )
        {
            $edit_form->addElement($edit_form->checkbox("manager", "", $manager_desc, $is_manager));
        }
        $edit_form->addElement($edit_form->hiddenInput("user_id", $user_id));
        $edit_form->addElement($edit_form->hiddenInput("original_email_address", $email_address));
        $edit_form->addElement($edit_form->hiddenInput("entity_id", $entity_id));
        $edit_form->addElement($edit_form->hiddenInput("entity_type", $entity_type));
        $edit_form->addElement($edit_form->submitButton("save_edit_btn", "Update", "btn-primary pull-right"));
        $edit_form->addElement($edit_form->button("cancel_edit_btn", "Cancel", "btn-default pull-right"));
        $edit_form_html = $edit_form->render();

        return $edit_form_html;
    }

    private function _user_delete_form( $user_id=null ) {

        if ( $user_id == "" ) return "";

        $email_address = null;
        $first_name = null;
        $last_name = null;

        $user = $this->user_model->get_user_by_id($user_id);
        if ( ! empty($user) )
        {
            $email_address = getArrayStringValue("email_address", $user);
            $first_name = getArrayStringValue("first_name", $user);
            $last_name = getArrayStringValue("last_name", $user);
        }

        $form = new UIModalForm("delete_user_form", "delete_user_form", base_url("users/delete"));
        $form->setTitle("Delete User ( {$email_address} )");
        $form->addElement($form->htmlView("users/delete_warning", array()));
        $form->addElement($form->emailInput("email_address", "Email Address", $email_address, "Ex: manager@company.com", array(), true));
        $form->addElement($form->textInput("first_name", "First Name", $first_name, "Ex: John", array(), true));
        $form->addElement($form->textInput("last_name", "Last Name", $last_name, "Ex: Smith", array(), true));
        $form->addElement($form->hiddenInput("user_id", $user_id));
        $form->addElement($form->submitButton("save_edit_btn", "Delete", "btn-primary pull-right"));
        $form->addElement($form->button("cancel_edit_btn", "Cancel", "btn-default pull-right"));
        $form_html = $form->render();

        return $form_html;
    }

    /**
     * _assignment_table
     *
     * This function will return the HTML for the list of possible
     * assignments associated with the specified user.
     *
     * @param $user_id
     * @return string|void
     */
    private function _assignment_table($user_id ) {

	    $parentcompany_id = GetSessionValue("companyparent_id");

        // Collect all of the users for a parent company denoted with the ResponsibleFor flag
        // indicating the individual users relationship with the child company.
        $companies = $this->Company_model->select_companies_by_assigned_user($parentcompany_id, $user_id);

        // Get the assigned table.
        $view_array = array();
        $view_array = array_merge($view_array, array("companies" => $companies));
        $view_array = array_merge($view_array, array("user_id" => $user_id));
        return RenderViewAsString("users/assignment_table", $view_array);

    }
}
