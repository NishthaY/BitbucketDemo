<?php

if ( ! isset($company_id) ) $company_id = "";
if ( ! isset($lives_widget) ) $lives_widget = "";

?>
<div class="row">
    <div class="col-sm-9">
        <h4 class="page-title">Lives</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item active"><a href="<?=base_url("support/manage/company/{$company_id}")?>">Support</a></li>
            <li class="breadcrumb-item">Lives</li>
        </ol>
    </div>
    <div class="col-sm-3">
    </div>
</div>
<div class="row">
    <div class="col-lg-9">
        <?=$lives_widget?>
    </div>
    <div class="col-lg-3">
        TODO: Add the ability to set a life filter.  Since the data on the left is limited to 1000 rows, you will need
        to apply a filter, like last name, to get the data set into something managable.
    </div>
</div>
