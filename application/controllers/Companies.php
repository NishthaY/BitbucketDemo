<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Companies extends SecureController {


	public function __construct(){
		parent::__construct();
		$this->load->model('Company_model','company_model',true);
		$this->load->model('User_model','user_model',true);
		$this->load->model('Queue_model','queue_model',true);
		$this->load->helper('Users_helper');
		$this->load->helper('Companies_helper');
		$this->load->library('form_validation');
	}


    /**
     * features
     *
     * Display UI that will list all features that can be toggled on or off
     * for a specific company.  This feature has restricted access is not
     * available to clients.
     *
     * @param $company_id
     */
    public function features($company_id )
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            $company = $this->Company_model->get_company($company_id);
            $company_name = GetArrayStringValue("company_name", $company);

            // Form header
            $header = new UIFormHeader($company_name);
            $header->addLink("Company List", base_url("companies/manage"));
            $header->addLink("Features");
            $page_header = $header->render();

            $widgets = array();
            $features = $this->Feature_model->get_company_features($company_id);
            foreach($features as $feature)
            {
                $feature_code = GetArrayStringValue("Code", $feature);
                $target = GetArrayStringValue("Target", $feature);
                $target_type = GetArrayStringValue("TargetType", $feature);
                GetArrayStringValue("Targetable", $feature) === 't' ? $targetable = true : $targetable = false;

                // If you have a feature that is targetable but has no target, do not show it.  That is the
                // record that allows you to CREATE the targetable features.
                if ( $targetable && $target === '' ) continue;

                $widget_name = "company_feature_control_{$company_id}_{$feature_code}";
                if ( $targetable ) $widget_name = "company_feature_control_{$company_id}_{$feature_code}_{$target_type}_{$target}";

                $href = base_url("companies/widget/feature/{$company_id}/{$feature_code}");
                if ( $targetable ) $href = base_url("companies/widget/feature/{$company_id}/{$feature_code}/{$target_type}/{$target}");


                $widget = new UIWidget($widget_name);
                $widget->setBody( $this->_feature_control($company_id, $feature_code, $target_type, $target) );
                $widget->setHref($href);
                $widget = $widget->render();

                $widgets[] = $widget;
            }

            // Add Beneficiary Mapping Widget
            $beneficiary_mapping_widget = new UIWidget("beneficiary_mapping_widget");
            $beneficiary_mapping_widget->setHref( base_url("companies/widget/beneficiary_mapping/{$company_id}/TARGETTYPE/TARGET") );
            $beneficiary_mapping_widget = $beneficiary_mapping_widget->render();

            // Add Targetable Feature Widget
            $targetable_feature_widget = new UIWidget("targetable_feature_widget");
            $targetable_feature_widget->setHref( base_url("companies/widget/targetable_feature/{$company_id}") );
            $targetable_feature_widget = $targetable_feature_widget->render();

            // File Transfer Feature Form
            $file_transfer_form_widget = new UIWidget("file_transfer_widget");
            $file_transfer_form_widget->setBody( $this->_file_transfer_form($company_id) );
            $file_transfer_form_widget->setHref( base_url("companies/widget/file_transfer/{$company_id}") );
            $file_transfer_form_widget = $file_transfer_form_widget->render();

            // Commission Tracking Feature Form
            $commission_tracking_form_widget = new UIWidget("commission_tracking_widget");
            $commission_tracking_form_widget->setBody( $this->_commission_tracking_form($company_id) );
            $commission_tracking_form_widget->setHref( base_url("companies/widget/commission_tracking/{$company_id}") );
            $commission_tracking_form_widget = $commission_tracking_form_widget->render();

            // Column Normalization Feature Form
            $column_normalization_widget = new UIWidget("column_normalization_widget");
            $column_normalization_widget->setHref( base_url("companies/widget/column_normalization/{$company_id}/TARGETTYPE/TARGET") );
            $column_normalization_widget = $column_normalization_widget->render();

            // Default Carrier Feature Form
            $default_carrier_widget = new UIWidget("default_carrier_widget");
            $default_carrier_widget->setHref( base_url("companies/widget/default_carrier/{$company_id}") );
            $default_carrier_widget = $default_carrier_widget->render();

            // Default Plan Feature Form
            $default_plan_widget = new UIWidget("default_plan_widget");
            $default_plan_widget->setHref( base_url("companies/widget/default_plan/{$company_id}") );
            $default_plan_widget = $default_plan_widget->render();

            // Default Clarifications Feature Form
            $default_clarifications_widget = new UIWidget("default_clarifications_widget");
            $default_clarifications_widget->setHref( base_url("companies/widget/default_clarifications/{$company_id}") );
            $default_clarifications_widget = $default_clarifications_widget->render();

            $view_array = array();
            $view_array = array_merge($view_array, array("page_header" => $page_header));
            $view_array = array_merge($view_array, array("widgets" => $widgets));
            $view_array = array_merge($view_array, array("file_transfer_widget" => $file_transfer_form_widget));
            $view_array = array_merge($view_array, array("commission_tracking_widget" => $commission_tracking_form_widget));
            $view_array = array_merge($view_array, array("column_normalization_widget" => $column_normalization_widget));
            $view_array = array_merge($view_array, array("default_carrier_widget" => $default_carrier_widget));
            $view_array = array_merge($view_array, array("default_plan_widget" => $default_plan_widget));
            $view_array = array_merge($view_array, array("targetable_feature_widget" => $targetable_feature_widget));
            $view_array = array_merge($view_array, array("beneficiary_mapping_widget" => $beneficiary_mapping_widget));
            $view_array = array_merge($view_array, array("default_clarifications_widget" => $default_clarifications_widget));
            $view_array = array_merge($view_array, array("company_id" => $company_id));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("companies/company_js_assets")));
            $page_template = array_merge($page_template, array("view" => "companies/features"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied($e->getMessage()); }
        catch( Exception $e ) { Error404($e->getMessage()); }
    }

    /**
     * assign_responsibility
     *
     * This function accepts POST requests and will assign a
     * Parent User responsibility for a company.  When granted
     * this assignment, the user inherits Manager permissions
     * for the company.
     *
     */
    public function assign_responsibility( )
    {

        try {
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new SecurityException("Unexpected request method.");
            if (getArrayStringValue("ajax", $_POST) != "1") throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if (!IsLoggedIn()) throw new SecurityException("You must be logged into access this function.");
            if (!IsAuthenticated("parent_company_write")) throw new SecurityException("Missing required permission.");

            // Must have a company id.
            $company_id = getArrayStringValue("company_id", $_POST);
            if ($company_id == "") throw new Exception("Invalid input company_id");

            $user_id = getArrayStringValue("user_id", $_POST);
            if ($user_id == "") throw new Exception("Invalid input user_id");

            // If we have a companyparent_id, then they may only enable companies up to the seat value.
            $company_parent_id = GetSessionValue("companyparent_id");
            if ($company_parent_id != "") {
                $this->User_model->insert_user_is_responsible_for_company($user_id, $company_id, $company_parent_id);
                $this->User_model->grant_user_acl($user_id, "Manager", "company", $company_id);
                $this->User_model->grant_user_acl($user_id, "PII Download", "company", $company_id);
            }

            AJAXSuccess("");

        } catch (UIException $e) {
            AjaxDanger($e->getMessage());
        } catch (SecurityException $e) {
            AccessDenied();
        } catch (Exception $e) {
            Error404();
        }
    }
    /**
     * unassign_responsibility
     *
     * This function accepts POST requests and will unassign a
     * Parent User responsibility from a company.  When this
     * assignment is removed, the user will no longer inherit
     * Manager permissions for the company.
     */
    public function unassign_responsibility( )
    {

        try
        {
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new SecurityException("Unexpected request method.");
            if (getArrayStringValue("ajax", $_POST) != "1") throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if (!IsLoggedIn()) throw new SecurityException("You must be logged into access this function.");
            if (!IsAuthenticated("parent_company_write")) throw new SecurityException("Missing required permission.");

            // Must have a company id.
            $company_id = getArrayStringValue("company_id", $_POST);
            if ($company_id == "") throw new Exception("Invalid input company_id");

            $user_id = getArrayStringValue("user_id", $_POST);
            if ($user_id == "") throw new Exception("Invalid input user_id");

            // If we have a companyparent_id, then they may only enable companies up to the seat value.
            $company_parent_id = GetSessionValue("companyparent_id");
            if ($company_parent_id != "") {
                $this->User_model->delete_user_is_responsible_for_company($user_id, $company_id, $company_parent_id);
                $this->User_model->deny_user_acl($user_id, "Manager", "company", $company_id);
                $this->User_model->deny_user_acl($user_id, "PII Download", "company", $company_id);
            }

            AJAXSuccess("");

        } catch (UIException $e) {
            AjaxDanger($e->getMessage());
        } catch (SecurityException $e) {
            AccessDenied();
        } catch (Exception $e) {
            Error404();
        }
    }

    /**
     * user_assignment
     *
     * This function renders the UI for the user assignment page.
     * This page allows a Parent Manager to assign users with the
     * role of Parent User responsibility for one of their child
     * companies.
     *
     * @param $company_id
     */
    public function user_assignment($company_id)
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            $company_parent_id = GetSessionValue("companyparent_id");


            // Company Data
            $company = $this->Company_model->get_company($company_id);
            $company_name = GetArrayStringValue("company_name", $company);


            // Assignment Widget
            $assignment_widget = new UIWidget("assignment_widget");
            $assignment_widget->setBody( $this->_assignment_table($company_parent_id, $company_id) );
            $assignment_widget->setHref(base_url("companies/widget/assignments/{$company_id}"));
            $assignment_widget = $assignment_widget->render();


            // Get the assigned table.
            $view_array = array();
            $view_array = array_merge($view_array, array("company_id" => $company_id));
            $assigned = RenderViewAsString("companies/assignment_table", $view_array);

            // Get the inherited table.
            // Pull all user for the parent and then filter out only the manager.
            $users = $this->User_model->get_all_users_for_parent($company_parent_id);
            $managers = array_filter(
                $users,
                function($user, $index)
                {
                    $is_manager = GetArrayStringValue('is_manager', $user);
                    $is_enabled = GetArrayStringValue('enabled', $user);
                    if ( $is_manager === 't' && $is_enabled == 't' ) return TRUE;
                    return FALSE;
                },
                ARRAY_FILTER_USE_BOTH);
            $view_array = array();
            $view_array = array_merge($view_array, array("users" => $managers));
            $view_array = array_merge($view_array, array("company_id" => $company_id));
            $inherited = RenderViewAsString("companies/inherited_table", $view_array);


            // Get the Company Users table.
            // Pull all the users for the company.
            $company_users = $this->User_model->get_all_active_users($company_id);
            $view_array = array();
            $view_array = array_merge($view_array, array("users" => $company_users));
            $view_array = array_merge($view_array, array("company_id" => $company_id));
            $all_users = RenderViewAsString("companies/company_users_table", $view_array);

            // Form header
            $header = new UIFormHeader("User Assignment");
            $header->addLink("Company List", base_url('companies/manage'));
            $header->addLink($company_name);
            $header = $header->render();

            $view_array = array();
            $view_array = array_merge($view_array, array("header_html" => $header));
            $view_array = array_merge($view_array, array("assigned_users_html" => $assigned));
            $view_array = array_merge($view_array, array("inherited_users_html" => $inherited));
            $view_array = array_merge($view_array, array("all_users_html" => $all_users));
            $view_array = array_merge($view_array, array("assignment_widget" => $assignment_widget));
            $view_array = array_merge($view_array, array("company_name" => GetArrayStringValue("company_name", $company)));
            $view_array = array_merge($view_array, array("address_line1" => GetArrayStringValue("company_address", $company)));
            $view_array = array_merge($view_array, array("city" => GetArrayStringValue("company_city", $company)));
            $view_array = array_merge($view_array, array("state" => GetArrayStringValue("company_state", $company)));
            $view_array = array_merge($view_array, array("zip" => GetArrayStringValue("company_postal", $company)));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("companies/company_js_assets")));
            $page_template = array_merge($page_template, array("view" => "companies/user_assignment"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
	public function company_list() {

		// company_list ( GET )
		//
		// Create and Manage companies.
		// ------------------------------------------------------------

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("parent_company_read") ) throw new SecurityException("Missing required permission.");

			// Form header
			$header = new UIFormHeader("Companies");
			if ( IsAuthenticated("parent_company_write") )
            {
                $header->addButton($header->button("add_company", "Add Company", "add_company_form" ));
            }
            $header->addLink("Company List");
			$header_html = $header->render();

			// Add Company Form
			$add_form_widget = new UIWidget("add_company_widget");
			$add_form_widget->setHref(base_url("companies/widget/add"));
			$add_form_widget = $add_form_widget->render();

			// Edit Company Form
			$edit_form_widget = new UIWidget("edit_company_widget");
			$edit_form_widget->setHref(base_url("companies/widget/edit"));
			$edit_form_widget = $edit_form_widget->render();

			// Change To Company Form
			$changeto_form_widget = new UIWidget("changeto_company_widget");
			$changeto_form_widget->setHref(base_url("companies/widget/changeto"));
			$changeto_form_widget = $changeto_form_widget->render();

			// Rollback Company Import Form
			$rollback_form_widget = new UIWidget("rollback_company_widget");
			$rollback_form_widget = $rollback_form_widget->render();

			// Company data widget.
			$widget = new UIWidget("companies_widget");
			$widget->setBody($this->_companies_table());
			$widget->setHref(base_url("companies/widget/list"));
			$widget->setCallback("InitCompanyTable");
			$company_table_html = $widget->render();

			$view_array = array();
			$view_array = array_merge($view_array, array("companies_table" => $company_table_html));
			$view_array = array_merge($view_array, array("add_form" => $add_form_widget));
			$view_array = array_merge($view_array, array("edit_form" => $edit_form_widget));
			$view_array = array_merge($view_array, array("changeto_form" => $changeto_form_widget));
			$view_array = array_merge($view_array, array("rollback_form" => $rollback_form_widget));
			$view_array = array_merge($view_array, array("form_header" => $header_html));

			$page_template = array();
			$page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("companies/company_js_assets")));
			$page_template = array_merge($page_template, array("view" => "companies/companies"));
			$page_template = array_merge($page_template, array("view_array" => $view_array));
			RenderView('templates/template_body_default', $page_template);

		}
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }

	}

	// POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+

    public function beneficiary_mapping_save()
    {
        try
        {
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new SecurityException("Unexpected request method.");
            if (getArrayStringValue("ajax", $_POST) != "1") throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if (!IsLoggedIn()) throw new SecurityException("You must be logged into access this function.");
            if (!IsAuthenticated("")) throw new SecurityException("Missing required permission.");

            // Collect our data.
            $identifier = GetArrayStringValue('identifier', $_POST);
            $identifier_type = GetArrayStringValue('identifier_type', $_POST);
            $feature_code = GetArrayStringValue('feature_code', $_POST);
            $target_type = GetArrayStringValue('target_type', $_POST);
            $target = GetArrayStringValue('target', $_POST);

            // Validate we have enough data.
            if ( $identifier === '' ) throw new UIException("Missing required input identifier.");
            if ( $identifier_type === '' ) throw new UIException("Missing required input identifier type.");
            if ( $feature_code === '' ) throw new UIException("Missing required input feature_code.");
            if ( $target_type === '' ) throw new UIException("Missing required input target_type.");
            if ( $target === '' ) throw new UIException("Unable to find target.");

            // Validate our identifier.
            $valid_identifier = false;
            if ( $identifier_type === 'company' ) $valid_identifier = true;
            else if ( $identifier_type === 'companyparent' ) $valid_identifier = true;
            if ( ! $valid_identifier ) throw new UIException("Unknown identifier type.");

            $tokens = array();
            if ( isset($_POST['token'])) $tokens = $_POST['token'];
            $this->Mapping_model->remove_beneficiary_maps($identifier, $identifier_type, $target);
            foreach($tokens as $token)
            {
                $description = trim($token);
                $normalize = trim(strtoupper($token));
                $this->Mapping_model->add_beneficiary_map($identifier, $identifier_type, $normalize, $description, $target);
            }

            AJAXSuccess("", base_url('companies/features/'.$identifier));

        } catch (UIException $e) {
            AjaxDanger($e->getMessage());
        } catch (SecurityException $e) {
            AccessDenied();
        } catch (Exception $e) {
            Error404();
        }
    }
    /**
     * feature_add
     *
     * This POST function will add a new targetable feature to the specified
     * company.
     *
     */
    public function feature_add()
    {
        try
        {
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new SecurityException("Unexpected request method.");
            if (getArrayStringValue("ajax", $_POST) != "1") throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if (!IsLoggedIn()) throw new SecurityException("You must be logged into access this function.");
            if (!IsAuthenticated("")) throw new SecurityException("Missing required permission.");

            // Collect our data.
            $identifier = GetArrayStringValue('identifier', $_POST);
            $identifier_type = GetArrayStringValue('identifier_type', $_POST);
            $feature_code = GetArrayStringValue('feature_code', $_POST);
            $target_type = GetArrayStringValue('target_type', $_POST);
            $mapping_column = GetArrayStringValue('mapping_column', $_POST);

            // Validate we have enough data.
            if ( $identifier === '' ) throw new UIException("Missing required input identifier.");
            if ( $identifier_type === '' ) throw new UIException("Missing required input identifier type.");
            if ( $feature_code === '' ) throw new UIException("Missing required input feature_code.");
            if ( $target_type === '' ) throw new UIException("Missing required input target_type.");
            if ( strtolower($target_type) === 'mapping_column') $target = $mapping_column;
            if ( $target === '' ) throw new UIException("Unable to find target.");

            // Validate our identifier.
            $valid_identifier = false;
            if ( $identifier_type === 'company' ) $valid_identifier = true;
            else if ( $identifier_type === 'companyparent' ) $valid_identifier = true;
            if ( ! $valid_identifier ) throw new UIException("Unknown identifier type.");

            // Does the feature exist already?
            $exists = $this->Feature_model->does_feature_exist_for_identifier( $identifier, $identifier_type, $feature_code, $target_type, $target );
            if ( $exists ) throw new UIException("That feature already exists and cannot be added again.");

            // Once a feature has been added, we will need to refresh the page that
            // displays the list.  Set the refresh URL now.
            $refresh_url = "";
            if ( $identifier_type === 'company' ) $refresh_url = base_url('companies/features/' . $identifier);
            else if ( $identifier_type === 'companyparent' ) $refresh_url = base_url('parents/features/' . $identifier);

            // Create the feature.
            $this->Feature_model->insert_feature_for_identifier($identifier, $identifier_type, $feature_code, $target, false);
            AJAXSuccess("", base_url('companies/features/'.$identifier));

        } catch (UIException $e) {
            AjaxDanger($e->getMessage());
        } catch (SecurityException $e) {
            AccessDenied();
        } catch (Exception $e) {
            Error404();
        }
    }
    /**
     * feature_remove
     *
     * This POST function will remove an existing targetable feature from the specified
     * company.
     *
     */
    public function feature_remove()
    {
        try
        {
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new SecurityException("Unexpected request method.");
            if (getArrayStringValue("ajax", $_POST) != "1") throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if (!IsLoggedIn()) throw new SecurityException("You must be logged into access this function.");
            if (!IsAuthenticated("")) throw new SecurityException("Missing required permission.");

            // Collect our data.
            $identifier = GetArrayStringValue('identifier', $_POST);
            $identifier_type = GetArrayStringValue('identifier_type', $_POST);
            $feature_code = GetArrayStringValue('feature_code', $_POST);
            $target_type = GetArrayStringValue('target_type', $_POST);
            $target = GetArrayStringValue('target', $_POST);

            // Validate we have enough data.
            if ( $identifier === '' ) throw new UIException("Missing required input identifier.");
            if ( $identifier_type === '' ) throw new UIException("Missing required input identifier type.");
            if ( $feature_code === '' ) throw new UIException("Missing required input feature_code.");
            if ( $target_type === '' ) throw new UIException("Missing required input target_type.");
            if ( $target === '' ) throw new UIException("Unable to find target.");

            // Validate our identifier.
            $valid_identifier = false;
            if ( $identifier_type === 'company' ) $valid_identifier = true;
            else if ( $identifier_type === 'companyparent' ) $valid_identifier = true;
            if ( ! $valid_identifier ) throw new UIException("Unknown identifier type.");

            // Does the feature exist already?
            $exists = $this->Feature_model->does_feature_exist_for_identifier( $identifier, $identifier_type, $feature_code, $target_type, $target );
            if ( ! $exists ) throw new UIException("That feature does not exist and cannot be removed.");

            // Once a feature has been added, we will need to refresh the page that
            // displays the list.  Set the refresh URL now.
            $refresh_url = "";
            if ( $identifier_type === 'company' ) $refresh_url = base_url('companies/features/' . $identifier);
            else if ( $identifier_type === 'companyparent' ) $refresh_url = base_url('parents/features/' . $identifier);

            // Create the feature.
            $this->Feature_model->delete_targetable_feature($identifier, $identifier_type, $feature_code, $target);
            AJAXSuccess("", base_url('companies/features/'.$identifier));

        } catch (UIException $e) {
            AjaxDanger($e->getMessage());
        } catch (SecurityException $e) {
            AccessDenied();
        } catch (Exception $e) {
            Error404();
        }
    }

    /**
     * toggle_feature
     *
     * This POST method will capture a request to toggle the current state of
     * a feature for a company.  This function has restricted access.
     *
     * @param $feature_code
     * @param $company_id
     */
    public function toggle_feature($feature_code, $company_id, $target_type='', $target='' )
    {
        try {
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new SecurityException("Unexpected request method.");
            if (getArrayStringValue("ajax", $_POST) != "1") throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if (!IsLoggedIn()) throw new SecurityException("You must be logged into access this function.");
            if (!IsAuthenticated("")) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($feature_code) === '' ) throw new UIException("Missing required input feature_code.");
            if ( GetStringValue($company_id) === '' ) throw new UIException("Missing required input company_id.");

            $feature = $this->Feature_model->get_company_feature($company_id, $feature_code, $target_type, $target);
            if ( empty($feature_code) ) throw new UIException("Unable to find that feature.");

            $enabled = GetArrayStringValue("Enabled", $feature);
            if ( $enabled === "t" )
            {
                if ($feature_code === 'COLUMN_NORMALIZATION_REGEX') $this->_disable_column_normalization($company_id, $feature);
                $this->Feature_model->disable_company_feature($company_id, $feature_code, $target_type, $target);

            }
            elseif ( $enabled === 'f' )
            {
                if ($feature_code === 'COLUMN_NORMALIZATION_REGEX') $this->_enable_column_normalization($company_id, $feature);
                $this->Feature_model->enable_company_feature($company_id, $feature_code, $target_type, $target);

            }
            else
            {
                throw new UIException("Unexpected data found in feature.");
            }

            AJAXSuccess("");

        } catch (UIException $e) {
            AjaxDanger($e->getMessage());
        } catch (SecurityException $e) {
            AccessDenied();
        } catch (Exception $e) {
            Error404();
        }
    }
    public function default_carrier_save()
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            // Validate our inputs.
            $this->form_validation->set_rules('default_carrier_code','default carrier code','required');
            $this->form_validation->set_rules('company_id', 'identifier', 'required|numeric');

            if ( $this->form_validation->run() == FALSE )
            {
                $errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
                if ( $errors == "" ) $errors = "Invalid or missing inputs.";
                throw new UIException($errors);
            }

            $company_id = GetArrayStringValue('company_id', $_POST);
            $carrier_code = GetArrayStringValue('default_carrier_code', $_POST);

            SavePreference($company_id, 'company', 'carrier', 'default_carrier_code', $carrier_code);
            AJAXSuccess("Configuration saved.");
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function default_clarifications_save()
    {
        try {
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new SecurityException("Unexpected request method.");
            if (getArrayStringValue("ajax", $_POST) != "1") throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if (!IsLoggedIn()) throw new SecurityException("You must be logged into access this function.");
            if (!IsAuthenticated("")) throw new SecurityException("Missing required permission.");

            // Collect our data.
            $identifier = GetArrayStringValue('identifier', $_POST);
            $identifier_type = GetArrayStringValue('identifier_type', $_POST);
            $clarification_type = GetArrayStringValue('clarification_type', $_POST);

            // Validate we have enough data.
            if ($identifier === '') throw new UIException("Missing required input identifier.");
            if ($identifier_type === '') throw new UIException("Missing required input identifier type.");
            if ($clarification_type === '') throw new UIException("Missing required input clarification_type.");

            // Validate our identifier.
            $valid_identifier = false;
            if ($identifier_type === 'company') $valid_identifier = true;
            else if ($identifier_type === 'companyparent') $valid_identifier = true;
            if (!$valid_identifier) throw new UIException("Unknown identifier type.");

            $this->load->helper('clarifications');
            SaveClarificationType($identifier, $identifier_type, $clarification_type);

            AJAXSuccess("Settings saved.");

        } catch (UIException $e) {
            AjaxDanger($e->getMessage());
        } catch (SecurityException $e) {
            AccessDenied();
        } catch (Exception $e) {
            Error404();
        }
    }
    public function default_plan_save()
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            // Validate our inputs.
            $this->form_validation->set_rules('identifier', 'identifier', 'required|numeric');
            $this->form_validation->set_rules('identifier_type', 'identifier_type', 'required');

            if ( $this->form_validation->run() == FALSE )
            {
                $errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
                if ( $errors == "" ) $errors = "Invalid or missing inputs.";
                throw new UIException($errors);
            }

            $identifier = GetArrayStringValue('identifier', $_POST);
            $identifier_type = GetArrayStringValue('identifier_type', $_POST);
            $plan_code = GetArrayStringValue('plan_code', $_POST);

            if ( $plan_code === '' )
            {
                RemovePreference($identifier, $identifier_type, 'plan', 'default_plan_code');
            }
            else
            {
                SavePreference($identifier, $identifier_type, 'plan', 'default_plan_code', $plan_code);
            }
            AJAXSuccess("Configuration saved.");
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function column_normalization_save()
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            $company_id = GetArrayStringValue("company_id", $_POST);
            $target = GetArrayStringValue("target", $_POST);
            $target_type = GetArrayStringValue("target_type", $_POST);
            $pattern = GetArrayStringValue("pattern", $_POST);
            $replace = GetArrayStringValue("replace", $_POST);
            $description = GetArrayStringValue("description", $_POST);

            if ( $company_id === '' ) throw new UIException('Missing required input companyparent_id');
            if ( $target === '' ) throw new UIException('Missing required input target');
            if ( $target_type === '' ) throw new UIException('Missing required input target_type');

            // Save the information in preferences.
            $this->Company_model->save_company_preference(  $company_id, "column_normalization", "{$target_type}_{$target}_pattern", $pattern );
            $this->Company_model->save_company_preference(  $company_id, "column_normalization", "{$target_type}_{$target}_replace", $replace );
            $this->Company_model->save_company_preference(  $company_id, "column_normalization", "{$target_type}_{$target}_description", $description );

            // If the feature is currently enabled, then not only do we need to save
            // the feature properties, but we also need to enable the feature in the application too.
            $feature = $this->Feature_model->get_company_feature($company_id, 'COLUMN_NORMALIZATION_REGEX', $target_type, $target);
            $enabled = GetArrayStringValue("Enabled", $feature);
            if ( $enabled === "t" ) {
                $this->_enable_column_normalization($company_id, $feature);
            }

            $payload = array();
            $payload['TargetType'] = $target_type;
            $payload['Target'] = $target;
            $payload['Pattern'] = $pattern;
            $payload['Replace'] = $replace;
            $payload['Description'] = $description;
            AuditIt("Custom normalization settings updated.", $payload, GetSessionValue('user_id'), null, $company_id);

            AJAXSuccess("Configuration saved.");


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function commission_tracking_save()
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            $commission_type = GetArrayStringValue("commission_type", $_POST);
            $company_id = GetArrayStringValue("company_id", $_POST);
            $oldest_effective_date = GetArrayStringValue("oldest_effective_date", $_POST);

            $commission_effective_date_type = 'RECENT_TIER_CHANGE';
            if ( $oldest_effective_date == 'on' ) $commission_effective_date_type = "OLDEST_LIFE_PLAN_EFFECTIVE_DATE";

            if ( $commission_type === "" ) throw new UIException("Missing required input commission type.");
            if ( $company_id === "" ) throw new UIException("Missing required input company id.");

            $this->Company_model->save_company_preference(  $company_id, "commission_tracking", "commission_type", $commission_type );
            $this->Company_model->save_company_preference(  $company_id, "commission_tracking", "commission_effective_date_type", $commission_effective_date_type );

            $payload = array();
            $payload['CommissionType'] = $commission_type;
            $payload['CommissionEffectiveDateType'] = $commission_effective_date_type;
            AuditIt("Commission tracking settings updated.", $payload, GetSessionValue('user_id'), $company_id);

            AJAXSuccess("Configuration saved.");


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    /**
     *  file_transfer_save
     *
     *  This function will save the information needed to transfer finalized
     *  reports to a CompanyParent.
     *
     */
    public function file_transfer_save()
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            // Validate our inputs.
            $this->form_validation->set_rules('hostname','hostname','required');
            $this->form_validation->set_rules('username','username','required');
            $this->form_validation->set_rules('password','password','required');
            $this->form_validation->set_rules('destination','destination path','required');
            $this->form_validation->set_rules('port','port','required|numeric');
            $this->form_validation->set_rules('company_id', 'identifier', 'required|numeric');

            if ( $this->form_validation->run() == FALSE )
            {
                $errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
                if ( $errors == "" ) $errors = "Invalid or missing inputs.";
                throw new UIException($errors);
            }

            $company_id = getArrayStringValue("company_id", $_POST);
            $hostname = trim(getArrayStringValue("hostname", $_POST));
            $username = trim(getArrayStringValue("username", $_POST));
            $password = trim(getArrayStringValue("password", $_POST));
            $destination = trim(getArrayStringValue("destination", $_POST));
            $port = trim(getArrayStringValue("port", $_POST));
            $ssh_key = trim(getArrayStringValue("ssh_key", $_POST));

            $encryption_key = GetCompanyEncryptionKey($company_id);
            $encrypted_password = A2PEncryptString($password, $encryption_key);
            $encrypted_ssh_key = A2PEncryptString($ssh_key, $encryption_key);

            $this->FileTransfer_model->upsert_company_file_transfer($company_id, $hostname, $username, $destination, $port, $encrypted_password, $encrypted_ssh_key);

            AJAXSuccess("Configuration saved.");


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
	public function company_add() {

		// company_add ( POST )
		//
		// This function handles the user request to add a company.
		// ------------------------------------------------------------

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");
			if ( APP_NAME === 'a2p-prodcopy' ) throw new Exception("Company create not allowed in PRODCOPY.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required permission.");


			// Validate our inputs.
			$this->form_validation->set_rules('company_name','company name','required');
			$this->form_validation->set_rules('company_address','company address','required');
			$this->form_validation->set_rules('company_city','company city','required');
			$this->form_validation->set_rules('company_state','company state','required');
			$this->form_validation->set_rules('company_postal','company postal','required');

			$validate_user = false;
			if ( GetArrayStringValue("email_address", $_POST) !== '' ) $validate_user = true;
            if ( GetArrayStringValue("first_name", $_POST) !== '' ) $validate_user = true;
            if ( GetArrayStringValue("last_name", $_POST) !== '' ) $validate_user = true;
			if ( $validate_user )
            {
                $this->form_validation->set_rules('email_address','email address','required');
                $this->form_validation->set_rules('first_name','first name','required');
                $this->form_validation->set_rules('last_name','last name','required');
            }


			// Validate the form.
			if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}

			// Clean our inputs.
			$company_name = trim(getArrayStringValue("company_name", $_POST));
			$company_address = trim(getArrayStringValue("company_address", $_POST));
			$company_city = trim(getArrayStringValue("company_city", $_POST));
			$company_state = trim(getArrayStringValue("company_state", $_POST));
			$company_postal = trim(getArrayStringValue("company_postal", $_POST));
			$company_id = trim(getArrayStringValue("company_id", $_POST));
			$email_address = trim(getArrayStringValue("email_address", $_POST));
			$first_name = trim(getArrayStringValue("first_name", $_POST));
			$last_name = trim(getArrayStringValue("last_name", $_POST));
			$company_parent_id = trim(getArrayStringValue("company_parent_id", $_POST));

			// The email address must be available to continue.
			if ( $email_address !== '' && ! IsUsernameAvailable( $email_address ) )
			{
				throw new UIException("Email address already in use.");
			}

			// The company name must be available to continue.
			if ( ! IsCompanyNameAvailable( $company_name ) )
			{
				throw new UIException("Business with that name already in use.");
			}
			if ( ! IsCompanyParentNameAvailable( $company_name ) )
			{
				throw new UIException("Business with that name already in use.");
			}

			// Companies can either be added by "Advice2Pay (1)" or a parent.  Create our
			// entity_type and entity_id values, if needed, so we can do a final security check.
			$entity_type = "company";
			$entity_id = "1";
			if ( $company_parent_id != "" ) $entity_type = "parent";
			if ( $company_parent_id != "") $entity_id = $company_parent_id;

			// Check to make sure the entity the user is reporting matches
			// their session.
			if ( $entity_type == "company" && $entity_id != GetSessionValue("company_id") ) throw new SecurityException("User does not have permission to add users for requested company.");
			if ( $entity_type == "parent" && $entity_id != GetSessionValue("companyparent_id") ) throw new SecurityException("User does not have permission to add users for requested parent.");

			// Create a new company using the API.
            $this->load->model('api/APICompany_model', 'APICompany_model');
            $inputs = array();
            $inputs['name'] = $company_name;
            $inputs['address'] = $company_address;
            $inputs['city'] = $company_city;
            $inputs['state'] = $company_state;
            $inputs['postal'] = $company_postal;
            if ( $entity_type === 'parent' )
            {
                $inputs['parent_identifier'] = $company_parent_id;
                $inputs['parent_identifier_type'] = 'companyparent';
            }

			$message = $this->APICompany_model->company_create($inputs);
            if ( ! $message->status && $message->code === 500 ) throw new Exception($message->message);
            if ( ! $message->status && $message->code !== 500 ) throw new UIException($message->message);
            $company_id = GetArrayStringValue('company_id', $message->results);


			// If we have an email address, then we are going to create our first
            // user at the same time.
			if ( $email_address !== '' )
            {
                // Create user.
                $password = GenerateWeakPassword();
                $this->user_model->create_user( $email_address, $first_name, $last_name, $password );

                // Collect info about the new user.
                $new_user = $this->user_model->get_user( $email_address );
                $new_user_id = getArrayStringValue("user_id", $new_user);

                // Link user to company.
                if ( ! $this->user_model->is_user_linked_to_company( $new_user_id, $company_id ) )
                {
                    $this->user_model->link_user_to_company( $new_user_id, $company_id );
                }

                // Grant user company_manager rights.
                $this->user_model->grant_user_acl($new_user_id, "Manager");
                $this->user_model->grant_user_acl($new_user_id, "PII Download");


                // Enable the user.
                $this->user_model->enable_user( $new_user_id );

                // Send onboarding email.
                if ( getArrayStringValue("onboarding", $_POST) == "on" )
                {
                    SendWelcomeEmail($new_user_id, $password, $company_name);
                }
            }


			// COMPANY FEATURES
            // If this company belongs to a parent, scan the parent for "enabled" parent features
            // that are applied to the child companies.  Create Company features that mimic the
            // parent features in that scenario.
            if ( $entity_type == "parent" )
            {
                if ( $this->company_model->is_company_linked_to_parent( $company_id, $entity_id ) )
                {
                    $parent_features = $this->Feature_model->get_companyparent_features($entity_id);
                    foreach($parent_features as $feature)
                    {
                        $feature_code = GetArrayStringValue('Code', $feature);
                        $feature_type = $this->Feature_model->get_feature_type($feature_code);
                        if ( $feature_type === 'company feature with parent override' )
                        {
                            GetArrayStringValue('Enabled', $feature) === 't' ? $enabled = true : $enabled = false;
                            if ( $enabled )
                            {
                                $target = GetArrayStringValue('Target', $feature);
                                $target_type = GetArrayStringValue('TargetType', $feature);
                                $this->Feature_model->enable_company_feature($company_id, $feature_code, $target_type, $target);
                            }
                        }
                    }
                }
            }


			AJAXSuccess("Company created.");


		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	public function company_edit() {

		// company_edit ( POST )
		//
		// This function handles the user request to edit a company.
		// ------------------------------------------------------------

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            $company_id = trim(getArrayStringValue("company_id", $_POST));

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write", "company", $company_id) ) throw new SecurityException("Missing required permission.");

			// Validate our inputs.
			$this->form_validation->set_rules('company_name','company name','required');
			$this->form_validation->set_rules('company_address','company address','required');
			$this->form_validation->set_rules('company_city','company city','required');
			$this->form_validation->set_rules('company_state','company state','required');
			$this->form_validation->set_rules('company_postal','company postal','required');

            if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}

			$company_name = trim(getArrayStringValue("company_name", $_POST));
			$company_name_orig = trim(getArrayStringValue("company_name_orig", $_POST));
			$company_address = trim(getArrayStringValue("company_address", $_POST));
			$company_city = trim(getArrayStringValue("company_city", $_POST));
			$company_state = trim(getArrayStringValue("company_state", $_POST));
			$company_postal = trim(getArrayStringValue("company_postal", $_POST));
			$company_id = trim(getArrayStringValue("company_id", $_POST));
			$company_parent_id = trim(getArrayStringValue("company_parent_id", $_POST));

			// The company name must be available to continue.
			if ( $company_name != $company_name_orig && ! IsCompanyNameAvailable( $company_name ) )
			{
				throw new UIException("Business with that name already in use.");
			}
			if ( ! IsCompanyParentNameAvailable( $company_name ) )
			{
				throw new UIException("Business with that name already in use.");
			}

			// Companies can either be edited by "Advice2Pay (1)" or a company parent.  Create our
			// entity_type and entity_id values, if neeeded, so we can do a final security check.
			$entity_type = "company";
			$entity_id = "1";
			if ( $company_parent_id != "" ) $entity_type = "parent";
			if ( $company_parent_id != "") $entity_id = $company_parent_id;

			if ( $entity_type == "company" && $entity_id != GetSessionValue("company_id") ) throw new SecurityException("User does not have permission to edit users for requested company.");
			if ( $entity_type == "parent" && $entity_id != GetSessionValue("companyparent_id") ) throw new SecurityException("User does not have permission to edit users for requested parent.");

			// Edit company.
			$this->company_model->update_company( $company_name, $company_address, $company_city, $company_state, $company_postal, $company_id );

			// Log this transaction so we know who did it.
			AJAXSuccess("Company updated!");



		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	public function company_disable() {

		// company_disable ( POST )
		//
		// This function handles the user request to disable a company.
		// ------------------------------------------------------------
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            $company_id = getArrayStringValue("company_id", $_POST);
            if ( $company_id == "" ) throw new Exception("Invalid input company_id");
            if ( $company_id == "1") throw new UIException("Not allowed to disable Advice2Pay company.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write", "company", $company_id) ) throw new SecurityException("Missing required permission.");



			// disable company.
			$this->company_model->disable_company( $company_id );
			AJAXSuccess("Company deactivated.");

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	public function company_enable( ) {

		// company_enable ( POST )
		//
		// This function handles the user request to enable a company.
		// ------------------------------------------------------------

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Must have a company id.
            $company_id = getArrayStringValue("company_id", $_POST);
            if ( $company_id == "" ) throw new Exception("Invalid input company_id");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write", "company", $company_id) ) throw new SecurityException("Missing required permission.");



			// If we have a companyparent_id, then they may only enable companies up to the seat value.
			$company_parent_id = GetSessionValue("companyparent_id");
			if ( $company_parent_id != "" )
			{
				$parent = $this->CompanyParent_model->get_companyparent($company_parent_id);
				$seats = getArrayIntValue("Seats", $parent);
				$used_seats = $this->CompanyParent_model->get_used_seats($company_parent_id);

				if ( getIntValue($used_seats) >= getIntValue($seats) ) throw new UIException("Please contact support.  You have exceeded the maximum number of companies that may be active at the same time.");
			}

			// enable company.
			$this->company_model->enable_company( $company_id );
			AJAXSuccess("Company activated.");

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	public function company_changeto() {

		// company_changeto ( POST )
		//
		// This function handles the user request to changeto a company.
		// ------------------------------------------------------------

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            $company_id = getArrayStringValue("company_id", $_POST);

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("parent_company_write,company_write", "company", $company_id) ) throw new SecurityException("Missing required permission.");

			// Validate our inputs.
			$this->form_validation->set_rules('company_id','company_id name','required');

			if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}

			$company_id = getArrayStringValue("company_id", $_POST);
			$company = $this->company_model->get_company($company_id);
			if ( $company_id == "") throw new UIException("Unknown company");
			if ( $company_id == "1") throw new UIException("Changing to the master account is not allowed.");
			if ( getArrayStringValue("enabled", $company) != "t" ) throw new UIException("Changing to a disabled company is not allowed.");

			// Where do we go after change to?  Set the landing location if we have one
            // else we will use the dashboard as the default landing location.
			getArrayStringValue('landing', $_POST) === '' ? $landing = base_url("dashboard") : $landing = getArrayStringValue('landing', $_POST);

			ChangeToCompany($company_id);
			AJAXSuccess("", $landing);

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	public function company_rollback() {

		// company_rollback ( POST )
		//
		// This function handles the user request to rollback company data.
		// ------------------------------------------------------------

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

			// Validate our inputs.
			$this->form_validation->set_rules('company_id','company_id name','required');

			if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}

			$company_id = getArrayStringValue("company_id", $_POST);
			$company = $this->company_model->get_company($company_id);
			if ( $company_id == "") throw new UIException("Unknown company");
			if ( $company_id == "1") throw new UIException("Working against the master account is not allowed.");

			$returning_uri = getArrayStringValue("uri", $_POST);
			$this->_rollback_company_reports($company_id);
			AJAXSuccess("", base_url($returning_uri));

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	public function company_savepref() {
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");

			$company_id = GetSessionValue("company_id");
			$group = getArrayStringValue("group", $_POST);
			$group_code = getArrayStringValue("group_code", $_POST);
			$value = getArrayStringValue("value", $_POST);

			// Validation
			if ( $group == "" ) throw new Exception("Missing required input group.");
			if ( $group_code == "" ) throw new Exception("Missing required input group_code.");
			if ( $company_id == "" ) throw new Exception("Missing required input group.");

			$pref = $this->company_model->get_company_preference( $company_id, $group, $group_code );
			if ( ! empty($pref) && getStringValue($value) == "" )
			{
				// If we already have a preference and the value we are setting
				// is the empty string, delete the existing preference rather than
				// saving blank.
				$this->company_model->remove_company_preference( $company_id, $group, $group_code );
			}
			else
			{
				$this->company_model->save_company_preference( $company_id, $group, $group_code, $value );
			}


			AJAXSuccess("Preference saved.");

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}

	// RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function render_targetable_feature_form($company_id)
    {
        try
        {
            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required write permission.");

            // organize inputs.
            $company_id = getStringValue($company_id);

            // validate required inputs.
            if ( $company_id == "" ) throw new Exception("Invalid input $company_id");

            $targetable_feature_form = AddTargetableFeatureForm($company_id, 'company');

            $array = array();
            $array['responseText'] = $targetable_feature_form;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_default_carrier($company_id)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($company_id) === '' ) throw new Exception("missing required input");

            $html = $this->_default_carrier_form($company_id);

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_default_plan($company_id)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($company_id) === '' ) throw new Exception("missing required input");

            $html = FeatureDefaultPlanForm($company_id, 'company');

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_default_clarifications($company_id)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($company_id) === '' ) throw new Exception("missing required input");

            $html = FeatureDefaultClarificationsForm($company_id, 'company');

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_column_normalization_form($company_id, $target_type, $target)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($company_id) === '' ) throw new Exception("missing required input");

            $add_form_html = $this->_column_normalization_form($company_id, $target_type, $target);

            $array = array();
            $array['responseText'] = $add_form_html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_commission_tracking_form($company_id)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($company_id) === '' ) throw new Exception("missing required input");

            $form_html = $this->_commission_tracking_form($company_id);

            $array = array();
            $array['responseText'] = $form_html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_file_transfer_form($company_id)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($company_id) === '' ) throw new Exception("missing required input");

            $add_form_html = $this->_file_transfer_form($company_id);

            $array = array();
            $array['responseText'] = $add_form_html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_assignment_table( $company_id ) {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) !== "POST" ) throw new SecurityException("Unsupported request method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required write permission.");

            $company_parent_id = GetSessionValue("companyparent_id");
            if ( $company_parent_id === '' ) throw new Exception("Missing required input: company_parent_id");
            if ( $company_id === '' ) throw new Exception("Missing required input: company_id");



            $array = array();
            $array['responseText'] = $this->_assignment_table($company_parent_id, $company_id);
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }

	public function render_companies_table() {
		try
		{
			// Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_read") ) throw new SecurityException("Missing required write permission.");

			// validate required inputs.
			//    nothing to do here.

			$array = array();
			$array['responseText'] = $this->_companies_table(getArrayStringValue("uri", $_POST));
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
	public function render_beneficiary_mapping_form($company_id, $target_type, $target)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");


            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required write permission.");

            $form_html = TargetableFeatureBeneficiaryMappingForm($company_id, 'company', $target_type, $target);

            $array = array();
            $array['responseText'] = $form_html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
	public function render_add_company_form() {

		try
		{
			// Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required write permission.");

			$form_html = $this->_company_add_form();

			$array = array();
			$array['responseText'] = $form_html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}

	}
	public function render_edit_company_form($company_id) {

		try
		{
			// Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write", "company", $company_id) ) throw new SecurityException("Missing required write permission.");

			// organize inputs.
			$company_id = getStringValue($company_id);

			// validate required inputs.
			if ( $company_id == "" ) throw new Exception("Invalid input company_id");

			$edit_form_html = $this->_company_edit_form($company_id);

			$array = array();
			$array['responseText'] = $edit_form_html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}

	}
	public function render_changeto_company_form($company_id) {
		try
		{
			// Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write", "company", $company_id) ) throw new SecurityException("Missing required write permission.");

			// organize inputs.
			$company_id = getStringValue($company_id);

			// validate required inputs.
			if ( $company_id == "" ) throw new Exception("Invalid input company_id");

			$changeto_form = $this->_company_changeto_form($company_id);

			$array = array();
			$array['responseText'] = $changeto_form;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
	public function render_rollback_company_form($company_id) {
		try
		{
			// Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required write permission.");

			// organize inputs.
			$company_id = getStringValue($company_id);

			// validate required inputs.
			if ( $company_id == "" ) throw new Exception("Invalid input company_id");

			$changeto_form = $this->_company_rollback_form($company_id, getArrayStringValue("uri", $_POST));

			$array = array();
			$array['responseText'] = $changeto_form;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}

    /**
     * render_feature_widget
     *
     * Wrap a WIDGET around the feature control UI so it can be
     * refreshed via AJAX.
     *
     */
    public function render_feature_widget( )
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new Exception("Unexpected request method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            $parts = explode("/", GetArrayStringValue("REQUEST_URI", $_SERVER));
            $feature_code = GetArrayStringValue(count($parts) - 1, $parts);
            $company_id = GetArrayStringValue(count($parts) - 2, $parts);

            if ( GetStringValue($company_id) === "" ) throw new Exception("Missing required input company_id");
            if ( GetStringValue($feature_code) === "" ) throw new Exception("Missing required input feature_code");

            $array['responseText'] = $this->_feature_control($company_id, $feature_code);
            AJAXSuccess("", null, $array);
        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }

    // VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	public function validate_company() {
        $company_name = GetArrayStringValue("company_name", $_POST);

        if ( !IsLoggedIn() )
        {
            echo '{ "validation": 0 }';
            return false;
        }

        if (! IsCompanyNameAvailable($company_name) ) {
            echo '{ "validation": 0 }';
            return false;
        }
        echo '{ "validation": 1 }';
        return true;
    }


	// PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	private function _states() {
		return $this->company_model->address_states();
	}

    /**
     * _feature_control
     *
     * Produce the HTML needed to manage an individual company
     * feature via the UI.
     *
     * @param $company_id
     * @param $feature_code
     * @return string|void
     */
    private function _feature_control($company_id, $feature_code, $target_type=null, $target=null )
    {
        $feature = $this->Feature_model->get_company_feature($company_id, $feature_code, $target_type, $target);
        $companyparent_id = GetCompanyParentId($company_id);

        $code = GetArrayStringValue("Code", $feature);
        $long_description = GetArrayStringValue("Description", $feature);
        $enabled = GetArrayStringValue("Enabled", $feature);
        $target = GetArrayStringValue("Target", $feature);
        $target_type = GetArrayStringValue("TargetType", $feature);
        GetArrayStringValue('Targetable', $feature) === 't' ? $targetable = true : $targetable = false;
        $child_flg = GetArrayStringValue("ChildFlg", $feature);

        // Check to see if the companyparent is overriding this company feature.
        $parent_override = false;
        if ( $child_flg === 't' )
        {
            if ($companyparent_id !== '' )
            {
                $parent_override = $this->Feature_model->is_feature_enabled_for_companyparent($code, $companyparent_id, $target_type, $target);
                if ( $parent_override ) $enabled = true;
            }
        }

        // If the Description appears to be a view, load the text and replace the long description.
        if ( file_exists(APPPATH . "views/{$long_description}.php") )
        {
            $view_array = array();
            $view_array['enabled'] = $enabled;
            $view_array['company_id'] = $company_id;
            $view_array['companyparent_id'] = $companyparent_id;
            $view_array['code'] = $code;
            $view_array['parent_override'] = $parent_override;
            $view_array['type'] = "company";
            $view_array['target'] = $target;
            $view_array['target_type'] = $target_type;
            $long_description = RenderViewAsString($long_description, $view_array );

            if ( $parent_override )
            {
                $long_description .= "<br><i>This feature has been enabled by the parent and may not be altered by the company.</i>";
            }
        }

        // Turn the code into a short description.
        $short_description = replaceFor($code, "_", " ");
        $short_description = strtolower($short_description);
        $short_description = ucwords($short_description);

        // Convert enabled to boolean
        if ( $enabled === 't' ) $enabled = true;
        if ( $enabled === 'f' ) $enabled = false;

        // Draw a confirm button to change the state of the feature.
        $button = "";
        if ( ! $parent_override )
        {
            $button = new UIConfirmButton();
            $button->setSpinner(false);
            if ( $enabled ) $button->setLabel("Disable");
            if ( ! $enabled ) $button->setLabel("Enable");
            $button->setHref(base_url("companies/feature/toggle/{$code}/{$company_id}/{$target_type}/{$target}"));
            if ( ! $targetable ) $button->setHref(base_url("companies/feature/toggle/{$code}/{$company_id}"));
            $button->setCallback("RefreshCompanyFeatures");
            $button->setCallbackParameter("company_feature_control_{$company_id}_{$code}_{$target_type}_{$target}");
            if( ! $targetable ) $button->setCallbackParameter("company_feature_control_{$company_id}_{$code}");
            if ( $enabled ) $button->setColor("red");

            if ( $targetable )
            {
                $attributes = array();
                $attributes['identifier'] = $company_id;
                $attributes['identifier_type'] = 'company';
                $attributes['feature_code'] = $code;
                $attributes['target_type'] = $target_type;
                $attributes['target'] = $target;
                $button->addExtraEnabledButton("Delete", "RemoveCompanyFeature", base_url("companies/feature/remove/targetable_feature"), $attributes );
            }



            $button = $button->render();
        }

        // Render the feature row.
        $view_array = array();
        $view_array = array_merge($view_array, array('short_description' => $short_description));
        $view_array = array_merge($view_array, array('long_description' => $long_description));
        $view_array = array_merge($view_array, array('button' => $button));
        $view_array = array_merge($view_array, array('company_id' => $company_id));
        $view_array = array_merge($view_array, array('enabled' => $enabled));
        $view_array = array_merge($view_array, array('target' => $target));
        $view_array = array_merge($view_array, array('target_type' => $target_type));
        return RenderViewAsString("companies/feature", $view_array);
    }
	private function _assignment_table( $company_parent_id, $company_id, $managers_only=false ) {

        // Collect all of the users for a parent company denoted with the ResponsibleFor flag
        // indicating the individual users relationship with the child company.
        $users = $this->User_model->get_users_responsible_for($company_parent_id, $company_id);

        // Filter out the users to show only staff members ( not managers ).
        $staff = array_filter(
            $users,
            function($user, $index)
            {
                $is_manager = GetArrayStringValue('IsManager', $user);
                if ( $is_manager !== 't' ) return TRUE;
                return FALSE;
            },
            ARRAY_FILTER_USE_BOTH);

        // Filter out the users to show only managers.
        $managers = array_filter(
            $users,
            function($user, $index)
            {
                $is_manager = GetArrayStringValue('IsManager', $user);
                if ( $is_manager === 't' ) return TRUE;
                return FALSE;
            },
            ARRAY_FILTER_USE_BOTH);

        $data = $staff;
        if ( $managers_only ) $data = $managers;

        // Get the assigned table.
        $view_array = array();
        $view_array = array_merge($view_array, array("users" => $data));
        $view_array = array_merge($view_array, array("company_id" => $company_id));
        return RenderViewAsString("companies/assignment_table", $view_array);

    }
	private function _companies_table($requesting_uri="") {

		// Dependng on the URI, return different data.
		$requesting_uri = getStringValue($requesting_uri);
		if ( strtolower($requesting_uri) == "dashboard/support" ) return $this->_companies_table_recent();
		if ( strtolower($requesting_uri) == "dashboard/parent" ) return $this->_companies_table_recent();
		return $this->_companies_table_all();

	}
	private function _companies_table_recent() {

		// _companies_table_admin
		//
		// Return the table and data for the admin widget on the dashboard.
		// ------------------------------------------------------------------
		$companies = array();

		$company_parent_id = GetSessionValue("companyparent_id");
		if ( $company_parent_id == "" )
		{
			$companies = $this->company_model->select_recent_companies();
			return RenderViewAsString("companies/companies_admin_table", array("data" => $companies));
		}

		$companies = $this->CompanyParent_model->select_recent_companies( GetSessionValue("companyparent_id") );
		return RenderViewAsString("companies/companies_parent_table", array("data" => $companies));


	}
	private function _companies_table_all() {

		// _companies_table_all
		//
		// Return the table and data for the companies controller.
		// ------------------------------------------------------------------
		$companies = array();

		if ( GetSessionValue("companyparent_id") !== '' ) $companies = $this->CompanyParent_model->get_companies_by_parent();
		if ( GetSessionValue("companyparent_id") === '' ) $companies = $this->company_model->get_all_companies();

		return RenderViewAsString("companies/companies_table", array("data" => $companies));
	}
	private function _company_add_form( ) {

		$form = new UIModalForm("add_company_form", "add_company_form", base_url("companies/add"));
		$form->setTitle("Add Company");
		$form->setCollapsable(true);
		$form->addElement($form->textInput("company_name", "Company Name", null, "Ex: Company Name"));
		$form->addElement($form->hiddenInput("company_name_orig", ""));
		$form->addElement($form->textInput("company_address", "Address", null, "Ex: 1234 Main St."));
		$form->addElement($form->textInput("company_city", "City", null, "Ex: Des Moines"));
		$form->addElement($form->dropdown("company_state", "State", null, $this->_states(), null, "", "", false, true));
		$form->addElement($form->textInput("company_postal", "Postal Code", null, "Ex: 55555"));

        $form->addElement($form->htmlView("companies/add_user_help", array(), "add_user_help", "", true));

		$form->addElement($form->emailInput("email_address", "Email Address", null, "Ex: manager@company.com", array(), false, true));
		$form->addElement($form->textInput("first_name", "First Name", null, "Ex: John", array(),false,true));
		$form->addElement($form->textInput("last_name", "Last Name", null, "Ex: Smith", array(), false, true));
		$form->addElement($form->checkbox("onboarding", "", "Notify this user of their new account via email.", true, false, true));
		$form->addElement($form->hiddenInput("company_parent_id", GetSessionValue("companyparent_id")));
		$form->addElement($form->submitButton("save_add_btn", "Add Company", "btn-primary pull-right"));
		$form->addElement($form->button("cancel_add_btn", "Cancel", "btn-default pull-right"));
        $form->addElement($form->button("user_add_btn", "Add Company User", "btn-default pull-left", false, array('open-label' => 'Add Company User', 'closed-label' => "Don&apos;t Add Company User")));
		$form_html = $form->render();

		return $form_html;
	}
	private function _company_edit_form( $company_id=null) {

		$company_name = null;
		$company_address = null;
		$company_city = null;
		$company_state = "";
		$company_postal = null;

		$company = $this->company_model->get_company($company_id);
		if ( ! empty($company) )
		{
			$company_name = getArrayStringValue("company_name", $company);
			$company_address = getArrayStringValue("company_address", $company);
			$company_city = getArrayStringValue("company_city", $company);
			$company_state = getArrayStringValue("company_state", $company);
			$company_postal = getArrayStringValue("company_postal", $company);
		}

		$edit_form = new UIModalForm("edit_company_form", "edit_company_form", base_url("companies/edit"));
		$edit_form->setTitle("Edit Company ( {$company_name} )");
		$edit_form->setCollapsable(true);
		$edit_form->addElement($edit_form->textInput("company_name", "Company Name", $company_name, "Company Name"));
		$edit_form->addElement($edit_form->hiddenInput("company_name_orig", $company_name));
		$edit_form->addElement($edit_form->textInput("company_address", "Address", $company_address, "1234 Main St."));
		$edit_form->addElement($edit_form->textInput("company_city", "City", $company_city, "Des Moines"));
		$edit_form->addElement($edit_form->dropdown("company_state", "State", "Iowa", $this->_states(), $company_state, "", "", false, true));
		$edit_form->addElement($edit_form->textInput("company_postal", "Postal Code", $company_postal, "55555"));
		$edit_form->addElement($edit_form->hiddenInput("company_id", $company_id));
		$edit_form->addElement($edit_form->hiddenInput("company_parent_id", GetSessionValue("companyparent_id")));
		$edit_form->addElement($edit_form->submitButton("save_edit_btn", "Update", "btn-primary pull-right"));
		$edit_form->addElement($edit_form->button("cancel_edit_btn", "Cancel", "btn-default pull-right"));

        if ( IsAuthenticated("") )
        {
            $edit_form->addElement($edit_form->submitButton("edit_company_features_btn", "Edit Features", "btn-white pull-left", array('href' => base_url("companies/features/{$company_id}"))));
        }

		$edit_form_html = $edit_form->render();

		return $edit_form_html;
	}
	private function _company_changeto_form( $company_id ) {

		$company_name = "";
		$company = $this->company_model->get_company($company_id);
		if ( ! empty($company) )
		{
			$company_name = getArrayStringValue("company_name", $company);
		}


		if ( IsAuthenticated("parent_company_write") && getArrayStringValue("enabled", $company) != "t" )
		{
			// Set the form title based on if we are doing an add or edit.
			$title = "Disabled Company ( {$company_name} )";

			$form = new UIModalForm("changeto_company_form", "changeto_company_form", base_url("companies/changeto"));
			$form->setTitle($title);
			$form->setCollapsable(true);
			$form->addElement($form->htmlView("companies/deny_changeto", array()));
			$form->addElement($form->hiddenInput("company_id", getStringValue($company_id)));
			$form->addElement($form->hiddenInput("company_parent_id", GetSessionValue("companyparent_id")));
            $form->addElement($form->hiddenInput("landing", ""));
			$form->addElement($form->button("no_btn", "Okay", "btn-primary pull-right"));
			$form_html = $form->render();

			return $form_html;
		}

		// Set the form title based on if we are doing an add or edit.
		$title = "Change To Company ( {$company_name} )";

		$form = new UIModalForm("changeto_company_form", "changeto_company_form", base_url("companies/changeto"));
		$form->setTitle($title);
		$form->setCollapsable(true);
		$form->addElement($form->htmlView("companies/confirm_changeto", array()));
		$form->addElement($form->hiddenInput("company_id", getStringValue($company_id)));
		$form->addElement($form->hiddenInput("company_parent_id", GetSessionValue("companyparent_id")));
        $form->addElement($form->hiddenInput("landing", ""));
		$form->addElement($form->submitButton("yes_btn", "Yes", "btn-primary pull-right"));
		$form->addElement($form->button("no_btn", "No", "btn-default pull-right"));
		$form_html = $form->render();

		return $form_html;
	}
	private function _company_rollback_form( $company_id, $returning_uri="" ) {

		$company_name = "";
		$company = $this->company_model->get_company($company_id);
		if ( ! empty($company) )
		{
			$company_name = getArrayStringValue("company_name", $company);
		}


		if ( ! IsAuthenticated("parent_company_write") )
		{
			// Set the form title based on if we are doing an add or edit.
			$title = "Rollback Company Import ( {$company_name} - Month YYYY )";
			$form = new UIModalForm("rollback_company_form", "rollback_company_form", base_url("companies/rollback"));
			$form->setTitle($title);
			$form->setCollapsable(true);
			$form->addElement($form->htmlView("companies/deny_rollback", array()));
			$form->addElement($form->hiddenInput("company_id", getStringValue($company_id)));
			$form->addElement($form->hiddenInput("uri", getStringValue($returning_uri)));
			$form->addElement($form->button("no_btn", "Okay", "btn-primary pull-right"));
			$form_html = $form->render();

			return $form_html;
		}

		// Pull some data so we know what we are about to rollback.
		$is_running = $this->Queue_model->does_company_have_running_or_pending_jobs($company_id);
		$wizard_flg = $this->Wizard_model->has_wizard_started($company_id);
		$dates = $this->Company_model->most_recent_company_import_date($company_id);
		$import_date = getArrayStringValue("UploadDisplayMonth", $dates);

		if ( $is_running )
		{
			// Try again later
			$form = new UIModalForm("rollback_company_form", "rollback_company_form", base_url("companies/rollback"));
			$form->setTitle("Try Again Later");
			$form->addElement($form->htmlView("companies/try_again_later_rollback", array()));
			$form->addElement($form->hiddenInput("company_id", getStringValue($company_id)));
			$form->addElement($form->hiddenInput("uri", getStringValue($returning_uri)));
			$form->addElement($form->button("no_btn", "Okay", "btn-primary pull-right"));
			$form_html = $form->render();
			return $form_html;
		}
		if ( $wizard_flg  ) {

			// Wizard Rollback
			$form = new UIModalForm("rollback_company_form", "rollback_company_form", base_url("companies/rollback"));
			$form->setTitle("Rollback - ( {$company_name} - Recent Upload )");
			$form->addElement($form->htmlView("companies/confirm_rollback_wizard", array()));
			$form->addElement($form->hiddenInput("company_id", getStringValue($company_id)));
			$form->addElement($form->hiddenInput("uri", getStringValue($returning_uri)));
			$form->addElement($form->submitButton("yes_btn", "Yes", "btn-primary pull-right"));
			$form->addElement($form->button("no_btn", "No", "btn-default pull-right"));
			$form_html = $form->render();
			return $form_html;

		}
		if ( ! $wizard_flg && $import_date != ""  )
		{
			// Finalized Import Rollback
			$form = new UIModalForm("rollback_company_form", "rollback_company_form", base_url("companies/rollback"));
			$form->setTitle("Rollback - ( {$company_name} -  {$import_date} )");
			$form->addElement($form->htmlView("companies/confirm_rollback", array()));
			$form->addElement($form->hiddenInput("company_id", getStringValue($company_id)));
			$form->addElement($form->hiddenInput("uri", getStringValue($returning_uri)));
			$form->addElement($form->submitButton("yes_btn", "Yes", "btn-primary pull-right"));
			$form->addElement($form->button("no_btn", "No", "btn-default pull-right"));
			$form_html = $form->render();
			return $form_html;
		}


		// Nothing to rollback.
		$form = new UIModalForm("rollback_company_form", "rollback_company_form", base_url("companies/rollback"));
		$form->setTitle("Rollback - ( {$company_name} )");
		$form->addElement($form->htmlView("companies/confirm_rollback_no_results", array()));
		$form->addElement($form->hiddenInput("company_id", getStringValue($company_id)));
		$form->addElement($form->hiddenInput("uri", getStringValue($returning_uri)));
		$form->addElement($form->button("no_btn", "Okay", "btn-primary pull-right"));
		$form_html = $form->render();
		return $form_html;

	}
	private function _rollback_company_reports($company_id) {

        // REMEMBER!
        // There are multiple ways data can be rolled back.  If you are adding
        // additional delete logic, add it in these places.
        // 1. Companies controller rolls back the most recent attempt, finalized or in progress.
        // 2. Wizard Helper rolls back the most recent wizard attempt which is in progress.

        // DELETE COMPANY
        // Added a new table that you need to rollback?  Don't forget to update
        // the "hard_delete" functions in the User_model and Company_model too.

		if ( $this->Wizard_model->has_wizard_started($company_id) )
		{
			// If a wizard run is in progress, remove that.
			RollbackWizardAttempt($company_id);
		}else{

			// Find the most recent import data set and remove it.
			$dates = $this->company_model->most_recent_company_import_date($company_id);
			if ( ! empty($dates) )
			{

			    // ATTENTION: The Import Date is calculated above using the most recent logic.
                // We want to pass in this date rather than relying on the rollback and
                // delete functions from picking the most recent one.  In a rollback from
                // the dashboard by company, the date may not be the same.  So make sure
                // you specify the one that we are working with from the select above.

				// Remove the ImportData
				$import_date = getArrayStringValue("UploadMonth", $dates);

                // Audit this transaction.
                $company = $this->Company_model->get_company($company_id);
                $payload = array();
                $payload = array_merge($payload, array('ImportDate' => $import_date));
                $payload = array_merge($payload, array('Function' => "RollbackWizardAttempt"));
                AuditIt( "Company rollback.", $payload);

                if ( $company_id !== GetSessionValue('company_id') )
                {
                    // This was a big event.  Add this audit record to the company in question too.
                    AuditIt( "Company rollback.", $payload, GetSessionValue('user_id'), $company_id);
                }

                // report we are in the process of changing the workflow for this company.
                NotifyCompanyChannel($company_id, "workflow_step_changing", array('company_id' => $company_id));

                $this->Reporting_model->delete_downloadable_reports($company_id, $import_date);

				$this->Validation_model->delete_validation_errors($company_id, 'company');		// does not use import_date.
                $this->Age_model->delete_age($company_id, $import_date);
                $this->Wizard_model->remove_washed_records($company_id, $import_date);
                $this->Reporting_model->delete_report_review_warnings($company_id, $import_date);
                $this->Reporting_model->delete_summary_data($company_id, $import_date);
                $this->Reporting_model->delete_company_report($company_id, $import_date);
				$this->Life_model->delete_companylifecompare($company_id, $import_date);
		        $this->Life_model->delete_companyliferesearch($company_id, $import_date);
		        $this->Life_model->delete_companylife_new_lives($company_id, $import_date);
		        $this->Life_model->delete_lifedata($company_id, $import_date);
				$this->Life_model->delete_companylife_disabled($company_id); // Okay, there is no import_date.
                $this->Life_model->delete_importlife($company_id, $import_date);
                $this->Life_model->delete_import_life_warning($company_id, $import_date);
				$this->LifeEvent_model->delete_all_retrodatalifeevent($company_id, $import_date);
                $this->LifeEvent_model->delete_all_retrodatalifeeventwarning($company_id, $import_date);
				$this->LifeEvent_model->delete_lifeeventcompare($company_id, $import_date);
                $this->Retro_model->delete_retro_data($company_id, $import_date);
                $this->Retro_model->delete_automatic_adjustments( $company_id, $import_date );
				$this->Adjustment_model->delete_manual_adjustment( $company_id, $import_date );
				$this->Relationship_model->delete_relationship_data($company_id, $import_date);
				$this->Spend_model->delete_spend_data( $company_id, $import_date );
				$this->PlanFees_model->delete_plan_fee_importdata( $company_id, $import_date );
				$this->Support_model->delete_support_timer( $company_id, $import_date );

				$action = new GenerateOriginalEffectiveDateData();
				$action->rollback($company_id, $import_date);

                $action = new GenerateReportTransamericaEligibility();
                $action->rollback($company_id, $import_date);

                $action = new GenerateReportTransamericaCommissions();
                $action->rollback($company_id, $import_date);

                $action = new GenerateWarningReport();
                $action->rollback($company_id, $import_date);

                $action = new GenerateUniversalEmployeeId();
                $action->rollback($company_id, $import_date);

                $action = new GenerateCommissionReport();
                $action->rollback($company_id, $import_date);

                $action = new GenerateCommissions();
                $action->rollback($company_id, $import_date);

                $action = new SkipMonthProcessing();
                $action->rollback($company_id, $import_date);

                // Remove CompanyBeneficiaryImport data
                $this->Beneficiary_model->beneficiary_importdata_remove($company_id, $import_date);

                // Remove ImportData which is already Finalized
				$this->Company_model->delete_company_import_data($company_id, $import_date);

				// Does this user have any ImportData left?
				$dates = $this->Company_model->most_recent_company_import_date($company_id);
				if ( empty($dates) )
				{
					// Remove the "starting_date" company preferences, as we just
					// rolled back their first attempt.  ( if they have any )
					$this->Company_model->remove_company_preference($company_id, "starting_date", "month");
					$this->Company_model->remove_company_preference($company_id, "starting_date", "year");
                    $this->Company_model->remove_company_preference($company_id, "mapping", "a2p_suggestions");
				}

				// report the workflow step has changed for this company.
                NotifyCompanyChannel($company_id, "workflow_step_changed", array('company_id' => $company_id));

			}

            // Very last step, vacuum the database.  We just made a bunch of
            // changes.
            //$this->Tuning_model->vacuum();

        }
	}
    private function _column_normalization_form($company_id='', $target_type='', $target='')
    {
        $company = $this->Company_model->get_company($company_id);
        $company_name = GetArrayStringValue("company_name", $company);


        // Collect the SAVED data for this feature from the preference table.
        $pref = $this->Company_model->get_company_preference( $company_id, "column_normalization", "{$target_type}_{$target}_pattern" );
        $pattern = GetArrayStringValue("value", $pref);

        $pref = $this->Company_model->get_company_preference( $company_id, "column_normalization", "{$target_type}_{$target}_replace" );
        $replace = GetArrayStringValue("value", $pref);

        $pref = $this->Company_model->get_company_preference( $company_id, "column_normalization", "{$target_type}_{$target}_description" );
        $description = GetArrayStringValue("value", $pref);


        $form = new UIModalForm("column_normalization_form", "column_normalization_form", base_url("companies/feature/save/column_normalization"));
        $form->setTitle("Column Normalization ( {$company_name} )");
        $form->setDescription("Add an additional custom search and replace to this columns normalization routine.  Activating the feature on the parent account will automatically enable this feature and the following settings on all associated companies.  ");


        $form->addElement($form->textInput('pattern','Search Pattern', $pattern, 'RegEx Search Pattern'));
        $form->addElement($form->textInput('replace','Replace', $replace, 'Empty String'));
        $form->addElement($form->textInput('description','Description', $description, 'Describe this custom normalization.'));

        $form->addElement($form->hiddenInput('company_id', $company_id));
        $form->addElement($form->hiddenInput('target', $target));
        $form->addElement($form->hiddenInput('target_type', $target_type));

        $form->addElement($form->submitButton("save_column_normalization_form", "Save Settings", "btn-primary pull-right"));
        $form->addElement($form->button("cancel_column_normalization_form", "Cancel", "btn-default pull-right"));
        $form_html = $form->render();

        return $form_html;
    }
	private function _commission_tracking_form($company_id='')
    {
        $company = $this->Company_model->get_company($company_id);
        $company_name = GetArrayStringValue("company_name", $company);

        // Collect the properties for the different commission type properties.
        $commission_type = GetCommissionType($company_id);
        $oed_variant = GetCommissionEffectiveDateType($company_id);

        // Convert the commission effective date type into true/false
        // true for oldest life plan effective date, false for recent tier change
        if ( $oed_variant === OLDEST_LIFE_PLAN_EFFECTIVE_DATE ) $oed_variant = true;
        else $oed_variant = false;

        // Does this company have any data in our system?
        $has_data = false;
        $import_dates = $this->Reporting_model->select_import_dates($company_id);
        if ( ! empty($import_dates) ) $has_data = true;

        // Get a list of the possible commission types.
        $commission_types = array();
        $types = $this->Commissions_model->select_commission_types();
        foreach($types as $type)
        {
            $code = GetArrayStringValue("Name", $type);
            $display = GetArrayStringValue("Display", $type);
            $commission_types[$code] = $display;
        }

        $form = new UIModalForm("commission_tracking_form", "commission_tracking_form", base_url("companies/feature/save/commission_tracking"));
        $form->setTitle("Commission Tracking ( {$company_name} )");
        $form->addElement($form->htmlView('features/commission_tracking_warning', array(), 'warning', '', true));
        $form->addElement($form->dropdown("commission_type", "Commission Type", "Select commission type.", $commission_types, $commission_type, "", "", false, true));
        $form->addElement($form->checkbox("oldest_effective_date", "", "Don't Reset Original Effective Date on Tier Change", $oed_variant));

        $form->addElement($form->hiddenInput('company_id', $company_id));
        $form->addElement($form->hiddenInput('has_data', $has_data));
        $form->addElement($form->hiddenInput('orig_commission_type', $commission_type));
        $form->addElement($form->hiddenInput('orig_oldest_effective_date', GetIntValue($oed_variant)));

        $form->addElement($form->submitButton("save_commission_tracking_form", "Save Settings", "btn-primary pull-right"));
        $form->addElement($form->button("cancel_commission_tracking_form", "Cancel", "btn-default pull-right"));
        $form_html = $form->render();

        return $form_html;
    }
    private function _file_transfer_form($company_id='')
    {
        $company = $this->Company_model->get_company($company_id);
        $company_name = GetArrayStringValue("company_name", $company);

        $encryption_key = GetCompanyEncryptionKey($company_id);
        $transfer = $this->FileTransfer_model->get_file_transfer_by_company_id($company_id);

        $hostname = getArrayStringValue("Hostname", $transfer);
        $username = getArrayStringValue("Username", $transfer);
        $destination = getArrayStringValue("DestinationPath", $transfer);
        $port = getArrayStringValue("Port", $transfer);
        $encrypted_password = getArrayStringValue("EncryptedPassword", $transfer);
        $encrypted_ssh_key = getArrayStringValue("EncryptedSSHKey", $transfer);

        $password = A2PDecryptString($encrypted_password, $encryption_key);
        $ssh_key = A2PDecryptString($encrypted_ssh_key, $encryption_key);

        $form = new UIModalForm("file_transfer_form", "file_transfer_form", base_url("companies/feature/save/file_transfer"));
        $form->setTitle("SFTP File Transfer ( {$company_name} )");
        $form->addElement($form->textInput("hostname", "Hostname", $hostname));
        $form->addElement($form->textInput("username", "Username", $username));
        $form->addElement($form->textInput("destination", "Destination Path", $destination, "/tmp"));
        $form->addElement($form->textInput("port", "Port", $port, "22"));

        $form->addElement($form->htmlView("features/file_transfer_password", array()));
        $form->addElement($form->textInput("password", "Password", $password));
        $form->addElement($form->textarea('ssh_key', 'Private SSH Key', $ssh_key, '3'));
        $form->addElement($form->hiddenInput('company_id', $company_id));
        $form->addElement($form->submitButton("save_file_transfer_form", "Save Settings", "btn-primary pull-right"));
        $form->addElement($form->button("cancel_file_transfer_form", "Cancel", "btn-default pull-right"));
        $form_html = $form->render();

        return $form_html;

    }
    private function _default_carrier_form($company_id='')
    {
        $company = $this->Company_model->get_company($company_id);
        $company_name = GetArrayStringValue("company_name", $company);

        $carrier_code = GetPreferenceValue($company_id, 'company', 'carrier', 'default_carrier_code');
        $carrier_description = $this->Carrier_model->get_carrier_description_by_carrier_code($carrier_code);

        $dropdown = new Select2("modal");
        $dropdown->setId("default_carrier_code");
        $dropdown->setSelectedValue($carrier_code);
        $carriers = $this->Carrier_model->get_known_carriers();
        foreach($carriers as $carrier)
        {
            $normalized = getArrayStringValue("CarrierCode", $carrier);
            $user_description = getArrayStringValue("UserDescription", $carrier);
            $dropdown->addItem("", $user_description, $normalized);
        }

        $form = new UIModalForm("default_carrier_form", "default_carrier_form", base_url("companies/feature/save/default_carrier"));
        $form->setTitle("Default Carrier ( {$company_name} )");
        $form->setDescription("If no carrier is supplied in an import file, assume the following carrier was implied.");


        $form->addElement($dropdown);

        $form->addElement($form->hiddenInput('company_id', $company_id));
        $form->addElement($form->hiddenInput('carrier_code', $carrier_code));

        $form->addElement($form->submitButton("save_default_carrier_form", "Save Settings", "btn-primary pull-right"));
        $form->addElement($form->button("cancel_default_carrier_form", "Cancel", "btn-default pull-right"));
        $form_html = $form->render();

        return $form_html;
    }
    private function _disable_column_normalization($company_id, $feature)
    {
        $target = GetArrayStringValue('Target', $feature);
        DisableColumnNormalizationRegExFeature($company_id, $target);
    }
    private function _enable_column_normalization($company_id, $feature)
    {
        $target = GetArrayStringValue('Target', $feature);
        EnableColumnNormalizationRegExFeature($company_id, $target);
    }

}
