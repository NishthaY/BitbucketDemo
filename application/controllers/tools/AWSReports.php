<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );

class AWSReports extends Tool
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Search the company buckets on S3 looking for reports that have
     * zero length in file size.
     *
     *
     * @param array|false|string $bucket
     * @throws Exception
     */
    function search($bucket = S3_BUCKET)
    {
        $count = 0;
        print "Bucket: " . $bucket . "\n";

        // Get all of directories in the companies folder in the specified bucket.
        $stuff = S3ListDirectories($bucket, 'companies', false);
        foreach($stuff as $item)
        {
            // Search for directories that contain our reports.
            $key = getArrayStringValue('Key', $item);
            if ( strpos($key, 'reports') !== false )
            {
                $files = S3ListFiles($bucket, $key);
                if ( ! empty($files) )
                {
                    foreach($files as $file)
                    {
                        // Examine each file in the reports folder.  Look to see what the file size
                        // is.
                        $file_key = GetArrayStringValue('Key', $file);
                        $file_size = getIntValue(GetArrayStringValue('Size', $file));

                        print ".";

                        if ( $file_size == 0 && $file_key !== '' )
                        {
                            // Oh no!  We have a report with a zero length file.
                            // note that and echo it out to the screen for review.
                            print "\n";
                            print "$file_key ($file_size)\n";
                            print_r($files);
                            print "\n\n";
                            $count++;
                        }
                    }
                }
            }
        }

        print "\n";
        if ( $count === 0 ) print "No zero length reports found.\n";
        print "done.\n";
    }
    function resend()
    {
        // We will need to know all the various report types.
        $supported = [REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE, REPORT_TYPE_TRANSAMERICA_ACTUARIAL_CODE, REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE ];
        $report_type = $this->getReportType($supported);
        $report_type_code = GetArrayStringValue('Name', $report_type);
        $report_type_id = GetArrayStringValue('Id', $report_type);
        $report_display = GetArrayStringValue("Display", $report_type);

        // What is the company we are working with?
        $company = $this->getCompany();
        $company_id = GetArrayStringValue("company_id", $company);
        $company_name = GetArrayStringValue("company_name", $company);

        // What is the import date we are working with?
        $report_year = $this->GetReportYear();
        $report_month = $this->GetReportMonth();
        $import_date = "{$report_month}/01/{$report_year}";

        // Choose which existing report you will resend.
        $existing_report = $this->getExistingReport($company_id, $import_date, $report_type_id );
        $carrier_name = GetArrayStringValue('CarrierName', $existing_report);

        // Who is doing this?
        $user_id = GetArrayStringValue("user_id", $this->authenticated_user);

        $msg = "\n";
        $msg .= "Company: $company_name ( $company_id )\n";
        $msg .= "Import Date: $import_date\n";
        $msg .= "Report: $report_display\n";
        $msg .= "CarrierName: $carrier_name\n";
        $msg .= "\n";
        $msg .= "You are about to resend the specified report to AWS.";

        $this->confirm($msg);



        if ( $report_type_code === REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE )
        {
            $obj = new GenerateReportTransamericaEligibility();
            $obj->resend($company_id, $import_date, $user_id);
        }
        else if ( $report_type_code === REPORT_TYPE_TRANSAMERICA_ACTUARIAL_CODE )
        {
            $obj = new GenerateReportTransamericaActuarial();
            $obj->resend($company_id, $import_date, $user_id);
        }
        else if ( $report_type_code === REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE )
        {
            $obj = new GenerateReportTransamericaCommissions();
            $obj->resend($company_id, $import_date, $user_id);
        }
        else
        {
            print "ERROR: Sorry, but the report type of [$report_type_code] is not yet supported.\n";
            print "  Only the following reports can be resent.\n";
            print "  " . REPORT_TYPE_TRANSAMERICA_ACTUARIAL_CODE . "\n";
            print "  " . REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE . "\n";
            print "  " . REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE . "\n";
        }
        print "done.\n";
    }

    protected function getExistingReport( $company_id, $import_date, $report_type_id )
    {
        system('clear');

        $reports = $this->Reporting_model->select_existing_carrier_reports($company_id, $import_date, $report_type_id);
        if ( count($reports) === 0 )
        {
            print "No reports for the specified company, import and report type exist.\n";
            print "Nothing to resend.\n";
            exit;
        }
        else if ( count($reports) === 1 )
        {
            // Found exactly one carrier.  Auto-select that for the user.
            return $reports[0];
        }


        print "\n";
        print "Review the list of reports below for the selected company, import date \n";
        print "and report type.  Select the carrier for the report you which to resend.\n";
        print "\n";
        uasort($reports, 'AssociativeArraySortFunction_carrier_code');
        foreach($reports as $report)
        {
            print "  " . GetArrayStringValue('CarrierCode', $report) . "\n";
        }
        $selected = readline("Carrier: ");

        foreach($reports as $report)
        {
            if ( GetArrayStringValue('CarrierCode', $report) === $selected )
            {
                return $report;
            }
        }
        exit;
    }
    protected function getReportType( $supported=array() )
    {
        system('clear');
        print "\n";
        print "Review the list of report types below and then at the command\n";
        print "prompt type in the report of your choice.\n";
        print "\n";
        $reports = $this->Reporting_model->select_report_types();
        uasort($reports, 'AssociativeArraySortFunction_Name');
        foreach($reports as $report)
        {
            $code = GetArrayStringValue('Name', $report);
            if ( empty($supported) || in_array($code, $supported) )
            {
                print "  {$code}\n";
            }
        }
        $selected_report_type = readline("Report Type: ");

        $report_type = $this->Reporting_model->select_report_type($selected_report_type);
        if ( count($report_type) == 0 ) return array();
        return $report_type;
    }

}

/* End of file ReportGenerator.php */
/* Location: ./application/controllers/cli/ReportGenerator.php */
