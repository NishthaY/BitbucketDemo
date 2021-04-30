<?php
    if ( ! isset($display_date) ) $display_date = "";
    if ( ! isset($total) ) $total = "";
    if ( ! isset($data) ) $data = array();
    $first_row = array();
    if ( ! empty($data) ) $first_row = $data[0];
?>
<div class="card-box table-responsive">
    <div class="pull-right commission-widget-details">
        <div>New: <?=GetReportMoneyValue(GetArrayStringValue("New", $first_row))?></div>
        <div>Renewal: <?=GetReportMoneyValue(GetArrayStringValue("Renewal", $first_row))?></div>
    </div>
    <h4 class="m-t-0 header-title"><b><?=$firstname?> <?=$lastname?></b></h4>
    <p class="text-muted font-13 m-b-30">
        Total Monthly Premium: <?=GetReportMoneyValue(GetArrayStringValue("Total", $first_row))?><BR>
    </p>
    <div>

        <table id="draft_table" class="table table-hover commission-card m-0">
            <thead>
            <tr class="">
                <th>&nbsp;</th>
                <th>Commissionable Amount</th>
                <th>Commission Effective Date</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($data as $row) {
                $premium = GetArrayStringValue("CommissionablePremium", $row);
                $date = GetArrayStringValue("DisplayCommissionEffectiveDate", $row);
                $reset_record = '';
                if ( GetArrayStringValue('ResetRecord', $row) === 't' ) $reset_record = '<i class="fa fa-star"></i>';
             ?>

                <tr class="">
                    <td width="25px;"><?=$reset_record?></td>
                    <td><?=GetReportMoneyValue($premium)?></td>
                    <td><?=$date?></td>
                    <td>&nbsp;</td>
                </tr>

                <?php
            } ?>
            </tbody>
        </table>
        <div class='pull-right'>
            <a id="show_history_btn" class="btn btn-white btn-xs waves-light waves-effect" href="">History <i class="ion-arrow-down-c"></i></a>
            <a id="hide_history_btn" class="hidden btn btn-white btn-xs waves-light waves-effect" href="">Hide <i class="ion-arrow-up-c"></i></a>
        </div>
    </div>
</div>