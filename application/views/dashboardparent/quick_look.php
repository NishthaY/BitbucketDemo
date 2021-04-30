<?php
    if (! isset($dashboard_task)) $dashboard_task = "";
    if (! isset($company_parent_id)) $company_parent_id = "";
    if (! isset($multi_company_widget) ) $multi_company_widget = "";
    if (! isset($edit_company_widget) ) $edit_company_widget = "";
    if (! isset($changeto_company_widget) ) $changeto_company_widget = "";
    if (! isset($getting_started_widget) ) $getting_started_widget = "";
    if (! isset($add_company_form_widget) ) $add_company_form_widget = "";
    if (! isset($download_list_widget) ) $download_list_widget = "";
    if (! isset($finalization_widget) ) $finalization_widget = "";
    if (! isset($warnings_widget) ) $warnings_widget = "";
    if (! isset($has_multi_company_data) ) $has_multi_company_data = false;
    if (! isset($header_html) ) $header_html = "";
    if (! isset($add_company_button) ) $add_company_button = "";
    if (! isset($action_button) ) $action_button = "";
    if (! isset($wf_parent_import_csv_widget) ) $wf_parent_import_csv_widget = "";
    if (! isset($import_data_widget) ) $import_data_widget = "";
    if (! isset($skip_month_widget) ) $skip_month_widget = "";


?>
<div id="dashboard_type" class="hidden" data-type="parent"></div>
<?=$header_html?>
<div class="widget-action-button-group">
    <div class="row">
        <div class="col-lg-12">
            <form>
                <div class="clearfix p-b-20">
                    <?=$action_button?>
                    <?=$add_company_button?>
                    <?=$wf_parent_import_csv_widget?>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <?=$multi_company_widget?>
        </div>
    </div>
</div>


<div class="row hidden">
    <div class="col-lg-6">
        <div class="card-box table-responsive" style="overflow: visible;">
            Placeholder: ?
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-box table-responsive" style="overflow: visible;">
            Placeholder: Parent Totals
        </div>
    </div>
</div>

<?=$edit_company_widget?>
<?=$changeto_company_widget?>
<?=$download_list_widget?>
<?=$dashboard_task?>
<?=$getting_started_widget?>
<?=$add_company_form_widget?>
<?=$finalization_widget?>
<?=$warnings_widget?>
<?=$skip_month_widget?>

