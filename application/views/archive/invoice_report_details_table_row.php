<?php
    if ( !isset($company_id) ) $company_id = "";
    if ( !isset($company_name) ) $company_name = "";
    if ( !isset($amount) ) $amount = "";
    if ( !isset($display_date) ) $display_date = "";
    if ( !isset($status) ) $status = "none";

    

    $hide_row = false;
?>
<?php
if ( ! $hide_row ) {
?>
    <tr class="">
        <td class="status-column"><i class="md md-border-circle status-indicator status-indicator-<?=$status?>"></i></td>
        <td><div class="invoice-report-cell text-nowrap"><?=$company_name?></div></td>
        <td><div class="invoice-report-cell"><?=$display_date?></div></td>
        <td><div class="invoice-report-cell"><?=$amount?></div></td>
    </tr>
<?php
}
?>
