<?php
    if ( ! isset($user_id) ) $user_id = "";
    if ( ! isset($emailaddress) ) $emailaddress = "";
    if ( ! isset($firstname) ) $firstname = "";
    if ( ! isset($lastname) ) $lastname = "";
    if ( ! isset($enabled) ) $enabled = "";
    if ( ! isset($is_manager) ) $is_manager = "";
    if ( ! isset($responsiblefor) ) $responsiblefor = "";
    if ( ! isset($company_id) ) $company_id = "";

    $enabled_link = base_url("companies/assignment/assign");
    $enabled_icon = "fa fa-users";
    $enabled_label = "Assign";
    $enabled_row_class = "disabled-row";
    if ( $responsiblefor == "t" )
    {
        $enabled_link = base_url("companies/assignment/unassign");
        $enabled_icon = "fa fa-user";
        $enabled_label = "Unassign";
        $enabled_row_class = "";
    }

?>
    <tr class="<?=$enabled_row_class?>">
        <td><?=getStringValue($emailaddress)?></td>
        <td><?=getStringValue($firstname)?> <?=GetStringValue($lastname)?></td>
        <?php
            if ( $responsiblefor === 't') {

                print '<td><div class="checkbox checkbox-primary checkbox-single disabled"><input type="checkbox" checked="" disabled=""><label>Responsible For</label></div></td>';


            }
            else
            {
                print '<td><div class="checkbox checkbox-primary checkbox-single disabled"><input type="checkbox" disabled=""><label></label></div></td>';
            }
        ?>

        <td class="action-cell">
            <span class="action-buttons pull-right nowrap">
                <a class="action-cell-assign btn btn-white btn-xs waves-light waves-effect" href="<?=$enabled_link?>" data-company-id="<?=$company_id?>" data-user-id="<?=$user_id?>"><i class='<?=$enabled_icon?> m-r-5'></i> <?=$enabled_label?></a>
            </span>
    </td>
</tr>
