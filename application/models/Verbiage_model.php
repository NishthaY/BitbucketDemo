<?php

class Verbiage_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    public function get( $group, $key ) {
        $file = "database/sql/verbiage/VerbiageSELECT_byGroupKey.sql";
        $vars = array(
            getStringValue($group),
            getStringValue($key)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return "";
        if ( count($results) > 1) throw new Exception("Found too many verbiage items for give key");
        $results = $results[0];
        return getArrayStringValue("Words", $results);
    }

}

/* End of file verbiage_model.php */
/* Location: ./system/application/models/verbiage_model.php */
