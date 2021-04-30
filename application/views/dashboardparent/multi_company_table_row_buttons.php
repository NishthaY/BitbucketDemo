<?php
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($enabled) ) $enabled = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($landing) ) $landing = "";
    if ( ! isset($import_date) ) $import_date = "";
    if ( ! isset($carriers) ) $carriers = array();

    $identifier = $company_id;
    $identifier_type = 'company';
?>
<?=RenderViewAsString("dashboardparent/multi_company_table_row_actions_button", array('company' => $company_id, 'enabled' => $enabled));?>
<a class="action-cell-changeto btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("companies/widget/changeto/{$company_id}")?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><i class="glyphicon glyphicon-circle-arrow-right m-r-5"></i> Change To</a>
<?=RenderViewAsString("dashboardparent/multi_company_table_row_continue_button", array('company' => $company_id, 'wf_row_description' => $description, 'landing' => $landing));?>
<?=RenderViewAsString("dashboardparent/multi_company_table_row_reports_button",  array('company' => $company_id, 'carriers' => $carriers, 'import_date' => $import_date, 'wf_row_description' => $description));?>
<?=RenderViewAsString("dashboardparent/multi_company_table_row_finalize_button", array('company' => $company_id, 'carriers' => $carriers, 'import_date' => $import_date, 'wf_row_description' => $description));?>
