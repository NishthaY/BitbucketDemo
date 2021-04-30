<?php

class ReportTransamericaEligibility_model extends ReportTransamerica_model {

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    public function exists_report_data( $company_id, $import_date )
    {
        $file = GetSQL( "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilitySELECT.sql" );
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        return GetDBExists( $this->db, $file, $vars );
    }
    public function write_report_warning( $company_id, $import_date, $report_name, $reason, $confirmation_required=false)
    {
        if ( GetStringValue($company_id) == "" ) return;
        if ( GetStringValue($import_date) == "" ) return;
        if ( GetStringValue($report_name) == "" ) return;

        if ( $confirmation_required ) $confirm = 't';
        if ( ! $confirmation_required ) $confirm = 'f';

        $file = "database/sql/reporttransamericaeligibility/ReportReviewWarningsINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetStringValue($report_name),
            GetStringValue($reason),
            GetStringValue($confirm)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function delete_report_data( $company_id, $import_date=null ) {

        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) === '' ) GetUploadDate($company_id);
        if ( $import_date == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function insert_report_data($company_id, $import_date, $carrier_id)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($carrier_id) == "" ) throw new Exception("Missing required input company_id");

        // First, we will collect all of the records.  The relationship column might be the SSN column or
        // it might be the EID column.  We don't know which yet.  So on this query, we capture both possible
        // relationship columns but we don't decide which one yet.
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($carrier_id)
        );
        SelectIntoInsert( $this->db, $file, $vars );


        // Next, we will pick either the SSN relationship or the EID relationship.  For now, we are assuming
        // the EmployeeSSN is the relationship column.  We can think of a dataset they could give us where it would
        // be EmployeeID.  So maybe this logic will need to grow down the road.
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityUPDATE_RelationshipColumn.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($carrier_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function insert_lost_data($company_id, $import_date, $carrier_id)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($carrier_id) == "" ) throw new Exception("Missing required input company_id");
        $recent_date = GetRecentDate($company_id);
        if ( $recent_date == "" ) throw new Exception("Missing required input recent_date");

        // First we will collect all of the records.  The relationship column could be SSN or it could be
        // EID.  We don't know which yet.  So this query, we capture both possible relationship columns
        // but do not decide on which one.
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityINSERT_LostItems.sql";
        $vars = array(
            GetStringValue($import_date),
            GetIntValue($company_id),
            GetStringValue($recent_date),
            GetIntValue($carrier_id)
        );
        SelectIntoInsert( $this->db, $file, $vars );

        // Next, we will pick either the SSN relationship or the EID relationship.  For now, we are assuming
        // the EmployeeSSN is the relationship column.  We can think of a dataset they could give us where it would
        // be EmployeeID.  So maybe this logic will need to grow down the road.
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityUPDATE_RelationshipColumn.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($carrier_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function delete_report_details( $company_id, $import_date=null ) {

        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) === '' ) GetUploadDate($company_id);
        if ( $import_date == "" ) throw new Exception("Missing required input import_date");

        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsDELETE.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function insert_report_details($company_id, $import_date, $carrier_id)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input company_id");


        // First, we will collect all of the details.  The relationship column might be the SSN column or
        // it might be the EID column.  We don't know which yet.  So on this query, we capture both possible
        // relationship columns but we don't decide which one yet.
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsINSERT.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($carrier_id)
        );
        SelectIntoInsert( $this->db, $file, $vars );


        // Next, we will pick either the SSN relationship or the EID relationship.  For now, we are assuming
        // the EmployeeSSN is the relationship column.  We can think of a dataset they could give us where it would
        // be EmployeeID.  So maybe this logic will need to grow down the road.
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsUPDATE_RelationshipColumn.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
            GetIntValue($carrier_id)
        );
        ExecuteSQL( $this->db, $file, $vars );


    }
    public function update_report_details_ChildTierMismatch( $company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input company_id");

        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsUPDATE_ChildTierMismatch.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update_report_details_IgnoreTierEO( $company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input company_id");
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsUPDATE_IgnoreTierEO.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

    public function update_report_details_IgnoreTierES( $company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input company_id");

        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsUPDATE_IgnoreTierES.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update_report_details_IgnoreTierEC( $company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input company_id");

        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsUPDATE_IgnoreTierEC.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update_report_NoDetails( $company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input company_id");
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityUPDATE_NoDetails.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function insert_warnings( $company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input company_id");
        
        // Detail Table Warnings
        $file = "database/sql/reportreview/ReportReviewWarningINSERT_TransamericaEligibilityDetailWarnings.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
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

            // This static array will be used to query the database for information
            // about records related to each data record.
            $lookup_vars = array(
                GetIntValue($company_id),
                GetStringValue($import_date),
                GetStringValue("carrier_id"),
                GetStringValue("plantype_id"),
                GetStringValue("plan_id"),
                GetStringValue("coveragetier_id"),
                GetStringValue("relationship_id"),
            );

            // This file has a header record, but we need data from the first row to write it.
            // Set a flag that tells us if we should or should not write the header as we loop
            // through the results.
            $write_header = true;

            // Select all master records from the file and loop through them
            // one at a time.
            $sql = GetSQL( "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilitySELECT.sql" );
            $vars = array(
                GetIntValue($company_id),
                GetStringValue($import_date)
            );
            $res = $this->db->query($sql, $vars);
            while( $row = $res->unbuffered_row('array'))
            {

                // Init the variables that will be collected and used over the family unit collection.
                $indemnity_amount ="";
                $creditable_coverage = "";
                $master_policy = "";
                $employee_ssn = "";

                // Select all of the lives associated with the master record.
                $sql = GetSQL( "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsSELECT.sql" );
                $lookup_vars[2] = GetStringValue($row['CarrierId']);
                $lookup_vars[3] = GetStringValue($row['PlanTypeId']);
                $lookup_vars[4] = GetStringValue($row['PlanId']);
                $lookup_vars[5] = GetStringValue($row['CoverageTierId']);
                //$lookup_vars[6] = GetStringValue($row['EmployeeNumber']);
                $lookup_vars[6] = GetStringValue($row['RelationshipId']);
                $res2 = $this->db->query($sql, $lookup_vars);

                // Write some helpful output, if debug is on, so we can watch this process.
                if ( $debug && $record_count === 1 ) print " ";
                if ( $debug && $record_count !== 1 && $record_count % 500 === 1 ) print "+";
                if ( $debug && $record_count !== 1 && $record_count % 10000 === 1 ) print " " . ($record_count - 1) . " records [".FormatBytes(memory_get_usage())."]\n ";

                // Make sure we are at the start of our temp file.
                rewind($fh_temp);

                // Write out each of the lives we found.  Keep track of how many
                // there were, you can't write more tan 15 of them.
                $life_count = 0;
                while( $life = $res2->unbuffered_row('array'))
                {
                    // VALIDATE LIFE
                    // Check to see if the life we just pulled from the database has data.  No, just skip it.
                    if( ! isset($life['ImportDataId']) ) continue;
                    
                    // Before we write out the first row, write out the file header record.
                    if ( $write_header )
                    {
                        // Only write the header once!
                        $write_header = false;

                        // What time are we generating this report?
                        $prefered_zone = GetConfigValue("timezone_display");
                        $d = new DateTime();
                        if ( $prefered_zone != "" ) $d->setTimezone(new DateTimeZone($prefered_zone));
                        $now = $d->format("m/d/Y H:i");

                        fputs($fh_temp, $this->normalizeString("XH", 2));
                        fputs($fh_temp, $this->normalizeString("", 25));
                        fputs($fh_temp, $this->normalizeString($company_name, 30));
                        fputs($fh_temp, $this->normalizeString("", 25));
                        fputs($fh_temp, $this->normalizeString($life['EmployeeGroupNumber'], 10, $encryption_key));
                        fputs($fh_temp, $this->normalizeString("", 25));
                        fputs($fh_temp, $this->normalizeString($now, 20));
                        fputs($fh_temp, $this->normalizeString("", 25));
                        fputs($fh_temp, $this->normalizeString(date("m/t/Y", strtotime($import_date)), 20));

                        // Move the pointer to the front of the file.
                        rewind($fh_temp);

                        // Read the line of data and encrypt it as a string.
                        $line = fgets($fh_temp);
                        $line = A2PEncryptString(trim($line, "\n\r"), $encryption_key, true);

                        // Write the encrypted string as output.
                        fputs($fh, $line.PHP_EOL);

                        // Move the pointer to the front of the file.
                        rewind($fh_temp);

                    }

                    // The very first life we find will be responsible for filling in the
                    // first 12 columns of the report.
                    if ( $life_count === 0 )
                    {
                        $import_data_id = $life['ImportDataId'];
                        $creditable_coverage = $life['CreditableCoverage'];
                        $indemnity_amount = $life['IndemnityAmount'];
                        $master_policy = $life['MasterPolicy'];
                        $employee_ssn = $life['EmployeeSSN'];
                        fputs($fh_temp, $this->normalizeMandatoryString($life['EmployeeGroupNumber'], 10, $encryption_key, 'group_number'));
                        fputs($fh_temp, $this->normalizeEmployeeNumber($life['EmployeeNumber'], 20, $encryption_key, STR_PAD_RIGHT, ' ', 'eid'));
                        fputs($fh_temp, $this->normalizeMandatoryString($this->scrubAddress($life['AddressLine1'], $encryption_key), 25, '', "address1"));
                        fputs($fh_temp, $this->normalizeString($this->scrubAddress($life['AddressLine2'], $encryption_key), 25, ''));
                        fputs($fh_temp, $this->normalizeMandatoryString($this->scrubAddress($life['City'], $encryption_key), 15, '', "city"));
                        fputs($fh_temp, $this->normalizeMandatoryState($life['State'], 2, $encryption_key, "state"));
                        fputs($fh_temp, $this->normalizeMandatoryZip($life['ZipCode'], $encryption_key, 'postalcode'));
                        fputs($fh_temp, $this->normalizeZipPlus4($life['ZipCodeExpansion'], $encryption_key));
                        fputs($fh_temp, $this->normalizeCountry($life['CountryCode'], $encryption_key));
                        fputs($fh_temp, $this->normalizePhone($life['PhoneNumber'], $encryption_key));
                        fputs($fh_temp, $this->normalizeMandatoryState($life['IssueState'], 2, $encryption_key,'enrollment_state'));
                        fputs($fh_temp, $this->normalizeDate($life['PaidToDate'], $encryption_key));
                    }

                    // Write out the data about this specific life.
                    fputs($fh_temp, $this->normalizeMandatoryRelationship($life['RelationshipCode'], $encryption_key, 'relationship'));
                    fputs($fh_temp, $this->normalizeString($life['Status'], 1, $encryption_key));
                    fputs($fh_temp, $this->normalizeMandatoryString($life['FirstName'], 15, $encryption_key, 'first_name'));
                    fputs($fh_temp, $this->normalizeMandatoryString($life['LastName'], 19, $encryption_key, 'last_name'));
                    fputs($fh_temp, $this->normalizeString($life['MiddleInitial'], 1, $encryption_key));
                    fputs($fh_temp, $this->normalizeMandatoryDate($life['EffectiveDate'], $encryption_key, 'coverage_start_date'));
                    fputs($fh_temp, $this->normalizeDate($life['TerminationDate'], $encryption_key));
                    fputs($fh_temp, $this->normalizeMandatoryDate($life['DateOfBirth'], $encryption_key, 'dob'));
                    fputs($fh_temp, $this->normalizeGender($life['Gender'], 1, $encryption_key));


                    // Keep track of the number of lives we process.
                    $life_count++;
                    if ( $life_count >= 15 ) break;
                }
                $res2->free_result();

                // If we found no lives for this family group, then we don't want to write this record!
                // Go ahead and add the IssueCode so we know why this item dropped from the file.
                if ( $life_count === 0 ) {
                    ExecuteSQL($this->db, "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityUPDATE_NoDetailsByGroup.sql", $lookup_vars);
                    continue;
                }

                // Write out blank lives until we have written 15 of them.
                for($i=$life_count;$i<15;$i++)
                {
                    // Add a blank child.
                    fputs($fh_temp, "                                                                    " ); // 68 chars
                }

                // Write the the three columns from the master record that help identify this live.
                fputs($fh_temp, $this->normalizeMandatoryProductType($row['ProductType'], 1, $encryption_key, 'plan_type'));
                fputs($fh_temp, $this->normalizeMandatoryOption($row['Option'], 2, $encryption_key, 'plan'));
                fputs($fh_temp, $this->normalizeMandatoryTier($row['Tier'], 2, $encryption_key, 'coverage_tier'));

                // We saved these last four items off the first life we encountered.
                fputs($fh_temp, $this->normalizeString($creditable_coverage, 2, $encryption_key));
                fputs($fh_temp, $this->normalizeMoney($indemnity_amount, 10, $encryption_key));
                fputs($fh_temp, $this->normalizeString($master_policy, 10, $encryption_key));
                fputs($fh_temp, $this->normalizeString($employee_ssn, 9, $encryption_key));

                // Add some filler to the end of the file per their specification.
                fputs($fh_temp, $this->normalizeString("", 94));

                // Move the pointer to the front of the file.
                rewind($fh_temp);

                // Read the line of data and encrypt it as a string.
                $line = fgets($fh_temp);
                $line = A2PEncryptString(trim($line, "\n\r"), $encryption_key, true);

                // Write the encrypted string as output.
                fputs($fh, $line.PHP_EOL);

                // Free up the variables you had to create in the loop.
                unset($creditable_coverage);
                unset($indemnity_amount);
                unset($import_data_id);
                unset($employee_ssn);

                $record_count++;



            }
            $res->free_result();

            // Move the pointer to the front of the file.
            rewind($fh_temp);

            // Write the footer.
            fputs($fh_temp, $this->normalizeString("XT", 2));
            fputs($fh_temp, $this->normalizeString("", 3));
            fputs($fh_temp, $this->normalizeString("COUNT", 5));
            fputs($fh_temp, $this->normalizeString("", 3));
            fputs($fh_temp, $this->normalizeRowCount($record_count - 1)); // Always 15 in length.
            fputs($fh_temp, $this->normalizeString("", 215));

            // Move the pointer to the front of the file.
            rewind($fh_temp);

            // Read the line of data and encrypt it as a string.
            $line = fgets($fh_temp);
            $line = substr($line, 0, 243);
            //$line = A2PEncryptString(trim($line, "\n\r"), $encryption_key, true);

            // Write the encrypted string as output.
            fputs($fh, $line);

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
            $report = $this->Reporting_model->select_report_type(REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE);
            $report_name = GetArrayStringValue("Display", $report);

            $helpful_message = $e->getAdditionalMessage($company_id);
            if ( GetStringValue($helpful_message) === '' ) $helpful_message = $e->getMessage();

            $this->write_report_warning($company_id, $import_date, $report_name, $helpful_message, true);
            throw $e;

        }
        catch(Exception $e)
        {
            if ( is_resource($fh_temp) ) fclose($fh_temp);

            $report = $this->Reporting_model->select_report_type(REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE);
            $report_name = GetArrayStringValue("Display", $report);

            $this->write_report_warning($company_id, $import_date, $report_name, $e->getMessage(), true);
            throw $e;
        }


        return $record_count;
    }
    protected function scrubAddress($str, $encryption_key='')
    {

        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        $encrypted = false;
        if ( GetStringValue($encryption_key !== '' ) )
        {
            $encrypted = IsEncryptedString($str);
            if ( $encrypted )
            {
                $str = A2PDecryptString($str, $encryption_key);
            }
        }

        // Scrub some characters out of the string before we normalize it.
        $scrubbed_str = $str;
        $scrubbed_str = replaceFor($scrubbed_str, "'", "");
        $scrubbed_str = replaceFor($scrubbed_str, "\"", "");
        $scrubbed_str = replaceFor($scrubbed_str, ":", "");
        $scrubbed_str = replaceFor($scrubbed_str, ";", "");
        $scrubbed_str = replaceFor($scrubbed_str, ",", "");
        $scrubbed_str = replaceFor($scrubbed_str, ".", "");
        $scrubbed_str = replaceFor($scrubbed_str, "&", "");
        $scrubbed_str = replaceFor($scrubbed_str, "#", "");
        $scrubbed_str = replaceFor($scrubbed_str, "$", "");
        $scrubbed_str = replaceFor($scrubbed_str, "%", "");
        $scrubbed_str = replaceFor($scrubbed_str, chr(145), ""); // Microsoft single tick left.
        $scrubbed_str = replaceFor($scrubbed_str, chr(146), ""); // Microsoft single tick right.
        $scrubbed_str = replaceFor($scrubbed_str, chr(147), ""); // Microsoft double quote left.
        $scrubbed_str = replaceFor($scrubbed_str, chr(148), ""); // Microsoft double quote right.
        $scrubbed_str = replaceFor($scrubbed_str, chr(151), "-"); // Microsoft emdash to hyphen

        return $scrubbed_str;
    }




    protected function normalizeZipPlus4($str, $encryption_key)
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key !== '' ) )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        $str = trim($str);
        $str = StripNonNumeric($str);
        if ( strlen($str) !== 9) $str = "";
        if ( strlen($str) > 4 ) $str = substr($str, -4);

        // make sure the string is of the correct length now.
        $str = $this->normalizeString($str, 4);
        return $str;
    }


    protected function normalizeCountry($str, $encryption_key)
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key !== '' ) )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        // Map the country to a country code.
        $country_code = 'US';
        $str = $this->normalizeString($country_code, 30);

        return $str;
    }


    protected function normalizePhone($str, $encryption_key)
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key !== '' ) )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        $str = StripNonNumeric($str);
        if ( strlen($str) === 11 && StartsWith($str, '1') ) $str = substr($str, -10);
        $str = $this->normalizeString($str, 10);

        return $str;

    }

    protected function normalizeMandatoryRelationship( $str, $encryption_key, $column_code)
    {
        $this->mandatory_check($str, $column_code);
        return $this->normalizeRelationship($str, $encryption_key);
    }
    protected function normalizeRelationship($str, $encryption_key)
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key !== '' ) )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        $str = strtolower($str);
        if ( $str === 'employee' )  $str = 'M';
        else if ( $str === 'spouse' )    $str = 'S';
        else if ( $str === 'dependent' ) $str = 'C';
        else $str = '';

        $str = $this->normalizeString($str, 1, $encryption_key);
        return $str;
    }





    protected function normalizeRowCount($row_count, $encryption_key=null)
    {

        // We are building a number that looks like this: 000,000,000,000
        // For example: 000,001,234,567

        // This is a string, right?
        $str = GetStringValue($row_count);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key !== '' ) )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }


        // Create an array that will hold all four segments of
        // the row count.
        $number_parts = array();
        $number_parts[0] = "000";
        $number_parts[1] = "000";
        $number_parts[2] = "000";
        $number_parts[3] = "000";

        // Turn out input into a number.
        $number = GetIntValue($str);

        // Format the number so that it has our seperator and no decimal points.
        $number = number_format($number,0,'.',',');

        // Cut the number into segments and store them in our array.
        $numbers = explode(',', $number);
        $numbers = array_reverse($numbers);
        while( count($numbers) < 4 )
        {
            $numbers[] = "000";
        }
        $numbers = array_reverse($numbers);

        // Construct our output.
        // Make sure each segment is 3 characters long and padded with zeros.
        $output = "";
        for($i=0;$i<count($numbers);$i++) {
            $part = $numbers[$i];
            if (strlen($part) < 3)
            {
                $part = str_pad($part, 3, STR_PAD_LEFT, '0');
            }
            $output .= $part . ",";
        }
        $output = fLeftBack($output, ',');

        return $output;
    }


    public function clean_worker_table( $company_id, $import_date )
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsWorkerDELETE_ByCompanyImport.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function capture_ignore_tier_change_dependents( $company_id, $import_date )
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsWorkerINSERT_IgnoreTierChange.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        CopyFromInto($this->db, $file, $vars);
    }
    public function ignore_tier_change_update_parent_not_terminating( $company_id, $import_date )
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsWorkerUPDATE_ParentNotTerminating.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function ignore_tier_change_lost_lives($company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsWorkerUPDATE_LostLives.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function ignore_tier_change_removed_flagged($company_id, $import_date)
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input import_date");
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsWorkerDELETE_Flagged.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function ignore_tier_change_update_from_worker( $company_id, $import_date )
    {
        if ( GetStringValue($company_id) == "" ) throw new Exception("Missing required input company_id");
        if ( GetStringValue($import_date) == "" ) throw new Exception("Missing required input company_id");
        $file = "database/sql/reporttransamericaeligibility/ReportTransamericaEligibilityDetailsUPDATE_IgnoreTierChange.sql";
        $vars = array(
            GetIntValue($company_id),
            GetStringValue($import_date),
        );
        ExecuteSQL( $this->db, $file, $vars );
    }

}


/* End of file ReportTransamericaEligibility_model.php */
/* Location: ./system/application/models/ReportTransamericaEligibility_model.php */
