<?php

class Beneficiary_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    public function beneficiary_importdata_remove( $company_id, $import_date="" )
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);

        $file = "database/sql/beneficiary/CompanyBeneficiaryImportDELETE_ByCompanyId.sql";
        $vars = array(
            getStringValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function boolean_is_beneficiary_map( $identifier, $identifier_type, $column_code, $token )
    {
        if ( $identifier_type === 'company' ) $file = "database/sql/beneficiary/CompanyBeneficiaryMapSELECT_ByNormalized.sql";
        else if ( $identifier_type === 'companyparent' ) $file = "database/sql/beneficiary/CompanyParentBeneficiaryMapSELECT_ByNormalized.sql";
        else throw new Exception("Unknown identifier type.");

        $vars = array(
            getStringValue($identifier),
            getStringValue($column_code),
            getStringValue($token)
        );
        return GetDBExists( $this->db, $file, $vars );
    }

    public function list_maps_for_column( $identifier, $identifier_type, $column_code )
    {
        if ( $identifier_type === 'company' ) $file = "database/sql/beneficiary/CompanyBeneficiaryMapSELECT_DistinctMaps.sql";
        else if ( $identifier_type === 'companyparent' ) $file = "database/sql/beneficiary/CompanyParentBeneficiaryMapSELECT_DistinctMaps.sql";
        else throw new Exception("Unknown identifier type.");

        $vars = array(
            getStringValue($identifier),
            getStringValue($column_code)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();

        $output = array();
        foreach($results as $item)
        {
            $output[] = GetArrayStringValue('NormalizedToken', $item);
        }
        return $output;
    }


}


/* End of file Beneficiary_model.php */
/* Location: ./system/application/models/Beneficiary_model.php */
