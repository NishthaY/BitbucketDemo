<?php

class Relationship_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    function has_relationship_data( $company_id ) {

        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) throw new Exception("Missing required input import_date.");

        $file = "database/sql/relationships/ImportDataSELECT_HasRelationships.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return "f";
        if ( count($results) > 1 ) throw new Exception("Found too many results when trying to count relationship data.");
        $results = $results[0];
        $has_relationships = getArrayStringValue("HasRelationships", $results);
        return $has_relationships;
    }
    function delete_relationship_data ( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/relationships/RelationshipDataDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function insert_relationship_data ( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/relationships/RelationshipDataINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function select_relationship_data_by_carrier_and_plantype( $company_id ) {

        $import_date = GetUploadDate($company_id);

        $file = "database/sql/relationships/RelationshipDataSELECT_GroupedByCarrierPlanType.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    function update_relationship_data_for_grouped_pricing ( $company_id, $carrier_id, $plantypecode, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/relationships/RelationshipDataUPDATE_GroupedPricingModel.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getIntValue($carrier_id)
            , getStringValue($plantypecode)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function update_relationship_data_for_grouped_family_pricing ( $company_id, $import_date=null ) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        $file = "database/sql/relationships/RelationshipDataUPDATE_GroupedFamilyPricingModel.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function update_company_relationship( $company_id, $id, $code ) {

        if ( getStringValue($company_id) == "" ) return;
        if ( getStringValue($id) == "" ) return;
        if ( getStringValue($code) == "" ) return;

        $file = "database/sql/relationships/CompanyRelationshipUPDATE.sql";
        $vars = array(
            getStringValue($code)
            , getIntValue($id)
            , getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function all_relationships_mapped( $company_id ) {
        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/relationships/CompanyRelationshipSELECT_IsMapped.sql";
        $vars = array(
            getIntValue($company_id)
            ,getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) throw new Exception("Found no results when trying to decide if all relationships are mapped.");
        if ( count($results) > 1 ) throw new Exception("Found too many results when trying to decide if all relationships are mapped.");
        $results = $results[0];
        $all_mapped = getArrayStringValue("AllMapped", $results);
        return $all_mapped;

    }
    function save_new_company_relationships($company_id){

        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/relationships/CompanyRelationshipINSERT_AddNewRelationships.sql";
        $vars = array(
            getIntValue($company_id)
            ,getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }

    function select_relationships_for_import($company_id) {
        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/relationships/CompanyRelationshipSELECT_AllRelationshipsForImport.sql";
        $vars = array(
            getIntValue($company_id)
            ,getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) $results = array();
        return $results;

    }
    function select_relationship_dropdown() {
        $file = "database/sql/relationships/RelationshipSELECT.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) $results = array();
        return $results;
    }
    function select_relationship_best_guess($user_description) {

        if ( getStringValue($user_description) == "" ) return array();

        $file = "database/sql/relationships/RelationshipMappingSELECT_BestGuess.sql";
        $vars = array(
            getStringValue($user_description)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) > 1 ) return array();
        return $results[0];
    }

}


/* End of file Relationship_model.php */
/* Location: ./system/application/models/Relationship_model.php */
