<?php

class Adjustment_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    function update_automatic_adjustments_life_event($company_id) {

        // Update any Automatic Adjustments that have been identified as
        // a life event.  For this specific query, we will exclude any
        // many2many life events.

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/adjustments/AutomaticAdjustmentUPDATE_LifeEvent.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_automatic_adjustments_life_event_many2_many($company_id, $coverage_start_date, $list) {

        // Update any Automatic Adjustments that have been identified as
        // a life event and are in a many2many situation because of a coverage
        // tier change.

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/adjustments/AutomaticAdjustmentUPDATE_LifeEventMany2Many.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($coverage_start_date)
            , getStringValue($list)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_automatic_adjustments_ignore_retro_change_narrow_for_plan_anniversary( $company_id, $plan_anniversary_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/adjustments/AutomaticAdjustmentsUPDATE_IgnorePlanAnniversaryRetroChangeNARROW.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($plan_anniversary_date)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
            , getIntValue($plan_id)
            , getIntValue($coveragetier_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function insert_plan_anniversary_retro_change_wide_warnings( $company_id, $plan_anniversary_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/adjustments/ReportReviewWarningsINSERT_PlanAnniversaryRetroChangeWIDE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($plan_anniversary_date)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
            , getIntValue($plan_id)
            , getIntValue($coveragetier_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function insert_plan_anniversary_retro_add_warnings( $company_id, $plan_anniversary_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/adjustments/ReportReviewWarningsINSERT_PlanAnniversaryRetroAdd.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($plan_anniversary_date)
            , getIntValue($carrier_id)
            , getIntValue($plantype_id)
            , getIntValue($plan_id)
            , getIntValue($coveragetier_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function select_automatic_adjustments_plan_anniversary_groupings( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/adjustments/AutomaticAdjustmentsSELECT_PlanAnniversary.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;
    }
    function delete_logically_zero_cost_termination( $company_id, $import_id=null ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/adjustments/AutomaticAdjustmentDELETE_ZeroCostOnTermination.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function delete_logically_limit_retro_period_adjustments( $company_id, $import_id=null ) {

        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        $file = "database/sql/adjustments/AutomaticAdjustmentDELETE_LimitRetroPeriod.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function insert_manual_adjustment( $company_id, $carrier_id, $memo, $amount ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        // Not yet supported.
        $plantype_id = null;
        $plan_id = null;
        $coveragetier_id = null;
        $life_id = null;

        $file = "database/sql/adjustments/ManualAdjustmentINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue( $carrier_id )
            , ( $plantype_id == null ? null : getIntValue($plantype_id) )
            , ( $plan_id == null ? null : getIntValue($plan_id) )
            , ( $coveragetier_id == null ? null : getIntValue($coveragetier_id) )
            , ( $life_id == null ? null : getIntValue($life_id) )
            , getIntValue( ADJUSTMENT_TYPE_MANUAL )
            , getFloatValue( $amount )
            , getStringValue( $memo )
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $carrier = $this->Company_model->get_company_carrier($company_id, $carrier_id);
        $payload = array();
        $payload["CarrierId"] = $carrier_id;
        $payload["CarrierCode"] = GetArrayStringValue('CarrierCode', $carrier);
        $payload["Amount"] = $amount;
        $payload['Memo'] = $memo;
        AuditIt("Manual adjustment added.", $payload);
    }
    function update_manual_adjustment( $company_id, $id, $carrier_id, $memo, $amount ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        // Not yet supported.
        $plantype_id = null;
        $plan_id = null;
        $coveragetier_id = null;
        $life_id = null;

        $file = "database/sql/adjustments/ManualAdjustmentUPDATE.sql";
        $vars = array(
            getIntValue( $carrier_id )
            , getStringValue( $memo )
            , getFloatValue( $amount )
            , getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue( $id )
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $carrier = $this->Company_model->get_company_carrier($company_id, $carrier_id);
        $payload = array();
        $payload["AdjustmentId"] = $id;
        $payload["CarrierId"] = $carrier_id;
        $payload["CarrierCode"] = GetArrayStringValue('CarrierCode', $carrier);
        $payload["Amount"] = $amount;
        $payload['Memo'] = $memo;
        AuditIt("Manual adjustment updated.", $payload);

    }
    function delete_manual_adjustment($company_id, $import_date=null) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/adjustments/ManualAdjustmentDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );


    }
    function select_manual_adjustments($company_id) {

        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/adjustments/ManualAdjustmentSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        return $results;

    }
    function select_manual_adjustment( $company_id, $id ) {

        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/adjustments/ManualAdjustmentSELECT_byId.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 ) return $results[0];
        return array();

    }
    function delete_manual_adjustment_by_id($company_id, $adjustment_id) {

        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();
        $adjustment = $this->select_manual_adjustment($company_id, $adjustment_id);

        $file = "database/sql/adjustments/ManualAdjustmentDELETE_ById.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($adjustment_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $carrier = $this->Company_model->get_company_carrier($company_id, GetArrayStringValue("CarrierId", $adjustment));
        $payload = array();
        $payload["AdjustmentId"] = GetArrayStringValue("Id", $adjustment);
        $payload["CarrierId"] = GetArrayStringValue("CarrierId", $adjustment);
        $payload["CarrierCode"] = GetArrayStringValue('CarrierCode', $carrier);
        $payload["Amount"] = GetArrayStringValue("Amount", $adjustment);
        $payload['Memo'] = GetArrayStringValue("Memo", $adjustment);
        AuditIt("Manual adjustment deleted.", $payload);


    }
    function update_summary_data_with_manual_adjustments( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) return;

        // Grab a collection of data from the database that has all of our automatic
        // adjustments.  This groups the adjustments by key, life and attributes.
        $file = "database/sql/adjustments/ManualAdjustmentsSELECT_ByRetroGroupings.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if (empty($results)) return;

        // Take the adjustment data we pulled above, examine the attributes and
        // then construct and update statement that will place the adjustment
        // total into the correct already existing location in the summary table
        // for the report.
        foreach($results as $item)
        {

            $carrier_id = getArrayStringValue("CarrierId", $item);

            $plantype_id = getArrayStringValue("PlanTypeId", $item);
            if ( getStringValue("PlanTypeId", $item) == "" ) $plantype_id = null;

            $plan_id = getArrayStringValue("PlanId", $item);
            if ( getStringValue("PlanId", $item) == "" ) $plan_id = null;

            $coveragetier_id = getArrayStringValue("CoverageTierId", $item);
            if ( getStringValue("CoverageTierId", $item) == "" ) $coveragetier_id = null;

            $ageband_id = null;
            $tobaccouser = null;

            // Does the summary record exist?
            if ( $this->Retro_model->does_summary_data_record_exist($company_id, $import_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, $ageband_id, $tobaccouser) == "f" )
            {
                $this->Reporting_model->insert_summary_data_record($company_id, $carrier_id, $plantype_id, $plan_id, $coveragetier_id, null, null);
            }

            // For manual adjustments, many columns might be null.  Handle them if they are null
            // by using the replacefor.
            $replaceFor = array();
            $replaceFor['{PLANTYPEID}'] =  " is null ";
            if ( $plantype_id != null ) $replaceFor['{PLANTYPEID}'] =  " = " . getStringValue($plantype_id);
            $replaceFor['{PLANID}'] =  " is null ";
            if ( $plan_id != null ) $replaceFor['{PLANID}'] =  " = " . getStringValue($plan_id);
            $replaceFor['{COVERAGETIERID}'] =  " is null ";
            if ( $coveragetier_id != null ) $replaceFor['{COVERAGETIERID}'] =  " = " . getStringValue($coveragetier_id);
            $replaceFor['{AGEBAND}'] =  " is null ";
            $replaceFor['{TOBACCOUSER}'] =  " is null ";

            $file = "database/sql/summarydata/SummaryDataUPDATE_ManualAdjustment.sql";
            $vars = array(
                getArrayFloatValue("TotalAdjustedVolume", $item)
                , getArrayFloatValue("TotalAdjustedPremium", $item)
                , getIntValue($company_id)
                , getStringValue($import_date)
                , getArrayIntValue("CarrierId", $item)
            );
            ExecuteSQL($this->db, $file, $vars, $replaceFor);
        }

    }
    function delete_negative_plan_anniversary_adjustments( $company_id, $plan_anniversary_date, $carrier_id, $plantype_id, $plan_id, $coveragetier_id ) {

        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/adjustments/AutomaticAdjustmentDELETE_NegativePlanAnniversary.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date),
            getStringValue($plan_anniversary_date),
            getIntValue($carrier_id),
            getIntValue($plantype_id),
            getIntValue($plan_id),
            getIntValue($coveragetier_id),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

}


/* End of file Adjustment_model.php */
/* Location: ./system/application/models/Adjustment_model.php */
