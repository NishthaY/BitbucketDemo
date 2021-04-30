<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TestHarness extends A2PWorker
{

	public function __construct( )
    {
        parent::__construct();                  // Command Line Only
        //parent::__construct( false );        // Web Access
    }
	public function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
        //parent::index($user_id, $company_id, $companyparent_id, $job_id);
        try {

            $import_date = "12/01/2019";
            print_r("import_date[{$import_date}]\n");

            $ago_date = strtotime('-1 months', strtotime($import_date));
            print_r( "ago_date[{$ago_date}]\n");

            $formatted = date("Y-m-d", $ago_date);
            print_r($formatted);


            //$archive_datetag = date("Y-m-d", strtotime("-1 months", "02/01/2020"));



            //$this->notify_status_update("QUICK_SCAN", $job_id);
            //$payload = array();
            //$payload['hello'] = 'world';
            //NotifyCompanyChannelUpdate( A2P_COMPANY_ID, 'admin_dashboard_task', 'bah', array('JobId' => $job_id));
            //NotifyCompanyParentChannelUpdate( 10, '','admin_dashboard_task', 'bah', array('JobId' => $job_id));

            // Here is how you can send a single report via a background job.
            // $this->Queue_model->add_job('FileTransfer','report',[ 2, 206, 3994]);

/*
            $obj = new GenerateUniversalEmployeeId(true);
            $obj->execute($company_id);


            $obj = new GenerateLifeData(true);
            $obj->execute($company_id);

            $obj = new GeneratePlanFees(true);
            $obj->execute($company_id);

            $obj = new GenerateAgeData(true);
            $obj->execute($company_id);

            $obj = new GenerateWashedData(true);
            $obj->execute($company_id);

            $obj = new GenerateRetroData(true);
            $obj->execute($company_id);

            // Generate Relationship Data
            $obj = new GenerateRelationshipData(true);
            $obj->execute($company_id);

            $obj = new GenerateAutomaticAdjustments(true);
            $obj->execute($company_id);

            $obj = new GenerateSummaryData(true);
            $obj->execute($company_id);

            // Capture Original Effective Date
            $this->debug("GenerateOriginalEffectiveDate");
            $obj = new GenerateOriginalEffectiveDateData(true);
            $obj->execute($company_id);
            $obj = null;

            // Capture Commission Data
            $this->debug("GenerateCommissions");
            $obj = new GenerateCommissions(true);
            $obj->execute($company_id);
            $obj = null;

            $obj = new GenerateDownloadableReports(true);
            $obj->execute($company_id);

            $obj = new GenerateReportTransamericaEligibility(true);
            $obj->execute($company_id);

            $obj = new GenerateReportTransamericaCommissions(true);
            $obj->execute($company_id);

            $obj = new GenerateReportTransamericaActuarial(true);
            $obj->execute($company_id);

            $obj = new GenerateReportTransamericaActuarial(true);
            $obj->execute($company_id);

            $action = new GenerateWarningReport(true);
            $action->execute($company_id);

*/


        }catch(Exception $e) {

            // We need to see this error in the process queue.  Write to STDOUT.
            print "Exception! " . $e->getMessage() . "\n";

        }
    }



}

/* End of file TestHarness.php */
/* Location: ./application/controllers/cli/TestHarness.php */
