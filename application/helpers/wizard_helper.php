<?php
function SetWizardStatusUpdate( $company_id, $message )
{
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) return "";
    return $CI->Wizard_model->update_status($company_id, $message);
}
function GetRecentWizardStatus($company_id)
{
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) return "";
    return $CI->Wizard_model->select_status($company_id);
}
function GetRecentWizardActivity( $company_id )
{
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) return "";
    return $CI->Wizard_model->select_activity($company_id);
}
function IsStartupStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_startup_step_complete($company_id);
    return $complete;
}
function IsUploadStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_upload_step_complete($company_id);
    return $complete;
}
function IsMatchStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_match_step_complete($company_id);
    return $complete;
}
function IsParsingStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_parsing_step_complete($company_id);
    return $complete;
}
function IsCorrectStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_correction_step_complete($company_id);
    return $complete;
}
function IsValidationStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_validation_step_complete($company_id);
    return $complete;
}
function IsSavingStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_saving_step_complete($company_id);
    return $complete;
}
function IsRelationshipStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_relationship_step_complete($company_id);
    return $complete;
}
function IsLivesStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_lives_step_complete($company_id);
    return $complete;
}
function IsPlanReviewStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_plan_review_step_complete($company_id);
    return $complete;
}
function IsClarificationsStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_clarifications_step_complete($company_id);
    return $complete;
}
function IsReportGenerationStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_report_generation_complete($company_id);
    return $complete;
}
function IsAdjustmentStepComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_adjustment_step_complete($company_id);
    return $complete;
}
function IsFinalizingReports($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_finalizing($company_id);
    return $complete;
}
function IsWizardComplete($company_id=null) {
    $CI = &get_instance();
    if ( getStringValue($company_id) == "" ) $company_id = GetSessionValue("company_id");
    $complete = $CI->Wizard_model->is_wizard_complete($company_id);
    return $complete;
}
function NotifyStepStart($company_id=null, $companyparent_id=null)
{
    LogIt("NotifyStepStart", "company_id[".$company_id."], companyparent_id[".$companyparent_id."]" );
    if ( GetStringValue($company_id) === '' ) $company_id = GetSessionValue("company_id");

    NotifyCompanyChannel($company_id, 'workflow_step_starting');
    if ( $company_id === '' ) NotifyCompanyParentChannel($companyparent_id, 'workflow_step_starting');
}
function NotifyStepComplete($company_id=null, $companyparent_id=null)
{
    if ( GetStringValue($company_id) === '' ) $company_id = GetSessionValue("company_id");

    NotifyCompanyChannel($company_id, 'dashboard_task');
    if ( $company_id === '' ) NotifyCompanyParentChannel($companyparent_id, 'dashboard_task');
    NotifyCompanyChannel(A2P_COMPANY_ID, 'admin_dashboard_task');
}
function NotifyWorkflowWidgetRefresh($identifier, $identifier_type, $workflow_name)
{
    $workflow = WorkflowFind($workflow_name);
    $widget_name = GetArrayStringValue("WidgetName", $workflow);
    $widget_refresh_callback = GetArrayStringValue("WidgetRefreshCallback", $workflow);

    if ( $widget_name === '' ) return;
    if ( $widget_refresh_callback === '' ) return;

    $payload = array();
    $payload['workflow_name'] = $workflow;
    $payload['identifier'] = $identifier;
    $payload['identifier_type'] = $identifier_type;
    $payload['widget_name'] = $widget_name;
    $payload['widget_refresh_callback'] = $widget_refresh_callback;


    if ( $identifier_type === 'company') NotifyCompanyChannel($identifier, 'workflow_widget_refresh', $payload);
    if ( $identifier_type === 'companyparent') NotifyCompanyParentChannel($identifier, 'workflow_widget_refresh', $payload);
}
function NotifyWizardComplete($company_id=null, $companyparent_id = null)
{
    LogIt("NotifyWizardComplete", "company_id[{$company_id}], companyparent_id[{$companyparent_id}]");
    //if ( GetStringValue($company_id) === '' ) $company_id = GetSessionValue("company_id");

    NotifyStepComplete($company_id, $companyparent_id);
    NotifyCompanyChannel($company_id, 'workflow_complete');
    if ( GetStringValue($company_id) === '' ) NotifyCompanyParentChannel($companyparent_id, 'workflow_complete');
}
function GetUploadDateDescription($company_id=null) {
    $CI = &get_instance();
    $CI->load->model("Wizard_model", "wizard_model", true);
    $CI->load->model("Company_model", "company_model", true);
    if ( $company_id == null ) $company_id = GetSessionValue("company_id");
    $results = $CI->Wizard_model->get_upload_date_info($company_id);

    // If we don't have any results, pull our starting date from company
    // preferences.
    if ( empty($results) )
    {
        $month = $CI->company_model->get_company_preference( $company_id, "starting_date", "month" );
        $month = getArrayStringValue("value", $month);
        if ( $month == "" ) return "";

        $year = $CI->company_model->get_company_preference( $company_id, "starting_date", "year" );
        $year = getArrayStringValue("value", $year);
        if ( $year == "" ) return "";

        $starting_date = "{$month}/01/{$year}";
        $results = $CI->Wizard_model->get_starting_upload_date_info($starting_date);
    }

    return getArrayStringValue("UploadDisplayMonth", $results);

}
function GetUploadDate( $company_id=null ) {
    $CI = &get_instance();
    $CI->load->model("Wizard_model", "wizard_model", true);
    $CI->load->model("Company_model", "company_model", true);
    if ( $company_id == null ) $company_id = GetSessionValue("company_id");
    $results = $CI->Wizard_model->get_upload_date_info($company_id);

    // If we don't have any results, pull our starting date from company
    // preferences.
    if ( empty($results) )
    {
        $month = $CI->company_model->get_company_preference( $company_id, "starting_date", "month" );
        $month = getArrayStringValue("value", $month);
        if ( $month == "" ) return "";

        $year = $CI->company_model->get_company_preference( $company_id, "starting_date", "year" );
        $year = getArrayStringValue("value", $year);
        if ( $year == "" ) return "";

        $starting_date = "{$month}/01/{$year}";
        $results = $CI->Wizard_model->get_starting_upload_date_info($starting_date);
    }

    return getArrayStringValue("UploadMonth", $results);
}
function GetUploadDateDescriptionShort($company_id=null) {
    $CI = &get_instance();
    $CI->load->model("Wizard_model", "wizard_model", true);
    $CI->load->model("Company_model", "company_model", true);
    if ( $company_id == null ) $company_id = GetSessionValue("company_id");
    $results = $CI->Wizard_model->get_upload_date_info($company_id);

    // If we don't have any results, pull our starting date from company
    // preferences.
    if ( empty($results) )
    {
        $month = $CI->company_model->get_company_preference( $company_id, "starting_date", "month" );
        $month = getArrayStringValue("value", $month);
        if ( $month == "" ) return "";

        $year = $CI->company_model->get_company_preference( $company_id, "starting_date", "year" );
        $year = getArrayStringValue("value", $year);
        if ( $year == "" ) return "";

        $starting_date = "{$month}/01/{$year}";
        $results = $CI->Wizard_model->get_starting_upload_date_info($starting_date);
    }

    return getArrayStringValue("UploadDisplayMonthShort", $results);

}
function GetUserColumnLabel( $identifier, $identifier_type, $column_no ) {
    // GetUserColumnLabel
    //
    // Returns the user defined column header name.
    // --------------------------------------------------------------
    $CI = &get_instance();
    $CI->load->model("Company_model", "company_model", true);

    $pref = GetPreferenceValue($identifier, $identifier_type, 'headers', 'user_names');
    $pref = json_decode($pref, true);
    if ( ! empty($pref['col_lookup']) )
    {
        return getArrayStringValue("col{$column_no}", $pref["col_lookup"]);
    }
    return "";
}
function GetDefaultColumnLabel( $identifier, $identifier_type, $column_no ) {

    // GetDefaultColumnLabel
    //
    // Returns the default column header name.
    // --------------------------------------------------------------
    $CI = &get_instance();
    $CI->load->model("Company_model", "company_model", true);

    $pref = GetPreferenceValue($identifier, $identifier_type, 'headers', 'default_names');
    $pref = json_decode($pref, true);
    if ( ! empty($pref['col_lookup']) )
    {
        return getArrayStringValue("col{$column_no}", $pref["col_lookup"]);
    }
    return "";
}
function RollbackWizardAttempt($company_id, $keep_this_upload="", $rollback_to="") {

    // REMEMBER!
    // There are multiple ways data can be rolled back.  If you are adding
    // additional delete logic, add it in these places.
    // 1. Companies controller rolls back the most recent attempt, finalized or in progress.
    // 2. Wizard Helper rolls back the most recent wizard attempt which is in progress.

    // DELETE COMPANY
    // Added a new table that you need to rollback?  Don't forget to update
    // the "hard_delete" functions in the User_model and Company_model too.

    $CI = &get_instance();
    $CI->load->model("Wizard_model", "wizard_model", true);
    $CI->load->model("Reporting_model", "reporting_model", true);
    $CI->load->model("Company_model", "company_model", true);
    $CI->load->model('Life_model','life_model',true);
    $CI->load->model('Retro_model','retro_model',true);

    // Audit this transaction.
    if ( $keep_this_upload === '' )
    {
        // If we are rolling back but "keeping an upload' then this is just the
        // application working a customer's request to upload a new file cleanly.
        // We don't need/want to audit that particular step.

        $import_date = GetUploadDate($company_id);
        $payload = array();
        $payload = array_merge($payload, array('ImportDate' => $import_date));
        AuditIt( "Company rollback.", $payload);

        if ( $company_id !== GetSessionValue('company_id') )
        {
            // This was a big event.  Add this audit record to the company in question too.
            AuditIt( "Company rollback.", $payload, GetSessionValue('user_id'), $company_id);
        }

    }
    NotifyCompanyChannel($company_id, "workflow_step_changing", array('company_id' => $company_id));


    try
    {
        $client = S3GetClient();
        S3DeleteBucketContent( S3_BUCKET, replaceFor(GetConfigValue("upload_prefix"), "COMPANYID", $company_id), getStringValue($keep_this_upload) );
        S3DeleteBucketContent( S3_BUCKET, replaceFor(GetConfigValue("parsed_prefix"), "COMPANYID", $company_id) );
        S3DeleteBucketContent( S3_BUCKET, replaceFor(GetConfigValue("errors_prefix"), "COMPANYID", $company_id) );
        S3DeleteBucketContent( S3_BUCKET, replaceFor(GetConfigValue("import_prefix"), "COMPANYID", $company_id) );
        $archive_prefix = GetConfigValue("archive_prefix");
        $archive_prefix = replaceFor($archive_prefix, "COMPANYID", $company_id);
        $archive_prefix = replaceFor($archive_prefix, "DATE", GetUploadDateFolderName($company_id));
        S3DeleteBucketContent( S3_BUCKET, $archive_prefix );
        $CI->reporting_model->delete_downloadable_reports($company_id);

    }
    catch(Exception $e){}


    if ( getStringValue($company_id) != "" ) {

        $CI->Validation_model->delete_validation_errors($company_id, 'company');
        $CI->Age_model->delete_age($company_id);
        $CI->Wizard_model->remove_washed_records($company_id);
        $CI->reporting_model->delete_report_review_warnings($company_id);
        $CI->reporting_model->delete_summary_data($company_id);
        $CI->reporting_model->delete_company_report($company_id);
        $CI->Life_model->delete_companylifecompare($company_id);
        $CI->Life_model->delete_companyliferesearch($company_id);
        $CI->Life_model->delete_companylife_new_lives($company_id);
        $CI->Life_model->delete_lifedata($company_id);
        $CI->Life_model->delete_companylife_disabled($company_id);
        $CI->Life_model->delete_importlife($company_id);
        $CI->Life_model->delete_import_life_warning($company_id);
        $CI->Relationship_model->delete_relationship_data($company_id);
        $CI->LifeEvent_model->delete_all_retrodatalifeevent($company_id);
        $CI->LifeEvent_model->delete_all_retrodatalifeeventwarning($company_id);
        $CI->LifeEvent_model->delete_lifeeventcompare($company_id);
        $CI->retro_model->delete_retro_data($company_id);
        $CI->retro_model->delete_automatic_adjustments( $company_id );
        $CI->Adjustment_model->delete_manual_adjustment( $company_id );
        $CI->Spend_model->delete_spend_data( $company_id );
        $CI->PlanFees_model->delete_plan_fee_importdata( $company_id );
        $CI->Support_model->delete_support_timer( $company_id );

        $action = new GenerateOriginalEffectiveDateData();
        $action->rollback($company_id);

        $action = new GenerateReportTransamericaEligibility();
        $action->rollback($company_id);

        $action = new GenerateReportTransamericaCommissions();
        $action->rollback($company_id);

        $action = new GenerateWarningReport();
        $action->rollback($company_id);

        $action = new GenerateUniversalEmployeeId();
        $action->rollback($company_id);

        $action = new GenerateCommissionReport();
        $action->rollback($company_id);

        $action = new GenerateCommissions();
        $action->rollback($company_id);

        if ( $keep_this_upload === '' )
        {
            // Only rollback the skip mont processing data if the keep this upload
            // file is not set.  If we are keeping the upload, then this is the
            // start of the upload processing.  Since skip month is set BEFORE the
            // upload, we don't want to remove it in this case.
            $action = new SkipMonthProcessing();
            $action->rollback($company_id);
        }


        // Remove CompanyBeneficiaryImport data
        $CI->Beneficiary_model->beneficiary_importdata_remove($company_id);

        // Remove ImportData not Finalized
        $CI->Wizard_model->remove_import_records($company_id);


        // By default, we will delete everything.  If we have a rollback to
        // tag, we might want to keep a few things rather than smoke everything.
        $rollback_to = strtoupper($rollback_to);
        switch ( $rollback_to )
        {
            case "START":
                // Empty the wizard record back to the "start" column.
                $CI->Wizard_model->rollback_to_start($company_id);
                break;
            default:

                // Does this user have any ImportData left?
                $dates = $CI->company_model->most_recent_company_import_date($company_id);
                if ( empty($dates) )
                {
                    // Remove the "starting_date" company preferences, as we just
                    // rolled back their first attempt.  ( if they have any )
                    $CI->company_model->remove_company_preference($company_id, "starting_date", "month");
                    $CI->company_model->remove_company_preference($company_id, "starting_date", "year");
                    $CI->company_model->remove_company_preference($company_id, "mapping", "a2p_suggestions");
                }

                // Remove the wizard record.
                $CI->Wizard_model->delete_wizard($company_id);

                break;
        }
        NotifyCompanyChannel($company_id, "workflow_step_changed", array('company_id' => $company_id));

        // Very last step, vacuum the database.  We just made a bunch of
        // changes.
        //$CI->Tuning_model->vacuum();


    }
}
function GetUploadKey( $identifier, $identifier_type )
{
    if ( $identifier_type === 'company' ) $prefix = replaceFor(GetConfigValue("upload_prefix"), 'COMPANYID', $identifier);
    if ( $identifier_type === 'companyparent' ) $prefix = replaceFor(GetConfigValue("parent_upload_prefix"), 'COMPANYPARENTID', $identifier);

    $files = S3ListFiles( S3_BUCKET, $prefix );
    if ( count($files) == 1 ) {
        $file = $files[0];
        $key = getArrayStringValue("Key", $file);
        if ( strpos($key, "/") !== FALSE ) $key = fRightBack($key, "/");
        return $key;
    }
    return "";
}
function DoesUploadContainHeaderRow( $company_id=null, $companyparent_id=null ) {

    $CI = &get_instance();
    $CI->load->model("Company_model", "company_model", true);

    $has_headers = false;
    if ( GetStringValue($company_id) !== '' )
    {
        $has_headers = true;
        $pref = $CI->company_model->get_company_preference($company_id, "upload_contains_header_row", "boolean");
        $pref = getArrayStringValue("value", $pref);
        if ( $pref == "f" ) $has_headers = false;
    }
    else if ( GetStringValue($companyparent_id) !== '' )
    {
        $has_headers = true;
        $pref = $CI->Company_model->get_company_preference($companyparent_id, "upload_contains_header_row", "boolean");
        $pref = getArrayStringValue("value", $pref);
        if ( $pref == "f" ) $has_headers = false;
    }

    return $has_headers;
}
function GetUploadFormData( $company_id=null, $companyparent_id=null) {

    $CI = &get_instance();
    $CI->load->helper('s3');

    // Default to company_id, unless a companyparent_id was passed in.
    if ( GetStringValue($companyparent_id) === '' )
    {
        // No company_id, collect it.
        if ( GetStringValue($company_id) === '' ) $company_id = GetSessionValue('company_id');

        // Collect the information we will need to work with files on
        // S3.  Create our landing area if needed.
        $upload_prefix = replaceFor(GetConfigValue("upload_prefix"), "COMPANYID", $company_id);
        S3MakeBucketPrefix( S3_BUCKET, $upload_prefix);

        // Create a unique upload filename that is clean of any special characters.
        $filename = "{$company_id}_" . time() . "_" . RandomString(10) . ".upload";

        // Keep track of the entity that is uploading the data.
        $entity = "company";
        $entity_id = $company_id;

    }
    else
    {
        // Collect the information we will need to work with files on
        // S3.  Create our landing area if needed.
        $upload_prefix = replaceFor(GetConfigValue("parent_upload_prefix"), "COMPANYPARENTID", $companyparent_id);
        S3MakeBucketPrefix( S3_BUCKET, $upload_prefix);

        // Create a unique upload filename that is clean of any special characters.
        $filename = "{$companyparent_id}_" . time() . "_" . RandomString(10) . ".upload";

        // Keep track of the entity that is uploading the data.
        $entity = "companyparent";
        $entity_id = $companyparent_id;
    }



    // Set some defaults for form input fields
    $formInputs = [
        'acl' => 'bucket-owner-full-control',
        'x-amz-server-side-encryption' => 'AES256',
    ];

    // Construct an array of conditions for policy
    // https://github.com/aws/aws-sdk-php/blob/master/tests/S3/PostObjectV4Test.php  (helpful example)
    $options = [
        ['acl' => 'bucket-owner-full-control'],
        ['bucket' => S3_BUCKET],
        ['starts-with', '$key', "{$upload_prefix}/"],
        ["x-amz-server-side-encryption" => "AES256"],
    ];

    // Create our post object.  This will generate all the
    // magic stuffs needed to create a form that will post directly to s3
    $client = S3GetClient();
    $postObject = new \Aws\S3\PostObjectV4(
        $client,
        S3_BUCKET,
        $formInputs,
        $options,
        '+2 hours'
    );

    $formInputs = $postObject->getFormInputs();
    $formInputs['key'] = "{$upload_prefix}/{$filename}";

    $formAttributes = $postObject->getFormAttributes();
    $formAttributes['data-entity'] = $entity;
    $formAttributes['data-entity_id'] = $entity_id;

    // Generate the HTML parts we will need to post against AWS.
    // We will pass these into the view so we know how to render the
    // upload form.
    $output = array();
    $output['attributes'] = $formAttributes;
    $output['inputs'] = $formInputs;

    return $output;
}
function UserDefaultPlanType($external_key) {

    $CI = &get_instance();
    $CI->load->model("Company_model", "company_model", true);

    $company_id = GetSessionValue("company_id");
    if ( $company_id == "" ) return "";

    $internal_key = "";
    $pref = $CI->company_model->get_company_preference($company_id, "plan_type_map", $external_key);
    if ( ! empty($pref) ) $pref = $pref[0];
    $internal_key = getArrayStringValue("value", $pref);
    return $internal_key;

}

function ColumnMappingObjects($mapping_columns, $identifier, $identifier_type) {

    // ColumnMappingObjects
    //
    // Create and return an array of column matching objects.
    // These are the PHP objects that know how to evaluate data for a given
    // column.
    // ------------------------------------------------------------------

    $CI = &get_instance();

    // Create a validation object for each of the mapping columns
    // if one exists.
    $objects = array();
    foreach($mapping_columns as $mapping) {
        $name = getArrayStringValue("name", $mapping);
        $class_name = ucfirst(strtolower($name));
        if ( file_exists(APPPATH."libraries/mapping/{$class_name}.php") )
        {
            $CI->load->library("mapping/{$class_name}");
            $object = new $class_name($identifier, $identifier_type, null);
            $objects[] = $object;
        }
    }

    return $objects;
}
function BestMappingColumnMatchFAST( $identifier, $identifier_type, $column_no)
{
    $CI = &get_instance();

    $pref = GetPreference($identifier, $identifier_type, 'column_map', "col{$column_no}");
    if ( empty($pref) )
    {
        // If the company has never saved their preference for this column before, then we
        // will offer up the A2P Recommended column mapping.
        $results = $CI->Mapping_model->get_customer_best_mapped_column_A2PRecommended($identifier, $identifier_type, $column_no);
    }
    else
    {
        // The company has already specified which column best fits their needs.  Use what they
        // previously elected.
        $results = $CI->Mapping_model->get_customer_best_mapped_column_UserElected($identifier, $identifier_type, $column_no);
    }
    return getArrayStringValue('MappingColumnName', $results);

}
function BestMappingColumnMatch($first_row, $second_row, $mapping_columns, $column_no, $identifier, $identifier_type, $debug = false) {

    // BestMappingColumnMatch
    //
    // When we are importing a user's data file, we need to identify the
    // columns.  The user will help us do this, but we are going to try
    // and do the best we can so they have as little work to do as possible.
    // This function will examine the 1st and 2nd rows of a data file and
    // try and decide which column it should map to.
    // ---------------------------------------------------------------

    $CI = &get_instance();

    $mapping_objects = ColumnMappingObjects($mapping_columns, $identifier, $identifier_type);

    // Required Value Check.
    if ( empty($mapping_objects) ) return "";
    if ( getStringValue($identifier) == "" ) return "";


    // USER DEFINED MATCH
    // Take a look at the company preferences.  If we will assume the first row is
    // a bunch of headers.  If we can match up a previous mapping to one of the column
    // names, then we will auto match that.
    // ---------------------------------------------------------------------
    if ( ! empty($first_row) )
    {
        // Get the value for the column we are trying to match from the first row.
        $value = getArrayStringValue("{$column_no}", $first_row);

        // Do a compare against this value for each of the columns we map.
        foreach($mapping_objects as $obj)
        {
            // Pull the previous mapped column value for this header ... if it exists.
            $column_name = strtolower(get_class($obj));
            $match = GetPreferenceValue($identifier, $identifier_type, 'user_column_label_map', strtoupper($value));

            if ( $match != "" )
            {
                // YES!  We found a user defined header value.
                $mapping_exists = $CI->Mapping_model->does_column_mapping_exist($identifier, $identifier_type, $match);
                if ( $mapping_exists ) {

                    // This column has already been mapped.  Since there could be duplicate columns with
                    // the same name, we can only return a match for column that was mapped.

                    $col_match = $CI->Mapping_model->get_mapped_column_no($identifier, $identifier_type, $match);
                    if ( getStringValue($col_match) == getStringValue($column_no) )
                    {
                        return $match;
                    }

                }else{

                    // We have not seen this before.  Let's map it!

                    // Wait, if the user has duplicate columns, we can't assume we
                    // know which one it goes to!
                    $duplicate_column = false;
                    foreach($first_row as $item)
                    {
                        if ( strtoupper($item) == strtoupper($value) )
                        {
                            $duplicate_column = true;
                            break;
                        }
                    }

                    // Okay, we can auto map this.
                    if ( ! $duplicate_column )
                    {
                        SavePreference($identifier, $identifier_type, 'user_column_label_map', strtoupper($value), $match);
                        SavePreference($identifier, $identifier_type, 'column_map', "col{$column_no}", $match);
                        return $match;
                    }
                }
            }
        }
    }


    // COLUMN NUMBER MATCH
    // If the user has the preference indicating that their file does not have
    // any column headers, then we are going to have to fall back to the column
    // number mapping else they will have to re-map every time.
    // ----------------------------------------------------------------------
    if ( ! empty($first_row) )
    {
        $pref = GetPreferenceValue($identifier, $identifier_type, 'upload_contains_header_row', 'boolean');
        if ( $pref == "f" )
        {
            foreach($mapping_objects as $obj)
            {
                $column_name = strtolower(get_class($obj));
                if ( $CI->Mapping_model->does_column_mapping_exist( $identifier, $identifier_type, $column_name ) )
                {
                    $stored_column_no = $CI->Mapping_model->get_mapped_column_no($identifier, $identifier_type, $column_name);

                    if ( getStringValue($stored_column_no) == getStringValue($column_no) )
                    {
                        return $column_name;
                    }
                }
            }
        }
    }

    // A2P COLUMN HEADER MATCH
    // Anything not yet mapped?  If so, let's pretend for a minute that
    // the first row of data is a column header.  Take a look at each one of
    // those and if we can interpret the header as a known value we will
    // map it for the user.  This will be done only if the column is not
    // already mapped.
    // ----------------------------------------------------------------------
    if ( ! empty($first_row) )
    {
        if ( IsA2PAutoColumnMappingEnabled($identifier, $identifier_type) )
        {
            // Due to PHI and HIPPA compliance, we cannot auto-map a column without the
            // customer knowing on subsequent file loads.  If we always do the A2P Column header
            // match, we might pick up a column in their file that they explicitly told us not to import.
            // To that end, the user only gets our suggestions if they have never finalized before.

            $value = getArrayStringValue("{$column_no}", $first_row);
            foreach($mapping_objects as $obj)
            {
                if ( $obj->column_header_match($value) ) {
                    $match = strtolower(get_class($obj));
                    if ( ! $CI->Mapping_model->does_column_mapping_exist( $identifier, $identifier_type, $match ) ) {
                        $header_value = getArrayStringValue("{$column_no}", $first_row);
                        SavePreference($identifier, $identifier_type, 'user_column_label_map', strtoupper($header_value), $match);
                        SavePreference($identifier, $identifier_type, 'column_map', "col{$column_no}", $match);
                        return $match;
                    }
                    return "";
                }
            }
        }
    }

    // Nothing.  Just return the empty string which means the user
    // will need to map this column, not us.
    return "";
}


/* End of file wizard_helper.php */
/* Location: ./application/helpers/wizard_helper.php */
