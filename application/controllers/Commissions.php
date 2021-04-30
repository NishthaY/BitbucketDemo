<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Commissions extends SecureController {


    function __construct(){
        parent::__construct();
    }

    public function support( $company_id, $life_id, $plan_id=null ) {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            //if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");

            $company_name = "";
            $carrier_id = "";
            $carrier = "";
            $plantype_id = "";
            $plantype = "";
            $plan = "";
            $firstname = "";
            $lastname = "";
            $commissions = array();


            // Get the most recent commission plan id for this life.
            $most_recent_plan_id = $this->Commissions_model->select_recent_commission_plan_by_life($company_id, $life_id);
            if ( GetStringValue($plan_id) === '' ) $plan_id = $most_recent_plan_id;

            // Get a list of all plans this user has belonged to.
            $plans = $this->Commissions_model->select_commission_plans_by_life($company_id, $life_id);

            // Warn
            $warn = false;
            if ( GetStringValue($plan_id) !== GetStringValue($most_recent_plan_id) ) $warn = true;



            $view = "commissions/support";

            // Collect all of the commission data for this life/plan.
            $data = $this->GenerateCommissions_model->select_commissions_by_lifeplan($life_id, $plan_id);
            if ( ! empty($data) )
            {
                // We have data!  Examine the first row and collect all the static
                // property data.  This is the data that does not have the potential
                // to change on every row.
                $row = $data[0];
                $company_id = GetArrayStringValue("CompanyId", $row);

                $encryption_key = GetCompanyEncryptionKey($company_id);
                $row = A2PDecryptArray($row, $encryption_key);

                $company_name = GetArrayStringValue("CompanyName", $row);
                $carrier_id = GetArrayStringValue("CarrierId", $row);
                $carrier = GetArrayStringValue("Carrier", $row);
                $plantype_id = GetArrayStringValue("PlanTypeId", $row);
                $plantype = GetArrayStringValue("PlanType", $row);
                $plan = GetArrayStringValue("Plan", $row);
                $firstname = GetArrayStringValue("FirstName", $row);
                $lastname = GetArrayStringValue("LastName", $row);




                // Now we will create a DTO object for each grouping.
                $dto = array();
                $current_display_date = "";
                $commissions = array();
                foreach($data as $item)
                {
                    $item = A2PDecryptArray($item, $encryption_key);
                    $display_date = GetArrayStringValue("DisplayDate", $item);

                    // If the display date changes, save the DTO we have been creating
                    // and make a new one.
                    if ( $display_date != $current_display_date )
                    {
                        // Save what we have found if we have something.
                        if ( !empty($dto) ) {
                            $commissions[] = $dto;
                        }
                        $dto = array();
                        $current_display_date = $display_date;

                        // Collect the new data that is static for all rows.
                        $dto = array();
                        $dto['display_date'] = $display_date;
                        $dto['company'] = GetArrayStringValue("CompanyName", $item);
                        $dto['company_id'] = GetArrayStringValue("CompanyId", $item);
                        $dto['import_date'] = GetArrayStringValue("ImportDate", $item);
                        $dto['firstname'] = GetArrayStringValue("FirstName", $item);
                        $dto['lastname'] = GetArrayStringValue("LastName", $item);

                        $dto['data'] = array();


                    }

                    // Capture the row.
                    $dto['data'][] = $item;

                }
                if ( !empty($dto) ) $commissions[] = $dto;

            }
            else
            {
                // No Results!
                $view = "commissions/support_noresults";

            }

            $view_array = array();
            $view_array = array_merge($view_array, array( "company_id" => $company_id));
            $view_array = array_merge($view_array, array( "company" => $company_name));
            $view_array = array_merge($view_array, array( "carrier_id" => $carrier_id));
            $view_array = array_merge($view_array, array( "carrier" => $carrier));
            $view_array = array_merge($view_array, array( "plantype_id" => $plantype_id));
            $view_array = array_merge($view_array, array( "plantype" => $plantype));
            $view_array = array_merge($view_array, array( "plan_id" => $plan_id));
            $view_array = array_merge($view_array, array( "plan" => $plan));
            $view_array = array_merge($view_array, array( "firstname" => $firstname));
            $view_array = array_merge($view_array, array( "lastname" => $lastname));
            $view_array = array_merge($view_array, array( "life_id" => $life_id));
            $view_array = array_merge($view_array, array( "commissions" => $commissions));
            $view_array = array_merge($view_array, array( "plans" => $plans));
            $view_array = array_merge($view_array, array( "warn" => $warn));


            $page_template = array();
            $page_template = array_merge($page_template, array("custom_js" => RenderViewAsString("commissions/js_assets")));
            $page_template = array_merge($page_template, array("view" => $view));
            $page_template = array_merge($page_template, array("view_array" => $view_array));
            RenderView('templates/template_body_default', $page_template);

        }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404(); }
    }

    function _lives_widget($company_id)
    {
        $encryption_key = GetCompanyEncryptionKey($company_id);
        $lives = $this->Life_model->select_all_lives($company_id);
        $lives = A2PDecryptArray($lives, $encryption_key);

        $headings = array();
        if ( ! empty($lives) ) $headings = array_keys($lives[0]);


        $view_array = array();
        $view_array['data'] = $lives;
        $view_array['headings'] = $headings;
        return RenderViewAsString("commissions/lives_widget", $view_array);
    }

}
