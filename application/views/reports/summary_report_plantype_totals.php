<?php
    if ( ! isset($lives) ) $lives = "";
    if ( ! isset($volume) ) $volume = "";
    if ( ! isset($premium) ) $premium = "";
    if ( ! isset($adjusted_lives) ) $adjusted_lives = "";
    if ( ! isset($adjusted_volume) ) $adjusted_volume = "";
    if ( ! isset($adjusted_premium) ) $adjusted_premium = "";
    if ( ! isset($total_lives) ) $total_lives = "";
    if ( ! isset($total_volume) ) $total_volume = "";
    if ( ! isset($total_premium) ) $total_premium = "";
    if ( ! isset($type) ) $type = "";

?>
<tr>
    <td class="plantype plantypetotal"></td>
    <td class="plan plantypetotal"></td>
    <td class="attributes plantypetotal" colspan="2">Plan Type Total</td>
    <td class="lives plantypetotal"><?=GetReportNumberValue($lives)?></td>
    <td class="volume plantypetotal"><?=GetReportMoneyValue($volume);?></td>
    <td class="premium plantypetotal"><?=GetReportMoneyValue($premium);?></td>
    <td class="lives plantypetotal"><?=GetReportNumberValue($adjusted_lives)?></td>
    <td class="volume plantypetotal"><?=GetReportMoneyValue($adjusted_volume);?></td>
    <td class="premium plantypetotal"><?=GetReportMoneyValue($adjusted_premium);?></td>
    <td class="lives plantypetotal"><?=GetReportNumberValue($total_lives)?></td>
    <td class="volume plantypetotal"><?=GetReportMoneyValue($total_volume);?></td>
    <td class="premium plantypetotal"><?=GetReportMoneyValue($total_premium);?></td>
</tr>
