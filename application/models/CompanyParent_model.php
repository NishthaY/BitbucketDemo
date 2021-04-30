<?php

class CompanyParent_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    public function update_companyparent_encryption_key( $companyparent_id, $encryption_key )
    {
        $file = "database/sql/companyparent/CompanyParentUPDATE_EncryptionKey.sql";
        $vars = array(
            GetStringValue($encryption_key),
            GetIntValue($companyparent_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function select_companyparent_encryption_key( $companyparent_id )
    {
        $file = "database/sql/companyparent/CompanyParentSELECT_EncryptionKey.sql";
        $vars = array(
            getIntValue($companyparent_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return "";
        return GetArrayStringValue("CompanyParentEncryptionKey", $results[0]);
    }
    public function get_companyparent_by_company($company_id)
    {
        $file = "database/sql/companyparent/CompanyParentSELECT_ByCompanyId.sql";
        $vars = array(
            GetIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();
        if ( count($results) > 1 ) throw new Exception("Found many parent companies, expected one.");
        return $results[0];
        return $results[0];
    }
    public function get_companies_by_parent( $company_parent_id=null )
    {
        if ( getStringValue($company_parent_id) === '' ) $company_parent_id = GetSessionValue("companyparent_id");
        $file = "database/sql/companyparent/CompanySELECT_ByParentId.sql";
        $vars = array(
            getIntValue($company_parent_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();

        $output = array();
        foreach($results as $company)
        {
            $company["is_child"] = 't';
            $output[] = $company;
        }
        return $output;
    }
    public function select_recent_companies( $company_parent_id ) {

        $user_id = GetSessionValue("user_id");

        $file = "database/sql/companyparent/CompanySELECT_MostRecentFirst.sql";
        $vars = array(
            $user_id
            , getIntValue($company_parent_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();

        $output = array();
        foreach($results as $company)
        {
            $company["is_child"] = 't';
            $output[] = $company;
        }
        return $output;

    }
    public function select_recent_parents( $companyparent_id ) {

        $user_id = GetSessionValue("user_id");

        $file = "database/sql/companyparent/CompanySELECT_MostRecentFirst.sql";
        $vars = array(
            $user_id
        , getIntValue($company_parent_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();

        $output = array();
        foreach($results as $company)
        {
            $company["is_child"] = 't';
            $output[] = $company;
        }
        return $output;

    }
    public function count_parents() {
        $file = "database/sql/companyparent/CompanyParentCOUNT.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 )
        {
            $results = $results[0];
            return getArrayStringValue("Count", $results);
        }
        throw new Exception("Unexpected situation.");
    }
    public function get_all_parents( ) {
        $file = "database/sql/companyparent/CompanyParentSELECT.sql";
        $vars = array( );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();

        return $results;
    }
    public function create_companyparent( $name, $address, $city, $state, $postal, $seats) {
        $file = "database/sql/companyparent/CompanyParentINSERT.sql";
        $vars = array(
            getStringValue($name)
            , getStringValue($address)
            , getStringValue($city)
            , getStringValue($state)
            , getStringValue($postal)
            , getIntValue($seats)
        );
        ExecuteSQL( $this->db, $file, $vars );

        $parents = $this->get_parent_by_name( $name );
        if ( empty($parents) ) throw new Exception("could not create parent!");
        $parent = $parents[0];

        // Audit this transaction.
        $payload = array();
        $payload = array_merge($payload, array('CompanyParentId'=>GetArrayStringValue('Id', $parent)));
        $payload = array_merge($payload, array('CompanyParentName' => GetArrayStringValue('Name', $parent)));
        AuditIt("Parent created.", $payload);

        return getArrayIntValue("Id", $parent);
    }
    public function get_parent_by_name( $company_name ) {
        $file = "database/sql/companyparent/CompanyParentSELECT_ByName.sql";
        $vars = array(
            getStringValue($company_name)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();
        return $results;
    }
    public function get_companyparent( $id ) {
        $file = "database/sql/companyparent/CompanyParentSELECT_ById.sql";
        $vars = array(
            getIntValue($id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many result for company parent [{$id}]");
        if ( count($results) == 0) return array();
        return $results[0];

    }
    public function update_companyparent( $name, $address, $city, $state, $postal, $seats, $id) {
        $file = "database/sql/companyparent/CompanyParentUPDATE.sql";
        $vars = array(
            getStringValue($name)
            , getStringValue($address)
            , getStringValue($city)
            , getStringValue($state)
            , getStringValue($postal)
            , getIntValue($id)
            , getIntValue($seats)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $parent = $this->CompanyParent_model->get_companyparent($id);
        $payload = array();
        $payload = array_merge($payload, array('CompanyParentId'=>GetArrayStringValue('Id', $parent)));
        $payload = array_merge($payload, array('CompanyParentName' => GetArrayStringValue('Name', $parent)));
        $payload = array_merge($payload, array('CompanyParentAddress' => GetArrayStringValue('Address', $parent)));
        $payload = array_merge($payload, array('CompanyParentCity' => GetArrayStringValue('City', $parent)));
        $payload = array_merge($payload, array('CompanyParentState' => GetArrayStringValue('State', $parent)));
        $payload = array_merge($payload, array('CompanyParentPostal' => GetArrayStringValue('Postal', $parent)));
        $payload = array_merge($payload, array('CompanyParentSeats' => GetArrayStringValue('Seats', $parent)));
        AuditIt("Parent updated.", $payload);
    }
    public function enable_companyparent ( $id ) {
        $file = "database/sql/companyparent/CompanyParentUPDATE_Enable.sql";
        $vars = array(
            getIntValue($id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $parent = $this->CompanyParent_model->get_companyparent($id);
        $payload = array();
        $payload = array_merge($payload, array('CompanyParentId'=>GetArrayStringValue('Id', $parent)));
        $payload = array_merge($payload, array('CompanyParentName' => GetArrayStringValue('Name', $parent)));
        AuditIt("Parent enabled.", $payload);
    }
    public function disable_companyparent( $id ) {
        $file = "database/sql/companyparent/CompanyParentUPDATE_Disable.sql";
        $vars = array(
            getIntValue($id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        $parent = $this->CompanyParent_model->get_companyparent($id);
        $payload = array();
        $payload = array_merge($payload, array('CompanyParentId'=>GetArrayStringValue('Id', $parent)));
        $payload = array_merge($payload, array('CompanyParentName' => GetArrayStringValue('Name', $parent)));
        AuditIt("Parent disabled.", $payload);
    }
    public function get_all_companyparents( ) {
        $file = "database/sql/companyparent/CompanyParentSELECT.sql";
        $vars = array(
            getIntValue( GetSessionValue("companyparent_id") )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();

        return $results;
    }
    public function get_used_seats( ) {
        $file = "database/sql/companyparent/CompanyParentSELECT_UsedSeats.sql";
        $vars = array(
            getIntValue( GetSessionValue("companyparent_id") )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) throw new Exception("Unexpected results from database.");
        if ( count($results) != 1) throw new Exception("Unexpected results from database.");

        return getArrayIntValue("UsedSeats", $results[0]);
    }



    // Company Parent Preferences.
    public function get_companyparent_preferences ( $company_id, $group ) {
        $file = "database/sql/companyparent/CompanyParentPreferenceSELECT_ByGroup.sql";
        $vars = array(
            getIntValue($company_id),
            ( $group == null ? null : getStringValue($group) )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        return $results;
    }
    public function get_companyparent_preference( $company_parent_id, $group, $group_code ) {
        $file = "database/sql/companyparent/CompanyParentPreferenceSELECT.sql";
        $vars = array(
            getIntValue($company_parent_id)
            , ( $group == null ? null : getStringValue($group) )
            , ( $group_code == null ? null : getStringValue($group_code) )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many company parent preferences.  Expected one or none.");
        return $results[0];
    }
    public function save_companyparent_preference(  $companyparent_id, $group, $group_code, $value  ) {
        $pref = $this->get_companyparent_preference($companyparent_id, $group, $group_code);
        if ( empty($pref) ) {
            $this->insert_companyparent_preference($companyparent_id, $group, $group_code, $value);
        }else{
            $this->update_companyparent_preference($companyparent_id, $group, $group_code, $value);
        }
    }
    public function insert_companyparent_preference( $companyparent_id, $group, $group_code, $value ) {
        $file = "database/sql/companyparent/CompanyParentPreferenceINSERT.sql";
        $vars = array(
            getIntValue($companyparent_id)
            , ( $group == null ? null : getStringValue($group) )
            , ( $group_code == null ? null : getStringValue($group_code) )
            , ( $value == null ? null : getStringValue($value) )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update_companyparent_preference( $companyparent_id, $group, $group_code, $value ) {
        $file = "database/sql/companyparent/CompanyParentPreferenceUPDATE.sql";
        $vars = array(
            getStringValue($value)
            , getIntValue($companyparent_id)
            , getStringValue($group)
            , getStringValue($group_code)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function remove_companyparent_preference( $companyparent_id, $group, $group_code ) {
        $file = "database/sql/companyparent/CompanyParentPreferenceDELETE.sql";
        $vars = array(
            getIntValue($companyparent_id),
            getStringValue($group),
            getStringValue($group_code)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function remove_companyparent_preference_group_code( $companyparent_id, $group, $value ) {
        $file = "database/sql/companyparent/CompanyParentPreferenceDELETE_ByGroupCode.sql";
        $vars = array(
            getIntValue($companyparent_id),
            getStringValue($group),
            getStringValue($value)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function remove_companyparent_preference_group( $companyparent_id, $group ) {
        $file = "database/sql/companyparent/CompanyParentPreferenceDELETE_ByGroup.sql";
        $vars = array(
            getIntValue($companyparent_id),
            getStringValue($group)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }




    public function delete_companyparent($companyparent_id)
    {
        if ( GetStringValue($companyparent_id) === '' ) return;

        $file = "database/sql/companyparent/CompanyParentDELETE.sql";
        $vars = array(
            GetIntValue($companyparent_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function hard_delete_companyparent($companyparent_id, $authenticated_user_id, $verbose=false)
    {

        // You can find all the tables in the database that have a CompanyParentId
        // with the following query.  You will need to update the list of tables
        // over time below.
        //
        // select c.relname
        // from pg_class as c
        // inner join pg_attribute as a on a.attrelid = c.oid
        // where a.attname = 'CompanyParentId' and c.relkind = 'r'
        // order by relname asc


        if ( getStringValue($companyparent_id) === '' ) throw new Exception("Missing require input companyparent_id");

        // Grab this data before we delete it.
        $company = $this->get_companyparent($companyparent_id);

        $tables = array();
        $tables[] = "Audit";
        $tables[] = "CompanyParentBeneficiaryMap";
        $tables[] = "CompanyParentBestMappedColumn";
        $tables[] = "CompanyParentCompanyRelationship";
        $tables[] = "CompanyParentFeature";
        $tables[] = "CompanyParentFileTransfer";
        $tables[] = "CompanyParentImportData";
        $tables[] = "CompanyParentMapCompany";
        $tables[] = "CompanyParentMappingColumn";
        $tables[] = "CompanyParentPreference";
        $tables[] = "Log";
        $tables[] = "UserCompanyParentRelationship";

        $template = 'delete from "{TABLE}" where "CompanyParentId" = ?';
        $vars = array(
            getIntValue($companyparent_id)
        );

        foreach( $tables as $table )
        {
            if ( $verbose ) print "Removing parent data from table {$table}.\n";
            $replacefor = array();
            $replacefor["{TABLE}"] = $table;
            ExecuteSQL( $this->db, $template, $vars, $replacefor );
        }

        if ( $verbose ) print "Removing parent data from table CompanyParent.\n";
        $sql = 'delete from "CompanyParent" where "Id" = ?';
        ExecuteSQL( $this->db, $sql, $vars );

        // Audit this action has completed.
        AuditIt("Delete parent and parent data.", $company, $authenticated_user_id, A2P_COMPANY_ID);
    }
    public function disable_custom_normalization($companyparent_id, $column_code)
    {
        $file = "database/sql/mapping/CompanyMappingColumnUPDATE_CustomNormalizationByCompanyParent.sql";
        $vars = array(
            null,
            GetStringValue($column_code),
            GetIntValue($companyparent_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function enable_custom_normalization($companyparent_id, $column_code, $rules)
    {
        $file = "database/sql/mapping/CompanyMappingColumnUPDATE_CustomNormalizationByCompanyParent.sql";
        $vars = array(
            GetStringValue(json_encode($rules)),
            GetStringValue($column_code),
            GetIntValue($companyparent_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }




}


/* End of file CompanyParent_model.php */
/* Location: ./system/application/models/CompanyParent_model.php */
