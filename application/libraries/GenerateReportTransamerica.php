<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateReportTransamerica extends A2PLibrary {

    protected $encryption_key;
    protected $import_date;
    protected $report_code;

    function __construct( $debug=false )
    {
        parent::__construct($debug);
    }



    /**
     * retry
     *
     * This function should be called if the report save resulted in a zero length
     * file on S3.  One more attempt will be made.  In either case, an email will
     * be senT to support outlining what happened and if there are next steps.
     *
     * @param $company_id
     * @param $import_date
     * @param $carrier_id
     * @param $save_results
     * @throws Exception
     */
    public function retry( $company_id, $import_date, $carrier_id, $save_results)
    {

        // Get the saved details.
        $file_details_saved = array();
        if ( isset($save_results['file_details_saved'] ) ) $file_details_saved = $save_results['file_details_saved'];

        // Get the encrypted details.
        $file_details_encrypted = array();
        if ( isset($save_results['file_details_encrypted']) ) $file_details_encrypted = $save_results['file_details_encrypted'];

        // Log the data.
        LogIt(__CLASS__, 'Saving '.$this->report_code.' report resulted in a zero length file.');
        LogIt(__CLASS__, 'File info after save.', $file_details_saved);
        LogIt(__CLASS__, 'File info after encrypt.', $file_details_encrypted);

        // What the hell just happened.  Take a breath.
        sleep(3);

        // Try to save the report again.
        $save_results2 = $this->save_report($company_id, $import_date, $carrier_id);

        // Decide if this issue is critical or not.  We will change what we say
        // in our email based on this decision.
        $critical = false;
        if ( GetArrayIntValue('file_length', $save_results2) <= 0 ) $critical = true;

        if ( $critical )
        {
            if ( isset($save_results2['file_details_saved'] ) ) $file_details_saved2 = $save_results2['file_details_saved'];
            if ( isset($save_results2['file_details_encrypted'] ) ) $file_details_encrypted2 = $save_results2['file_details_encrypted'];

            // Log the data for retry if it failed.
            LogIt(__CLASS__, 'RETRY: Saving '.$this->report_code.' report resulted in a zero length file.');
            LogIt(__CLASS__, 'RETRY: File info after save.', $file_details_saved2);
            LogIt(__CLASS__, 'RETRY: File info after encrypt.', $file_details_encrypted2);
        }

        // Send an email to support.  Either critical or warning if the retry triggered.
        $view_array = array();
        $view_array['company_id'] = $company_id;
        $view_array['import_date'] = $import_date;
        $view_array['carrier_id'] = $carrier_id;
        $view_array['critical'] = $critical;
        $view_array['report_code'] = $this->report_code;
        isset($save_results2['file_details_saved'] ) ? $view_array['file_details_saved'] = $save_results2['file_details_saved'] :  $view_array['file_details_saved'] = array();
        isset($save_results2['file_details_encrypted'] ) ? $view_array['file_details_encrypted'] = $save_results2['file_details_encrypted'] :  $view_array['file_details_encrypted'] = array();
        if ( $critical ) SendFYISupportEmail( "ALERT: Zero File Length", RenderViewAsString("emails/fyi_zero_length_file", $view_array) );
        if ( ! $critical ) SendFYISupportEmail( "WARNING: Zero File Length", RenderViewAsString("emails/fyi_zero_length_file", $view_array) );

        // If this was a CRITICAL report, go ahead and throw a report exception.
        if ( $critical )
        {
            $report = $this->ci->Reporting_model->select_report_type($this->report_code);
            $report_name = GetArrayStringValue("Display", $report);
            throw new ReportException("Zero file length detected.");
        }
    }

    /**
     * resend
     *
     * This function will "re-transmit" the report data to it's location on S3.  Data will
     * not be regenerated and a audit record will be written.
     *
     * @param $company_id
     * @param $import_date
     * @param null $user_id
     */
    public function resend( $company_id, $import_date, $user_id=null)
    {
        // You can only do this from the command line.
        if ( ! $this->ci->input->is_cli_request() ) {
            Error404();
            return;
        }

        try
        {
            $CI = $this->ci;
            $this->debug = true;
            if ( GetStringValue($company_id) === '' ) throw new Exception("Missing required input: company_id");
            if ( GetStringValue($import_date) === '' ) throw new Exception("Missing required input: import_date");

            // Does this company have this report enabled in the feature list?  No, do not generate this report.
            $enabled = $this->isEnabled($company_id);
            if ( ! $enabled ) throw new Exception("Report type not enabled for specified company.");

            // Does this company have Transamerica as a carrier?  No, do not generate this report.
            $carrier_details = $CI->Company_model->get_company_carrier_by_name($company_id, "TRANSAMERICA");
            $carrier_id = GetArrayStringValue("Id", $carrier_details);
            if ( $carrier_id === "" ) throw new Exception("Company does not belong to TRANSAMERICA.");

            // Has this company already generated the report before?
            $exists = $CI->Reporting_model->company_report_exists( $company_id, $carrier_id, $this->report_code, $import_date );
            if ( ! $exists ) throw new Exception("Company has not already generated this report.  Unable to resend.");

            // Ensure we have the encryption key in the cache
            $CI->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
            $this->encryption_key = $CI->cache->get("crypto_{$company_id}");
            if ( GetStringValue($this->encryption_key) === 'FALSE' )
            {
                $this->encryption_key = GetCompanyEncryptionKey($company_id);
                $CI->cache->save("crypto_{$company_id}", $this->encryption_key, 300);
            }

            // Resend the report to AWS.
            $save_results = $this->save_report($company_id, $import_date, $carrier_id);

            if (GetArrayIntValue('file_length', $save_results) === 0) {
                print_r($save_results);
                throw new Exception("Saved file had zero file length.");
            }

            // Audit it!
            $audit = array();
            $audit['ReportCode'] = $this->report_code;
            $audit['CompanyId'] = $company_id;
            $audit['CompanyName'] = GetCompanyName($company_id);
            $audit['ImportDate'] = $import_date;
            AuditIt("Report resent to AWS.", $audit, $user_id, $company_id);

            // Remove the critical error that indicated this report was not generated.
            $report = $this->ci->Reporting_model->select_report_type($this->report_code);
            $report_name = GetArrayStringValue("Display", $report);
            $warnings = $this->ci->Reporting_model->select_critical_report_warnings_zero_file_length($company_id, $import_date, $report_name);
            if ( count($warnings) <= 1 )
            {
                print "Cleaning up warnings report.\n";
                $this->ci->Reporting_model->delete_critical_report_warnings_zero_file_length($company_id, $import_date, $report_name);

                // Generate Warning Report.
                $obj = new GenerateWarningReport(true);
                $obj->execute($company_id, $user_id);
                $obj = null;
            }


        }
        catch(Exception $e)
        {
            print "There was an error while processing the resend request\n";
            print $e->getMessage();
        }

    }


}
