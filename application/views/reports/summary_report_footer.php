<?php
    if ( ! isset($page) ) $page = "";
    if ( ! isset($total) ) $total = "";
    if ( ! isset($company) ) $company = "";
    if ( ! isset($carrier) ) $carrier = "";

    $page = trim(getStringValue($page));
    $total = trim(getStringValue($total));


?>
<div>
    <table cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td style="border-top: 1px solid #ccc; text-align: left; color: #999; font-size: 7px;"><?=$carrier?></td>
            <td style="border-top: 1px solid #ccc; text-align: center; color: #999; font-size: 7px;"><?=$page?> of <?=$total?></td>
            <td style="border-top: 1px solid #ccc; text-align: right; color: #999; font-size: 7px;"><?=$company?></td>
        </tr>
    </table>
</div>
