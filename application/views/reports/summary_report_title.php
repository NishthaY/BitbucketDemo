<?php
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($pagebreak) ) $pagebreak = true;

    $attr = "";
    if ( $pagebreak )
    {
        $attr = 'pagebreak="true"';
    }
?>
<tr <?=$attr?>>
    <td class="title strong-right-border strong-left-border strong-top-border" colspan="13">Carrier Summary Report</td>
</tr>
<tr class="summarydata line1">
    <td class="carriertitle">Carrier</td>
    <td class="carriername" colspan="3"><?=$carrier_display?></td>
    <td class="billingperiodtitle" colspan="2">Billing Period:</td>
    <td class="billingperiod" colspan="3"><?=GetUploadDateDescription($company_id);?></td>
    <td>&nbsp;</td>
    <td class="totalamountduetitle" colspan="3">Total Amount Due:</td>
</tr>
<tr class="summarydata line2">
    <td class="customertitle">Customer:</td>
    <td class="customername" colspan="3"><?=GetCompanyName($company_id);?></td>
    <td class="datepreparedtitle" colspan="2">Date Prepared:</td>
    <td class="dateprepared" colspan="3"><?=GetPreparedDate($company_id);?></td>
    <td>&nbsp;</td>
    <td class="totalamountdue" colspan="3"><?=GetReportMoneyValue($amount_due);?></td>
</tr>
<tr class="grouptitle">
    <td class="benefits strong-bottom-border top-border" colspan="4">Benefits</td>
    <td class="currentmonth strong-bottom-border top-border" colspan="3"><?=GetUploadDateDescription($company_id)?></td>
    <td class="adjustments strong-bottom-border top-border" colspan="3">Adjustments</td>
    <td class="adjustedtotal strong-right-border strong-bottom-border top-border" colspan="3">Adjusted Total</td>
</tr>
<tr class="columntitle">
    <td class="plantype strong-left-border">Plan Type</td>
    <td class="plan">Plan</td>
    <td class="tier">Tier</td>
    <td class="attributes">Attributes</td>
    <td class="lives">Count</td>
    <td class="volume">Volume</td>
    <td class="premium">Premium</td>
    <td class="lives">Count</td>
    <td class="volume">Volume</td>
    <td class="premium">Premium</td>
    <td class="lives">Count</td>
    <td class="volume">Volume</td>
    <td class="premium strong-right-border">Premium</td>
</tr>
