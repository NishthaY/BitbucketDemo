<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GeneratePlanFees extends A2PLibrary {

    function __construct( $debug=false )
    {
        parent::__construct($debug);
    }

    public function execute( $company_id, $user_id=null )
    {
        try {

            parent::execute($company_id);

            $CI = $this->ci;
            $this->slowdown = GetAppOption(REST_SECONDS_BETWEEN_QUERIES);

            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");

            // What is our import date?
            $import_date = GetUploadDate($company_id);
            if ( $import_date == "" ) throw new Exception("Invalid import_date.  How is that possible?");

            $this->debug(" ImportDate: [{$import_date}]");
            $this->debug(" ComapnyId:  [{$company_id}]");

            // Plan Fees are dependent upon the employee relationship.  If you do not have
            // relationships, you don't have fees.
            if ( HasRelationship($company_id) )
            {


                $this->debug(" Creating any missing coverage tiers associated with plan fees.");
                $this->_generate_missing_planfee_coveragetiers($company_id);
                $this->timer(" Creating any missing coverage tiers associated with plan fees.");

                $this->debug(" Deleting any existing LifeData records, associated with PlanFees, that were previously generated.");
                $CI->PlanFees_model->delete_plan_fee_lifedata($company_id);
                $this->timer(" Deleting any existing LifeData records, associated with PlanFees, that were previously generated.");

                $this->debug(" Deleting any existing fees that were previously generated.");
                $CI->PlanFees_model->delete_plan_fee_importdata($company_id);
                $this->timer(" Deleting any existing fees that were previously generated.");

                $this->debug(" Generating relationship data which is needed to generate fees.");
                $CI->Relationship_model->delete_relationship_data($company_id);
                $CI->Relationship_model->insert_relationship_data($company_id);
                $this->timer(" Generating relationship data which is needed to generate fees.");

                $this->debug(" Generating ASO Fee import records.");
                $aso_fees = $CI->PlanFees_model->select_company_plan_aso_fees($company_id);
                foreach($aso_fees as $fee)
                {
                    $plantype = getArrayStringValue("NewPlanType", $fee);
                    $carrier = getArrayStringValue("NewCarrier", $fee);
                    $original_carrier_id = getArrayStringValue("CarrierId", $fee);
                    $original_plantype_id = getArrayStringValue("PlanTypeId", $fee);
                    $cost = getArrayStringValue("ASOFee", $fee);

                    // Skip creating this ImportData record for the fee if the parent plan type
                    // does not support Plan Type fees.
                    $parent_plantype_supports_fee = false;
                    if ( getArrayStringValue("SupportsFee", $fee) == "t" ) $parent_plantype_supports_fee = true;
                    if ( ! $parent_plantype_supports_fee ) continue;

                    // Create it.
                    $CI->PlanFees_model->insert_importdata_plan_fee_records($company_id, $carrier, $plantype, $original_carrier_id, $original_plantype_id, $cost);
                }
                $this->timer(" Generating ASO Fee import records.");

                $this->debug(" Generating Stop Loss Fee import records.");
                $aso_fees = $CI->PlanFees_model->select_company_plan_stoploss_fees($company_id);
                foreach($aso_fees as $fee)
                {
                    $plantype = getArrayStringValue("NewPlanType", $fee);
                    $carrier = getArrayStringValue("NewCarrier", $fee);
                    $original_carrier_id = getArrayStringValue("CarrierId", $fee);
                    $original_plantype_id = getArrayStringValue("PlanTypeId", $fee);
                    $cost = getArrayStringValue("StopLossFee", $fee);

                    // Skip creating this ImportData record for the fee if the parent plan type
                    // does not support Plan Type fees.
                    $parent_plantype_supports_fee = false;
                    if ( getArrayStringValue("SupportsFee", $fee) == "t" ) $parent_plantype_supports_fee = true;
                    if ( ! $parent_plantype_supports_fee ) continue;

                    $CI->PlanFees_model->insert_importdata_plan_fee_records($company_id, $carrier, $plantype, $original_carrier_id, $original_plantype_id, $cost);
                }
                $this->timer(" Generating Stop Loss Fee import records.");

                $this->debug(" Generating the ImportLife records for any Plan Fee import records we created.");
                $CI->Life_model->insert_importlife_keys($company_id);
                $this->timer(" Generating the ImportLife records for any Plan Fee import records we created.");

                $this->debug(" Generating LifeData records for any Plan Fee import records that were created.");
                $CI->Life_model->insert_life_data($company_id, $import_date);
                $this->timer(" Generating LifeData records for any Plan Fee import records that were created.");

                $this->debug(" Cleaning up the relationship data.");
                $CI->Relationship_model->delete_relationship_data($company_id);
                $this->timer(" Cleaning up the relationship data.");



            }







        } catch(Exception $e) {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }
    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

    private function _generate_missing_planfee_coveragetiers( $company_id ) {

        // _generate_missing_planfee_coveragetiers
        //
        // When a plan fee is added via the UI, we create new plans and coverage
        // tiers specific for the fee being saved.  HOWEVER, if the customer already
        // as the plan and fees and then adds a new tier to the existing plan via the file
        // the user will never need or want to go re-save the plan in order to
        // generate the coverage tiers.
        //
        // This function will scan a company for existing plan fees.  Any fees
        // that are in place will be collected and then we will create the planfee
        // coverage tiers for those items.  The fucntion that does this is smart
        // enough to not create them if they already exist, but we will need to
        // create them if they are missing.
        // ------------------------------------------------------------------------

        $CI = $this->ci;

        // Collect all plan records that have saved fee data on them.  These are the
        // parent records.
        $parents = $CI->PlanFees_model->select_planfee_parent_records($company_id);

        $lookup = array();
        foreach($parents as $parent)
        {
            // Using the plan name, look for other records that have that same name but
            // do not have any fee data associated with them.  These are the children
            // records.  Plans that represent the fees we are going to create.
            $normalized_plan = getArrayStringValue("PlanNormalized", $parent);
            $parent_planid = getArrayStringValue("PlanId", $parent);
            if ( empty($lookup[$parent_planid]) ) $lookup[$parent_planid] = array();

            // To exclude duplicates, stash the child key in a lookup.
            $children = $CI->PlanFees_model->select_planfee_child_records($company_id, $normalized_plan);
            foreach($children as $child)
            {
                $child_planid = getArrayStringValue("Id", $child);
                $lookup[$parent_planid][$child_planid] = "TRUE";

            }

        }

        // Walk the lookup structure so that we get each unique parent and child keys
        // for fees on this customer.  Then, once we have that, create any planfee coverage
        // tiers that might be needed.
        foreach ($lookup as $parent_planid=>$children){
            foreach( $children as $child_planid=>$meh)
            {
                $CI->PlanFees_model->create_planfee_coveragetier( $company_id, $parent_planid, $child_planid);
            }

        }
    }

}
