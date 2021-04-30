<?php

if ( ! isset($user_id) ) $user_id = "";
if ( ! isset($company_id) ) $company_id = "";
if ( ! isset($company_name) ) $company_name = "";
if ( ! isset($company_address) ) $company_address = "";
if ( ! isset($responsiblefor) ) $responsiblefor = "";

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
    <td><?=getStringValue($company_name)?></td>
    <td><?=getStringValue($company_address)?></td>
    <?php
    if ( $responsiblefor === 't')
    {
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
