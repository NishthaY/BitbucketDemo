<?php

class Carrier_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    function get_known_carriers()
    {
        $file = "database/sql/carrier/CarrierSELECT.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function is_known_carrier( $carrier )
    {
        $file = "database/sql/carrier/CarrierSELECT_ByCode.sql";
        $vars = array(
            getStringValue($carrier)
        );
        return GetDBExists( $this->db, $file, $vars );
    }
    function get_carrier_code_by_user_description($user_description)
    {
        if ( GetStringValue($user_description) === '' ) return "";

        $file = "database/sql/carrier/CarrierMappingSELECT_ByUserDescription.sql";
        $vars = array(
            GetStringValue($user_description)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return "";
        if ( count($results) !== 1 ) return "";
        return GetArrayStringValue("CarrierCode", $results[0]);
    }
    function get_carrier_description_by_carrier_code($carrier_code)
    {
        if ( GetStringValue($carrier_code) === '' ) return "";

        $file = "database/sql/carrier/CarrierSELECT_ByCode.sql";
        $vars = array(
            GetStringValue($carrier_code)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return "";
        if ( count($results) !== 1 ) return "";
        return GetArrayStringValue("UserDescription", $results[0]);
    }


}


/* End of file Carrier_model.php */
/* Location: ./system/application/models/Carrier_model.php */
