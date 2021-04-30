<?php

class CompanyParentMap_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    public function select_snapshotdata($companyparent_id)
    {
        $file = "database/sql/importdata/CompanyParentImportDataSELECT_SnapshotData.sql";
        $vars = array
        (
            GetIntValue($companyparent_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }

    public function delete_importdata($companyparent_id)
    {
        $file = "database/sql/importdata/CompanyParentImportDataDELETE_ByCompanyParentId.sql";
        $vars = array
        (
            getIntValue($companyparent_id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function insert_importdata($companyparent_id, $company)
    {
        $file = "database/sql/importdata/CompanyParentImportDataINSERT.sql";
        $vars = array
        (
            GetIntValue($companyparent_id),
            GetStringValue($company)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function select_importdata($companyparent_id)
    {
        $file = "database/sql/importdata/CompanyParentImportDataSELECT_ImportedCompanies.sql";
        $vars = array
        (
            GetIntValue($companyparent_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }
    public function select_importdata_by_id($id)
    {
        $file = "database/sql/importdata/CompanyParentImportDataSELECT_ById.sql";
        $vars = array
        (
            GetIntValue($id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results!");
        return $results[0];
    }
    public function delete_mapping_by_id($id)
    {
        $file = "database/sql/mapping/CompanyParentMapCompanyDELETE_ById.sql";
        $vars = array
        (
            GetIntValue($id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function ignore_mapping_by_id($id, $ignored=true)
    {
        if ( GetStringValue($ignored) === '' ) $ignored = true;

        $file = "database/sql/mapping/CompanyParentMapCompanyUPDATE_IgnoreById.sql";
        $vars = array
        (
            $ignored ? 't' : 'f',
            GetIntValue($id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function delete_mapping_by_companyparent_id($companyparent_id)
    {
        $file = "database/sql/mapping/CompanyParentMapCompanyDELETE_ByCompanyParentId.sql";
        $vars = array
        (
            GetIntValue($companyparent_id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function update_mapping_remove_ignored_mappings($companyparent_id)
    {
        $file = "database/sql/mapping/CompanyParentMapCompanyUPDATE_ByCompanyParentIdIgnoredMappings.sql";
        $vars = array
        (
            GetIntValue($companyparent_id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function insert_mapping($companyparent_id, $normalized, $user_desc, $company_id, $ignored)
    {
        if ( GetStringValue($ignored) === '' ) $ignored = false;

        $file = "database/sql/mapping/CompanyParentMapCompanyINSERT.sql";
        $vars = array
        (
            GetIntValue($companyparent_id),
            GetStringValue($normalized),
            GetStringValue($user_desc),
            GetIntValue($company_id),
            $ignored ? 't' : 'f'
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function update_mapping($companyparent_id, $normalized, $user_desc, $company_id, $ignored)
    {
        if ( GetStringValue($ignored) === '' ) $ignored = false;

        $file = "database/sql/mapping/CompanyParentMapCompanyUPDATE.sql";
        $vars = array
        (
            GetIntValue($company_id),
            GetStringValue($user_desc),
            $ignored ? 't' : 'f',
            GetIntValue($companyparent_id),
            GetStringValue($normalized)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function select_mapping($companyparent_id, $normalized)
    {
        $file = "database/sql/mapping/CompanyParentMapCompanySELECT.sql";
        $vars = array
        (
            GetIntValue($companyparent_id),
            GetStringValue($normalized)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }
    public function select_mappings($companyparent_id)
    {
        $file = "database/sql/mapping/CompanyParentMapCompanySELECT_ByCompanyParentId.sql";
        $vars = array
        (
            GetIntValue($companyparent_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        return $results;
    }
    public function count_mappings($companyparent_id)
    {
        $results = $this->select_mappings($companyparent_id);
        return count($results);
    }
    public function exists_mapping($companyparent_id, $normalized)
    {
        $file = "database/sql/mapping/CompanyParentMapCompanySELECT.sql";
        $vars = array
        (
            GetIntValue($companyparent_id),
            GetStringValue($normalized)
        );
        return GetDBExists($this->db, $file, $vars);
    }
    public function is_mapping_ignored($companyparent_id, $normalized)
    {
        $results = $this->select_mapping($companyparent_id, $normalized);
        if ( count($results) > 1 ) return true;

        $results = $results[0];
        if ( GetArrayStringValue('Ignored', $results) === 'f' ) return false;
        return true;
    }
    public function upsert_mapping($companyparent_id, $normalized, $user_desc, $company_id, $ignored)
    {
        if ( $this->exists_mapping($companyparent_id, $normalized) )
        {
            // UPDATE
            $this->update_mapping($companyparent_id, $normalized, $user_desc, $company_id, $ignored);
        }
        else
        {
            // INSERT
            $this->insert_mapping($companyparent_id, $normalized, $user_desc, $company_id, $ignored);
        }
    }


}


/* End of file CompanyParent_model.php */
/* Location: ./system/application/models/CompanyParent_model.php */
