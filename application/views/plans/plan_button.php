<?php
    if ( ! isset($plantype_code) ) $plantype_code = "";
    if ( ! isset($plan) ) $plan = "";
    if ( ! isset($plan_indicator) ) $plan_indicator = "";
    if ( ! isset($has_relationship) ) $has_relationship = false;
    if ( ! isset($has_planfees) ) $has_planfees = false;


?>
<?php
if ( $plantype_code == "" || ! $has_relationship || ! $has_planfees )
{
    ?>
    <div style="padding-top: 12px;" class="col-xs-2 col line-right" data-plan="<?=$plan?>"><div><?=$plan?></div></div>
    <?php
}
else
{
    ?>
    <div class="col-xs-2 col line-right" data-plan="<?=$plan?>"><div><a class='plan-link btn btn-white waves-light waves-effect m-b-5 btn-responsive' href="#"><?=RenderViewAsString("plans/{$plan_indicator}")?><?=$plan?></a></div></div>
    <?php
}
?>
