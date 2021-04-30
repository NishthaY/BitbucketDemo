<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );

class RotateKey extends Tool
{

    protected $delay;
    private $_debug;


    public function __construct()
    {
        parent::__construct();
        $this->delay = 1;       // How many seconds to wait between KMS calls.
        $this->_debug = true;   // Write detailed information to STDOUT
    }


    /**
     * rotate
     *
     * Route the request as there are several ways we can rotate keys.
     *
     * @param null $all
     */
    function rotate($all=null)
    {
        if ( APP_NAME === 'a2p-prodcopy' )
        {
            print "Security key rotation not allowed in PRODCOPY.\n";
            print "No keys have been rotated.\n";
            return;
        }

        $all = GetStringValue($all);
        $all = strtoupper($all);
        if ( $all === '' )
        {
            // Allow the user to select which company to rotate.
            $this->_interactive();
        }
        else if ($all === 'ALL')
        {
            // Rotate everything for them.
            $this->_all();
        }
        else
        {
            // Show them the help screen.
            $this->index();
        }
    }


    /**
     * debug
     *
     * Write detailed messages about our progress to STDOUT.
     * @param $message
     */
    protected function debug($message)
    {
        if ( $this->_debug )
        {
            $type = gettype($message);
            if ( $type === 'boolean' || $type === 'integer' || $type === 'double' || $type === 'string' )
            {
                $message = trim($message);
                print $message . "\n";
            }
        }
    }


    /**
     * _interactive
     *
     * Ask the user what they want to do using an interactive interface.
     */
    private function _interactive()
    {
        // What company are we working with.
        $item = $this->getCompanyOrParent();
        if ( empty($item) )
        {
            print "Operation canceled.\n";
            exit;
        }
        if ( count($item) === 1 ) $item = $item[0];
        if ( GetArrayStringValue('company_id', $item) !== '' ) $this->_rotate_company_keys($item);
        if ( GetArrayStringValue('Id', $item) !== '' ) $this->_rotate_parent_keys($item);
    }

    /**
     * _all
     *
     * Rotate all keys for both company and parent companies.
     */
    private function _all()
    {
        $this->confirm("ROTATE KEYS\nYou are about to rotate ALL keys for this environment.");

        // Turn off debugging.  We will only write one line per
        // company/parent we are rotating.
        $this->debug = false;

        $companies = $this->Company_model->get_all_companies();
        if ( ! empty($companies))
        {
            uasort($companies, 'AssociativeArraySortFunction_company_name');
            foreach($companies as $company)
            {
                print "Rotating " . GetArrayStringValue('company_name', $company) ."\n";
                $this->_rotate_company_keys($company, false);
            }
        }

        $parents = $this->CompanyParent_model->get_all_parents();
        if ( ! empty($parents))
        {
            uasort($parents, 'AssociativeArraySortFunction_Name');
            foreach($parents as $parent)
            {
                print "Rotating " . GetArrayStringValue('Name', $parent) ."\n";
                $this->_rotate_parent_keys($parent, false);
            }
        }
    }

    /**
     * _rotate_company_keys
     *
     * Rotate all keys associated with a specific company.
     *
     * @param $company
     * @param bool $confirm
     */
    private function _rotate_company_keys($company, $confirm=true)
    {
        $comany_name = GetArrayStringValue('company_name', $company);
        $company_id = getArrayStringValue('company_id', $company);

        if ( $confirm )
        {
            $this->confirm("ROTATE KEYS\nYou are about to rotate keys for the company {$comany_name}.");
        }

        // Get the KMS Alias for this company and rotate it.
        $alias_name = "alias/".APP_NAME."/company_" . $company_id;
        $key = KMSGetAlias($alias_name);
        $this->_rotate_key($key, $company_id, null);
    }

    /**
     * _rotate_parent_keys
     *
     * Rotate all keys for a specific company parent.
     * @param $parent
     * @param bool $confirm
     */
    private function _rotate_parent_keys($parent, $confirm=true)
    {
        $companyparent_id = GetArrayStringValue('Id', $parent);
        $companyparent_name = getArrayStringValue('Name', $parent);

        if ( $confirm )
        {
            $this->confirm("ROTATE KEYS\nYou are about to rotate keys for the parent company {$companyparent_name}.");
        }

        $alias_name = "alias/".APP_NAME."/companyparent_" . $companyparent_id;
        $key = KMSGetAlias($alias_name);
        $this->_rotate_key($key, null, $companyparent_id);
    }

    /**
     * _rotate_key
     *
     * Do the actual key rotation for either a company or a parentcompany.
     * @param $key
     * @param null $company_id
     * @param null $companyparent_id
     */
    private function _rotate_key($key, $company_id=null, $companyparent_id=null)
    {
        try
        {
            throw new Exception("skipping.");

            // You must have a key, and either a company_id or companyparent_id, but not both, to do this.
            if ( empty($key) ) throw new Exception("Unable to find key to rotate.");
            if ( GetStringValue($company_id) === '' && GetArrayStringValue($companyparent_id) === '') throw new Exception("Missing required input.  Provide company_id OR companyparent_id.");
            if ( GetStringValue($company_id) !== '' && GetArrayStringValue($companyparent_id) !== '') throw new Exception("Too many inputs.  Provide compnay_id OR companyparent_id, not both.");

            // Collect and organize our data.
            $user_id = GetArrayStringValue('Id', $this->authenticated_user);
            $alias_name = GetArrayStringValue("AliasName", $key);
            if ( GetStringValue($company_id) !== '' ) $cmk_description = APP_NAME . ": Company ( {$company_id} )";
            if ( GetStringValue($companyparent_id) !== '' ) $cmk_description = APP_NAME . ": CompanyParent ( {$companyparent_id} )";

            // ROTATE
            // Create a new key and call it alias_name.  Then return new name for the old key.
            $this->debug("ROTATING[{$alias_name}]");
            $retired_alias_name = KMSRotateKey($alias_name, $cmk_description);
            sleep($this->delay);

            // I want to audit the new and old key ids here.
            $new_key = KMSGetAlias($alias_name);
            $new_key_id = GetArrayStringValue("TargetKeyId", $new_key);
            $old_key = KMSGetAlias($retired_alias_name);
            $old_key_id = GetArrayStringValue("TargetKeyId", $old_key);

            $payload = array();
            $payload['NewKeyAlias'] = $alias_name;
            $payload['NewKeyId'] = $new_key_id;
            $payload['RetiredKeyAlias'] = $retired_alias_name;
            $payload['RetiredKeyId'] = $old_key_id;
            AuditIt("Rotated customer master key.", $payload, $user_id, $company_id, $companyparent_id);

            // MIGRATE
            // Re-encrypt any KMS encrypted data with the new key.
            $this->debug("MIGRATING[{$alias_name}]");
            $this->_migrateKey( $alias_name );
            sleep($this->delay);
            AuditIt("Migrated data to new key.", array(), $user_id, $company_id, $companyparent_id);

            // RETIRE
            // Schedule the old key we no longer use for deletion.
            $this->debug("RETIRING[{$alias_name}]");
            KMSScheduleAliasForDeletion($retired_alias_name, 30);
            sleep($this->delay);

            $payload = array();
            $payload['RetiredKeyAlias'] = $retired_alias_name;
            $payload['RetiredKeyId'] = $old_key_id;
            $payload['DaysTillRemoval'] = 30;
            AuditIt("Retired customer master key.", $payload, $user_id, $company_id, $companyparent_id);
        }
        catch(Exception $e)
        {
            $description = "";
            if ( GetStringValue($company_id) !== '') $description = "Company[{$company_id}]";
            if ( GetStringValue($companyparent_id) !== '') $description = "CompanyParent[{$companyparent_id}]";
            print " ERROR: {$description}: " . $e->getMessage() . "\n";
        }

    }



}

/* End of file RotateKey.php */
/* Location: ./application/controllers/cli/RotateKey.php */
