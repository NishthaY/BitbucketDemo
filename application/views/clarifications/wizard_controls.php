<?php
    if ( ! isset($page_header) ) $page_header = "";
    if ( ! isset($form) ) $form = "";
    if ( ! isset($data) ) $data = array();
    if ( ! isset($month) ) $month = "";
    if ( ! isset($company_id) ) $company_id = "";

?>
<div class="alert alert-success hidden" role="alert"><span class="alert-message"></span></div>
<?=$page_header?>
<div class="row">
    <div class="col-sm-12">
        <p class="text-muted page-title-alt">
            Based on the data provided for <?=$month?> compared to the previous month, we need to collect a bit more info to process your billing correctly.
            <BR>Your default clarification setting is <?=GetClarificationType($company_id, 'company')?>.
        </p>
    </div>
</div>
<?=$form?>
