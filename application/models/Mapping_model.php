<?php

class Mapping_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
        $this->load->model('Reporting_model');
    }
    public function add_beneficiary_map($identifier, $identifier_type, $normalized, $description, $code)
    {
        if ( $identifier_type === 'company') $file = "database/sql/mapping/CompanyBeneficiaryMapINSERT.sql";
        else if ( $identifier_type === 'companyparent') $file = "database/sql/mapping/CompanyParentBeneficiaryMapINSERT.sql";
        else throw new Exception("Unknown identifier type.");
        $vars = array(
            GetIntValue($identifier),
            GetStringValue($normalized),
            GetStringValue($description),
            GetStringValue($code)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function remove_beneficiary_maps($identifier, $identifier_type, $column_code)
    {
        if ( $identifier_type === 'company') $file = "database/sql/mapping/CompanyBeneficiaryMapDELETE_ByIdentifier.sql";
        else if ( $identifier_type === 'companyparent') $file = "database/sql/mapping/CompanyParentBeneficiaryMapDELETE_ByIdentifier.sql";
        else throw new Exception("Unknown identifier type.");
        $vars = array(
            GetIntValue($identifier),
            GetStringValue($column_code)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function select_beneficiary_maps($identifier, $identifier_type, $column_code)
    {
        if ( $identifier_type === 'company') $file = "database/sql/mapping/CompanyBeneficiaryMapSELECT_ByIdentifier.sql";
        else if ( $identifier_type === 'companyparent') $file = "database/sql/mapping/CompanyParentBeneficiaryMapSELECT_ByIdentifier.sql";
        else throw new Exception("Unknown identifier type.");
        $vars = array(
            GetIntValue($identifier),
            GetStringValue($column_code)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if (empty($results) ) return array();
        return $results;
    }

    public function select_mapping_columns()
    {
        $file = "database/sql/mapping/MappingColumnsSELECT.sql";
        $results = GetDBResults($this->db, $file, array());
        return $results;
    }

    public function delete_company_mapped_columns($company_id)
    {
        $file = "database/sql/mapping/CompanyMappingColumnDELETE.sql";
        $vars = array
        (
            getIntValue($company_id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }

    public function insert_company_mapping_column( $company_id, $name, $display, $required, $default_value, $column_name, $encrypted, $conditional, $conditional_list, $normalization_regex )
    {
        $file = "database/sql/mapping/CompanyMappingColumnINSERT.sql";
        $vars = array
        (
            getIntValue($company_id),
            GetStringValue($name) === '' ? null : GetStringValue($name),
            GetStringValue($display) === '' ? null : GetStringValue($display),
            GetStringValue($required) === 't' ? true : false,
            GetStringValue($default_value) === '' ? null : GetStringValue($default_value),
            GetStringValue($column_name) === '' ? null : GetStringValue($column_name),
            GetStringValue($encrypted) === '' ? null : GetStringValue($encrypted),
            GetStringValue($conditional) === 't' ? true : false,
            GetStringValue($conditional_list) === '' ? null : GetStringValue($conditional_list),
            GetStringValue($normalization_regex) === '' ? null : GetStringValue($normalization_regex)
        );
        ExecuteSQL($this->db, $file, $vars);

    }

    /**
     * get_mapping_columns
     *
     * Return a collection of mapping columns.  The required column will be
     * calculated based on the company_id and/or companyparent_id that is passed
     * in.
     *
     * @param null $company_id
     * @param null $companyparent_id
     * @return array
     */
    public function get_mapping_columns($company_id=null, $companyparent_id=null)
    {
        $identifier = $company_id;
        $identifier_type = 'company';
        if ( GetStringValue($company_id) === '' )
        {
            $identifier = $companyparent_id;
            $identifier_type = 'companyparent';
        }

        if ( GetStringValue($identifier_type) === 'company' )
        {
            $file = "database/sql/mapping/CompanyMappingColumnSELECT.sql";
            $vars = array(
                GetIntValue($identifier)
            );
            $results = GetDBResults($this->db, $file, $vars);
            if ( empty($results) ) return array();

            return $results;
        }
        if ( GetStringValue($identifier_type) === 'companyparent' )
        {
            $file = "database/sql/mapping/CompanyParentMappingColumnSELECT.sql";
            $vars = array(
                GetIntValue($identifier)
            );
            $results = GetDBResults($this->db, $file, $vars);
            if ( empty($results) ) return array();

            return $results;
        }

    }


    /**
     * get_mapping_column_by_name
     *
     * Pull our collection of mapping columns and then filter the
     * results down to the one we are looking for.
     *
     * @param $name
     * @param null $company_id
     * @param null $companyparent_id
     * @return array|mixed
     */
    public function get_mapping_column_by_name($column_code, $company_id=null, $companyparent_id=null)
    {
        $columns = $this->get_mapping_columns($company_id, $companyparent_id);
        foreach ($columns as $column )
        {
            if ( GetArrayStringValue('name', $column) === $column_code ) return $column;
        }
        return array();
    }

    /**
     * get_required_mapping_columns
     *
     * Pull the collection of mapping columns and then filter the results
     * down to the ones that are required.
     *
     * @param null $company_id
     * @param null $companyparent_id
     * @return array
     */
    public function get_required_mapping_columns($company_id=null, $companyparent_id=null)
    {
        $identifier = $company_id;
        $identifier_type = 'company';
        if ( GetStringValue($company_id) === '' )
        {
            $identifier = $companyparent_id;
            $identifier_type = 'companyparent';
        }

        $filtered = array();
        $columns = $this->get_mapping_columns($company_id, $companyparent_id);
        foreach ($columns as $column )
        {
            if ( GetArrayStringValue('required', $column) === 't' ) $filtered[] = $column;
        }
        return $filtered;

    }

    /**
     * get_mapped_columns_with_default_values
     *
     * Pull a collection of mapping columns and then filter the results down
     * to the ones that have a default value.
     *
     * @return array
     */
    public function get_mapped_columns_with_default_values()
    {

        $file = "database/sql/mapping/MappingColumnsSELECT_ColumnsThatDefault.sql";
        $vars = array( );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }

    /**
     * is_column_required
     *
     * Given a column code and a report code, return TRUE or FALSE if the
     * report requires the column.
     *
     * @param $column_code
     * @param $report_code
     * @return bool
     * @throws Exception
     */
    public function is_column_required($column_code, $report_code )
    {
        $file = "database/sql/mapping/MappingColumnsBOOLEAN_IsRequiredByReport.sql";
        $vars = array(
            getStringValue($report_code),
            getStringValue($column_code)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( count($results) !== 1 ) throw new Exception("Expected one result for boolean check");
        $bool = GetArrayStringValue("Required", $results[0]);
        if ( $bool === "t") return true;
        if ( $bool === "f") return false;
        throw new Exception("Unexpected results for boolean check.");
    }


    public function delete_customer_best_mapped_column($company_id=null, $companyparent_id=null)
    {
        if ( GetStringValue($company_id) !== '' )
        {
            $file = "database/sql/mapping/CompanyBestMappedColumnDELETE.sql";
            $vars = array(
                getIntValue($company_id)
            );
            ExecuteSQL($this->db, $file, $vars);
        }
        else if ( GetStringValue($companyparent_id) !== '' )
        {
            $file = "database/sql/mapping/CompanyParentBestMappedColumnDELETE.sql";
            $vars = array(
                getIntValue($companyparent_id)
            );
            ExecuteSQL($this->db, $file, $vars);
        }

    }

    public function insert_customer_best_mapped_column($company_id=null, $companyparent_id=null, $column_name, $map_name, $column_no)
    {
        if ( GetStringValue($company_id) !== '' )
        {
            $file = "database/sql/mapping/CompanyBestMappedColumnINSERT.sql";
            $vars = array(
                getIntValue($company_id),
                getStringValue($column_name) === '' ? null : getStringValue($column_name),
                getStringValue($column_name) === '' ? null : strtoupper(getStringValue($column_name)),
                getStringValue($map_name) === '' ? null : getStringValue($map_name),
                getIntValue($column_no),
            );
            ExecuteSQL($this->db, $file, $vars);
        }
        else if ( GetStringValue($companyparent_id) !== '' )
        {
            $file = "database/sql/mapping/CompanyParentBestMappedColumnINSERT.sql";
            $vars = array(
                getIntValue($companyparent_id),
                getStringValue($column_name) === '' ? null : getStringValue($column_name),
                getStringValue($column_name) === '' ? null : strtoupper(getStringValue($column_name)),
                getStringValue($map_name) === '' ? null : getStringValue($map_name),
                getIntValue($column_no),
            );
            ExecuteSQL($this->db, $file, $vars);
        }
    }

    public function get_customer_best_mapped_column_A2PRecommended($identifier, $identifier_type, $column_no)
    {
        if ( $identifier_type === 'company' )
        {
            $file = "database/sql/mapping/CompanyBestMappedColumnSELECT_ByColumnNumber.sql";
            $vars = array(
                getIntValue($identifier),
                getIntValue($column_no)
            );
            $results = GetDBResults($this->db, $file, $vars);
            if (count($results) === 1) {
                $results = $results[0];
                return $results;
            }
            return array();
        }
        else if ( $identifier_type === 'companyparent' )
        {
            $file = "database/sql/mapping/CompanyParentBestMappedColumnSELECT_ByColumnNumber.sql";
            $vars = array(
                getIntValue($identifier),
                getIntValue($column_no)
            );
            $results = GetDBResults($this->db, $file, $vars);
            if (count($results) === 1) {
                $results = $results[0];
                return $results;
            }
            return array();
        }
        throw new Exception("Unknown identifier.");

    }
    public function get_customer_best_mapped_column_UserElected($identifier, $identifier_type, $column_no)
    {
        if ( $identifier_type === 'company' )
        {
            $file = "database/sql/mapping/CompanyBestMappedColumnSELECT_ByColumnNumber_UserElected.sql";
            $vars = array(
                getIntValue($column_no),
                getIntValue($identifier),
                getIntValue($column_no)
            );
            $results = GetDBResults($this->db, $file, $vars);
            if (count($results) === 1) {
                $results = $results[0];
                return $results;
            }
            return array();
        }
        else if ( $identifier_type === 'companyparent' )
        {
            $file = "database/sql/mapping/CompanyParentBestMappedColumnSELECT_ByColumnNumber_UserElected.sql";
            $vars = array(
                getIntValue($column_no),
                getIntValue($identifier),
                getIntValue($column_no)
            );
            $results = GetDBResults($this->db, $file, $vars);
            if (count($results) === 1) {
                $results = $results[0];
                return $results;
            }
            return array();
        }
        throw new Exception(__FUNCTION__ . ": Unknown identifier type.");
    }

    public function get_mapped_columns($company_id, $companyparent_id)
    {
        $identifier = $company_id;
        $identifier_type = 'company';
        if ( GetStringValue($company_id) === '' )
        {
            $identifier = $companyparent_id;
            $identifier_type = 'companyparent';
        }


        if ( $identifier_type === 'company' ) $file = "database/sql/company/CompanyPreferenceSELECT_MappedColumns.sql";
        else if ( $identifier_type === 'companyparent' ) $file = "database/sql/companyparent/CompanyParentPreferenceSELECT_MappedColumns.sql";
        else throw new Exception(__FUNCTION__ . ": Unknown identifier type.");
        $vars = array(
            getIntValue($identifier)
        );
        $results = GetDBResults($this->db, $file, $vars);
        return $results;

    }

    public function get_mapped_column_no($company_id, $companyparent_id, $column_name)
    {

        $identifier = $company_id;
        $identifier_type = 'company';
        if ( GetStringValue($company_id) === '' )
        {
            $identifier = $companyparent_id;
            $identifier_type = 'companyparent';
        }


        if (!$this->does_column_mapping_exist($company_id, $companyparent_id, $column_name)) return false;

        if ( $identifier_type === 'company') $file = "database/sql/mapping/SelectColumnNoForMappedColumn_ByCompany.sql";
        else if ( $identifier_type === 'companyparent') $file = "database/sql/mapping/SelectColumnNoForMappedColumn_ByParent.sql";
        else throw new Exception(__FUNCTION__ . ": Unknown identifier type.");

        $vars = array(
            getIntValue($identifier),
            getStringValue($column_name)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if (count($results) != 1) throw new Exception("Unexpected results when checking to see if a column mapping already existed.");
        $results = $results[0];
        $result = getArrayStringValue("column", $results);
        $result = replaceFor($result, "col", "");
        if (StripNonNumeric($result) == $result) return getIntValue($result);
        return false;
    }

    public function does_column_mapping_exist($company_id, $companyparent_id, $column_name)
    {

        $identifier = $company_id;
        $identifier_type = 'company';
        if ( GetStringValue($company_id) === '' )
        {
            $identifier = $companyparent_id;
            $identifier_type = 'companyparent';
        }

        if ( $identifier_type === 'company') $file = "database/sql/mapping/DoesColumnMappingExist_ByCompany.sql";
        if ( $identifier_type === 'companyparent') $file = "database/sql/mapping/DoesColumnMappingExist_ByParent.sql";

        $vars = array(
            getIntValue($identifier),
            getStringValue($column_name)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if (count($results) != 1) throw new Exception("Unexpected results when checking to see if a column mapping already existed.");
        $results = $results[0];
        if (getArrayStringValue("mapped", $results) == "t") return true;
        return false;
    }

    public function does_column_header_mapping_exist($company_id, $column_name)
    {
        $file = "database/sql/mapping/DoesColumnHeaderMappingExist.sql";
        $vars = array(
            getIntValue($company_id)
        , getStringValue($column_name)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if (count($results) != 1) throw new Exception("Unexpected results when checking to see if a column header mapping already existed.");
        $results = $results[0];
        if (getArrayStringValue("mapped", $results) == "t") return true;
        return false;
    }







    public function get_mapping_column_headers($name)
    {
        $file = "database/sql/mapping/SelectMappingColumnHeaders.sql";
        $vars = array(getStringValue($name));
        $results = GetDBResults($this->db, $file, $vars);
        if (count($results) == 0) return array();
        $out = array();
        foreach ($results as $row) {
            $out[] = getArrayStringValue("header", $row);
        }
        return $out;
    }

    public function get_plan_types_for_user_dopdown()
    {
        $file = "database/sql/mapping/PlanTypesSELECT_UserDropdown.sql";
        $vars = array();
        $results = GetDBResults($this->db, $file, $vars);
        if (count($results) == 0) return array();
        return $results;
    }



    public function default_mapping_column($company_id, $column_name, $value)
    {
        $file = "database/sql/importdata/ImportDataUPDATE_DefaultValue.sql";
        $vars = array(
            GetStringValue($value) === '' ? null : GetStringValue($value)
            , GetIntValue($company_id)
        );
        $replacefor = array();
        $replacefor['{COLUMN}'] = $column_name;
        ExecuteSQL($this->db, $file, $vars, $replacefor);
    }
    public function update_default_mapping_column($company_id, $column_name, $current_value, $new_value)
    {
        $file = "database/sql/importdata/ImportDataUPDATE_DefaultValueByCurrentValue.sql";
        $vars = array(
            GetStringValue($new_value) === '' ? null : GetStringValue($new_value),
            GetIntValue($company_id),
            GetStringValue($current_value) === '' ? null : GetStringValue($current_value),
        );
        $replacefor = array();
        $replacefor['{COLUMN}'] = $column_name;
        ExecuteSQL($this->db, $file, $vars, $replacefor);
    }
    public function get_upload_column_for_mapping($identifier, $identifier_type, $mapping)
    {
        $result = [];
        try
        {
            if ($identifier_type === 'company' ) $file = "database/sql/company/CompanyPreferenceSELECT_GetUploadColumnForMapping.sql";
            else if ($identifier_type === 'companyparent' ) $file = "database/sql/companyparent/CompanyParentPreferenceSELECT_GetUploadColumnForMapping.sql";
            else throw new Exception("Unknown identifier_type.");

            // mapping is something like "first_name" and you get back "col#"
            $vars = array(
                getStringValue($mapping),
                getIntValue($identifier)
            );
            $results = GetDBResults( $this->db, $file, $vars );
            if ( count($results) > 1) {
                $payload = ['identifier'=>$identifier, 'identifier_type'=>$identifier_type, 'mapping'=>$mapping];
                $message = "Got too many results when looking up the upload column for a given mapping.";
                LogIt("Mapping Exception", $message, $payload);
                throw new Exception($message);
            }
            if ( count($results) == 0 ) return "";
            $results = $results[0];

            $result = getArrayStringValue("column_name", $results);

        }catch(Exception $e)
        {
            $payload = [];
            $payload['identifier'] = $identifier;
            $payload['identifier_type'] = $identifier_type;
            $payload['mapping'] = $mapping;
            LogIt("Exception", $e->getMessage(), $payload);

            throw $e;
        }
        return $result;

    }

    public function delete_companyparentmappingcolumn( $companyparent_id )
    {
        $file = "database/sql/mapping/CompanyParentMappingColumnDELETE.sql";
        $vars = array(
            GetIntValue($companyparent_id)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
    public function insert_companyparentmappingcolumn( $companyparent_id, $name, $display )
    {
        $file = "database/sql/mapping/CompanyParentMappingColumnINSERT.sql";
        $vars = array(
            GetIntValue($companyparent_id),
            GetStringValue($name),
            GetStringValue($display)
        );
        ExecuteSQL($this->db, $file, $vars);
    }
}


/* End of file mapping_model.php */
/* Location: ./system/application/models/mapping_model.php */
