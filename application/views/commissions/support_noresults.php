<?php
    if ( ! isset($company) ) $carrier = "";
    if ( ! isset($company_id) ) $carrier = "";
    if ( ! isset($carrier) ) $carrier = "";
    if ( ! isset($carrier_id) ) $carrier_id = "";
    if ( ! isset($plantype) ) $plantype = "";
    if ( ! isset($plantype_id) ) $plantype_id = "";
    if ( ! isset($plan) ) $plan = "";
    if ( ! isset($plan_id) ) $plan_id = "";
    if ( ! isset($firstname) ) $firstname = "";
    if ( ! isset($lastname) ) $lastname = "";
    if ( ! isset($life_id) ) $life_id = "";

    if ( ! isset($commissions) ) $commissions = array();

?>
<div class="row">
    <div class="col-sm-9">
        <h4 class="page-title">
            <ol class="breadcrumb m-t-0 p-t-0">
                <li class="clickable-header-breadcrumb" data-href="<?=base_url("support/manage/company/{$company_id}")?>">Support</li>
                <li class="clickable-header-breadcrumb" data-href="<?=base_url("support/lives/company/{$company_id}")?>">Life</li>
                <li class="" data-href="">Commissions</li>
            </ol>
        </h4>
    </div>
    <div class="col-sm-12">
        No Results Found.
    </div>
</div>

