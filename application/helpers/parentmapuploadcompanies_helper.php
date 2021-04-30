<?php

/**
 * IsCompanyAvailableForParentMap
 *
 * This function will return TRUE if the company specified
 * is not yet started the upload process.
 *
 * @param $company_id
 * @return bool
 */
function IsCompanyAvailableForParentMap($company_id)
{
    $CI = &get_instance();

    // If we have not started a wizard for this company, we are certainly available.
    if ( ! $CI->Wizard_model->has_wizard_started($company_id) ) return true;

    // In the case where the startup step is done, but the upload is not ... this we will consider available too.
    if ( IsStartupStepComplete( $company_id ) && ! IsUploadStepComplete($company_id ) ) return true;

    return false;
}

/**
 * GetUserElectedStartMonthForCompanyParentMap
 *
 * Users in the company parent upload workflow have the ability
 * to specify what import date they are processing in their file.
 * This date  is tied to the companyparent specified and is
 * persistent.
 *
 * @param $companyparent_id
 * @return string
 * @throws Exception
 */
function GetUserElectedStartMonthForCompanyParentMap($companyparent_id)
{
    $CI = &get_instance();

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


/**
 * GetExpandedCompaniesForParentMap
 *
 * This function will return a collection of company objects associated with
 * the specified companyparent.  The collection will be expanded to include data
 * beyond just the companies to facilitate the CompanyParentMap process.
 *
 * @param $companyparent_id
 * @return string
 * @throws Exception
 */
function GetExpandedCompaniesForParentMap($companyparent_id)
{

    $CI = &get_instance();

    $companies = $CI->CompanyParent_model->get_companies_by_parent($companyparent_id);
    uasort($companies, 'AssociativeArraySortFunction_company_name');

    $oldest = null;
    $newest = null;
    $current = date('m') . "/01/" . date('Y');
    $start = GetUserElectedStartMonthForCompanyParentMap($companyparent_id);

    $index = 0;
    foreach($companies as $company)
    {
        $company['import_date'] = "";
        $company['import_date_description'] = "";
        $company_id = GetArrayStringValue('company_id', $company);
        $company['available'] = IsCompanyAvailableForParentMap($company_id);
        if ( $company_id !== '' )
        {
            if ( GetUploadDate($company_id) === '' )
            {
                // If we have no upload date, then this a brand new company.  Assume there start date
                // will be set to the user elected start date.
                $company['import_date'] = $start;
                $company['import_date_description'] = FormatDateMonthYYYY($start);
                $company['import_date_short'] = FormatDateMonYYYY($start);

            }
            else
            {
                $company['import_date'] = GetUploadDate($company_id);
                $company['import_date_description'] = GetImportDateDescription($company_id);
                $company['import_date_short'] = GetUploadDateDescriptionShort($company_id);
            }


            if ( GetArrayStringValue('import_date', $company) !== '' )
            {
                if ( $oldest === null )
                {
                    $oldest = GetArrayStringValue('import_date', $company);
                }
                else
                {
                    if ( strtotime(GetArrayStringValue('import_date', $company)) < strtotime($oldest) )
                    {
                        $oldest = GetArrayStringValue('import_date', $company);
                    }
                }
            }

            if ( GetArrayStringValue('import_date', $company) !== '' )
            {
                if ( $newest === null )
                {
                    $newest = GetArrayStringValue('import_date', $company);
                }
                else
                {
                    if ( strtotime(GetArrayStringValue('import_date', $company)) > strtotime($newest) )
                    {
                        $newest = GetArrayStringValue('import_date', $company);
                    }
                }
            }

        }
        $companies[$index] = $company;
        $index++;
    }

    uasort($companies, 'AssociativeArraySortFunction_company_name');
    return $companies;
}

/* End of file parentmapuploadcompanies_helper.php */
/* Location: ./application/helpers/parentmapuploadcompanies_helper.php */
