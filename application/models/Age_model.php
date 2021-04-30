<?php

class Age_model extends CI_Model{

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);

    }
    function update_leap_babies_age($company_id, $import_date)
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);

        $file = "database/sql/age/AgeUPDATE_AgeForLeapBabies.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_age_on_for_leap_babies($company_id, $import_date)
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        $current_year = date("Y");

        $file = "database/sql/age/AgeUPDATE_AgeOnForLeapBabies.sql";
        $vars = array(
            getStringValue($current_year)
            , getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function set_leap_baby_flg($company_id, $leap_date, $import_date=null)
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        $file = "database/sql/age/AgeUPDATE_SetLeapBabyFlg.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($leap_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function delete_age( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/age/AgeDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function insert_age( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/age/AgeINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            ,getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function update_age( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/age/AgeUPDATE_AgeAndAgeDescription.sql";
        $vars = array(
            getIntValue($company_id)
            ,getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function select_age_coverage_tiers( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/age/AgeSELECT_DistinctCoverageTiers.sql";
        $vars = array(
            getIntValue($company_id)
            ,getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function select_age_calculation_data_by_tier( $coveragetier_id )
    {
        $file = "database/sql/age/AgeSELECT_AgeCalculationDataByTier.sql";
        $vars = array(
            getIntValue($coveragetier_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) > 0 ) return $results[0];
        return array();
    }
    function update_age_calculation_data( $company_id, $tier, $calculation_type, $anniversary_day, $anniversary_month ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/age/AgeUPDATE_AgeCalculationData.sql";
        $vars = array(
            ( getStringValue($calculation_type) == "" ? 3 : getIntValue($calculation_type) )        
            , ( $anniversary_day == null ) ? null : getIntValue($anniversary_day)
            , ( $anniversary_month == null ) ? null : getIntValue($anniversary_month)
            ,getIntValue($company_id)
            ,getStringValue($import_date)
            ,getIntValue($tier)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_age_on( $company_id, $tier ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $time = strtotime($import_date);
        $report_year = date('Y', $time);
        $report_month = date('m', $time);

        $file = "database/sql/age/AgeUPDATE_AgeOn.sql";
        $vars = array(
            getIntValue($report_year)
            ,getIntValue($report_month)
            ,getIntValue($report_year)
            ,getIntValue($report_month)
            ,getIntValue($report_year)
            ,getIntValue($report_year)
            ,getIntValue($company_id)
            ,getStringValue($import_date)
            ,getIntValue($tier)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function update_issued_date( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/age/AgeUPDATE_IssuedDate.sql";
        $vars = array(
            getIntValue($company_id)
            ,getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

}

/* End of file Age_model.php */
/* Location: ./application/models/Age_model.php */
