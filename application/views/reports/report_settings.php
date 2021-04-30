<?php
if ( ! isset($form_header) ) $form_header = "";
if ( ! isset($company_id) ) $company_id = "";
if ( ! isset($relationship_settings) ) $relationship_settings = "";
if ( ! isset($column_settings) ) $column_settings = "";
if ( ! isset($plan_settings) ) $plan_settings = "";




?>
<?=$form_header?>
<div class="row">
    <div class="col-sm-8">
        <?=$column_settings?>
    </div>
    <div class="col-sm-4"></div>
</div>
<div class="row">
    <div class="col-sm-8">
        <?=$relationship_settings?>
    </div>
    <div class="col-sm-4"></div>
</div>
<div class="row">
    <div class="col-sm-8">
        <?=$plan_settings?>
    </div>
    <div class="col-sm-4"></div>
</div>

