<?php

/**
 * AddTargetableFeatureForm
 *
 * The for to add a new feature is shared between company and companyparents.
 * This function will return the HTML for the form.
 *
 * @param $identifier
 * @param $identifier_type
 * @return string|void
 * @throws UIException
 */
function AddTargetableFeatureForm($identifier, $identifier_type)
{
    $CI = &get_instance();

    if ( $identifier_type === 'company' )
    {
        $company = $CI->Company_model->get_company($identifier);
        $identifier_name = GetArrayStringValue("company_name", $company);
        $save_url = base_url("companies/feature/save/targetable_feature");
    }
    else if ($identifier_type === 'companyparent' )
    {
        $companyparent = $CI->CompanyParent_model->get_companyparent($identifier);
        $identifier_name = GetArrayStringValue("Name", $companyparent);
        $save_url = base_url("parents/feature/save/targetable_feature");

    }
    else throw new UIException("Unsupported identifier type.");


    // Create a list of targetable features to be displayed in the form.
    // Collect the data then organize it for display, passing it into
    // an htmlView on the form.
    $list = array();
    $feature_list = $CI->Feature_model->get_targetable_features($identifier_type);

    foreach($feature_list as $item)
    {
        $row = array();
        $row['code'] = GetArrayStringValue('Code', $item);

        $display = GetArrayStringValue('Code', $item);
        $display = replaceFor($display, "_", " ");
        $display = strtolower($display);
        $display = ucwords($display);
        $row['display'] = $display;

        $description = GetArrayStringValue('Description', $item) . "_targetable_description";
        $description = RenderViewAsString($description);
        $row['description'] = $description;

        $target_type = GetArrayStringValue('TargetType', $item);
        $row['target_type'] = $target_type;

        $row['Sort'] = $display;

        $list[] = $row;
    }
    uasort($list, 'AssociativeArraySortFunction_Sort');

    $target_dropdowns = array();

    // TARGET_TYPE: mapping_column
    // Create a dropdown of all possible mapping column values.  ( all possible targets )
    $dropdown = new Select2("modal");
    $dropdown->setId("mapping_column");
    $columns = $CI->Mapping_model->select_mapping_columns();
    foreach($columns as $column)
    {
        $name = GetArrayStringValue('Name', $column);
        $display = GetArrayStringValue('Display', $column);
        $dropdown->addItem("", $display, $name);
    }
    $target_dropdowns['mapping_column'] = $dropdown->render();

    // OTHER TARGET TYPES
    // In the future, we will have other target types.  Make a dropdown
    // for each and then add them to the collection for display.  Make
    // sure the lookup is the "target_type" value.

    // Most of this form is rendered via a view.  Create that view here
    // and pass in the dropdown values for each possible targetable value.
    $view = "features/targetable_feature_list";
    $view_array = array();
    $view_array['feature_list'] = $list;
    $view_array['dropdowns']    = $target_dropdowns;

    $form = new UIModalForm("targetable_feature_form", "targetable_feature_form", $save_url);
    $form->setTitle("Add Feature ( {$identifier_name} )");
    $form->addElement($form->htmlView($view, $view_array));

    $form->addElement($form->hiddenInput('target_type', ""));   // Will be populated via JS as the user interacts with the form.
    $form->addElement($form->hiddenInput('identifier', $identifier));
    $form->addElement($form->hiddenInput('identifier_type', $identifier_type));
    $form->addElement($form->submitButton("save_targetable_feature_form", "Add Feature", "btn-primary pull-right"));
    $form->addElement($form->button("cancel_targetable_feature_form", "Cancel", "btn-default pull-right"));
    $form_html = $form->render();

    return $form_html;
}
function FeatureDefaultPlanForm($identifier, $identifier_type )
{
    $CI = &get_instance();

    if ( $identifier_type === 'company' )
    {
        $company = $CI->Company_model->get_company($identifier);
        $identifier_name = GetArrayStringValue("company_name", $company);
        $save_url = base_url("companies/feature/save/default_plan");
    }
    else if ($identifier_type === 'companyparent' )
    {
        $companyparent = $CI->CompanyParent_model->get_companyparent($identifier);
        $identifier_name = GetArrayStringValue("Name", $companyparent);
        $save_url = base_url("parents/feature/save/default_plan");

    }
    else throw new UIException("Unsupported identifier type.");

    $plan_code = GetPreferenceValue($identifier, $identifier_type, 'plan', 'default_plan_code');

    $form = new UIModalForm("default_plan_form", "default_plan_form", $save_url);
    $form->setTitle("Default Plan ( {$identifier_name} )");
    $form->setDescription("If no plan is supplied in an import file, assume the following plan was implied.");

    $form->addElement($form->textInput('plan_code', "Plan Code", $plan_code));
    $form->addElement($form->hiddenInput('identifier', $identifier));
    $form->addElement($form->hiddenInput('identifier_type', $identifier_type));

    $form->addElement($form->submitButton("save_default_plan_form", "Save Settings", "btn-primary pull-right"));
    $form->addElement($form->button("cancel_default_plan_form", "Cancel", "btn-default pull-right"));
    $form_html = $form->render();

    return $form_html;
}
function TargetableFeatureBeneficiaryMappingForm($identifier, $identifier_type, $target_type, $target)
{
    $CI = &get_instance();

    if ( $identifier_type === 'company' )
    {
        $company = $CI->Company_model->get_company($identifier);
        $identifier_name = GetArrayStringValue("company_name", $company);
        $save_url = base_url("companies/feature/save/beneficiary_mapping");
    }
    else if ($identifier_type === 'companyparent' )
    {
        $companyparent = $CI->CompanyParent_model->get_companyparent($identifier);
        $identifier_name = GetArrayStringValue("Name", $companyparent);
        $save_url = base_url("parents/feature/save/beneficiary_mapping");

    }
    else throw new UIException("Unsupported identifier type.");

    $tokens = $CI->Mapping_model->select_beneficiary_maps($identifier, $identifier_type, $target);

    $view_array = array();
    $view_array['tokens'] = $tokens;

    $field = $target;
    $field = replaceFor($field, "_", " ");
    $field = strtolower($field);

    $form = new UIModalForm("beneficiary_mapping_form", "beneficiary_mapping_form", $save_url);
    $form->setTitle("Beneficiary Mapping ( {$identifier_name} )");
    $form->setDescription("Search the <strong>{$field}</strong> field looking for the provided tokens.  On import, if the content found in the <strong>{$field}</strong> field matches the selected tokens below, the record will be flagged as containing beneficiary data.");

    $form->addElement($form->htmlView("templates/form/modal/mapping_selector", $view_array));
    $form->addElement($form->hiddenInput('feature_code', 'BENEFICIARY_MAPPING'));
    $form->addElement($form->hiddenInput('target_type', $target_type));
    $form->addElement($form->hiddenInput('target', $target));
    $form->addElement($form->hiddenInput('identifier', $identifier));
    $form->addElement($form->hiddenInput('identifier_type', $identifier_type));
    $form->addElement($form->submitButton("save_beneficiary_handling_form", "Save", "btn-primary pull-right"));
    $form->addElement($form->button("cancel_beneficiary_mapping_form", "Cancel", "btn-default pull-right"));
    $form_html = $form->render();

    return $form_html;
}

/**
 * IsAtLeastOneFeatureEnabledForCompany
 *
 * This function will return TRUE if the company has at least one feature
 * enabled of the specified type.  ( i.e. if this is targetable there could
 * be several feature of this type )
 *
 * This function takes into consideration the parent if the feature is of
 * a type where the parent might override the companies settings.
 *
 *
 * @param $company_id
 * @param $feature_code
 * @return bool
 * @throws Exception
 */
function IsAtLeastOneFeatureEnabledForCompany($company_id, $feature_code)
{
    $CI = &get_instance();

    // Is the feature passed in a parent override feature?  No, bail.
    $feature_type = $CI->Feature_model->get_feature_type($feature_code);
    if ( $feature_type === 'companyparent feature' ) throw new Exception("Unexpected feature type.");
    if ( $feature_type === 'company feature' ) return $CI->Feature_model->is_atleast_one_feature_enabled($company_id, 'company', $feature_code, true);
    if ( $feature_type === 'company feature with parent override')
    {
        $enabled = $CI->Feature_model->is_atleast_one_feature_enabled($company_id, 'company', $feature_code, true);
        if ( $enabled ) return true;

        $companyparent_id = GetCompanyParentId($company_id);
        if ( $companyparent_id !== '' )
        {
            $enabled = $CI->Feature_model->is_atleast_one_feature_enabled($companyparent_id, 'companyparent', $feature_code, true);
            if ( $enabled ) return true;
        }
    }

    return false;
}

/**
 * GetDistinctTargetsByFeatureCodeForCompany
 *
 * This function, returns a list of targets for the company and feature specified.
 * The list will return only enabled targets and will review the list of parent
 * targets of the same name if the feature is a parent override type.
 *
 * In the end, you get an array of unique target values for enabled features
 * on the company based on the feature type.
 *
 * @param $company_id
 * @param $feature_code
 * @param string $enabled
 * @return array
 * @throws Exception
 */
function GetDistinctTargetsByFeatureCodeForCompany($company_id, $feature_code)
{
    $CI = &get_instance();

    // Is the feature passed in a parent override feature?  No, bail.
    $feature_type = $CI->Feature_model->get_feature_type($feature_code);
    if ( $feature_type === 'companyparent feature' ) throw new Exception("Unexpected feature type.");
    if ( $feature_type === 'company feature' ) return $CI->Feature_model->list_distinct_targets_by_feature($company_id, 'company', $feature_code, true);
    if ( $feature_type === 'company feature with parent override')
    {
        $lookup = array();

        // Find all of the possible targets that have been assigned the company.
        $company_targets = $CI->Feature_model->list_distinct_targets_by_feature($company_id, 'company', $feature_code, true);
        foreach($company_targets as $target)
        {
            $lookup[$target] = $target;
        }

        // Find all of the possible targets that have been assigned to the companyparent.
        $companyparent_id = GetCompanyParentId($company_id);
        if ( $companyparent_id !== '' )
        {
            $companyparent_targets = $CI->Feature_model->list_distinct_targets_by_feature($companyparent_id, 'companyparent', $feature_code, true);
            foreach($companyparent_targets as $target)
            {
                $lookup[$target] = $target;
            }
        }

        // Return the unique merged list between enabled company targets and companyparent targets.
        return array_keys($lookup);
    }

    // No results
    return array();

}

/**
 * GetFeatureIdentifierTypeForTargetableFeature
 *
 * Sometimes features have data associated with them.  When trying to access
 * that data, you need to know if you should pull the data from the company
 * or the parent.  This function will review the feature type, and then based
 * on it's type the state of the feature on the company and parent to decide
 * where the feature data is stored.
 *
 * This function returns 'company' or 'companyparent' indicating where the
 * data for the feature is stored.
 *
 * @param $company_id
 * @param $feature_code
 * @param $target_type
 * @param $target
 * @return string
 * @throws Exception
 */
function GetFeatureIdentifierTypeForTargetableFeature($company_id, $feature_code, $target_type, $target)
{
    $CI = &get_instance();

    $feature_type = $CI->Feature_model->get_feature_type($feature_code);
    if ( $feature_type === 'company feature' ) return 'company';
    else if ( $feature_type === 'companyparent feature' ) return 'companyparent';
    else if ( $feature_type !== 'company feature with parent override') throw new Exception("Unexpected feature_type for feature_code.");

    // company feature with parent override
    // At this point, we want the companyparent, but only if it's enabled.

    // Does this company have a parent?  No, then the identifier type must be company.
    $companyparent_id = GetCompanyParentId($company_id);
    if ( $companyparent_id === '' ) return 'company';

    // If there is a company parent feature for the feature_code, is it enabled.
    $companyparent_feature = $CI->Feature_model->get_companyparent_feature( $companyparent_id, $feature_code, $target_type, $target);
    if ( GetArrayStringValue('Enabled', $companyparent_feature) === 't' ) return "companyparent";
    return "company";
}


/* End of file feature_helper.php */
/* Location: ./application/helpers/display_helper.php */