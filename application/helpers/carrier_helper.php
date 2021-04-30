<?php

/**
 * GetDefaultCarrier
 *
 * Find and return the default carrier for the specified identifier type.
 * If no default carrier can be found, then the empty string is returned.
 *
 * Supported identifier types are:
 *  - company
 *  - companyparent
 *
 * @param $identifier
 * @param $identifier_type
 * @return string
 * @throws Exception
 */
function GetDefaultCarrier( $identifier, $identifier_type )
{
    $CI = &get_instance();
    if ( $identifier_type === 'companyparent' )
    {
        $parent_default = "";
        $feature_type = $CI->Feature_model->get_feature_type('DEFAULT_CARRIER');
        if ( $feature_type === 'company feature with parent override' )
        {
            if ( $CI->Feature_model->is_feature_enabled_for_companyparent('DEFAULT_CARRIER', $identifier) )
            {
                $parent_default = GetPreferenceValue($identifier, $identifier_type, 'carrier', 'default_carrier_code');
                if ( $parent_default !== '' ) $parent_default = $CI->Carrier_model->get_carrier_description_by_carrier_code($parent_default);
            }
        }
        return $parent_default;
    }
    else if ( $identifier_type === 'company' )
    {
        $company_default = "";
        $feature_type = $CI->Feature_model->get_feature_type('DEFAULT_CARRIER');
        if ( strpos($feature_type, 'company feature' ) !== FALSE )
        {
            if ( $CI->Feature_model->is_feature_enabled_for_company('DEFAULT_CARRIER', $identifier) )
            {
                $company_default = GetPreferenceValue($identifier, $identifier_type, 'carrier', 'default_carrier_code');
                if ( $company_default !== '' ) $company_default = $CI->Carrier_model->get_carrier_description_by_carrier_code($company_default);
            }
        }
        return $company_default;
    }
    return "";
}


/* End of file carrier_helper.php */
/* Location: ./application/helpers/carrier_helper.php */
