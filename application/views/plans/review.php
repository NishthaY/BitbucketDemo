<?php
    if ( ! isset($page_header) ) $page_header = "";
    if ( ! isset($page_form) ) $page_form = "";
    if ( ! isset($continue_flg) ) $continue_flg = false;
    if ( ! isset($warning_flg) ) $warning_flg = false;
    if ( ! isset($data) ) $data = array();
    if ( ! isset($carrier_widget)) $carrier_widget = "";
    if ( ! isset($plantype_widget)) $plantype_widget = "";
    if ( ! isset($ageband_widget)) $ageband_widget = "";
    if ( ! isset($tobacco_widget)) $tobacco_widget = "";
    if ( ! isset($company_id)) $company_id = "";
    if ( ! isset($required_list)) $required_list = "";

    $continue_disabled = " disabled ";
    if ( $continue_flg ) $continue_disabled = "";

    $continue_class = "btn-primary";
    if ( $continue_disabled ) $continue_class = "btn-working";

    $ignore_warning_class = " hidden ";
    if ( $warning_flg ) $ignore_warning_class = "";

    $has_relationship = HasRelationship($company_id);

?>
<div class="alert alert-success hidden" role="alert"><span class="alert-message"></span></div>
<?=$page_header?>
<div class="row">
    <div class="col-sm-12">
        <p class="text-muted page-title-alt">
            Following are the Plan Types, Plans and Coverage Tiers detected for your data.  To edit settings for a given plan type or coverage tier, click the link associated.
        </p>
    </div>
</div>
<div id="all_ignored_message" class="alert alert-wizard <?=$ignore_warning_class?>" role="alert">
    <span class="alert-message">
        <div>
            <span id="required_list" class="hidden"><?=$required_list?></span>
            <h4 class="page-title">Missing Required Information:</h4>
            <div class="row">
                <div class="col-sm-12">
                    <p class="">
                        All plan types have been ignored.  You must enable at least one plan type to continue.
                    </p>
                </div>
            </div>
        </div>
    </span>
</div>
<?=$page_form?>
<div class="panel panel-color panel-primary" >
    <div id="upload_review_table" class="panel-body" style="display:none;">
        <div class="row header">
            <div class="col-xs-2 header"><h4><strong>Carrier</strong></h4></div>
            <div class="col-xs-2 header"><h4><strong>Plan Type</strong></h4></div>
            <div class="col-xs-2 header"><h4><strong>Plan</strong></h4></div>
            <div class="col-xs-6 header"><h4><strong>Coverage Tier</strong></h4></div>
        </div>
        <?php
        foreach($data as $item)
        {
            $carrier = getArrayStringValue("Carrier", $item);
            $carrier_indicator = "no_indicator";
            if ( getArrayStringValue("IsCarrierMapped", $item) === 'FALSE' ) $carrier_indicator = "inline_question_indicator";

            $plantype = getArrayStringValue("PlanType", $item);
            $plantype_indicator = "inline_question_indicator";
            if ( getArrayStringValue("MappedFlg", $item) == "t" ) $plantype_indicator = "no_indicator";
            $plantype_code = getArrayStringValue("PlanTypeCode", $item);
            $plan = getArrayStringValue("Plan", $item);
            $plan_indicator = "no_indicator";
            $coverage_tier = getArrayStringValue("CoverageTier", $item);

            $coverage_tier_indicator = "no_indicator";
            $band_data = array();
            if ( isset($item['band']) ) $band_data = $item['band'];
            $tobacco_data = array();
            if ( isset($item['tobacco']) ) $tobacco_data = $item['tobacco'];

            $results = $this->PlanFees_model->plantype_has_plan_fees($plantype_code);
            $has_planfees = getArrayStringValue("PlanFees", $results);

            $plan_data = array();
            $plan_data['plan'] = $plan;
            $plan_data['plan_indicator'] = $plan_indicator;
            $plan_data['plantype_code'] = $plantype_code;
            $plan_data['has_relationship'] = $has_relationship;
            $plan_data['has_planfees'] = ( $has_planfees == "t" ? true : false );



            if ( getArrayStringValue("Ignored", $item) == "t" )
            {
                ?>
                <div class="row body">
                    <div class="col-xs-2 col line-right" data-carrier="<?=$carrier?>"><div><a class='carrier-link btn btn-white waves-light waves-effect m-b-5 btn-responsive' href="#"><?=RenderViewAsString("plans/{$carrier_indicator}")?><?=$carrier?></a></div></div>
                    <div class="col-xs-2 col line-right" data-plan-type="<?=$plantype?>" data-plan-type-code="<?=$plantype_code?>"><div><a class='plantype-link btn btn-white waves-light waves-effect m-b-5 btn-responsive' href="#"><?=RenderViewAsString("plans/no_indicator")?><?=$plantype?></a></div></div>
                    <div class="col-xs-8 col"><div class='plansetting-text'><i>Plan Type set to Ignored</i></div></div>
                </div>
                <?php
            }
            else
            {
                ?>
                <div class="row body">
                    <div class="col-xs-2 col line-right" data-carrier="<?=$carrier?>"><div><a class='carrier-link btn btn-white waves-light waves-effect m-b-5 btn-responsive' href="#"><?=RenderViewAsString("plans/{$carrier_indicator}")?><?=$carrier?></a></div></div>
                    <div class="col-xs-2 col line-right" data-plan-type="<?=$plantype?>" data-plan-type-code="<?=$plantype_code?>"><div><a class='plantype-link btn btn-white waves-light waves-effect m-b-5 btn-responsive' href="#"><?=RenderViewAsString("plans/{$plantype_indicator}")?><?=$plantype?></a></div></div>
                    <?=RenderViewAsString("plans/plan_button", $plan_data);?>
                    <div class="col-xs-2 col dimished-line line-right" data-coverage-tier="<?=$coverage_tier?>"><div class='plansetting-text'><?=$coverage_tier?></div></div>
                    <div class="col-xs-4 col dimished-line">
                        <div>
                            <?=RenderViewAsString("plans/agebands", array("details" => $band_data));?>
                            <?=RenderViewAsString("plans/tobacco", array("details" => $tobacco_data));?>
                        </div>
                    </div>
                </div>
                <?php
            }

        }
        ?>

    </div>
</div>
<?=$carrier_widget?>
<?=$plantype_widget?>
<?=$plan_widget?>
<?=$ageband_widget?>
<?=$tobacco_widget?>
