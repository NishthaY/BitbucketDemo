<?php

class Tobacco_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    public function is_plantype_tobacco( $plantype_code ) {
        $file = "database/sql/tobacco/PlanTypeSELECT_IsTobacco.sql";
        $vars = array(
            getStringValue($plantype_code)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Unexpected situation.");
        $results = $results[0];
        return getArrayStringValue("is_tobacco", $results);
    }
    public function coverage_tier_tobacco_details( $company_id, $coveragetier_id ) {
        $file = "database/sql/tobacco/CoverageTierTobaccoSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($coveragetier_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();
        if ( count($results) == 1 ) return $results[0];
        throw new Exception("Unexpected results");
    }
    public function update_tobacco_attribute($company_id, $coveragetier_id, $value) {
        $file = "database/sql/tobacco/CompanyCoverageTierUPDATE_TobaccoIgnored.sql";
        $vars = array(
            getStringValue($value)
            , getIntValue($company_id)
            , getIntValue($coveragetier_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }



}


/* End of file Tobacco_model.php */
/* Location: ./system/application/models/Tobacco_model.php */
