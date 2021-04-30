<?php
    if ( ! isset($page_header) ) $page_header = "";
    if ( ! isset($form) ) $form = "";
    if ( ! isset($data) ) $data = array();

?>
<div class="alert alert-success hidden" role="alert"><span class="alert-message"></span></div>
<?=$page_header?>
<div class="row">
    <div class="col-sm-12">
        <p class="text-muted page-title-alt">
            Following are individual life records detected from your data that may be updates to records found in the prior month.
        </p>
    </div>
</div>
<?=$form?>
