<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateUniversalEmployeeId extends A2PLibrary {

    function __construct( $debug=false )
    {
        parent::__construct($debug);
    }

    public function execute( $company_id, $user_id=null )
    {
        try {
            parent::execute($company_id);

            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");

            // What is our import date?
            $import_date = GetUploadDate($company_id);
            if ( $import_date == "" ) throw new Exception("Invalid import_date.  How is that possible?");

            $this->debug(" ImportDate: [{$import_date}]");
            $this->debug(" ComapnyId:  [{$company_id}]");


            $this->debug("Starting rollback for [{$import_date}]");
            $this->rollback($company_id, $import_date);

            // Copy any records from the ImportData table that do not have an employee id
            // set.  We will be updating this value in the import table shortly, but we want
            // a record of the original data should we need to rollback.
            $this->debug("Capturing original import data information before update.");
            $this->ci->UniversalEmployee_model->insert_rollback($company_id, $import_date);

            // Create a record in the CompanyUniversalEmployee table.  One for each ImportData
            // record that does not have an EmployeeId.  This new record will include a unique
            // GUID that will become our UEID value.  This record will only be created if the
            // tables does not already have an identifying record by Employee SSN.
            $this->debug("Generating a2p-ueid values.");
            $this->ci->UniversalEmployee_model->insert_not_encrypted($company_id, $import_date);


            // Find each record in the CompanyUniversalEmployee table that contains data that
            // has not yet been finalized.  Each of those records will now be tagged as UEIDs and
            // encrypted.
            $this->debug("Encrypting a2p-ueid values.");
            $results = $this->ci->UniversalEmployee_model->select_not_encrypted($company_id, $import_date);
            foreach($results as $item)
            {
                $id = GetArrayStringValue('Id', $item);
                $ueid = GetArrayStringValue('UniversalEmployeeId', $item);
                $ueid = EUID_TAG . $ueid;
                $encrypted = A2PEncryptString($ueid, $this->encryption_key, true);
                $this->ci->UniversalEmployee_model->save_encrypted_ueid($company_id, $import_date, $encrypted, $id);
            }

            // Push the UEID into the EmployeeId column on the ImportData table.
            $this->debug("Saving a2p-uid values to import data.");
            $this->ci->UniversalEmployee_model->update_employee_id($company_id, $import_date);



        } catch(Exception $e) {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }
    public function rollback($company_id, $import_date=null)
    {

        if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");

        // What is our import date?
        $import_date = GetStringValue($import_date);
        if ( $import_date == "" ) $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) throw new Exception("Invalid import_date.  How is that possible?");

        $this->debug("ROLLBACK: Removing universal employee ids that were discovered on this import.");
        $this->ci->UniversalEmployee_model->delete($company_id, $import_date);

        $this->debug("ROLLBACK: cleaning up the rollback table.");
        $this->ci->UniversalEmployee_model->delete_rollback($company_id, $import_date);

    }
    public function restore($company_id, $import_date = null )
    {
        if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");

        // What is our import date?
        $import_date = GetStringValue($import_date);
        if ( $import_date == "" ) $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) throw new Exception("Invalid import_date.  How is that possible?");

        // Push the Employee Id that was stored in the Rollback table for this import
        // back into the ImportData table.  This will need to happen if the user initial did not
        // map the Employee Id, but later decides they want to.
        $this->debug("RESTORE: Putting back the user supplied Employee Id for this import.");
        $this->ci->UniversalEmployee_model->rollback_employee_id($company_id, $import_date);

        // Clean up the data for the universal employee id.  We generated things we no longer need.
        $this->rollback($company_id, $import_date);
    }
    /**
     * validate
     *
     * Take a look at this month's import data and decide if everything
     * is in order or if we need to recalculate the users data from their
     * import file.
     *
     * @param $company_id
     * @param $companyparent_id
     * @return bool
     * @throws Exception
     */
    public function validate($company_id, $companyparent_id="")
    {

        // Validate our inputs.
        if ( GetStringValue($company_id) === '' ) throw new Exception("Missing required input company_id");

        // Ensure we have the encryption key in the cache
        $this->debug("Getting encryptin key.");
        $this->ci->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $this->encryption_key = $this->ci->cache->get("crypto_{$company_id}");
        if ( GetStringValue($this->encryption_key) === 'FALSE' )
        {
            $this->encryption_key = GetCompanyEncryptionKey($company_id);
            $this->ci->cache->save("crypto_{$company_id}", $this->encryption_key, 300);
        }

        // What is our import date?
        $this->debug("Getting import date.");
        $import_date = GetUploadDate($company_id);
        if ( $import_date == "" ) throw new Exception("Invalid import_date.  How is that possible?");


        // Run through any rules that will help us decide if the data
        // we have generated for this import is valid or not.
        $this->debug("Reviewing data.");
        $valid = true;
        if ( GetStringValue($companyparent_id) === '' ) $companyparent_id = GetCompanyParentId($company_id);
        $valid = $this->_rule1($company_id, $companyparent_id, $import_date);

        if ( $valid ) $this->debug("The universal employee data is valid.");
        if ( ! $valid ) $this->debug("The universal employee data is not valid.");

        return $valid;

    }

    /**
     * _rule1
     *
     * Check to see if the user has mapped the EmployeeId column for this import
     * month.  If they have, then we need to check and see if we have any universal
     * employee ids in the import table for the corresponding import month.  If we
     * do, we are in a scenario where the user did not initially map the EmployeeId
     * column, but now they did.  We must restore their original import data.  Thus
     * the universal employee logic is invalid.
     *
     * @param $company_id
     * @param $companyparent_id
     * @param $import_date
     * @return bool
     */
    private function _rule1($company_id, $companyparent_id, $import_date)
    {

        // Is the EmployeeId column mapped?
        $mapped = false;
        $mapping_columns = $this->ci->Mapping_model->get_mapping_columns($company_id, $companyparent_id);
        foreach($mapping_columns as $column)
        {
            if ( GetArrayStringValue('name', $column) === 'eid' )
            {
                $mapped = true;
            }
            if ( $mapped ) break;
        }
        if ( $mapped ) $this->debug("The Employee Id column is mapped.");
        if ( ! $mapped ) $this->debug("The Employee Id column is not mapped.");

        // If the EmployeeId column is mapped, we need to check and see if
        // we have any Universal Employee Ids in the import table.
        if ( $mapped )
        {
            // Since mapping of this column is all or none, we know that the data in the
            // EmployeeId for this month is all universal ids or no universal ids.  Due
            // to that fact, we can just check the first record in the table for this
            // customer and import month.

            $employee_id = $this->ci->UniversalEmployee_model->get_employee_id_sample($company_id, $import_date);
            $this->debug("EmployeeId: [{$employee_id}]");
            $employee_id = A2PDecryptString($employee_id, $this->encryption_key);

            $this->debug("CompanyId: [{$company_id}]");
            $this->debug("ImportDate: [{$import_date}]");
            $this->debug("EmployeeId: [{$employee_id}]");


            if ( StartsWith($employee_id, EUID_TAG) )
            {
                // Okay, we have universal employee numbers in the import table.  This is an edge case
                // where the user originally did not map EmployeeId, but then they changed their mind
                // and re-mapped their columns.  This time, they did map the employee id.  We nee to
                // restore their original data somehow.
                return false;
            }
        }
        return true;
    }


}
