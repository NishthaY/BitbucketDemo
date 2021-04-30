<?php

class History_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    function insert_history_changeto_parent( $companyparent_id )
    {
        $user_id = GetSessionValue("user_id");
        if ( getStringValue($user_id) == "" ) return;

        // First we must delete the previous history record, if there was one.
        $this->delete_history_changeto_parent($companyparent_id);

        // Second, add the new history record for this user and company.
        $file = "database/sql/history/HistoryChangeToCompanyParentINSERT.sql";
        $vars = array(
            getIntValue($user_id),
            getIntValue( $companyparent_id )
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Last, delete any old records.
        $this->delete_history_changeto_parent_old();

    }
    function delete_history_changeto_parent( $companyparent_id )
    {
        $user_id = GetSessionValue("user_id");
        if ( getStringValue($user_id) == "" ) return;

        $file = "database/sql/history/HistoryChangeToCompanyParentDELETE.sql";
        $vars = array(
            getIntValue($user_id),
            getIntValue( $companyparent_id )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function delete_history_changeto_parent_old( $keep=5 ) {

        $user_id = GetSessionValue("user_id");
        if ( getStringValue($user_id) == "" ) return;

        $file = "database/sql/history/HistoryChangeToDELETE_OldData.sql";
        $vars = array(
            getIntValue($user_id)
        , getIntValue($keep)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }



    function insert_history_failedjob( $job_id ) {

        $user_id = GetSessionValue("user_id");
        if ( getStringValue($user_id) == "" ) return;

        // First we must delete the previous record, if there was one.
        $this->delete_history_failedjob( $job_id );

        // Second, add the new history record for this user and job.
        $file = "database/sql/history/HistoryFailedJobINSERT.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue( $job_id )
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function delete_history_failedjob( $job_id ) {

        $user_id = GetSessionValue("user_id");
        if ( getStringValue($user_id) == "" ) return;

        $file = "database/sql/history/HistoryFailedJobDELETE.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue( $job_id )
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function insert_history_changeto( $company_id ) {

        $user_id = GetSessionValue("user_id");
        if ( getStringValue($user_id) == "" ) return;

        // First we must delete the previous history record, if there was one.
        $this->delete_history_changeto($company_id);

        // Second, add the new history record for this user and company.
        $file = "database/sql/history/HistoryChangeToINSERT.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue( $company_id )
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Last, delete any old records.
        $this->delete_history_changeto_old();

    }
    function delete_history_changeto_old( $keep=5 ) {

        $user_id = GetSessionValue("user_id");
        if ( getStringValue($user_id) == "" ) return;

        $file = "database/sql/history/HistoryChangeToDELETE_OldData.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue($keep)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function delete_history_changeto( $company_id ) {

        $user_id = GetSessionValue("user_id");
        if ( getStringValue($user_id) == "" ) return;

        $file = "database/sql/history/HistoryChangeToDELETE.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue( $company_id )
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function select_draft_reports( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/history/CompanyReportSELECT_HistoryDraftGrouped.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $draft = GetDBResults( $this->db, $file, $vars );
        if ( empty($draft) ) $draft = array();
        return $draft;

    }
    function select_report_history( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return array();

        $file = "database/sql/history/CompanyReportSELECT_HistoryGrouped.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $finalized = GetDBResults( $this->db, $file, $vars );
        if ( empty($finalized) ) $finalized = array();
        return $finalized;

    }
    
}


/* End of file Adjustment_model.php */
/* Location: ./system/application/models/Adjustment_model.php */
