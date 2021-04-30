<?php

class Commissions_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    function select_commission_plans_by_life($company_id, $life_id)
    {
        $file = "database/sql/commissions/CompanyCommissionSELECT_PlansByLife.sql";
        $vars = array(
            GetIntValue($company_id),
            GetIntValue($life_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }
    function select_recent_commission_plan_by_life($company_id, $life_id)
    {
        $file = "database/sql/commissions/CompanyCommissionSELECT_MostRecentPlanByLife.sql";
        $vars = array(
            GetIntValue($company_id),
            GetIntValue($life_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return "";
        if ( count($results) !== 1 ) return "";
        return GetArrayIntValue("RecentPlanId", $results[0]);
    }
    function select_commission_types( )
    {
        $file = "database/sql/commissions/CommissionTypeSELECT_All.sql";
        $vars = array( );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }
    function select_commission_type( $code )
    {
        $file = "database/sql/commissions/CommissionTypeSELECT_ByName.sql";
        $vars = array(
            GetStringValue($code)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }
}
/* End of file GenerateCommissions_model.php */
/* Location: ./system/application/models/GenerateCommissions_model.php */
