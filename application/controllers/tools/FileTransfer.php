<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );

class FileTransfer extends Tool
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * resend
     *
     * This will allow you to "resend" a File Transfer request for the specified
     * company and report date.  This is an interactive function and you will
     * be asked to provide the required data.
     *
     */
    public function resend( )
    {
        try
        {
            system("clear");
            print "This tool will resend monthly reports to the SFTP location on file.\n";
            print "Press any key to continue or <ctrl-c> to quit.\n";
            readline("");

            $user_id = GetArrayStringValue("user_id", $this->authenticated_user);
            if ( $user_id === '' )
            {
                print "You must be authenticated to use this tool.\n";
                exit;
            }

            $company = $this->getCompany();
            $company_id = GetArrayStringValue("company_id", $company);

            $report_year = $this->GetReportYear();
            $report_month = $this->GetReportMonth();
            $report_id = $this->GetReport($company_id, "$report_month/01/$report_year");

            if ( GetStringValue($report_id) === '' )
            {
                $command = "php index.php cli/FileTransfer/resend {$user_id} {$company_id} {$report_year} {$report_month}";
            }
            else
            {
                $command = "php index.php cli/FileTransfer/report {$user_id} {$company_id} {$report_id}";
            }

            print $command . "\n";
            system($command);

            print "done.\n";
        }
        catch(Exception $e)
        {
            print "Exception! " . $e->getMessage() . "\n";
        }
    }

    protected function GetReport($company_id, $report_date)
    {
        $report_id = "";

        $done = false;
        while ( ! $done )
        {
            system('clear');
            print "\n";
            print "At the command prompt, enter the report id of the report you want to resend.\n";
            print "Just press enter to resend all of these reports.\n";
            print "\n";


            $reports = $this->FileTransfer_model->get_reports_for_transfer($company_id, $report_date);
            foreach($reports as $report)
            {
                $report_id = GetArrayStringValue('ReportId', $report);
                $filename = GetReportFilename($report_id);
                print "  $report_id $filename\n";
            }

            print "\n";
            $report_id = readline("ReportId (####): ");
            print "REPORTID: $report_id\n";

            // If they entered no numbers, we will assume ALL.
            if ( GetStringValue($report_id) === '' ) $done = true;

            if ( ! $done )
            {
                // Does the number entered match a report id?
                $report_id = StripNonNumeric($report_id);
                if ( ! empty($report_id) )
                {
                    foreach($reports as $report)
                    {
                        if ( $report_id == GetArrayStringValue('ReportId', $report) )
                        {
                            $done = true;
                        }
                    }
                }
            }


        }
        return $report_id;
    }

}

/* End of file FileTransfer.php */
/* Location: ./application/controllers/cli/FileTransfer.php */
