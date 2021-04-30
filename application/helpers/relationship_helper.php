<?php
function ArchiveRelationshipSettings( $company_id, $user_id ) {

    // ArchiveRelationshipSettings
    //
    // This function will collect all of the information set on the Relationship
    // Settings screen and save a snapshot for future reference.
    // ---------------------------------------------------------------------

    $CI = &get_instance();

    // Organize our Snapshot Data
    $relationships = $CI->Relationship_model->select_relationships_for_import($company_id);

    $pref = $CI->Company_model->get_company_preference(  $company_id, "relationships", "dependent_pricing_model" );
    $pricing_model = getArrayStringValue("value", $pref);

    $data = array();
    foreach($relationships as $relationship)
    {
        $row = array();
        $row["UserDescription"] = getArrayStringValue("UserDescription", $relationship);
        $row["Relationship"] = getArrayStringValue("RelationshipDescription", $relationship);
        $row["RelationshipCode"] = getArrayStringValue("RelationshipCode", $relationship);
        $row["CompanyRelationshipId"] = getArrayStringValue("CompanyRelationshipId", $relationship);
        $data[] = $row;
    }

    $list = array();
    $list["Column Mapped"] = HasRelationship($company_id);
    $list["Pricing Model"] = ( getStringValue($list["Column Mapped"]) == "TRUE" ) ? $pricing_model : "";


    ArchiveHistoricalData($company_id, 'company', "relationship_settings", $data, $list, $user_id, 3);
}
function HasRelationship( $company_id ) {
    // HasRelationship
    //
    // This function will return TRUE if the company currently has mapped
    // the relationship column.  When the user has done this, we will need
    // to take extra steps in the workflow.
    // ---------------------------------------------------------------------

    // Initialize Singleton
    $CI = &get_instance();

    $pref = $CI->Company_model->get_company_preferences_by_value($company_id, "column_map", "relationship");

    if ( ! empty($pref) ) {

        // We do have a relationship column mapped!  Before we say "Yes, we have relationships."
        // let's look and see if the file contains any data in the relationship column.
        if ( $CI->Relationship_model->has_relationship_data($company_id) == 'f' ) return false;
        return true;
    }
    return false;

}
function AllRelationshipsMapped( $company_id ) {

    // Initialize Singleton
    $CI = &get_instance();

    $all_mapped = $CI->Relationship_model->all_relationships_mapped($company_id);
    if ( $all_mapped == "t" ) return true;
    return false;
}
function IsRelationshipPricingModelSet( $company_id ) {

    // Initialize Singleton
    $CI = &get_instance();

    $pricing_model = $CI->Company_model->get_company_preference($company_id, "relationships", "dependent_pricing_model");
    ( empty($pricing_model) ) ? $pricing_model = "" : $pricing_model = getArrayStringValue("value", $pricing_model);
    if ( $pricing_model == "" ) return false;
    return true;
}

/* End of file relationship_helper.php */
/* Location: ./application/helpers/relationship_helper.php */
