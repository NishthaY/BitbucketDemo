<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MigrateCommissions extends A2PWorker
{

    function __construct()
    {
        // Construct our parent class
        parent::__construct();

        //CLI ONLY! GO AWAY!
        if ( ! $this->input->is_cli_request() ) {
            Error404();
            return;
        }

    }
    function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
        parent::index($user_id, $company_id, $companyparent_id, $job_id);

        $this->timers = true;
        $this->_migrate($user_id, $company_id);

    }

    private function _rollback( $user_id, $company_id )
    {
        $history = array();

        // Get a list of import dates for this company over all of their data.
        $import_dates = $this->Reporting_model->select_import_dates($company_id);

        // Sort the dates so we can roll them back in order.
        $rollback_dates = array_reverse($import_dates);
        foreach ($rollback_dates as $row) {
            $date = GetArrayStringValue("ImportDate", $row);
            $this->debug("Rollback {$date}.");


            // Capture Original Effective Date
            $obj = new GenerateOriginalEffectiveDateData();
            $obj->rollback($company_id, $date);
            $obj = null;

            // Generate Commissions
            $obj = new GenerateCommissions();
            $obj->rollback($company_id, $date);
            $obj = null;

            $history[$date] = "Commission data removed for {$date}.";

        }

        AuditIt("Data Migration: Commission data removed.", $history, $user_id, $company_id);

    }

    /**
     * migrate
     *
     * This function will collect the import dates for the given company and
     * roll them back from newest to oldest.  Once complete, the system will
     * then re-calcuate the OED table and commissions for the company from the
     * oldest import date to the latest.
     *
     * If an import date is provided, the migration will start at that date
     * forward.
     *
     * @param null $company_id
     * @param bool $verbose
     */
    private function _migrate( $user_id, $company_id )
    {
        $date = "";
        try
        {
            // STOP!
            // Do not migrate if the company has a data file up for review and not yet finalized.
            if ( ! $this->_allDataFinalized($company_id) )
            {
                if ( ! IsReportGenerationStepComplete($company_id) )
                {
                    throw new Exception("Customer data either not finalized or not ready to be finalized.");
                }
            }

            // Rollback all of the Commission related data in the database for this customer.
            $this->_rollback($user_id, $company_id);

            // Get a list of import dates for this company over all of their data.
            $import_dates = $this->Reporting_model->select_import_dates($company_id);

            // Reprocess OED and Commissions.
            $history = array();
            foreach ($import_dates as $row)
            {
                $date = GetArrayStringValue("ImportDate", $row);
                $date = FormatDateMMDDYYYY($date);  // Convert the date to MM/DD/YYYY format.

                $finalized = GetArrayStringValue("Finalized", $row);
                $this->debug("Migrating commissions for {$date}.");
                $this->timer("Migrating: [{$date}]");

                // Capture Original Effective Date
                $this->debug("  GenerateOriginalEffectiveDateData");
                $obj = new GenerateOriginalEffectiveDateData();
                $obj->execute($company_id, $user_id, $date);
                $obj = null;

                // Generate Commissions
                $this->debug("  GenerateCommissions");
                $obj = new GenerateCommissions();
                $obj->execute($company_id, $user_id, $date);
                $obj = null;

                $time = $this->timer("end");
                $output = "Migrated [{$date}] in {$time}\n";
                $this->audit[] = $output;

                $history[$date] = "Commission data generated for {$date} in {$time}.";

            }
            AuditIt("Data Migration: Generated commission data.", $history, $user_id, $company_id);

        }
        catch(Exception $e)
        {
            print "Commission migration has failed.\n";
            print "Company: [{$company_id}]\n";
            print "ImportDate: [{$date}]\n";
            print "---\n";
            print $e->getMessage();
            exit;
        }
    }

    private function _allDataFinalized($company_id)
    {
        // Get a list of import dates for this company over all of their data.
        $import_dates = $this->Reporting_model->select_import_dates($company_id);
        foreach($import_dates as $item)
        {
            $finalized = GetArrayStringValue("Finalized", $item);
            if ( $finalized === 'f' ) return false;
        }
        return true;
    }

}

/* End of file MigrateCommissions.php */
/* Location: ./application/controllers/cli/MigrateCommissions.php */
