<?php
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($company_name) ) $company_name = "";
    if ( ! isset($company_parent_id) ) $company_parent_id = "";
    if ( ! isset($company_parent_name) ) $company_parent_name = "";
    if ( ! isset($recent_changes) ) $recent_changes = array();
    if ( ! isset($recent_snapshots) ) $recent_snapshots = array();
    if ( ! isset($recent_tickets) ) $recent_tickets = array();
    if ( ! isset($recent_exports) ) $recent_exports = array();
    if ( ! isset($in_process) ) $in_process = array();
    if ( ! isset($life_count)) $life_count = 0;
    if ( ! isset($commission_count)) $commission_count = 0;
    if ( ! isset($estimated_runtime)) $estimated_runtime = "";
    if ( ! isset($invoice_report_summary_widget) ) $invoice_report_summary_widget = "";

    $object_id = $company_id;
    $object_type = "company";
    if ( getStringValue($company_id) == "" ) {
        $object_id = $company_parent_id;
        $object_type = "parent";
    }
    


?>
<div class="row">
    <div class="col-sm-9">
        <h4 class="page-title">
        <ol class="breadcrumb m-t-0 p-t-0">
            <li class="" data-href="">Support</li>
        </ol>
        </h4>
    </div>
    <div class="col-sm-3">
        <div>
            <?=RenderViewAsString("archive/support_widget", array( "selected_id" => $object_id, "selected_type" => $object_type));?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <?php
            $view_array = array();
            $view_array['recent_changes'] = $recent_changes;
            $view_array['id'] = $object_id;
            $view_array['type'] = $object_type;
            if ( ! empty($recent_changes) ) echo RenderViewAsString("archive/recent_changes_widget", $view_array);
            if ( empty($recent_changes) ) echo RenderViewAsString("archive/recent_changes_noresults_widget", $view_array);
        ?>
    </div>
    <div class="col-sm-6">
        <?php
            $view_array = array();
            $view_array['recent_snapshots'] = $recent_snapshots;
            $view_array['id'] = $object_id;
            $view_array['type'] = $object_type;
            if ( ! empty($recent_snapshots) ) echo RenderViewAsString("archive/recent_snapshots_widget", $view_array);
            if ( empty($recent_snapshots) ) echo RenderViewAsString("archive/recent_snapshots_noresults_widget", $view_array);
        ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <?php
            $view_array = array();
            $view_array['recent_tickets'] = $recent_tickets;
            $view_array['id'] = $object_id;
            $view_array['type'] = $object_type;
            if (!empty($recent_tickets)) echo RenderViewAsString("archive/recent_tickets_widget", $view_array);
            if (empty($recent_tickets)) echo RenderViewAsString("archive/recent_tickets_noresults_widget", $view_array);
        ?>
    </div>
    <div class="col-sm-6">
        <?php
        $view_array = array();
        $view_array['recent_exports'] = $recent_exports;
        $view_array['id'] = $object_id;
        $view_array['type'] = $object_type;
        if (!empty($recent_exports)) echo RenderViewAsString("archive/recent_exports_widget", $view_array);
        if (empty($recent_exports)) echo RenderViewAsString("archive/recent_exports_noresults_widget", $view_array);
        ?>
    </div>
</div>





<div class="row">
    <div class="col-sm-12">
        <?php
            $view_array = array();
            $view_array['in_process'] = $in_process;
            $view_array['object_type'] = $object_type;
            if ( ! empty($in_process) && $object_type === 'company' ) echo RenderViewAsString("archive/in_process_wizard_widget", $view_array);
            if ( ! empty($in_process) && $object_type === 'parent' ) echo RenderViewAsString("archive/in_process_workflow_widget", $view_array);
            if ( empty($in_process) ) echo RenderViewAsString("archive/in_process_noresults_widget", $view_array);
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-3">
        <?php
        $view_array = array();
        $view_array['commission_count'] = $commission_count;
        $view_array['id'] = $object_id;
        $view_array['type'] = $object_type;
        echo RenderViewAsString("archive/commission_validation_summary_widget", $view_array);
        ?>
    </div>
    <div class="col-sm-3">
        <?php
        $view_array = array();
        $view_array['estimated_runtime'] = $estimated_runtime;
        $view_array['id'] = $object_id;
        $view_array['type'] = $object_type;
        echo RenderViewAsString("archive/support_timers_widget", $view_array);
        ?>
    </div>
    <div class="col-sm-3">
        <?php
            //$view_array = array();
            //echo RenderViewAsString("archive/blank_summary_widget", $view_array);
        ?>
        <?=$invoice_report_summary_widget?>
    </div>
    <div class="col-sm-3">
        <?php
        $view_array = array();
        $view_array['workflow_count'] = 4;
        echo RenderViewAsString("archive/workflow_viewer_widget", $view_array);
        ?>
    </div>
</div>
