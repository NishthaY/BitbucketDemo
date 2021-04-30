<?php

class Ageband_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    public function get_default_carrier_agebands($carrier_code, $band_code)
    {
        $file = "database/sql/ageband/AgeBandSELECT_DefaultCarrier.sql";
        $vars = array(
            GetStringValue($carrier_code),
            GetStringValue($band_code)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function get_default_agebands($band_code)
    {
        $file = "database/sql/ageband/AgeBandSELECT_Default.sql";
        $vars = array(
            GetStringValue($band_code)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function get_agetypes_dropdown ( ) {
        $file = "database/sql/agetype/AgeTypeSELECT_AgeTypeDropdown.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();

        $dropdown = array();
        foreach($results as $item) {
            $name = getArrayStringValue("Name", $item);
            $description = getArrayStringValue("Description", $item);
            $dropdown[$name] = $description;
        }
        return $dropdown;
    }
    public function get_best_guess_age_rules( $company_id, $carrier_id, $plantype_id, $plan_id ) {
        $file = "database/sql/ageband/CoverageTierAgeBandSELECT_BestGuess.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
            , getIntValue($plan_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) return array();
        $results = $results[0];

        $best_guess_id = getArrayStringValue("BestGuessId", $results);
        return $this->get_age_type_by_tier($best_guess_id);

    }
    public function get_age_type_by_tier( $tier_id ) {
        $file = "database/sql/agetype/AgeTypeSELECT_byCompanyCoverageTier.sql";
        $vars = array(
            getIntValue($tier_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found multiple records when pulling the age type for a given tier.");
        $results = $results[0];
        return $results;
    }
    public function get_age_type_by_name( $age_type_name ) {
        $file = "database/sql/agetype/AgeTypeSELECT_byName.sql";
        $vars = array(
            getStringValue($age_type_name)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found multiple age type records with the same name.");
        $results = $results[0];
        return $results;
    }
    public function get_best_guess_age_bands( $company_id, $carrier_id, $plantype_id, $plan_id ) {
        $file = "database/sql/ageband/CoverageTierAgeBandSELECT_BestGuess.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
            , getIntValue($plan_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) return array();
        $results = $results[0];

        $best_guess_id = getArrayStringValue("BestGuessId", $results);
        return $this->get_age_bands($best_guess_id);

    }
    public function get_age_bands( $id ) {
        $file = "database/sql/ageband/AgeBandSELECT.sql";
        $vars = array(
            getIntValue($id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function delete_age_bands( $id ) {
        $file = "database/sql/ageband/AgeBandDELETE.sql";
        $vars = array(
            getIntValue($id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function delete_all_bands_by_company( $company_id ) {
        $file = "database/sql/ageband/AgeBandDELETE_ByCompanyId.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function insert_age_band( $id, $start, $end, $age_type_id, $anniversary_month, $anniversary_day ) {
        $file = "database/sql/ageband/AgeBandINSERT.sql";
        $vars = array(
            getIntValue($id)
            , getIntValue($start)
            , getIntValue($end)
            , ( getStringValue($age_type_id) == "" ) ? null : getIntValue($age_type_id)
            , ( getStringValue($anniversary_month) == "" ) ? null : getIntValue($anniversary_month)
            , ( getStringValue($anniversary_day) == "" ) ? null : getIntValue($anniversary_day)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function set_coverage_tier_ageband_ignored( $company_id, $coveragetier_id, $value ) {
        $file = "database/sql/ageband/CoverageTierAgeBandUPDATE_Ignored.sql";
        $vars = array(
            getStringValue($value)
            , getIntValue($company_id)
            , getIntValue($coveragetier_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function is_plantype_bandable( $plantype_code ) {
        $file = "database/sql/ageband/PlanTypeSELECT_IsBandable.sql";
        $vars = array(
            getStringValue($plantype_code)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Unexpected situation.");
        $results = $results[0];
        return getArrayStringValue("is_bandable", $results);
    }
    public function coverage_tier_ageband_details( $company_id, $coveragetier_id ) {
        $file = "database/sql/ageband/CoverageTierAgeBandSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($coveragetier_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();
        if ( count($results) == 1 ) return $results[0];
        throw new Exception("Unexpected results");
    }
    public function count_agebands( $company_ageband_id ) {
        $file = "database/sql/ageband/AgeBandSELECT.sql";
        $vars = array(
            getIntValue($company_ageband_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        return count($results);
    }

}


/* End of file Ageband_model.php */
/* Location: ./system/application/models/Ageband_model.php */
