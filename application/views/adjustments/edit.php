<?php
    if ( ! isset($page_header) ) $page_header = "";
    if ( ! isset($form_html) ) $form_html = "";
    if ( ! isset($manual_adjustment_widget) ) $manual_adjustment_widget = "";
?>
<?=$page_header?>
<div class="row">
    <div class="col-sm-12">
        <p class="text-muted page-title-alt">
            Manual adjustments allow you to apply an adjustment to a specified carrier.
        </p>
    </div>
</div>
<?=$form_html?>
<?=$manual_adjustment_widget?>
