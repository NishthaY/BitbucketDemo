<?php

class AppOption_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    public function exists( $key ) {

        if ( getStringValue($key) == "" ) throw new Exception("Missing required value key");

        $file = "database/sql/appoption/AppOptionEXISTS.sql";
        $vars = array(
            getStringValue($key)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) throw new Exception("Found too few results when looking for an application option.");
        if ( count($results) > 1 )  throw new Exception("Found too many results when looking for an application option.");
        $results = $results[0];
        if ( getArrayStringValue("Exists", $results) == "t" ) return true;
        if ( getArrayStringValue("Exists", $results) == "f" ) return false;
        throw new Exception("Unexpected results looking for an application option.");

    }
    public function select( $key ) {

        if ( getStringValue($key) == "" ) throw new Exception("Missing required value key");

        $file = "database/sql/appoption/AppOptionSELECT.sql";
        $vars = array(
            getStringValue($key)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return "";
        if ( count($results) > 1 ) throw new Exception("Found too many results when looking for application option.");
        $results = $results[0];
        return getArrayStringValue("Value", $results);
    }
    public function delete( $key ) {

        if ( getStringValue($key) == "" ) throw new Exception("Missing required value key");

        $file = "database/sql/appoption/AppOptionDELETE.sql";
        $vars = array(
            getStringValue($key)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function upsert( $key, $value ) {

        if ( getStringValue($key) == "" ) throw new Exception("Missing required value key");

        if ( $this->exists($key) )
        {
            $this->update($key,$value);
        }
        else
        {
            $this->insert($key,$value);
        }
    }
    public function insert( $key, $value ) {

        if ( getStringValue($key) == "" ) throw new Exception("Missing required value key");

        $file = "database/sql/appoption/AppOptionINSERT.sql";
        $vars = array(
            getStringValue($key)
            , getStringValue($value)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update( $key, $value ) {

        if ( getStringValue($key) == "" ) throw new Exception("Missing required value key");

        $file = "database/sql/appoption/AppOptionUPDATE.sql";
        $vars = array(
            getStringValue($value)
            , getStringValue($key)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }


}


/* End of file AppOption_model.php */
/* Location: ./system/application/models/AppOption_model.php */
