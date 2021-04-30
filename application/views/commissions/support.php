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
    if ( ! isset($plans) ) $plans = array();
    if ( ! isset($warn) ) $warn = false;



?>
<div class="row">
    <div class="col-sm-9">
        <h4 class="page-title">Commissions</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item active"><a href="<?=base_url("support/manage/company/{$company_id}")?>">Support</a></li>
            <li class="breadcrumb-item active"><a href="<?=base_url("support/lives/company/{$company_id}")?>">Life</a></li>
            <li class="breadcrumb-item">Commissions</li>
            <li class="breadcrumb-item">
                    <span class="dropdown">
                        <a href="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class=""><?=$plan?> <i class="ion-arrow-down-b"></i></span></a>
                        <ul class="dropdown-menu" style="margin-left: 0px;">
                            <?php
                            foreach($plans as $item)
                            {
                                $display = GetArrayStringValue("UserDescription", $item);
                                $item_id = GetArrayStringValue("PlanId", $item);
                                $url = base_url("support/commissions/company/{$company_id}/{$life_id}/{$item_id}");
                                ?><li><a href="<?=$url?>"><?=$display?></a></li><?php
                            }
                            ?>
                        </ul>
                    </span>
            </li>
        </ol>
    </div>
    <div class="col-sm-3">
    </div>
</div>
<div class="row">
    <div class="col-sm-8">
        <?php
        if ( $warn )
        {
            $alert = new UIAlert();
            $alert->setMessage("This is not the active commission record for this life.<BR> This is the historical information for a life plan that has been discontinued.");
            $alert->setType("a2p");
            print $alert->render();
        }
        if ( count($commissions) > 0 )
        {
            $commission = $commissions[0];
            print RenderViewAsString("commissions/commission_widget", $commission);
        }
        ?>
        <?php
        foreach($commissions as $item)
        {
            print RenderViewAsString("commissions/commission_history_widget", $item);
        }
        ?>
    </div>
    <div class="col-sm-4">
        <?php
        $view_array = array();
        $view_array = array_merge($view_array, array( "company_id" => $company_id));
        $view_array = array_merge($view_array, array( "company" => $company));
        $view_array = array_merge($view_array, array( "carrier_id" => $carrier_id));
        $view_array = array_merge($view_array, array( "carrier" => $carrier));
        $view_array = array_merge($view_array, array( "plantype_id" => $plantype_id));
        $view_array = array_merge($view_array, array( "plantype" => $plantype));
        $view_array = array_merge($view_array, array( "plan_id" => $plan_id));
        $view_array = array_merge($view_array, array( "plan" => $plan));
        $view_array = array_merge($view_array, array( "firstname" => $firstname));
        $view_array = array_merge($view_array, array( "lastname" => $lastname));
        $view_array = array_merge($view_array, array( "life_id" => $life_id));
        echo RenderViewAsString("commissions/properties_widget", $view_array);
        ?>
    </div>
</div>
