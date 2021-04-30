<?php
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($type) ) $type = '';
    if ( ! isset($enabled) ) $enabled = "";

    $enabled_link = base_url("companies/enable");
    $enabled_icon = "glyphicon glyphicon-eye-open";
    $enabled_label = "Enable";
    $enable_action = "enable";
    if ( $enabled == "t" )
    {
        $enabled_link = base_url("companies/disable");
        $enabled_icon = "glyphicon glyphicon-eye-close";
        $enabled_label = "Disable";
        $enable_action = "disable";
    }


    $identifier = $company_id;
    $identifier_type = 'company';

    $skip_month_disabled = "";
    if ( ! IsSkipMonthProcessingAllowed($company_id, 'company') ) $skip_month_disabled = "disabled";


?>
<?php
if ( $type === 'disabled' )
{
?>
    <a disabled data-toggle="dropdown" class="action-cell-parent-options btn btn-white btn-xs waves-light waves-effect" type="button" formnovalidate=""><i class="md md-settings m-r-5"></i> Actions</a>
<?php
}
?>
<?php
if ( $type !== 'disabled' )
{
?>
    <span class="dropdown dropdown-action-button-container">
        <a class="btn btn-white btn-xs waves-light waves-effect dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" formnovalidate=""><i class="md md-settings m-r-5"></i> Actions</a>
        <ul role="menu" class="dropdown-menu options-cell-parent-reportreview">
            <li data-action='skip' data-company-id="<?=$company_id?>" class=" <?=$skip_month_disabled?> "><a disabled href="<?=base_url("widgettask/skip_month/{$company_id}")?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><span><i class="md md-skip-next"></i> Skip Month</span></a></li>
            <li data-action='edit' data-company-id="<?=$company_id?>"><a href="<?=base_url("companies/widget/edit/{$company_id}")?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><span><i class="glyphicon glyphicon-pencil m-r-5"></i> Edit Company Info</span></a></li>
            <li data-action='<?=$enable_action?>' data-company-id="<?=$company_id?>"><a href="<?=$enabled_link?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>" ><span><i class="<?=$enabled_icon?> m-r-5"></i> <?=$enabled_label?> Company</span></a> </li>
        </ul>
    </span>

<?php
}
?>