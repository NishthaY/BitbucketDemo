<?php

class SkipMonthProcessing_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    public function insert_record( $company_id, $import_date )
    {
        $file = "database/sql/skipmonthprocessing/SkipMonthProcessingINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function remove_record( $company_id, $import_date )
    {
        $file = "database/sql/skipmonthprocessing/SkipMonthProcessingDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

}


/* End of file SkipMonthProcessing_model.php */
/* Location: ./system/application/models/SkipMonthProcessing_model.php */
