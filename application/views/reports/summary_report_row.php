<?php
    if ( ! isset($plantype) ) $plantype = "";
    if ( ! isset($plan) ) $plan = "";
    if ( ! isset($tier) ) $tier = "";
    if ( ! isset($attributes) ) $attributes = "";
    if ( ! isset($lives) ) $lives = "";
    if ( ! isset($volume) ) $volume = "";
    if ( ! isset($premium) ) $premium = "";
    if ( ! isset($adjusted_lives) ) $adjusted_lives = "";
    if ( ! isset($adjusted_volume) ) $adjusted_volume = "";
    if ( ! isset($adjusted_premium) ) $adjusted_premium = "";
    if ( ! isset($total_lives) ) $total_lives = "";
    if ( ! isset($total_volume) ) $total_volume = "";
    if ( ! isset($total_premium) ) $total_premium = "";
    if ( ! isset($page_break) ) $page_break = false;
    if ( ! isset($pdf) ) $pdf = false;
    if ( ! isset($company_id) ) $company_id = "";

    $header = "";
    if ( $page_break && $pdf )
    {
        $header = RenderViewAsString("reports/summary_report_title", array("company_id" => $company_id, "pagebreak" => true) );
    }
?>
<?=$header?>
<tr>
    <td class="plantype"><?=$plantype?></td>
    <td class="plan"><?=$plan?></td>
    <td class="tier"><?=$tier?></td>
    <td class="attributes"><?=$attributes?></td>
    <td class="lives"><?=$lives?></td>
    <td class="volume"><?=$volume?></td>
    <td class="premium"><?=$premium?></td>
    <td class="lives"><?=$adjusted_lives?></td>
    <td class="volume"><?=$adjusted_volume?></td>
    <td class="premium"><?=$adjusted_premium?></td>
    <td class="lives"><?=$total_lives?></td>
    <td class="volume"><?=$total_volume?></td>
    <td class="premium"><?=$total_premium?></td>
</tr>
