<?php
    if ( ! isset($identifier) ) $identifier = "";
    if ( ! isset($identifier_type) ) $identifier_type = "";
    if ( ! isset($name) ) $name = "";
    if ( ! isset($address) ) $address = "";
    if ( ! isset($city) ) $city = "";
    if ( ! isset($state) ) $state = "";
    if ( ! isset($postal) ) $postal = "";
    if ( ! isset($enabled) ) $enabled = "";
    if ( ! isset($is_child) ) $is_child = "";

    $enabled_label = "";
    $enabled_icon = "";
    $enabled_label = "";
    $enabled_row_class = "";
    $href_rollback = "";
    $href_snapshot = "";
    $href_edit = "";
    $href_changeto = "";
    if ( $identifier_type === 'company' )
    {
        $enabled_link = base_url("companies/enable");
        $enabled_icon = "glyphicon glyphicon-eye-open";
        $enabled_label = "Enable";
        $enabled_row_class = "disabled-row";
        if ( $enabled == "t" )
        {
            $enabled_link = base_url("companies/disable");
            $enabled_icon = "glyphicon glyphicon-eye-close"; //"glyphicon glyphicon-ban-circle";
            $enabled_label = "Disable";
            $enabled_row_class = "";
        }
        $href_rollback = base_url("companies/widget/rollback/{$identifier}");
        $href_snapshot = base_url("support/snapshots/company/{$identifier}");
        $href_edit = base_url("companies/widget/edit/{$identifier}");
        $href_changeto = base_url("companies/widget/changeto/{$identifier}");
    }
    else if ( $identifier_type === 'companyparent')
    {
        $enabled_link = base_url("parents/enable");
        $enabled_icon = "glyphicon glyphicon-eye-open";
        $enabled_label = "Enable";
        $enabled_row_class = "disabled-row";
        if ( $enabled == "t" )
        {
            $enabled_link = base_url("parents/disable");
            $enabled_icon = "glyphicon glyphicon-eye-close"; //"glyphicon glyphicon-ban-circle";
            $enabled_label = "Disable";
            $enabled_row_class = "";
        }
        $href_rollback = base_url("parents/widget/rollback/{$identifier}");
        $href_snapshot = base_url("");
        $href_edit = base_url("parents/widget/edit/{$identifier}");
        $href_changeto = base_url("parents/widget/changeto/{$identifier}");
    }



?>
<tr class="<?=$enabled_row_class?>">
    <td><strong><?=$name?></strong></td>
    <td><?=$address?>, <?=$city?> <?=$state?> <?=$postal?></td>
    <td class="action-cell">
        <div class="action-buttons pull-right">
            <a class="action-cell-rollback btn btn-white btn-xs waves-light waves-effect" href="<?=$href_rollback?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><i class='fa fa-undo m-r-5'></i> Rollback</a>
            <a class="action-cell-snapshot btn btn-white btn-xs waves-light waves-effect" href="<?=$href_snapshot?>"><i class='fa fa-camera-retro m-r-5'></i> Snapshots</a>
            <a class="action-cell-edit btn btn-white btn-xs waves-light waves-effect" href="<?=$href_edit?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>" ><i class='glyphicon glyphicon-pencil m-r-5'></i> Edit</a>
            <a class="action-cell-remove btn btn-white btn-xs waves-light waves-effect" href="<?=$enabled_link?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><i class='<?=$enabled_icon?> m-r-5'></i> <?=$enabled_label?></a>
            <a class="action-cell-changeto btn btn-white btn-xs waves-light waves-effect" href="<?=$href_changeto?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>" ><i class='glyphicon glyphicon-circle-arrow-right m-r-5'></i> Change To</a>
        </div>
    </td>
</tr>
