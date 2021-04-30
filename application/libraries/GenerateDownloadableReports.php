<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateDownloadableReports extends A2PLibrary {

    protected $encryption_key;

    function __construct( $debug=false )
    {
        parent::__construct($debug);
    }

    public function execute( $company_id, $user_id=null )
    {
        try {
            parent::execute($company_id);

            $CI = $this->ci;

            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");


            $this->debug(" Removing downloadable reports from past attempts.");
            $CI->Reporting_model->delete_downloadable_reports($company_id);
            $this->timer(" Removing downloadable reports from past attempts.");

            $this->debug(" Removing company reports from past attempts.");
            $CI->Reporting_model->delete_company_report($company_id);
            $this->timer(" Removing company reports from past attempts.");

            $this->debug(" Generating detail reports");
            $this->_generate_detail_reports($company_id);
            $this->timer(" Generating detail reports");

            $this->debug(" Generating summary reports");
            $this->_generate_summary_reports($company_id);
            $this->timer(" Generating summary reports");

            $this->debug(" Generating detail reports for premium equivalent");
            $this->_generate_detail_reports($company_id, true);     // Premium Equivalent
            $this->timer(" Generating detail reports for premium equivalent");

            $this->debug(" Generating summary reports for premium equivalent");
            $this->_generate_summary_reports($company_id, true);    // Premium Equivalent
            $this->timer(" Generating summary reports for premium equivalent");

            $this->debug(" Generating zero dollar reports");
            $this->_remove_zero_dollar_reports($company_id);
            $this->timer(" Generating zero dollar reports");


        } catch(Exception $e) {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }

    // PRIVATE +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

    private function _remove_zero_dollar_reports($company_id) {

        // _remove_zero_dollar_reports
        //
        // This function will look for reports that have been generated that
        // have no numerical value. Zero lives, Zero Premium, Zero Volume.
        // Those reports are then removed from the "CompanyReport" table
        // as well as S3.
        // ------------------------------------------------------------------

        $CI = $this->ci;

        // Select all reports that have zero dollars.
        $this->debug(" select_summary_data_report_review_zero_dollar");
        $reports = $CI->Reporting_model->select_summary_data_report_review_zero_dollar($company_id);

        // For each report you find, delete them of S3 and the CompayReport table.
        foreach($reports as $report)
        {
            $carrier_id = getArrayStringValue("CarrierId", $report);
            $premium_equivalent = getArrayStringValue("PremiumEquivalent", $report);
            if ($premium_equivalent == "t")
            {
                // Remove the summary report
                $this->debug(" select_draft_company_report_id ( REPORT_TYPE_PE_SUMMARY )");
                $report_id = $CI->Reporting_model->select_draft_company_report_id($company_id, $carrier_id, REPORT_TYPE_PE_SUMMARY);
                if ( getStringValue($report_id) != "" )
                {
                    $prefix = GetConfigValue("reporting_prefix");
                    $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
                    $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id));
                    $prefix  = replaceFor($prefix, "TYPE", REPORT_TYPE_PE_SUMMARY_CODE);
                    S3DeleteFile( S3_BUCKET, $prefix, "{$carrier_id}.pdf" );
                    $this->debug(" delete_company_report_by_id [{$report_id}]");
                    $CI->Reporting_model->delete_company_report_by_id($report_id);
                }

                // Remove the detail report
                $this->debug(" select_draft_company_report_id ( REPORT_TYPE_PE_DETAIL )");
                $report_id = $CI->Reporting_model->select_draft_company_report_id($company_id, $carrier_id, REPORT_TYPE_PE_DETAIL);
                if ( getStringValue($report_id) != "" )
                {
                    $prefix = GetConfigValue("reporting_prefix");
                    $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
                    $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id));
                    $prefix  = replaceFor($prefix, "TYPE", REPORT_TYPE_PE_DETAIL_CODE);
                    S3DeleteFile( S3_BUCKET, $prefix, "{$carrier_id}.csv" );
                    $this->debug(" delete_company_report_by_id [{$report_id}]");
                    $CI->Reporting_model->delete_company_report_by_id($report_id);
                }
            }
            else
            {
                // Remove the summary report
                $report_id = $CI->Reporting_model->select_draft_company_report_id($company_id, $carrier_id, REPORT_TYPE_SUMMARY);
                $this->debug(" select_draft_company_report_id ( REPORT_TYPE_SUMMARY )");
                if ( getStringValue($report_id) != "" )
                {
                    $prefix = GetConfigValue("reporting_prefix");
                    $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
                    $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id));
                    $prefix  = replaceFor($prefix, "TYPE", REPORT_TYPE_SUMMARY_CODE);
                    S3DeleteFile( S3_BUCKET, $prefix, "{$carrier_id}.pdf" );
                    $this->debug(" delete_company_report_by_id [{$report_id}]");
                    $CI->Reporting_model->delete_company_report_by_id($report_id);
                }

                // Remove the detail report
                $report_id = $CI->Reporting_model->select_draft_company_report_id($company_id, $carrier_id, REPORT_TYPE_DETAIL);
                $this->debug(" select_draft_company_report_id ( REPORT_TYPE_DETAIL )");
                if ( getStringValue($report_id) != "" )
                {
                    $prefix = GetConfigValue("reporting_prefix");
                    $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
                    $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id));
                    $prefix  = replaceFor($prefix, "TYPE", REPORT_TYPE_DETAIL_CODE);
                    S3DeleteFile( S3_BUCKET, $prefix, "{$carrier_id}.csv" );
                    $this->debug(" delete_company_report_by_id [{$report_id}]");
                    $CI->Reporting_model->delete_company_report_by_id($report_id);
                }

            }

        }
    }
    private function _generate_detail_reports($company_id, $premium_equivalent=false) {

        $CI = $this->ci;

        // Collect our list of carriers from the summary report.
        $this->debug(" select_summary_report_carriers");
        $carriers = $CI->Reporting_model->select_summary_report_carriers($company_id);

        $report_type = "detail";
        if ( $premium_equivalent ) $report_type = "pe_detail";

        // Create the detail report folder
        $prefix = GetConfigValue("reporting_prefix");
        $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
        $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id));
        $prefix  = replaceFor($prefix, "TYPE", $report_type);
        S3MakeBucketPrefix(S3_BUCKET, $prefix);

        // We only want to write headers once.
        $write_headers = true;

        // Generate the Detail Report.
        foreach($carriers as $item)
        {

            // The carrier list contains both Standard and Premium Equivalent reports.
            // Based on the input $premium_equivalent, skip the carriers that don't match our type.
            $carrier_pe_flg = getArrayStringValue("PremiumEquivalentFlg", $item);
            if ( $premium_equivalent   && $carrier_pe_flg == 'f' ) continue;
            if ( ! $premium_equivalent && $carrier_pe_flg == 't' ) continue;

            // For each new report, we want to activate the headers.
            $write_headers = true;

            $fh = null;
            try
            {
                // create the CSV file for download, make sure it's encrypted.
                $carrier_id = getArrayIntValue("CarrierId", $item);
                $filename = "{$carrier_id}.csv";
                S3SaveEncryptedFile(S3_BUCKET, $prefix, $filename, "");
                $fh = S3OpenFile( S3_BUCKET, $prefix, $filename, $options='w' );

                // Pull the ImportData we will add to the detail report.
                if ( $premium_equivalent )
                {
                    // If we are generating a premium equivalent report, we might have in
                    // hand a child carrier_id that maps back to 1 to many parent carrier ids.
                    // We really want the parent PE data in the report, so pull each of the
                    // parent carrier_ids and merge them all together.
                    $data = array();
                    $this->debug(" select_pe_parent_child_relationships [{$company_id}] [{$carrier_id}]");
                    $relationships = $CI->PlanFees_model->select_pe_parent_child_relationships($company_id, $carrier_id);
                    foreach($relationships as $relationship)
                    {
                        $parent_carrier_id = getArrayStringValue("ParentCarrierId", $relationship);
                        $this->debug(" select_detail_report_import_data [{$company_id}] [{$parent_carrier_id}] [{$premium_equivalent}]");
                        $result = $CI->Reporting_model->write_detail_report_import_data($fh, $company_id, $parent_carrier_id, $this->encryption_key, $premium_equivalent, $write_headers);
                        if ( $result > 0 ) $write_headers = false;
                    }
                }
                else
                {
                    // This is not a PE pull, this will just generate the detail report
                    // the original way.  Just by carrier_id and no premium equivalent details.
                    $this->debug(" write_detail_report_import_data [{$company_id}] [{$carrier_id}] [{$premium_equivalent}]");
                    $result = $CI->Reporting_model->write_detail_report_import_data($fh, $company_id, $carrier_id, $this->encryption_key, $premium_equivalent, $write_headers);
                    if ( $result > 0 ) $write_headers = false;
                }

                // Pull the Automatic Adjustments we will add to the detail report.
                $this->debug(" write_detail_report_automatic_adjustments [{$company_id}] [{$carrier_id}] [{$premium_equivalent}]");
                $result = $CI->Reporting_model->write_detail_report_automatic_adjustments($fh, $company_id, $carrier_id, $this->encryption_key, $premium_equivalent, $write_headers);
                if ( $result > 0 ) $write_headers = false;

                // Pull the Manual Adjustments we will add to the detail report.
                $this->debug(" write_detail_report_manual_adjustments [{$company_id}] [{$carrier_id}]");
                $result = $CI->Reporting_model->write_detail_report_manual_adjustments($fh, $company_id, $carrier_id, $this->encryption_key, $write_headers);
                if ( $result > 0 ) $write_headers = false;

                // Record that this report exists in the DB.
                $this->debug(" insert_company_report [{$company_id}] [{$carrier_id}] [{$report_type}]");
                $CI->Reporting_model->insert_company_report( $company_id, $carrier_id, $report_type );
            }
            catch(Exception $e)
            {
                if ( $fh != null ) fclose($fh);
                throw $e;
            }

        }

        // Secure the reports on S3
        S3EncryptAllFiles(S3_BUCKET, $prefix);

    }
    private function _generate_summary_reports($company_id, $premium_equivalent=false) {

        $CI = $this->ci;

        // Collect our list of carriers from the summary report.
        $this->debug(" select_summary_report_carriers");
        $carriers = $CI->Reporting_model->select_summary_report_carriers($company_id);
        $report_type = "summary";
        if ( $premium_equivalent ) $report_type = "pe_summary";

        // Create the summary report folder
        $prefix = GetConfigValue("reporting_prefix");
        $prefix  = replaceFor($prefix, "COMPANYID", $company_id);
        $prefix  = replaceFor($prefix, "DATE", GetUploadDateFolderName($company_id));
        $prefix  = replaceFor($prefix, "TYPE", $report_type);
        S3MakeBucketPrefix(S3_BUCKET, $prefix);

        foreach($carriers as $carrier)
        {
            // The carrier list contains both Standard and Premium Equivalent reports.
            // Based on the input $premium_equivalent, skip the carriers that don't match our type.
            $carrier_pe_flg = getArrayStringValue("PremiumEquivalentFlg", $carrier);
            if ( $premium_equivalent   && $carrier_pe_flg == 'f' ) continue;
            if ( ! $premium_equivalent && $carrier_pe_flg == 't' ) continue;

            $carrier_id = getArrayStringValue("CarrierId", $carrier);
            $this->_generate_summary_report($company_id, $carrier_id, $prefix, $report_type, $premium_equivalent);
        }

        // Secure the reports on S3
        S3EncryptAllFiles(S3_BUCKET, $prefix);

    }
    private function _generate_summary_report($company_id, $carrier_id, $prefix, $report_type, $premium_equivalent) {

        $CI = $this->ci;

        // Limit our dataset to just the requested carrier carrier.
        $data = array();
        $this->debug(" select_summary_report_carriers");
        $carriers = $CI->Reporting_model->select_summary_report_carriers( $company_id );
        foreach($carriers as $carrier)
        {
            if ( getArrayStringValue("CarrierId", $carrier) != $carrier_id) continue;
            $carrier_pe_flg = getArrayStringValue("PremiumEquivalentFlg", $carrier);
            if ( $premium_equivalent   && $carrier_pe_flg == 'f' ) continue;
            if ( ! $premium_equivalent && $carrier_pe_flg == 't' ) continue;

            // Collect the carrier label and the data for the dataset we
            // are working with.
            $carrier_display = getArrayStringValue("CarrierDescription", $carrier);
            $this->debug(" select_summary_data_by_carrier/select_summary_data_premium_equivalent_by_carrier [{$company_id}] [{$carrier_id}]");
            if ( ! $premium_equivalent ) 	$data[] = $CI->Reporting_model->select_summary_data_by_carrier($company_id, $carrier_id);
            if ( $premium_equivalent ) $data[] = $CI->Reporting_model->select_summary_data_premium_equivalent_by_carrier($company_id, $carrier_id);

        }

        // Caclulate the amount due from out data set.
        $total_amount_due = 0.00;
        foreach($data as &$section)
        {
            foreach($section as &$item)
            {
                // If we have no Lives, Volume or Premium then the row is of no
                // real value on the summary report.  Hide it.
                $item["HideRow"] = 'f';
                $lives = getArrayFloatValue("Lives", $item);
                $volume = getArrayFloatValue("Volume", $item);
                $premium = getArrayFloatValue("Premium", $item);
                $adjustedlives = getArrayFloatValue("AdjustedLives", $item);
                $adjustedvolume = getArrayFloatValue("AdjustedVolume", $item);
                $adjustedpremium = getArrayFloatValue("AdjustedPremium", $item);
                if ( $lives == 0 && $volume == 0 && $premium == 0 && $adjustedlives == 0 && $adjustedvolume == 0 && $adjustedpremium == 0 )
                {
                    $item["HideRow"] = 't';
                }

                // keep a running total of the premium for our final amount due.
                $amount_due = getArrayFloatValue("TotalPremium", $item);
                $total_amount_due = $total_amount_due + getFloatValue($amount_due);
            }
        }

        $page_template = array();
        $page_template = array_merge($page_template, array("company_id" => $company_id));
        $page_template = array_merge($page_template, array("carrier_id" => $carrier_id));
        $page_template = array_merge($page_template, array("carrier_display" => $carrier_display));
        $page_template = array_merge($page_template, array("data" => $data));
        $page_template = array_merge($page_template, array("amount_due" => $total_amount_due));
        $page_template = array_merge($page_template, array("pdf" => true));

        try
        {
            // create our Summary Report PDF.
            $pdf = new A2P_PDF("L", "pt", "A4", true, 'UTF-8', false);

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Advice2Pay');
            $pdf->SetTitle(GetUploadDateFolderName($company_id) . " A2P Summary");
            $pdf->SetSubject('Advice2Pay Summary Report');

            $keywords = array();
            $keywords[] = GetUploadDateFolderName($company_id);
            $keywords[] = GetUploadDate($company_id);
            $keywords[] = "a2p";
            $keywords[] = "A2P";
            $keywords[] = "Summary Report";
            $pdf->SetKeywords(implode(", ", $keywords));

            $pdf->SetPrintHeader(false);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER + 5);
            $pdf->footer_html_view = "reports/summary_report_footer";
            $pdf->footer_html_view_array = array();



            $pdf->AddPage();

            $html = RenderViewAsString("templates/template_summary_report", $page_template);
            $pdf->writeHTML($html, true, false, true, false, '');

            // Create an encrypted file on S3.
            $filename = "{$carrier_id}.pdf";
            S3SaveEncryptedFile(S3_BUCKET, $prefix, $filename, "");

            // Start capturing our STDOUT buffer.
            while ( ob_get_length() ) { ob_end_clean(); }
            ob_start();

            // Generate the PDF and stream it to STDOUT.
            $pdf->Output("a2p.pdf", 'I');

            // capture the PDF Buffer into a variable.
            $pdf_data = ob_get_clean();

            // Write the PDF to S3.
            S3SaveEncryptedFile(S3_BUCKET, $prefix, $filename, $pdf_data);

            // Record that this report exists in the DB.
            $CI->Reporting_model->insert_company_report( $company_id, $carrier_id, $report_type );
        }
        catch(Exception $e)
        {
            while ( ob_get_length() )
            {
                ob_end_clean();
            }
            throw $e;
        }
    }
}
