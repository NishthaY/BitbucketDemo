<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class A2PReportInvoice {

    function __construct()
    {
        $CI =& get_instance();
        $CI->load->model('A2PReportInvoice_model');
    }

    function RenderDetailsWidget($identifier, $identifier_type, $date_tag)
    {
        $CI =& get_instance();

        if ( $identifier_type !== 'companyparent' ) return RenderViewAsString("archive/invoice_report_details_noresults", []);
        $import_date = substr($date_tag, 4, 2) . "/01/" . substr($date_tag,0,4);
        $report = $this->_invoice_report_data($identifier, $identifier_type, $date_tag);

        $total = 0;
        foreach($report as $item)
        {
            $total = $total + getArrayFloatValue('money', $item);
        }

        $view = "archive/invoice_report_details";
        if ( $total === 0 ) $view = "archive/invoice_report_details_noresults";

        $view_array = array();
        $view_array['data'] = $report;
        $view_array['total'] = $total;
        $view_array['report_date'] = date('F Y', strtotime($import_date));

        $html = RenderViewAsString($view, $view_array);

        return $html;
    }
    function RenderSummaryWidget($identifier, $identifier_type, $date_tag='')
    {
        $CI =& get_instance();

        // NO RESULTS
        // Return the no results experience right away if we get inputs that can't be processed.
        if ( $identifier_type !== 'companyparent' ) return RenderViewAsString("archive/invoice_report_summary_noresults", []);
        if ( GetStringValue($identifier) === A2P_COMPANY_ID ) return RenderViewAsString("archive/invoice_report_summary_noresults", []);

        // Show or hide the "more" button on the widget if you do or do not have
        // a date tag.  If you have a date_tag, then you are on the report page and
        // there is no "more" to see.
        $show_button = true;
        if ( GetStringValue($date_tag) !== '' ) $show_button = false;

        // Default the date tag to now.
        if ( GetStringValue($date_tag) === '' ) $date_tag = date('Ym');

        // Calculate the import date.
        $import_date = substr($date_tag, 4, 2) . "/01/" . substr($date_tag,0,4);

        // Collect our report data.
        $data = $this->_invoice_report_data($identifier, $identifier_type, $date_tag);

        // Total the dollar values for each company in the report.
        $total = 0;
        foreach($data as $item)
        {
            $total = $total + getArrayFloatValue('money', $item);
        }

        $datetag_menu = $this->_datetag_menu($data);



        $view_array = array();
        $view_array['display_date'] = date('M Y', strtotime($import_date));
        $view_array['money_value'] = GetReportMoneyValue($total);
        $view_array['show_button'] = $show_button;
        $view_array['identifier'] = $identifier;
        $view_array['identifier_type'] = $identifier_type;
        $view_array['date_tag'] = $date_tag;
        $view_array['datetag_menu'] = $datetag_menu;

        $html = RenderViewAsString("archive/invoice_report_summary_widget", $view_array);

        return $html;
    }

    /**
     * _datetag_menu
     *
     * For this report, we want the date tag menu to be the current date through the oldest
     * date in the report collection.  Construct an array that has at least date_tag and
     * description for the menu, ordered.
     *
     * @param $data
     * @return array
     */
    private function _datetag_menu($data)
    {
        $menu = array();

        // If there is no data, just return the empty array.
        if ( count($data) === 0 ) return array();

        // Calculate the MIN date found in the collection.
        $min = 999999;
        foreach($data as $item)
        {
            $import_date = getArrayStringValue('initial_import_date', $item);
            $year = substr($import_date, 6, 4);
            $month = substr($import_date, 0,2);
            $tag = $year . $month;
            $tag = GetIntValue($tag);
            if ( $tag < $min ) $min = $tag;
        }

        // Calculate the MAX date, which is the current import month.
        $max = date('Ym');


        // Turn our max and min into full date strings.
        $max_date_str = substr($max, 4, 2) . "/01/" .substr($max, 0, 4);
        $min_date_str = substr($min, 4, 2) . "/01/" .substr($min, 0, 4);

        // Turn our date strings into dates.
        $max_date = strtotime($max_date_str);
        $min_date = strtotime($min_date_str);

        // Loop from the max date to the min date, creating the menu structure as we go.
        $date = $max_date;
        while( $date >= $min_date)
        {
            $date_str = date('m/d/Y', $date);
            $date_tag = date('Ym', $date);
            $description = date('F Y', $date);
            $menu[] = [ 'date' => $date_str, 'date_tag'=>$date_tag, 'description' => $description ];
            $date = strtotime("$date_str -1 MONTH");
        }

        return $menu;

    }
    private function _invoice_report_data($identifier, $identifier_type, $date_tag)
    {
        $CI =& get_instance();

        $company_id = "";
        $companyparent_id = "";
        $companies = array();
        if ( $identifier_type === 'company' )
        {
            $company_id = $identifier;
            $companyparent_id = GetCompanyParentId($company_id);
            $company = $CI->Company_model->get_company_by_id($company_id);
            $companies[] = $company;
        }
        else if( $identifier_type === 'companyparent' )
        {
            $companyparent_id = $identifier;
            $companies = $CI->CompanyParent_model->get_companies_by_parent( $companyparent_id );
        }

        $import_date = substr($date_tag, 4, 2) . "/01/" . substr($date_tag,0,4);

        $report = array();
        foreach($companies as $company)
        {
            $row = array();
            $row['id']  = '';
            $row['name'] = '';
            $row['import_date'] = '';
            $row['display_date'] = '';
            $row['finalized'] = 'f';

            $this_company_id = GetArrayStringValue('company_id', $company);
            $this_company_name = GetArrayStringValue('company_name', $company);

            // If a company has no finalized data, skip it.
            $has_finalized_data = $CI->Reporting_model->does_company_have_finalized_data( $this_company_id );
            if ( ! $has_finalized_data ) continue;

            // Find report data.  If we have none, then they are a new company that has not made it
            // to the report review screen yet.  Skip it.
            $info = $CI->A2PReportInvoice_model->select_invoice_report_import_date($this_company_id, $date_tag);
            if ( empty($info) ) continue;

            // Collect the import date for the reports we found for this company.  It might match the date_tag
            // or it might not.
            $this_import_date = GetArrayStringValue('RecentImportDate', $info);

            // Find the "initial" import date for this company.
            $info = $CI->A2PReportInvoice_model->select_invoice_report_initial_import_date($this_company_id, $date_tag);
            $this_initial_import_date = GetArrayStringValue('InitialImportDate', $info);
            if ( $this_initial_import_date === '' ) continue;

            // Now we need to make sure the import date we just picked up is associated with finalized data.
            // If it's not finalized, then we need to move backwards.  I can't imagine a scenario that would
            // not be the previous month, but I don't want to be wrong.  Walk backwards, no more than a year,
            // and look for a finalized report set.  If we can't find one, skip this company.
            $done = false;
            $count = 12;
            while( $count > 0 && $done )
            {
                $prev_import_date = date('m/d/Y', strtotime(date($this_import_date).' -1 MONTH'));
                $finalized = $CI->Reporting_model->has_company_import_been_finalized( $this_company_id, $prev_import_date );
                $this_import_date = $prev_import_date;
                if ( $finalized ) $done = true;
                else $count--;
            }
            if ( $count === 0 ) continue;

            // if the "last finalized report" date does not match our input date, create a display date
            // indicator so we can see what month it was.  If it matches, then there will be no indicator.
            $this_display_date = "";
            if ( $this_import_date !== $import_date )
            {
                $this_display_date = date('M Y', strtotime($this_import_date));
            }

            // If the month in review has pending reports not yet finalized, note that.
            $pending = $CI->A2PReportInvoice_model->has_reports_in_review($this_company_id, $this_import_date);
            if ( $pending ) $pending = 't';
            else $pending = 'f';


            // Find the dollar value for the month associated with the last finalized
            // report set for this company.
            $money = $this->_calculate_company_total($this_company_id, $this_import_date);

            $row['id']  = $this_company_id;
            $row['name'] = $this_company_name;
            $row['import_date'] = $this_import_date;
            $row['display_date'] = $this_display_date;
            $row['money'] = $money;
            $row['pending'] = $pending;
            $row['initial_import_date'] = $this_initial_import_date;
            $report[] = $row;

        }
        return $report;

    }

    /**
     * _calculate_company_total
     *
     * Calculate the monthly premium less the adjusted amount for the TotalAdjustedPremium.
     *
     * @param $company_id
     * @param $import_date
     * @return float|int
     */
    private function _calculate_company_total( $company_id, $import_date )
    {
        $CI =& get_instance();

        $total_amount_due = 0.00;
        $carriers = $CI->A2PReportInvoice_model->select_carriers_by_company($company_id);
        foreach($carriers as $carrier)
        {
            $carrier_id = GetArrayStringValue('Id', $carrier);
            $amount_due = $CI->A2PReportInvoice_model->select_adjusted_total($company_id, $carrier_id, $import_date);
            $total_amount_due = $total_amount_due + $amount_due;
        }
        return $total_amount_due;
    }




}
