<?php
class Reporting_model extends CI_Model{

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    function delete_critical_report_warnings_zero_file_length( $company_id, $import_date, $report_name )
    {
        $replace_for = array();
        $replace_for['{REPORT_NAME}'] = $report_name;

        $file = "database/sql/reportreview/ReportReviewWarningDELETE_CriticalReportWarningZeroFileLengthDetected.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars, $replace_for );
    }
    function select_critical_report_warnings_zero_file_length( $company_id, $import_date, $report_name )
    {
        $replace_for = array();
        $replace_for['{REPORT_NAME}'] = $report_name;

        $file = "database/sql/reportreview/ReportReviewWarningSELECT_CriticalReportWarningZeroFileLengthDetected.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars, $replace_for );
        if ( empty($results) ) return array();
        return $results;
    }
    function select_existing_carrier_reports($company_id, $import_date, $report_type_id)
    {
        $file = "database/sql/reporting/CompanyReportSELECT_ExistingCarrierReports.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($report_type_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function select_report_review_counts($company_id)
    {
        $import_date = GetUploadDate($company_id);
        if ( $import_date === '' ) return array();

        $file = "database/sql/reportreview/ReportReviewWarningSELECT_Counts.sql";
        $vars = array(
            GetIntValue($company_id),
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results[0];
    }
    function select_import_dates( $company_id )
    {
        $file = "database/sql/importdata/ImportDataSELECT_ImportDates.sql";
        $vars = array(
            GetIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function insert_report_review_warning_generic( $company_id, $import_date, $message, $import_date_id=0)
    {
        $file = "database/sql/reporting/ReportReviewWarningINSERT_Generic.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($import_date_id)
            , getStringValue($message)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function select_warnings_filename_details( $company_id )
    {
        $import_date = GetUploadDate($company_id);
        if ( $import_date === '' ) throw new Exception("Invalid import date.");

        $file = "database/sql/reporting/CompanySELECT_FilenameDetailsGeneric.sql";
        $vars = array(
            GetStringValue($import_date),
            GetIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when looking up filename details.");
        return $results[0];

    }
    function does_warning_report_contain_universal_eid( $company_id, $import_date=null, $encryption_key )
    {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        if ( $import_date === '' ) throw new Exception('Invalid import date');

        $file = "database/sql/reportreview/ReportReviewWarningSELECT_FirstEmployeeId.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 0 ) return false;

        $results = $results[0];
        $eid = GetArrayStringValue("EmployeeId", $results);
        $item = A2PDecryptString($eid, $encryption_key);
        if ( StartsWith($item, EUID_TAG) ) return true;
        return false;
    }
    function create_warnings_report( $fh, $encryption_key, $company_id)
    {
        $import_date = GetUploadDate($company_id);
        if ( $import_date === '' ) throw new Exception("Invalid import date.");

        if ( $this->does_warning_report_contain_universal_eid($company_id, $import_date, $encryption_key) )
        {
            $file = "database/sql/reportreview/ReportReviewWarningSELECT_ReportNoEmployeeId.sql";
        }
        else
        {
            $file = "database/sql/reportreview/ReportReviewWarningSELECT_Report.sql";
        }
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        WriteDBSecureFile($fh, $this->db, $file, $vars, $company_id, $encryption_key);

    }
    public function get_report_property_value($report_code, $group, $key)
    {
        $property = $this->get_report_property($report_code, $group, $key);
        $value = GetArrayStringValue("Value", $property);
        return $value;
    }
    public function get_report_property($report_code, $group, $key)
    {
        $file = "database/sql/reporting/ReportPropertySELECT.sql";
        $vars = array(
            GetStringValue($report_code),
            GetStringValue($group),
            GetStringValue($key)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when looking up report property.");
        return $results[0];
    }

    public function get_recent_report_id( $company_id, $report_code)
    {
        $file = "database/sql/reporting/CompanyReportSELECT_CompaniesMostRecentReportOfType.sql";
        $vars = array(
            getIntValue($company_id),
            GetStringValue($report_code)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) !== 1 ) return "";
        return GetArrayStringValue("Id", $results[0]);
    }
    public function select_report_filename_details( $report_id )
    {
        $file = "database/sql/reporting/CompanyReportSELECT_FilenameDetails.sql";
        $vars = array(
            getIntValue($report_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) != 1 ) return array();
        return $results[0];
    }
    function insert_summary_data_premium_equivalent_records( $company_id ) {
        $import_date = GetUploadDate($company_id);

        $file = "database/sql/summarydata/SummaryDataPremiumEquivalentINSERT_InitialCopy.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function select_recent_date( $company_id ) {

        $file = "database/sql/importdata/RecentDateSELECT.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) != 1 ) return array();
        return $results[0];
    }
    function select_import_date( $company_id ) {

        $file = "database/sql/importdata/ImportDateSELECT.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) != 1 ) return array();
        return $results[0];
    }
    function format_date( $date ) {

        if ( GetStringValue($date) === '' ) return array();
        $file = "database/sql/importdata/FormatDateSELECT.sql";
        $vars = array( );

        $replaceFor = array();
        $replaceFor['{DATE}'] = $date;

        $results = GetDBResults( $this->db, $file, $vars, $replaceFor );
        if ( empty($results) ) return array();
        if ( count($results) != 1 ) return array();
        return $results[0];
    }


    function select_draft_reports( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/reporting/CompanyReportSELECT_HistoryDraft.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
        );
        $draft = GetDBResults( $this->db, $file, $vars );
        if ( empty($draft) ) $draft = array();
        return $draft;

    }
    function select_report_history( $company_id, $import_date='' ) {

        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/reporting/CompanyReportSELECT_History.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
        );
        $finalized = GetDBResults( $this->db, $file, $vars );
        if ( empty($finalized) ) $finalized = array();
        return $finalized;

    }
    function select_draft_company_report_id ( $company_id, $carrier_id, $report_type_id, $import_date=null ) {

        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/reporting/CompanyReportSELECT_ByData.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($carrier_id)
            , getIntValue($report_type_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) {
            $results = $results[0];
            return getArrayStringValue("Id", $results);
        }
        return "";


    }
    function select_company_report( $company_id, $report_id ) {
        $file = "database/sql/reporting/CompanyReportSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($report_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) return $results[0];
        return array();
    }
    function company_report_exists( $company_id, $carrier_id, $report_type, $import_date )
    {
        // Convert the report type code into an integer.
        $type_info = $this->select_report_type($report_type);
        if ( count($type_info) == 0 ) return array();
        $report_type_id = getArrayIntValue("Id", $type_info);

        $file = "database/sql/reporting/CompanyReportSELECT_ByData.sql";
        $vars = array(
            getIntValue($company_id),
            getIntValue($carrier_id),
            getIntValue($report_type_id),
            getStringValue($import_date),
        );
        return GetDBExists( $this->db, $file, $vars );

    }
    function insert_company_report( $company_id, $carrier_id, $report_type, $import_date=null ) {

        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        // Convert the report type code into an integer.
        $type_info = $this->select_report_type($report_type);
        if ( count($type_info) == 0 ) return array();
        $report_type_id = getArrayIntValue("Id", $type_info);

        $file = "database/sql/reporting/CompanyReportINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($report_type_id)
            , getIntValue($carrier_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function delete_company_report( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/reporting/CompanyReportDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function delete_company_report_by_type( $company_id, $report_type_code, $import_date=null )
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/reporting/CompanyReportDELETE_ByType.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetStringValue($report_type_code)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function delete_company_report_by_id( $id ) {

        $file = "database/sql/reporting/CompanyReportDELETE_ById.sql";
        $vars = array(
            getIntValue($id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function select_report_type( $type_code ) {
        $file = "database/sql/reporting/ReportTypeSELECT_ByType.sql";
        $vars = array(
            getStringValue($type_code)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) return $results[0];
        return array();
    }
    function select_report_types(  ) {
        $file = "database/sql/reporting/ReportTypeSELECT.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return $results;
        return $results;
    }
    function select_carrier_by_id( $company_id, $carrier_id) {
        $file = "database/sql/reporting/CompanyCarrierSELECT_ByCarrierId.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($carrier_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) return $results[0];
        return array();
    }
    function write_detail_report_import_data($fh, $company_id, $carrier_id, $encryption_key, $premium_equivalent=false, $write_headers=true ) {

        if ( getStringValue($carrier_id)  == "" ) return array();
        if ( getStringValue($company_id)  == "" ) return array();
        
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $pe = "f";
        if ( $premium_equivalent ) $pe = "t";

        $file = "database/sql/importdata/ImportDataSELECT_DetailReport.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
            , getStringValue($pe)
        );
        $results = WriteDBSecureFile( $fh, $this->db, $file, $vars, $company_id, $encryption_key, array(), $write_headers, "A2PBillingReportOutputRules" );
        if ( $results === FALSE )
        {
            throw new Exception("Unable to write records to disk");
        }
        return $results;

    }
    function write_detail_report_automatic_adjustments( $fh, $company_id, $carrier_id, $encryption_key, $premium_equivalent=false, $write_headers=true ) {

        if ( getStringValue($carrier_id)  == "" ) return array();
        if ( getStringValue($company_id)  == "" ) return array();

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $pe = "f";
        if ( $premium_equivalent ) $pe = "t";

        $file = "database/sql/adjustments/AutomaticAdjustmentSELECT_DetailReport.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
            , getStringValue($pe)
        );
        $results = WriteDBSecureFile( $fh, $this->db, $file, $vars, $company_id, $encryption_key, array(), $write_headers, "A2PBillingReportOutputRules" );
        if ( $results === FALSE )
        {
            throw new Exception("Unable to write records to disk");
        }
        return $results;

    }
    function write_detail_report_manual_adjustments( $fh, $company_id, $carrier_id, $encryption_key, $write_headers=true ) {

        if ( getStringValue($carrier_id)  == "" ) return array();
        if ( getStringValue($company_id)  == "" ) return array();

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/adjustments/ManualAdjustmentSELECT_DetailReport.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
        );
        $results = WriteDBSecureFile( $fh, $this->db, $file, $vars, $company_id, $encryption_key, array(), $write_headers, "A2PBillingReportOutputRules" );
        if ( $results === FALSE )
        {
            throw new Exception("Unable to write records to disk");
        }
        return $results;

    }
    function write_commission_report($fh, $company_id, $carrier_id, $encryption_key, $write_headers=true )
    {

        if ( getStringValue($carrier_id)  == "" ) return array();
        if ( getStringValue($company_id)  == "" ) return array();

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/commissions/CompanyCommissionSELECT_A2PCommissionDetailReport.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($carrier_id)
        );
        $results = WriteDBSecureFile( $fh, $this->db, $file, $vars, $company_id, $encryption_key, array(), $write_headers );
        if ( $results === FALSE )
        {
            throw new Exception("Unable to write records to disk");
        }
        return $results;

    }

    function is_tobacco_ignored( $company_id, $coveragetier_id ) {

        // Did the user not even map the TobaccoUser column?  No, tobacco is ignored.
        if ( ! HasTobaccoUser($company_id) ) return "t";

        // Does associated PlanType even support the Tobacco Attribute?  No, tobacco is ignored.
        $file = "database/sql/reporting/PlanTypesSELECT_IsTobaccoPlanType.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($coveragetier_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 )
        {
            $results = $results[0];
            $results = GetArrayStringValue("SupportsTobaccoAttribute", $results);
            if ( $results == "f" ) return "t";
        }

        // Did the user elect to ignore tobacco?
        $file = "database/sql/reporting/CompanyCoverageTierSELECT_IsTobaccoIgnored.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($coveragetier_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 )
        {
            $results = $results[0];
            $results = GetArrayStringValue("TobaccoIgnored", $results);
            return $results;
        }
        return "t";
    }
    function delete_downloadable_reports ( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $prefix = GetConfigValue("reporting_prefix");
        $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
        $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id, $import_date));
        $prefix = fLeftBack(fLeftBack($prefix, "TYPE"), "/");

        try {
            S3GetClient();
            S3DeleteBucketContent(S3_BUCKET, $prefix);
        }catch(Exception $e) {}

    }
    function select_summary_data_premium_equivalent_by_carrier( $company_id, $carrier_id ) {
        return $this->select_summary_data_generic_by_carrier( $company_id, $carrier_id, true );
    }
    function select_summary_data_by_carrier( $company_id, $carrier_id ) {
        return $this->select_summary_data_generic_by_carrier( $company_id, $carrier_id, false );
    }
    function select_summary_data_generic_by_carrier( $company_id, $carrier_id, $premium_equivalent=false ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $replaceFor = array();
        $replaceFor['{TABLENAME}'] = "SummaryData";
        if ( $premium_equivalent ) $replaceFor['{TABLENAME}'] = "SummaryDataPremiumEquivalent";


        $file = "database/sql/summarydata/SummaryDataGenericSELECT_ReportByCarrier.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
        );
        $results = GetDBResults( $this->db, $file, $vars, $replaceFor );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function select_summary_data_prepared_date ( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/summarydata/SummaryDataSELECT_PreparedDate.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1 ) return "";
        return $results = $results[0];

    }

    function get_downloadable_reports( $company_id, $carrier_id, $report_date )
    {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();
        $file = "database/sql/summarydata/CompanyReportSELECT_ReportReviewDownloadableReports.sql";
        $vars = array(
            GetIntValue($company_id),
            GetIntValue($carrier_id),
            GetStringValue($report_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        return $results;
    }

    function select_summary_data_report_review( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/summarydata/SummaryDataTablesSELECT_ReportReview.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function select_summary_data_report_review_zero_dollar( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/summarydata/SummaryDataTablesSELECT_ReportReview_ZeroDollar.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function update_summary_data_totals( $company_id, $summarydata_id, $lives, $volume, $premium ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/summarydata/SummaryDataUPDATE_TotalByAttributes.sql";
        $vars = array(
            getIntValue($lives)
            , getFloatValue($volume)
            , getFloatValue($premium)
            , getIntValue($summarydata_id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function select_summary_data_totals( $company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $ageband_id, $tobacco_user ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        // We want to pull only the summary data records that match the attributes we
        // are looking for ( ageband and tobacco ).  We can't bind here so we need to do
        // a replace.
        $replaceFor = array();
        $replaceFor["{AGEBAND}"] = " = " . intval($ageband_id) . " ";
        if ( $ageband_id == null ) $replaceFor["{AGEBAND}"] = " is null ";
        $replaceFor["{TOBACCOUSER}"] = " = '" . trim($this->db->escape($tobacco_user), "'") . "' ";
        if ( $tobacco_user == null ) $replaceFor["{TOBACCOUSER}"] = " is null ";

        // Use slightly different quries if Tobacco is ignored.
        $file = "database/sql/summarydata/SummaryDataSELECT_TotalByAttributes.sql";
        if ( $this->is_tobacco_ignored($company_id, $coveragetier_id) == "t" )
        {
            $file = "database/sql/summarydata/SummaryDataSELECT_TotalByAttributes_TobaccoIgnored.sql";
        }
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
            , getIntValue($plan_id)
            , getIntValue($coveragetier_id)
        );
        $results = GetDBResults( $this->db, $file, $vars, $replaceFor );
        if ( count($results) == 0 ) return array();
        if ( count($results) == 1 ) return $results[0];
        throw new Exception("Calculating summary totals resulted in an unexpected situation.");

    }
    function select_summary_data( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/summarydata/SummaryDataSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function insert_summary_data_record( $company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $ageband_id, $tobacco_user ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/summarydata/SummaryDataINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , ( $carrier_id == null ? null : getIntValue($carrier_id) )
            , ( $plantype_id == null ? null : getIntValue($plantype_id) )
            , ( $plan_id == null ? null : getIntValue($plan_id) )
            , ( $coveragetier_id == null ? null : getIntValue($coveragetier_id) )
            , ( $ageband_id == null ? null : getIntValue($ageband_id) )
            , ( $tobacco_user == null ? null : getStringValue($tobacco_user) )
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function does_summary_data_tobacco_user_catch_all_record_exist( $company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $ageband_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/summarydata/SummaryDataEXISTS_TobaccoUserCatchAll.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
            , getIntValue($plan_id)
            , getIntValue($coveragetier_id)
            , ( $ageband_id == null ? null : getIntValue($ageband_id) )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return "f";
        if ( count($results) == 1 ) return getArrayStringValue("Exists", $results[0]);
        throw new Exception("Unexpected results trying to check on the tobacco user summary data record.");

    }
    function select_summary_data_tobacco_user( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/summarydata/SummaryDataSELECT_TobaccoUser.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function does_summary_data_ageband_catch_all_record_exist( $company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $tobacco_user ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/summarydata/SummaryDataEXISTS_BandedCatchAll.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
            , getIntValue($plan_id)
            , getIntValue($coveragetier_id)
            , ( $tobacco_user == null ? null : getStringValue($tobacco_user) )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return "f";
        if ( count($results) == 1 ) return getArrayStringValue("Exists", $results[0]);
        throw new Exception("Unexpected results trying to check on the banded summary data record.");

    }
    function select_summary_data_banded( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/summarydata/SummaryDataSELECT_Banded.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function select_summary_report_carriers( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/summarydata/SummaryDataSELECT_Carriers.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function insert_summary_data( $company_id, $carrier_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/summarydata/SummaryDataINSERT_Groupings.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
            , getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function delete_summary_data( $company_id, $import_date=null ) {
        $this->delete_summary_data_generic("SummaryData", $company_id, $import_date);
    }
    function delete_summary_data_premium_equivalent( $company_id, $import_date=null ) {
        $this->delete_summary_data_generic("SummaryDataPremiumEquivalent", $company_id, $import_date);
    }
    function delete_summary_data_generic( $table_name, $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $replaceFor = array();
        $replaceFor['{TABLENAME}'] = $table_name;

        $file = "database/sql/summarydata/SummaryDataGenericDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars, $replaceFor );

    }
    function select_report_review_warnings_confirmation( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/reportreview/ReportReviewWarningSELECT_Confirmation.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function select_report_review_warnings_not_confirmation( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/reportreview/ReportReviewWarningSELECT_NotConfirmation.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function select_report_review_warnings( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/reportreview/ReportReviewWarningSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function select_report_review_warnings_no_employee_id( $company_id, $import_date = null ) {

        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/reportreview/ReportReviewWarningSELECT_NoEmployeeId.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function insert_wash_warnings( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/reportreview/ReportReviewWarningINSERT_WashWarnings.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function delete_report_review_warnings( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/reportreview/ReportReviewWarningDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function insert_washed_data( $company_id ) {
        //$this->insert_washed_data_ORIGINAL($company_id);
        $this->insert_washed_data_FASTER($company_id);
    }
    function insert_washed_data_ORIGINAL( $company_id ) {
        $file = "database/sql/washeddata/WashedDataINSERT.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function insert_washed_data_FASTER( $company_id )
    {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/washeddata/WashedDataINSERT_Faster.sql";
        $vars = array(
            getIntValue($company_id),
            GetStringValue($import_date)
        );
        SelectIntoInsert( $this->db, $file, $vars );
    }
    function does_company_have_finalized_data( $company_id ) {
        $file = "database/sql/reporting/ImportDataSELECT_HasFinalizedData.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return FALSE;
        if ( count($results) > 1 ) throw new Exception("Unexpected results.");
        $results = $results[0];
        if ( getArrayStringValue("HasFinalizedData", $results) == "t" ) return true;
        if ( getArrayStringValue("HasFinalizedData", $results) == "f" ) return false;
        throw new Exception("Unexpected results.");
    }
    function has_company_import_been_finalized( $company_id, $import_date ) {
        $file = "database/sql/reporting/ImportDataSELECT_HasImportBeenFinalized.sql";
        $vars = array
        (
            getIntValue($company_id),
            getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return FALSE;
        if ( count($results) > 1 ) throw new Exception("Unexpected results.");
        $results = $results[0];
        if ( getArrayStringValue("HasBeenFinalized", $results) == "t" ) return true;
        if ( getArrayStringValue("HasBeenFinalized", $results) == "f" ) return false;
        throw new Exception("Unexpected results.");
    }
    function finalize_upload_data( $company_id ) {
        $file = "database/sql/reporting/ImportDataUPDATE_MarkAsFinalized.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function save_new_company_carriers($company_id) {

        // Create any missing carriers.
        $file = "database/sql/reporting/CompanyCarrierINSERT_AddNewCarriersByCompany.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // List carriers without user descriptions.
        $file = "database/sql/reporting/CompanyCarrierSELECT_MissingCarrierDescription.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return;

        // Apply the the user description. (Formatting that matches some/all of the import data)
        foreach($results as $item)
        {
            $carrier_id = getArrayStringValue("Id", $item);
            $normalized_carrier = getArrayStringValue("CarrierNormalized", $item);

            $file = "database/sql/reporting/CompanyCarrierUPDATE_UserDescription.sql";
            $vars = array(
                getIntValue($company_id)
                , getStringValue($normalized_carrier)
                , getIntValue($carrier_id)
            );
            ExecuteSQL( $this->db, $file, $vars );
        }


    }
    function save_new_company_plan_types($company_id) {

        // CREATE: create any missing plan types for this company.
        $file = "database/sql/reporting/CompanyPlanTypeINSERT_AddNewPlanTypesByCompany.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // FIND: find plan types for this company that have no user description.
        $file = "database/sql/reporting/CompanyPlanTypeSELECT_MissingPlanTypeDescription.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return;


        // UPDATE: Apply the the user description.
        foreach($results as $item)
        {
            $record_id              = getArrayStringValue("Id", $item);
            $normalized_carrier     = getArrayStringValue("CarrierNormalized", $item);
            $normalized_plan_type   = getArrayStringValue("PlanTypeNormalized", $item);

            $file = "database/sql/reporting/CompanyPlanTypeUPDATE_UserDescription.sql";
            $vars = array(
                getIntValue($company_id)
                , getStringValue($normalized_carrier)
                , getStringValue($normalized_plan_type)
                , getIntValue($record_id)
            );
            ExecuteSQL( $this->db, $file, $vars );
        }


    }
    function save_new_company_plans($company_id) {

        // CREATE: create any missing plans for this company.
        $file = "database/sql/reporting/CompanyPlanINSERT_AddNewPlansByCompany.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // FIND: find plans for this company that have no user description.
        $file = "database/sql/reporting/CompanyPlanSELECT_MissingPlanDescription.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return;

        // UPDATE: Apply the the user description.
        foreach($results as $item)
        {
            $record_id              = getArrayStringValue("Id", $item);
            $normalized_carrier     = getArrayStringValue("CarrierNormalized", $item);
            $normalized_plan_type   = getArrayStringValue("PlanTypeNormalized", $item);
            $normalized_plan        = getArrayStringValue("PlanNormalized", $item);

            $file = "database/sql/reporting/CompanyPlanUPDATE_UserDescription.sql";
            $vars = array(
                getIntValue($company_id)
                , getStringValue($normalized_carrier)
                , getStringValue($normalized_plan_type)
                , getStringValue($normalized_plan)
                , getIntValue($record_id)
            );
            ExecuteSQL( $this->db, $file, $vars );
        }
    }
    function save_new_company_coverage_tiers($company_id) {

        // CREATE: create any missing coverage tiers for this company.
        $file = "database/sql/reporting/CompanyCoverageTierINSERT_AddNewCoverageTiersByCompany.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // FIND: find coverage tiers for this company that have no user description.
        $file = "database/sql/reporting/CompanyCoverageTierSELECT_MissingCoverageTierDescription.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return;

        // UPDATE: Apply the coverage tier user description.
        foreach($results as $item)
        {
            $record_id                  = getArrayStringValue("Id", $item);
            $normalized_carrier         = getArrayStringValue("CarrierNormalized", $item);
            $normalized_plan_type       = getArrayStringValue("PlanTypeNormalized", $item);
            $normalized_plan            = getArrayStringValue("PlanNormalized", $item);
            $normalized_coverage_tier   = getArrayStringValue("CoverageTierNormalized", $item);

            $file = "database/sql/reporting/CompanyCoverageTierUPDATE_UserDescription.sql";
            $vars = array(
                getIntValue($company_id)
                , getStringValue($normalized_carrier)
                , getStringValue($normalized_plan_type)
                , getStringValue($normalized_plan)
                , getStringValue($normalized_coverage_tier)
                , getIntValue($record_id)
            );
            ExecuteSQL( $this->db, $file, $vars );
        }
    }
    function get_plantype_description_by_code($plantype_code)
    {
        $file = "database/sql/reporting/PlanTypesSELECT_CodeDescription.sql";
        $vars = array(
            getStringValue($plantype_code)
        );
        $results = ExecuteSQL( $this->db, $file, $vars );
        if ( count($results) === 1 )
        {
            $results = $results[0];
            return GetArrayStringValue("Display", $results);
        }
        return "";
    }
    function list_distinct_plantypes( $company_id, $plantype_code )
    {
        $file = "database/sql/reporting/CompanyPlanTypeSELECT_DistinctPlanTypes.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($plantype_code)
        );
        $results = ExecuteSQL( $this->db, $file, $vars );

        $list = array();
        foreach($results as $result)
        {
            $item = GetArrayStringValue("UserDescription", $result);
            $list[] = $item;
        }
        return $list;
    }
    function list_distinct_plantypes_for_plan( $company_id, $plan_code )
    {
        $file = "database/sql/reporting/CompanyPlanSELECT_DistinctPlanTypesForPlan.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($plan_code)
        );
        $results = ExecuteSQL( $this->db, $file, $vars );

        $list = array();
        foreach($results as $result)
        {
            $item = GetArrayStringValue("UserDescription", $result);
            $list[] = $item;
        }
        return $list;
    }
    function list_distinct_plans_for_tier( $company_id, $coveragetier_normalized )
    {
        $file = "database/sql/reporting/CompanyCoverageTierSELECT_DistinctPlansForTier.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($coveragetier_normalized)
        );
        $results = ExecuteSQL($this->db, $file, $vars);

        $list = array();
        foreach ($results as $result) {
            $item = GetArrayStringValue("UserDescription", $result);
            $list[] = $item;
        }
        return $list;
    }
    function insert_retrodatalifeeventwarnings( $company_id, $import_date )
    {
        $file = "database/sql/reportreview/ReportReviewWarningINSERT_RetroDataLifeEventWarnings.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
}

/* End of file reporting_model.php */
/* Location: ./application/models/reporting_model.php */
