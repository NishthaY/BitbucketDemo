<?php

class ReportTransamericaCommissions_model extends ReportTransamerica_model {

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    public function delete_report_data( $company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) === '' ) GetUploadDate($company_id);
        if ( $import_date == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/reporttransamericacommission/ReportTransamericaCommissionDELETE.sql";
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

        $file = "database/sql/reporttransamericacommission/ReportTransamericaCommissionDetailDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function insert_report_data( $company_id, $import_date, $carrier_id )
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");
        if ( GetStringValue($carrier_id) == "" ) throw new Exception("Missing required input carrier_id");

        $file = "database/sql/reporttransamericacommission/ReportTransamericaCommissionINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($carrier_id)
        );
        SelectIntoInsert( $this->db, $file, $vars );
    }
    public function insert_report_data_lost_lives( $company_id, $import_date )
    {

        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");

        // The Recent Date, is the last finalized date.  ( i.e.  The previous month. )  If we don't have one, then
        // there is no previous data.  In that case, there is nothing to do here.
        $recent_date = GetRecentDate($company_id);
        if ( GetStringValue($recent_date) === '' ) return;

        $file = "database/sql/reporttransamericacommission/ReportTransamericaCommissionINSERT_LostLives.sql";
        $vars = array(
            GetStringValue($import_date),
            GetIntValue($company_id),
            GetStringValue($recent_date)
        );
        SelectIntoInsert( $this->db, $file, $vars );
    }

    public function insert_report_detail_data( $company_id, $import_date )
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");
        $file = "database/sql/reporttransamericacommission/ReportTransamericaCommissionDetailINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        SelectIntoInsert( $this->db, $file, $vars );
    }
    public function insert_report_detail_data_lost_lives( $company_id, $import_date )
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");

        // The Recent Date, is the last finalized date.  ( i.e.  The previous month. )  If we don't have one, then
        // there is no previous data.  In that case, there is nothing to do here.
        $recent_date = GetRecentDate($company_id);
        if ( GetStringValue($recent_date) === '' ) return;

        $file = "database/sql/reporttransamericacommission/ReportTransamericaCommissionDetailINSERT_LostLives.sql";
        $vars = array(
            GetStringValue($recent_date),
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        SelectIntoInsert( $this->db, $file, $vars );
    }
    public function update_report_data( $company_id, $import_date )
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/reporttransamericacommission/ReportTransamericaCommissionUPDATE_Enabled.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function write_report_warning( $company_id, $import_date, $report_name, $reason, $confirmation_required=false)
    {
        if ( GetStringValue($company_id) == "" ) return;
        if ( GetStringValue($import_date) == "" ) return;
        if ( GetStringValue($report_name) == "" ) return;
        if ( GetStringValue($reason) == "" ) return;

        if ( $confirmation_required ) $confirm = 't';
        if ( ! $confirmation_required ) $confirm = 'f';

        $file = "database/sql/reporttransamericacommission/ReportReviewWarningsINSERT.sql";
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
            fputs($fh, "{a2p-comment}:encrypted_on[".date("c")."]" . PHP_EOL);

            // Select all master records from the file and loop through them
            // one at a time.
            $sql = GetSQL( "database/sql/reporttransamericacommission/ReportTransamericaCommissionDetailSELECT.sql" );
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
                if ( $debug && $record_count !== 1 && $record_count % 10000 === 1 ) print " " . ($record_count - 1) . " records [".FormatBytes(memory_get_usage())."]\n ";

                // Make sure we are at the start of our temp file.
                rewind($fh_temp);

                // Write out the data about this specific life.
                fputs($fh_temp, $this->normalizeMandatoryString($row['MasterPolicy'], 10, $encryption_key, "policy"));
                fputs($fh_temp, $this->normalizeEmployeeID_SSN($row['EmployeeId'], $row['EmployeeSSN'], 16, $encryption_key, "eid"));
                fputs($fh_temp, $this->normalizeMandatoryDate($row['TierEffectiveDate'], $encryption_key, "coverage_start_date"));
                fputs($fh_temp, $this->normalizeMandatoryTier($row['Tier'], 2, $encryption_key, 'coverage_tier'));
                fputs($fh_temp, $this->normalizeMoney($row['TierMonthlyPremium'], 7, $encryption_key));
                fputs($fh_temp, $this->normalizeString($row['CurrentCertStatus'], 1, $encryption_key));
                fputs($fh_temp, $this->normalizeDate($row['OriginalCertIssueDate'], $encryption_key));
                fputs($fh_temp, $this->normalizeCertTermDate($row['CertTermDate'], $encryption_key));
                fputs($fh_temp, $this->normalizeDate($row['MonthPaidFor'], $encryption_key));
                fputs($fh_temp, $this->normalizeEmployeeName($row['Suffix'],$row['FirstName'],$row['MiddleName'],$row['LastName'], 40, $encryption_key));
                fputs($fh_temp, $this->normalizeMoney($row['PremiumFirstYear'], 7, $encryption_key));
                fputs($fh_temp, $this->normalizeMoney($row['PremiumRenewal'], 7, $encryption_key));

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

        }
        catch ( ReportException $e )
        {
            if ( is_resource($fh_temp) ) fclose($fh_temp);
            $report = $this->Reporting_model->select_report_type(REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE);
            $report_name = GetArrayStringValue("Display", $report);

            $helpful_message = $e->getAdditionalMessage($company_id);
            if ( GetStringValue($helpful_message) === '' ) $helpful_message = $e->getMessage();

            $this->write_report_warning($company_id, $import_date, $report_name, $helpful_message, true);
            throw $e;

        }
        catch(Exception $e)
        {
            if ( is_resource($fh_temp) ) fclose($fh_temp);

            $report = $this->Reporting_model->select_report_type(REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE);
            $report_name = GetArrayStringValue("Display", $report);

            $this->write_report_warning($company_id, $import_date, $report_name, $e->getMessage(), true);
            throw $e;
        }


        return $record_count;
    }

    /**
     * normalizeEmployeeID_SSN
     *
     * The eid column will display the Employee Id value.  If that is missing OR if that
     * column contains an A2P Universal Employee Id we will display the Employee SSN.
     *
     * @param $str_eid
     * @param $str_ssn
     * @param $length
     * @param $encryption_key
     * @param $column_code
     * @return bool|string
     */
    protected function normalizeEmployeeID_SSN( $str_eid, $str_ssn, $length, $encryption_key, $column_code )
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
            $this->mandatory_check($str, $column_code);
            return $this->normalizeString($str, $length, $encryption_key, STR_PAD_RIGHT, ' ', $column_code);
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
        return $this->normalizeString($str, $length, $encryption_key, STR_PAD_RIGHT, ' ', $column_code);
    }
    protected function normalizeEmployeeName( $suffix, $first_name, $middle_name, $last_name, $length, $encryption_key )
    {
        $this->mandatory_check($first_name, "first_name");
        $this->mandatory_check($last_name, "last_name");

        // This is a string, right?
        $suffix_str = GetStringValue($suffix);
        $first_name_str = GetStringValue($first_name);
        $middle_name_str = GetStringValue($middle_name);
        $last_name_str = GetStringValue($last_name);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key !== '' ) )
        {
            $encrypted = IsEncryptedString($suffix_str);
            if ( $encrypted ) $suffix_str = A2PDecryptString($suffix_str, $encryption_key);
        }
        if ( GetStringValue($encryption_key !== '' ) )
        {
            $encrypted = IsEncryptedString($first_name_str);
            if ( $encrypted ) $first_name_str = A2PDecryptString($first_name_str, $encryption_key);
        }
        if ( GetStringValue($encryption_key !== '' ) )
        {
            $encrypted = IsEncryptedString($middle_name_str);
            if ( $encrypted ) $middle_name_str = A2PDecryptString($middle_name_str, $encryption_key);
        }
        if ( GetStringValue($encryption_key !== '' ) )
        {
            $encrypted = IsEncryptedString($last_name_str);
            if ( $encrypted ) $last_name_str = A2PDecryptString($last_name_str, $encryption_key);
        }



        // LAST (SUFFIX), FIRST (MIDDLE)
        // Items in () are only supplied if available.
        $str = $last_name_str;
        if ( $suffix_str !== '' ) $str .= " {$suffix_str}";
        $str .= ', ' . $first_name_str;
        if ( $middle_name_str !== '' ) $str .= " {$middle_name_str}";

        $str = $this->normalizeString($str, $length);
        return $str;

    }
    protected function normalizeCertTermDate($str,  $encryption_key)
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // If we don't have a CertTermDate, then we send all zeros.
        if ( $str === '' ) return str_pad("", 8, STR_PAD_LEFT, '0');

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key !== '' ) )
        {
            $encrypted = IsEncryptedString($str);
            if ( $encrypted ) $str = A2PDecryptString($str, $encryption_key);
        }

        if ( $str !== '' )
        {
            $timestamp = strtotime($str);
            $str = date('Ymd', $timestamp);
        }
        $str = $this->normalizeString($str, 8);

        return $str;
    }

    protected function normalizeMandatoryDate($str, $encryption_key, $column_code )
    {
        // Validate that we pass the mandatory check.
        $this->mandatory_check($str, $column_code);

        return $this->normalizeDate($str, $encryption_key);
    }
    protected function normalizeDate($str,  $encryption_key)
    {


        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key !== '' ) )
        {
            $encrypted = IsEncryptedString($str);
            if ( $encrypted ) $str = A2PDecryptString($str, $encryption_key);
        }

        if ( $str !== '' )
        {
            $timestamp = strtotime($str);
            $str = date('Ymd', $timestamp);  // YYYYMMDD format for this report.
        }
        $str = $this->normalizeString($str, 8);

        return $str;
    }

    /**
     * normalizeMoney
     *
     * Return a Transamerica money value for the commissions report.  This money value
     * will remove all commas and dollar indicators.  It will NOT contain a decimal
     * point and support up to two decimal places.  The left will be padded
     * with zeros.
     *
     * Example: 0000692470 ~ $6,924.70 ( length = 10 )
     *
     * @param $str
     * @param $length
     * @param $encryption_key
     * @return string
     */
    protected function normalizeMoney($str, $length, $encryption_key)
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key !== '' ) )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }


        if ( strpos($str, '.') === FALSE ) $str = $str . ".00";

        // Cents
        $right = fRightBack($str, ".");
        $right = StripNonNumeric($right);
        $right = $this->normalizeString($right, 2, null, STR_PAD_RIGHT, '0');

        // Dollars
        $left = fLeftBack($str, ".");
        $left = StripNonNumeric($left);
        $left = $this->normalizeString($left, $length - 2, null, STR_PAD_LEFT, '0');

        $str = "{$left}{$right}";
        return $str;
    }


}


/* End of file EligibilityReport_model.php */
/* Location: ./system/application/models/EligibilityReport_model.php */
