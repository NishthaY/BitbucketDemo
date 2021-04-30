<?php
if ( ! isset($user_id) ) $user_id = "";
if ( ! isset($email_address) ) $email_address = "";
if ( ! isset($first_name) ) $first_name = "";
if ( ! isset($last_name) ) $last_name = "";
if ( ! isset($enabled) ) $enabled = "";
if ( ! isset($is_manager) ) $is_manager = "";
if ( ! isset($company_id) ) $company_id = "";

$is_manager_class = "";
if ( $is_manager == "f" ) $is_manager_class = "";
if ( $is_manager == "t" ) $is_manager_class = "checked";
if ( $is_manager != "" && $is_manager_class != "checked" ) $is_manager_class = "";

?>
<tr class="">
    <td><?=getStringValue($email_address)?></td>
    <td><?=getStringValue($first_name)?> <?=GetStringValue($last_name)?></td>
    <td>
        <div class="checkbox checkbox-primary checkbox-single disabled">
            <input type="checkbox" <?=$is_manager_class?> disabled>
            <label></label>
        </div>
    </td>
</tr>
