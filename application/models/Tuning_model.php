<?php

class Tuning_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    public function work_mem()
    {
        $file = "database/sql/tuning/workmem.sql";
        $vars = array( );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) !== 1 ) return "";
        $results = $results[0];
        return GetArrayStringValue("work_mem", $results);
    }
    public function set_work_mem( $value )
    {
        $file = "database/sql/tuning/set_workmem.sql";
        $vars = array(
            getStringValue($value)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function vacuum($force=false)
    {
        if ( $force || strtoupper(GetAppOption("VACUUM_ENABLED")) === 'TRUE' )
        {
            $file = "VACUUM";
            $vars = array( );
            ExecuteSQL( $this->db, $file, $vars );
        }
     }
    public function vacuum_full($force=false)
    {
        if ( $force || strtoupper(GetAppOption("VACUUM_ENABLED")) === 'TRUE' )
        {
            $file = "VACUUM FULL";
            $vars = array( );
            ExecuteSQL( $this->db, $file, $vars );
        }
    }
    public function analyse( $table_name )
    {
        $file = "ANALYSE " . $table_name;
        $vars = array();
        ExecuteSQL($this->db, $file, $vars);
    }

}


/* End of file Tuning_model.php */
/* Location: ./system/application/models/Tuning_model.php */
