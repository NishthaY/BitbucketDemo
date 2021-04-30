<?php
    if ( ! isset($user_id) ) $user_id = "";
    if ( ! isset($email_address) ) $email_address = "";
    if ( ! isset($first_name) ) $first_name = "";
    if ( ! isset($last_name) ) $last_name = "";
    if ( ! isset($enabled) ) $enabled = "";
    if ( ! isset($is_manager) ) $is_manager = "";
    if ( ! isset($company_id) ) $company_id = "";

?>
    <tr class="">
        <td><?=getStringValue($email_address)?></td>
        <td><?=getStringValue($first_name)?> <?=GetStringValue($last_name)?></td>
        <td><div class="checkbox checkbox-primary checkbox-single disabled"><input type="checkbox" checked="" disabled=""><label>Responsible For</label></div></td>
</tr>
