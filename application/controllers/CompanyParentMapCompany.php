<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CompanyParentMapCompany extends A2PWorkflowStepController
{

    //protected $wf_stepname;             // See parent class for more details
    //protected $wf_name;                 // See parent class for more details
    //protected $identifier;              // See parent class for more details
    //protected $identifier_type;         // See parent class for more details
    //protected $timers;                  // See parent class for more details
    //protected $timer_array;             // See parent class for more details
    //protected $encryption_key;          // See parent class for more details
    //protected $company_id;              // See parent class for more details
    //protected $companyparent_id;        // See parent class for more details

    /**
     * index
     *
     * Render the company map page.
     *
     * @param $wf_name
     */
    public function index($wf_name) {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            // Properties
            // Set the global properties on this class based on the workflow name passed in.
            $this->setWorkflowProperties($wf_name, 'map');

            // Navigation Check!
            // Make sure users don't jump forwards in a workflow.
            if ( ! IsWorkflowWaiting( $this->wf_name, $this->wf_stepname, $this->identifier, $this->identifier_type) ) throw new UIException("Workflow not ready.");


            // BUSINESS LOGIC


            // Map Companies Widget
            $map_companies_widget = new UIWidget("map_companies_widget");
            $map_companies_widget->setBody( $this->_company_map_widget($this->companyparent_id) );
            $map_companies_widget->setHref( base_url("parent/map/company/widget") );
            $widget = $map_companies_widget->render();

            $summary_widget = new UIWidget("company_map_summary_widget");
            $summary_widget->setBody( $this->_company_map_summary_widget($this->companyparent_id) );
            $summary_widget->setHref( base_url("parent/map/company/widget/summary") );
            $summary_widget = $summary_widget->render();

            $confirm_widget = new UIWidget('confirm_company_create_widget');
            $confirm_widget->setHref( base_url("parent/map/company/confirm/IDENTIFIER") );
            $confirm_widget = $confirm_widget->render();

            $page_header = new UIFormHeader();
            $page_header->setTitle("Map Company");
            $page_header = $page_header->render();

            // Generate the form for this step.
            $form = new UIWizardForm("parent_map_form");
            $form->setAction(base_url("parent/map/company/validate"));
            $form->addTopWizardButton($form->button("parent_map_continue_btn", "Continue", "btn-working", true, array(), true));
            $form->addTopWizardButton($form->button("workflow_start_over_btn", "Start Over", " btn-wf-rollback btn-default pull-left m-l-0", false, array("href" => base_url("workflow/rollback/{$wf_name}"))));
            $form->addTopWizardButton($form->button("workflow_parent_match_btn", "Match", " btn-wf-moveto btn-default pull-left m-l-0", false, array("href" => base_url("workflow/moveto/{$wf_name}/parse"))));
            $form->addElement($form->top_buttons());
            $form->addElement($form->hiddenInput("wf_name", $wf_name));
            $form->addElement($form->hiddenInput("widget_type", $this->_getWidgetType($this->companyparent_id)));
            $form = $form->render();

            $view_array = array();
            $view_array = array_merge($view_array, array("page_header" => $page_header));
            $view_array = array_merge($view_array, array("form" => $form));

            $view_array['companyparent_id'] = $this->companyparent_id;
            $view_array['widget'] = $widget;
            $view_array['summary_widget'] = $summary_widget;
            $view_array['confirm_widget'] = $confirm_widget;

            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("companyparentmap/js_assets")));
            $page_template = array_merge($page_template, array("view" => "companyparentmap/default"));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch ( UIException $e ) { redirect(base_url("dashboard/parent")); }
        catch( SecurityException $e ) { AccessDenied($e->getMessage()); }
        catch( Exception $e ) { Error404( $e ); }
    }


    /**
     * validate ( POST )
     *
     * When the user attempts to move to the next screen by hitting the continue
     * button.  This is the function that is called.  It will validate that the
     * data we needed from the user is all in order.  If not, an error will be
     * issued, else we will move the workflow to the next step.
     *
     */
    public function validate() {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            // Collect interesting post data.
            $wf_name = GetArrayStringValue('wf_name', $_POST);
            $widget_type = GetArrayStringValue('widget_type', $_POST);

            // Validate the post data.
            if ( GetStringValue($wf_name) === '' ) throw new Exception("Missing required input: wf_name");
            if ( GetStringValue($widget_type) === '' ) throw new Exception("Missing required input: widget_type");

            // Set the workflow properties on the class.
            $this->setWorkflowProperties($wf_name, 'map');

            // Default the import date if the use did not physically elect it themselves.
            $this->_default_importdate($this->companyparent_id);

            if ( $widget_type === 'multiple_companies')
            {
                // Make sure we have a map record for each of unique normalized import item.
                $data = $this->CompanyParentMap_model->select_importdata($this->companyparent_id);
                foreach($data as $item)
                {
                    $normalized = GetArrayStringValue("CompanyNormalized", $item);
                    if ( ! $this->CompanyParentMap_model->exists_mapping($this->companyparent_id, $normalized) )
                    {
                        throw new UIException("Unable to save mapping.  Please try again.");
                    }
                }

                // Remove the "single_company" preference, if it exists, because they elected multiple companies.
                RemovePreference($this->identifier, $this->identifier_type, 'companyparentmap', 'selected_company_id');


                // Audit it!
                $audit = array();
                $audit['ImportDate'] = GetPreferenceValue($this->companyparent_id, 'companyparent', 'companyparentmap', 'import_date');
                $count = 1;
                foreach( $data as $item )
                {
                    $company_id = GetArrayStringValue('CompanyId', $item);
                    $company = $this->Company_model->get_company($company_id);
                    $company_name = GetArrayStringValue("company_name", $company);
                    $key = "CompanyName" . $count;
                    $audit[$key] = $company_name;
                }
                AuditIt("Mapped companies for multi-company upload.", $audit);

            }
            else
            {
                // Make sure we have the saved property.
                $company_id = GetPreferenceValue($this->companyparent_id, 'companyparent', 'companyparentmap', 'selected_company_id');
                if ( GetStringValue($company_id) === '' ) throw new UIException("Unable to save mapping.  Please try again.");

                $company = $this->Company_model->get_company($company_id);
                $company_name = GetArrayStringValue("company_name", $company);

                // Audit it!
                $audit = array();
                $audit['ImportDate'] = GetPreferenceValue($this->companyparent_id, 'companyparent', 'companyparentmap', 'import_date');;
                $audit['CompanyName'] = $company_name;
                AuditIt("Mapped companies for multi-company upload.", $audit);


            }

            // Take a snapshot of the data as it stands now and then move the workflow forward.
            $this->takeSnapshot();
            WorkflowStateMoveForward($this->identifier, $this->identifier_type, $wf_name);
            WorkflowStartBackgroundJob($this->identifier, $this->identifier_type, $wf_name, GetSessionValue('user_id'));
            AJAXSuccess("Good news Everybody!   ", base_url('dashboard/parent') );
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }


    /**
     * save ( POST )
     *
     * This function is called via AJAX from the multiple company widget.  It takes in
     * information about the mapping the user has elected and saves that data to the
     * map table as needed.
     *
     * Other than just saving a map, this will also recognize "add" and "ignore".
     * - Ignore: removes an existing mapping.
     * - Add: Generates a new company from the input data.
     *
     */
    public function save() {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            // Collect and organize our inputs.
            $companyparent_id = GetSessionValue("companyparent_id");
            $input_name = GetArrayStringValue('name', $_POST);      // Key: map-12
            $value = GetArrayStringValue('value', $_POST);          // Action: ie. "add"
            $label = GetArrayStringValue('label', $_POST);          // Dropdown label: "Add New Company"
            $importdata_id = fRightBack($input_name, "-");

            // Collect the user description of this company based on the mapping id passed in.
            $import_data = $this->CompanyParentMap_model->select_importdata_by_id($importdata_id);
            $user_description = GetArrayStringValue("Company", $import_data);
            $normalized_user_description = trim(strtoupper($user_description));

            if ( $value === 'ignore' )
            {
                // Remove the mapping.
                $mapping = $this->CompanyParentMap_model->select_mapping($companyparent_id, $normalized_user_description);
                foreach($mapping as $map)
                {
                    $mapping_id = GetArrayStringValue("Id", $map);
                    $this->CompanyParentMap_model->ignore_mapping_by_id($mapping_id);
                }
                AJAXSuccess("ignored");
            }
            else if ( $value === 'add' )
            {
                $this->load->model('api/APICompany_model', 'APICompany_model');
                $inputs = array();
                $inputs['name'] = $user_description;
                $inputs['parent_identifier'] = $companyparent_id;
                $inputs['parent_identifier_type'] = "companyparent";

                $message = $this->APICompany_model->company_create($inputs);
                if ( ! $message->status )
                {
                    LogIt("Trouble creating new company", $message->message, $inputs, GetSessionValue('user_id'), '', $companyparent_id);
                    AJAXDanger("Unable to create company.");
                }

                // We just created a new company.  Set the 'value' to the new company id so we
                // can create the mapping next.
                $results = (array) $message->results;
                $value = GetArrayStringValue('company_id', $results);

                // Audit this transaction.
                $company = $this->Company_model->get_company($value);
                $payload = array();
                $payload['CompanyId'] = GetArrayStringValue('company_id', $company);
                $payload['CompanyName'] = GetArrayStringValue('company_name', $company);
                AuditIt("Company created.", $payload);


            }

            // Save our data if VALUE is numeric, which indicates it is a company_id
            if ( StripNonNumeric($value) !== '' )
            {
                $company_id = $value;
                $this->CompanyParentMap_model->upsert_mapping($companyparent_id, $normalized_user_description, $user_description, $company_id, false);
            }


            AJAXSuccess("Mapping saved.");
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }

    function save_importdate()
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            $companyparent_id = GetSessionValue('companyparent_id');
            $month = GetArrayStringValue('month', $_POST);
            $year  = GetArrayStringValue('year', $_POST);


            if ( GetStringValue($companyparent_id) === '') throw new SecurityException("Missing companyparent_id");
            if ( GetStringValue($month) === '') throw new UIException("Missing required input month.");
            if ( GetStringValue($year) === '') throw new UIException("Missing required input year.");

            $import_date = "{$month}/01/{$year}";
            SavePreference($companyparent_id, 'companyparent', 'companyparentmap', 'import_date', $import_date);


            AJAXSuccess("Mapping saved.");
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }

    /**
     * save_single_mapping
     *
     * If the user did not map the company column, then we have a situation
     * where the user is just going to tell us what company they want to map
     * all of the data to.
     *
     * This function will save the selected company as a preference so we
     * can access it later.
     */
    public function save_single_mapping()
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");

            // Default to the companyparent in session.
            $companyparent_id = GetSessionValue("companyparent_id");

            // INPUTS
            // Extract and clean the inputs selected by the user.
            $company_id = GetArrayStringValue('company_id', $_POST);

            if ( GetStringValue($companyparent_id) === '' ) throw new Exception("Missing required input: companyparent_id");
            if ( GetStringValue($company_id) === '' ) throw new Exception("Missing required input: company_id");

            $company = $this->Company_model->get_company($company_id);
            if ( empty($company_id) ) throw new Exception("Unable to locate the specified company");

            // Save the company_id as a preference.
            SavePreference($companyparent_id, 'companyparent', 'companyparentmap', 'selected_company_id', $company_id);

            AJAXSuccess("Mapping saved.");
        }
        catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }

    /**
     * render_company_map_widget ( POST )
     *
     * This function will generate the widget that displays the mapping form to
     * the user.  This could be for a single or multiple company experience.
     *
     * The HTML is returned in an AJAX response object.
     *
     */
    public function render_company_map_widget(  )
    {
        try
        {
            // Check method.
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new Exception("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required write permission.");

            $html = $this->_company_map_widget( GetSessionValue('companyparent_id') );

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }

    public function render_company_map_summary_widget(  )
    {
        try
        {
            // Check method.
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new Exception("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required write permission.");

            $companyparent_id = GetSessionValue('companyparent_id');
            $html = $this->_company_map_summary_widget( $companyparent_id );

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }

    function render_company_map_confirm_widget( $mapping )
    {
        try
        {
            // Check method.
            if (getStringValue($this->input->server('REQUEST_METHOD')) != "POST") throw new Exception("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required write permission.");

            $companyparent_id = GetSessionValue('companyparent_id');
            $html = $this->_company_map_confirm_widget( $companyparent_id, $mapping );

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }

    /**
     * _default_importdate
     *
     * If the companyparent does not have an import_date defined then auto select the
     * default date for them.
     *
     * @param $companyparent_id
     * @throws Exception
     */
    private function _default_importdate($companyparent_id)
    {
        $suggested = getdate(strtotime("+1 months"));
        $suggested_month = getArrayStringValue("mon", $suggested);
        $suggested_month = str_pad($suggested_month,2,"0",STR_PAD_LEFT);
        $suggested_year = getArrayStringValue("year", $suggested);

        $start = GetPreferenceValue($companyparent_id, 'companyparent', 'companyparentmap', 'import_date');
        if ( $start === '' )
        {
            $import_date = "{$suggested_month}/01/{$suggested_year}";
            SavePreference($companyparent_id, 'companyparent', 'companyparentmap', 'import_date', $import_date);
        }
    }



    /**
     * _getWidgetType
     *
     * Returns "mutliple_companies" or "single_company" based on which type of widget
     * we should show the user.
     *
     * @param $companyparent_id
     * @return string
     */
    private function _getWidgetType($companyparent_id)
    {
        // Did the user map the company column?
        $matched = $this->Mapping_model->does_column_mapping_exist('', $companyparent_id, "company");

        if ($matched) return "multiple_companies";
        return "single_company";
    }

    /**
     * _company_map_widget
     *
     * Generates the raw HTML for the widget that allows the user to
     * map multiple companies or just a single company based on their
     * column mappings.
     *
     * @param $companyparent_id
     * @return string|void
     * @throws Exception
     */
    private function _company_map_widget( $companyparent_id )
    {
        // Get a sorted list of companies for the company parent.
        $companies = $this->CompanyParent_model->get_companies_by_parent($companyparent_id);
        uasort($companies, 'AssociativeArraySortFunction_company_name');

        // Which type of widget should we generate?  Single Company or Multiple Company?
        $widget_type = $this->_getWidgetType($companyparent_id);

        if ( $widget_type === "multiple_companies" )
        {
            // Show a version of the widget that allows you to map multiple companies.
            $imported_companies = array();

            $index = 0;
            $data = $this->CompanyParentMap_model->select_importdata($companyparent_id);
            foreach($data as $item)
            {
                $item['import_date'] = "";
                $item['import_date_description'] = "";
                $company_id = GetArrayStringValue('CompanyId', $item);
                if ( $company_id !== '' )
                {
                    $item['ImportDate'] = GetUploadDate($company_id);
                    $item['ImportDateDescription'] = GetImportDateDescription($company_id);
                }
                $imported_companies[$index] = $item;
                $index++;
            }

            $view_array = array();
            $view_array['imported_companies'] = $imported_companies;
            $view_array['companyparent_id'] = $companyparent_id;
            $view_array['companies'] = $companies;
            $view_array['import_date'] = $this->_getUserElectedStartMonth($companyparent_id);
            return RenderViewAsString('companyparentmap/multi_company_widget', $view_array);

        }

        // SINGLE COMPANY WIDGET!
        // Show a version of the mapping that allows the user to pick a single
        // company for the whole file.

        $a2p_match = $this->_isA2PColumnMatch($companyparent_id, 'companyparent', 'company');
        $selected_company_id = GetPreferenceValue($companyparent_id, 'companyparent', 'companyparentmap', 'selected_company_id');

        $view_array = array();
        $view_array['companies'] = $companies;
        $view_array['a2p_match'] = $a2p_match;
        $view_array['selected_company_id'] = $selected_company_id;
        return RenderViewAsString('companyparentmap/single_company_widget', $view_array);

    }
    private function _company_map_confirm_widget( $companyparent_id, $mapping )
    {
        // Find the import id for the mapping passed in so we can display the
        // name of the company we will be creating.
        $import_id = StripNonNumeric($mapping);
        $data = $this->CompanyParentMap_model->select_importdata_by_id($import_id);
        $company_name = GetArrayStringValue('Company', $data);

        // Construct the view array that will be used in the form on the htmlView.
        $view_array = array();
        $view_array['mapping'] = $mapping;
        $view_array['companyparent_id'] = $mapping;
        $view_array['company_name'] = $company_name;

        // Generate the Form
        $form = new UIModalForm("confirm_new_company_form", "confirm_new_company_form", base_url("todo"));
        $form->setTitle("Company Create");
        $form->setCollapsable(true);
        $form->addElement($form->htmlView("companyparentmap/confirm_company_create", $view_array));
        $form->addElement($form->hiddenInput("companyparent_id", $companyparent_id));
        $form->addElement($form->hiddenInput("mapping", $mapping));
        $form->addElement($form->submitButton("yes_btn", "Yes", "btn-primary pull-right"));
        $form->addElement($form->button("no_btn", "No", "btn-default pull-right"));
        $form_html = $form->render();
        return $form_html;
    }
    private function _company_map_summary_widget( $companyparent_id )
    {
        // Which type of widget should we generate?  Single Company or Multiple Company?
        $widget_type = $this->_getWidgetType($companyparent_id);

        if ( $widget_type === "multiple_companies" )
        {
            return $this->_company_map_summary_widget_multiple_companies($companyparent_id);
        }
        else
        {
            // This is the company that will be elected!  It could be the empty string.
            $company_id = GetPreferenceValue($companyparent_id, 'companyparent', 'companyparentmap', 'selected_company_id');
            return $this->_company_map_summary_widget_single_company($companyparent_id, $company_id);
        }
    }
    private function _company_map_summary_widget_single_company( $companyparent_id, $selected_company_id )
    {
        $this->load->helper('parentmapuploadcompanies');

        // Get the start date last used in this workflow for the companyparent in question.
        $start = GetUserElectedStartMonthForCompanyParentMap($companyparent_id);

        // Get a list of all the companies that are associated with this companyparent and
        // expand their data to include additional information to support the mapping process.
        $companies = GetExpandedCompaniesForParentMap($companyparent_id);

        // Itererate over the collection of companies and split them into three buckets.
        // importing, not importing and unavailable.
        $importing = array();
        $not_importing = array();
        $unavailable = array();
        foreach($companies as $company)
        {

            $item_import_date = GetArrayStringValue('import_date', $company);
            $item_company_id = GetArrayStringValue('company_id', $company);
            $available = GetArrayStringValue('available', $company);

            if ( $available === 'FALSE' )
            {
                // If the company is busy processing other files, then it is unavailable for
                // import.  Slot the company into the unavailable bucket.
                $unavailable[] = $company;
            }
            else if ( $item_import_date == $start && GetStringValue($selected_company_id) === $item_company_id && GetStringValue($selected_company_id) !== '' )
            {
                $importing[] = $company;
            }
            else
            {
                $not_importing[] = $company;
            }
        }

        // Sort our collections before we display them.
        uasort($not_importing, 'AssociativeArraySortFunction_company_name');
        uasort($importing, 'AssociativeArraySortFunction_company_name');
        uasort($unavailable, 'AssociativeArraySortFunction_company_name');

        $view_array = array();
        $view_array['companyparent_id'] = $companyparent_id;
        $view_array['companies'] = $companies;
        $view_array['importing'] = $importing;
        $view_array['not_importing'] = $not_importing;
        $view_array['unavailable'] = $unavailable;
        $view_array['start_date'] = FormatDateMonthYYYY($start);
        $view_array['start_months'] = $this->_startMonthsDropdown($companyparent_id);
        $view_array['start_years'] = $this->_startYearsDropdown($companyparent_id);

        return RenderViewAsString('companyparentmap/summary_widget', $view_array);
    }
    private function _company_map_summary_widget_multiple_companies( $companyparent_id )
    {
        $this->load->helper('parentmapuploadcompanies');

        // Get the start date last used in this workflow for the companyparent in question.
        $start = GetUserElectedStartMonthForCompanyParentMap($companyparent_id);

        // Get a list of all the companies that are associated with this companyparent and
        // expand their data to include additional information to support the mapping process.
        $companies = GetExpandedCompaniesForParentMap($companyparent_id);

        // Get a collection of company mappings for this companyparent that have been mapped
        // before.  We will use this list to auto-select item in the dropdown.
        $mapped_companies = $this->CompanyParentMap_model->select_importdata($companyparent_id);

        $importing = array();
        $not_importing = array();
        $unavailable = array();
        foreach($companies as $company)
        {
            $item_import_date = GetArrayStringValue('import_date', $company);
            $item_company_id = GetArrayStringValue('company_id', $company);
            $available = GetArrayStringValue('available', $company);

            if ( $available === 'FALSE' )
            {
                // If the company is busy processing other files, then it is unavailable for
                // import.  Slot the company into the unavailable bucket.
                $unavailable[] = $company;
            }
            else if ( $item_import_date == $start )
            {
                // The company has the correct import date based on what the user has elected to import.
                // Find the mapped company data. If it's not Ignored, add it to the importing list.
                $mapped_company = array();
                $index = array_search($item_company_id, array_column($mapped_companies, 'CompanyId'));
                if ( $index !== FALSE ) $mapped_company = $mapped_companies[$index];

                if ( $index === FALSE )
                {
                    // We did not identify this mapping, do not map it.
                    $not_importing[] = $company;
                }
                else
                {
                    $ignored = GetArrayStringValue('Ignored', $mapped_company);
                    if ( $ignored === 't' )
                    {
                        // We have a mapping, but the user elected to ignore it.
                        $not_importing[] = $company;
                    }
                    else
                    {
                        // We have a mapping and it's not ignored!
                        $importing[] = $company;
                    }
                }

            }
            else
            {
                $not_importing[] = $company;
            }
        }

        // Sort our collections before we display them.
        uasort($not_importing, 'AssociativeArraySortFunction_company_name');
        uasort($importing, 'AssociativeArraySortFunction_company_name');
        uasort($unavailable, 'AssociativeArraySortFunction_company_name');

        $view_array = array();
        $view_array['companyparent_id'] = $companyparent_id;
        $view_array['companies'] = $companies;
        $view_array['importing'] = $importing;
        $view_array['not_importing'] = $not_importing;
        $view_array['unavailable'] = $unavailable;
        $view_array['start_date'] = FormatDateMonthYYYY($start);
        $view_array['start_months'] = $this->_startMonthsDropdown($companyparent_id);
        $view_array['start_years'] = $this->_startYearsDropdown($companyparent_id);

        return RenderViewAsString('companyparentmap/summary_widget', $view_array);

    }
    private function _isA2PColumnMatch( $identifier, $identifer_type, $column_code )
    {
        $company_id = '';
        $companyparent_id = $identifier;
        if ( $identifer_type === 'company' )
        {
            $company_id = $identifier;
            $companyparent_id = GetCompanyParentId($company_id);
        }


        // Did the user map the company column?
        $matched = $this->Mapping_model->does_column_mapping_exist($company_id, $companyparent_id, $column_code);

        $a2p_matched = FALSE;
        if ( ! $matched )
        {
            // Get an array of possible column headings the user could supply that we will
            // assume is the company column.
            $accepted_column_headings = $this->Mapping_model->get_mapping_column_headers($column_code);
            $accepted_column_headings = array_map('strtoupper', $accepted_column_headings);
            $accepted_column_headings = array_map('trim', $accepted_column_headings);

            // Get a JSON array of the user inputs mapped to their coloumn number.
            $value = GetPreferenceValue($this->identifier, $this->identifier_type, 'headers', 'user_names');
            $data = json_decode($value, true);

            if( isset($data['name_lookup']) ) {
                $data = $data['name_lookup'];
                $user_supplied_headings = array_keys($data);

                foreach($user_supplied_headings as $user_supplied_heading)
                {
                    if ( in_array($user_supplied_heading, $accepted_column_headings) ) $a2p_matched = TRUE;
                }
            }
        }
        return $a2p_matched;
    }

    private function _getUserElectedStartMonth($companyparent_id)
    {
        $start = GetPreferenceValue($companyparent_id, 'companyparent', 'companyparentmap', 'import_date');

        if ( $start === '' )
        {
            $suggested = getdate(strtotime("+1 months"));
            $suggested_month = getArrayStringValue("mon", $suggested);
            $suggested_month = str_pad($suggested_month,2,"0",STR_PAD_LEFT);
            $suggested_year = getArrayStringValue("year", $suggested);
            $start = "{$suggested_month}/01/{$suggested_year}";
        }

        return $start;
    }

    private function _isCompanyUnavailable($company_id)
    {
        $upload_date = GetUploadDate($company_id);
        if ( $upload_date === '' ) return false;
        return true;
    }
    private function _startMonthsDropdown($companyparent_id)
    {
        $suggested_start_date = $this->_getUserElectedStartMonth($companyparent_id);
        $suggested_start_month = fLeft($suggested_start_date, "/");

        $start_month_dropdown = new Dropdown("inline");
        $start_month_dropdown->setId("start_month");
        $start_month_dropdown->addItem('01', 'January');
        $start_month_dropdown->addItem('02', 'February');
        $start_month_dropdown->addItem('03', 'March');
        $start_month_dropdown->addItem('04', 'April');
        $start_month_dropdown->addItem('05', 'May');
        $start_month_dropdown->addItem('06', 'June');
        $start_month_dropdown->addItem('07', 'July');
        $start_month_dropdown->addItem('08', 'August');
        $start_month_dropdown->addItem('09', 'September');
        $start_month_dropdown->addItem('10', 'October');
        $start_month_dropdown->addItem('11', 'November');
        $start_month_dropdown->addItem('12', 'December');
        $start_month_dropdown->callback_onchange = "CompanyParentMapStartDateDropdownOnChange";
        $start_month_dropdown->scrollable_flg = false;
        $start_month_dropdown->selected = $suggested_start_month;
        $start_month_dropdown = $start_month_dropdown->render();

        return $start_month_dropdown;
    }
    private function _startYearsDropdown($companyparent_id)
    {
        $suggested_start_date = $this->_getUserElectedStartMonth($companyparent_id);
        $suggested_start_year = fRightBack($suggested_start_date, "/");

        $current_year = getIntValue(date("Y"));
        $last_year = $current_year - 1;
        $next_year = $current_year + 1;

        $start_year_dropdown = new Dropdown("inline");
        $start_year_dropdown->setId("start_year");
        $start_year_dropdown->addItem($last_year, $last_year);
        $start_year_dropdown->addItem($current_year, $current_year);
        $start_year_dropdown->addItem($next_year, $next_year);
        $start_year_dropdown->callback_onchange = "CompanyParentMapStartDateDropdownOnChange";
        $start_year_dropdown->scrollable_flg = false;
        $start_year_dropdown->selected = $suggested_start_year;
        $start_year_dropdown = $start_year_dropdown->render();
        return $start_year_dropdown;
    }

}
