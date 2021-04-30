<?php

class Feature_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function list_distinct_targets_by_feature($identifier, $identifier_type, $feature_code, $enabled='t')
    {
        if ( is_bool($enabled) )
        {
            if ( $enabled ) $enabled = 't';
            else if ( $enabled ) $enabled = 'f';
        }

        if ( $identifier_type === 'company' ) $file = "database/sql/feature/CompanyFeatureSELECT_DistinctTargets.sql";
        else if ( $identifier_type === 'companyparent' ) $file = "database/sql/feature/CompanyParentFeatureSELECT_DistinctTargets.sql";

        $vars = array(
            getIntValue($identifier),
            getStringValue($feature_code),
            getStringValue($enabled) === 'f' ? false : true
        );
        $results = ExecuteSQL( $this->db, $file, $vars );
        if ( empty($results) ) return array();

        // Return just a list of distinct targets.
        $output = array();
        foreach($results as $item)
        {
            $output[] = GetArrayStringValue('Target', $item);
        }
        return $output;

    }

    public function delete_targetable_feature($identifier, $identifier_type, $feature_code, $target)
    {
        if ( GetStringValue($target) === '' ) throw new Exception("Target may not be empty.");

        if ( $identifier_type === 'company' ) $file = "database/sql/feature/CompanyFeatureDELETE_ByTarget.sql";
        else if ( $identifier_type === 'companyparent' ) $file = "database/sql/feature/CompanyParentFeatureDELETE_ByTarget.sql";
        else throw new Exception("Unknown identifier type.");

        $vars = array(
            GetIntValue($identifier),
            GetStringValue($feature_code),
            GetStringValue($target)
        );
        ExecuteSQL($this->db, $file, $vars);


        // Audit this transaction.
        $company_id = $identifier;
        $companyparent_id = null;
        $message = "Company feature deleted.";
        if ( $identifier_type === 'companyparent' )
        {
            $company_id = null;
            $companyparent_id = $identifier;
            $message = "Parent company feature deleted.";
        }
        $payload = array();
        $payload = array_merge($payload, array('Identifier' => $identifier));
        $payload = array_merge($payload, array('IdentifierType' => $identifier_type));
        $payload = array_merge($payload, array('FeatureCode' => $feature_code));
        $payload = array_merge($payload, array('Target' => $target));
        AuditIt($message, $payload, GetSessionValue('username'), $company_id, $companyparent_id);

    }

    public function does_feature_exist_for_identifier($identifier, $identifier_type, $feature_code, $target_type=null, $target=null)
    {
        if ( $identifier_type === 'company' ) return $this->does_company_feature_exist($identifier, $feature_code, $target_type, $target);
        else if ( $identifier_type === 'companyparent' ) return $this->does_companyparent_feature_exist($identifier, $feature_code, $target_type, $target);
        else throw new Exception("Unknown identifier type.");
    }
    public function insert_feature_for_identifier($identifier, $identifier_type, $feature_code, $target, $enabled)
    {
        if ( GetStringValue($enabled) === 't' ) $enabled = true;
        if ( GetStringValue($enabled) === 'f' ) $enabled = false;

        if ( $identifier_type === 'company' ) $file = "database/sql/feature/CompanyFeatureINSERT.sql";
        else if ( $identifier_type === 'companyparent' ) $file = "database/sql/feature/CompanyParentFeatureINSERT.sql";
        else throw new Exception("Unknown identifier type.");

        $vars = array(
            GetIntValue($identifier),
            GetStringValue($feature_code),
            $enabled,
            GetStringValue($target) === '' ? null : GetStringValue($target)
        );
        ExecuteSQL($this->db, $file, $vars);


        // Audit this transaction.
        $company_id = $identifier;
        $companyparent_id = null;
        $message = "Company feature created.";
        if ( $identifier_type === 'companyparent' )
        {
            $company_id = null;
            $companyparent_id = $identifier;
            $message = "Parent company feature created.";
        }
        $payload = array();
        $payload = array_merge($payload, array('Identifier' => $identifier));
        $payload = array_merge($payload, array('IdentifierType' => $identifier_type));
        $payload = array_merge($payload, array('FeatureCode' => $feature_code));
        $payload = array_merge($payload, array('Target' => $target));
        AuditIt($message, $payload, GetSessionValue('username'), $company_id, $companyparent_id);
    }
    public function get_targetable_features($identifier_type)
    {

        if ( $identifier_type === 'company' ) $file = "database/sql/feature/FeatureSELECT_TargetableCompanyFeatures.sql";
        else if ($identifier_type === 'companyparent') $file = "database/sql/feature/FeatureSELECT_TargetableCompanyParentFeatures.sql";
        else throw new Exception("Unknown identifier type.");

        $vars = array();
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        return $results;
    }

    public function is_companyparent_feature_enabled( $companyparent_id, $feature_code)
    {
        // Get the FEATURE for the company.
        $company_feature = array();
        $file = "database/sql/feature/FeatureSELECT_CompanyFeatureByCode.sql";
        $vars = array(
            GetStringValue($feature_code)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( count($results) > 1 ) throw new Exception("Found too many features at the company level.");
        if ( ! empty($results) ) $company_feature = $results[0];

        // Get the FEATURE for the parent.
        $companyparent_feature = array();
        $file = "database/sql/feature/FeatureSELECT_CompanyParentFeatureByCode.sql";
        $vars = array(
            GetStringValue($feature_code)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( count($results) > 1 ) throw new Exception("Found too many features at the companyparent level.");
        if ( ! empty($results) ) $companyparent_feature = $results[0];


        // If the PARENT and CHILD features both exist AND they have the exact same feature
        // id, then this is CHILD feature with PARENT override, not a PARENT feature.
        if ( GetArrayStringValue("Id", $companyparent_feature) === GetArrayStringValue("Id", $company_feature) )
        {
            return false;
        }

        // Okay, this is a feature that is assigned to just a companyparent.  Let's return
        // if it's enabled or not.
        $feature = $this->get_companyparent_feature($companyparent_id, $feature_code);
        if ( GetArrayStringValue("Enabled", $feature) === 't' ) return true;
        return false;

    }

    /*
     * get_feature_type
     *
     * What type of feature are we dealing with?
     *  - company feature: Company may enable/disable feature.
     *  - companyparent feature: CompanyParent may enable/disable feature.
     *  - company feature with parent override: Company assumes CompanyParent feature settings.
     *
     */
    public function get_feature_type($feature_code)
    {
        // Get the FEATURE for the company.
        $company_feature = array();
        $file = "database/sql/feature/FeatureSELECT_CompanyFeatureByCode.sql";
        $vars = array(
            GetStringValue($feature_code)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( count($results) > 1 ) throw new Exception("Found too many features at the company level.");
        if ( ! empty($results) ) $company_feature = $results[0];


        // Get the FEATURE for the parent.
        $companyparent_feature = array();
        $file = "database/sql/feature/FeatureSELECT_CompanyParentFeatureByCode.sql";
        $vars = array(
            GetStringValue($feature_code)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( count($results) > 1 ) throw new Exception("Found too many features at the companyparent level.");
        if ( ! empty($results) ) $companyparent_feature = $results[0];


        // Look at the company and companyparent features and decide what type of feature this is.
        // The type will determine how we decide if it's enabled or not.
        $feature_type = "";
        if ( empty($companyparent_feature) && ! empty($company_feature) ) $feature_type = "company feature";
        if ( ! empty($companyparent_feature) && empty($company_feature) ) $feature_type = "companyparent feature";
        if ( ! empty($companyparent_feature) && ! empty($company_feature) )
        {
            if ( GetArrayStringValue("Id", $companyparent_feature) === GetArrayStringValue("Id", $company_feature) )
            {
                // If the company and companyparent were granted permissions by the SAME feature
                // record, then it's company feature with parent override.
                $feature_type = "company feature with parent override";
            }
            else
            {
                // In the case where we have a feature that can be independently installed on
                // both the company and the parent company, then we are going to check the
                // company feature.  This function checks to see if a feature is enabled
                // for a COMPANY, not a parent company.
                $feature_type = "company feature";
            }

        }
        return $feature_type;
    }
    /**
     * is_feature_enabled
     *
     * This returns TRUE or FALSE depending on if the feature specified is enabled
     * on the COMPANY.  It's possible that the company's parent has turned it on
     * for them.
     *
     * @param $company_id
     * @param $feature_code
     * @return bool
     * @throws Exception
     */
    public function is_feature_enabled($company_id, $feature_code, $target_type=null, $target=null)
    {
        // Check to see if the feature code passed in is targetable or not.  If it is, then you
        // must provide a target type and target.
        if ( $this->is_feature_targetable($feature_code) )
        {
            if ( GetStringValue($target_type) === '' ) throw new Exception("Missing required input, target_type.");
            if ( GetStringValue($target) === '' ) throw new Exception("Missing required input, target");
        }

        $feature_type = $this->get_feature_type($feature_code);

        if ( $feature_type === 'company feature' )
        {
            $feature = $this->get_company_feature($company_id, $feature_code, $target_type, $target);
            if ( GetArrayStringValue("Enabled", $feature) === 't' ) return true;
            return false;
        }
        else if ( $feature_type === 'companyparent feature' )
        {
            $companyparent_id = GetCompanyParentId($company_id);
            $feature = $this->get_companyparent_feature($companyparent_id, $feature_code, $target_type, $target);
            if ( GetArrayStringValue("Enabled", $feature) === 't' ) return true;
            return false;
        }
        else if ( $feature_type === 'company feature with parent override' )
        {
            $companyparent_id = GetCompanyParentId($company_id);
            $feature = $this->get_companyparent_feature($companyparent_id, $feature_code, $target_type, $target);
            if ( GetArrayStringValue("Enabled", $feature) === 't' ) return true;

            $feature = $this->get_company_feature($company_id, $feature_code, $target_type, $target);
            if ( GetArrayStringValue("Enabled", $feature) === 't' ) return true;
            return false;
        }
        else
        {
            throw new Exception("Unable to figure out what type of feature this is.");
        }

    }
    public function does_company_feature_exist( $company_id, $feature_code, $target_type=null, $target=null )
    {
        $file = "database/sql/feature/CompanyFeatureEXISTS.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($feature_code),
            GetStringValue($target) === '' ? null : GetStringValue($target)
        );
        return GetDBExists($this->db, $file, $vars);
    }
    public function does_companyparent_feature_exist( $companyparent_id, $feature_code, $target_type=null, $target=null )
    {
        $file = "database/sql/feature/CompanyParentFeatureEXISTS.sql";
        $vars = array(
            GetIntValue($companyparent_id),
            GetStringValue($feature_code),
            GetStringValue($target) === '' ? null : GetStringValue($target)
        );
        return GetDBExists($this->db, $file, $vars);
    }
    public function get_company_feature($company_id, $feature_code, $target_type='', $target='')
    {
        $file = "database/sql/feature/CompanyFeatureSELECT_ByCode.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($target) === '' ? null : GetStringValue($target),
            GetStringValue($feature_code),
            GetStringValue($target_type) === '' ? null : GetStringValue($target_type),
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return $results;
        if ( count($results) !== 1 ) throw new Exception("Found too many features on lookup.");

        return $results[0];
    }
    public function get_companyparent_feature($companyparent_id, $feature_code, $target_type='', $target='')
    {
        $file = "database/sql/feature/CompanyParentFeatureSELECT_ByCode.sql";
        $vars = array(
            GetIntValue($companyparent_id),
            GetStringValue($target) === '' ? null : GetStringValue($target),
            GetStringValue($feature_code),
            GetStringValue($target_type) === '' ? null : GetStringValue($target_type),
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return array();
        if ( count($results) === 1 ) return $results[0];
        throw new Exception("Found too many features.");
    }
    public function enable_company_feature($company_id, $feature_code, $target_type=null, $target=null)
    {
        if ( $this->does_company_feature_exist($company_id, $feature_code, $target_type, $target) )
        {
            $file = "database/sql/feature/CompanyFeatureUPDATE_Enabled.sql";
            $vars = array(
                true,
                GetIntValue($company_id),
                GetStringValue($feature_code),
                GetStringValue($target) === '' ? null : GetStringValue($target)
            );
            ExecuteSQL($this->db, $file, $vars);
        }
        else
        {
            $file = "database/sql/feature/CompanyFeatureINSERT.sql";
            $vars = array(
                GetIntValue($company_id),
                GetStringValue($feature_code),
                true,
                GetStringValue($target) === '' ? null : GetStringValue($target)
            );
            ExecuteSQL($this->db, $file, $vars);
        }

        $company = $this->Company_model->get_company($company_id);
        $company_name = GetArrayStringValue("company_name", $company);

        // Audit this transaction.
        $payload = array();
        $payload = array_merge($payload, array('Feature' => $feature_code));
        $payload = array_merge($payload, array('TargetType' => $target_type));
        $payload = array_merge($payload, array('Target' => $target));
        $payload = array_merge($payload, array('CompanyId' => $company_id));
        $payload = array_merge($payload, array('CompanyName' => $company_name));
        AuditIt("Company feature enabled.", $payload, GetSessionValue('user_id'), $company_id);
    }
    public function enable_companyparent_feature($companyparent_id, $feature_code, $target_type=null, $target=null)
    {
        $target_type = GetStringValue($target_type);
        $target = GetStringValue($target);

        if ( $this->does_companyparent_feature_exist($companyparent_id, $feature_code, $target_type, $target) )
        {
            $file = "database/sql/feature/CompanyParentFeatureUPDATE_Enabled.sql";
            $vars = array(
                true,
                GetIntValue($companyparent_id),
                GetStringValue($feature_code),
                GetStringValue($target) === '' ? null : GetStringValue($target)
            );
            ExecuteSQL($this->db, $file, $vars);
        }
        else
        {
            $file = "database/sql/feature/CompanyParentFeatureINSERT.sql";
            $vars = array(
                GetIntValue($companyparent_id),
                GetStringValue($feature_code),
                true,
                GetStringValue($target) === '' ? null : GetStringValue($target)
            );
            ExecuteSQL($this->db, $file, $vars);
        }

        $parent = $this->CompanyParent_model->get_companyparent($companyparent_id);
        $parent_name = GetArrayStringValue("Name", $parent);

        // Audit this transaction.
        $payload = array();
        $payload = array_merge($payload, array('Feature' => $feature_code));
        $payload = array_merge($payload, array('TargetType' => $target_type));
        $payload = array_merge($payload, array('Target' => $target));
        $payload = array_merge($payload, array('CompanyParentId' => $companyparent_id));
        $payload = array_merge($payload, array('CompanyParentName' => $parent_name));
        AuditIt("Parent feature enabled.", $payload, GetSessionValue('user_id'), null, $companyparent_id);


        // COMPANY FEATURE WITH PARENT OVERRIDE
        // If we just enabled a feature on the parent, trigger the feature on the child company
        // if it is linked to the parent.
        $feature_type = $this->get_feature_type($feature_code);
        if ( $feature_type === 'company feature with parent override' )
        {
            $companies = $this->CompanyParent_model->get_companies_by_parent($companyparent_id);
            foreach($companies as $company)
            {
                $company_id = GetArrayStringValue('company_id', $company);
                $this->enable_company_feature($company_id, $feature_code, $target_type, $target);
            }
        }


    }
    public function disable_company_feature($company_id, $feature_code, $target_type=null, $target=null)
    {
        if ( $this->does_company_feature_exist($company_id, $feature_code, $target_type, $target) )
        {
            $file = "database/sql/feature/CompanyFeatureUPDATE_Enabled.sql";
            $vars = array(
                false,
                GetIntValue($company_id),
                GetStringValue($feature_code),
                GetStringValue($target) === '' ? null : GetStringValue($target)
            );
            ExecuteSQL($this->db, $file, $vars);
        }
        else
        {
            $file = "database/sql/feature/CompanyFeatureINSERT.sql";
            $vars = array(
                GetIntValue($company_id),
                GetStringValue($feature_code),
                false,
                GetStringValue($target) === '' ? null : GetStringValue($target)
            );
            ExecuteSQL($this->db, $file, $vars);
        }

        $company = $this->Company_model->get_company($company_id);
        $company_name = GetArrayStringValue("company_name", $company);

        // Audit this transaction.
        $payload = array();
        $payload = array_merge($payload, array('Feature' => $feature_code));
        $payload = array_merge($payload, array('CompanyId' => $company_id));
        $payload = array_merge($payload, array('CompanyName' => $company_name));
        AuditIt("Company feature disabled.", $payload, GetSessionValue('user_id'), $company_id);
    }
    public function disable_companyparent_feature($companyparent_id, $feature_code, $target_type=null, $target=null)
    {
        $target_type = GetStringValue($target_type);
        $target = GetStringValue($target);

        if ( $this->does_companyparent_feature_exist($companyparent_id, $feature_code, $target_type, $target) )
        {
            $file = "database/sql/feature/CompanyParentFeatureUPDATE_Enabled.sql";
            $vars = array(
                false,
                GetIntValue($companyparent_id),
                GetStringValue($feature_code),
                GetStringValue($target) === '' ? null : GetStringValue($target)
            );
            ExecuteSQL($this->db, $file, $vars);
        }
        else
        {
            $file = "database/sql/feature/CompanyParentFeatureINSERT.sql";
            $vars = array(
                GetIntValue($companyparent_id),
                GetStringValue($feature_code),
                false,
                GetStringValue($target) === '' ? null : GetStringValue($target)
            );
            ExecuteSQL($this->db, $file, $vars);
        }


        $parent = $this->CompanyParent_model->get_companyparent($companyparent_id);
        $parent_name = GetArrayStringValue("Name", $parent);

        // Audit this transaction.
        $payload = array();
        $payload = array_merge($payload, array('Feature' => $feature_code));
        $payload = array_merge($payload, array('TargetType' => $target_type));
        $payload = array_merge($payload, array('Target' => $target));
        $payload = array_merge($payload, array('CompanyParentId' => $companyparent_id));
        $payload = array_merge($payload, array('CompanyParnetName' => $parent_name));
        AuditIt("Parent feature disabled.", $payload, GetSessionValue('user_id'), null, $companyparent_id);


        // COMPANY FEATURE WITH PARENT OVERRIDE
        // If we just disabled a feature on the parent, trigger the same change on the child company
        // if it is linked to the parent.
        $feature_type = $this->get_feature_type($feature_code);
        if ( $feature_type === 'company feature with parent override' )
        {
            $companies = $this->CompanyParent_model->get_companies_by_parent($companyparent_id);
            foreach($companies as $company)
            {
                $company_id = GetArrayStringValue('company_id', $company);
                $this->disable_company_feature($company_id, $feature_code, $target_type, $target);
            }
        }

    }
    public function get_company_features($company_id)
    {
        $file = "database/sql/feature/CompanyFeatureSELECT.sql";
        $vars = array(
            GetIntValue($company_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return $results;

        return $results;

    }
    public function get_companyparent_features($companyparent_id)
    {
        $file = "database/sql/feature/CompanyParentFeatureSELECT.sql";
        $vars = array(
            GetIntValue($companyparent_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return $results;

        return $results;

    }
    public function is_feature_enabled_for_companyparent( $feature_code, $companyparent_id, $target_type=null, $target=null )
    {
        // No companyparent_id, certainly not enabled for that company parent.
        if ( $companyparent_id === '' ) return false;

        $file = "database/sql/feature/CompanyParentFeatureBOOLEAN_IsEnabled.sql";
        $vars = array(
            GetStringValue($target) === '' ? null : GetStringValue($target),
            GetStringValue($feature_code),
            GetStringValue($target_type) === '' ? null : GetStringValue($target_type),
            GetIntValue($companyparent_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( count($results) === 0 ) return false;
        if ( count($results) !== 1 ) throw new Exception("Did not find exactly one result when looking up boolean details for parent.");
        $results = GetArrayStringValue("Enabled", $results[0]);

        // Convert this result to true of false.
        if ( $results === 't' ) return true;
        if ( $results === 'f' ) return false;
        throw new Exception("Unexpected results when looking up boolean");
    }
    public function is_feature_enabled_for_company( $feature_code, $company_id, $target_type=null, $target=null )
    {

        $feature = $this->get_company_feature($company_id, $feature_code);

        // No company_id, certainly not enabled for that company.
        if ( $company_id === '' ) return false;

        $file = "database/sql/feature/CompanyFeatureBOOLEAN_IsEnabled.sql";
        $vars = array(
            GetStringValue($target) === '' ? null : GetStringValue($target),
            GetStringValue($feature_code),
            GetStringValue($target_type) === '' ? null : GetStringValue($target_type),
            GetIntValue($company_id)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( count($results) === 0 ) return false;
        if ( count($results) !== 1 ) throw new Exception("Did not find exactly one result when looking up boolean details for company.");
        $results = GetArrayStringValue("Enabled", $results[0]);

        // Convert this result to true of false.
        if ( $results === 't' ) return true;
        if ( $results === 'f' ) return false;
        throw new Exception("Unexpected results when looking up boolean");
    }
    function is_feature_targetable($feature_code)
    {
        $file = "database/sql/feature/FeatureBOOLEAN_HasTarget.sql";
        $vars = array(
            GetStringValue($feature_code)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( count($results) === 0 ) return false;

        // There could be more than one feature with the same code in the case like FILE_TRANSFER
        // where the feature can be applied to the company or companyparent independently.  In this
        // case if ANY of the features are marked targetable, return true.  The assumption here is
        // that a feature will either all be targetable or all not targetable.
        foreach($results as $result)
        {
            $targetable = GetArrayStringValue("Targetable", $results);
            if ( $targetable === 't' ) return true;
        }
        return false;
    }

    function is_atleast_one_feature_enabled($identifier, $identifier_type, $feature_code)
    {
        // If the companyparent, then just do a simple check against the database.
        if ( $identifier_type === 'companyparent') $file = "database/sql/feature/CompanyParentFeatureSELECT_CollectionByCode.sql";
        else if ( $identifier_type === 'company' ) $file = "database/sql/feature/CompanyFeatureSELECT_CollectionByCode.sql";
        else throw new Exception("Unknown identifier type.");

        $vars = array(
            GetIntValue($identifier),
            GetStringValue($feature_code)
        );
        $results = GetDBResults($this->db, $file, $vars);
        if ( empty($results) ) return FALSE;

        foreach( $results as $result)
        {
            $enabled = GetArrayStringValue('Enabled', $result);
            if ( $enabled === 't' ) return TRUE;
        }
        return FALSE;
    }



}


/* End of file Feature_model.php */
/* Location: ./system/application/models/Feature_model.php */
