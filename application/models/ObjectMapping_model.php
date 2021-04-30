<?php

class ObjectMapping_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    public function get_mapping_lookup( $column_code )
    {
        $file = "database/sql/objectmapping/ObjectMappingSELECT_AllByCode.sql";
        $vars = array(
            GetStringValue($column_code),
            GetStringValue($column_code)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }
    public function select_allowed_values( $id )
    {
        $file = "database/sql/objectmapping/ObjectMappingSELECT_ForDownload.sql";
        $vars = array(
            GetIntValue($id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        return $results;
    }
    public function is_valid_object_type( $type )
    {
        $file = "database/sql/objectmapping/ObjectMappingPropertySELECT_ByCode.sql";
        $vars = array(
            GetStringValue($type)
        );
        return GetDBExists( $this->db, $file, $vars );
    }
    public function get_mapping_properties( $id )
    {
        $file = "database/sql/objectmapping/ObjectMappingPropertySELECT_ById.sql";
        $vars = array(
            GetIntValue($id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) > 1 ) throw new Exception("Found multiple objects with the same id.");
        return $results[0];
    }
    public function get_mapping_properties_by_code( $code )
    {
        $file = "database/sql/objectmapping/ObjectMappingPropertySELECT_ByCode.sql";
        $vars = array(
            GetStringValue($code)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) > 1 ) throw new Exception("Found multiple objects with the same code.");
        return $results[0];
    }
    public function get_mapping($type, $input, $case_sensitive=true)
    {
        if ( $case_sensitive )
        {
            $file = "database/sql/objectmapping/ObjectMappingSELECT_CaseSensitive.sql";
            $vars = array(
                GetStringValue($type),
                GetStringValue($input)
            );
            $results = GetDBResults( $this->db, $file, $vars );
            if ( count($results) == 0) return "";
            if ( count($results) > 1 ) throw new Exception("Found many results for object[{$type}], input[{$input}].  Expected exactly one.");
            return GetArrayStringValue("Output", $results[0]);

        }
        else
        {
            $file = "database/sql/objectmapping/ObjectMappingSELECT_CaseInsensitive.sql";
            $vars = array(
                GetStringValue($type),
                GetStringValue($input)
            );
            $results = GetDBResults( $this->db, $file, $vars );
            if ( count($results) == 0) return "";
            if ( count($results) > 1 ) throw new Exception("Found many results for object[{$type}], input[{$input}].  Expected exactly one.");
            return GetArrayStringValue("Output", $results[0]);
        }
        return "";
    }

}


/* End of file ObjectMapping_model.php */
/* Location: ./system/application/models/ObjectMapping_model.php */
