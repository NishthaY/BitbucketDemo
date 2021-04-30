<?php

class Export_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    public function select_export_report_years_list($identifier, $identifier_type)
    {
        $file = "";
        if ( $identifier_type === 'company') $file = "database/sql/export/ImportDataSELECT_ImportYearsByCompany.sql";
        if ( $identifier_type === 'companyparent') $file = "database/sql/export/ImportDataSELECT_ImportYearsByCompanyParent.sql";
        if ( $file === '' ) return array();

        $vars = array
        (
            GetStringValue($identifier)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();

        $list = array();
        foreach($results as $item)
        {
            $list[] = GetArrayStringValue('Year', $item);
        }

        return $list;
    }
    public function get_export_id_by_job($job_id)
    {
        $file = "database/sql/export/ExportPropertySELECT_ExportIdByJobId.sql";
        $vars = array
        (
            GetStringValue($job_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return "";
        if ( count($results) > 1 ) throw new Exception("Found too many results.");

        $results = $results[0];
        return GetArrayStringValue('ExportId', $results);
    }
    public function get_recent_export_id($identifier, $identifier_type)
    {
        $file = "database/sql/export/ExportSELECT_MostRecentId.sql";
        $vars = array
        (
            GetStringValue($identifier),
            GetStringValue($identifier_type)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return "";
        if ( count($results) > 1 ) throw new Exception("Found too many results.");

        $results = $results[0];
        return GetArrayStringValue('Id', $results);
    }
    public function insert_export($identifier, $identifier_type, $status='REQUESTED')
    {
        // REQUESTED
        // IN_PROGRESS
        // COMPLETE

        $file = "database/sql/export/ExportINSERT.sql";
        $vars = array
        (
            GetIntValue($identifier),
            GetStringValue($identifier_type),
            GetStringValue($status)
        );
        ExecuteSQL($this->db, $file, $vars);

    }
    public function delete_export($export_id)
    {
        $this->delete_export_properties($export_id);

        $file = "database/sql/export/ExportDELETE_ById.sql";
        $vars = array
        (
            GetIntValue($export_id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function select_all_exports($identifier, $identifier_type)
    {
        $prefered_zone = GetConfigValue("timezone_display");
        $replace_for = array();
        $replace_for['{PREFERED_TIMEZONE}'] = $prefered_zone;
        $replace_for['{PREFERED_TIMEZONE}'] = $prefered_zone;

        $file = "database/sql/export/ExportSELECT_ByIdentifier.sql";
        $vars = array
        (
            GetStringValue($identifier),
            GetStringValue($identifier_type)
        );
        $results = GetDBResults($this->db, $file, $vars, $replace_for);
        if ( count($results) == 0 ) return array();

        for($i=0;$i<count($results);$i++)
        {
            $export = $results[$i];
            $export_id = GetArrayStringValue("Id", $export);
            $properties = $this->select_export_properties($export_id);
            $export['Properties'] = $properties;
            $results[$i] = $export;
        }

        return $results;
    }
    public function select_export($export_id)
    {
        $file = "database/sql/export/ExportSELECT_ById.sql";
        $vars = array
        (
            GetIntValue($export_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( count($results) == 0 ) return array();
        if ( count($results) > 1 ) throw new Exception("found too many exports by id.");
        return $results[0];
    }
    public function select_export_properties($export_id)
    {
        $file = "database/sql/export/ExportPropertySELECT_ByExportId.sql";
        $vars = array
        (
            GetStringValue($export_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( count($results) === 0 ) return array();
        return $results;
    }
    public function select_export_property_by_key($export_id, $key)
    {
        $file = "database/sql/export/ExportPropertySELECT_ByExportIdAndKey.sql";
        $vars = array
        (
            GetStringValue($export_id),
            GetStringValue($key)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( count($results) === 0 ) return "";
        if ( count($results) > 1 ) throw new Exception("Found too many export properties by that key.");

        $result = $results[0];
        return GetArrayStringValue('PropertyValue', $result);
    }
    protected function insert_export_property($export_id, $key, $value)
    {
        $file = "database/sql/export/ExportPropertyINSERT.sql";
        $vars = array
        (
            GetIntValue($export_id),
            GetStringValue($key),
            GetStringValue($value)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function upsert_export_property($export_id, $key, $value)
    {
        $property = $this->select_export_property_by_key($export_id, $key);
        if( empty($property) )
        {
            $this->insert_export_property($export_id, $key, $value);
        }
        else
        {
            $this->update_export_property($export_id, $key, $value);
        }
        $this->mark_export_updated($export_id);
    }
    public function delete_export_properties($export_id)
    {
        $file = "database/sql/export/ExportPropertyDELETE_ByExportId.sql";
        $vars = array
        (
            GetIntValue($export_id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function delete_export_property($export_id, $key)
    {
        $file = "database/sql/export/ExportPropertyDELETE_ByExportIdAndKey.sql";
        $vars = array
        (
            GetIntValue($export_id),
            GetStringValue($key)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    protected function update_export_property($export_id, $key, $value)
    {
        $file = "database/sql/export/ExportPropertyUPDATE.sql";
        $vars = array
        (
            GetStringValue($value),
            GetIntValue($export_id),
            GetStringValue($key)
        );
        ExecuteSQL($this->db, $file, $vars);
    }

    public function set_export_status($export_id, $status)
    {
        $file = "database/sql/export/ExportUPDATE_SetStatus.sql";
        $vars = array
        (
            GetStringValue($status),
            GetStringValue($export_id)
        );
        ExecuteSQL($this->db, $file, $vars);

    }
    public function mark_export_updated($export_id)
    {
        $file = "database/sql/export/ExportUPDATE_SetModified.sql";
        $vars = array
        (
            GetStringValue($export_id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }





}


/* End of file Feature_model.php */
/* Location: ./system/application/models/Feature_model.php */
