<?php
function ReportingReviewData($company_id = null) {
    if ( ! IsLoggedIn() ) return array();
    if ( GetStringValue($company_id) == '' ) $company_id = GetSessionValue("company_id");

    $CI = &get_instance();
    $CI->load->model('Reporting_model','reporting_model',true);
    $data = $CI->reporting_model->select_summary_data_report_review($company_id);

    setlocale(LC_MONETARY, 'en_US.UTF-8');
    foreach ( $data as &$item )
    {
        $carrier_id = GetArrayStringValue("CarrierId", $item);
        $carrier    = GetArrayStringValue("Carrier", $item);

        // Show the default A2P summary and detail reports.
        $summary_report_type_id = 1;
        $detail_report_type_id = 2;

        // This particular carrier is a PremiumEquivalent carrier, so show the
        // premium equivalent reports instead.
        if ( getArrayStringValue("PremiumEquivalent", $item) == "t" ) {
            $summary_report_type_id = 3;
            $detail_report_type_id = 4;
        }

        $report_id = GetDraftReportId($company_id, $carrier_id, $detail_report_type_id);

        $item["Carrier"] = GetArrayStringValue("Carrier", $item);
        $item["CarrierId"] = $carrier_id;
        $item["Total"] = getMoneyValue(GetArrayFloatValue("Total", $item));
        $item["Adjustments"] = getMoneyValue(GetArrayFloatValue("Adjustments", $item));
        $item["BalanceDue"] = getMoneyValue(GetArrayFloatValue("BalanceDue", $item));
        $item['SummaryLink'] = base_url("report/summary/{$company_id}/{$carrier_id}/{$summary_report_type_id}");


    }
    return $data;

}
function ReportingReviewWarningData( ) {
    if ( ! IsLoggedIn() ) return array();
    $company_id = GetSessionValue("company_id");

    $CI = &get_instance();
    $CI->load->model('Reporting_model','reporting_model',true);

    // Ensure we have the encryption key in the cache
    $CI->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $encryption_key = $CI->cache->get("crypto_{$company_id}");
    if ( GetStringValue($encryption_key) === 'FALSE' )
    {
        $encryption_key = GetCompanyEncryptionKey($company_id);
        $CI->cache->save("crypto_{$company_id}", $encryption_key, 300);
    }

    // Grab the report data.
    $data = $CI->reporting_model->select_report_review_warnings($company_id);
    $data = A2PDecryptArray($data, $encryption_key);

    // Examine the first EmployeeId in the results.  If it starts with the
    // Universal Employee Id tag, then we need to pull the data again, but this
    // time do not pull the EmployeeId data.
    if ( count($data) > 0 )
    {
        $row1 = $data[0];
        $eid = GetArrayStringValue("Employee Id", $row1);
        if ( StartsWith($eid, EUID_TAG) )
        {
            $data = $CI->reporting_model->select_report_review_warnings_no_employee_id($company_id);
            $data = A2PDecryptArray($data, $encryption_key);
        }
    }

    return $data;

}
function ReportingReviewWarningCounts($company_id)
{
    if ( ! IsLoggedIn() ) return array();

    $CI = &get_instance();
    $CI->load->model('Reporting_model','reporting_model',true);

    // Grab the report data.
    $data = $CI->reporting_model->select_report_review_counts($company_id);

    // No Results
    if ( empty($data) )
    {
        $data = array();
        $data['CompanyId'] = $company_id;
        $data['Critical'] = 0;
        $data['Warnings'] = 0;
    }

    return $data;
}

function ReportingReviewWarningDataConfirmation( ) {
    if ( ! IsLoggedIn() ) return array();
    $company_id = GetSessionValue("company_id");

    $CI = &get_instance();
    $CI->load->model('Reporting_model','reporting_model',true);

    // Ensure we have the encryption key in the cache
    $CI->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $encryption_key = $CI->cache->get("crypto_{$company_id}");
    if ( GetStringValue($encryption_key) === 'FALSE' )
    {
        $encryption_key = GetCompanyEncryptionKey($company_id);
        $CI->cache->save("crypto_{$company_id}", $encryption_key, 300);
    }

    // Grab the report data.
    $data = $CI->reporting_model->select_report_review_warnings($company_id);
    $data = A2PDecryptArray($data, $encryption_key);

    // Examine the first EmployeeId in the results.  If it starts with the
    // Universal Employee Id tag, then we need to pull the data again, but this
    // time do not pull the EmployeeId data.
    if ( count($data) > 0 )
    {
        $row1 = $data[0];
        $eid = GetArrayStringValue("Employee Id", $row1);
        if ( StartsWith($eid, EUID_TAG) )
        {
            $data = $CI->reporting_model->select_report_review_warnings_no_employee_id($company_id);
            $data = A2PDecryptArray($data, $encryption_key);
        }
    }

    return $data;

}

/**
 * IsSkipMonthProcessingAllowed
 *
 * This function will return true if the entity in question qualifies for
 * the skip month processing feature.  If it does not qualify, then a
 * reason string is returned.
 *
 * @return bool
 */
function IsSkipMonthProcessingAllowed($identifier, $identifier_type)
{
    $CI = &get_instance();

    $company_id = null;
    $companyparent_id = null;
    if( $identifier_type === 'company' )
    {
        $company_id = $identifier;
        $companyparent_id = GetCompanyParentId($company_id);
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $company_id = null;
        $companyparent_id = $identifier;
    }

    // REQUIRED INPUTS
    // If we don't have a company, then skipping an import month is not allowed.
    if ( GetStringValue($company_id) === '' ) return FALSE;

    $import_date = GetUploadDate($company_id);
    $recent_month = GetRecentDate($company_id);
    $archive_datetag = date("Ym", strtotime("-1 months", strtotime($import_date)));


    // NOT PROCESSING
    // In the case of the parent dashboard, it's possible to attempt to kick off
    // skip month processing anytime.  If this company has started processing a
    // file already, don't allow skip month.
    if ( $CI->Wizard_model->has_wizard_started($company_id) ) return "Monthly processing already in progress.";

    // PREVIOUS IMPORT
    // Grab the most recent finalized month.  If there is none, then there is no
    // import that we could use as a basis for skipping.
    if ( $recent_month === '' ) return "No previous finalized import available for processing.";


    // ANNIVERSARY MONTH - Is this the Anniversary Month?
    // Scan all of the plantypes for this company.  If any have a Plan Anniversary set AND we are
    // trying to "skip" the plan anniversary months, stop them.
    $anniversary_months = $CI->Company_model->list_company_distinct_active_plan_anniversary_months($company_id);
    if ( ! empty($anniversary_months) )
    {
        $month = FLeft($import_date, '/');
        $month = GetStringValue(GetIntValue($month));

        if ( in_array($month, $anniversary_months )) return "One or more plan types will skip over their plan anniversary.";
    }

    // MAX SKIP WINDOW - How many months in a row have we skipped?
    // The smallest retro window over all active plan types is the max number of
    // times we will allow someone to skip a month of processing.
    $max = $CI->Company_model->get_max_skip_months($company_id);
    $skips = $CI->Company_model->skips_in_window($company_id, $import_date, $max);
    if ( $skips >= $max ) return "One or more plan types will exceed the number of skips allowed based on their retro window.";

    // MISSING FILE
    // Check AWS to make sure a file exists in the archive and
    // was not manually deleted.
    try
    {
        $archive_prefix = GetS3Prefix('archive', $identifier, $identifier_type);
        $archive_prefix = replaceFor($archive_prefix, "COMPANYID", $identifier);
        $archive_prefix = replaceFor($archive_prefix, "COMPANYPARENTID", $identifier);
        $archive_prefix = replaceFor($archive_prefix, "DATE", $archive_datetag);
        $archive_prefix .= "/upload";

        $files = S3ListFiles(S3_BUCKET, $archive_prefix);
        if ( count($files) == 0 ) throw new Exception("No archive file found when checking for skip month processing.");
        if ( count($files) > 1 ) throw new Exception("Found too many archive files when checking for skip month processing.");
        foreach($files as $file)
        {
            $filename = getArrayStringValue("Key", $file);
            $file = fRightBack($filename, "/");
        }
        if ( $file === '' ) throw new Exception("Unable to locate the archive filename.");
    }catch(Exception $e)
    {
        $payload = [];
        $payload['identifier'] = $identifier;
        $payload['identifier_type'] = $identifier_type;
        $payload['import_date'] = $import_date;
        $payload['recent_month'] = $recent_month;
        $payload['archive_datetag'] = $archive_datetag;
        LogIt("SkipMonthProcessing", $e->getMessage(), $payload);
        return "No historical archive available for processing.";
    }

    return true;
}
function HasExistingReportData() {

    if ( ! IsLoggedIn() ) return false;
    $company_id = GetSessionValue("company_id");

    $CI = &get_instance();
    return $CI->Reporting_model->does_company_have_finalized_data($company_id);

}
function DropdownMonths() {

    $months = array();
    $months["01"] = "January";
    $months["02"] = "February";
    $months["03"] = "March";
    $months["04"] = "April";
    $months["05"] = "May";
    $months["06"] = "June";
    $months["07"] = "July";
    $months["08"] = "August";
    $months["09"] = "September";
    $months["10"] = "October";
    $months["11"] = "November";
    $months["12"] = "December";
    return $months;
}
function DropdownDays() {
    $days = array();
    $days["01"] = "1st";
    $days["02"] = "2nd";
    $days["03"] = "3rd";
    $days["04"] = "4th";
    $days["05"] = "5th";
    $days["06"] = "6th";
    $days["07"] = "7th";
    $days["08"] = "8th";
    $days["09"] = "9th";
    $days["10"] = "10th";
    $days["11"] = "11th";
    $days["12"] = "12th";
    $days["13"] = "13th";
    $days["14"] = "14th";
    $days["15"] = "15th";
    $days["16"] = "16th";
    $days["17"] = "17th";
    $days["18"] = "18th";
    $days["19"] = "19th";
    $days["20"] = "20th";
    $days["21"] = "21st";
    $days["22"] = "20th";
    $days["23"] = "23th";
    $days["24"] = "24th";
    $days["25"] = "25th";
    $days["26"] = "26th";
    $days["27"] = "27th";
    $days["28"] = "28th";
    $days["29"] = "29th";
    $days["30"] = "30th";
    $days["31"] = "31st";
    return $days;
}
function GettingStartedYears()
{
    // How many years to we want in this dropdown?
    // There must always be three.
    $number = GetAppOption(GETTING_STARTED_YEARS);
    $number = GetIntValue($number);
    if ( $number < 3 ) $number = 3;

    // The last two are the current year and next year.
    // Reduce the number by two to account for those.
    $number = $number - 2;

    // What is the current date.
    $current = date("Y");

    // For X number of years in the past, add a year
    // until we are caught up to this year.
    for($i=$number;$i>0;$i--)
    {
        $years[$current-$i] = $current - $i;
    }

    // Add this year and next.
    $years[$current] = $current;
    $years[$current+1] = $current+1;

    return $years;
}

function unarchive($identifier, $identifier_type, $from_date, $to_date )
{
    $archive_datetag = date("Ym", strtotime("-1 months", $from_date));

    // Check to see if the file exists in the archive first.
    $archive_prefix = GetS3Prefix('archive', $identifier, $identifier_type);
    $archive_prefix = replaceFor($archive_prefix, "COMPANYID", $identifier);
    $archive_prefix = replaceFor($archive_prefix, "COMPANYPARENTID", $identifier);
    $archive_prefix = replaceFor($archive_prefix, "DATE", $archive_datetag);
    $archive_prefix .= "/upload";

    // Stream the file out as a zip file.
    $filename = "";
    $files = S3ListFiles(S3_BUCKET, $archive_prefix);
    if ( count($files) == 0 ) throw new Exception("File not found.");
    if ( count($files) > 1 ) throw new Exception("Found too many files.");
    foreach($files as $file)
    {
        $filename = getArrayStringValue("Key", $file);
        $file = fRightBack($filename, "/");
    }

    $upload_prefix = GetS3Prefix('upload', $identifier, $identifier_type);
    S3EncryptExistingFile( S3_BUCKET, $archive_prefix, $filename, $upload_prefix, $filename );

}
/* End of file dashboard_helper.php */
/* Location: ./application/helpers/dashboard_helper.php */
