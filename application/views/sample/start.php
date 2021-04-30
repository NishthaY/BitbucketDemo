<?php
    if ( ! isset($page_header) ) $page_header = "";
    if ( ! isset($validation_form) ) $validation_form = "";
?>
<div class="alert alert-success hidden" role="alert"><span class="alert-message"></span></div>
<?=$page_header?>
<div class="row">
    <div class="col-sm-12">
        <p class="text-muted page-title-alt">
            This is a sample waiting page for a workflow.  You can make yours look any way you want to fit your needs.
        </p>
    </div>
</div>
<?=$validation_form?>
