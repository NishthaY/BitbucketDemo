<?php defined('BASEPATH') OR exit('No direct script access allowed');

use \phpseclib\Net\SFTP;
use \phpseclib\Crypt\RSA;

class FileTransfer extends A2PWorker
{
    private $report_date;                   // Deliver all reports for this import date.
    private $report_id;                     // Filter transfers to this report_id only.
    private $company_id_filter;             // Filter transfers to reports being delivered to these company ids.
    private $companyparent_id_filter;       // Filter transfers to reports being delivered to these parent ids.

    public function __construct()
    {
        parent::__construct();
        $this->load->library('FileTransferReports');
        $this->send_notifications = false;                  // Parent Class

        $this->report_id                = '';
        $this->company_id_filter        = array();
        $this->companyparent_id_filter  = array();
    }


    /**
     * resend
     *
     * Kick off a file transfer request for the specified company and import
     * month/year.
     *
     * @param $user_id
     * @param $company_id
     * @param string $year
     * @param string $month
     * @throws Exception
     */
    public function resend( $user_id, $company_id, $year="", $month="")
    {
        $this->debug = true;

        $month = str_pad($month, '2', '0', STR_PAD_LEFT);
        $month = substr($month, -2);
        $this->report_date = "{$month}/01/{$year}";

        $this->debug("Report Date: {$this->report_date}");
        $this->index( $user_id, $company_id);
    }

    /**
     * report
     *
     * This function will send a single report, buy report_id.  By default, the report will
     * be delivered to both the company and company parent if so configured.  You can change
     * that behavior by "filtering" the transfers by either company_id or companyparent_id.
     * In the case where both the company and the parent are enrolled for delivery, you
     * can deliver to only one or the other by filtering out the interested party you do not
     * want to notify.
     *
     * @param $user_id
     * @param $company_id
     * @param $report_id
     * @param string $company_id_filter
     * @param string $companyparent_id_filter
     * @param string $job_id
     */
    public function report( $user_id, $company_id, $report_id, $company_id_filter="", $companyparent_id_filter="", $job_id="" )
    {
        // If we are running via the queue processor, we will have a job_id.  Set our
        // debug level accordingly.
        $this->debug = false;
        if ( GetStringValue($job_id) === '') $this->debug = true;

        // Remove any "NULL" values that might have been passed in on the command line.
        if ( strtoupper($company_id_filter) === 'NULL' ) $company_id_filter = '';
        if ( strtoupper($companyparent_id_filter) === 'NULL' ) $companyparent_id_filter = '';

        $details = $this->Reporting_model->select_company_report( $company_id, $report_id);
        if ( ! empty($details) )
        {
            if ( GetArrayStringValue('CompanyId', $details) === GetStringValue($company_id) )
            {
                $this->report_id = $report_id;
                $this->report_date = GetArrayStringValue('ImportDate', $details);

                if ( GetStringValue($company_id_filter) !== '' ) $this->company_id_filter[] = GetStringValue($company_id_filter);
                if ( GetStringValue($companyparent_id_filter) !== '' ) $this->companyparent_id_filter[] = GetStringValue($companyparent_id_filter);

                $this->debug("Report Id: {$this->report_id}");
                $this->debug("Report Date: {$this->report_date}");
                $this->index( $user_id, $company_id, GetCompanyParentId($company_id) );
            }
        }
    }


    /**
     * index
     *
     * Default functionality.  Deliver all reports for a given import to interested
     * entities based on enrollment of the FILE_TRANSFER feature.
     *
     * This function will restrict the transfers based on the value of properties
     * found on the class as well as the status the FILE_TRANSFER feature.  See also
     * the resend and report function for more clarification.
     *
     * @param $user_id
     * @param string $company_id
     * @param string $companyparent_id
     * @param string $job_id
     * @throws Exception
     */
    public function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
        parent::index($user_id, $company_id, $companyparent_id, $job_id);

        $fp = null;
        try
        {
            // Get our import date and start our support timer.
            SupportTimerStart($company_id, $this->report_date, __CLASS__, null);

            // Filter this request by a single report, if we have a report_id on this class.
            if ( GetStringValue($this->report_id) !== '' ) $this->filetransferreports->addReportIdFilter($this->report_id);

            // Filter the transfers to only companies in the company list.  If none provided, all will transfer.
            foreach($this->company_id_filter as $company_id_filter)
            {
                $this->filetransferreports->addCompanyIdFilter($company_id_filter);
            }

            // Filter the transfers to only companyparents in the companyparent list.  If none provided, all will transfer.
            foreach($this->companyparent_id_filter as $companyparent_id_filter)
            {
                $this->filetransferreports->addCompanyParentIdFilter($companyparent_id_filter);
            }

            // Execute the transfers.
            $this->filetransferreports->execute($company_id, $user_id);

            // Notify this background step is complete.
            if ( $this->send_notifications) NotifyStepComplete($company_id);

        }
        catch(Exception $e)
        {
            $do_not_report_list = array( 'a2p-prodcopy');
            if ( ! in_array(APP_NAME, $do_not_report_list ) )
            {
                // We need to see this error in the process queue.  Write to STDOUT.
                $message = $e->getMessage();
                print "Exception! {$message}\n";
                if ( $this->send_notifications) NotifyStepComplete($company_id);
            }
        }

        SupportTimerEnd($company_id, $this->report_date, __CLASS__, null);
    }

}

/* End of file FileTransfer.php */
/* Location: ./application/controllers/cli/FileTransfer.php */
