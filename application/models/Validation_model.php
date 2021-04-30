<?php

class Validation_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    public function is_upload_file_valid( $identifier, $identifier_type ) {
        if ( $identifier_type === 'company') $file = "database/sql/validation/ValidationErrorCOUNT_ByCompany.sql";
        else if ( $identifier_type === 'companyparent') $file = "database/sql/validation/ValidationErrorCOUNT_ByCompanyParent.sql";
        else throw new Exception(__FUNCTION__ . ": Unknown identifier type.");

        $vars = array(
            getIntValue($identifier)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) > 1) throw new Exception("Got too many results when running " . __FUNCTION__ );
        if ( count($results) == 0 ) throw new Exception("Got too few results when running " . __FUNCTION__ );
        $results = $results[0];

        $result = getArrayStringValue("valid", $results);
        if ( $result == "t") return true;
        if ( $result == "f" ) return false;
        throw new Exception("Got unexpected results when running " . __FUNCTION__ );
    }
    public function count_validation_errors( $identifier, $identifier_type )
    {
        if ( $identifier_type === 'company') $file = "database/sql/validation/ValidationErrorCOUNT_ByCompany.sql";
        else if ( $identifier_type === 'companyparent') $file = "database/sql/validation/ValidationErrorCOUNT_ByCompanyParent.sql";
        else throw new Exception(__FUNCTION__ . ": Unknown identifier type of [".GetStringValue($identifier_type)."].");

        // If we don't have file, there are no errors.
        if ( GetStringValue($file) === '' ) return 0;

        $vars = array(
            getIntValue($identifier)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) throw new Exception("Expected exactly one result.  Found none.");
        if ( count($results) > 1 ) throw new Exception("Expected exactly one result.  Found too many.");
        $results = $results[0];
        return getArrayIntValue("count", $results);

    }
    public function get_validation_errors( $identifier, $identifier_type, $starting_index, $max_rows )
    {
        if ( $identifier_type === 'company' ) $file = "database/sql/validation/ValidationErrorSELECT_ByCompanyAndId.sql";
        else if ( $identifier_type === 'companyparent' ) $file = "database/sql/validation/ValidationErrorSELECT_ByCompanyParentAndId.sql";
        else throw new Exception(__FUNCTION__ . ": Unknown identifier type.");

        $vars = array(
            getIntValue($identifier),
            getIntValue($starting_index),
            getIntValue($max_rows)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        return $results;
    }
    public function get_validation_error( $identifier, $identifier_type, $row_no, $column_name )
    {
        if ( $identifier_type === 'company' ) $file = "database/sql/validation/ValidationErrorSELECT_ByCompany.sql";
        else if ( $identifier_type === 'companyparent' ) $file = "database/sql/validation/ValidationErrorSELECT_ByCompanyParent.sql";
        else throw new Exception(__FUNCTION__ . ": Unknown identifier type.");

        $vars = array(
            getIntValue($identifier),
            getIntValue($row_no),
            getStringValue($column_name)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many errors.  Expected one.");
        return $results[0];
    }
    public function delete_validation_errors( $identifier, $identifier_type )
    {
        if ( $identifier_type === 'company' ) $file = "database/sql/validation/ValidationErrorDELETE_ByCompany.sql";
        else if ( $identifier_type === 'companyparent' ) $file = "database/sql/validation/ValidationErrorDELETE_ByCompanyParent.sql";
        else throw new Exception(__FUNCTION__ . ": Unknown identifier type.");

        $vars = array(
            getIntValue($identifier)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function write_validation_error( $row_number, $short_code, $message, $column_name, $column_no, $upload_key, $identifier, $identifier_type ) {

        if ( $identifier_type === 'company' ) $file = "database/sql/validation/ValidationErrorINSERT_ByCompany.sql";
        else if ( $identifier_type === 'companyparent' ) $file = "database/sql/validation/ValidationErrorINSERT_ByCompanyParent.sql";
        else throw new Exception("Unknown identifier_type.");

        $vars = array(
            getStringValue($upload_key),
            getIntValue($identifier),
            getIntValue($row_number),
            getStringValue($short_code),
            getStringValue($message),
            getStringValue($column_name),
            getIntValue($column_no)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

}

/* End of file Validation_model.php */
/* Location: ./system/application/models/Validation_model.php */
