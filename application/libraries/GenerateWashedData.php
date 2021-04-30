<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateWashedData extends A2PLibrary  {

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

            $this->debug(" Removing washed data for specified company and import date.");
            $CI->Wizard_model->remove_washed_records($company_id);
            $this->timer(" Removing washed data for specified company and import date.");

            $this->debug(" Inserting washed data for specified company and import date.");
            $CI->Reporting_model->insert_washed_data($company_id);
            $this->timer(" Inserting washed data for specified company and import date.");

            $this->debug(" Looking for warnings we need to show the user.");
            $CI->Reporting_model->insert_wash_warnings($company_id);
            $this->timer(" Looking for warnings we need to show the user.");


        } catch(Exception $e) {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }



}
