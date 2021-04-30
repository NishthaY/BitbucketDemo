<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Downloads extends SecureController {

	function __construct(){
		parent::__construct();

    }
    public function transfer_report($entity, $report_code, $company_id, $report_id )
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_read,company_read", "company", $company_id) ) throw new SecurityException("Missing required permission.");

            // Required check.
            if ( getStringValue($entity) == "" ) throw new Exception("Missing required input entity.");
            if ( getStringValue($report_code) == "" ) throw new Exception("Missing required input report_code.");
            if ( getStringValue($company_id) == "" ) throw new Exception("Missing required input company_id.");
            if ( getStringValue($report_id) == "" ) throw new Exception("Missing required input report_id.");

            // Collect the company parent id, if possible.
            $companyparent_id = GetCompanyParentId($company_id);

            // Make sure the the report exists and belongs to them!
            $results = $this->Reporting_model->select_company_report( $company_id, $report_id );
            if ( empty($results) ) throw new SecurityException("You are not authorized to view the requested report.");

            // ENTITY PERMISSIONS
            // Manual file transfer requests have specific security rules around who can request them
            // based on what entity the user is associated with.  Users associated with a company can only
            // request file transfer requests to the company locations.  Users associated with a companyparent
            // can only request file transfers to the companyparent locations.  A2P users can request file transfers
            // against any parent and/or company.
            $user_id = GetSessionValue('user_id');
            $user = $this->User_model->get_user_by_id($user_id);
            if ( GetSessionValue('_company_id') === GetStringValue(A2P_COMPANY_ID) )
            {
                // A2P users may request SFTP delivery to any entity.
            }
            else if ( GetArrayStringValue('company_id', $user) !== '' )
            {
                // The user belongs to a company, they can only request file transfers against the company entity.
                if ( $entity !== 'company' ) throw new SecurityException("User has insufficient privileges for SFTP delivery to $entity.");

                // The user must belong to the company they are requesting the the report for.  I'm pretty sure this is
                // covered already in a security check above, but let's just do this again to be sure.
                if ( GetArrayStringValue('company_id', $user) !== GetStringValue($company_id) ) throw new SecurityException("User has insufficient privileges for SFTP delivery to specified company.");
            }
            else if ( GetArrayStringValue('company_parent_id', $user) !== '' )
            {
                // The user belongs to a companyparent, they can only request file transfers against the parent entity.
                if ( $entity !== 'parent' ) throw new SecurityException("User has insufficient privileges for SFTP delivery to $entity.");

                // The user must belong to the companyparent they are requesting the the report for.  I'm pretty sure this is
                // covered already in a security check above, but let's just do this again to be sure.
                if ( GetArrayStringValue('company_parent_id', $user) !== GetStringValue($companyparent_id) ) throw new SecurityException("User has insufficient privileges for SFTP delivery to specified parent.");
            }


            // FEATURE ENABLED
            // Now that we know the user is requesting a report the the correct entity type, we need to make sure
            // the feature is enabled.  If it's not, we must deny them.
            $sftp_company_enabled = $this->Feature_model->is_feature_enabled($company_id, 'FILE_TRANSFER');
            $sftp_companyparent_enabled = $this->Feature_model->is_feature_enabled_for_companyparent('FILE_TRANSFER', $companyparent_id );
            if ( GetSessionValue('_company_id') === GetStringValue(A2P_COMPANY_ID) )
            {
                if ( $entity === 'company' && ! $sftp_company_enabled ) throw new UIException("File transfer feature is not enabled on the specified company.");
                if ( $entity === 'parent' && ! $sftp_companyparent_enabled ) throw new UIException("File transfer feature is not enabled on the specified company.");
            }
            else if ( GetArrayStringValue('company_id', $user) !== '' )
            {
                if ( $entity === 'company' && ! $sftp_company_enabled ) throw new UIException("File transfer feature is not enabled on the specified company.");
            }
            else if ( GetArrayStringValue('company_parent_id', $user) !== '' )
            {
                if ( $entity === 'parent' && ! $sftp_companyparent_enabled ) throw new UIException("File transfer feature is not enabled on the specified company.");
            }

            // ENTITY
            // Now that we have validated the inputs are okay, we can use the entity indicator
            // to decide where we will deliver the report.
            $company_filter = "NULL";
            $companyparent_filter = "NULL";
            if ( $entity === 'company' )
            {
                $company_filter = $company_id;
            }
            else if ( $entity === 'parent' )
            {
                $companyparent_filter = $companyparent_id;
            }

            $async = true;
            if ( $async )
            {
                $params = [ GetSessionValue('user_id'), $company_id, $report_id, $company_filter, $companyparent_filter ];
                $this->Queue_model->add_job('FileTransfer', 'report', $params);
            }
            else
            {
                // I just don't feel this is secure enough.  A background task only exists for
                // the life cycle of the task.  The web server, lives until the next deployment.
                // This directory could be watched, while a worker job does not exist to be hacked
                // until it is needed.  While this UI experience is better, I'm going to keep this
                // an async task for now.
                $this->load->library('FileTransferReports');
                $this->filetransferreports->addReportIdFilter($report_id);
                $this->filetransferreports->addCompanyIdFilter($company_filter);
                $this->filetransferreports->addCompanyParentIdFilter($companyparent_filter);
                $this->filetransferreports->execute($company_id);
            }

            AJAXSuccess("Transfer request accepted.");
        }
        catch(Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    public function download_support_timers($company_id, $date_tag)
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");
            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");

            // Required check.
            if ( getStringValue($company_id) == "" ) throw new Exception("Missing required input company_id.");
            if ( getStringValue($date_tag) == "" ) throw new Exception("Missing required input date_tag.");

            // Initialize the controller for this company.
            $this->init($company_id);

            $company = $this->Company_model->get_company($company_id);
            $company_name = GetArrayStringValue('company_name', $company);

            $import_date = substr($date_tag, 4, 2) . "/01/" . substr($date_tag, 0,4);
            $results = $this->Support_model->select_support_timer_download_report($company_id, $import_date);
            if ( count($results) === 0 ) throw new Exception("Nothing to download.");

            // Create the output filename.
            $filename = "{$date_tag}_{$company_name}_SupportTimers.csv";
            $filename = ReplaceFor($filename, " ", "");
            $filename = GetFilenameFromString($filename);

            header("Content-type: text/csv");
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: IE is too broken to support Content-Disposition properly');

            // Write the CSV file into memory, this is a small file, and then print it.
            $fh = fopen('php://temp', 'rw');
            fputcsv($fh, array_keys(current($results)));

            foreach ( $results as $row ) {
                fputcsv($fh, $row);
            }
            rewind($fh);
            $csv = stream_get_contents($fh);
            fclose($fh);

            print $csv;
            exit;

        }
        catch ( UIException $e ) { print $e->getMessage(); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }
    }
    public function download_object_mappings( $company_id, $object_mapping_id )
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_read") ) throw new SecurityException("Missing required permission.");

            // Required check.
            if ( getStringValue($company_id) == "" ) throw new Exception("Missing required input company_id.");
            if ( getStringValue($object_mapping_id) == "" ) throw new Exception("Missing required input object_mapping_id.");

            // Initialize the controller for this company.
            $this->init($company_id);

            $details = $this->ObjectMapping_model->get_mapping_properties($object_mapping_id);
            if ( GetArrayStringValue("Downloadable", $details) !== 't' ) throw new Exception("That object is not available for download.");

            $results = $this->ObjectMapping_model->select_allowed_values($object_mapping_id);
            if ( count($results) === 0 ) throw new Exception("Nothing to download.");

            // Create the output filename.
            $code = GetArrayStringValue("Code", $details);
            $filename = "{$code}_AllowedValues.txt";
            $filename = ReplaceFor($filename, " ", "");
            $filename = GetFilenameFromString($filename);


            header("Content-type: text/plain");
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: IE is too broken to support Content-Disposition properly');

            print GetArrayStringValue("Display", $details) . PHP_EOL;
            print "---" . PHP_EOL;
            foreach($results as $row)
            {
                $value = GetArrayStringValue("AllowedValues", $row);
                if ( $value !== '' ) print $value . WINDOWS_NEWLINE;
            }
            exit;
        }
        catch ( UIException $e ) { print $e->getMessage(); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }

    }
    public function download_fixed_width_report( $company_id, $report_id ) {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_read,company_read", "company", $company_id) ) throw new SecurityException("Missing required permission.");

            // Required check.
            if ( getStringValue($company_id) == "" ) throw new Exception("Missing required input company_id.");
            if ( getStringValue($report_id) == "" ) throw new Exception("Missing required input report_id.");

            // Initialize the controller for this company.
            $this->init($company_id);

            // Collect all the information about this report.
            $report = $this->Reporting_model->select_company_report($company_id, $report_id);
            if ( empty($report) ) throw new Exception("Unable to locate the requested report.");
            $report_id = GetArrayStringValue("Id", $report);
            $carrier_id = getArrayStringValue("CarrierId", $report);
            $date = replaceFor(fLeftBack(getArrayStringValue("ImportDate", $report), "-"), "-", "");
            $report_type_id = getArrayStringValue("ReportTypeId", $report);
            $report_type_code = getArrayStringValue("ReportTypeCode", $report);

            // PII_DOWNLOAD
            // Does this report contain PII data?
            // Does this user have permission to download reports that contain PII data?
            if ( ReportContainsPII($report_type_code) )
            {
                if ( ! IsAuthenticated('pii_download', 'company', $company_id) )
                {
                    throw new SecurityException("Insufficient security rights to download this content.");
                }
            }

            // Figure out what we are going to call the dowload file.
            $carrier_info = $this->Reporting_model->select_carrier_by_id($company_id, $carrier_id);
            $carrier_description = getArrayStringValue("UserDescription", $carrier_info);
            $output_filename = GetReportFilename($report_id);

            // Grab the file we are going to parse.
            S3GetClient();
            $prefix = GetConfigValue("reporting_prefix");
            $prefix = replaceFor($prefix, "COMPANYID", getStringValue($company_id));
            $prefix = replaceFor($prefix, "TYPE", $report_type_code);
            $prefix = replaceFor($prefix, "DATE", getStringValue($date));
            $filename = "s3://" . S3_BUCKET . "/{$prefix}/{$carrier_id}.txt";

            // If the file does not exist, we will need to do something.
            if ( ! file_exists($filename) ) throw new Exception("Unable to locate the requested report.");

            // process the error file so we can construct an error file.
            $data 	= file_get_contents($filename);
            $fh 	= null;

            header("Content-type: text/plain");
            header('Content-Disposition: attachment; filename="'.$output_filename.'"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: IE is too broken to support Content-Disposition properly');
            try {
                $fh = fopen($filename, "r");
                if ($fh) {
                    while (($line = fgets($fh)) !== false)
                    {
                        if ( IsEncryptedString($line) )
                        {
                            print A2PDecryptString(trim($line), $this->encryption_key) . WINDOWS_NEWLINE;
                        }
                        else if ( IsEncryptedStringComment($line) )
                        {
                            // Do nothing here.  Don't write our internal comments out in the final file.
                        }
                        else
                        {
                            print trim($line) . WINDOWS_NEWLINE;
                        }
                    }
                    fclose($fh);
                }
            }catch(Exception $e) {
                if ( $fh ) fclose($fh);
            }

            // Audit this transaction
            $this->_auditDownload($company_id, $report);

            exit;
        }
        catch ( UIException $e ) { print $e->getMessage(); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e ) { Error404( $e ); }

    }
    public function download_export( $export_id ) {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");

            // Required check.
            if ( getStringValue($export_id) == "" ) throw new Exception("Missing required input export_id.");

            // EXPORT
            // Find the export we are talking about.
            $export = $this->Export_model->select_export($export_id);
            if ( empty($export) ) throw new Exception("Unable to find the export record.");

            $identifier = GetArrayStringValue('Identifier', $export);
            $identifier_type = GetArrayStringValue('IdentifierType', $export);

            // EXPORT STATUS
            // Check and make sure the export is ready for download.
            $status = GetArrayStringValue('Status', $export);
            if ( $status !== 'COMPLETE' ) throw new Exception("Export not in COMPLETE state!");

            // Get the output filename.
            $this->load->library('A2PExport');
            $obj = new A2PExport();
            $output_filename = $obj->GetDownloadFilename($export_id);

            // Get the S3 export filename.
            $export_filename = $obj->GetS3Filename($export_id);

            // Grab the file we are going to parse.
            S3GetClient();
            $prefix = GetS3Prefix('export', $identifier, $identifier_type);
            $filename = "s3://" . S3_BUCKET . "/{$prefix}/{$export_filename}";

            // If the file does not exist, we will need to do something.
            if ( ! file_exists($filename) ) throw new Exception("Unable to locate the requested export.");

            // This report must be loaded into memory to be decrypted.  Increase our
            // memory footprint if needed or fail with an Error404.
            $this->_increase_memory_for_download_if_needed($prefix, $export_filename);


            // Decrypt the zip file using the A2P Encryption Key
            $this->encryption_key = A2PGetEncryptionKey();
            $big_data = file_get_contents($filename);
            $unsecure = A2PDecryptString($big_data, $this->encryption_key);
            $data = base64_decode($unsecure);


            // Collect info for audit.
            $identifier = GetArrayStringValue('Identifier', $export);
            $identifier_type = GetArrayStringValue('IdentifierType', $export);
            $identifier_name = GetIdentifierName($identifier, $identifier_type);
            $included = $this->Export_model->select_export_property_by_key($export_id, 'include');

            // Audit the download.
            $payload = array();
            $payload['Identifier'] = $identifier;
            $payload['IdentifierType'] = $identifier_type;
            $payload['IdentifierName'] = $identifier_name;
            $payload['included']    = $included;
            $payload['export_id']   = $export_id;
            $payload['filename']    = $export_filename;
            AuditIt('Downloaded an export.', $payload, GetSessionValue('user_id'), A2P_COMPANY_ID);

            header("Content-type: application/zip");
            header('Content-Disposition: attachment; filename="'.$output_filename.'"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: IE is too broken to support Content-Disposition properly');
            print $data;
            exit;

            exit;
        }
        catch ( UIException $e ) { print $e->getMessage(); }
        catch( SecurityException $e ) { AccessDenied(); }
        catch( Exception $e )
        {
            LogIt("Download Error", $e->getMessage());
            Error404( $e );
        }

    }

    public function download_summary_report( $company_id, $report_id ) {
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_read,company_read", "company", $company_id) ) throw new SecurityException("Missing required permission.");

			// Required check.
			if ( getStringValue($company_id) == "" ) throw new Exception("Missing required input company_id.");
			if ( getStringValue($report_id) == "" ) throw new Exception("Missing required input report_id.");

			// Collect all the information about this report.
			$report = $this->Reporting_model->select_company_report($company_id, $report_id);
            $report_id = GetArrayStringValue("Id", $report);
			if ( empty($report) ) throw new Exception("Unable to locate the requested report.");
			$carrier_id = getArrayStringValue("CarrierId", $report);
			$date = replaceFor(fLeftBack(getArrayStringValue("ImportDate", $report), "-"), "-", "");
			$report_type_id = getArrayStringValue("ReportTypeId", $report);
			$report_type_code = getArrayStringValue("ReportTypeCode", $report);

            // PII_DOWNLOAD
            // Does this report contain PII data?
            // Does this user have permission to download reports that contain PII data?
            if ( ReportContainsPII($report_type_code) )
            {
                if ( ! IsAuthenticated('pii_download', 'company', $company_id) )
                {
                    throw new SecurityException("Insufficient security rights to download this content.");
                }
            }

			// Figure out what we are going to call the dowload file.
			$carrier_info = $this->Reporting_model->select_carrier_by_id($company_id, $carrier_id);
			$carrier_description = getArrayStringValue("UserDescription", $carrier_info);
            $output_filename = GetReportFilename($report_id);

			// Grab the file we are going to parse.
			S3GetClient();
			$prefix = GetConfigValue("reporting_prefix");
			$prefix = replaceFor($prefix, "COMPANYID", getStringValue($company_id));
			$prefix = replaceFor($prefix, "TYPE", $report_type_code);
			$prefix = replaceFor($prefix, "DATE", getStringValue($date));
			$filename = "s3://" . S3_BUCKET . "/{$prefix}/{$carrier_id}.pdf";


			// If the file does not exist, we will need to do something.
			if ( ! file_exists($filename) ) throw new Exception("Unable to locate the requested report.");

			// process the error file so we can construct an error file.
			$data 	= file_get_contents($filename);
			$fh 	= null;

			header("Content-type: application/pdf");
            header('Content-Disposition: attachment; filename="'.$output_filename.'"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: IE is too broken to support Content-Disposition properly');
			try {
				$fh = fopen($filename, "r");
	            if ($fh) {
	                while (($line = fgets($fh)) !== false)
					{
						print $line;
					}
					fclose($fh);
				}
			}catch(Exception $e) {
				if ( $fh ) fclose($fh);
			}

			// Audit this transaction
            $this->_auditDownload($company_id, $report);

			exit;
		}
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404( $e ); }

	}
	public function download_detail_report( $company_id, $report_id ) {
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_read,company_read", "company", $company_id) ) throw new SecurityException("Missing required permission.");

			// Required check.
			if ( getStringValue($company_id) == "" ) throw new Exception("Missing required input company_id.");
			if ( getStringValue($report_id) == "" ) throw new Exception("Missing required input report_id.");

			// Initialize the controller for this company.
			$this->init($company_id);

			// Collect all the information about this report.
			$report = $this->Reporting_model->select_company_report($company_id, $report_id);
			if ( empty($report) ) throw new Exception("Unable to locate the requested report.");
			$report_id = GetArrayStringValue("Id", $report);
			$carrier_id = getArrayStringValue("CarrierId", $report);
			$date = replaceFor(fLeftBack(getArrayStringValue("ImportDate", $report), "-"), "-", "");
			$report_type_id = getArrayStringValue("ReportTypeId", $report);
			$report_type_code = getArrayStringValue("ReportTypeCode", $report);

            // PII_DOWNLOAD
            // Does this report contain PII data?
            // Does this user have permission to download reports that contain PII data?
            if ( ReportContainsPII($report_type_code) )
            {
                if ( ! IsAuthenticated('pii_download', 'company', $company_id) )
                {
                    throw new SecurityException("Insufficient security rights to download this content.");
                }
            }

			// Figure out what we are going to call the dowload file.
			$carrier_info = $this->Reporting_model->select_carrier_by_id($company_id, $carrier_id);
			$carrier_description = getArrayStringValue("UserDescription", $carrier_info);
			$output_filename = GetReportFilename($report_id);

			// Grab the file we are going to parse.
			S3GetClient();
			$prefix = GetConfigValue("reporting_prefix");
			$prefix = replaceFor($prefix, "COMPANYID", getStringValue($company_id));
			$prefix = replaceFor($prefix, "TYPE", $report_type_code);
			$prefix = replaceFor($prefix, "DATE", getStringValue($date));
			$filename = "s3://" . S3_BUCKET . "/{$prefix}/{$carrier_id}.csv";

			// If the file does not exist, we will need to do something.
			if ( ! file_exists($filename) ) throw new Exception("Unable to locate the requested report.");

			// process the error file so we can construct an error file.
			$data 	= file_get_contents($filename);
			$fh 	= null;

			header("Content-type: text/csv");
            header('Content-Disposition: attachment; filename="'.$output_filename.'"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: IE is too broken to support Content-Disposition properly');
			try {
				$fh = fopen($filename, "r");
	            if ($fh) {
	                while (($line = fgets($fh)) !== false)
					{
                        if ( IsEncryptedString($line) )
                        {
                            print A2PDecryptString(trim($line), $this->encryption_key) . WINDOWS_NEWLINE;
                        }
                        else if ( IsEncryptedStringComment($line) )
                        {
                            // Do nothing here.  Don't write our internal comments out in the final file.
                        }
                        else
                        {
                            print trim($line) . WINDOWS_NEWLINE;
                        }
					}
					fclose($fh);
				}
			}catch(Exception $e) {
				if ( $fh ) fclose($fh);
			}

            // Audit this transaction
            $this->_auditDownload($company_id, $report);

			exit;
		}
		catch ( UIException $e ) { print $e->getMessage(); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404( $e ); }

	}
    public function validation_errors( $identifier_type, $identifier ) {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            if ( $identifier_type === 'company' )
            {
                $company_id = $identifier;
                $companyparent_id = GetCompanyParentId($company_id);
            }
            elseif($identifier_type === 'companyparent' )
            {
                $company_id = null;
                $companyparent_id = $identifier;
            }
            else throw new SecurityException("Insufficient security rights to access this content.");


            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( $identifier_type === 'company' && ! IsAuthenticated("company_write") ) throw new SecurityException("Missing required permission.");
            if ( $identifier_type === 'companyparent' && ! IsAuthenticated("parent_company_write") ) throw new SecurityException("Missing required permission.");


            // Grab the file we are going to parse.
            S3GetClient();
            $prefix = GetS3Prefix("errors", $identifier, $identifier_type);
            $filename = "s3://" . S3_BUCKET . "/{$prefix}/errors.json";

            // If the file does not exist, we will need to do something.
            if ( ! file_exists($filename) )
            {
                header("Content-type: plain/text");
                header('Content-Disposition: attachment; filename="errors.txt"');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: IE is too broken to support Content-Disposition properly');

                print "No errors are available for download at this time.";
                exit;
            }

            // process the error file so we can construct an error file.
            $errors = file_get_contents($filename);
            $obj = json_decode($errors, true);

            header("Content-type: plain/text");
            header('Content-Disposition: attachment; filename="errors.txt"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: IE is too broken to support Content-Disposition properly');
            foreach($obj as $line_no=>$data)
            {
                if ( ! empty($data['messages']) ) {
                    $messages = $data["messages"];
                    if ( ! empty($messages) )
                    {
                        foreach($messages as $message)
                        {
                            $message = getStringValue($message);
                            print "line {$line_no}: {$message}\n";
                        }
                    }
                }
            }
            exit;


        }
        catch ( UIException $e ) { print $e->getMessage(); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404( $e ); }

    }

    public function download_duplicate_data_report( $company_id ) {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_read,parent_company_read", 'company', $company_id) ) throw new SecurityException("Missing required permission.");



            // Required check.
            if ( getStringValue($company_id) == "" ) throw new Exception("Missing required input company_id.");


            // PII_DOWNLOAD
            // Does this user have permission to download reports that contain PII data?
            if ( ! IsAuthenticated('pii_download', 'company', $company_id) )
            {
                throw new SecurityException("Insufficient security rights to download this content.");
            }


            // Initialize the controller for this company.
            $this->init($company_id);




            // Figure out what we are going to call the download file.
            $obj = new GenerateDuplicateLivesReport();
            $output_filename = $obj->getFilename();

            // Grab the file we are going to parse.
            S3GetClient();
            $prefix = GetConfigValue("errors_prefix");
            $prefix = replaceFor($prefix, "COMPANYID", getStringValue($company_id));
            $filename = "s3://" . S3_BUCKET . "/{$prefix}/{$output_filename}";
            

            // If the file does not exist, we will need to do something.
            if ( ! file_exists($filename) ) throw new Exception("Unable to locate the requested report.");

            // process the error file so we can construct an error file.
            $data 	= file_get_contents($filename);
            $fh 	= null;

            header("Content-type: text/csv");
            header('Content-Disposition: attachment; filename="'.$output_filename.'"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: IE is too broken to support Content-Disposition properly');
            try {
                $fh = fopen($filename, "r");
                if ($fh) {
                    while (($line = fgets($fh)) !== false)
                    {
                        if ( IsEncryptedString($line) )
                        {
                            print A2PDecryptString(trim($line), $this->encryption_key) . PHP_EOL;
                        }
                        else if ( IsEncryptedStringComment($line) )
                        {
                            // Do nothing here.  Don't write our internal comments out in the final file.
                        }
                        else
                        {
                            print $line;
                        }
                    }
                    fclose($fh);
                }
            }catch(Exception $e) {
                if ( $fh ) fclose($fh);
            }
            exit;
        }
        catch ( UIException $e ) { print $e->getMessage(); }
        catch( SecurityException $e ) { AccessDenied($e->getMessage()); }
        catch( Exception $e ) { Error404( $e ); }

    }
    public function download_issues_report( $company_id, $import_date=null )
    {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("parent_company_read,company_read", 'company', $company_id) ) throw new SecurityException("Missing required permission.");



            // Required check.
            if ( getStringValue($company_id) == "" ) throw new Exception("Missing required input company_id.");


            // Initialize the controller for this company.
            $this->init($company_id);

            // PII_DOWNLOAD
            // Does this report contain PII data?
            // Does this user have permission to download reports that contain PII data?
            if ( ReportContainsPII(REPORT_TYPE_ISSUES_CODE) )
            {
                if ( ! IsAuthenticated('pii_download', 'company', $company_id) )
                {
                    throw new SecurityException("Insufficient security rights to download this content.");
                }
            }


            // If the date passed in looks like it's in the format of YYYY-MM-DD, convert it to MM/DD/YYYY.
            if ( $import_date !== "" && strpos($import_date, "-") !== FALSE )
            {
                $parts = explode("-", $import_date);
                $year = getArrayStringValue("0", $parts);
                $month = getArrayStringValue("1", $parts);
                $day = getArrayStringValue("2", $parts);
                $import_date = "{$month}/{$day}/{$year}";
            }

            // Figure out what we are going to call the download file.
            $obj = new GenerateWarningReport();
            $aws_filename = $obj->getFilename();
            $output_filename = GetProcessReportFilename($company_id);

            // Grab the file we are going to parse.
            S3GetClient();
            $prefix = GetConfigValue("reporting_prefix");
            $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
            $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id, $import_date));
            $prefix  = replaceFor($prefix, "TYPE", REPORT_TYPE_ISSUES_CODE);
            S3MakeBucketPrefix(S3_BUCKET, $prefix);
            $filename = "s3://" . S3_BUCKET . "/{$prefix}/{$aws_filename}";


            // If the file does not exist, we will need to do something.
            if ( ! file_exists($filename) ) throw new Exception("Unable to locate the requested report.");

            // process the error file so we can construct an error file.
            $data 	= file_get_contents($filename);
            $fh 	= null;

            header("Content-type: text/csv");
            header('Content-Disposition: attachment; filename="'.$output_filename.'"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: IE is too broken to support Content-Disposition properly');
            try {
                $fh = fopen($filename, "r");
                if ($fh) {
                    while (($line = fgets($fh)) !== false)
                    {
                        if ( IsEncryptedString($line) )
                        {
                            print A2PDecryptString(trim($line), $this->encryption_key) . PHP_EOL;
                        }
                        else if ( IsEncryptedStringComment($line) )
                        {
                            // Do nothing here.  Don't write our internal comments out in the final file.
                        }
                        else
                        {
                            print $line;
                        }
                    }
                    fclose($fh);
                }
            }catch(Exception $e) {
                if ( $fh ) fclose($fh);
            }
            exit;
        }
        catch ( UIException $e ) { print $e->getMessage(); }
        catch( SecurityException $e ) { AccessDenied($e->getMessage()); }
        catch( Exception $e ) { Error404( $e ); }

    }


    private function _auditDownload($company_id, $report)
    {
        // Is this a draft report?
        $draft_flg = false;
        if ( new DateTime(GetUploadDate()) == new DateTime(getArrayStringValue("ImportDate", $report)) ) $draft_flg = true;

        // Get some better information about the carrier associated with the report..
        $carrier_id = GetArrayStringValue("CarrierId", $report);
        $carrier = $this->Company_model->get_company_carrier($company_id, $carrier_id);
        $carrier_code = GetArrayStringValue('CarrierCode', $carrier);

        // Audit this transaction.
        $payload = array();
        $payload["ReportId"] = GetArrayStringValue("Id", $report);
        $payload["ReportType"] = GetArrayStringValue("ReportTypeCode", $report);
        $payload["CarrierId"] = $carrier_id;
        $payload["CarrierCode"] = $carrier_code;
        $payload["ReportDate"] = GetArrayStringValue("ImportDate", $report);
        if ( $draft_flg ) AuditIt("Downloaded draft report.", $payload);
        if ( ! $draft_flg ) AuditIt("Downloaded finalized report.", $payload);
    }

    /**
     * _increase_memory_for_download_if_needed
     *
     * NOTE: This function is only needed to be executed on a download that must
     * be pulled into memory in order to be decrypted.  If the file is being
     * decrypted line by line, there is no need to adjust the memory footprint.
     *
     * This function will examine the file on AWS and decide if we need to bump up
     * our running memory footprint in order to load it into memory.  However, we
     * will not let the memory footprint grow beyond the total number of megs defined
     * in the DOWNLOAD_FILESIZE_MAX application variable.  If that value is not set
     * the download will be aborted.
     *
     * @param $prefix
     * @param $filename
     */
    private function _increase_memory_for_download_if_needed( $prefix, $filename )
    {
        // Collect details about the file and collect it's size and units.
        $details = S3ListFile( S3_BUCKET, $prefix, $filename );
        $bytes = GetArrayStringValue('Size', $details);
        $size   = ceil(fLeft(FormatBytes($bytes), ' '));
        $units  = fRight(FormatBytes($bytes), ' ');

        // We are going to allow the memory to grow if needed to download the file, but
        // we will not let it grow beyond 3/4 of the total memory on the server.
        $max_threshold = GetIntValue(StripNonNumeric(GetAppOption('DOWNLOAD_FILESIZE_MAX')));  // in megs
        if ( $max_threshold === 0 ) Error404("Unable to determine the max amount of memory we can use to download this file.");

        $memory_info = [];
        $memory_info['prefix'] = $prefix;
        $memory_info['filename'] = $filename;
        $memory_info['max_threshold'] = $max_threshold . " mb";
        $memory_info['file size'] = "{$bytes} {$units}";
        LogIt("Export download request memory information", $memory_info);

        // Start failing if the file is too big!  Since the file must be placed into memory to
        // decrypt, we have to have enough running memory to do it.  However, we don't want to
        // consume all the ram as we are on the web server, not a one-off dynos.
        if ( strtolower($units) == 'tb' )
        {
            // File is too big to download.
            Error404('The file being downloaded is larger than a terabyte.');
        }
        if ( strtolower($units) == 'gb' )
        {
            // Convert GB into MB.
            $units = 'mb';
            $units = GetIntValue($units) * 1024;
        }
        if ( strtolower($units) == 'mb' && $size > $max_threshold )
        {
            Error404("The file being downloaded is larger ( {$size} mb ) than our calculated max threshold. ( {$max_threshold} mb");
        }
        if ( strtolower($units) == 'mb')
        {
            // Calculate our desired memory limit which is 20 megs larger than the file size.
            // If our desired memory limit is larger than the current memory limit, then go
            // ahead and bump it up for the download.  The memory growth will exists for only
            // this download.  However, if the desired memory limit is over our max threshold
            // then we have to stop.

            // We are going to need a little more room than just the size of the
            // file.  Grab that value from app options.  ( defaults to zero if missing )
            $padding = GetIntValue(StripNonNumeric(GetAppOption('DOWNLOAD_FILESIZE_PADDING')));  // in megs

            // Double the file size and add a little room.  If we are still under our threshold
            // then allow the download.
            $desired_memory_limit = ( $size * 2 + $padding );
            if ( $desired_memory_limit > $max_threshold )
            {
                Error404("The desired memory allocation [{$desired_memory_limit}] is larger than our max threshold [{$max_threshold}].");
            }

            $current_memory_limit = getStringValue(ini_get('memory_limit'));
            $current_memory_limit = GetIntValue(StripNonNumeric($current_memory_limit));
            if ( $desired_memory_limit > $current_memory_limit )
            {
                ini_set('memory_limit', "{$desired_memory_limit}M");
                LogIt("Adjusted the running memory footprint to [{$desired_memory_limit}M] to accommodate the file download.");
            }
        }
    }
}
