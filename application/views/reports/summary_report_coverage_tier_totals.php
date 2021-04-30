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
    if ( ! isset($count) ) $count = "";

?>
<?php
    if ( getIntValue($count) != 1 ) {
        ?>
        <tr class="tiertotal">
            <td class="plantype"></td>
            <td class="plan"></td>
            <td class="attributes" colspan="2">Tier Total</td>
            <td class="lives"><?=GetReportNumberValue($lives)?></td>
            <td class="volume"><?=GetReportMoneyValue($volume);?></td>
            <td class="premium"><?=GetReportMoneyValue($premium);?></td>
            <td class="lives"><?=GetReportNumberValue($adjusted_lives)?></td>
            <td class="volume"><?=GetReportMoneyValue($adjusted_volume);?></td>
            <td class="premium"><?=GetReportMoneyValue($adjusted_premium);?></td>
            <td class="lives"><?=GetReportNumberValue($total_lives)?></td>
            <td class="volume"><?=GetReportMoneyValue($total_volume);?></td>
            <td class="premium"><?=GetReportMoneyValue($total_premium);?></td>
        </tr>

        <?php
    }
?>
