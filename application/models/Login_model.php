<?php

class Login_model extends CI_Model {

    private $db;

    /**
     * Login_model constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    public function has_two_factor_code_expired( $user_id )
    {
        $file = 'database/sql/login/LoginSELECT_HasCodeExpired.sql';
        $vars = array(
            getIntValue($user_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if (count($results) != 1) throw new Exception('Found incorrect number of records.');
        $results = $results[0];
        $expired = GetArrayStringValue("CodeExpired", $results);
        if ( $expired === 't' ) return TRUE;
        if ( $expired === 'f' ) return FALSE;
        throw new Exception("Unexpected results when checking code expiration date.");
    }

    /**
     * Returns a row of data from the Login table by UserId.
     *
     * @param $user_id
     * @return array
     * @throws Exception
     */
    public function get_login_details($user_id)
    {
        $file = 'database/sql/login/LoginSELECT.sql';
        $vars = array(
            getIntValue($user_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if (count($results) === 0) return array();
        if (count($results) > 1) throw new Exception('Found too many records, expected no more than one.');

        return $results[0];
    }

    /**
     * Update the phone number field found on a specific row in
     * the Login table.
     *
     * @param $user_id
     * @param $phone
     */
    public function update_phone($user_id, $phone) {
        $file = 'database/sql/login/LoginUPDATE_Phone.sql';
        $vars = array(
            getStringValue($phone) === '' ? null : getStringValue($phone)
        , getIntValue($user_id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }

    /**
     * Update the hash value for a specific user in the Login table.
     *
     * @param $user_id
     * @param $hash
     */
    public function update_hash($user_id, $hash) {
        $file = 'database/sql/login/LoginUPDATE_Hash.sql';
        $vars = array(
            getStringValue($hash) === '' ? null : getStringValue($hash)
            , getIntValue($user_id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }

    /**
     * Insert a new record into the Login table with the specified data.
     *
     * @param $user_id
     * @param bool $enabled
     * @param null $phone
     * @param null $hash
     */
    public function insert_details($user_id, $enabled=true, $phone=null, $hash=null)
    {
        // Normalized and default enabled.
        if ( getStringValue($enabled) === '' ) $enabled = true;
        if ( getStringValue($enabled) === 'TRUE' ) $enabled = true;
        if ( getStringValue($enabled) === 'FALSE' ) $enabled = false;
        if ( ! is_bool($enabled) ) $enabled = true;

        $file = 'database/sql/login/LoginINSERT.sql';
        $vars = array(
            getIntValue($user_id)
            , getStringValue($enabled) === 'FALSE' ? 'f' : 't'
            , getStringValue($phone) === '' ? null : getStringValue($phone)
            , getStringValue($hash) === '' ? null : getStringValue($hash)
        );
        ExecuteSQL($this->db, $file, $vars);

    }


}


/* End of file Login_model.php */
/* Location: ./system/application/models/Login_model.php */
