<?php
    if ( ! isset($data) ) $data = array();

    $render_widget = true;
    $footer_class = "hidden";

?>

<?php
if ( $render_widget ) {
?>
<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive "> <!-- dont forget to put back the hidden class -->
            <table id="invoice_report_details_table" class="table table-striped" width="100%">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Company</th>
                        <th>Month</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if ( !empty($data) ) {
                            foreach($data as $item) {
                                $row = array();
                                $row['company_id'] = GetArrayStringValue("id", $item);
                                $row['company_name'] = GetArrayStringValue("name", $item);
                                $row['amount'] = GetReportMoneyValue(GetArrayStringValue("money", $item));
                                $row['display_date'] = GetArrayStringValue("display_date", $item);
                                $row['status'] = "none";

                                if ( GetArrayStringValue('pending', $item) === 't' )
                                {
                                    $row['status'] = "attention";
                                    $footer_class = "";
                                }

                                // No display date is the indicator that we did not source the import date to get the dollar
                                // values.  We used that above to help us decide if we should or should not show the status indicator.
                                // Now we fill it in as we don't want any blanks in the table data.
                                if ( GetArrayStringValue('display_date', $row) === '' )
                                {
                                    $row['display_date'] = date('M Y', strtotime(GetArrayStringValue('import_date', $item)));
                                }

                                RenderViewSTDOUT("archive/invoice_report_details_table_row", $row);
                            }
                        }
                    ?>
                </tbody>
                <tfoot class="<?=$footer_class?>">
                    <tr>
                        <td colspan="4"><span class="pull-right"><small>Marked records indicate that pending reports, if finalized, would change this invoice.</small></span></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php
}
?>
