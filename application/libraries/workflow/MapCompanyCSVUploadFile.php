<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MapCompanyCSVUploadFile extends WorkflowLibrary
{
    //protected $ci;                        // See parent class for more information.
    //protected $cli;                       // See parent class for more information.
    //protected $company_id;                // See parent class for more information.
    //protected $companyparent_id;          // See parent class for more information.
    //protected $database_logging_enabled;  // See parent class for more information.
    //protected $debug;                     // See parent class for more information.
    //protected $encryption_key;            // See parent class for more information.
    //protected $identifier;                // See parent class for more information.
    //protected $identifier_type;           // See parent class for more information.
    //protected $job_id;                    // See parent class for more information.
    //protected $user_id;                   // See parent class for more information.
    //protected $verbiage_group             // See parent class for more information.

    public function execute()
    {
        $fh_source = null;
        try
        {
            $CI = $this->ci;

            $this->debug("Mapping companies for upload.");

            // Did the user map the company column?
            $matched = $CI->Mapping_model->does_column_mapping_exist('', $this->companyparent_id, "company");
            if ( $matched) $this->debug("The user matched the company column.");
            if ( ! $matched) $this->debug("The user did not match the company column.");

            if ( $matched )
            {
                // Remove any data found in the CompanyParentMap
                $CI->CompanyParentMap_model->delete_importdata($this->companyparent_id);

                // Did the import file contain a header?
                $has_headers = DoesUploadContainHeaderRow( $this->company_id, $this->companyparent_id );
                if ( $has_headers) $this->debug("The import file contained a header row.");
                if ( ! $has_headers) $this->debug("The import file does not have a header row.");

                // Locate the 'company' column number.
                $col_no = $CI->Mapping_model->get_mapped_column_no(null, $this->companyparent_id, 'company');

                // Find the file on S3 for the company column.
                $parsed_prefix = GetS3Prefix('parsed', $this->identifier, $this->identifier_type);
                $source_filename = "col{$col_no}.txt";
                $fh_source = S3OpenFile(S3_BUCKET, $parsed_prefix, $source_filename, 'r');

                // Create a lookup by normalized company name for each and every company in the file.
                $lookup = array();
                $count = 0;
                $iterator = $this->_readTheFile($fh_source);
                foreach ($iterator as $iteration)
                {
                    $count++;
                    if ( $count === 1 && $has_headers ) continue;

                    $encrypted = IsEncryptedString($iteration);

                    if ( $encrypted && IsEncryptedStringComment($iteration) ) continue;
                    if ( $encrypted )
                    {
                        $iteration = A2PDecryptString($iteration, $this->encryption_key);
                        $normalized = strtoupper($iteration);
                        $normalized = trim($normalized);
                        $lookup[$normalized] = $iteration;
                    }
                }
                if ( is_resource($fh_source) ) fclose($fh_source);

                $this->debug("Found a total of [".count($lookup)."] unique company names in the import");
                foreach($lookup as $normalized=>$company_name)
                {
                    $CI->CompanyParentMap_model->insert_importdata($this->companyparent_id, $company_name);
                }

            }
            else
            {
                // There was no company column mapped, thus we need the user to confirm which company
                // will get ALL of the data in the import file.  Make sure we clean up any import data
                // from previous runs, then stop the user on the map screen.
                $CI->CompanyParentMap_model->delete_importdata($this->companyparent_id);
            }



            $removed_ignored_mappings = GetWorkflowProgressProperty($this->identifier, $this->identifier_type, $this->wf_name, 'RemovedIgnoredMappings');
            if ( $removed_ignored_mappings === '' )
            {
                $CI->CompanyParentMap_model->update_mapping_remove_ignored_mappings($this->companyparent_id);
                SetWorkflowProgressProperty($this->identifier, $this->identifier_type, $this->wf_name, 'RemovedIgnoredMappings', 'TRUE');
            }



            throw new A2PWorkflowWaitingException("Ready for user to select which companies they want to process from their import file.", 'SendParentUploadMapCompaniesWaiting');




        } catch(Exception $e) {
            if ( is_resource($fh_source) ) fclose($fh_source);
            throw $e;
        }
    }

    public function snapshot()
    {
        try
        {
            $CI = $this->ci;

            $type = 'multiple';
            $selected_company_id = GetPreferenceValue($this->identifier, $this->identifier_type, 'companyparentmap', 'selected_company_id');
            if ( $selected_company_id !== '' ) $type = 'single';

            // If the user mapped the whole file to a single company, capture the following.
            if( $type === 'single' )
            {
                $company = $CI->Company_model->get_company($selected_company_id);
                $name = GetArrayStringValue('company_name', $company);
                $this->addSnapshotList('mapped_company_id', $selected_company_id);
                $this->addSnapshotList('mapped_company', $name);

            }


            // If the user mapped multiple companies in their import file, capture the following.
            if ( $type === 'multiple' )
            {
                // Find the column_no specified by the user that represents the "Company" column.
                $prefs = GetPreferences($this->identifier, $this->identifier_type, 'column_map');
                $index = array_search('company', array_column($prefs, 'value') );
                if ( $index !== FALSE )
                {
                    $pref = $prefs[$index];
                    $column_no = GetArrayStringValue('group_code', $pref);
                    $column_no = replaceFor($column_no, "col", "");
                    $this->addSnapshotList('mapped_column_no', $column_no);
                }

                // Find the column name, as specified by the user in the CSV file, and save that to our snapshot.
                $pref = GetPreferenceValue($this->identifier, $this->identifier_type, 'headers', 'user_names');
                $pref = json_decode($pref, true);
                if ( isset($pref['col_lookup'] ) && GetStringValue($column_no) !== '' )
                {
                    $col_lookup = $pref['col_lookup'];
                    $csv_column_header = GetArrayStringValue('col'.$column_no, $col_lookup);
                    if ( $csv_column_header !== '' )
                    {
                        $this->addSnapshotList('column_name', $csv_column_header);
                    }
                }

                $data = $CI->CompanyParentMap_model->select_snapshotdata($this->identifier);
                foreach($data as $item)
                {
                    $this->addSnapshotData($item);
                }

            }

            // SNAP!
            parent::takeSnapshot();
        }
        catch(Exception $e)
        {

        }

    }

    public function rollback( )
    {
        try
        {
            $CI = $this->ci;


        }
        catch(Exception $e)
        {

        }
    }

    private function _readTheFile($handle)
    {
        while(!feof($handle)) {
            yield trim(fgets($handle));
        }

    }


}
