<?php

class UniversalEmployee_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    function get_employee_id_sample( $company_id, $import_date )
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/universalemployee/ImportDateSELECT_FirstEmployeeId.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        $results = ExecuteSQL($this->db, $file, $vars);

        $employee_id = "";
        if ( count($results) >= 1 )
        {
            $employee_id = GetArrayStringValue('EmployeeId', $results[0]);
        }
        return $employee_id;
    }
    function save_encrypted_ueid($company_id, $import_date, $encrypted, $id)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");
        if ( GetStringValue($encrypted) == "" ) throw new Exception("Missing required input encrypted");
        if ( GetStringValue($id) == "" ) throw new Exception("Missing required input id");

        $file = "database/sql/universalemployee/CompanyUniversalEmployeeUPDATE_SaveEncryptedUEID.sql";
        $vars = array(
            GetStringValue($encrypted),
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    function select_not_encrypted( $company_id )
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");

        $file = "database/sql/universalemployee/CompanyUniversalEmployeeSELECT_NotEncrypted.sql";
        $vars = array(
            GetIntValue($company_id)
        );
        $results = ExecuteSQL($this->db, $file, $vars);
        if ( count($results) === 0 ) return array();
        return $results;
    }
    function insert_not_encrypted($company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/universalemployee/CompanyUniversalEmployeeINSERT_NotEncrypted.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        CopyFromInto( $this->db, $file, $vars );
    }
    function delete($company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/universalemployee/CompanyUniversalEmployeeDELETE_ByDiscoveryDate.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_employee_id($company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/universalemployee/ImportDataUPDATE_SetUniversalEmployeeId.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function rollback_employee_id($company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/universalemployee/ImportDataUPDATE_RollbackUniversalEmployeeId.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function insert_rollback($company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/universalemployee/CompanyUniversalEmployeeRollbackINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        CopyFromInto( $this->db, $file, $vars );
    }
    function delete_rollback( $company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/universalemployee/CompanyUniversalEmployeeRollbackDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }


}


/* End of file UniversalEmployee_model.php */
/* Location: ./system/application/models/UniversalEmployee_model.php */
