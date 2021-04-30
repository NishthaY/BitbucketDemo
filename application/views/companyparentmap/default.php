<?php
    if ( ! isset($page_header) ) $page_header = "";
    if ( ! isset($form) ) $form = "";
    if ( ! isset($widget) ) $widget = "";
    if ( ! isset($widget_type) ) $widget_type = "";
    if ( ! isset($summary_widget) ) $summary_widget = "";
    if ( ! isset($confirm_widget) ) $confirm_widget = "";


?>
<input id="selector_type" type="hidden" value="<?=$widget_type?>">
<div class="alert alert-success hidden" role="alert"><span class="alert-message"></span></div>
<?=$page_header?>
<div class="row">
    <div class="col-sm-12">
        <p class="text-muted page-title-alt">
            We have identified the following company matches from your data.
        </p>
    </div>
</div>
<?=$form?>

<div class="row">
    <div class="col-md-4">
        <?=$summary_widget?>
    </div>
    <div class="col-md-8">
        <?=$widget?>
    </div>
</div>
<?=$confirm_widget?>
