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
``
?>
<div class="card-box">
    <h4 class="m-t-0 m-b-20 header-title"><b>Properties</b></h4>

    <div class="nicescroll" tabindex="5000" style="overflow: hidden; outline: none; max-height: 280px; min-height: 280px">
        <ul class="list-unstyled transaction-list m-r-5">

            <li>
                <i class="md md-face-unlock"></i>
                <span class="tran-text">Life</span>
                <span class="pull-right text-muted"><?=$firstname?> <?=$lastname?> ( <?=$life_id?> )</span>
                <span class="clearfix"></span>
            </li>
            <li style="height:30px;"></li>
            <li>
                <i class="ion-briefcase"></i>
                <span class="tran-text">Company</span>
                <span class="pull-right text-muted"><?=$company?> ( <?=$company_id?> )</span>
                <span class="clearfix"></span>
            </li>
            <li style="height:30px;"></li>
            <li>
                <i class="ion-ios7-circle-filled"></i>
                <span class="tran-text">Carrier</span>
                <span class="pull-right text-muted"><?=$carrier?> ( <?=$carrier_id?> )</span>
                <span class="clearfix"></span>
            </li>
            <li>
                <i class="ion-ios7-circle-filled"></i>
                <span class="tran-text">Plan Type</span>
                <span class="pull-right text-muted"><?=$plantype?> ( <?=$plantype_id?> )</span>
                <span class="clearfix"></span>
            </li>
            <li>
                <i class="ion-ios7-circle-filled"></i>
                <span class="tran-text">Plan</span>
                <span class="pull-right text-muted"><?=$plan?> ( <?=$plan_id?> )</span>
                <span class="clearfix"></span>
            </li>


        </ul>
    </div>
</div>
