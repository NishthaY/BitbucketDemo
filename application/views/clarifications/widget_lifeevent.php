<?php

    if ( ! isset($FirstName) ) $FirstName = "";
    if ( ! isset($LastName) ) $LastName = "";
    if ( ! isset($EmployeeId) ) $EmployeeId = "";
    if ( ! isset($Carrier) ) $Carrier = "";
    if ( ! isset($PlanType) ) $PlanType = "";
    if ( ! isset($Plan) ) $Plan = "";
    if ( ! isset($BeforeCoverageStartDateList) ) $BeforeCoverageStartDateList = "";
    if ( ! isset($BeforeCoverageMonth) ) $BeforeCoverageMonth = "";
    if ( ! isset($CoverageStartDate) ) $CoverageStartDate = "";
    if ( ! isset($LifeEvent) ) $LifeEvent = "";
    if ( ! isset($RetroDataLifeEventId)) $RetroDataLifeEventId = "";
    if ( ! isset($RetroRule)) $RetroRule = 1;


    $FirstName = ucwords(strtolower(getStringValue($FirstName)));
    $LastName = ucwords(strtolower(getStringValue($LastName)));
    $EmployeeId = getStringValue($EmployeeId);
    $col_index = 12;

    $LifeEvent == "t" ? $le_checked = "checked" : $le_checked = "";
    $LifeEvent == "f" ? $checked = "checked" : $checked = "";


    $before_coverage_start_dates = array();
    $dates = explode(",", $BeforeCoverageStartDateList);
    foreach($dates as $date)
    {
        $date = date('m/d/Y',strtotime($date));
        $before_coverage_start_dates[] = $date;
    }
    sort($before_coverage_start_dates);

    $before_coverage_start_date = array_pop($before_coverage_start_dates);  // Latest Start Date
    //$before_coverage_start_date = $before_coverage_start_dates[0];  // Earliest Start Date
    //$before_coverage_start_date = implode(", ", $before_coverage_start_dates);  // All Start Dates




?>
<div class='row'>
    <div class="col-sm-<?=$col_index?>">
        <div class="widget-bg-color-icon card-box fadeInDown animated">
            <div class="row"><div class="col-sm-12"><b><?=$LastName?>, <?=$FirstName?> ( #<?=$EmployeeId?> )</b> - <?=$Carrier?> / <?=$PlanType?> / <?=$Plan?></div></div>
            <div class="row"><div class="col-sm-12"><span>Coverage Start Date</span><br></div></div>
            <div class="row"><div class="col-sm-12 m-l-10"><span>Last Month: <?=$before_coverage_start_date?></span><br></div></div>
            <div class="row"><div class="col-sm-12 m-l-10"><span>This Month: <?=$CoverageStartDate?></span><br></div></div>
            <hr>

            <div class="row clickable-clarifications" data-id="<?=$RetroDataLifeEventId?>" data-href="<?=base_url()?>clarifications/save">
                <div class="col-sm-12">
                    <span class="p-l-15 pull-left"><input class="m-r-5" type="radio" name="LifeEvent-<?=$RetroDataLifeEventId?>" value="NO" <?=$checked?>></span>
                    <span class="m-l-40" style="display:block;">The coverage start date change was due to a correction made in the source system. An adjustment for <?=MonthsImpacted($CoverageStartDate, $before_coverage_start_date, $RetroRule);?> may be applied.</span>
                </div>
            </div>
            <div class="row clickable-clarifications" data-id="<?=$RetroDataLifeEventId?>" data-href="<?=base_url()?>clarifications/save">
                <div class="col-sm-12">
                    <span class="p-l-15 pull-left"><input class="m-r-5" type="radio" name="LifeEvent-<?=$RetroDataLifeEventId?>" value="YES" <?=$le_checked?>></span>
                    <span class="m-l-40" style="display:block;">The coverage start date change was due to a life event update. Coverage has been in effect and no adjustments prior to the new start date are necessary.</span>
                </div>
            </div>
        </div>
    </div>
</div>
