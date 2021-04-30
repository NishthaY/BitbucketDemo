<?php
function GetCommissionType( $company_id )
{
    $CI = &get_instance();

    $commission_type = "";
    $feature_code = 'COMMISSION_TRACKING';
    $feature_type = $CI->Feature_model->get_feature_type($feature_code);
    if ( $feature_type == 'company feature with parent override')
    {
        $companyparent_id = GetCompanyParentId($company_id);
        if ( $CI->Feature_model->is_feature_enabled_for_companyparent( $feature_code, $companyparent_id ) )
        {
            // Get the commission type from the parent.
            $pref = $CI->CompanyParent_model->get_companyparent_preference( $companyparent_id, "commission_tracking", "commission_type" );
            $commission_type = GetArrayStringValue("value", $pref);
        }else
        {
            // The parent is not overriding the company.  Use the company settings.
            $pref = $CI->Company_model->get_company_preference( $company_id, "commission_tracking", "commission_type" );
            $commission_type = GetArrayStringValue("value", $pref);
        }
    }
    else if ( $feature_type === 'company feature' )
    {
        // This is a company feature, use the company preference.
        $pref = $CI->Company_model->get_company_preference( $company_id, "commission_tracking", "commission_type" );
        $commission_type = GetArrayStringValue("value", $pref);
    }
    else if ( $feature_type === 'companyparent feature' )
    {
        // This is a company parent feature, use the company parent feature.
        $companyparent_id = GetCompanyParentId($company_id);
        $pref = $CI->CompanyParent_model->get_companyparent_preference( $companyparent_id, "commission_tracking", "commission_type" );
        $commission_type = GetArrayStringValue("value", $pref);
    }

    return $commission_type;
}
function GetCommissionEffectiveDateType($company_id)
{
    $CI = &get_instance();

    $commission_effective_date_type = RECENT_TIER_CHANGE;
    $feature_code = 'COMMISSION_TRACKING';
    $feature_type = $CI->Feature_model->get_feature_type($feature_code);
    if ( $feature_type == 'company feature with parent override')
    {
        $companyparent_id = GetCompanyParentId($company_id);
        if ( $CI->Feature_model->is_feature_enabled_for_companyparent( $feature_code, $companyparent_id ) )
        {
            // Get the commission type from the parent.
            $pref = $CI->CompanyParent_model->get_companyparent_preference( $companyparent_id, "commission_tracking", "commission_effective_date_type" );
            $commission_effective_date_type = GetArrayStringValue("value", $pref);
        }else
        {
            // The parent is not overriding the company.  Use the company settings.
            $pref = $CI->Company_model->get_company_preference( $company_id, "commission_tracking", "commission_effective_date_type" );
            $commission_effective_date_type = GetArrayStringValue("value", $pref);
        }
    }
    else if ( $feature_type === 'company feature' )
    {
        // This is a company feature, use the company preference.
        $pref = $CI->Company_model->get_company_preference( $company_id, "commission_tracking", "commission_effective_date_type" );
        $commission_effective_date_type = GetArrayStringValue("value", $pref);
    }
    else if ( $feature_type === 'companyparent feature' )
    {
        // This is a company parent feature, use the company parent feature.
        $companyparent_id = GetCompanyParentId($company_id);
        $pref = $CI->CompanyParent_model->get_companyparent_preference( $companyparent_id, "commission_tracking", "commission_effective_date_type" );
        $commission_effective_date_type = GetArrayStringValue("value", $pref);
    }

    return $commission_effective_date_type;

}