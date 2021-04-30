<?php

function HasTobaccoUser( $company_id ) {
    // HasTobacco
    //
    // This function will return TRUE if the company currently has mapped
    // the TobaccoUser column.
    // ---------------------------------------------------------------------

    // Initialize Singleton
    $CI = &get_instance();

    $pref = $CI->Company_model->get_company_preferences_by_value($company_id, "column_map", "tobacco_user");

    if ( ! empty($pref) ) {
        return true;
    }
    return false;

}
function ArchivePlanSettings( $company_id, $user_id ) {

    // ArchivePlanSettings
    //
    // This function will collect all of the information set on the Plan Settings
    // screen and save a snapshot for future reference.
    // ---------------------------------------------------------------------

    $CI = &get_instance();

    $settings = $CI->Archive_model->select_plan_settings_for_archive($company_id);
    foreach($settings as &$item){

        // Organize our data.
        $carrierid = getArrayStringValue("CarrierId", $item);
        $plantypeid = getArrayStringValue("PlanTypeId", $item);
        $planid = getArrayStringValue("PlanId", $item);
        $coveragetierid = getArrayStringValue("CoverageTierId", $item);
        $plantypecode = getArrayStringValue("PlanTypeCode", $item);
        $carrier = getArrayStringValue("Carrier", $item);
        $plantype = getArrayStringvalue("PlanType", $item);
        $uniqueid = "{$carrierid}-{$plantypeid}-{$planid}-{$coveragetierid}";

        // What are the items in the PlanType dialog.
        $plantype_data = $CI->Company_model->get_company_plantype_data( $company_id, $carrier, $plantype );
        $item["RetroRule"] = getArrayStringValue("RetroRule", $plantype_data);
        $item["WashRule"] = getArrayStringValue("WashRule", $plantype_data);
        $item["PlanAnniversaryMonth"] = getArrayStringValue("PlanAnniversaryMonth", $plantype_data);

        // If the plan type is ignored, the settings saved are not in play.
        // Empty them out for the snapshot.
        if ( getArrayStringValue("PlanTypeIgnored", "t") )
        {
            $item["PlanTypeCode"] = "";
            $item["RetroRule"] = "";
            $item["WashRule"] = "";
            $item["PlanAnniversaryMonth"] = "";
        }

        // What is the ageband data associated with this Plan Setting
        $item["Is Ageband Capable"] = $CI->Ageband_model->is_plantype_bandable($plantypecode);
        $ageband_details = $CI->Ageband_model->coverage_tier_ageband_details($company_id, $coveragetierid );
        if ( count($ageband_details) <> 0 ) $item["Ageband Is Ignored"] = getArrayStringValue("Ignored", $ageband_details);
        if ( count($ageband_details) == 0 ) $item["Ageband Is Ignored"] = "f";
        $bands = "";
        $band_data = $CI->Archive_model->select_age_bands_for_archive($coveragetierid);
        foreach( $band_data as $band )
        {
            $bands .= getArrayStringValue("Band", $band) . ", ";
        }
        if ( $item["Is Ageband Capable"] == "f" ) $bands = "";
        if ( $item["Ageband Is Ignored"] == "t" ) $bands = "";
        $item['Age Bands'] = fLeftBack($bands, ",");

        // What is the age calculation rule for this Plan Setting?
        $age_calcuation_data = $CI->Archive_model->select_age_calculation_for_archive($coveragetierid);
        $item['Age Calculation'] = getArrayStringValue("Age Type Description", $age_calcuation_data);
        $item['Age Type Id'] = getArrayStringValue("Age Type Id", $age_calcuation_data);
        $item['Anniversary Month'] = getArrayStringValue("Anniversary Month", $age_calcuation_data);
        $item['Anniversary Day'] = getArrayStringValue("Anniversary Day", $age_calcuation_data);

        // What are the ageband fees for this Plan Setting?
        $plan_data = $CI->Company_model->get_company_plan_by_id($company_id, $planid);
        if ( getArrayStringValue("ASOFee", $plan_data) != "" )
        {
            $item['ASO Fee'] = getArrayStringValue("ASOFee", $plan_data);
            $item['ASO Fee CarrierId'] = getArrayStringValue("ASOFeeCarrierId", $plan_data);
            $item['ASO Fee PlanTypeId'] = getArrayStringValue("ASOFeePlanTypeId", $plan_data);
        }
        if ( getArrayStringValue("StopLossFee", $plan_data) != "" )
        {
            $item['Stop Loss Fee'] = getArrayStringValue("StopLossFee", $plan_data);
            $item['Stop Loss Fee CarrierId'] = getArrayStringValue("StopLossFeeCarrierId", $plan_data);
            $item['Stop Loss Fee PlanTypeId'] = getArrayStringValue("StopLossFeePlanTypeId", $plan_data);
        }



        // What is the tobacco data associated with this Plan Setting.
        $item["Is Tobacco Capable"] = $CI->Tobacco_model->is_plantype_tobacco($plantypecode);
        $tobacco_details = $CI->Tobacco_model->coverage_tier_tobacco_details($company_id, $coveragetierid );
        ( count($tobacco_details) <> 0 ? $item["Tobacco Is Ignored"] = getArrayStringValue("Ignored", $tobacco_details) : $item["Tobacco Is Ignored"] = "f" );
        ( HasTobaccoUser($company_id) ? $item["Tobacco Column Mapped"] = "t" : $item["Tobacco Column Mapped"] = "f" );

        // What is the carrier mapping for this Plan Setting?
        $carrier = $CI->Company_model->get_company_carrier( $company_id, $carrierid );
        $item["CarrierCode"] = GetArrayStringValue("CarrierCode", $carrier);


    }
    ArchiveHistoricalData($company_id, 'company', "plan_settings", $settings, array(), $user_id, 4);
}
function GetPlansDataReview($company_id) {

    // GetPlansDataReview
    //
    // The "Plans" wizard step allows a customer to create and validate a bunch
    // of data based on the Carrier, Plan Type, Plan and Carrier Code.  Some of
    // these data points include a plan type mapping to an A2P code and Age bands.
    //
    // This function will examine all of the user data that can be set on the
    // "Plans" page.  This function will return a data structure that contains
    // the user's data as well "valid" and "warning" indicators telling the
    // recipient if the user may move on to the next step or not.
    // ---------------------------------------------------------------------

    // Initialize Singleton
    $CI = &get_instance();
    $CI->load->model("Wizard_model", "wizard_model", true);


    // Collect all of the data for this customer.
    $data = $CI->Wizard_model->select_upload_review( $company_id );
    $all_plantypes_mapped = $CI->Wizard_model->does_company_have_all_plantypes_mapped($company_id);
    $all_plantypes_ignored = $CI->Wizard_model->does_company_have_all_plantypes_ignored($company_id);

    // CARRIERS
    $all_carriers_mapped = true;
    foreach($data as &$item)
    {
        $carrier = GetArrayStringValue("Carrier", $item);
        $mapped = false;
        $carrier_details = $CI->Company_model->get_company_carrier_by_name($company_id, $carrier);
        $carrier_code = GetArrayStringValue("CarrierCode", $carrier_details);
        if ( $carrier_code !== '' ) $mapped = true;
        if ( $mapped === false ) $all_carriers_mapped = false;
        $item['IsCarrierMapped'] = $mapped;
    }


    // AGE BANDS
    // Add ageband data if applicable.
    $all_agebands_mapped = true;
    foreach($data as &$item)
    {
        // Is this plantype ignored?
        $plantype_ignored = getArrayStringValue("Ignored", $item);

        // Generate age band data and attach it to this data set.
        $item['band'] = GetAgeBandDataForItem($company_id, $item);

        // While we have the data right here, note if this ageband, if requried,
        // has been filled in by the user.
        $bandable = getArrayStringValue("bandable", $item['band']);
        $ignored = getArrayStringValue("ignored", $item['band']);
        $count = getArrayStringValue("count", $item['band']);
        //pprint_r("plantype_ignored[{$plantype_ignored}], bandable[{$bandable}], ignored[{$ignored}], count[{$count}]");
        if ( $plantype_ignored == "f" && $bandable == "t" && $ignored == "f" && $count <= 0 ) {
            $all_agebands_mapped = false;
        }
    }

    // TOBACCO
    // Add tobacco data if applicable.
    foreach( $data as &$item)
    {
        // Generate tobacco data and attach it to this data set.
        $item['tobacco'] = GetTobaccoDataForItem($company_id, $item);
    }


    // VALIDATION
    // These items must be in place before this step in the wizard
    // can be validated and the user may continue.
    $valid = true;
    if ( ! $all_carriers_mapped ) $valid = false;
    if ( $all_plantypes_ignored ) $valid = false;
    if ( ! $all_plantypes_mapped ) $valid = false;
    if ( ! $all_agebands_mapped ) $valid = false;


    // WARNING
    // Note if we should warn the user they ignored all plan types.
    $warning_flg = false;
    if ( $all_plantypes_mapped && $all_plantypes_ignored ) $warning_flg = true;

    $payload = array();
    $payload['data'] = $data;
    $payload['valid'] = $valid;
    $payload['warning'] = $warning_flg;

    return $payload;

}

function GetTobaccoDataForItem($company_id, $item) {

    // Initialize Singleton
    $CI = &get_instance();
    $CI->load->model("Tobacco_model", "tobacco_model", true);

    // Default Tobacco data.
    $mapped = "f";                  // Is the carrier, plantype mapped to an A2P plantypecode?
    $has_attribute = "f";           // Does A2P consider this carrier, plantypecode combination to be something that might have tobacco attributes?
    $ignored = "f";                 // Has this attribute on the carrier, plantype, plan, tier been ignored by the company?
    $column_mapped = "f";           // Did the user specify a TobaccoUser column in the data file?

    // Is it mapped?  ( Is this plan type a mapped plan type )
    $plantypecode = getArrayStringValue("PlanTypeCode", $item );
    if ( getArrayStringValue("PlanTypeCode", $item ) != "" ) {
        $mapped = "t";
    }

    // Has Attribute?
    if ( $mapped == "t" )
    {
        $has_attribute = $CI->tobacco_model->is_plantype_tobacco($plantypecode);
    }

    // Is the attribute ignored?
    $coverage_tier_id = null;
    if ( $mapped == "t" && $has_attribute == "t")
    {
        // Did the user choose to ignore this attribute for the
        // specific carrier/plantype combination?
        $coverage_tier_id = getArrayStringValue("CoverageTierId", $item);
        $details = $CI->tobacco_model->coverage_tier_tobacco_details($company_id, $coverage_tier_id );
        if ( count($details) <> 0 )
        {
            $ignored = getArrayStringValue("Ignored", $details);
        }

    }

    // Is the TobaccoUser Column mapped
    if ( HasTobaccoUser($company_id) ) $column_mapped = "t";

    // Build the attribute data structure for the given item.
    $attr = array();
    $attr["mapped"] = $mapped;
    $attr["has_attribute"] = $has_attribute;
    $attr["ignored"] = $ignored;
    $attr["column_mapped"] = $column_mapped;

    // return it.
    return $attr;

}

function GetAgeBandDataForItem($company_id, $item) {

    // GetAgeBandDataForItem
    //
    // The item passed in is a "grouping" from a companies upload file
    // for a Carrier, Plan Type, Plan, Coverage Tier.  This function will
    // attach "Ageband" data to the item in question.  The Ageband data will
    // indicate the following information about the item's age bands ( if any )
    //
    // mapped (t,f)             Is the carrier, plantype mapped to an A2P plantypecode?
    // bandable (t,f)           Does A2P consider this carrier, plantypecode combination to be something that might have agebands?
    // count ( # )              How many agebands are attached to this carrier, plantype, plan, tier?
    // ignored (t,f)            Has this ageband on the carrier, plantype, plan, tier been ignored by the company?
    // company_ageband_id (#)   ID to access identify the bands in the database.
    // ---------------------------------------------------------------------


    // Initialize Singleton
    $CI = &get_instance();
    $CI->load->model("Ageband_model", "ageband_model", true);


    // Default Age Band data.
    $mapped = "f";                  // Is the carrier, plantype mapped to an A2P plantypecode?
    $bandable = "f";                // Does A2P consider this carrier, plantypecode combination to be something that might have agebands?
    $count = 0;                     // How many agebands are attached to this carrier, plantype, plan, tier?
    $ignored = "f";                 // Has this ageband on the carrier, plantype, plan, tier been ignored by the company?
    $coverage_tier_id = "";         // ID to access identify the bands in the database.

    // Is it mapped?  ( Is this plan type a mapped plan type )
    $plantypecode = getArrayStringValue("PlanTypeCode", $item );
    if ( getArrayStringValue("PlanTypeCode", $item ) != "" ) {
        $mapped = "t";
    }

    // Is it bandable?
    if ( $mapped == "t" )
    {
        $bandable = $CI->ageband_model->is_plantype_bandable($plantypecode);
    }

    // Is the band ignored?
    $coverage_tier_id = null;
    if ( $mapped == "t" && $bandable == "t")
    {
        $coverage_tier_id = getArrayStringValue("CoverageTierId", $item);
        $details = $CI->ageband_model->coverage_tier_ageband_details($company_id, $coverage_tier_id );
        if ( count($details) <> 0 )
        {
            $ignored = getArrayStringValue("Ignored", $details);
        }
    }


    // How many age bands are there?
    if ( $mapped == "t" && $bandable == "t" && $ignored == "f")
    {
        if ( getStringValue($coverage_tier_id)  != "" )
        {
            $count = $CI->ageband_model->count_agebands($coverage_tier_id);
        }
    }

    // Build the ageband data structure for the given item.
    $band = array();
    $band["mapped"] = $mapped;
    $band["bandable"] = $bandable;
    $band["count"] = $count;
    $band["ignored"] = $ignored;
    $band["company_ageband_id"] = getStringValue($coverage_tier_id);

    // return it.
    return $band;
}

/* End of file plans_helper.php */
/* Location: ./application/helpers/plans_helper.php */
