<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Viewer extends SecureController {

	function __construct(){
		parent::__construct();

    }

    public function summary_report( $company_id, $carrier_id, $report_type_id ) {
        try
        {
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "GET" ) throw new SecurityException("Unexpected request method.");

            // Security Check!
            // This function requires that you be authenticated in order to use it.
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("company_read") ) throw new SecurityException("Missing required permission.");
            if ( getStringValue($company_id) != GetSessionValue("company_id") ) throw new SecurityException("Insufficient security rights to access this content.");

			// Are we trying to see the premium equivalent version of this report?
			$premium_equivalent = false;
			if ( $report_type_id == REPORT_TYPE_PE_SUMMARY ) $premium_equivalent = true;

			$data = array();
			$carriers = $this->Reporting_model->select_summary_report_carriers( $company_id );
			foreach($carriers as $carrier) {

				// Look over all of the carriers.  Find the one that matches the
				// request by CarrierId and Premium Equivalent.
				if ( getArrayStringValue("CarrierId", $carrier) != $carrier_id) continue;
				$carrier_pe_flg = getArrayStringValue("PremiumEquivalentFlg", $carrier);
	            if ( $premium_equivalent   && $carrier_pe_flg == 'f' ) continue;
	            if ( ! $premium_equivalent && $carrier_pe_flg == 't' ) continue;

				// Collect the carrier label and the data for the dataset we
				// are working with.
				$carrier_display = getArrayStringValue("CarrierDescription", $carrier);
				if ( ! $premium_equivalent ) 	$data[] = $this->Reporting_model->select_summary_data_by_carrier($company_id, $carrier_id);
				if ( $premium_equivalent ) $data[] = $this->Reporting_model->select_summary_data_premium_equivalent_by_carrier($company_id, $carrier_id);
			}
			// Caclulate the amount due from out data set.
			$total_amount_due = 0.00;
			foreach($data as &$section) {
				foreach($section as &$item) {
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

			RenderView('templates/template_summary_report', $page_template);


        }
        catch ( UIException $e ) { print $e->getMessage(); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404( $e ); }

    }

}
