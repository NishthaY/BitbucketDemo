<?php

class ReportTransamericaActuarial_model extends ReportTransamerica_model {

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    public function insert_report_data($company_id, $import_date, $carrier_id)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import-date");
        if ( GetStringValue($carrier_id) == "" ) throw new Exception("Missing required input carrier_id");

        $file = "database/sql/reporttransamericaactuarial/ReportTransamericaActuarialINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($carrier_id)
        );
        SelectIntoInsert( $this->db, $file, $vars );
    }
    public function insert_lost_data($company_id, $import_date, $carrier_id)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");
        if ( GetStringValue($carrier_id) == "" ) throw new Exception("Missing required input carrier_id");
        $recent_date = GetRecentDate($company_id);
        if ( $recent_date == "" ) throw new Exception("Missing required input recent_date");

        $file = "database/sql/reporttransamericaactuarial/ReportTransamericaActuarialINSERT_LostItems.sql";
        $vars = array(
            GetStringValue($import_date),
            GetIntValue($company_id),
            GetStringValue($recent_date),
            GetIntValue($carrier_id)
        );
        SelectIntoInsert( $this->db, $file, $vars );
    }
    public function delete_report_data( $company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) === '' ) GetUploadDate($company_id);
        if ( $import_date == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/reporttransamericaactuarial/ReportTransamericaActuarialDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function delete_report_detail_data( $company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) === '' ) GetUploadDate($company_id);
        if ( $import_date == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/reporttransamericaactuarial/ReportTransamericaActuarialDetailsDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function insert_report_detail_data( $company_id, $import_date, $carrier_id )
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/reporttransamericaactuarial/ReportTransamericaActuarialDetailsINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($carrier_id)
        );
        SelectIntoInsert( $this->db, $file, $vars );
    }
    public function write_report_warning( $company_id, $import_date, $report_name, $reason, $confirmation_required=false)
    {
        if ( GetStringValue($company_id) == "" ) return;
        if ( GetStringValue($import_date) == "" ) return;
        if ( GetStringValue($report_name) == "" ) return;
        if ( GetStringValue($reason) == "" ) return;

        if ( $confirmation_required ) $confirm = 't';
        if ( ! $confirmation_required ) $confirm = 'f';

        $file = "database/sql/reporttransamericaactuarial/ReportReviewWarningsINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetStringValue($report_name),
            GetStringValue($reason),
            GetStringValue($confirm)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function save_report_to_s3($company_id, $import_date, $fh, $encryption_key, $debug=false)
    {
        $import_data_id = "";
        $fh_temp = null;
        try
        {
            $record_count = 1;

            if ($debug) print " memory_usage[" . FormatBytes(memory_get_usage()) . "]\n";

            // Create a timestamp for when this file was generated along with the import
            // month and year.  These will be part of each rows output.  We will also store
            // the timestamp as the encrypted on date for the file too.
            $timestamp = date('YmdHis');
            $import_month = fLeft($import_date, "/");
            $import_year = fRightBack($import_date, "/");

            // Get details about the company.
            $this->company_id = $company_id;
            $company = $this->Company_model->get_company($company_id);
            $company_name = GetArrayStringValue("company_name", $company);

            // Open a memory "file" for read/write...  Ensure the temp file is AWS secured.
            $temp_filename_secure = "tmp_" . RandomString();
            $prefix = replaceFor(GetConfigvalue("upload_prefix"), "COMPANYID", $company_id);
            S3SaveEncryptedFile( S3_BUCKET, $prefix, $temp_filename_secure, '' );
            $fh_temp = S3OpenFile(S3_BUCKET, $prefix, $temp_filename_secure, 'w');

            // Tag the start of this file with metadata about who and when this file was created.
            fputs($fh, "{a2p-comment}:company_id[{$company_id}]" . PHP_EOL);
            fputs($fh, "{a2p-comment}:app_name[".APP_NAME."]" . PHP_EOL);
            fputs($fh, "{a2p-comment}:encrypted_on[".date("c", strtotime($timestamp))."]" . PHP_EOL);

            // Select all master records from the file and loop through them
            // one at a time.
            $sql = GetSQL( "database/sql/reporttransamericaactuarial/ReportTransamericaActuarialDetailsSELECT.sql" );
            $vars = array(
                GetIntValue($company_id),
                GetStringValue($import_date)
            );
            $res = $this->db->query($sql, $vars);
            while( $row = $res->unbuffered_row('array'))
            {

                // Write some helpful output, if debug is on, so we can watch this process.
                if ( $debug && $record_count === 1 ) print " ";
                if ( $debug && $record_count !== 1 && $record_count % 500 === 1 ) print "+";
                if ( $debug && $record_count !== 1 && $record_count % 10000 === 1 ) print " " . ($record_count - 1). " records [".FormatBytes(memory_get_usage())."]\n ";

                // Make sure we are at the start of our temp file.
                rewind($fh_temp);

                // Write out the data about this specific life.
                fputs($fh_temp, $this->normalizeMandatoryPolicyNumber($row['PolicyNumber'], $row['EmployeeSSN'], 10, $encryption_key, "policy"));
                fputs($fh_temp, $this->normalizeMandatoryString($row['GroupNumber'], 10, $encryption_key, "group_number"));
                fputs($fh_temp, $this->normalizeMandatoryState($row['ResidentState'], 10, $encryption_key, 'state'));
                fputs($fh_temp, $this->normalizeString($row['StatusCode'], 10, $encryption_key));
                fputs($fh_temp, $this->normalizeDate($row['IssueDate'], $encryption_key));
                fputs($fh_temp, $this->normalizeDate($row['PaidToDate'], $encryption_key));
                fputs($fh_temp, $this->normalizeDate($row['SystemTerminationDate'], $encryption_key));
                fputs($fh_temp, $this->normalizeString($row['BillingMode'], 10, $encryption_key));
                fputs($fh_temp, $this->normalizeMandatoryMoney($row['ModalPremium'], 8, $encryption_key, 'monthly_cost'));
                fputs($fh_temp, $this->normalizeMandatoryDate($row['InsuredDOB'], $encryption_key, 'dob'));
                fputs($fh_temp, $this->normalizeGender($row['InsuredSex'], 10, $encryption_key));
                fputs($fh_temp, $this->normalizeMandatoryState($row['InsuredState'], 10, $encryption_key, 'enrollment_state'));
                fputs($fh_temp, $this->normalizeMandatoryZip($row['InsuredZIP'], $encryption_key, 'postalcode'));
                fputs($fh_temp, $this->normalizeSSN($row['InsuredSSN'], $encryption_key));
                fputs($fh_temp, $this->normalizeMandatoryString($row['InsuredFirstName'], 20, $encryption_key, 'first_name'));
                fputs($fh_temp, $this->normalizeMandatoryString($row['InsuredLastName'], 30, $encryption_key, 'last_name'));

                fputs($fh_temp, $this->normalizeMandatoryProductType($row['ProductType'], 1, $encryption_key, 'plan_type'));
                fputs($fh_temp, $this->normalizeMandatoryTier($row['Tier'], 2, $encryption_key, 'coverage_tier'));
                fputs($fh_temp, $this->normalizeMandatoryOption($row['Option'], 2, $encryption_key, 'plan'));

                fputs($fh_temp, $this->normalizeString($timestamp, 14, $encryption_key));
                fputs($fh_temp, $this->normalizeString($import_year, 4, $encryption_key));
                fputs($fh_temp, $this->normalizeString($import_month, 2, $encryption_key));

                // Move the pointer to the front of the file.
                rewind($fh_temp);

                // Read the line of data and encrypt it as a string.
                $line = fgets($fh_temp);
                $line = A2PEncryptString(trim($line, "\n\r"), $encryption_key, true);

                // Write the encrypted string as output.
                fputs($fh, $line.PHP_EOL);

                $record_count++;



            }
            $res->free_result();

            // Move the pointer to the front of the file.
            rewind($fh_temp);


            // Close the temp file and delete it.
            if ( is_resource($fh_temp) ) fclose($fh_temp);
            S3DeleteFile(S3_BUCKET, $prefix, $temp_filename_secure);

            if ( $debug ) print "+ " . ($record_count - 1) . " records\n";
            if ( $debug ) print " memory_usage[".FormatBytes(memory_get_usage())."]\n";

        }catch ( ReportException $e )
        {
            if ( is_resource($fh_temp) ) fclose($fh_temp);
            $report = $this->Reporting_model->select_report_type(REPORT_TYPE_TRANSAMERICA_ACTUARIAL_CODE);
            $report_name = GetArrayStringValue("Display", $report);

            $helpful_message = $e->getAdditionalMessage($company_id);
            if ( GetStringValue($helpful_message) === '' ) $helpful_message = $e->getMessage();

            $this->write_report_warning($company_id, $import_date, $report_name, $helpful_message, true);
            throw $e;

        }
        catch(Exception $e)
        {
            if ( is_resource($fh_temp) ) fclose($fh_temp);

            $report = $this->Reporting_model->select_report_type(REPORT_TYPE_TRANSAMERICA_ACTUARIAL_CODE);
            $report_name = GetArrayStringValue("Display", $report);

            $this->write_report_warning($company_id, $import_date, $report_name, $e->getMessage(), true);
            throw $e;
        }


        return $record_count;
    }

    /**
     * normalizeMandatoryPolicyNumber
     *
     * The policy number is the A2P EmployeeId field.  Since the employee id
     * is a conditional field between EmployeeId and EmployeeSSN, we will return
     * first the EmployeeId as the policy number.  If that is not available, we
     * will return the EmployeeSSN.  Sometimes the EmployeeId is auto-generated
     * by A2P.  The auto-generated employee id is never shown the the end user
     * so that represents a blank EmployeeId.
     *
     * @param $str_eid
     * @param $str_ssn
     * @param $length
     * @param $encryption_key
     * @param $column_code
     * @return bool|string
     */
    protected function normalizeMandatoryPolicyNumber( $str_eid, $str_ssn, $length, $encryption_key, $column_code )
    {
        // EMPLOYEE ID
        // This is a string, right?
        $str = GetStringValue($str_eid);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key) !== '' )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        // Check to see if the employee id starts with our auto-generated tag.
        // If it was, we don't want to show that on the report.
        if ( StartsWith($str, EUID_TAG) ) $str = "";

        // We have an EmployeeId.  Let's return that.
        if ( $str !== '' )
        {
            return $this->normalizeMandatoryString($str, $length, $encryption_key, $column_code);
        }


        // EMPLOYEE SSN
        // This is a string, right?
        $str = GetStringValue($str_ssn);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key) !== '' )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        $this->mandatory_check($str, $column_code);
        return $this->normalizeMandatoryString($str, $length, $encryption_key, $column_code);
    }

    protected function normalizeMandatorySSN($str, $encryption_key, $column_code)
    {
        $this->mandatory_check($str, $column_code);
        return $this->normalizeSSN($str, $encryption_key);
    }
    protected function normalizeSSN($str, $encryption_key)
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key !== '' ) )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        $str = $this->normalizeString($str, 9, $encryption_key);

        return $str;
    }

}


/* End of file EligibilityReport_model.php */
/* Location: ./system/application/models/EligibilityReport_model.php */
