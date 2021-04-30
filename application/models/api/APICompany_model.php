<?php

class APICompany_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    /**
     * company_create
     *
     * API Interface function to create a company object for A2P.
     *
     * @param $inputs
     * @return APIMessage
     * @throws APIException
     */
    function company_create($inputs)
    {
        $audit = array();
        try
        {
            $required = array( 'name' );
            $missing = CheckRequired($required, $inputs);
            if ( $missing !== TRUE ) throw new APIException("missing required inputs", 400, array('fields' => $missing));

            // Validate
            // Nothing to validate beyond required inputs.

            // Clean inputs.
            $name = trim(getArrayStringValue("name", $inputs));
            $address = trim(getArrayStringValue("address", $inputs));
            $city = trim(getArrayStringValue("city", $inputs));
            $state = trim(getArrayStringValue("state", $inputs));
            $postal = trim(getArrayStringValue("postal", $inputs));
            $identifier = trim(getArrayStringValue("parent_identifier", $inputs));
            $identifier_type = trim(getArrayStringValue("parent_identifier_type", $inputs));

            if ( ! IsCompanyNameAvailable( $name ) )
            {
                throw new APIException("Business with that name already in use.");
            }
            if ( ! IsCompanyParentNameAvailable( $name ) )
            {
                throw new APIException("Business with that name already in use.");
            }

            // Create company
            $company_id = $this->Company_model->create_company( $name, $address, $city, $state, $postal );
            $audit[] = "Created a new company with the id of [{$company_id}].";



            // Link company to parent.
            if ( $identifier_type == "companyparent" )
            {
                if ( ! $this->Company_model->is_company_linked_to_parent( $company_id, $identifier ) )
                {
                    $this->Company_model->link_company_to_parent( $company_id, $identifier );
                    $audit[] = "Linked company [{$company_id}] to [{$identifier_type}] with id [{$identifier}].";
                }
            }

            try
            {
                // Create a custom encryption key for this company.
                CreateCompanyEncryptionKey($company_id);
                $audit[] = "Created encryption key.";
            }
            catch(Exception $e)
            {
                LogIt('Error:'.__FUNCTION__, 'Unable to create a new company', $e->getMessage());
                $this->Company_model->delete_company($company_id);
                throw new APIException("Unable to create new security token.  Please contact support for assistance.", 400);
            }

            $results = array( 'company_id' => $company_id );
            $message = new APIMessage(200, $results );
            $message->audit = $audit;
            return $message;
        }
        catch(APIException $e)
        {
            $message = new APIErrorMessage($e->getCode(), $e->getMessage());
            $message->inputs = $inputs;
            $message->function = __FUNCTION__;
            $message->audit = $audit;
            return $message;
        }
        catch(Exception $e)
        {
            $message = new APIErrorMessage(500, "Unexpected situation: " . __FUNCTION__ . ' has failed.', $e->getMessage());
            $message->inputs = $inputs;
            $message->function = __FUNCTION__;
            $message->audit = $audit;
            return $message;
        }




    }


    /**
     * _create_customer
     *
     * Create a new company.  Links company to related object
     * if an identifier is specified and creates an encryption key.
     * @param $inputs
     * @throws APIException
     */
    private function _create_customer($inputs)
    {

    }

}


/* End of file Company_model.php */
/* Location: ./system/application/models/Company_model.php */
