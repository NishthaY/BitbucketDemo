<?php
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($import_date) ) $import_date = "";
    if ( ! isset($display_date) ) $display_date = "";
    if ( ! isset($carrier) ) $carrier = "";
    if ( ! isset($carrier_id) ) $carrier_id = "";
    if ( ! isset($draft_flg) ) $draft_flg = false;
    if ( ! isset($report_name) ) $report_name = false;
    if ( ! isset($report_code) ) $report_code = "";

    $icon = "fa-lock";
    if ( $draft_flg == "t" ) $icon = "fa-unlock-alt";

    $display_name = $carrier;
    if ( $display_name === '' ) $display_name = $report_name;

?>
<tr class="">
    <td><i class="fa <?=$icon?>"></td>
    <td><?=$display_date?></td>
    <td><?=$display_name?></td>
    <td class="">
        <div class="row">
            <div class="col-xs-6"></div>
            <?php
            if ( $carrier_id != "" )
            {
                ?>
                <div class="col-xs-6"><a data-company-id='<?=$company_id?>' data-carrier='<?=$carrier_id?>' data-import-date="<?=$import_date?>"  class="report-list-download-btn report-review-btn btn btn-xs btn-block btn-default waves-effect" type="button" formnovalidate>Reports</a></div>
                <?php
            }
            else
            {
                ?>
                <div class="col-xs-6"><a data-company-id='<?=$company_id?>' data-import-date="<?=$import_date?>" data-report-code="<?=$report_code?>" class="report-list-download-btn report-review-btn btn btn-xs btn-block btn-default waves-effect" type="button" formnovalidate>Download</a></div>
                <?php
            }
            ?>

        </div>
    </td>
</tr>
