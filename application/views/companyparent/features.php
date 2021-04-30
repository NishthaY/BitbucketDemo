<?php
    if ( ! isset($page_header) ) $page_header = "";
    if ( ! isset($widgets) ) $widgets = array();
    if ( ! isset($file_transfer_widget) ) $file_transfer_widget = "";
    if ( ! isset($commission_tracking_widget) ) $commission_tracking_widget = "";
    if ( ! isset($column_normalization_widget) ) $column_normalization_widget = "";
    if ( ! isset($default_carrier_widget) ) $default_carrier_widget = "";
    if ( ! isset($targetable_feature_widget) ) $targetable_feature_widget = "";
    if ( ! isset($beneficiary_mapping_widget) ) $beneficiary_mapping_widget = "";
    if ( ! isset($default_plan_widget) ) $default_plan_widget = "";
    if ( ! isset($default_clarifications_widget) ) $default_clarifications_widget = "";
?>

<?=$page_header?>
<div class="row">
    <div class="col-lg-12">
        <div class="clearfix p-b-10">
            <button id="add_feature_btn" class="pull-right btn w-lg btn-lg waves-effect waves-light btn-white" type="button" formnovalidate="">Add Feature</button>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="widget-bg-color-icon card-box fadeInDown animated">
            <?php
                foreach($widgets as $widget)
                {
                    print $widget;
                }
            ?>
        </div>
    </div>
</div>
<?=$file_transfer_widget?>
<?=$commission_tracking_widget?>
<?=$column_normalization_widget?>
<?=$default_carrier_widget?>
<?=$targetable_feature_widget?>
<?=$beneficiary_mapping_widget?>
<?=$default_plan_widget?>
<?=$default_clarifications_widget?>