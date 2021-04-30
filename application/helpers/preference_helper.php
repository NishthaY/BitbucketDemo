<?php

/**
 * SavePreference
 *
 * Save a preference
 *
 * @param $identifier
 * @param $identifier_type
 * @param $group
 * @param $group_code
 * @param $value
 * @throws Exception
 */
function SavePreference($identifier, $identifier_type, $group, $group_code, $value )
{
    $CI = &get_instance();

    if ( $identifier_type === 'company' )
    {
        $CI->Company_model->save_company_preference($identifier, $group, $group_code, $value );
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $CI->CompanyParent_model->save_companyparent_preference( $identifier, $group, $group_code, $value );
    }
    else
    {
        throw new Exception(__FUNCTION__ . ":Unknown identifier type.");
    }
}

/**
 * GetPreferences
 *
 * Get the full preference record as a json object.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $group
 * @return mixed
 * @throws Exception
 */
function GetPreferences($identifier, $identifier_type, $group)
{
    $CI = &get_instance();

    if ( $identifier_type === 'company' )
    {
        return $CI->Company_model->get_company_preferences($identifier, $group );
    }
    else if ( $identifier_type === 'companyparent' )
    {
        return $CI->CompanyParent_model->get_companyparent_preferences( $identifier, $group );
    }
    else
    {
        throw new Exception(__FUNCTION__ . ":Unknown identifier type.");
    }
}

/**
 * GetPreferenceValue
 *
 * Get the value of a preference.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $group
 * @param $group_code
 * @return string
 * @throws Exception
 */
function GetPreferenceValue($identifier, $identifier_type, $group, $group_code )
{
    $preference = GetPreference($identifier, $identifier_type, $group, $group_code);
    return GetArrayStringValue("value", $preference);
}

/**
 * GetPreference
 *
 * Get the full preference record as a json object.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $group
 * @param $group_code
 * @return mixed
 * @throws Exception
 */
function GetPreference($identifier, $identifier_type, $group, $group_code )
{
    $CI = &get_instance();

    if ( $identifier_type === 'company' )
    {
        return $CI->Company_model->get_company_preference($identifier, $group, $group_code );
    }
    else if ( $identifier_type === 'companyparent' )
    {
        return $CI->CompanyParent_model->get_companyparent_preference( $identifier, $group, $group_code );
    }
    else
    {
        throw new Exception(__FUNCTION__ . ":Unknown identifier type.");
    }
}

/**
 * RemovePreference
 *
 * Delete a specific preference.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $group
 * @param $group_code
 * @throws Exception
 */
function RemovePreference($identifier, $identifier_type, $group, $group_code)
{
    $CI = &get_instance();

    if ( $identifier_type === 'company' )
    {
        $CI->Company_model->remove_company_preference($identifier, $group, $group_code );
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $CI->CompanyParent_model->remove_companyparent_preference( $identifier, $group, $group_code );
    }
    else
    {
        throw new Exception(__FUNCTION__ . ":Unknown identifier type.");
    }
}

/**
 * RemovePreferenceByValue
 *
 * Delete a preference in a group that has a specific value.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $group
 * @param $value
 * @throws Exception
 */
function RemovePreferenceByValue($identifier, $identifier_type, $group, $value)
{
    $CI = &get_instance();

    if ( $identifier_type === 'company' )
    {
        $CI->Company_model->remove_company_preference_group_code($identifier, $group, $value );
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $CI->CompanyParent_model->remove_companyparent_preference_group_code( $identifier, $group, $value );
    }
    else
    {
        throw new Exception(__FUNCTION__ . ":Unknown identifier type.");
    }
}

/**
 * RemovePreferences
 *
 * Delete all preferences in a specific group.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $group
 * @return mixed
 * @throws Exception
 */
function RemovePreferences($identifier, $identifier_type, $group)
{
    $CI = &get_instance();

    if ( $identifier_type === 'company' )
    {
        return $CI->Company_model->remove_company_preference_group($identifier, $group );
    }
    else if ( $identifier_type === 'companyparent' )
    {
        return $CI->CompanyParent_model->remove_companyparent_preference_group( $identifier, $group );
    }
    else
    {
        throw new Exception(__FUNCTION__ . ":Unknown identifier type.");
    }
}
