<?php

class GenerateDuplicateLivesReport_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    function insert_duplicate_lives($company_id, $import_date)
    {
        $file = "database/sql/duplicatelives/ImportDataDuplicateLivesINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }
    function check_for_duplicate_lives($company_id, $import_date)
    {
        $file = "database/sql/duplicatelives/ImportDataDuplicateLivesSELECT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        return GetDBExists($this->db, $file, $vars);
    }
    function delete_duplicate_lives($company_id, $import_date)
    {
        $file = "database/sql/duplicatelives/ImportDataDuplicateLivesDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        return ExecuteSQL($this->db, $file, $vars);
    }
    function create_report( $fh, $encryption_key, $company_id, $import_date)
    {
        $import_date = GetUploadDate($company_id);
        if ( $import_date === '' ) throw new Exception("Invalid import date.");

        $file = "database/sql/duplicatelives/ImportDataDuplicateLivesSELECT_Report.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        WriteDBSecureFile($fh, $this->db, $file, $vars, $company_id, $encryption_key);
    }
    function write_warning_message($company_id, $import_date, $link)
    {
        $replaceFor = array();
        $replaceFor['{DOWNLOAD_LINK}'] = $link;
        $file = "database/sql/duplicatelives/ReportReviewWarningsINSERT_DuplicateLives.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        return ExecuteSQL($this->db, $file, $vars, $replaceFor);
    }

}
/* End of file DuplicateLifes_model.php */
/* Location: ./system/application/models/DuplicateLifes_model.php */
