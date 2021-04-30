<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($company_id) ) $company_id = GetSessionValue("company_id");

    $render_widget = false;
    if ( HasExistingReportData() ) $render_widget = true;

    $recent_date = GetRecentDate($company_id);
    $recent_date = ReplaceFor($recent_date, "/", "-");

?>

<?php
if ( $render_widget ) {
?>
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive "> <!-- dont forget to put back the hidden class -->
            <h4 class="m-t-0 header-title"><b><?=GetRecentDateDescription($company_id)?> Reports</b></h4>
            <table id="recent_reports_table" class="table table-striped" width="100%">
                <thead>
                    <tr>
                        <th>Carrier</th>
                        <th><?=GetRecentMon($company_id)?> Spend</th>
                        <th><?=GetRecentMon($company_id)?> Wash/Retro Catch</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if ( !empty($data) ) {
                            foreach($data as $item) {
                                $row = array();
                                $row['carrier_id'] = GetArrayStringValue("CarrierId", $item);
                                $row['carrier'] = GetArrayStringValue("Carrier", $item);
                                $row['spend'] = GetReportMoneyValue(GetArrayStringValue("Spend", $item));
                                $row['wash_retro_catch'] = GetReportMoneyValue(GetArrayStringValue("WashRetroCatch", $item));
                                $row['summary_report_id'] = GetArrayStringValue("SummaryReportId", $item);
                                $row['detail_report_id'] = GetArrayStringValue("DetailReportId", $item);
                                $row['company_id'] = $company_id;
                                $row['recent_date'] = $recent_date;
                                RenderViewSTDOUT("dashboard/recent_reports_table_row", $row);

                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
}
?>
