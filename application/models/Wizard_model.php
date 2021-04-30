<?php

require('vendor/autoload.php');
//use Aws\S3\S3Client;
//use Aws\S3\Exception\S3Exception;
//use Symfony\Component\Finder\Finder;

class Wizard_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
        $this->load->helper('s3');
        $this->load->helper('wizard');
    }
    public function select_status($company_id)
    {
        $file = "database/sql/wizard/WizardSELECT_RecentStatusUpdate.sql";
        $vars = array(
            GetIntValue($company_id)
        );
        $results = ExecuteSQL( $this->db, $file, $vars );
        if ( empty($results) ) return "";
        if ( count($results) > 1 ) return "";
        $results = $results[0];
        return GetArrayStringValue('RecentStatusUpdate', $results);

    }
    public function update_status($company_id, $message)
    {
        $file = "database/sql/wizard/WizardUPDATE_status.sql";
        $vars = array(
            GetStringValue($message),
            GetIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function select_activity($company_id)
    {
        $file = "database/sql/wizard/WizardSELECT_RecentActivity.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return "";
        if ( count($results) > 1 ) return "";
        $results = $results[0];
        return GetArrayStringValue('RecentActivity', $results);
    }
    public function update_activity($company_id, $message)
    {
        if ( getStringValue($company_id) === '' ) return;
        $file = "database/sql/wizard/WizardUPDATE_RecentActivity.sql";
        $vars = array(
            getStringValue($message)
            , getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function select_recent_error( $company_id=null, $companyparent_id=null ) {

        $replaceFor = array();

        if ( GetStringValue($company_id) !== '' )
        {
            $file = "database/sql/wizard/ProcessQueueSELECT_ErrorMessage.sql";
            $replaceFor["{COMPANY_ID}"] = getIntValue($company_id);
        }
        else
        {
            $file = "database/sql/wizard/ProcessQueueSELECT_CompanyParentErrorMessage.sql";
            $replaceFor["{COMPANYPARENT_ID}"] = getIntValue($companyparent_id);
        }
        $results = GetDBResults( $this->db, $file, array(), $replaceFor );
        if ( count($results) == 1 ) return $results[0];
        return array();
    }
    public function remove_washed_records( $company_id, $import_date=null ) {

        if ( getStringValue($company_id) == "" ) return;
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/washeddata/WashedDataDELETE_byImportDate.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function get_upload_date_info( $company_id ) {
        $file = "database/sql/importdata/ImportDataSELECT_UploadDate.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) == 1 ) return $results[0];
        throw new Exception("Expected no more than 1 ageband result.");
    }
    public function get_starting_upload_date_info( $date_string ) {
        if ( getStringValue($date_string) == "" ) return array();
        $file = "database/sql/importdata/ImportDataSELECT_StartingUploadDate.sql";
        $vars = array(
            getStringValue($date_string)
            ,getStringValue($date_string)
            ,getStringValue($date_string)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) == 1 ) return $results[0];
        throw new Exception("Expected no more than 1 ageband result.");
    }
    public function get_ageband_data( $company_id, $plantypecode ) {
        $file = "database/sql/ageband/AgeBandSELECT_CountAgeBandsForCompanyPlanType.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($plantypecode)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) == 1 ) return $results[0];
        throw new Exception("Expected no more than 1 ageband result.");
    }
    public function does_company_have_all_plantypes_ignored( $company_id ) {
        $file = "database/sql/importdata/ImportDataSELECT_AllPlanTypesIgnored.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return "";
        if ( count($results) > 1 ) throw new Exception("Found too many results when evaluating mapped plantypes");
        $results = $results[0];
        $results = getArrayStringValue("EverythingIgnoredFlg", $results);
        if ( $results == "t") return true;
        return false;
    }
    public function does_company_have_all_plantypes_mapped( $company_id ) {
        $file = "database/sql/importdata/ImportDataSELECT_AllPlanTypesMapped.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return "";
        if ( count($results) > 1 ) throw new Exception("Found too many results when evaluating mapped plantypes");
        $results = $results[0];
        $results = getArrayStringValue("EverythingMappedFlg", $results);
        if ( $results == "t" ) return true;
        return false;
    }
    public function get_best_guess_retrorule( $company_id, $carrier ) {
        $file = "database/sql/wizard/RetroRulesSELECT_BestGuessByCarrier.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return "";
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plantype data");
        $results = $results[0];
        return getArrayStringValue("RetroRule", $results);
    }
    public function get_best_guess_washrule( $company_id, $carrier ) {
        $file = "database/sql/wizard/WashRulesSELECT_BestGuessByCarrier.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return "";
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plantype data");
        $results = $results[0];
        return getArrayStringValue("WashRule", $results);
    }
    public function get_best_guess_plananniversarymonth( $company_id, $carrier) {
        $file = "database/sql/wizard/PlanAnniversaryMonthSELECT_BestGuessByCarrier.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return "";
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plantype data");
        $results = $results[0];
        return getArrayStringValue("PlanAnniversaryMonth", $results);
    }
    public function get_retrorules( ) {
        $file = "database/sql/wizard/RetroRulesSELECT.sql";
        $vars = array( );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();
        return $results;
    }
    public function get_washrules( ) {
        $file = "database/sql/wizard/WashRulesSELECT.sql";
        $vars = array(  );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();
        return $results;
    }

    public function select_upload_review( $company_id ) {
        $file = "database/sql/importdata/ImportDataSELECT_review.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        return $results;
    }
    public function remove_import_records( $company_id ) {
        $file = "database/sql/importdata/ImportDataDELETE.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function DEFUNCTinsert_import_record( $company_id, $import_date, $data ) {
        // BAH.  I don't think this is used.  Marked as defunct.  Delete later.
        $annual_salary = getArrayStringValue("annual_salary", $data);
        $carrier = getArrayStringValue("carrier", $data);
        $coverage_end_date = getArrayStringValue("coverage_end_date", $data);
        $coverage_start_date = getArrayStringValue("coverage_start_date", $data);
        $coverage_tier = getArrayStringValue("coverage_tier", $data);
        $dob = getArrayStringValue("dob", $data);
        $employee_id = getArrayStringValue("eid", $data);
        $employer_cost = getArrayStringValue("employer_cost", $data);
        $employment_active = getArrayStringValue("employment_active", $data);
        $employment_end = getArrayStringValue("employment_end", $data);
        $employment_start = getArrayStringValue("employment_start", $data);
        $first_name = getArrayStringValue("first_name", $data);
        $gender = getArrayStringValue("gender", $data);
        $last_name = getArrayStringValue("last_name", $data);
        $middle_name = getArrayStringValue("middle_name", $data);
        $plan_type = getArrayStringValue("plan_type", $data);
        $plan = getArrayStringValue("plan", $data);
        $ssn = getArrayStringValue("ssn", $data);
        $tobacco_user = getArrayStringValue("tobacco_user", $data);
        $volume = getArrayStringValue("volume", $data);

        $plan_type_code = getArrayStringValue("plan_type_code", $data);

        $file = "database/sql/importdata/ImportDataINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue("f")
            , ( $employee_id == "" ? null : getStringValue($employee_id) )
            , ( $plan_type == "" ? null : getStringValue($plan_type) )
            , ( $plan_type_code == "" ? null : getStringValue($plan_type_code) )
            , ( $first_name == "" ? null : getStringValue($first_name) )
            , ( $last_name == "" ? null : getStringValue($last_name) )
            , ( $coverage_start_date == "" ? null : getStringValue($coverage_start_date) )
            , ( $coverage_end_date == "" ? null : getStringValue($coverage_end_date) )
            , ( $annual_salary == "" ? null : getStringValue($annual_salary) )
            , ( $carrier == "" ? null : getStringValue($carrier) )
            , ( $coverage_tier == "" ? null : getStringValue($coverage_tier) )
            , ( $dob == "" ? null : getStringValue($dob) )
            , ( $employer_cost == "" ? null : getStringValue($employer_cost) )
            , ( $employment_active == "" ? null : getStringValue($employment_active) )
            , ( $employment_end == "" ? null : getStringValue($employment_end) )
            , ( $employment_start == "" ? null : getStringValue($employment_start) )
            , ( $middle_name == "" ? null : getStringValue($middle_name) )
            , ( $gender == "" ? null : getStringValue($gender) )
            , ( $plan == "" ? null : getStringValue($plan) )
            , ( $ssn == "" ? null : getStringValue($ssn) )
            , ( $tobacco_user == "" ? null : getStringValue($tobacco_user) )
            , ( $volume == "" ? null : getStringValue($volume) )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function reset_wizard_to_match( $company_id ) {
        $file = "database/sql/wizard/WizardUPDATE_ResetToMatch.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function get_mapping_for_upload_column($identifier, $identifier_type, $upload_column)
    {
        // upload column is "col#" and you get back the mapped name like "first_name".
        if ( $identifier_type === 'company') $file = "database/sql/wizard/GetMappingForUploadColumn_ByCompany.sql";
        if ( $identifier_type === 'companyparent') $file = "database/sql/wizard/GetMappingForUploadColumn_ByParent.sql";
        $vars = array(
            getStringValue($upload_column),
            getIntValue($identifier)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) > 1) throw new Exception("Got too many results when lookup up the upload column fora given mapping.");
        if ( count($results) == 0 ) return "";
        $results = $results[0];

        $result = getArrayStringValue("column_name", $results);
        return $result;
    }
    public function rollback_to_start( $company_id ) {

        if ( ! $this->does_wizard_record_exist($company_id) ) return;

        $file = "database/sql/wizard/WizardUPDATE_RollbackToStart.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function create_wizard_record( $company_id, $user_id ) {
        $file = "database/sql/wizard/WizardINSERT_CreateRecord.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($user_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function delete_wizard( $company_id ) {
        $file = "database/sql/wizard/DeleteWizardUpload.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function does_wizard_record_exist($company_id) {
        $file = "database/sql/wizard/DoesWizardRecordExist.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("exists", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function is_startup_step_complete($company_id) {
        $file = "database/sql/wizard/IsStartupStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function startup_step_complete( $company_id ) {
        $file = "database/sql/wizard/WizardStartupStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function upload_step_complete($company_id, $filename ) {
        $file = "database/sql/wizard/WizardUploadStepComplete.sql";
        $vars = array(
            ( $filename == "" ? null : getStringValue($filename) )
            , getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $payload = array();
        $payload['ImportDate'] = GetUploadDate($company_id);
        AuditIt('File uploaded.', $payload);
    }
    public function is_upload_step_complete($company_id) {
        $file = "database/sql/wizard/IsUploadStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function parsing_step_complete( $company_id ) {
        $file = "database/sql/wizard/WizardParsingStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function is_parsing_step_complete($company_id) {
        $file = "database/sql/wizard/IsParsingStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function validation_step_complete( $company_id ) {
        $file = "database/sql/wizard/WizardValidationStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function is_validation_step_complete($company_id) {
        $file = "database/sql/wizard/IsValidationStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function match_step_complete( $company_id ) {
        $file = "database/sql/wizard/WizardMatchStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function match_step_incomplete( $company_id ) {
        $file = "database/sql/wizard/WizardMatchStepIncomplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function is_match_step_complete($company_id) {
        $file = "database/sql/wizard/IsMatchStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function correction_step_complete( $company_id ) {
        $file = "database/sql/wizard/WizardCorrectionStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function is_correction_step_complete($company_id) {
        $file = "database/sql/wizard/IsCorrectionStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function plan_review_step_complete( $company_id ) {
        $file = "database/sql/wizard/WizardPlanReviewStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function plan_review_step_incomplete( $company_id ) {
        $file = "database/sql/wizard/WizardUPDATE_PlanReviewStepIncomplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function is_plan_review_step_complete($company_id) {
        $file = "database/sql/wizard/IsPlanReviewStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function is_relationship_step_complete($company_id) {
        $file = "database/sql/wizard/WizardSELECT_IsRelationshipStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function relationship_step_complete( $company_id ) {
        $file = "database/sql/wizard/WizardUPDATE_RelationshipStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

    public function is_lives_step_complete($company_id) {
        $file = "database/sql/wizard/WizardSELECT_IsLivesStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function is_clarifications_step_complete($company_id) {
        $file = "database/sql/wizard/WizardSELECT_IsClarificationsStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }

    public function lives_step_complete( $company_id ) {
        $file = "database/sql/wizard/WizardUPDATE_LivesStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function clarifications_step_complete( $company_id ) {
        $file = "database/sql/wizard/WizardUPDATE_ClarificationsStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function clarifications_step_incomplete($company_id) {
        $file = "database/sql/wizard/WizardUPDATE_SetClarificationsIncomplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function reset_wizard_to_plan_review( $company_id ) {
        $file = "database/sql/wizard/UpdateWizardForPlanReviewAgain.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function reset_wizard_to_adjustments( $company_id ) {
        $file = "database/sql/wizard/WizardUPDATE_ResetToAdjustmentStep.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function reset_wizard_to_relationships( $company_id ) {
        $file = "database/sql/wizard/WizardUPDATE_ResetToRelationshipStep.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function reset_wizard_to_lives( $company_id ) {
        $file = "database/sql/wizard/WizardUPDATE_ResetToLivesStep.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function reset_wizard_to_clarifications( $company_id ) {
        $file = "database/sql/wizard/WizardUPDATE_ResetToClarificationsStep.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function saving_step_complete( $company_id ) {
        $file = "database/sql/wizard/WizardSavingStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function is_saving_step_complete($company_id) {
        $file = "database/sql/wizard/IsSavingStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function report_generation_incomplete($company_id) {
        $file = "database/sql/wizard/WizardUPDATE_SetReportGenerationIncomplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function report_generation_complete( $company_id ) {
        $file = "database/sql/wizard/WizardReportGenerationStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function is_report_generation_complete($company_id) {
        $file = "database/sql/wizard/IsReportGenerationStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function is_adjustment_step_complete($company_id) {
        $file = "database/sql/wizard/IsAdjustmentStepComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function adjustment_step_complete($company_id) {
        $file = "database/sql/wizard/WizardUPDATE_SetAdjustmentsComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function finalization_started( $company_id ) {
        $file = "database/sql/importdata/ImportDataUPDATE_FinalizationStarted.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function finalization_completed( $company_id ) {
        $file = "database/sql/importdata/ImportDataUPDATE_FinalizationCompleted.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function is_finalizing($company_id) {
        $file = "database/sql/wizard/IsFinalizingImportData.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Got unexpected results while trying to check if wizard record existed or not.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function is_wizard_complete($company_id) {

        // I think this is what we need to do for is wizard complete now.
        // Since finalizing is a wizard step.
        if ( ! $this->has_wizard_started($company_id) ) return true;
        return false;

        $file = "database/sql/wizard/WizardSELECT_IsWizardComplete.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Unexpected situation.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function has_wizard_started($company_id) {
        $file = "database/sql/wizard/WizardSELECT_HasWizardStarted.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Unexpected situation.");
        $result = getArrayStringValue("complete", $results[0]);
        if ( $result == "t") return true;
        return false;
    }
    public function select_wizard_data($company_id) {
        $file = "database/sql/wizard/WizardSELECT.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) return array();
        return $results[0];
    }


}


/* End of file Wizard_model.php */
/* Location: ./system/application/models/Wizard_model.php */
