<?php

class Company_model extends CI_Model {

    function __construct()
    {
        parent::__construct();

        $this->db = $this->load->database('default', TRUE);

    }
    public function update_company_encryption_key( $company_id, $encryption_key )
    {
        $file = "database/sql/company/CompanyUPDATE_EncryptionKey.sql";
        $vars = array(
            GetStringValue($encryption_key),
            GetIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function select_company_encryption_key( $company_id )
    {
        $file = "database/sql/company/CompanySELECT_EncryptionKey.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return "";
        return GetArrayStringValue("CompanyEncryptionKey", $results[0]);
    }
    public function select_companies_by_assigned_user( $parentcompany_id, $user_id )
    {
        $file = "database/sql/company/CompanySELECT_ResponsibleFor.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue($parentcompany_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    public function count_companies() {
        $file = "database/sql/company/CompanyCOUNT.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 )
        {
            $results = $results[0];
            return getArrayStringValue("Count", $results);
        }
        throw new Exception("Unexpected situation.");
    }

    // COMPANY DATA ROLLBACK +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function delete_company_washed_data($company_id, $import_date) {

        if ( getStringValue($company_id) == "" ) return;
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/washeddata/WashedDataDELETE_byImportDate.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function delete_company_import_data($company_id, $import_date) {
        $file = "database/sql/importdata/ImportDataDELETE_byImportDate.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function most_recent_company_import_date($company_id) {
        $file = "database/sql/importdata/ImportDataSELECT_MostRecentUploadDate.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) == 1 ) return $results[0];
        throw new Exception("Unexpected situation.");
    }

    // COMPANY CARRIER  +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function get_company_carriers($company_id)
    {
        $file = "database/sql/company/CompanyCarrierSELECT_ByCompanyId.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function get_company_carrier( $company_id, $carrier_id ) {

        $file = "database/sql/company/CompanyCarrierSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($carrier_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plantype data");
        return $results[0];
    }
    public function get_company_carrier_by_name( $company_id, $carrier ) {

        $file = "database/sql/company/CompanyCarrierSELECT_ByName.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plantype data");
        return $results[0];
    }
    public function get_company_carrier_by_user_description( $company_id, $user_description ) {

        $file = "database/sql/company/CompanyCarrierSELECT_ByUserDescription.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($user_description)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company carrier data");
        return $results[0];
    }
    public function get_company_carrier_by_carrier_code( $company_id, $carrier_code ) {

        $file = "database/sql/company/CompanyCarrierSELECT_ByCarrierCode.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier_code)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company carrier data");
        return $results[0];
    }
    public function insert_company_carrier( $company_id, $carrier ) {
        $file = "database/sql/company/CompanyCarrierINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier)
            , getStringValue($carrier)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function insert_company_carrier_by_carrier_code( $company_id, $carrier_code ) {
        $file = "database/sql/company/CompanyCarrierINSERT_ByCode.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier_code)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update_company_carrier_code( $id, $carrier_code )
    {
        $carrier_code = trim(strtoupper($carrier_code));
        $file = "database/sql/company/CompanyCarrierUPDATE_CarrierCode.sql";
        $vars = array(
            getStringValue($carrier_code)
            , getIntValue($id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update_company_carrier_description( $id, $user_description )
    {
        $file = "database/sql/company/CompanyCarrierUPDATE.sql";
        $vars = array(
            getStringValue($user_description)
            , getStringValue($user_description)
            , getIntValue($id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function get_company_carrier_best_match_carrier_code($company_id)
    {
        $file = "database/sql/company/CompanyCarrierSELECT_CarrierCodeBestMatch.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function is_mapped_carrier( $company_id, $carrier )
    {
        $carrier = trim(strtoupper($carrier));
        $file = "database/sql/company/CompanyCarrierSELECT_ByName.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier)
        );
        return GetDBExists( $this->db, $file, $vars );
    }

    // COMPANY PLANTYPE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function get_company_plantype_data( $company_id, $carrier, $plantype) {
        $file = "database/sql/company/CompanyPlanTypeSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier)
            , getStringValue($plantype)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plantype data");
        return $results[0];
    }
    public function get_company_plantype_by_desc( $company_id, $carrier_id, $plantype ) {
        $file = "database/sql/company/CompanyPlanTypeSELECT_ByCarrierIdPlanTypeDesc.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($carrier_id)
            , getStringValue($plantype)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plantype data");
        return $results[0];
    }
    public function get_company_plantype_data_by_code( $company_id, $carrier, $plantypecode) {
        $file = "database/sql/company/CompanyPlanTypeSELECT_ByPlanTypeCode.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier)
            , getStringValue($plantypecode)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plantype data");
        return $results[0];
    }
    public function get_compmay_plantype_data_by_ids( $company_id, $carrier_id, $plantype_id ) {
        $file = "database/sql/company/CompanyPlanTypeSELECT_ByIds.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier_id)
            , getStringValue($plantype_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plantype data");
        return $results[0];
    }
    public function get_compmay_plantype_data_by_id( $company_id, $plantype_id ) {
        $file = "database/sql/company/CompanyPlanTypeSELECT_ById.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($plantype_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plantype data");
        return $results[0];
    }
    public function insert_company_plantype( $company_id, $carrier_id, $plantype, $plantype_code ) {
        $file = "database/sql/company/CompanyPlanTypeINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($carrier_id)
            , getStringValue($plantype)
            , getStringValue($plantype)
            , getStringValue($plantype_code)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function set_company_plantype_code( $company_id, $carrier_id, $plantype_normalized, $value ) {

        $file = "database/sql/company/CompanyPlanTypeUPDATE_PlanTypeCode.sql";
        $vars = array(
            getStringValue($value)
            , getIntValue($company_id)
            , getStringValue($carrier_id)
            , getStringValue($plantype_normalized)
        );
        ExecuteSQL( $this->db, $file, $vars );


    }
    public function set_company_plantype_retrorule( $company_id, $carrier_id, $plantype_normalized, $value ) {
        $file = "database/sql/company/CompanyPlanTypeUPDATE_RetroRule.sql";
        $vars = array(
            getStringValue($value)
            , getIntValue($company_id)
            , getStringValue($carrier_id)
            , getStringValue($plantype_normalized)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function set_company_plantype_washrule( $company_id, $carrier_id, $plantype_normalized, $value ) {
        $file = "database/sql/company/CompanyPlanTypeUPDATE_WashRule.sql";
        $vars = array(
            getStringValue($value)
            , getIntValue($company_id)
            , getStringValue($carrier_id)
            , getStringValue($plantype_normalized)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function set_company_plantype_ignored( $company_id, $carrier_id, $plantype_normalized, $value ) {
        $file = "database/sql/company/CompanyPlanTypeUPDATE_Ignored.sql";
        $vars = array(
            getStringValue($value)
            , getIntValue($company_id)
            , getStringValue($carrier_id)
            , getStringValue($plantype_normalized)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function set_company_plantype_plananniversarymonth( $company_id, $carrier_id, $plantype_normalized, $value ) {
        $file = "database/sql/company/CompanyPlanTypeUPDATE_PlanAnniversaryMonth.sql";
        $vars = array(
            ( getStringValue($value) == "" ? null : getIntValue($value) )
            , getIntValue($company_id)
            , getIntValue($carrier_id)
            , getStringValue($plantype_normalized)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

    /**
     * list_company_distinct_active_plan_anniversary_months
     *
     * Return any plan anniversary months that have been applied to active plantypes
     * for a given customer over all carriers.
     *
     * @param $company_id
     * @return array
     */
    public function list_company_distinct_active_plan_anniversary_months($company_id)
    {
        $file = "database/sql/company/CompanyPlanTypeSELECT_DistinctPlanAnniversaryMonths.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();

        $list = [];
        foreach($results as $item)
        {
            $list[] = GetArrayStringValue('PlanAnniversaryMonth', $item);
        }

        return $list;
    }

    /**
     * get_max_skip_months
     *
     * Based on all available active plan types for a given company, find the smallest
     * retro window.  This becomes the MAX number of months you can use the skip month
     * processing feature in a row before you are denied.
     *
     * @param $company_id
     * @return int
     */
    public function get_max_skip_months($company_id)
    {
        $file = "database/sql/company/CompanyPlanTypeSELECT_MaxSkipWindow.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return 0;

        $results = $results[0];
        $max = GetArrayIntValue('MaxSkipWindow', $results);

        return $max;
    }

    // COMPANY PLAN +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function get_company_plan_by_description( $company_id, $carrier_id, $plantype_id, $description ) {
        $file = "database/sql/company/CompanyPlanSELECT_ByDescription.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
            , getStringValue($description)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plan data");
        return $results[0];
    }
    public function get_company_plan_by_id ( $company_id, $plan_id ) {
        $file = "database/sql/company/CompanyPlanSELECT_ById.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($plan_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plan data");
        return $results[0];
    }
    public function get_company_plans( $company_id, $carrier_id, $plantype_id ) {
        $file = "database/sql/company/CompanyPlanSELECT_AllPlansForCarrierIdPlanTypeid.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function insert_company_plan( $company_id, $carrier_id, $plantype_id, $plan_description) {
        $file = "database/sql/company/CompanyPlanINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
            , getStringValue($plan_description)
            , getStringValue($plan_description)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }


    // COMPANY COVERAGE TIER +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    public function get_company_coveragetier_by_descriptions( $company_id, $carrier, $plantype, $plan, $coveragetier ) {
        $file = "database/sql/company/CompanyCoverageTierSELECT_ByDescriptions.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier)
            , getStringValue($plantype)
            , getStringValue($plan)
            , getStringValue($coveragetier)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company plan data");
        return $results[0];
    }
    public function get_company_coveragetier_by_description( $company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier ) {
        $file = "database/sql/company/CompanyCoverageTierSELECT_ByDescription.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($carrier_id)
            , getStringValue($plantype_id)
            , getStringValue($plan_id)
            , getStringValue($coveragetier)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results when accessing company coveragetier data");
        return $results[0];
    }
    public function get_company_coveragetier_by_plan_id( $company_id, $plan_id ) {
        $file = "database/sql/company/CompanyCoverageTierSELECT_ByPlanId.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($plan_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function get_company_coveragetier_by_plantype_id( $company_id, $plantype_id ) {
        $file = "database/sql/company/CompanyCoverageTierSELECT_ByPlanTypeId.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($plantype_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function insert_company_coverage_tier( $company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_desc, $ageband_enabled, $tobacco_enabled ){

        if (! is_bool($ageband_enabled) ) throw new Exception("Expect ageband_enabled to be boolean or null.");
        if ( $ageband_enabled ) $ageband_enabled = "t";
        if ( ! $ageband_enabled ) $ageband_enabled = "f";

        if ( ! is_bool($tobacco_enabled) ) throw new Exception("Expect tobacco_enabled to be boolean or null.");
        if ( $tobacco_enabled ) $tobacco_enabled = "t";
        if ( ! $tobacco_enabled ) $tobacco_enabled = "f";

        $file = "database/sql/company/CompanyCoverageTierINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
            , getIntValue($plan_id)
            , getStringValue($coveragetier_desc)
            , getStringValue($coveragetier_desc)
            , ( $ageband_enabled == null ? null : getStringValue($ageband_enabled) )
            , ( $tobacco_enabled == null ? null : getStringValue($tobacco_enabled) )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

    public function get_company_preferences ( $company_id, $group ) {
        $file = "database/sql/company/SelectCompanyPreferenceByGroup.sql";
        $vars = array(
            getIntValue($company_id)
            , ( $group == null ? null : getStringValue($group) )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        return $results;
    }
    public function get_company_preference( $company_id, $group, $group_code ) {
        $file = "database/sql/company/SelectCompanyPreferenceByGroupAndGroupCode.sql";
        $vars = array(
            getIntValue($company_id)
            , ( $group == null ? null : getStringValue($group) )
            , ( $group_code == null ? null : getStringValue($group_code) )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many company preferences.  Expected one or none.");
        return $results[0];
    }
    public function get_company_preferences_by_value( $company_id, $group, $value ) {
        $file = "database/sql/company/SelectCompanyPreferencesByValue.sql";
        $vars = array(
            getIntValue($company_id)
            , ( $group == null ? null : getStringValue($group) )
            , ( $value == null ? null : getStringValue($value) )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();
        return $results;
    }
    public function save_company_preference(  $company_id, $group, $group_code, $value  ) {
        $pref = $this->get_company_preference($company_id, $group, $group_code);
        if ( empty($pref) ) {
            $this->insert_company_preference($company_id, $group, $group_code, $value);
        }else{
            $this->update_company_preference($company_id, $group, $group_code, $value);
        }
    }
    public function insert_company_preference( $company_id, $group, $group_code, $value ) {
        $file = "database/sql/company/InsertCompanyPreference.sql";
        $vars = array(
            getIntValue($company_id)
            , ( $group == null ? null : getStringValue($group) )
            , ( $group_code == null ? null : getStringValue($group_code) )
            , ( $value == null ? null : getStringValue($value) )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update_company_preference( $company_id, $group, $group_code, $value ) {
        $file = "database/sql/company/UpdateCompanyPreference.sql";
        $vars = array(
            getStringValue($value)
            , getIntValue($company_id)
            , getStringValue($group)
            , getStringValue($group_code)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function remove_company_preference( $company_id, $group, $group_code ) {
        $file = "database/sql/company/RemoveCompanyPreference.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($group)
            , getStringValue($group_code)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function remove_company_preference_group_code( $company_id, $group, $value ) {
        $file = "database/sql/company/CompanyPreferenceDELETE_ByGroupCode.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($group)
            , getStringValue($value)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function remove_company_preference_group( $company_id, $group ) {
        $file = "database/sql/company/RemoveCompanyPreferenceGroup.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($group)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

    public function address_states() {
        $states = array();
        $states["AL"] = "Alabama";
        $states["AK"] = "Alaska";
        $states["AZ"] = "Arizona";
        $states["AR"] = "Arkansas";
        $states["CA"] = "California";
        $states["CO"] = "Colorado";
        $states["CT"] = "Connecticut";
        $states["DE"] = "Delaware";
        $states["FL"] = "Florida";
        $states["GA"] = "Georgia";
        $states["HI"] = "Hawaii";
        $states["ID"] = "Idaho";
        $states["IL"] = "Illinois";
        $states["IN"] = "Indiana";
        $states["IA"] = "Iowa";
        $states["KS"] = "Kansas";
        $states["KY"] = "Kentucky";
        $states["LA"] = "Louisiana";
        $states["ME"] = "Maine";
        $states["MD"] = "Maryland";
        $states["MA"] = "Massachusetts";
        $states["MI"] = "Michigan";
        $states["MN"] = "Minnesota";
        $states["MS"] = "Mississippi";
        $states["MO"] = "Missouri";
        $states["MT"] = "Montana";
        $states["NE"] = "Nebraska";
        $states["NV"] = "Nevada";
        $states["NH"] = "New Hampshire";
        $states["NJ"] = "New Jersey";
        $states["NM"] = "New Mexico";
        $states["NY"] = "New York";
        $states["NC"] = "North Carolina";
        $states["ND"] = "North Dakota";
        $states["OH"] = "Ohio";
        $states["OK"] = "Oklahoma";
        $states["OR"] = "Oregon";
        $states["PA"] = "Pennsylvania";
        $states["RI"] = "Rhode Island";
        $states["SC"] = "South Carolina";
        $states["SD"] = "South Dakota";
        $states["TN"] = "Tennessee";
        $states["TX"] = "Texas";
        $states["UT"] = "Utah";
        $states["VT"] = "Vermont";
        $states["VA"] = "Virginia";
        $states["WA"] = "Washington";
        $states["WV"] = "West Virginia";
        $states["WI"] = "Wisconsin";
        $states["WY"] = "Wyoming";
        return $states;
    }
    public function enable_company ( $company_id ) {
        $file = "database/sql/company/EnableCompany.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $company = $this->get_company($company_id);
        $payload = array();
        $payload = array_merge($payload, array('CompanyId'=>GetArrayStringValue('company_id', $company)));
        $payload = array_merge($payload, array('CompanyName' => GetArrayStringValue('company_name', $company)));
        AuditIt("Company enabled.", $payload);
    }
    public function disable_company ( $company_id ) {
        $file = "database/sql/company/DisableCompany.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $company = $this->get_company($company_id);
        $payload = array();
        $payload = array_merge($payload, array('CompanyId'=>GetArrayStringValue('company_id', $company)));
        $payload = array_merge($payload, array('CompanyName' => GetArrayStringValue('company_name', $company)));
        AuditIt("Company disabled.", $payload);
    }

    public function get_all_companies( ) {
        $file = "database/sql/company/CompanySELECT.sql";
        $vars = array( );
        $companies = GetDBResults( $this->db, $file, $vars );
        if ( count($companies) == 0) return array();

        $output = array();
        foreach($companies as $company)
        {
            $company = $this->_addIsChildPropertToCompany($company);
            $output[] = $company;
        }
        return $output;
    }
    public function select_recent_companies( ) {

        $user_id = GetSessionValue("user_id");

        $file = "database/sql/company/CompanySELECT_MostRecentFirst.sql";
        $vars = array(
            $user_id
        );
        $companies = GetDBResults( $this->db, $file, $vars );
        if ( count($companies) == 0) return array();

        $output = array();
        foreach($companies as $company)
        {
            $company = $this->_addIsChildPropertToCompany($company);
            $output[] = $company;
        }
        return $output;
    }

    public function get_company( $company_id ) {
        $file = "database/sql/company/SelectCompany.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many result for company [{$company_id}]");
        if ( count($results) == 0) return array();

        $company = $results[0];
        $company = $this->_addIsChildPropertToCompany($company);

        return $company;

    }
    public function get_company_by_name( $company_name ) {
        $file = "database/sql/company/SelectCompanyByName.sql";
        $vars = array(
            getStringValue($company_name)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();

        $company = $results[0];
        $company = $this->_addIsChildPropertToCompany($company);

        return $company;
    }

    public function create_company( $name, $address, $city, $state, $postal) {
        $file = "database/sql/company/AddNewCompany.sql";
        $vars = array(
            getStringValue($name)
            , getStringValue($address) === '' ? null : getStringValue($address)
            , getStringValue($city) === '' ? null : getStringValue($city)
            , getStringValue($state) === '' ? null : getStringValue($state)
            , getStringValue($postal) === '' ? null : getStringValue($postal)
        );
        ExecuteSQL( $this->db, $file, $vars );

        $company = $this->get_company_by_name( $name );
        if ( empty($company) ) throw new Exception("could not create company!");

        // Audit this transaction.
        $payload = array();
        $payload = array_merge($payload, array('CompanyId'=>GetArrayStringValue('company_id', $company)));
        $payload = array_merge($payload, array('CompanyName' => GetArrayStringValue('company_name', $company)));
        AuditIt("Company created.", $payload);

        return getArrayIntValue("company_id", $company);

    }

    public function update_company( $name, $address, $city, $state, $postal, $id) {
        $file = "database/sql/company/UpdateCompany.sql";
        $vars = array(
            getStringValue($name)
            , getStringValue($address)
            , getStringValue($city)
            , getStringValue($state)
            , getStringValue($postal)
            , getIntValue($id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $company = $this->get_company($id);
        $payload = array();
        $payload = array_merge($payload, array('CompanyId'=>GetArrayStringValue('company_id', $company)));
        $payload = array_merge($payload, array('CompanyName' => GetArrayStringValue('company_name', $company)));
        $payload = array_merge($payload, array('CompanyAddress' => GetArrayStringValue('company_address', $company)));
        $payload = array_merge($payload, array('CompanyCity' => GetArrayStringValue('company_city', $company)));
        $payload = array_merge($payload, array('CompanyState' => GetArrayStringValue('company_state', $company)));
        $payload = array_merge($payload, array('CompanyPostal' => GetArrayStringValue('company_postal', $company)));
        AuditIt("Company profile updated.", $payload);
    }
    public function link_company_to_parent( $company_id, $company_parent_id ) {

        $file = "database/sql/company/CompanyParentCompanyRelationshipINSERT_link.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($company_parent_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction
        $company = $this->get_company($company_id);
        $parent = $this->CompanyParent_model->get_companyparent($company_parent_id);
        $payload = array();
        $payload = array_merge($payload, array('CompanyId'=>GetArrayStringValue('company_id', $company)));
        $payload = array_merge($payload, array('CompanyName' => GetArrayStringValue('company_name', $company)));
        $payload = array_merge($payload, array('CompanyParentId'=>GetArrayStringValue('Id', $parent)));
        $payload = array_merge($payload, array('CompanyParentName' => GetArrayStringValue('Name', $parent)));
        AuditIt("Company assigned to parent.", $payload);
    }
    public function is_company_linked_to_parent( $company_id, $company_parent_id ) {

        $file = "database/sql/company/IsCompanyLinkedToParent.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($company_parent_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Unexpected results from the database.");
        $results = $results[0];
        if ( getArrayStringValue("linked", $results) == "1" ) return true;

        return false;

    }
    public function count_company_parent_relationships( $company_id ) {

        $file = "database/sql/company/CompanyParentCompanyRelationshipCOUNT_Parents.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Unexpected results from the database.");
        $results = $results[0];
        return GetArrayIntValue("count", $results);

    }





    private function _addIsChildPropertToCompany($company)
    {
        $company_id = GetArrayStringValue("company_id", $company);
        $file = "database/sql/company/CompanyParentCompanyRelationshipCOUNT_Parents.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) !== 1) throw new Exception('Unexpected results when counting records in database.');
        $results = $results[0];
        $count = GetArrayIntValue("count", $results);
        if( $count === 0 )
        {
            $company["is_child"] = 'f';
        }
        else
        {
            $company["is_child"] = 't';
        }
        return $company;
    }
    public function delete_company($company_id)
    {
        if ( GetStringValue($company_id) === '' ) return;

        $file = "database/sql/company/CompanyDELETE.sql";
        $vars = array(
            GetIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function hard_delete_company($company_id, $authenticated_user_id, $verbose=false)
    {
        if ( getStringValue($company_id) === '' ) throw new Exception("Missing require input company_id");

        // Grab this data before we delete it.
        $company = $this->get_company($company_id);

        $tables = array();
        $tables[] = "Age";
        $tables[] = "Audit";
        $tables[] = "AutomaticAdjustment";
        $tables[] = "CompanyBeneficiaryMap";
        $tables[] = "CompanyBeneficiaryImport";
        $tables[] = "CompanyBestMappedColumn";
        $tables[] = "CompanyCarrier";
        $tables[] = "CompanyCommission";
        $tables[] = "CompanyCommissionData";
        $tables[] = "CompanyCommissionDataCompare";
        $tables[] = "CompanyCommissionLife";
        $tables[] = "CompanyCommissionLifeResearch";
        $tables[] = "CompanyCommissionSummary";
        $tables[] = "CompanyCommissionValidate";
        $tables[] = "CompanyCommissionWarning";
        $tables[] = "CompanyCommissionWorker";
        $tables[] = "CompanyCoverageTier";
        $tables[] = "CompanyFeature";
        $tables[] = "CompanyFileTransfer";
        $tables[] = "CompanyLife";
        $tables[] = "CompanyLifeCompare";
        $tables[] = "CompanyLifeDiff";
        $tables[] = "CompanyLifeResearch";
        $tables[] = "CompanyMappingColumn";
        $tables[] = "CompanyParentCompanyRelationship";
        $tables[] = "CompanyParentMapCompany";
        $tables[] = "CompanyPlan";
        $tables[] = "CompanyPlanType";
        $tables[] = "CompanyPreference";
        $tables[] = "CompanyRelationship";
        $tables[] = "CompanyReport";
        $tables[] = "CompanyUniversalEmployee";
        $tables[] = "CompanyUniversalEmployeeRollback";
        $tables[] = "HistoryChangeToCompany";
        $tables[] = "HistoryEmail";
        $tables[] = "ImportData";
        $tables[] = "ImportDataDuplicateLives";
        $tables[] = "ImportLife";
        $tables[] = "ImportLifeWorker";
        $tables[] = "ImportLifeWarning";
        $tables[] = "LifeData";
        $tables[] = "LifeEventCompare";
        $tables[] = "LifeOriginalEffectiveDateCompare";
        $tables[] = "LifeOriginalEffectiveDateRollback";
        $tables[] = "Log";
        $tables[] = "LogTimer";
        $tables[] = "LogTimerRelationship";
        $tables[] = "ManualAdjustment";
        $tables[] = "RelationshipData";
        $tables[] = "ReportReviewWarnings";
        $tables[] = "ReportTransamericaActuarial";
        $tables[] = "ReportTransamericaActuarialDetails";
        $tables[] = "ReportTransamericaCommission";
        $tables[] = "ReportTransamericaCommissionDetail";
        $tables[] = "ReportTransamericaEligibility";
        $tables[] = "ReportTransamericaEligibilityDetails";
        $tables[] = "RetroData";
        $tables[] = "RetroDataLifeEvent";
        $tables[] = "RetroDataLifeEventWarning";
        $tables[] = "SkipMonthProcessing";
        $tables[] = "SummaryData";
        $tables[] = "SummaryDataPremiumEquivalent";
        $tables[] = "SummaryDataYTD";
        $tables[] = "SupportTimer";
        $tables[] = "UserCompany";
        $tables[] = "UserResponsibleForCompany";
        $tables[] = "ValidationErrors";
        $tables[] = "WashedData";
        $tables[] = "Wizard";

        $template = 'delete from "{TABLE}" where "CompanyId" = ?';
        $vars = array(
            getIntValue($company_id)
        );

        foreach( $tables as $table )
        {
            if ( $verbose ) print "Removing company data from table {$table}.\n";
            $replacefor = array();
            $replacefor["{TABLE}"] = $table;
            ExecuteSQL( $this->db, $template, $vars, $replacefor );
        }

        if ( $verbose ) print "Removing company data from table Company.\n";
        $sql = 'delete from "Company" where "Id" = ?';
        ExecuteSQL( $this->db, $sql, $vars );

        // Audit this action has completed.
        AuditIt("Delete company and company data.", $company, $authenticated_user_id, A2P_COMPANY_ID);
    }
    public function disable_custom_normalization($company_id, $column_code)
    {
        $file = "database/sql/mapping/CompanyMappingColumnUPDATE_CustomNormalizationByCompany.sql";
        $vars = array(
            null,
            GetStringValue($column_code),
            GetIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function enable_custom_normalization($company_id, $column_code, $rules)
    {
        $file = "database/sql/mapping/CompanyMappingColumnUPDATE_CustomNormalizationByCompany.sql";
        $vars = array(
            GetStringValue(json_encode($rules)),
            GetStringValue($column_code),
            GetIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function skips_in_window($company_id, $import_date, $max)
    {
        $replaceFor = [];
        $replaceFor['{IMPORTDATE}'] = GetStringValue($import_date);
        $replaceFor['{MAX}'] = GetStringValue($max);
        $replaceFor['{IMPORTDATE}'] = GetStringValue($import_date);

        $file = "database/sql/skipmonthprocessing/SkipMonthProcessingBOOLEAN_SkipsInWindow.sql";
        $vars = array(
            GetIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars, $replaceFor );
        if ( count($results) !== 1 ) throw new Exception("Found too many results.");

        $results = $results[0];
        return GetArrayIntValue('SkipsInWindow', $results);
    }
}


/* End of file Company_model.php */
/* Location: ./system/application/models/Company_model.php */
