<?php

    if ( ! isset($id) ) $id = "";
    if ( ! isset($enabled) ) $enabled = "f";
    if ( ! isset($name) ) $name = "";
    if ( ! isset($address) ) $address = "";
    if ( ! isset($city) ) $city = "";
    if ( ! isset($state) ) $state = "";
    if ( ! isset($postal) ) $postal = "";

    $identifier = $id;
    $identifier_type = 'companyparent';

    $enabled_link = base_url("parents/enable");
    $enabled_icon = "glyphicon glyphicon-eye-open";
    $enabled_row_class = "disabled-row";
    $enabled_label = "Enable";
    if ( $enabled == "t" )
    {
        $enabled_link = base_url("parents/disable");
        $enabled_icon = "glyphicon glyphicon-eye-close";
        $enabled_row_class = "";
        $enabled_label = "Disable";
    }

?>
<tr class="<?=$enabled_row_class?>">
    <td><?=getStringValue($name)?></td>
    <td><?=getStringValue($address)?>, <?=getStringValue($city)?> <?=getStringValue($state)?> <?=getStringValue($postal)?></td>
    <td class='action-cell'>
        <span class="action-buttons pull-right nowrap">
            <a class="action-cell-rollback btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("parents/widget/rollback/{$identifier}")?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><i class='fa fa-undo m-r-5'></i> Rollback</a>
            <a class="action-cell-edit btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("parents/widget/edit/{$id}")?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>" ><i class='glyphicon glyphicon-pencil m-r-5'></i> Edit</a>
            <a class="action-cell-remove btn btn-white btn-xs waves-light waves-effect" href="<?=$enabled_link?>" data-company-parent-id="<?=$id?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>" ><i class='<?=$enabled_icon?> m-r-5'></i> <?=$enabled_label?></a>
            <a class="action-cell-changeto btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("parents/widget/changeto/{$id}")?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>" ><i class='glyphicon glyphicon-circle-arrow-right m-r-5'></i> Change To</a>
        </span>
    </td>
</tr>
