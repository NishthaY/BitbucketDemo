<?php

class Archive_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    function count_commission_validation_errors($company_id)
    {
        $file = 'database/sql/archive/CompanyCommissionValidationSELECT_CountByCompany.sql';
        $vars = array(
            GetIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return 0;
        $results = $results[0];
        return GetArrayIntValue("total", $results);
    }
    function select_life_summary($company_id)
    {
        $file = "database/sql/archive/LifeSummarySELECT.sql";
        $vars = array(
            GetIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return 0;
        $results = $results[0];
        return GetArrayIntValue("total", $results);
    }
    function archive_email_transaction($companyparent_id, $company_id, $user_id, $to, $to_address, $from, $from_address, $subject, $body)
    {
        $file = "database/sql/archive/HistoryEmailINSERT.sql";
        $vars = array
        (
            GetStringValue($companyparent_id) === '' ? null : GetIntValue($companyparent_id),
            GetStringValue($company_id) === '' ? null : GetIntValue($company_id),
            GetStringValue($user_id) === '' ? null : GetIntValue($user_id),
            GetStringValue($to) === '' ? null : GetStringValue($to),
            GetStringValue($to_address) === '' ? null : GetStringValue($to_address),
            GetStringValue($from) === '' ? null : GetStringValue($from),
            GetStringValue($from_address) === '' ? null : GetStringValue($from_address),
            GetStringValue($subject) === '' ? null : GetStringValue($subject),
            GetStringValue($body) === '' ? null : GetStringValue($body),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function get_archive_email_transaction_id($to_address)
    {
        $file = "database/sql/archive/HistoryEmailSELECT_MostRecentByEmail.sql";
        $vars = array(
            GetStringValue($to_address)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) throw new Exception("Expected exactly one result.");
        $results = $results[0];
        return GetArrayStringValue("Id", $results);

    }

    function select_in_process_items() {

        $file = "database/sql/archive/WizardSELECT_InProcess.sql";
        $vars = array();
        $draft = GetDBResults( $this->db, $file, $vars );
        if ( empty($draft) ) return array();
        return $draft;

    }

    function select_draft_reports( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/archive/CompanyReportSELECT_ArchiveDraftGrouped.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $draft = GetDBResults( $this->db, $file, $vars );
        if ( empty($draft) ) return array();
        return $draft;

    }
    function select_report_history( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/archive/CompanyReportSELECT_ArchiveGrouped.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $finalized = GetDBResults( $this->db, $file, $vars );
        if ( empty($finalized) ) return array();
        return $finalized;

    }
    function select_column_mappings_for_archive($identifier, $identifier_type) {

        if ( $identifier_type === 'company') $file = "database/sql/archive/CompanyPreferenceSELECT_ColumnMappings.sql";
        if ( $identifier_type === 'companyparent') $file = "database/sql/archive/CompanyParentPreferenceSELECT_ColumnMappings.sql";
        $vars = array(
            getIntValue($identifier)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;

    }
    function select_plan_settings_for_archive($company_id) {
        $file = "database/sql/archive/ImportDataSELECT_PlanSettings.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function select_age_calculation_for_archive( $coveragetierid ) {
        $file = "database/sql/archive/AgeBandSELECT_AgeCalculation.sql";
        $vars = array(
            getIntValue($coveragetierid)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results[0];
    }
    function select_age_bands_for_archive( $coveragetierid ) {
        $file = "database/sql/archive/AgeBandSELECT_AgeBands.sql";
        $vars = array(
            getIntValue($coveragetierid)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function select_manual_adjustments_for_archive( $company_id ) {
        $import_date = GetUploadDate($company_id);
        $file = "database/sql/archive/ManualAdjustmentSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function select_life_compare_for_archive( $company_id ) {
        $import_date = GetUploadDate($company_id);
        $file = "database/sql/archive/LifeCompareSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function select_report_review_warnings_for_archive( $company_id) {
        $import_date = GetUploadDate($company_id);
        $file = "database/sql/archive/ReportReviewWarningsSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function select_audit_report( $company_id, $tag ) {
        $file = "database/sql/archive/AuditSELECT_Company-{$tag}.sql";
        if ( file_exists($file) )
        {
            $vars = array(
                getConfigValue("timezone_display")
                ,getIntValue($company_id)
            );
            $results = GetDBResults( $this->db, $file, $vars );
            if ( empty($results) ) return array();
            return $results;
        }
        return array();
    }
    function select_parent_audit_report( $company_parent_id, $tag ) {
        $file = "database/sql/archive/AuditSELECT_Parent-{$tag}.sql";
        if ( file_exists($file) )
        {
            $vars = array(
                getConfigValue("timezone_display")
                ,getIntValue($company_parent_id)
            );
            $results = GetDBResults( $this->db, $file, $vars );
            if ( empty($results) ) return array();
            return $results;
        }
        return array();
    }
    function select_recent_company_changes( $company_id ) {
        $file = "database/sql/archive/AuditSELECT_RecentCompanyChanges.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function select_recent_parent_changes( $company_parent_id ) {
        $file = "database/sql/archive/AuditSELECT_RecentParentChanges.sql";
        $vars = array(
            getIntValue($company_parent_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function select_recent_company_snapshots( $company_id ) {
        $file = "database/sql/archive/CompanyReportsSELECT_RecentCompanySnapshots.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function select_recent_tickets( $identifier, $identifier_type )
    {
        $retval = array();

        $count = 3;
        $tickets = $this->select_tickets( $identifier, $identifier_type );
        foreach($tickets as $ticket)
        {
            $row = array();
            $row['description'] = GetArrayStringValue("description", $ticket);

            $timestamp = strtotime(GetArrayStringValue("description", $ticket));
            $date = date('Y-m-d', $timestamp);
            $datetime1 = date_create($date);
            $datetime2 = date_create();
            $interval = date_diff($datetime1, $datetime2);
            $age =  $interval->format('%R%a days');


            $row['age'] = $age;


            $retval[] = $row;
            $count--;
            if ( $count == 0 ) break;
        }

        return $retval;
    }
    function select_tickets( $identifier, $identifier_type )
    {
        // Return this.
        $results = array();

        // Call AWS and formulate our results based on the top level
        // folders we find in the support folder.
        $support_prefix = GetS3Prefix('support', $identifier, $identifier_type);
        $support_prefix = replaceFor($support_prefix, "COMPANYID", $identifier);
        $support_prefix = replaceFor($support_prefix, "COMPANYPARENTID", $identifier);
        $support_prefix = fLeftBack($support_prefix, "/");

        // Get the directories.
        $data = S3ListDirectories(S3_BUCKET, $support_prefix);
        foreach($data as $folder)
        {
            $key = GetArrayStringValue("Key", $folder);
            $date_tag = fRightBack(fLeftBack($key, "/"), "/");
            $description = date('Y-m-d H:i:s',strtotime($date_tag));

            // If the directory is not numeric, then it's not something we want to show.
            if ( StripNonNumeric($date_tag) === $date_tag )
            {
                // Only add date_tags that are 100% numeric.
                $row = array();
                $row['description'] = $description;
                $row['date_tag'] = $date_tag;
                $row['identifier'] = $identifier;
                $row['identifier_type'] = $identifier_type;
                $results[] = $row;
            }


        }

        // Sort our results by date_tag desc.
        usort($results, function ($item1, $item2) {
            if ($item1['date_tag'] == $item2['date_tag']) return 0;
            return $item1['date_tag'] > $item2['date_tag'] ? -1 : 1;
        });

        return $results;
    }

    function select_recent_snapshots( $identifier, $identifier_type, $quantity=3 )
    {
        $all = $this->select_snapshots($identifier, $identifier_type);

        $recent = array();
        for($i=0;$i<$quantity;$i++)
        {
            if ( $i >= count($all) ) break;
            $row = array();

            // when was this snapshot?
            $timestamp = strtotime(GetArrayStringValue("description", $all[$i]));
            $date = date('M jS, Y g:i A', $timestamp);
            $row['time'] = $date;

            $recent[] = $row;
        }
        return $recent;
    }
    function select_snapshots( $identifier, $identifier_type )
    {

        // Call AWS and formulate our results based on the top level
        // folders we find in the support folder.
        $prefix = GetS3Prefix('archive', $identifier, $identifier_type);
        $prefix = replaceFor($prefix, "COMPANYID", $identifier);
        $prefix = replaceFor($prefix, "COMPANYPARENTID", $identifier);
        $prefix = fLeftBack($prefix, "/");

        $lookup = array();
        $objects = S3ListFiles(S3_BUCKET, $prefix);
        foreach($objects as $obj)
        {
            $key = GetArrayStringValue('Key', $obj);
            $key = replaceFor($key, $prefix, "");
            $key = fRight($key, "/", $key);
            $dir = fLeft($key, "/", $key);
            $lookup[$dir] = true;
        }
        $dirs = array_keys($lookup);

        $results = array();
        foreach($dirs as $date_tag)
        {
            if ( strlen($date_tag) === 6 )
            {
                $year = substr($date_tag, 0,4);
                $month = substr($date_tag, 4, 2);
                $description = date('F Y',strtotime("{$month}/01/{$year}"));
            }
            else
            {
                $description = date('Y-m-d H:i:s',strtotime($date_tag));
            }

            // If the directory is not numeric, then it's not something we want to show.
            if ( StripNonNumeric(GetStringValue($date_tag)) === GetStringValue($date_tag) )
            {
                // Only add date_tags that are 100% numeric.
                $row = array();
                $row['description'] = $description;
                $row['date_tag'] = $date_tag;
                $row['identifier'] = $identifier;
                $row['identifier_type'] = $identifier_type;
                $results[] = $row;
            }

        }

        // Sort our results by date_tag desc.
        usort($results, function ($item1, $item2) {
            if ($item1['date_tag'] == $item2['date_tag']) return 0;
            return $item1['date_tag'] > $item2['date_tag'] ? -1 : 1;
        });

        return $results;
    }
    function select_recent_exports( $identifier, $identifier_type, $quantity=3 )
    {
        $all = $this->Export_model->select_all_exports($identifier, $identifier_type);

        $recent = array();
        for($i=0;$i<$quantity;$i++)
        {
            if ( $i >= count($all) ) break;
            $row = array();

            // when was this snapshot?
            $timestamp = strtotime(GetArrayStringValue("Modified", $all[$i]));
            $date = date('M jS, Y g:i A', $timestamp);
            $row['time'] = $date;

            $recent[] = $row;
        }
        return $recent;
    }
}


/* End of file Adjustment_model.php */
/* Location: ./system/application/models/Adjustment_model.php */
