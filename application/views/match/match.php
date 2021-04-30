<?php
    if ( ! isset($page_header) ) $page_header = "";
    if ( ! isset($validation_form) ) $validation_form = "";
?>
<div class="alert alert-success hidden" role="alert"><span class="alert-message"></span></div>
<?=$page_header?>
<div class="row">
    <div class="col-sm-12">
        <p class="text-muted page-title-alt">
            We have identified the following column matches from your data. Please look at our existing matches and confirm things look correct and match any required columns not yet matched.  For optimal processing of data and carrier acceptance, we suggest matching as many columns as possible to data you have, even if the column is not indicated as required. Once you are done matching, continue.
        </p>
    </div>
</div>
<?=$validation_form?>
