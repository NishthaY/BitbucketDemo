<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CompanyParent extends SecureController {

    protected $route;

	public function __construct(){
		parent::__construct();
        $this->load->helper('CompanyParent_helper');
        $this->load->helper('Companies_helper');
        $this->load->library('form_validation');
    }

    // SCREENS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

    /**
     * features
     *
     * Screen that will show a list of all features available to a
     * specific company parent.
     *
     * @param $companyparent_id
     */
    public function features($companyparent_id )
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            $companyparent = $this->CompanyParent_model->get_companyparent($companyparent_id);
            $companyparent_name = GetArrayStringValue("Name", $companyparent);

            // Form header
            $header = new UIFormHeader($companyparent_name);
            $header->addLink("Parent List", base_url("parents/manage"));
            $header->addLink("Features");
            $page_header = $header->render();

            $widgets = array();
            $features = $this->Feature_model->get_companyparent_features($companyparent_id);
            foreach($features as $feature)
            {
                $feature_code = GetArrayStringValue("Code", $feature);
                $target = GetArrayStringValue("Target", $feature);
                $target_type = GetArrayStringValue("TargetType", $feature);
                GetArrayStringValue("Targetable", $feature) === 't' ? $targetable = true : $targetable = false;

                // If you have a feature that is targetable but has no target, do not show it.  That is the
                // record that allows you to CREATE the targetable features.
                if ( $targetable && $target === '' ) continue;

                $widget_name = "companyparent_feature_control_{$companyparent_id}_{$feature_code}";
                if ( $targetable ) $widget_name = "companyparent_feature_control_{$companyparent_id}_{$feature_code}_{$target_type}_{$target}";

                $href = base_url("parents/widget/feature/{$companyparent_id}/{$feature_code}");
                if ( $targetable ) $href = base_url("parents/widget/feature/{$companyparent_id}/{$feature_code}/{$target_type}/{$target}");

                $widget = new UIWidget($widget_name);
                $widget->setBody( $this->_feature_control($companyparent_id, $feature_code, $target_type, $target) );
                $widget->setHref($href);
                $widget = $widget->render();

                $widgets[] = $widget;
            }

            // Add Beneficiary Mapping Widget
            $beneficiary_mapping_widget = new UIWidget("beneficiary_mapping_widget");
            $beneficiary_mapping_widget->setHref( base_url("parents/widget/beneficiary_mapping/{$companyparent_id}/TARGETTYPE/TARGET") );
            $beneficiary_mapping_widget = $beneficiary_mapping_widget->render();

            // Add Targetable Feature Widget
            $targetable_feature_widget = new UIWidget("targetable_feature_widget");
            $targetable_feature_widget->setHref( base_url("parents/widget/targetable_feature/{$companyparent_id}") );
            $targetable_feature_widget = $targetable_feature_widget->render();

            // File Transfer Feature Form
            $file_transfer_form_widget = new UIWidget("file_transfer_widget");
            $file_transfer_form_widget->setBody( $this->_file_transfer_form($companyparent_id) );
            $file_transfer_form_widget->setHref( base_url("parents/widget/file_transfer/{$companyparent_id}") );
            $file_transfer_form_widget = $file_transfer_form_widget->render();

            // Commission Tracking Feature Form
            $commission_tracking_form_widget = new UIWidget("commission_tracking_widget");
            $commission_tracking_form_widget->setBody( $this->_commission_tracking_form($companyparent_id) );
            $commission_tracking_form_widget->setHref( base_url("parents/widget/commission_tracking/{$companyparent_id}") );
            $commission_tracking_form_widget = $commission_tracking_form_widget->render();

            // Column Normalization Feature Form
            $column_normalization_widget = new UIWidget("column_normalization_widget");
            $column_normalization_widget->setHref( base_url("parents/widget/column_normalization/{$companyparent_id}/TARGETTYPE/TARGET") );
            $column_normalization_widget = $column_normalization_widget->render();

            // Default Carrier Feature Form
            $default_carrier_widget = new UIWidget("default_carrier_widget");
            $default_carrier_widget->setHref( base_url("parents/widget/default_carrier/{$companyparent_id}") );
            $default_carrier_widget = $default_carrier_widget->render();

            // Default Plan Feature Form
            $default_plan_widget = new UIWidget("default_plan_widget");
            $default_plan_widget->setHref( base_url("parents/widget/default_plan/{$companyparent_id}") );
            $default_plan_widget = $default_plan_widget->render();

            // Default Clarifications Feature Form
            $default_clarifications_widget = new UIWidget("default_clarifications_widget");
            $default_clarifications_widget->setHref( base_url("parents/widget/default_clarifications/{$companyparent_id}") );
            $default_clarifications_widget = $default_clarifications_widget->render();

            $view_array = array();
            $view_array = array_merge($view_array, array("page_header" => $page_header));
            $view_array = array_merge($view_array, array("widgets" => $widgets));
            $view_array = array_merge($view_array, array("file_transfer_widget" => $file_transfer_form_widget));
            $view_array = array_merge($view_array, array("commission_tracking_widget" => $commission_tracking_form_widget));
            $view_array = array_merge($view_array, array("column_normalization_widget" => $column_normalization_widget));
            $view_array = array_merge($view_array, array("companyparent_id" => $companyparent_id));
            $view_array = array_merge($view_array, array("default_carrier_widget" => $default_carrier_widget));
            $view_array = array_merge($view_array, array("targetable_feature_widget" => $targetable_feature_widget));
            $view_array = array_merge($view_array, array("beneficiary_mapping_widget" => $beneficiary_mapping_widget));
            $view_array = array_merge($view_array, array("default_plan_widget" => $default_plan_widget));
            $view_array = array_merge($view_array, array("default_clarifications_widget" => $default_clarifications_widget));

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("companyparent/js_assets")));
            $page_template = array_merge($page_template, array("view" => "companyparent/features"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }
    public function parent_list() {

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

			// Form header
			$header = new UIFormHeader("Parents");
			$header->addButton($header->button("add_parent", "Add Parent", "add_parent_form" ));
            $header->addLink("Parent List");
			$header_html = $header->render();

			// Add Parent Form
			$add_form_widget = new UIWidget("add_parent_widget");
			$add_form_widget->setHref(base_url("parents/widget/add"));
			$add_form_widget = $add_form_widget->render();

			// Edit Parent Form
			$edit_form_widget = new UIWidget("edit_parent_widget");
			$edit_form_widget->setBody( $this->_parent_edit_form(null) );
			$edit_form_widget->setHref(base_url("parents/widget/edit"));
			$edit_form_widget = $edit_form_widget->render();

			// Change To Company Form
			$changeto_form_widget = new UIWidget("changeto_parent_widget");
			$changeto_form_widget->setBody( $this->_parent_changeto_form(null) );
			$changeto_form_widget->setHref(base_url("parents/widget/changeto"));
			$changeto_form_widget = $changeto_form_widget->render();

            // Rollback CompanyParent Confirmation Dialog.
            $companyparent_rollback_widget = new UIWidget("rollback_companyparent_widget");
            $companyparent_rollback_widget->setHref(base_url("parents/widget/rollback"));
            $companyparent_rollback_widget = $companyparent_rollback_widget->render();


            // Company data widget.
			$widget = new UIWidget("parents_widget");
			$widget->setBody($this->_main_table());
			$widget->setHref(base_url("parents/widget/list"));
			$widget->setCallback("InitCompanyParentTable");
			$table_html = $widget->render();

			$view_array = array();
			$view_array = array_merge($view_array, array("table_html" => $table_html));
			$view_array = array_merge($view_array, array("add_form" => $add_form_widget));
			$view_array = array_merge($view_array, array("edit_form" => $edit_form_widget));
			$view_array = array_merge($view_array, array("changeto_form" => $changeto_form_widget));
            $view_array = array_merge($view_array, array("rollback_widget" => $companyparent_rollback_widget));
			$view_array = array_merge($view_array, array("form_header" => $header_html));


			$page_template = array();
			$page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("companyparent/js_assets")));
			$page_template = array_merge($page_template, array("view" => "companyparent/main"));
			$page_template = array_merge($page_template, array("view_array" => $view_array));
			RenderView('templates/template_body_default', $page_template);

		}
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }

	}

    // POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+

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

            $companyparent_id = GetArrayStringValue("companyparent_id", $_POST);
            $target = GetArrayStringValue("target", $_POST);
            $target_type = GetArrayStringValue("target_type", $_POST);
            $pattern = GetArrayStringValue("pattern", $_POST);
            $replace = GetArrayStringValue("replace", $_POST);
            $description = GetArrayStringValue("description", $_POST);

            if ( $companyparent_id === '' ) throw new UIException('Missing required input companyparent_id');
            if ( $target === '' ) throw new UIException('Missing required input target');
            if ( $target_type === '' ) throw new UIException('Missing required input target_type');

            // Save the information in preferences.
            $this->CompanyParent_model->save_companyparent_preference(  $companyparent_id, "column_normalization", "{$target_type}_{$target}_pattern", $pattern );
            $this->CompanyParent_model->save_companyparent_preference(  $companyparent_id, "column_normalization", "{$target_type}_{$target}_replace", $replace );
            $this->CompanyParent_model->save_companyparent_preference(  $companyparent_id, "column_normalization", "{$target_type}_{$target}_description", $description );

            // If the feature is currently enabled, then not only do we need to save
            // the feature properties, but we also need to enable the feature in the application too.
            $feature = $this->Feature_model->get_companyparent_feature($companyparent_id, 'COLUMN_NORMALIZATION_REGEX', $target_type, $target);
            $enabled = GetArrayStringValue("Enabled", $feature);
            if ( $enabled === "t" ) {
                $this->_enable_column_normalization($companyparent_id, $feature);
            }

            $payload = array();
            $payload['TargetType'] = $target_type;
            $payload['Target'] = $target;
            $payload['Pattern'] = $pattern;
            $payload['Replace'] = $replace;
            $payload['Description'] = $description;
            AuditIt("Custom normalization settings updated.", $payload, GetSessionValue('user_id'), null, $companyparent_id);

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
            $companyparent_id = GetArrayStringValue("companyparent_id", $_POST);
            $oldest_effective_date = GetArrayStringValue("oldest_effective_date", $_POST);

            $commission_effective_date_type = 'RECENT_TIER_CHANGE';
            if ( $oldest_effective_date == 'on' ) $commission_effective_date_type = "OLDEST_LIFE_PLAN_EFFECTIVE_DATE";


            if ( $commission_type === "" ) throw new UIException("Missing required input commission type.");
            if ( $companyparent_id === "" ) throw new UIException("Missing required input companyparent id.");

            $this->CompanyParent_model->save_companyparent_preference(  $companyparent_id, "commission_tracking", "commission_type", $commission_type );
            $this->CompanyParent_model->save_companyparent_preference(  $companyparent_id, "commission_tracking", "commission_effective_date_type", $commission_effective_date_type );

            $payload = array();
            $payload['CommissionType'] = $commission_type;
            $payload['CommissionEffectiveDateType'] = $commission_effective_date_type;
            AuditIt("Commission tracking settings updated.", $payload, GetSessionValue('user_id'), null, $companyparent_id);



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
            $this->form_validation->set_rules('companyparent_id', 'identifier', 'required|numeric');

            if ( $this->form_validation->run() == FALSE )
            {
                $errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
                if ( $errors == "" ) $errors = "Invalid or missing inputs.";
                throw new UIException($errors);
            }

            $companyparent_id = getArrayStringValue("companyparent_id", $_POST);
            $hostname = trim(getArrayStringValue("hostname", $_POST));
            $username = trim(getArrayStringValue("username", $_POST));
            $password = trim(getArrayStringValue("password", $_POST));
            $destination = trim(getArrayStringValue("destination", $_POST));
            $port = trim(getArrayStringValue("port", $_POST));
            $ssh_key = trim(getArrayStringValue("ssh_key", $_POST));

            $encryption_key = GetCompanyParentEncryptionKey($companyparent_id);
            $encrypted_password = A2PEncryptString($password, $encryption_key);
            $encrypted_ssh_key = A2PEncryptString($ssh_key, $encryption_key);

            $this->FileTransfer_model->upsert_companyparent_file_transfer($companyparent_id, $hostname, $username, $destination, $port, $encrypted_password, $encrypted_ssh_key);


            AJAXSuccess("Configuration saved.");


        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
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
            $this->form_validation->set_rules('companyparent_id', 'identifier', 'required|numeric');

            if ( $this->form_validation->run() == FALSE )
            {
                $errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
                if ( $errors == "" ) $errors = "Invalid or missing inputs.";
                throw new UIException($errors);
            }

            $companyparent_id = GetArrayStringValue('companyparent_id', $_POST);
            $carrier_code = GetArrayStringValue('default_carrier_code', $_POST);

            SavePreference($companyparent_id, 'companyparent', 'carrier', 'default_carrier_code', $carrier_code);
            AJAXSuccess("Configuration saved.");
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
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
            if ( isset($_POST['token']) ) $tokens = $_POST['token'];
            $this->Mapping_model->remove_beneficiary_maps($identifier, $identifier_type, $target);
            foreach($tokens as $token) {
                $description = trim($token);
                $normalize = trim(strtoupper($token));
                if ($normalize !== '')
                {
                    $this->Mapping_model->add_beneficiary_map($identifier, $identifier_type, $normalize, $description, $target);
                }
            }


            AJAXSuccess("", base_url('parents/features/'.$identifier));

        } catch (UIException $e) {
            AjaxDanger($e->getMessage());
        } catch (SecurityException $e) {
            AccessDenied();
        } catch (Exception $e) {
            Error404();
        }
    }
    public function default_clarifications_save()
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
            $clarification_type = GetArrayStringValue('clarification_type', $_POST);

            // Validate we have enough data.
            if ( $identifier === '' ) throw new UIException("Missing required input identifier.");
            if ( $identifier_type === '' ) throw new UIException("Missing required input identifier type.");
            if ( $clarification_type === '' ) throw new UIException("Missing required input clarification_type.");

            // Validate our identifier.
            $valid_identifier = false;
            if ( $identifier_type === 'company' ) $valid_identifier = true;
            else if ( $identifier_type === 'companyparent' ) $valid_identifier = true;
            if ( ! $valid_identifier ) throw new UIException("Unknown identifier type.");

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
    public function parent_add() {

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");
            if ( APP_NAME === 'a2p-prodcopy' ) throw new Exception("Parent create not allowed in PRODCOPY.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission parent_company_write.");


			// Validate our inputs.
			$this->form_validation->set_rules('name','parent name','required');
			$this->form_validation->set_rules('address','parent address','required');
			$this->form_validation->set_rules('city','parent city','required');
			$this->form_validation->set_rules('state','parent state','required');
			$this->form_validation->set_rules('postal','parent postal','required');
            $this->form_validation->set_rules('seats','seats','required|numeric|greater_than[-1]');

            $validate_user = false;
            if ( GetArrayStringValue("email_address", $_POST) !== '' ) $validate_user = true;
            if ( GetArrayStringValue("first_name", $_POST) !== '' ) $validate_user = true;
            if ( GetArrayStringValue("last_name", $_POST) !== '' ) $validate_user = true;
            if ( $validate_user ) {
                $this->form_validation->set_rules('email_address', 'email address', 'required');
                $this->form_validation->set_rules('first_name', 'first name', 'required');
                $this->form_validation->set_rules('last_name', 'last name', 'required');
            }

			if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}

            $name = trim(getArrayStringValue("name", $_POST));
			$address = trim(getArrayStringValue("address", $_POST));
			$city = trim(getArrayStringValue("city", $_POST));
			$state = trim(getArrayStringValue("state", $_POST));
			$postal = trim(getArrayStringValue("postal", $_POST));
			$parent_id = trim(getArrayStringValue("company_parent_id", $_POST));
			$email_address = trim(getArrayStringValue("email_address", $_POST));
			$first_name = trim(getArrayStringValue("first_name", $_POST));
			$last_name = trim(getArrayStringValue("last_name", $_POST));
            $seats = getArrayIntValue("seats", $_POST);


			// The email address must be available to continue.
			if ( $email_address !== '' && ! IsUsernameAvailable( $email_address ) )
			{
				throw new UIException("Email address already in use.");
			}

            // The company_parent_name must be available to continue.
			if ( ! IsCompanyParentNameAvailable( $name ) )
			{
				throw new UIException("Business with that name already in use.");
			}
            if ( ! IsCompanyNameAvailable( $name ) )
            {
                throw new UIException("Business with that name already in use.");
            }

			// Create parent
			$company_parent_id = $this->CompanyParent_model->create_companyparent( $name, $address, $city, $state, $postal, $seats );

            try
            {
                // Create a custom encryption key for this parent.
                CreateCompanyParentEncryptionKey($company_parent_id);
            }
            catch(Exception $e)
            {
                LogIt('Error:'.__FUNCTION__, 'Unable to create a new companyparent', $e->getMessage());
                $this->Companyparent_model->delete_companyparent($company_parent_id);
                throw new UIException("Unable to create new security token.  Please contact support for assistance.");
            }



            if ( $email_address !== '' )
            {
                // Create user.
                $password = GenerateWeakPassword();
                $this->User_model->create_user( $email_address, $first_name, $last_name, $password );

                // Collect info about the new user.
                $new_user = $this->User_model->get_user( $email_address );
                $new_user_id = getArrayStringValue("user_id", $new_user);

                // Link user to CompanyParent.
                if ( ! $this->User_model->is_user_linked_to_parent( $new_user_id, $company_parent_id ) )
                {
                    $this->User_model->link_user_to_parent( $new_user_id, $company_parent_id );
                }

                // Grant user parent write rights.
                $this->User_model->grant_user_acl($new_user_id, "Parent Manager");
                $this->User_model->grant_user_acl($new_user_id, "PII Download");

                // Enable the user.
                $this->User_model->enable_user( $new_user_id );

                // Send onboarding email.
                if ( getArrayStringValue("onboarding", $_POST) == "on" )
                {
                    SendWelcomeEmail($new_user_id, $password, $name);
                }
            }

			AJAXSuccess("Parent created.");


		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
    public function parent_edit() {

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission parent_company_write.");

			// Validate our inputs.
			$this->form_validation->set_rules('name','parent name','required');
			$this->form_validation->set_rules('address','parent address','required');
			$this->form_validation->set_rules('city','parent city','required');
			$this->form_validation->set_rules('state','parent state','required');
			$this->form_validation->set_rules('postal','parent postal','required');
            $this->form_validation->set_rules('seats','seats','required|numeric|greater_than[-1]');

            if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}

			$name = trim(getArrayStringValue("name", $_POST));
            $name_orig = trim(getArrayStringValue("parent_name_orig", $_POST));
			$address = trim(getArrayStringValue("address", $_POST));
			$city = trim(getArrayStringValue("city", $_POST));
			$state = trim(getArrayStringValue("state", $_POST));
			$postal = trim(getArrayStringValue("postal", $_POST));
			$parent_id = trim(getArrayStringValue("parent_id", $_POST));
            $seats = getArrayIntValue("seats", $_POST);

            // The company_parent_name must be available to continue.
            if ( $name != $name_orig && ! IsCompanyParentNameAvailable( $name ) )
            {
                throw new UIException("Business with that name already in use.");
            }
            if ( ! IsCompanyNameAvailable( $name ) )
            {
                throw new UIException("Business with that name already in use.");
            }

			// Edit parent.
			$this->CompanyParent_model->update_companyparent( $name, $address, $city, $state, $postal, $parent_id, $seats );
			AJAXSuccess("Parent updated!");

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
    public function parent_disable() {

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

			$company_parent_id = getArrayStringValue("company_parent_id", $_POST);
			if ( $company_parent_id == "" ) throw new Exception("Invalid input company_parent_id");

			// disable parent.
			$this->CompanyParent_model->disable_companyparent( $company_parent_id );
			AJAXSuccess("Parent deactivated.");

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	public function parent_enable( ) {

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            $company_parent_id = getArrayStringValue("company_parent_id", $_POST);
			if ( $company_parent_id == "" ) throw new Exception("Invalid input company_parent_id");

			// disable parent.
			$this->CompanyParent_model->enable_companyparent( $company_parent_id );
			AJAXSuccess("Parent activated.");

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
    public function parent_changeto() {

		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

			// Validate our inputs.
			$this->form_validation->set_rules('company_parent_id','company parent id','required');

			if ( $this->form_validation->run() == FALSE )
			{
				$errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
				if ( $errors == "" ) $errors = "Invalid or missing inputs.";
				throw new UIException($errors);
			}

			$company_parent_id = getArrayStringValue("company_parent_id", $_POST);
			if ( $company_parent_id == "") throw new UIException("Unknown company parent.");

			ChangeToCompanyParent($company_parent_id);
			AJAXSuccess("", base_url("dashboard"));

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
    public function parent_rollback() {

        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
            if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            // Validate our inputs.
            $this->form_validation->set_rules('companyparent_id','companyparent_id','required');

            if ( $this->form_validation->run() == FALSE )
            {
                $errors = replaceFor(trim(strip_tags(validation_errors())), "\n", "  ");
                if ( $errors == "" ) $errors = "Invalid or missing inputs.";
                throw new UIException($errors);
            }

            $companyparent_id = getArrayStringValue("companyparent_id", $_POST);
            $companyparent = $this->CompanyParent_model->get_companyparent($companyparent_id);
            if ( $companyparent_id == "") throw new UIException("Unknown companyparent");
            if ( $companyparent_id == "1") throw new UIException("Working against the master account is not allowed.");

            $returning_uri = getArrayStringValue("uri", $_POST);

            // Rollback the current workflow.
            $wf_name = 'parent_import_csv'; // This is the only parent workflow right now.  Later this will be passed into this call.
            WorkflowRollback($companyparent_id, 'companyparent', $wf_name);

            AJAXSuccess("", base_url($returning_uri));

        }
        catch ( UIException $e ) { pprint_r($e->getMessage()); exit; AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { pprint_r($e->getMessage());  exit; AccessDenied(); }
        catch( Exception $e ) { pprint_r($e->getMessage());  exit; Error404(); }
    }

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
            AJAXSuccess("", base_url('parents/features/'.$identifier));

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
            AJAXSuccess("", base_url('parents/features/'.$identifier));

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
     * POST function that will toggle the current state of the specified
     * company parent feature.
     *
     * @param $feature_code
     * @param $companyparent_id
     */
    public function toggle_feature($feature_code, $companyparent_id, $target_type='', $target='' )
    {
        try {
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new SecurityException("Unexpected request method.");
            if (getArrayStringValue("ajax", $_POST) != "1") throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if (!IsLoggedIn()) throw new SecurityException("You must be logged into access this function.");
            if (!IsAuthenticated("")) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($feature_code) === '' ) throw new UIException("Missing required input feature_code.");
            if ( GetStringValue($companyparent_id) === '' ) throw new UIException("Missing required input companyparent_id.");

            $feature = $this->Feature_model->get_companyparent_feature($companyparent_id, $feature_code, $target_type, $target);
            if ( empty($feature_code) ) throw new UIException("Unable to find that feature.");

            $enabled = GetArrayStringValue("Enabled", $feature);
            if ( $enabled === "t" )
            {
                if ($feature_code === 'COLUMN_NORMALIZATION_REGEX') $this->_disable_column_normalization($companyparent_id, $feature);
                $this->Feature_model->disable_companyparent_feature($companyparent_id, $feature_code, $target_type, $target);

                $payload = array();
                $payload['feature'] = $feature_code;
                //AuditIt('Feature disabled.', $payload, GetSessionValue('user_id'), null, $companyparent_id);

            }
            elseif ( $enabled === 'f' )
            {
                if ($feature_code === 'COLUMN_NORMALIZATION_REGEX') $this->_enable_column_normalization($companyparent_id, $feature);
                $this->Feature_model->enable_companyparent_feature($companyparent_id, $feature_code, $target_type, $target);

                $payload = array();
                $payload['feature'] = $feature_code;
                //AuditIt('Feature enabled.', $payload, GetSessionValue('user_id'), null, $companyparent_id);
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
    public function parent_savepref()
    {
        try
        {
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new SecurityException("Unexpected request method.");
            if (getArrayStringValue("ajax", $_POST) != "1") throw new Exception("Javascript is required.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if (!IsLoggedIn()) throw new SecurityException("You must be logged into access this function.");

            $companyparent_id = GetSessionValue("companyparent_id");
            $group = getArrayStringValue("group", $_POST);
            $group_code = getArrayStringValue("group_code", $_POST);
            $value = getArrayStringValue("value", $_POST);

            // Validation
            if ($group == "") throw new Exception("Missing required input group.");
            if ($group_code == "") throw new Exception("Missing required input group_code.");
            if ($companyparent_id == "") throw new Exception("Missing required input group.");

            $pref = $this->CompanyParent_model->get_companyparent_preference($companyparent_id, $group, $group_code);
            if (!empty($pref) && getStringValue($value) == "") {
                // If we already have a preference and the value we are setting
                // is the empty string, delete the existing preference rather than
                // saving blank.
                $this->CompanyParent_model->remove_companyparent_preference($companyparent_id, $group, $group_code);
            } else {
                $this->CompanyParent_model->save_companyparent_preference($companyparent_id, $group, $group_code, $value);


                AJAXSuccess("Preference saved.");

            }
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

    // VALIDATORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    public function validate_parent() {
        $parent_name = GetArrayStringValue("parent_name", $_POST);
        if ( !IsLoggedIn() )
        {
            echo '{ "validation": 0 }';
            return false;
        }

        if (! IsCompanyParentNameAvailable($parent_name) ) {
            echo '{ "validation": 0 }';
            return false;
        }
        echo '{ "validation": 1 }';
        return true;
    }


    // RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function render_beneficiary_mapping_form($companyparent_id, $target_type, $target)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");


            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_write,parent_company_write") ) throw new SecurityException("Missing required write permission.");

            $form_html = TargetableFeatureBeneficiaryMappingForm($companyparent_id, 'companyparent', $target_type, $target);

            $array = array();
            $array['responseText'] = $form_html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_targetable_feature_form($companyparent_id)
    {
        try
        {
            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required write permission.");

            // organize inputs.
            $companyparent_id = getStringValue($companyparent_id);

            // validate required inputs.
            if ( $companyparent_id == "" ) throw new Exception("Invalid input companyparent_id");

            $targetable_feature_form = AddTargetableFeatureForm($companyparent_id, 'companyparent');

            $array = array();
            $array['responseText'] = $targetable_feature_form;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_rollback_companyparent_form($companyparent_id) {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required write permission.");

            // organize inputs.
            $companyparent_id = getStringValue($companyparent_id);

            // validate required inputs.
            if ( $companyparent_id == "" ) throw new Exception("Invalid input company_id");

            $changeto_form = $this->_companyparent_rollback_form($companyparent_id, getArrayStringValue("uri", $_POST));

            $array = array();
            $array['responseText'] = $changeto_form;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_main_table() {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            $array = array();
            $array['responseText'] = $this->_main_table();
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }

    public function render_commission_tracking_form($companyparent_id)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($companyparent_id) === '' ) throw new Exception("missing required input");

            $form_html = $this->_commission_tracking_form($companyparent_id);

            $array = array();
            $array['responseText'] = $form_html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_file_transfer_form($companyparent_id)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($companyparent_id) === '' ) throw new Exception("missing required input");

            $add_form_html = $this->_file_transfer_form($companyparent_id);

            $array = array();
            $array['responseText'] = $add_form_html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_column_normalization_form($companyparent_id, $target_type, $target)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($companyparent_id) === '' ) throw new Exception("missing required input");

            $add_form_html = $this->_column_normalization_form($companyparent_id, $target_type, $target);

            $array = array();
            $array['responseText'] = $add_form_html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_default_carrier($companyparent_id)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($companyparent_id) === '' ) throw new Exception("missing required input");

            $html = $this->_default_carrier_form($companyparent_id);

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_default_plan($companyparent_id)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($companyparent_id) === '' ) throw new Exception("missing required input");

            $html = FeatureDefaultPlanForm($companyparent_id, 'companyparent');

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function render_default_clarifications($companyparent_id)
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("") ) throw new SecurityException("Missing required permission.");

            if ( GetStringValue($companyparent_id) === '' ) throw new Exception("missing required input");

            $html = FeatureDefaultClarificationsForm($companyparent_id, 'companyparent');

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
	public function render_add_parent_form() {

        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            $add_form_html = $this->_parent_add_form();

            $array = array();
            $array['responseText'] = $add_form_html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }

    }
    public function render_edit_parent_form($parent_id) {

        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            // organize inputs.
            $parent_id = getStringValue($parent_id);

            // validate required inputs.
            if ( $parent_id == "" ) throw new Exception("Invalid input parent_id");

            $edit_form_html = $this->_parent_edit_form($parent_id);

            $array = array();
            $array['responseText'] = $edit_form_html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }

    }
    public function render_changeto_parent_form($parent_id) {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

			// organize inputs.
			$parent_id = getStringValue($parent_id);

			// validate required inputs.
			if ( $parent_id == "" ) throw new Exception("Invalid input parent_id");

			$changeto_form = $this->_parent_changeto_form($parent_id);

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
     * Wrap the company parent feature control HTML in a widget
     * so we can seamlessly refresh it from the UI.
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
            $companyparent_id = GetArrayStringValue(count($parts) - 2, $parts);

            if ( GetStringValue($companyparent_id) === "" ) throw new Exception("Missing required input companyparent_id");
            if ( GetStringValue($feature_code) === "" ) throw new Exception("Missing required input feature_code");

            $array['responseText'] = $this->_feature_control($companyparent_id, $feature_code);
            AJAXSuccess("", null, $array);
        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }


    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    private function _states() {
		return $this->Company_model->address_states();
	}
    private function _main_table() {
		$parents = $this->CompanyParent_model->get_all_parents();
		return RenderViewAsString("companyparent/main_table", array("data" => $parents));
	}
    private function _parent_add_form( ) {

		$form = new UIModalForm("add_parent_form", "add_parent_form", base_url("parents/add"));
		$form->setTitle("Add Parent");
		$form->setCollapsable(true);
		$form->addElement($form->textInput("name", "Parent Name", null, "Parent Name"));
        $form->addElement($form->hiddenInput("parent_name_orig", ""));
		$form->addElement($form->textInput("address", "Address", null, "1234 Main St."));
		$form->addElement($form->textInput("city", "City", null, "Des Moines"));
		$form->addElement($form->dropdown("state", "State", null, $this->_states(), null, "", "", false, true));
		$form->addElement($form->textInput("postal", "Postal Code", null, "55555"));
        $form->addElement($form->textInput("seats", "Seats", null, "0"));

        $form->addElement($form->htmlView("companyparent/add_user_help", array(), "add_user_help", "", true));

		$form->addElement($form->emailInput("email_address", "Email Address", null, "manager@company.com", array(), false, true));
		$form->addElement($form->textInput("first_name", "First Name", null, "John", array(), false, true));
		$form->addElement($form->textInput("last_name", "Last Name", null, "Smith", array(), false, true));
		$form->addElement($form->checkbox("onboarding", "", "Notify this user of their new account via email.", true, false, true));
		$form->addElement($form->submitButton("save_add_btn", "Add Parent", "btn-primary pull-right"));
		$form->addElement($form->button("cancel_add_btn", "Cancel", "btn-default pull-right"));
        $form->addElement($form->button("user_add_btn", "Add Parent User", "btn-default pull-left", false, array('open-label' => 'Add Parent User', 'closed-label' => "Don&apos;t Add Parent User")));
		$form_html = $form->render();

		return $form_html;
	}
    private function _parent_edit_form( $parent_id=null) {

        $name = null;
        $address = null;
        $city = null;
        $state = "";
        $postal = null;
        $seats = 0;

        $parent = $this->CompanyParent_model->get_companyparent($parent_id);
        if ( ! empty($parent) )
        {
            $name = getArrayStringValue("Name", $parent);
            $address = getArrayStringValue("Address", $parent);
            $city = getArrayStringValue("City", $parent);
            $state = getArrayStringValue("State", $parent);
            $postal = getArrayStringValue("Postal", $parent);
            $seats = getArrayIntValue("Seats", $parent);
        }

        $edit_form = new UIModalForm("edit_parent_form", "edit_parent_form", base_url("parents/edit"));
        $edit_form->setTitle("Edit Parent ( {$name} )");
        $edit_form->setCollapsable(true);
        $edit_form->addElement($edit_form->textInput("name", "Parent Name", $name, "Company Name"));
        $edit_form->addElement($edit_form->hiddenInput("parent_name_orig", $name));
        $edit_form->addElement($edit_form->textInput("address", "Address", $address, "1234 Main St."));
        $edit_form->addElement($edit_form->textInput("city", "City", $city, "Des Moines"));
        $edit_form->addElement($edit_form->dropdown("state", "State", "Iowa", $this->_states(), $state, "", "", false, true));
        $edit_form->addElement($edit_form->textInput("postal", "Postal Code", $postal, "55555"));
        $edit_form->addElement($edit_form->textInput("seats", "Seats", $seats, "0"));
        $edit_form->addElement($edit_form->hiddenInput("parent_id", $parent_id));
        $edit_form->addElement($edit_form->submitButton("save_edit_btn", "Update", "btn-primary pull-right"));
        $edit_form->addElement($edit_form->button("cancel_edit_btn", "Cancel", "btn-default pull-right"));

        if ( IsAuthenticated("") )
        {
            $edit_form->addElement($edit_form->submitButton("edit_parent_features_btn", "Edit Features", "btn-white pull-left", array('href' => base_url("parents/features/{$parent_id}"))));
        }

        $edit_form_html = $edit_form->render();

        return $edit_form_html;
    }
    private function _parent_changeto_form( $company_parent_id ) {

        $parent_name = "";
        $parent = $this->CompanyParent_model->get_companyparent($company_parent_id);
        if ( ! empty($parent) )
        {
            $parent_name = GetArrayStringValue("Name", $parent);
        }

        // Set the form title based on if we are doing an add or edit.
        $title = "Change To Parent ( {$parent_name} )";

        $form = new UIModalForm("changeto_parent_form", "changeto_parent_form", base_url("parents/changeto"));
        $form->setTitle($title);
        $form->setCollapsable(false);
        $form->addElement($form->htmlView("companyparent/confirm_changeto", array()));
        $form->addElement($form->hiddenInput("company_parent_id", getStringValue($company_parent_id)));
        $form->addElement($form->submitButton("yes_btn", "Yes", "btn-primary pull-right"));
        $form->addElement($form->button("no_btn", "No", "btn-default pull-right"));
        $form_html = $form->render();

        return $form_html;
    }

    /**
     * _feature_control
     *
     * Generate the HTML that allows us to control a company parent
     * feature.
     *
     * @param $companyparent_id
     * @param $feature_code
     * @return string|void
     */
    private function _feature_control($companyparent_id, $feature_code, $target_type=null, $target=null )
    {
        $feature = $this->Feature_model->get_companyparent_feature($companyparent_id, $feature_code, $target_type, $target);

        $code = GetArrayStringValue("Code", $feature);
        $long_description = GetArrayStringValue("Description", $feature);
        $enabled = GetArrayStringValue("Enabled", $feature);
        $target = GetArrayStringValue("Target", $feature);
        $target_type = GetArrayStringValue("TargetType", $feature);
        GetArrayStringValue('Targetable', $feature) === 't' ? $targetable = true : $targetable = false;

        // If the Description appears to be a view, load the text and replace the long description.
        if ( file_exists(APPPATH . "views/{$long_description}.php") )
        {
            $view_array = array();
            $view_array['enabled'] = $enabled;
            $view_array['companyparent_id'] = $companyparent_id;
            $view_array['code'] = $code;
            $view_array['type'] = "companyparent";
            $view_array['target'] = $target;
            $view_array['target_type'] = $target_type;
            $long_description = RenderViewAsString($long_description, $view_array );

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
        $button = new UIConfirmButton();
        $button->setSpinner(false);
        if ( $enabled ) $button->setLabel("Disable");
        if ( ! $enabled ) $button->setLabel("Enable");
        $button->setHref(base_url("parents/feature/toggle/{$code}/{$companyparent_id}/{$target_type}/$target"));
        if ( ! $targetable ) $button->setHref(base_url("parents/feature/toggle/{$code}/{$companyparent_id}"));
        $button->setCallback("RefreshCompanyParentFeatures");
        $button->setCallbackParameter("companyparent_feature_control_{$companyparent_id}_{$code}_{$target_type}_{$target}");
        if( ! $targetable ) $button->setCallbackParameter("companyparent_feature_control_{$companyparent_id}_{$code}");
        if ( $enabled ) $button->setColor("red");

        if ( $targetable )
        {
            $attributes = array();
            $attributes['identifier'] = $companyparent_id;
            $attributes['identifier_type'] = 'companyparent';
            $attributes['feature_code'] = $code;
            $attributes['target_type'] = $target_type;
            $attributes['target'] = $target;
            $button->addExtraEnabledButton("Delete", "RemoveCompanyParentFeature", base_url("parents/feature/remove/targetable_feature"), $attributes );
        }

        $button = $button->render();



        // Render the feature row.
        $view_array = array();
        $view_array = array_merge($view_array, array('short_description' => $short_description));
        $view_array = array_merge($view_array, array('long_description' => $long_description));
        $view_array = array_merge($view_array, array('button' => $button));
        $view_array = array_merge($view_array, array('enabled' => $enabled));
        $view_array = array_merge($view_array, array('target' => $target));
        $view_array = array_merge($view_array, array('target_type' => $target_type));
        return RenderViewAsString("companyparent/feature", $view_array);
    }
    private function _column_normalization_form($companyparent_id='', $target_type='', $target='')
    {
        $companyparent = $this->CompanyParent_model->get_companyparent($companyparent_id);
        $companyparent_name = GetArrayStringValue("Name", $companyparent);


        // Collect the SAVED data for this feature from the preference table.
        $pref = $this->CompanyParent_model->get_companyparent_preference( $companyparent_id, "column_normalization", "{$target_type}_{$target}_pattern" );
        $pattern = GetArrayStringValue("value", $pref);

        $pref = $this->CompanyParent_model->get_companyparent_preference( $companyparent_id, "column_normalization", "{$target_type}_{$target}_replace" );
        $replace = GetArrayStringValue("value", $pref);

        $pref = $this->CompanyParent_model->get_companyparent_preference( $companyparent_id, "column_normalization", "{$target_type}_{$target}_description" );
        $description = GetArrayStringValue("value", $pref);


        $form = new UIModalForm("column_normalization_form", "column_normalization_form", base_url("parents/feature/save/column_normalization"));
        $form->setTitle("Column Normalization ( {$companyparent_name} )");
        $form->setDescription("Add an additional custom search and replace to this columns normalization routine.  Activating the feature on the parent account will automatically enable this feature and the following settings on all associated companies.  ");


        $form->addElement($form->textInput('pattern','Search Pattern', $pattern, 'RegEx Search Pattern'));
        $form->addElement($form->textInput('replace','Replace', $replace, 'Empty String'));
        $form->addElement($form->textInput('description','Description', $description, 'Describe this custom normalization.'));

        $form->addElement($form->hiddenInput('companyparent_id', $companyparent_id));
        $form->addElement($form->hiddenInput('target', $target));
        $form->addElement($form->hiddenInput('target_type', $target_type));

        $form->addElement($form->submitButton("save_column_normalization_form", "Save Settings", "btn-primary pull-right"));
        $form->addElement($form->button("cancel_column_normalization_form", "Cancel", "btn-default pull-right"));
        $form_html = $form->render();

        return $form_html;
    }
    private function _default_carrier_form($companyparent_id='')
    {
        $companyparent = $this->CompanyParent_model->get_companyparent($companyparent_id);
        $companyparent_name = GetArrayStringValue("Name", $companyparent);

        $carrier_code = GetPreferenceValue($companyparent_id, 'companyparent', 'carrier', 'default_carrier_code');
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

        $form = new UIModalForm("default_carrier_form", "default_carrier_form", base_url("parents/feature/save/default_carrier"));
        $form->setTitle("Default Carrier ( {$companyparent_name} )");
        $form->setDescription("If no carrier is supplied in an import file, assume the following carrier was implied.");


        $form->addElement($dropdown);

        $form->addElement($form->hiddenInput('companyparent_id', $companyparent_id));
        $form->addElement($form->hiddenInput('carrier_code', $carrier_code));

        $form->addElement($form->submitButton("save_default_carrier_form", "Save Settings", "btn-primary pull-right"));
        $form->addElement($form->button("cancel_default_carrier_form", "Cancel", "btn-default pull-right"));
        $form_html = $form->render();

        return $form_html;
    }
    private function _commission_tracking_form($companyparent_id='')
    {
        $companyparent = $this->CompanyParent_model->get_companyparent($companyparent_id);
        $companyparent_name = GetArrayStringValue("Name", $companyparent);


        $pref = $this->CompanyParent_model->get_companyparent_preference( $companyparent_id, "commission_tracking", "commission_type" );
        $commission_type = GetArrayStringValue("value", $pref);

        $pref = $this->CompanyParent_model->get_companyparent_preference( $companyparent_id, "commission_tracking", "commission_effective_date_type" );
        $commission_effective_date_type = GetArrayStringValue("value", $pref);

        // Convert the commission effective date type into true/false
        // true for oldest life plan effective date, false for recent tier change
        $oed_variant = $commission_effective_date_type;
        if ( $oed_variant === OLDEST_LIFE_PLAN_EFFECTIVE_DATE ) $oed_variant = true;
        else $oed_variant = false;

        // Always warn the user if they change the parent.
        $has_data = true;

        // Get a list of the possible commission types.
        $commission_types = array();
        $types = $this->Commissions_model->select_commission_types();
        foreach($types as $type)
        {
            $code = GetArrayStringValue("Name", $type);
            $display = GetArrayStringValue("Display", $type);
            $commission_types[$code] = $display;
        }

        $form = new UIModalForm("commission_tracking_form", "commission_tracking_form", base_url("parents/feature/save/commission_tracking"));
        $form->setTitle("Commission Tracking ( {$companyparent_name} )");
        $form->setDescription("Choose the type of commission tracking you would like to enable.  Activating the feature on the parent account will automatically enable this feature and the following settings on all associated companies.  ");

        $form->addElement($form->htmlView('features/commission_tracking_parent_warning', array(), 'warning', '', true));
        $form->addElement($form->dropdown("commission_type", "Commission Type", "Select commission type.", $commission_types, $commission_type, "", "", false, true));
        $form->addElement($form->checkbox("oldest_effective_date", "", "Don't Reset Original Effective Date on Tier Change.", $oed_variant));

        $form->addElement($form->hiddenInput('companyparent_id', $companyparent_id));
        $form->addElement($form->hiddenInput('has_data', $has_data));
        $form->addElement($form->hiddenInput('orig_commission_type', $commission_type));
        $form->addElement($form->hiddenInput('orig_oldest_effective_date', GetIntValue($oed_variant)));

        $form->addElement($form->submitButton("save_commission_tracking_form", "Save Settings", "btn-primary pull-right"));
        $form->addElement($form->button("cancel_commission_tracking_form", "Cancel", "btn-default pull-right"));
        $form_html = $form->render();

        return $form_html;
    }
    private function _file_transfer_form($companyparent_id='')
    {
        $companyparent = $this->CompanyParent_model->get_companyparent($companyparent_id);
        $companyparent_name = GetArrayStringValue("Name", $companyparent);

        $encryption_key = GetCompanyParentEncryptionKey($companyparent_id);
        $transfer = $this->FileTransfer_model->get_file_transfer_by_companyparent_id($companyparent_id);

        $hostname = getArrayStringValue("Hostname", $transfer);
        $username = getArrayStringValue("Username", $transfer);
        $destination = getArrayStringValue("DestinationPath", $transfer);
        $port = getArrayStringValue("Port", $transfer);
        $encrypted_password = getArrayStringValue("EncryptedPassword", $transfer);
        $encrypted_ssh_key = getArrayStringValue("EncryptedSSHKey", $transfer);

        $password = A2PDecryptString($encrypted_password, $encryption_key);
        $ssh_key = A2PDecryptString($encrypted_ssh_key, $encryption_key);

        $form = new UIModalForm("file_transfer_form", "file_transfer_form", base_url("parents/feature/save/file_transfer"));
        $form->setTitle("SFTP File Transfer ( {$companyparent_name} )");
        $form->addElement($form->textInput("hostname", "Hostname", $hostname));
        $form->addElement($form->textInput("username", "Username", $username));
        $form->addElement($form->textInput("destination", "Destination Path", $destination, "/tmp"));
        $form->addElement($form->textInput("port", "Port", $port, "22"));

        $form->addElement($form->htmlView("features/file_transfer_password", array()));
        $form->addElement($form->textInput("password", "Password", $password));
        $form->addElement($form->textarea('ssh_key', 'Private SSH Key', $ssh_key, '3'));
        $form->addElement($form->hiddenInput('companyparent_id', $companyparent_id));
        $form->addElement($form->submitButton("save_file_transfer_form", "Save Settings", "btn-primary pull-right"));
        $form->addElement($form->button("cancel_file_transfer_form", "Cancel", "btn-default pull-right"));
        $form_html = $form->render();

        return $form_html;

    }
    private function _disable_column_normalization($companyparent_id, $feature)
    {
        $target = GetArrayStringValue('Target', $feature);
        $this->CompanyParent_model->disable_custom_normalization($companyparent_id, $target);
    }
    private function _enable_column_normalization($companyparent_id, $feature)
    {

        $target_type = GetArrayStringValue('TargetType', $feature);
        $target = GetArrayStringValue('Target', $feature);

        $pref = $this->CompanyParent_model->get_companyparent_preference( $companyparent_id, "column_normalization", "{$target_type}_{$target}_pattern" );
        $pattern = GetArrayStringValue("value", $pref);

        $pref = $this->CompanyParent_model->get_companyparent_preference( $companyparent_id, "column_normalization", "{$target_type}_{$target}_replace" );
        $replace = GetArrayStringValue("value", $pref);

        $pref = $this->CompanyParent_model->get_companyparent_preference( $companyparent_id, "column_normalization", "{$target_type}_{$target}_description" );
        $description = GetArrayStringValue("value", $pref);

        // Wait!  If the pattern is the empty string, then don't enable this.
        // Just disable it.  No pattern means disabled, but allows us to keep the
        // other attributes as preferences.
        if ( $pattern === '' )
        {
            $this->_disable_column_normalization($companyparent_id, $feature);
            return;
        }

        $rule = array();
        $rule['pattern'] = $pattern;
        $rule['replace'] = $replace;
        $rule['description'] = $description;

        $rules = array();
        $rules[] = $rule;

        $this->CompanyParent_model->enable_custom_normalization($companyparent_id, $target, $rules);

    }


    private function _companyparent_rollback_form( $companyparent_id, $returning_uri="" )
    {
        $companyparent_name = "";
        $companyparent = $this->CompanyParent_model->get_companyparent($companyparent_id);
        if ( ! empty($companyparent) )
        {
            $companyparent_name = getArrayStringValue("Name", $companyparent);
        }

        if ( ! IsAuthenticated("parent_company_write") )
        {
            // Set the form title based on if we are doing an add or edit.
            $title = "Rollback Multi-Company Import ( {$companyparent_name} )";
            $form = new UIModalForm("rollback_companyparent_form", "rollback_companyparent_form", base_url("parents/rollback"));
            $form->setTitle($title);
            $form->setCollapsable(true);
            $form->addElement($form->htmlView("companyparent/deny_rollback", array('companyparent_name' => $companyparent_name)));
            $form->addElement($form->hiddenInput("companyparent_id", getStringValue($companyparent_id)));
            $form->addElement($form->hiddenInput("uri", getStringValue($returning_uri)));
            $form->addElement($form->button("no_btn", "Okay", "btn-primary pull-right"));
            $form_html = $form->render();

            return $form_html;
        }

        // At this point, there is only one companyparent workflow.  Thus, we will just hard code it
        // here.  Later, this might have to be provided by the rollback request.
        $wf_name = 'parent_import_csv';

        // Pull some data so we know what we are about to rollback.
        $is_running = $this->Queue_model->does_companyparent_have_running_or_pending_jobs($companyparent_id);
        $workflow_flag = HasWorkflowStarted($companyparent_id, 'companyparent', $wf_name);

        if ( $is_running )
        {
            // Try again later
            $form = new UIModalForm("rollback_companyparent_form", "rollback_companyparent_form", base_url("parents/rollback"));
            $form->setTitle("Try Again Later");
            $form->addElement($form->htmlView("companyparent/try_again_later_rollback", array('companyparent_name' => $companyparent_name)));
            $form->addElement($form->hiddenInput("companyparent_id", getStringValue($companyparent_id)));
            $form->addElement($form->hiddenInput("uri", getStringValue($returning_uri)));
            $form->addElement($form->button("no_btn", "Okay", "btn-primary pull-right"));
            $form_html = $form->render();
            return $form_html;
        }
        if ( $workflow_flag  ) {

            // Workflow Rollback
            $form = new UIModalForm("rollback_companyparent_form", "rollback_companyparent_form", base_url("parents/rollback"));
            $form->setTitle("Rollback Multi-Company Import ( {$companyparent_name} )");
            $form->addElement($form->htmlView("companyparent/confirm_rollback_wizard", array('companyparent_name' => $companyparent_name)));
            $form->addElement($form->hiddenInput("companyparent_id", getStringValue($companyparent_id)));
            $form->addElement($form->hiddenInput("uri", getStringValue($returning_uri)));
            $form->addElement($form->submitButton("yes_btn", "Yes", "btn-primary pull-right"));
            $form->addElement($form->button("no_btn", "No", "btn-default pull-right"));
            $form_html = $form->render();
            return $form_html;

        }

        // Nothing to rollback.
        $form = new UIModalForm("rollback_companyparent_form", "rollback_companyparent_form", base_url("parents/rollback"));
        $form->setTitle("Rollback - ( {$companyparent_name} )");
        $form->addElement($form->htmlView("companyparent/confirm_rollback_no_results", array('companyparent_name' => $companyparent_name)));
        $form->addElement($form->hiddenInput("companyparent_id", getStringValue($companyparent_id)));
        $form->addElement($form->hiddenInput("uri", getStringValue($returning_uri)));
        $form->addElement($form->button("no_btn", "Okay", "btn-primary pull-right"));
        $form_html = $form->render();
        return $form_html;

    }
}
