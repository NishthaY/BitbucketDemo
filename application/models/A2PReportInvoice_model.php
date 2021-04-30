<?php

class A2PReportInvoice_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    function select_invoice_report_list($identifier, $identifier_type)
    {
        $file = "";
        if ( $identifier_type === 'company') $file = "database/sql/reporta2pinvoice/SummaryDataSELECT_InvoiceReportDropdown.sql";
        if ( $identifier_type === 'companyparent') $file = "database/sql/reporta2pinvoice/SummaryDataSELECT_InvoiceReportDropdown_ByCompanyParent.sql";
        $vars = array(
            getIntValue($identifier)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();

        $list = array();
        foreach($results as $result)
        {
            $row = array();
            $row['description'] = GetArrayStringValue('Display', $result);
            $row['date_tag'] = GetArrayStringValue('DateTag', $result);
            $row['short_date'] = GetArrayStringValue('ShortDate', $result);
            $row['identifier'] = $identifier;
            $row['identifier_type'] = $identifier_type;
            $list[] = $row;
        }
        return $list;
    }

    function select_invoice_report_import_date($company_id, $date_tag)
    {

        $import_date = substr($date_tag, 4, 2) . "/01/" . substr($date_tag,0,4);

        $file = "database/sql/reporta2pinvoice/SummaryDataSELECT_InvoiceReport_MostRecentImport.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) > 1 ) throw new Exception("Too many results. Expected exactly one.");

        return $results[0];

    }
    function select_invoice_report_initial_import_date($company_id, $date_tag)
    {
        $import_date = substr($date_tag, 4, 2) . "/01/" . substr($date_tag,0,4);

        $file = "database/sql/reporta2pinvoice/SummaryDataSELECT_InvoiceReport_InitialImport.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) > 1 ) throw new Exception("Too many results. Expected exactly one.");

        return $results[0];
    }
    function has_reports_in_review( $company_id, $import_date )
    {
        $file = "database/sql/reporta2pinvoice/SummaryDataBOOLEAN_HasReportsInReview.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) throw new Exception("Expected exactly one result on boolean check, but found none.");
        if ( count($results) > 1 ) throw new Exception("Expected exactly one result on boolean check, but found many.");

        $results = $results[0];
        if ( GetArrayStringValue('InReportReview', $results) === 't' ) return true;
        if ( GetArrayStringValue('InReportReview', $results) === 'f' ) return false;
        throw new Exception("Did not find expected results on a boolean check.");
    }
    function select_carriers_by_company( $company_id )
    {
        $file = "database/sql/reporta2pinvoice/CompanyCarrierSELECT_ByCompanyId.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return $results;
        return $results;
    }
    function select_adjusted_total( $company_id, $carrier_id, $import_date )
    {
        $file = "database/sql/reporta2pinvoice/SummaryDataSELECT_AdjustedTotalByCompanyCarrier.sql";
        $vars = array(
            getIntValue($company_id),
            getIntValue($carrier_id),
            getStringValue($import_date),
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) !== 1 ) throw new Exception("Did not get exactly one result on a count.");
        $amount = GetArrayStringValue('TotalAdjustedPremium', $results[0]);
        return GetFloatValue($amount);
    }

}


/* End of file A2PReportInvoice_model.php */
/* Location: ./system/application/models/A2PReportInvoice_model.php */
