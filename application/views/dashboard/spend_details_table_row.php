<?php
    if ( ! isset($carrier) ) $carrier = "";
    if ( ! isset($benifit) ) $benifit = "";
    if ( ! isset($monthly_cost) ) $monthly_cost = "";
    if ( ! isset($monthly_cost_ytd) ) $monthly_cost_ytd = "";
    if ( ! isset($wash_retro) ) $wash_retro = "";
    if ( ! isset($wash_retro_ytd) ) $wash_retro_ytd = "";


    $hide_row = false;
    if (
        GetReportMoneyValue($monthly_cost) == "-"
        && GetReportMoneyValue($monthly_cost_ytd) == "-"
        && GetReportMoneyValue($wash_retro) == "-"
        && GetReportMoneyValue($wash_retro_ytd) == "-"
    ) $hide_row = true;


?>
<?php
if ( ! $hide_row ) {
?>
    <tr class="">
        <!--<td><?=getStringValue($carrier)?></td>-->
        <td><span class='text-nowrap'><?=getStringValue($benifit)?></span></td>
        <td><?=GetReportMoneyValue($monthly_cost)?></td>
        <td><?=GetReportMoneyValue($monthly_cost_ytd)?></td>
        <td><?=GetReportMoneyValue($wash_retro)?></td>
        <td><?=GetReportMoneyValue($wash_retro_ytd)?></td>
    </tr>
<?php
}
?>
