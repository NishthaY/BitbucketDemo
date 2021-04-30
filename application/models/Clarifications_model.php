<?php

class Clarifications_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    function has_clarifications($company_id, $import_date=null) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventSELECT_HasClarifications.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1 ) throw new Exception(__FUNCTION__ . ": expected exactly one result, but got many.");
        $results = $results[0];
        if ( getArrayStringValue("HasClarifications", $results) == "t" ) return true;
        if ( getArrayStringValue("HasClarifications", $results) == "f" ) return false;
        return false;
    }
    function has_clarifications_yet_to_review($company_id, $import_date=null) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventSELECT_HasClarificationsYetToReview.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1 ) throw new Exception(__FUNCTION__ . ": expected exactly one result, but got many.");
        $results = $results[0];
        if ( getArrayStringValue("HasClarifications", $results) == "t" ) return true;
        if ( getArrayStringValue("HasClarifications", $results) == "f" ) return false;
        return false;
    }



}


/* End of file Clarifications_model.php */
/* Location: ./system/application/models/Clarifications_model.php */
