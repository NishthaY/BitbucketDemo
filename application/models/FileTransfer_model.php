<?php

class FileTransfer_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    public function get_file_transfer_by_companyparent_id($companyparent_id) {
        $file = 'database/sql/filetransfer/CompanyParentFileTransferSELECT_ByCompanyParentId.sql';
        $vars = array(
            GetIntValue($companyparent_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results.");
        return $results[0];
    }
    public function upsert_companyparent_file_transfer($companyparent_id, $hostname, $username, $destination, $port, $encrypted_password, $encrypted_ssh_key)
    {
        $file = "";
        $vars = array();

        $transfer = $this->get_file_transfer_by_companyparent_id($companyparent_id);
        if ( empty($transfer) )
        {
            // INSERT
            $file = 'database/sql/filetransfer/CompanyParentFileTransferINSERT_ByCompanyParentId.sql';
            $vars = array(
                GetIntValue($companyparent_id),
                GetStringValue($username),
                GetStringValue($hostname),
                GetIntValue($port),
                GetStringValue($destination),
                GetStringValue($encrypted_password),
                GetStringValue($encrypted_ssh_key) === '' ? null : GetStringValue($encrypted_ssh_key)
            );
        }
        else
        {
            // UPDATE
            $file = 'database/sql/filetransfer/CompanyParentFileTransferUPDATE_ByCompanyParentId.sql';
            $vars = array(
                GetStringValue($hostname),
                GetStringValue($username),
                GetStringValue($destination),
                GetIntValue($port),
                GetStringValue($encrypted_password),
                GetStringValue($encrypted_ssh_key) === '' ? null : GetStringValue($encrypted_ssh_key),
                GetIntValue($companyparent_id),
            );
        }
        ExecuteSQL($this->db, $file, $vars);
    }
    public function get_file_transfer_by_company_id($company_id) {
        $file = 'database/sql/filetransfer/CompanyFileTransferSELECT_ByCompanyId.sql';
        $vars = array(
            GetIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many results.");
        return $results[0];
    }
    public function upsert_company_file_transfer($company_id, $hostname, $username, $destination, $port, $encrypted_password, $encrypted_ssh_key)
    {
        $file = "";
        $vars = array();

        $transfer = $this->get_file_transfer_by_company_id($company_id);
        if ( empty($transfer) )
        {
            // INSERT
            $file = 'database/sql/filetransfer/CompanyFileTransferINSERT_ByCompanyId.sql';
            $vars = array(
                GetIntValue($company_id),
                GetStringValue($username),
                GetStringValue($hostname),
                GetIntValue($port),
                GetStringValue($destination),
                GetStringValue($encrypted_password),
                GetStringValue($encrypted_ssh_key) === '' ? null : GetStringValue($encrypted_ssh_key)
            );
        }
        else
        {
            // UPDATE
            $file = 'database/sql/filetransfer/CompanyFileTransferUPDATE_ByCompanyId.sql';
            $vars = array(
                GetStringValue($hostname),
                GetStringValue($username),
                GetStringValue($destination),
                GetIntValue($port),
                GetStringValue($encrypted_password),
                GetStringValue($encrypted_ssh_key) === '' ? null : GetStringValue($encrypted_ssh_key),
                GetIntValue($company_id),
            );
        }
        ExecuteSQL($this->db, $file, $vars);
    }
    public function get_file_transfer_by_company($company_id) {
        $file = 'database/sql/filetransfer/FileTransferSELECT_ActiveByCompany.sql';
        $vars = array(
            GetIntValue($company_id)
            , GetIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 0 ) return array();
        return $results;
    }
    public function get_reports_for_transfer($company_id, $import_date)
    {
        $file = 'database/sql/filetransfer/CompanyReportSELECT_AllReportsForImport.sql';
        $vars = array(
            GetIntValue($company_id)
            , GetStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) === 0 ) return array();
        return $results;
    }


}


/* End of file FileTransfer_model.php */
/* Location: ./system/application/models/FileTransfer_model.php */





