<?php

class PlanFees_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    public function select_planfee_parent_records( $company_id ) {
        $file = "database/sql/planfees/CompanyPlanSELECT_PlanFeeParentRecords.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function select_planfee_child_records( $company_id, $normalized_plan_description ) {
        $file = "database/sql/planfees/CompanyPlanSELECT_PlanFeeChildrenByDescription.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($normalized_plan_description)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function select_pe_parent_child_relationships( $company_id, $carrier_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/planfees/SummaryDataPremiumEquivalentSELECT_ParentChildRelationshiops.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function insert_premium_equivalent( $company_id, $carrier_id, $plantype_id, $target_carrier_id ){
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/planfees/SummaryDataPremiumEquivalentINSERT.sql";
        $vars = array(
            getIntValue($carrier_id)
            , getIntValue($target_carrier_id)
            , getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function delete_related_premium_equivalent_records( $company_id, $carrier_id, $plantype_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/planfees/SummaryDataDELETE_PlanFee_PlanType_Records.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function select_planfee_plantype_relationships( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/planfees/SummaryDataSELECT_PlanFee_PlanType_Relationships.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function plantype_has_plan_fees( $plantype_code ) {
        $file = "database/sql/planfees/PlanTypesSELECT_HasPlanFees.sql";
        $vars = array(
            getStringValue($plantype_code)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) == 1 ) return $results[0];
        return $results;
    }
    /*
    public function update_summary_data_premium_equivalent_totals( $record_id, $lives, $premium, $volume, $adjusted_lives, $adjusted_premium, $adjusted_volume) {

        $file = "database/sql/summarydata/SummaryDataPremiumEquivalentUPDATE_FeeRecordTotals.sql";
        $vars = array(
            getIntValue($lives)
            , getIntValue($premium)
            , getFloatValue($volume)
            , getFloatValue($adjusted_lives)
            , getFloatValue($adjusted_premium)
            , getFloatValue($adjusted_volume)
            , getIntValue($record_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    */
    /*
    public function insert_company_coverage_tiers_for_aso_fees( $company_id ) {
        $file = "database/sql/planfees/CompanyCoverageTierINSERT_ASOFees.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    */
    /*
    public function insert_company_coverage_tiers_for_stoploss_fees( $company_id ) {
        $file = "database/sql/planfees/CompanyCoverageTierINSERT_StopLossFees.sql";
        $vars = array(
            getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    */
    public function delete_plan_fee_importdata($company_id, $import_date = "") {
        if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/planfees/ImportDataDELETE_PlanFees.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function delete_plan_fee_lifedata($company_id) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/planfees/LifeDataDELETE_PlanFees.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function insert_importdata_plan_fee_records($company_id, $carrier, $plantype, $original_carrier_id, $original_plantype_id, $cost) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/planfees/ImportDataINSERT_PlanFees.sql";
        $vars = array(
            getStringValue($plantype)
            , getStringValue($carrier)
            , getStringValue($cost)
            , getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($original_carrier_id)
            , getIntValue($original_plantype_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function select_company_plan_aso_fees( $company_id) {
        $file = "database/sql/planfees/CompanyPlanSELECT_ASOFees.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function select_company_plan_stoploss_fees( $company_id) {
        $file = "database/sql/planfees/CompanyPlanSELECT_StopLossFees.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    public function select_company_plan( $company_id, $carrier, $plantype, $plan ) {

        $file = "database/sql/planfees/CompanyPlanSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($company_id)
            , getIntValue($company_id)
            , getStringValue($carrier)
            , getStringValue($plantype)
            , getStringValue($plan)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1 ) throw new Exception("Unexpected situation. Found more than one company plan.");
        return $results[0];
    }
    public function create_planfee_carrier( $company_id, $carrier_code ) {

        // create_planfee_carrier
        //
        // Lookup the carrier specified.  If missing, create it.
        // ------------------------------------------------------------------

        if ( getStringValue($carrier_code) == "" ) return null;

        $results = $this->Company_model->get_company_carrier_by_carrier_code($company_id, $carrier_code);
        $carrier_id = getArrayStringValue("Id", $results);
        if ( empty( $results ) )
        {
            // Insert the carrier.
            //pprint_r("creating a new carrier: [{$company_id}][{$carrier}]");
            $this->Company_model->insert_company_carrier_by_carrier_code($company_id, $carrier_code);

            // Select the carrier we just inserted.
            $results = $this->Company_model->get_company_carrier_by_carrier_code($company_id, $carrier_code);
            return getArrayStringValue("Id", $results);
        }
        return $carrier_id;
    }
    public function create_planfee_plantype( $company_id, $carrier_id, $plantype_id, $fee_userdescription, $fee_plantype_code ) {

        // create_planfee_plantype
        //
        // Lookup the carrier/plantype specified.  If missing, create it.
        // ------------------------------------------------------------------

        if ( getStringValue($carrier_id) == "" ) return null;
        $carrier_data = $this->Company_model->get_company_carrier( $company_id, $carrier_id );
        $carrier = getArrayStringValue("UserDescription", $carrier_data);
        $fee_plantype_data = $this->Company_model->get_company_plantype_data( $company_id, $carrier, $fee_userdescription );
        $fee_plantype_id = getArrayStringValue("Id", $fee_plantype_data);

        if ( empty($fee_plantype_data) )
        {
            //pprint_r("creating a new plantype: [{$company_id}][{$carrier_id}][{$fee_userdescription}][{$fee_plantype_code}]");
            $this->Company_model->insert_company_plantype( $company_id, $carrier_id, $fee_userdescription, $fee_plantype_code );
            $fee_plantype_data = $this->Company_model->get_company_plantype_data( $company_id, $carrier, $fee_userdescription );
            return getArrayStringValue("Id", $fee_plantype_data);
        }
        return $fee_plantype_id;
    }
    public function create_planfee_plan( $company_id, $carrier_id, $fee_plantype_id, $plan_description ){

        // create_planfee_plan
        //
        // Lookup the carrier/plantype/plan specified.  If missing, create it.
        // ------------------------------------------------------------------

        if ( getStringValue($carrier_id) == "" ) return null;
        if ( getStringValue($fee_plantype_id) == "" ) return null;
        $fee_plan_data = $this->Company_model->get_company_plan_by_description( $company_id, $carrier_id, $fee_plantype_id, $plan_description );
        $fee_plan_id = getArrayStringValue("Id", $fee_plan_data);

        if ( empty($fee_plan_data) )
        {
            // CREATE. The combination of the new carrier, new plantype and the current plan
            // does not exist.  Let's create it.
            $this->Company_model->insert_company_plan($company_id, $carrier_id, $fee_plantype_id, $plan_description);
            $fee_plan_data = $this->Company_model->get_company_plan_by_description( $company_id, $carrier_id, $fee_plantype_id, $plan_description );
            $fee_plan_id = getArrayStringValue("Id", $fee_plan_data);
        }
        return $fee_plan_id;
    }
    public function create_planfee_coveragetier( $company_id, $parent_plan_id, $fee_plan_id ) {

        // create_planfee_coveragetier
        //
        // Lookup the parent plan record and then pull out all the coverage
        // tiers associated with it.  Then create, if needed, coverage tier
        // records for the plan fee record.
        // ------------------------------------------------------------------

        if ( getStringValue($company_id) == "" ) return null;
        if ( getStringValue($parent_plan_id) == "" ) return null;
        if ( getStringValue($fee_plan_id) == "" ) return null;

        // Startng with the Plan id, collect information about the Carrier and
        // PlanType for the given fee.
        $fee_plan_data = $this->Company_model->get_company_plan_by_id( $company_id, $fee_plan_id );
        $fee_carrier_id = getArrayStringValue("CarrierId", $fee_plan_data);
        $fee_plantype_id = getArrayStringValue("PlanTypeId", $fee_plan_data);
        $fee_carrier_data = $this->Company_model->get_company_carrier( $company_id, $fee_carrier_id );
        $fee_carrier = getArrayStringValue("UserDescription", $fee_carrier_data);
        $fee_plantype_data = $this->Company_model->get_compmay_plantype_data_by_ids( $company_id, $fee_carrier_id, $fee_plantype_id);
        $fee_plantype = getArrayStringValue("UserDescription", $fee_plantype_data);
        $fee_plantypecode = getArrayStringValue("PlanTypeCode", $fee_plantype_data);
        $fee_plan = getArrayStringValue("UserDescription", $fee_plan_data);

        // Collect all of the coverage tiers associated with the parent plan.
        $items = $this->Company_model->get_company_coveragetier_by_plan_id( $company_id, $parent_plan_id );
        foreach($items as $item){

            // Grab the coverage tier description off the parent.
            $parent_coveragetier = getArrayStringValue("UserDescription", $item);

            // Does the coverage tier exist for the fee?
            $fee_coveragetier_data = $this->Company_model->get_company_coveragetier_by_description( $company_id, $fee_carrier_id, $fee_plantype_id, $fee_plan_id, $parent_coveragetier );
            $fee_coveragetier_id = getArrayStringValue("Id", $fee_coveragetier_data);

            if ( empty($fee_coveragetier_data) )
            {
                // The coverage tier does not exist for the fee, create it.
                //pprint_r("creating a new coveragetier: [{$company_id}][{$fee_carrier_id}][{$fee_plantype_id}][{$fee_plan_id}][$parent_coveragetier]");
                $this->Company_model->insert_company_coverage_tier($company_id, $fee_carrier_id, $fee_plantype_id, $fee_plan_id, $parent_coveragetier, true, true );
                $fee_coveragetier_data = $this->Company_model->get_company_coveragetier_by_description( $company_id, $fee_carrier_id, $fee_plantype_id, $fee_plan_id, $parent_coveragetier );
                $fee_coveragetier_id = getArrayStringValue("Id", $fee_coveragetier_data);
            }

        }

    }
    private function _debug_carrier( $company_id, $id ) {

        $carrier_data = $this->Company_model->get_company_carrier($company_id, $id);
        $carrier_id = getArrayStringValue("Id", $carrier_data);
        $carrier = getArrayStringValue("UserDescription", $carrier_data);

        pprint_r("CARRIER: {$carrier} ({$carrier_id})");

    }
    private function _debug_plantype( $company_id, $id ) {

        $plantype_data = $this->Company_model->get_compmay_plantype_data_by_id($company_id, $id);
        $carrier_id = getArrayStringValue("CarrierId", $plantype_data);
        $plantype_id = getArrayStringValue("Id", $plantype_data);
        $plantype = getArrayStringValue("UserDescription", $plantype_data);
        $plantypecode = getArrayStringValue("PlanTypeCode", $plantype_data);

        $carrier_data = $this->Company_model->get_company_carrier($company_id, $carrier_id);
        $carrier = getArrayStringValue("UserDescription", $carrier_data);

        pprint_r("PLANTYPE: {$carrier} ({$carrier_id}) {$plantype} [{$plantypecode}] ({$plantype_id})");

    }
    private function _debug_plan( $company_id, $plan_id ) {

        $plan_data = $this->Company_model->get_company_plan_by_id($company_id, $plan_id);
        $carrier_id = getArrayStringValue("CarrierId", $plan_data);
        $plantype_id = getArrayStringValue("PlanTypeId", $plan_data);
        $plan_id = getArrayStringVAlue("Id", $plan_data);
        $plan = getArrayStringVAlue("UserDescription", $plan_data);

        $carrier_data = $this->Company_model->get_company_carrier($company_id, getArrayStringValue("CarrierId", $plan_data));
        $carrier = getArrayStringValue("UserDescription", $carrier_data);

        $plantype_data = $this->Company_model->get_compmay_plantype_data_by_ids($company_id, getArrayStringValue("CarrierId", $plan_data), getArrayStringValue("PlanTypeId", $plan_data));
        $plantype = getArrayStringValue("UserDescription", $plantype_data);
        $plantypecode = getArrayStringValue("PlanTypeCode", $plantype_data);

        pprint_r("PLAN: {$carrier} ({$carrier_id}) {$plantype} [{$plantypecode}] ({$plantype_id}) {$plan} ({$plan_id})");

    }

    public function update_company_plan_fee( $company_id, $plan_id, $fee, $fee_carrier_code, $fee_tag ) {

        // update_company_plan_fee
        //
        // The end goal of this function is to update the planfee data.  What makes
        // this interesting is that we may need to create records in CompanyCarrier,
        // CompnayPlanType and CompanyPlan before we can do that.
        // ------------------------------------------------------------------

        // Look up information about the plan passed in.  grab info for the plantype
        // and carrier associated with the plan as well.
        $plan_data = $this->Company_model->get_company_plan_by_id($company_id, $plan_id);
        $carrier_id = getArrayIntValue("CarrierId", $plan_data);
        $plan_description = getArrayStringValue("UserDescription", $plan_data);
        $plantype_id = getArrayStringValue("PlanTypeId", $plan_data);
        $plantype_data = $this->Company_model->get_compmay_plantype_data_by_ids( $company_id, $carrier_id, $plantype_id );
        $plantype_userdescription = getArrayStringValue("UserDescription", $plantype_data);
        $plantype_code = getArrayStringValue("PlanTypeCode", $plantype_data);

        // Generate Fee Specific data we will need to do the update.
        if ($fee_tag == "aso" )
        {
            $fee_update_sql = "CompanyPlanUPDATE_ASOFee.sql";
            $fee_plantype_userdescription = $plantype_userdescription . " ASO";
            $fee_plantype_code = $plantype_code . "_aso";
        }else if ( $fee_tag == "stoploss" )
        {
            $fee_update_sql = "CompanyPlanUPDATE_StopLossFee.sql";
            $fee_plantype_userdescription = $plantype_userdescription . " Stop Loss";
            $fee_plantype_code = $plantype_code . "_stoploss";
        }else{
            throw new Exception("Unsupported fee tag.");
        }


        // We were just handed a carrier_code.  Grab the carrier id from the list
        // of known carriers for this company.  This id will remain blank if it is a new carrier.
        $fee_carrier_id = "";
        $fee_carrier_data = $this->Company_model->get_company_carrier_by_carrier_code($company_id, $fee_carrier_code);
        $fee_carrier_id = GetArrayStringValue("Id", $fee_carrier_data);

        // Create the Key Records if we have a fee in hand.
        if ( getStringValue($fee) != "" )
        {
            //pprint_r("INPUT: carrier_id: {$carrier_id}");
            //pprint_r("INPUT: plan_description: {$plan_description}");
            //pprint_r("INPUT: plantype_description: {$plantype_userdescription} ({$plantype_id})");
            //pprint_r("INPUT: plantype_code: {$plantype_code}");
            //pprint_r("INPUT: fee_plantype_description: {$fee_plantype_userdescription}");
            //pprint_r("INPUT: fee_plantype_code: {$fee_plantype_code}");

            // CompanyCarrier
            // Create the CompanyCarrier record for the carrier specified if needed.
            $fee_carrier_id = $this->create_planfee_carrier($company_id, $fee_carrier_code);
            //$this->_debug_carrier($company_id, $fee_carrier_id);

            // CompanyPlanType
            // If the carrier_id and the fee_carrier_id are different, we might need to
            // create more than one plantype record.  Check now an capture the plantype ids.
            $fee_plantype_id1 = $this->create_planfee_plantype($company_id, $carrier_id, $plantype_id, $fee_plantype_userdescription, $fee_plantype_code);
            $fee_plantype_id2 = $this->create_planfee_plantype($company_id, $fee_carrier_id, $plantype_id, $fee_plantype_userdescription, $fee_plantype_code);
            //$this->_debug_plantype($company_id, $fee_plantype_id1);
            //$this->_debug_plantype($company_id, $fee_plantype_id2);

            // CompanyPlan
            // We will need to make sure that we have a CompanyPlan record for each fee
            // record.
            $fee_plan_id1 = $this->create_planfee_plan( $company_id, $carrier_id, $fee_plantype_id1, $plan_description );
            $fee_plan_id2 = $this->create_planfee_plan( $company_id, $carrier_id, $fee_plantype_id2, $plan_description );
            $fee_plan_id3 = $this->create_planfee_plan( $company_id, $fee_carrier_id, $fee_plantype_id1, $plan_description );
            $fee_plan_id4 = $this->create_planfee_plan( $company_id, $fee_carrier_id, $fee_plantype_id2, $plan_description );
            //$this->_debug_plan($company_id, $fee_plan_id1);
            //$this->_debug_plan($company_id, $fee_plan_id2);
            //$this->_debug_plan($company_id, $fee_plan_id3);
            //$this->_debug_plan($company_id, $fee_plan_id4);


            // CompanyCoverageTier
            // We will need CompanyCoverageTier records too!  Good news, we don't need
            // to wait and see what elections the user takes.  PlanFees will always have
            // AgeBands and Tobacco attributes disabled like they elected to not have them.
            // Create them now if they do not already exist for each of the plan records
            // you created.
            $this->create_planfee_coveragetier( $company_id, $plan_id, $fee_plan_id1 );
            $this->create_planfee_coveragetier( $company_id, $plan_id, $fee_plan_id2 );
            $this->create_planfee_coveragetier( $company_id, $plan_id, $fee_plan_id3 );
            $this->create_planfee_coveragetier( $company_id, $plan_id, $fee_plan_id4 );

        }



        $file = "database/sql/planfees/{$fee_update_sql}";
        $vars = array(
            ( getStringValue($fee) == "" ? null : getFloatValue($fee) )
            , ( getStringValue($fee) == "" ? null : getIntValue($carrier_id) )
            , ( getStringValue($fee) == "" ? null : getIntValue($fee_plantype_id1) )
            , getIntValue($company_id)
            , getIntValue($plan_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        $file = "database/sql/planfees/{$fee_update_sql}";
        $vars = array(
            ( getStringValue($fee) == "" ? null : getFloatValue($fee) )
            , ( getStringValue($fee) == "" ? null : getIntValue($carrier_id) )
            , ( getStringValue($fee) == "" ? null : getIntValue($fee_plantype_id2) )
            , getIntValue($company_id)
            , getIntValue($plan_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        $file = "database/sql/planfees/{$fee_update_sql}";
        $vars = array(
            ( getStringValue($fee) == "" ? null : getFloatValue($fee) )
            , ( getStringValue($fee) == "" ? null : getIntValue($fee_carrier_id) )
            , ( getStringValue($fee) == "" ? null : getIntValue($fee_plantype_id1) )
            , getIntValue($company_id)
            , getIntValue($plan_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        $file = "database/sql/planfees/{$fee_update_sql}";
        $vars = array(
            ( getStringValue($fee) == "" ? null : getFloatValue($fee) )
            , ( getStringValue($fee) == "" ? null : getIntValue($fee_carrier_id) )
            , ( getStringValue($fee) == "" ? null : getIntValue($fee_plantype_id2) )
            , getIntValue($company_id)
            , getIntValue($plan_id)
        );
        ExecuteSQL( $this->db, $file, $vars );


    }
    public function push_plantype_attributes( $company_id, $carrier_id, $plantype )
    {

        // When a plantype changes (parent), scan the company plan reocrds for
        // fees.  If we can find a fee that is tied to the parent plantype, grab
        // the child it's refering to and push the parent plantype attributes
        // onto the child plantype attributes.
        //
        // Because a "fee" will make new plantype records, when the parent record
        // updates, the child records must get those same changes.  Thus we push
        // the parent plantype records onto the related plantype records.

        $parent = $this->Company_model->get_company_plantype_by_desc($company_id, $carrier_id, $plantype);
        $parent_carrier_id = $carrier_id;
        $parent_plantype_id = getArrayStringValue("Id", $parent);


        // Here are the parent attributes we want to push to all of the related plantypes
        $parent_userdescription = getArrayStringValue("UserDescription", $parent);
        $parent_retrorule = getArrayStringValue("RetroRule", $parent);
        $parent_washrule = getArrayStringValue("WashRule", $parent);
        $parent_plananniversaymonth = getArrayStringValue("PlanAnniversaryMonth", $parent);
        $parent_plantypecode = getArrayStringValue("PlanTypeCode", $parent);
        $ignored = getArrayStringValue("Ignored", $parent);

        //pprint_r("parent_carrier_id: {$parent_carrier_id}");
        //pprint_r("parent_plantype_id: {$parent_plantype_id}");
        //pprint_r("parent_userdescription: {$parent_userdescription}");
        //pprint_r("parent_retrorule: {$parent_retrorule}");
        //pprint_r("parent_washrule: {$parent_washrule}");
        //pprint_r("parent_plananniversaymonth: {$parent_plananniversaymonth}");
        //pprint_r("parent_plantypecode: {$parent_plantypecode}");
        //pprint_r("ignored: {$ignored}");
        //exit;


        // Select all of the ASO fees for this company.
        $tags = array();
        $tags[] = "aso";
        $tags[] = "stoploss";

        foreach($tags as $tag)
        {
            if ( $tag == "aso" ) $fees = $this->select_company_plan_aso_fees($company_id);
            if ( $tag == "stoploss" ) $fees = $this->select_company_plan_stoploss_fees($company_id);
            //pprint_r($parent_plantype_id);
            //pprint_r($parent);
            //pprint_r($tag);
            //pprint_r($fees);
            //exit;
            foreach($fees as $fee)
            {
                // Grab the ASO Fee Carrier and PlanType ids.
                $fee_carrier_id = getArrayStringValue("CarrierId", $fee);
                $fee_plantype_id = getArrayStringValue("PlanTypeId", $fee);

                // The above query pulled ALL of the plan fee records, not just the
                // ones for the plan we are savings.  This is because we need to push data
                // if they had a new carrier.  To this end, we must create the child plantype code
                // to match the plan type code of the current fee being saved.
                $parent_plantypecode = getArrayStringValue("ParentPlanTypeCode", $fee);

                // Assuming a new carrier id, update the child with the parent values.
                $child_carrier_id = getArrayStringValue("NewCarrierId", $fee);
                $child_plantype_id = getArrayStringValue("NewPlanTypeId", $fee);
                $child_plantype_user_description = getArrayStringValue("NewPlanType", $fee);
                $child_plantype_code = $parent_plantypecode . "_{$tag}";

                // update the child record.
                $this->Company_model->set_company_plantype_retrorule( $company_id, $child_carrier_id, strtoupper($child_plantype_user_description), $parent_retrorule );
                $this->Company_model->set_company_plantype_washrule( $company_id, $child_carrier_id, strtoupper($child_plantype_user_description), $parent_washrule );
                $this->Company_model->set_company_plantype_plananniversarymonth( $company_id, $child_carrier_id, strtoupper($child_plantype_user_description), $parent_plananniversaymonth );
                $this->Company_model->set_company_plantype_ignored( $company_id, $child_carrier_id, strtoupper($child_plantype_user_description), $ignored );
                $this->Company_model->set_company_plantype_code( $company_id, $child_carrier_id, strtoupper($child_plantype_user_description), $child_plantype_code );

                // Now, assume the carrier did not switch.
                $child_carrier_id = $carrier_id;

                // update the child record.
                $this->Company_model->set_company_plantype_retrorule( $company_id, $child_carrier_id, strtoupper($child_plantype_user_description), $parent_retrorule );
                $this->Company_model->set_company_plantype_washrule( $company_id, $child_carrier_id, strtoupper($child_plantype_user_description), $parent_washrule );
                $this->Company_model->set_company_plantype_plananniversarymonth( $company_id, $child_carrier_id, strtoupper($child_plantype_user_description), $parent_plananniversaymonth );
                $this->Company_model->set_company_plantype_ignored( $company_id, $child_carrier_id, strtoupper($child_plantype_user_description), $ignored );
                $this->Company_model->set_company_plantype_code( $company_id, $child_carrier_id, strtoupper($child_plantype_user_description), $child_plantype_code );

            }
        }



    }
    public function update_premium_equivalent( $company_id, $plan_id, $premium_equivalent ) {

        if ( getStringValue($premium_equivalent) == "t" ) $premium_equivalent = true;
        if ( getStringValue($premium_equivalent) == "f" ) $premium_equivalent = false;
        if ( ! is_bool($premium_equivalent) ) throw new Exception("Premium equivalent must be a boolean.");

        $file = "database/sql/planfees/CompanyPlanUPDATE_PremiumEquivalent.sql";
        $vars = array(
            ( $premium_equivalent ? "t" : "f" )
            , getIntValue($company_id)
            , getIntValue($plan_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }


}


/* End of file PlanFees_model.php */
/* Location: ./system/application/models/PlanFees_model.php */
