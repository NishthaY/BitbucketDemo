<?php
    if ( ! isset($carrier) ) $carrier = "";
    if ( ! isset($carrier_id) ) $carrier_id = "";
    if ( ! isset($spend) ) $spend = "";
    if ( ! isset($wash_retro_catch) ) $wash_retro_catch = "";
    if ( ! isset($summary_report_id) ) $summary_report_id = "";
    if ( ! isset($detail_report_id) ) $detail_report_id = "";
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($recent_date) ) $recent_date = "";
    

    $hide_row = false;
?>
<?php
if ( ! $hide_row ) {
?>
    <tr class="">
        <td><div class="text-nowrap"><?=$carrier?></div></td>
        <td><?=$spend?></td>
        <td><?=$wash_retro_catch?></td>
        <td class="">
            <div class="row">
                <div class="col-xs-6"></div>
                <a data-company-id="<?=$company_id?>" data-carrier="<?=$carrier_id;?>" data-import-date="<?=$recent_date?>" class="report-list-download-btn report-list-btn btn btn-xs btn-block btn-default waves-effect" type="button" formnovalidate>Reports</a></td>
            </div>
        </td>
    </tr>
<?php
}
?>


