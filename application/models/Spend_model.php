<?php

class Spend_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    function insert_spend_data ( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/spend/SummaryDataYTDINSERT.sql";
        $vars = array(
            getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function delete_spend_data( $company_id, $import_date=null ) {

        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/spend/SummaryDataYTDDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }

    function select_spend_data_recent_reports_by_carrier( $company_id ) {

        $recent_date = GetRecentDate($company_id);
        if ( $recent_date == "" ) return;

        $file = "database/sql/spend/SpendDataSELECT_RecentReportsByCarrier.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($recent_date)
            , getIntValue($company_id)
            , getStringValue($recent_date)
        );

        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function select_spend_data_monthly_spend( $company_id ) {

        $recent_date = GetRecentDate($company_id);
        if ( $recent_date == "" ) return;

        $file = "database/sql/spend/SpendDataSELECT_MonthlySpend.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($recent_date)
        );

        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) != 1 ) return array();
        return $results[0];
    }
    function select_spend_data_monthly_spend_ytd( $company_id ) {

        $recent_date = GetRecentDate($company_id);
        if ( $recent_date == "" ) return;

        $file = "database/sql/spend/SpendDataSELECT_MonthlySpendYTD.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($recent_date)
        );

        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) != 1 ) return array();
        return $results[0];
    }
    function select_spend_data_wash_retro_ytd( $company_id ) {

        $recent_date = GetRecentDate($company_id);
        if ( $recent_date == "" ) return;

        $file = "database/sql/spend/SpendDataSELECT_WashRetroSpendYTD.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($recent_date)
        );

        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) != 1 ) return array();
        return $results[0];
    }
    function select_spend_data( $company_id ) {
        $recent_date = GetRecentDate($company_id);
        if ( $recent_date == "" ) return;

        $file = "database/sql/spend/SpendDataSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($recent_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) $results = array();
        return $results;
    }

}


/* End of file Spend_model.php */
/* Location: ./system/application/models/Spend_model.php */
