<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateRelationshipData extends A2PLibrary {

    function __construct( $debug=false )
    {
        parent::__construct($debug);
    }

    public function execute( $company_id, $user_id=null )
    {
        try {

            parent::execute($company_id);

            $CI = $this->ci;

            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");

            // What is our import date?
            $import_date = GetUploadDate($company_id);
            if ( $import_date == "" ) throw new Exception("Invalid import_date.  How is that possible?");

            $this->debug(" ImportDate: [{$import_date}]");
            $this->debug(" ComapnyId:  [{$company_id}]");

            $this->debug(" Removing relationship data for specified company and import date.");
            $CI->Relationship_model->delete_relationship_data($company_id);
            $this->timer(" Removing relationship data for specified company and import date.");

            if ( HasRelationship($company_id) )
            {
                $this->debug(" Looking for relationships and adding them to RelationshipData table.");
                $CI->Relationship_model->insert_relationship_data($company_id);
                $this->timer(" Looking for relationships and adding them to RelationshipData table.");

                // What is our dependent pricing model?
                $pricing_model = $CI->Company_model->get_company_preference($company_id, "relationships", "dependent_pricing_model");
                ( empty($pricing_model) ) ? $pricing_model = "" : $pricing_model = getArrayStringValue("value", $pricing_model);


                if ( strtoupper($pricing_model) == "GROUPED" )
                {
                    // Collect the unique CarrierId/PlanTypeCodes for our data.  We will need to apply the relationship
                    // grouped logic for all dependents per carrierid/plantypecode.
                    $this->debug(" Looking for unique carrier/plantypecode for the dependents.");
                    $unique_plantypes = $CI->Relationship_model->select_relationship_data_by_carrier_and_plantype($company_id);
                    if ( ! empty($unique_plantypes) )
                    {
                        foreach($unique_plantypes as $unique_plantype)
                        {
                            $carrier_id = getArrayStringValue("CarrierId", $unique_plantype);
                            $plantypecode = getArrayStringValue("PlanTypeCode", $unique_plantype);
                            $this->debug(" Updating relationship data for grouped pricing model for dependents in carrier[{$carrier_id}] and plantype[{$plantypecode}].");
                            $CI->Relationship_model->update_relationship_data_for_grouped_pricing($company_id, $carrier_id, $plantypecode);
                            $this->timer(" Updating relationship data for grouped pricing model for dependents in carrier[{$carrier_id}] and plantype[{$plantypecode}].");
                        }
                    }

                }
                else if ( strtoupper($pricing_model) === 'GROUPED_FAMILY' )
                {
                    $this->debug(" Updating relationship data for grouped family pricing model.");
                    $CI->Relationship_model->update_relationship_data_for_grouped_family_pricing($company_id);
                    $this->timer(" Updating relationship data for grouped family pricing model.");
                }
                else
                {
                    $this->debug(" No changes made to dependent pricing as the pricing model was not GROUPED.");
                }

            }




        } catch(Exception $e) {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }


}
