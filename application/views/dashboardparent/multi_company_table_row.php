<?php
    if ( ! isset($company_name) ) $company_name = "";
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($enabled) ) $enabled = "";
    if ( ! isset($carriers) ) $carriers = array();
    if ( ! isset($import_date) ) $import_date = "";
    if ( ! isset($runtime_error ) ) $runtime_error = "";
    if ( ! isset($busy) ) $busy = false;
    if ( ! isset($status) ) $status = "";
    if ( ! isset($landing) ) $landing = "";

    $hide_row = false;

    $status_class = "status-indicator";
    if ( $status === 'attention' ) $status_class = "status-indicator-attention";
    if ( $status === 'working' ) $status_class = "status-indicator-working";

    $identifier = $company_id;
    $identifier_type = 'company';

    $runtime_error_class = "";
    if ( GetStringValue($runtime_error) === '' ) $runtime_error_class = "hidden";

    // Style the whole row if it is enabled or disabled.
    $enabled_row_class = "disabled-row";
    if ( $enabled == "t" )
    {
        $enabled_row_class = "";
    }
?>
<?php
if ( ! $hide_row ) {
?>
    <tr class="<?= $enabled_row_class ?>" data-companyid="<?=$company_id?>">

        <td class="checkbox-column">
            <div class="checkbox-wrapper">
                <div class="checkbox_outer">
                    <input id="" type="checkbox" name="">
                </div>
            </div>
        </td>
        <td class="status-column">
            <i class="md md-border-circle status-indicator <?=$status_class?> "></i>
        </td>
        <td>

            <div class=""><span class="company-name"><?=$company_name?></span></div>
            <div class="">
                <?php
                    $message_hidden = "";
                    if ( $busy ) $message_hidden = "hidden";

                    $status_hidden = "hidden";
                    if ( $busy ) $status_hidden = "";
                ?>
                <span class="background-task-message-container <?=$message_hidden?> " data-companyid="<?=$company_id?>"><span class="background-task-message" data-companyid="<?=$company_id?>"><?=$description?></span></span>
                <span class="background-task-status-message-container <?=$status_hidden?>" data-companyid="<?=$company_id?>"><i class="fa fa-spin fa-cog"></i> <span class="background-task-status-message" data-companyid="<?=$company_id?>"><?=$description?></span></span>
                <span class="background-task-complete-container hidden" data-companyid="<?=$company_id?>"><i class="fa fa-spin fa-cog"></i> Reviewing results.</span>
            </div>
            <div class="runtime-error <?=$runtime_error_class?>"><?=$runtime_error?></div>
        </td>
        <td class="action-cell">
            <div class="action-buttons pull-right">
                <?php
                    $view_array = array();
                    $view_array['company_id'] = $company_id;
                    $view_array['enabled'] = $enabled;
                    $view_array['description'] = $description;
                    $view_array['landing'] = $landing;
                    $view_array['import_date'] = $import_date;
                    $view_array['carriers'] = $carriers;
                ?>
                <?=RenderViewAsString('dashboardparent/multi_company_table_row_buttons', $view_array);?>
            </div>
        </td>
    </tr>
    <?=RenderViewAsString("dashboardparent/review_draft_reports", array('company_id' => $company_id));?>
<?php

}
?>
