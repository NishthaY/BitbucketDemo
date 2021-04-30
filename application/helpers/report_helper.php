<?php
function WriteReportReviewWarningMessage($company_id, $import_date, $message, $import_data_id=0)
{
    $CI = &get_instance();

    if ( GetStringValue($company_id) === '' ) return;
    if ( GetStringValue($import_date) === '' ) return;
    if ( GetStringValue($message) === '' ) return;
    if ( GetStringValue($import_data_id) === '' ) $import_data_id = 0;

    $CI->Reporting_model->insert_report_review_warning_generic($company_id, $import_date, $message, $import_data_id);
}
function A2PBillingReportOutputRules($string, $column_no)
{

    // Never output a string that starts with the Universal Employee Id
    // unless it happens to be column zero.  Column zero of the A2P Billing
    // report is the UEID column.  This data could be found in other columns
    // and we don't want to show that to the end user.
    if ( $column_no !== 0 && StartsWith($string, EUID_TAG) ) return "";


    // Column 0 - Universal Employee Id
    // This column contains a tagged guid.  When writing this data out, suppress the tag.
    if ( $column_no === 0 && StartsWith($string, EUID_TAG) ) return replaceFor($string, EUID_TAG, "");


    return $string;
}
/**
 * GetProcessReportFilename
 *
 * We have a report called "Potential Issues" or "Warnings".  This function
 * will return the public facing filename for this report.
 *
 * @param $company_id
 * @return string
 */
function GetProcessReportFilename($company_id)
{
    $CI = &get_instance();

    $details = $CI->Reporting_model->select_warnings_filename_details($company_id);
    if ( empty($details) ) return '';

    $company_id = GetArrayStringValue("CompanyId", $details);
    $company_name = GetArrayStringValue('CompanyName', $details);
    $report_date = GetArrayStringValue('ReportDate', $details);

    // Calculate the SUFFIX
    $suffix = "csv";

    // Calculate the report type that we will show the user.
    $report_type_display = "process_report";

    // Calculate the level
    $level = LevelTag();
    if ( $level === 'PROD' ) $level = "";
    if ( $level !== '' ) $level .= "_";

    // Format the report filename in A2P format.
    $filename = "{$level}A2P_{$company_name}_{$report_date}_{$report_type_display}.{$suffix}";

    // FEATURE: ( TRANSAMERICA_FILE_FORMAT )
    // If this report is for Transamerica, format it as the Transamerica file format.
    if ( $CI->Feature_model->is_feature_enabled($company_id, 'TRANSAMERICA_FILE_FORMAT') )
    {
        $tag = "ProcessReport";
        if ( $tag !== '' )
        {
            $filename = "{$level}TA-{COMPANY_NAME}-{YYYY}-{MM}-A2P-{TAG}.{$suffix}";
            $filename = replaceFor($filename, "{COMPANY_NAME}", getArrayStringValue("CompanyName", $details));
            $filename = replaceFor($filename, "{YYYY}", substr($report_date, 0, 4));
            $filename = replaceFor($filename, "{MM}", substr($report_date, 4, 2));
            $filename = replaceFor($filename, "{TAG}", $tag);
        }
    }

    return GetFilenameFromString($filename);
}
/**
 * GetReportFilename
 *
 * Given a Company Report Id, calculate what the A2P external user
 * filename should be.  This function makes sure the resulting filename
 * does not contain file system reserved characters that otherwise might
 * slip in with the custom user data like company name.
 *
 * @param $company_report_id
 * @return string
 */
function GetReportFilename( $company_report_id )
{
    $CI = &get_instance();

    $details = $CI->Reporting_model->select_report_filename_details($company_report_id);
    if ( empty($details) ) return '';

    $company_id = GetArrayStringValue("CompanyId", $details);
    $company_name = GetArrayStringValue('CompanyName', $details);
    $report_carrier = GetArrayStringValue('Carrier', $details);
    $report_date = GetArrayStringValue('ReportDate', $details);
    $report_type = GetArrayStringValue('ReportTypeCode', $details);
    $carrier_id = GetArrayStringValue('CarrierId', $details);
    $import_date = GetArrayStringValue('ImportDate', $details);


    // DRAFT TAG
    // Add a draft tag to the filename if the import month for the report has not yet been finalized.
    $draft = "_DRAFT";
    $finalized = $CI->Reporting_model->has_company_import_been_finalized($company_id, $import_date);
    if ( $finalized ) $draft = "";


    // Calculate the SUFFIX
    $suffix = "pdf";
    if ( $report_type === REPORT_TYPE_COMMISSION_CODE ) $suffix = 'csv';
    if ( $report_type === REPORT_TYPE_DETAIL_CODE ) $suffix = "csv";
    if ( $report_type === REPORT_TYPE_PE_DETAIL_CODE ) $suffix = "csv";
    if ( $report_type === REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE ) $suffix = "txt";
    if ( $report_type === REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE ) $suffix = "txt";
    if ( $report_type === REPORT_TYPE_TRANSAMERICA_ACTUARIAL_CODE) $suffix = "txt";

    // Calculate the report type that we will show the user.
    $report_type_display = $report_type;
    if ( $report_type === REPORT_TYPE_PE_SUMMARY_CODE ) $report_type_display = replaceFor($report_type, "pe_", "") . '_premium_equivalent';
    if ( $report_type === REPORT_TYPE_PE_DETAIL_CODE ) $report_type_display = replaceFor($report_type, "pe_", "") . '_premium_equivalent';

    // Calculate the level
    $level = LevelTag();
    if ( $level === 'PROD' ) $level = "";
    if ( $level !== '' ) $level .= "_";



    // Format the report filename in A2P format.
    $filename = "{$level}A2P_{$company_name}_{$report_carrier}_{$report_date}_{$report_type_display}{$draft}.{$suffix}";

    // FEATURE: ( TRANSAMERICA_FILE_FORMAT )
    // If this report is for Transamerica, format it as the Transamerica file format.
    if ( $CI->Feature_model->is_feature_enabled($company_id, 'TRANSAMERICA_FILE_FORMAT') )
    {
        $carrier_code = getArrayStringValue("CarrierCode", $details);
        if ( $carrier_code === 'TRANSAMERICA' )
        {
            $tag = "";
            if ( $report_type === REPORT_TYPE_DETAIL_CODE ) $tag = "Detail";
            if ( $report_type === REPORT_TYPE_SUMMARY_CODE ) $tag = "Summary";
            if ( $report_type === REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE ) $tag = "Eligibility";
            if ( $report_type === REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE ) $tag = "Commissions";
            if ( $report_type === REPORT_TYPE_TRANSAMERICA_ACTUARIAL_CODE) $tag = "Actuarial";

            if ( $tag !== '' )
            {
                $draft = replaceFor($draft, "_", "-");
                $filename = "{$level}TA-{COMPANY_NAME}-{YYYY}-{MM}-A2P-{TAG}{TIMESTAMP}{DRAFT}.{$suffix}";
                $filename = replaceFor($filename, "{COMPANY_NAME}", getArrayStringValue("CompanyName", $details));
                $filename = replaceFor($filename, "{YYYY}", substr($report_date, 0, 4));
                $filename = replaceFor($filename, "{MM}", substr($report_date, 4, 2));
                $filename = replaceFor($filename, "{TAG}", $tag);
                $filename = replaceFor($filename, "{DRAFT}", $draft);
            }

            // Some of the Transamerica Report Filenames have a timestamp.  Add those now.
            if ( $report_type === REPORT_TYPE_TRANSAMERICA_ACTUARIAL_CODE )
            {
                S3GetClient();
                $prefix = GetConfigValue("reporting_prefix");
                $prefix = replaceFor($prefix, "COMPANYID", getStringValue($company_id));
                $prefix = replaceFor($prefix, "TYPE", $report_type);
                $prefix = replaceFor($prefix, "DATE", $report_date);
                $obj = S3ListFile(S3_BUCKET, $prefix, "{$carrier_id}.txt");
                $last_modified = $obj['LastModified']->format('YmdHis');

                // Attempt to read the 'encrypted_on' comment in the file.  If we can find it, use
                // that for the timestamp in this filename.
                $encrypted_on = A2PGetEncryptedFileComment( $prefix, "{$carrier_id}.txt", 'encrypted_on' );
                if ( $encrypted_on !== FALSE )
                {
                    $last_modified = date('YmdHis', strtotime($encrypted_on));
                }

                $filename = replaceFor($filename, "{TIMESTAMP}", "-" . $last_modified);
            }
            else
            {
                $filename = replaceFor($filename, "{TIMESTAMP}", "");
            }
        }
    }



    return GetFilenameFromString($filename);
}
function ArchiveReportReviewWarnings( $company_id, $user_id ) {

    // ArchiveReportReviewWarnings
    //
    // This function will collect all of the data we showed the customer
    // during the report review phase.  Capturing this so it is easy to
    // show/research warnings that were ignored without going to the DB.
    // ---------------------------------------------------------------------

    $CI = &get_instance();

    // Organize our Snapshot Data
    $data = $CI->Archive_model->select_report_review_warnings_for_archive($company_id);
    ArchiveHistoricalData($company_id, 'company', "report_review_warnings", $data, array(), $user_id, 1);


}
function GetRecentMon( $company_id ) {
    // GetRecentMon
    //
    // This function will return the Month, in human readable short form.  For
    // example "Aug" for August.  This is the month most recently finalized
    // date for the given customer.
    // ------------------------------------------------------------------

    $CI = &get_instance();
    $data = $CI->Reporting_model->select_recent_date($company_id);
    return getArrayStringValue("RecentMon", $data);
}
function GetRecentMonth( $company_id ) {

    // GetRecentMonth
    //
    // This function will return the Month, in human readable form.  For
    // example "August".  This is the month most recently finalized
    // for the given customer.
    // ------------------------------------------------------------------

    $CI = &get_instance();
    $data = $CI->Reporting_model->select_recent_date($company_id);
    return getArrayStringValue("RecentMonth", $data);
}
function GetRecentDate( $company_id ) {

    // GetRecentDate
    //
    // This function will return the date, in MM/DD/CCYY format.  For
    // example "10/01/2016".  This is the month most recently finalized
    // for the given customer.
    // ------------------------------------------------------------------

    $CI = &get_instance();
    $data = $CI->Reporting_model->select_recent_date($company_id);
    return getArrayStringValue("RecentMMDDYYYY", $data);
}
function GetRecentDateDescription( $company_id ) {

    // GetRecentDateDescription
    //
    // This function will retun the date, in Month CCYY format.  For
    // example "May 2016".  This is the month most recently finalized
    // for the given customer.
    // ------------------------------------------------------------------

    $CI = &get_instance();
    $data = $CI->Reporting_model->select_recent_date($company_id);
    return getArrayStringValue("RecentMonthYYYY", $data);
}
function GetImportDateDescription( $company_id ) {

    $CI = &get_instance();
    $data = $CI->Reporting_model->select_Import_date($company_id);
    return getArrayStringValue("ImportMonthYYYY", $data);
}
function FormatDateMMDDYYYY( $date )
{
    $CI = &get_instance();
    $data = $CI->Reporting_model->format_date($date);
    return getArrayStringValue("MMDDYYYY", $data);
}
function FormatDateMonthYYYY( $date )
{

    $CI = &get_instance();
    $data = $CI->Reporting_model->format_date($date);
    return getArrayStringValue("MonthYYYY", $data);
}
function FormatDateMonYYYY( $date )
{
    $CI = &get_instance();
    $data = $CI->Reporting_model->format_date($date);
    return getArrayStringValue("MonYYYY", $data);
}
function FormatDateYYYYMM( $date )
{
    $CI = &get_instance();
    $data = $CI->Reporting_model->format_date($date);
    return getArrayStringValue("YYYYmm", $data);
}
function FormatDateMonth( $date )
{

    $CI = &get_instance();
    $data = $CI->Reporting_model->format_date($date);
    return getArrayStringValue("Month", $data);
}
function FormatDateMon( $date )
{

    $CI = &get_instance();
    $data = $CI->Reporting_model->format_date($date);
    return getArrayStringValue("Mon", $data);
}

function MaskCustomerData($item, $header_label="") {
    // Mask Customer Data
    //
    // This function identfies the input data as something we want to
    // obfuscate.  If identified, the string will be replaced with different
    // text.  If it is not identified, then the original input is returned.
    // ---------------------------------------------------------------------

    // SSN - data
    if( preg_match("/^[0-9]{3}-[0-9]{2}-[0-9]{4}$/", $item) ) {
        $last = substr($item, -4);
        return "###-##-{$last}";
    }

    // SSN - header
    // If the data did not look like an SSN, but the header makes me think
    // it could be an SSN ... mask it.
    if ( strpos(strtoupper($header_label), "SSN") !== FALSE )
    {
        $last = substr($item, -4);
        if ( strlen($last) < 4 )
        {
            $last = str_pad($last, 4, '0', STR_PAD_LEFT);
            $last = substr($last, -4);
        }
        return "###-##-{$last}";
    }

    return $item;

}
function GetPlanTypeDescription($item){
    $plantype = getArrayStringValue("PlanTypeDescription", $item);
    if ($plantype == "" )
    {
        $plan = getArrayStringValue("PlanDescription", $item);
        $coveragetier = getArrayStringValue("CoverageTierDescription", $item);
        if ( $plan == "" && $coveragetier == "" )
        {
            return "Manual Adjustments";
        }
    }
    return $plantype;
}
function GetDraftReportId($company_id, $carrier_id, $report_type_id ) {
    $CI = &get_instance();
    $CI->load->model('Reporting_model', 'reporting_model');
    return $CI->reporting_model->select_draft_company_report_id($company_id, $carrier_id, $report_type_id);
}
function GetUploadDateFolderName( $company_id, $import_date=null ) {
    if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
    $month = fLeft($import_date, "/");
    $day = fBetween($import_date, "/", "/");
    $year = fRightBack($import_date, "/");
    return "{$year}{$month}";
}
function GetReportMoneyValue( $value ) {
    $value = GetMoneyValue($value);
    if ( $value == "$0.00" ) return "-";
    if ( strpos($value, "-") !== FALSE )
    {
        return "( ".replaceFor($value, "-", "")." )";
    }
    return $value;
}
function GetPreparedDate($company_id) {
    $CI = &get_instance();
    $CI->load->model('Reporting_model', 'reporting_model');
    $CI->load->model('Wizard_model', 'wizard_model');
    $data = $CI->reporting_model->select_summary_data_prepared_date($company_id);
    return GetArrayStringValue("PreparedDate", $data);
}
function GetSummaryReportAttributeDescription( $details ) {

    $ageband_start  = getArrayStringValue("AgeBandStart", $details);
    $ageband_end    = getArrayStringValue("AgeBandEnd", $details);
    $tobacco_user   = getArrayStringValue("TobaccoUser", $details);

    if ($ageband_start == "0" ) $ageband_start = "B";
    if ($ageband_end == "1000" ) $ageband_end = "D";

    $output = "";
    if ( $tobacco_user != "" && $tobacco_user == "f" ) $output .= "NT&nbsp;&nbsp;";
    if ( $tobacco_user != "" && $tobacco_user == "t" ) $output .= "T&nbsp;&nbsp;&nbsp;&nbsp;";
    if ( $ageband_start != "" && $ageband_end != "" ) $output .= "{$ageband_start}&nbsp;-&nbsp;{$ageband_end}";
    return $output;

}
function GetReportNumberValue( $input ) {
    if ( $input == "" ) return "0";
    if ( getIntValue($input) < 0 ) return "( ".abs(getIntValue($input))." )";
    return getStringValue($input);
}
function GetSummaryReportDescription($description, $previous_description) {

    // GetSummaryReportDescription
    //
    // This function will compare to strings.  If they are the same, the
    // empty string is returned else description.
    // --------------------------------------------------------------------
    $description = getStringValue($description);
    $previous_description = getStringValue($previous_description);
    if ( $description == $previous_description ) return "";
    return $description;
}
function InitSummaryReportTotalsArray() {
    $array = array();
    $array['lives']     = 0;
    $array['volume']    = 0;
    $array['premium']   = 0;
    $array['adjusted_lives']     = 0;
    $array['adjusted_volume']    = 0;
    $array['adjusted_premium']   = 0;
    $array['total_lives']     = 0;
    $array['total_volume']    = 0;
    $array['total_premium']   = 0;
    $array['count']           = 0;

    return $array;
}

/**
 * ReportContainsPII
 *
 * This function will tell you TRUE/FALSE if the report type code
 * passed in contains PII ( personally identifiable information )
 * or not.
 *
 * @param $report_type
 * @return bool
 */
function ReportContainsPII( $report_type )
{
    if ( $report_type === REPORT_TYPE_SUMMARY_CODE) return FALSE;
    if ( $report_type === REPORT_TYPE_PE_SUMMARY_CODE) return FALSE;
    if ( $report_type === REPORT_TYPE_ISSUES_CODE) return FALSE;
    return TRUE;
}

/* End of file report_helper.php */
/* Location: ./application/helpers/report_helper.php */
