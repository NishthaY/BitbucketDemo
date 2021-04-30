<?php

function HasClarificationsYetToReview( $company_id ) {

    // HasComparisonsYetToReview
    //
    // This function will return true if there are one or more comparisons that
    // have been identified as needing reviewed by a person AND those items
    // have not yet been resolved by the end user.
    // ---------------------------------------------------------------------

    // Initialize Singleton
    $CI = &get_instance();
    if ( $CI->Clarifications_model->has_clarifications_yet_to_review($company_id) ) return true;
    return false;

}
function HasClarifications( $company_id ) {

    // HasClarifications
    //
    // This function will return true if there are one or more clarifications that
    // have been identified as needing reviewed by person.
    // ---------------------------------------------------------------------

    // Initialize Singleton
    $CI = &get_instance();

    if ( $CI->Clarifications_model->has_clarifications($company_id) ) return true;
    return false;

}
function MonthsImpacted( $coverage_start_date, $before_coverage_start_date, $retro_rule )
{
    // Take the coverage start date passed in and get the first of the month.
    // This is the first month to consider.
    $dates = array();
    $dates[] = date("m/01/Y", strtotime($coverage_start_date));

    // Drop the retro rule by one, as we just handled the first date.
    $retro_rule = getIntValue($retro_rule) - 1;
    if ( getIntValue($retro_rule) > 0 )
    {
        // If we have retro rules in play still, then we need to calculate the
        // first of each of those months.
        for($i=$retro_rule;$i>0;$i--)
        {
            $dates[] = date("m/01/Y", strtotime($coverage_start_date . " -{$i} months"));
        }
    }

    // Now, take the list of months we calcualted and throw out any that are
    // greater than the current previous ( before ) start month.
    $filtered_dates = array();
    foreach($dates as $date)
    {
        if ( strtotime($before_coverage_start_date) <= strtotime($date) )
        {
            $filtered_dates[] = strtotime($date);
        }
    }
    sort($filtered_dates);

    // What ever we have left, turn into month names.  This will be the list
    // of months adjustments will be impacted.
    $months = "";
    foreach($filtered_dates as $date)
    {
        $months .= date( "F", $date ) . ", ";
    }
    if ( strpos($months, ",") !== FALSE ) $months = fLeftBack($months, ",");

    return $months;


}

/**
 * FeatureDefaultClarificationsForm
 *
 * Render the configuration form that will allow you to customize the clarification
 * logic per parent/company.
 *
 * @param $identifier
 * @param $identifier_type
 * @return string|void
 * @throws Exception
 */
function FeatureDefaultClarificationsForm($identifier, $identifier_type)
{
    $CI = &get_instance();

    if ( $identifier_type === 'company' )
    {
        $company = $CI->Company_model->get_company($identifier);
        $identifier_name = GetArrayStringValue("company_name", $company);
        $identifier_url = 'companies/feature/save/default_clarifications';
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $companyparent = $CI->CompanyParent_model->get_companyparent($identifier);
        $identifier_name = GetArrayStringValue("Name", $companyparent);
        $identifier_url = 'parents/feature/save/default_clarifications';
    }
    else
    {
        // I don't know to render that.
        return "";
    }




    $form = new UIModalForm("default_clarifications_form", "default_clarifications_form", base_url($identifier_url));
    $form->setTitle("Default Clarifications ( {$identifier_name} )");
    $form->setDescription("Disable prompting for all data clarifications requiring manual input and log default clarifications to process report.");

    $clarification_type = GetPreferenceValue($identifier, $identifier_type, 'clarifications', 'type');
    if ( $clarification_type === '' ) $clarification_type = 'retro'; // Default something if not set.
    $form->addElement($form->htmlView("clarifications/feature_options", ['clarification_type' => $clarification_type]));

    $form->addElement($form->hiddenInput('identifier', $identifier));
    $form->addElement($form->hiddenInput('identifier_type', $identifier_type));

    $form->addElement($form->submitButton("save_default_clarifications_form", "Save Settings", "btn-primary pull-right"));
    $form->addElement($form->button("cancel_default_clarifications_form", "Cancel", "btn-default pull-right"));
    $form_html = $form->render();

    return $form_html;
}

/**
 * Return the saved clarification type for the specified identifier if the
 * underlying feature is enabled.
 *
 * @param $identifier
 * @param $identifier_type
 * @return string
 * @throws Exception
 */
function GetClarificationType($identifier, $identifier_type)
{
    $CI = &get_instance();

    // Based on the identifier type, collect our id values or bail.
    if ( $identifier_type === 'company' )
    {
        $company_id = $identifier;
        $companyparent_id = GetCompanyParentId($company_id);
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $companyparent_id = $identifier;
    }
    else
    {
        // Unsupported identifier type, return empty string.
        return "";
    }


    // Find the feature based on the scope defined by that feature and the identifiers provided.
    $feature = array();
    $feature_type = $CI->Feature_model->get_feature_type('DEFAULT_CLARIFICATIONS');
    if ( $feature_type === 'company feature' )
    {
        // This is a COMPANY feature.
        if ( $identifier_type === 'company' )
        {
            // We were provided a COMPANY identifier, collect the feature.
            $feature = $CI->Feature_model->get_company_feature($company_id, 'DEFAULT_CLARIFICATIONS');
        }
    }
    else if ( $feature_type === 'companyparent feature' || $feature_type === 'company feature with parent override' )
    {
        // This is a COMPANYPARENT type feature.
        if ( $companyparent_id !== '' )
        {
            // We have a COMPANYPARENT_ID and the feature type supports parents.  Collect the feature.
            $feature = $CI->Feature_model->get_companyparent_feature($companyparent_id, 'DEFAULT_CLARIFICATIONS');
        }
        else
        {
            // We have no COMPANYPARENT_ID, so this is a company with no parent.
            $feature = $CI->Feature_model->get_company_feature($company_id, 'DEFAULT_CLARIFICATIONS');
        }
    }

    // If we found no feature, return the empty string.
    if ( empty($feature) ) return "";

    // Is the feature enabled?  No, return the empty string.
    $enabled = false;
    if ( GetArrayStringValue("Enabled", $feature) === 't' ) $enabled = true;
    if ( ! $enabled ) return "";


    // Return the preference saved at the scope required for the feature type now that we know the
    // feature is currently enabled.
    if ( $feature_type === 'company feature' && $identifier_type === 'company')
    {
        return GetPreferenceValue($company_id, 'company', 'clarifications', 'type');
    }
    else if ( $feature_type === 'companyparent feature' || $feature_type === 'company feature with parent override' )
    {
        // This is a COMPANYPARENT type feature.
        if ( $companyparent_id !== '' )
        {
            return GetPreferenceValue($companyparent_id, 'companyparent', 'clarifications', 'type');
        }
        else
        {
            return GetPreferenceValue($company_id, 'company', 'clarifications', 'type');
        }
    }





    return "";
}

/**
 * Save the clarification type to the appropriate preference based on the feature
 * type.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $clarification_type
 */
function SaveClarificationType($identifier, $identifier_type, $clarification_type)
{
    $CI = &get_instance();

    // Based on the identifier type, collect our id values or bail.
    if ( $identifier_type === 'company' )
    {
        $company_id = $identifier;
        $companyparent_id = GetCompanyParentId($company_id);
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $companyparent_id = $identifier;
    }
    else
    {
        // Unsupported identifier type, return.
        return;
    }

    // What type of feature are we dealing with?
    $feature_type = $CI->Feature_model->get_feature_type('DEFAULT_CLARIFICATIONS');

    // Save the feature value based on the feature type.  We don't care if the feature is enabled or not, saving is
    // fine.  The GET function will take care of understanding if the feature is enabled or not and return
    // appropriately.
    if ( $feature_type === 'company feature' )
    {
        if ( $clarification_type === '' ) RemovePreference($company_id, 'company', 'clarifications', 'type');
        if ( $clarification_type !== '' ) SavePreference($company_id, 'company', 'clarifications', 'type', $clarification_type);
    }
    else if ( $feature_type === 'companyparent feature' || $feature_type === 'company feature with parent override' )
    {
        if ( $companyparent_id !== '' )
        {
            if ( $clarification_type === '' ) RemovePreference($companyparent_id, 'companyparent', 'clarifications', 'type');
            if ( $clarification_type !== '' ) SavePreference($companyparent_id, 'companyparent', 'clarifications', 'type', $clarification_type);
        }
        else
        {
            if ( $clarification_type === '' ) RemovePreference($company_id, 'company', 'clarifications', 'type');
            if ( $clarification_type !== '' ) SavePreference($company_id, 'company', 'clarifications', 'type', $clarification_type);
        }
    }

}


/* End of file clarifications_helper.php */
/* Location: ./application/helpers/clarifications_helper.php */
