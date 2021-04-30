<?php
    if ( ! isset($company_id) ) $company_id = GetSessionValue("company_id");
    if ( ! isset($company_name) ) $company_name = "";
    if ( ! isset($company_address) ) $company_address = "";
    if ( ! isset($company_city) ) $company_city = "";
    if ( ! isset($company_state) ) $company_state = "";
    if ( ! isset($company_postal) ) $company_postal = "";
    if ( ! isset($enabled) ) $enabled = "";
    if ( ! isset($is_child) ) $is_child = "";

    $identifier = $company_id;
    $identifier_type = 'company';

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


?>
<tr class="<?=$enabled_row_class?>">
    <td><strong><?=$company_name?></strong></td>
    <td><?=$company_address?>, <?=$company_city?> <?=$company_state?> <?=$company_postal?></td>
    <td class="action-cell">
        <div class="action-buttons pull-right">
            <a class="action-cell-rollback btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("companies/widget/rollback/{$company_id}")?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>" ><i class='fa fa-undo m-r-5'></i> Rollback</a>
            <a class="action-cell-snapshot btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("support/snapshots/company/{$company_id}")?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>" ><i class='fa fa-camera-retro m-r-5'></i> Snapshots</a>
            <a class="action-cell-edit btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("companies/widget/edit/{$company_id}")?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>" ><i class='glyphicon glyphicon-pencil m-r-5'></i> Edit</a>
            <a class="action-cell-remove btn btn-white btn-xs waves-light waves-effect" href="<?=$enabled_link?>" data-company-id="<?=$company_id?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><i class='<?=$enabled_icon?> m-r-5'></i> <?=$enabled_label?></a>
            <a class="action-cell-changeto btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("companies/widget/changeto/{$company_id}")?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><i class='glyphicon glyphicon-circle-arrow-right m-r-5'></i> Change To</a>
        </div>
    </td>
</tr>
