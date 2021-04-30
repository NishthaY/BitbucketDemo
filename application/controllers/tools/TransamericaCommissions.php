<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );


/**
 * Class TransamericaCommissions
 *
 * This tool can be used to rollback and rebuild the Transamerica Commission report
 * for one to many companies.
 *
 */
class TransamericaCommissions extends Tool
{
    protected $report_code;

    public function __construct()
    {
        parent::__construct();
        $this->report_code = REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE;
    }

    function execute()
    {
        $failed = array();
        try
        {
            // Authenticated user
            $user_id = GetArrayStringValue('user_id', $this->authenticated_user);
            if ( GetStringValue($user_id) === '' ) throw new Exception("Missing authenticated user!");

            // Find the Transamerica Parent Company
            $companyparent = $this->CompanyParent_model->get_parent_by_name('Transamerica');
            if ( count($companyparent) !== 1 ) throw new Exception("Unable to find the transamerica parent company");
            $companyparent = $companyparent[0];
            $companyparent_id = GetArrayIntValue("Id", $companyparent);

            // Find all of their companies.
            $companies = $this->getParentCompanyOrCompanies($companyparent_id);
            foreach($companies as $company)
            {
                try
                {
                    $company_id = GetArrayIntValue('company_id', $company);
                    $company_name = GetArrayStringValue('company_name', $company);
                    print "\nprocessing {$company_name}: ";

                    $imports = $this->Reporting_model->select_import_dates($company_id);
                    $imports = array_reverse($imports);
                    foreach($imports as $item)
                    {
                        $import_date = GetArrayStringValue($item, "ImportDate");
                        $import_date = FormatDateMMDDYYYY($import_date);
                        print $import_date . " ";

                        $obj = new GenerateReportTransamericaCommissions();
                        $obj->rollback($company_id, $import_date);
                        $obj = null;
                    }

                    $imports = array_reverse($imports);
                    foreach($imports as $item)
                    {
                        $import_date = GetArrayStringValue($item, "ImportDate");
                        $import_date = FormatDateMMDDYYYY($import_date);
                        print $import_date . " ";

                        $obj = new GenerateReportTransamericaCommissions();
                        $obj->regenerate($company_id, $import_date, $user_id);
                        $obj = null;
                    }

                }
                catch(Exception $e)
                {
                    print "FAILED: " . $e->getMessage();

                    $investigate = array();
                    $investigate['company_id'] = $company_id;
                    $investigate['company_name'] = $company_name;
                    $investigate['error'] = $e->getMessage();
                    $failed[] = $investigate;
                }
            }
        }
        catch(Exception $e)
        {
            print "ERROR: " . $e->getMessage();
            exit;
        }

        if ( count($failed) !== 0 )
        {
            print "There were a total of [".count($failed)."] companies that failed.\n";
            print_r($failed);
            print "failure.\n";

        }
        print "done.\n";
        return;
    }

}

/* End of file TransamericaCommissions.php */
/* Location: ./application/controllers/cli/TransamericaCommissions.php */
