<?php

    if ( ! isset($user_id) ) $user_id = "";
    if ( ! isset($enabled) ) $enabled = "f";
    if ( ! isset($email_address) ) $email_address = "";
    if ( ! isset($first_name) ) $first_name = "";
    if ( ! isset($last_name) ) $last_name = "";
    if ( ! isset($is_manager) ) $is_manager = "?";

    $enabled_link = base_url("users/enable");
    $enabled_icon = "glyphicon glyphicon-eye-open";
    $enabled_row_class = "disabled-row";
    $enabled_label = "Enable";
    if ( $enabled == "t" )
    {
        $enabled_link = base_url("users/disable");
        $enabled_icon = "glyphicon glyphicon-eye-close";
        $enabled_row_class = "";
        $enabled_label = "Disable";
    }

    $is_manager_class = "";
    if ( $is_manager == "f" ) $is_manager_class = "";
    if ( $is_manager == "t" ) $is_manager_class = "checked";
    if ( $is_manager != "" && $is_manager_class != "checked" ) $is_manager_class = "";
    if ( GetSessionValue("company_id") == "1" ) $is_manager_class = "checked"; // display checked if A2P.


?>
<tr class="<?=$enabled_row_class?>">
    <td><?=$email_address?></td>
    <td><?=$first_name?> <?=$last_name?></td>
    <td>
        <div class="checkbox checkbox-primary checkbox-single disabled">
            <input type="checkbox" <?=$is_manager_class?> disabled>
            <label></label>
        </div>
    </td>
    <td class='action-cell'>
        <span class="action-buttons pull-right nowrap">

            <?php
            if ( $is_manager === 'f' && IsAuthenticated("parent_company_write") && GetSessionValue("companyparent_id") !== '' ) {
                ?>
                <a class="action-cell-assignment btn btn-white btn-xs waves-light waves-effect"
                   href="<?= base_url('users/assignment/' . $user_id); ?>"><i class='fa fa-users m-r-5'></i> Assignments</a>
                <?php
            }
            ?>

            <a class="action-cell-delete btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("users/widget/delete/{$user_id}")?>"><i class='fa fa-trash m-r-5'></i> Delete</a>
            <a class="action-cell-edit btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("users/widget/edit/{$user_id}")?>"><i class='glyphicon glyphicon-pencil m-r-5'></i> Edit</a>
            <a class="action-cell-remove btn btn-white btn-xs waves-light waves-effect" href="<?=$enabled_link?>" data-user-id="<?=$user_id?>"><i class='<?=$enabled_icon?> m-r-5'></i> <?=$enabled_label?></a>
        </span>
    </td>
</tr>
