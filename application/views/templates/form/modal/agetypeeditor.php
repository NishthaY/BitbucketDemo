<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($age_type) ) $age_type = "";
    if ( ! isset($best_guess) ) $best_guess = "";
    if ( ! isset($age_type_dropdown) ) $age_type_dropdown = "";
    if ( ! isset($anniversary_month_dropdown) ) $anniversary_month_dropdown = "";
    if ( ! isset($anniversary_day_dropdown) ) $anniversary_day_dropdown = "";
    if ( ! isset($anniversary_year) ) $anniversary_year = "";
    if ( ! isset($anniversary_class) ) $anniversary_class = "";
    if ( ! isset($washed_class) ) $washed_class = "";
    if ( ! isset($issued_class) ) $issued_class = "";
    if ( ! isset($best_guess_flg) ) $best_guess_flg = false;


    $best_guess_class = "hidden";
    if ( $best_guess_flg ) $best_guess_class = "";

?>
<div class="form-group has-feedback p-b-20">
    <div><label>Age Calculation Rule</label></div>
    <div>Select the rule that determines when in the reporting period birthdays should be calculated.</div>
    <div class="p-t-10">
        <div class="form-inline"> <?=$age_type_dropdown?> </div>
    </div>
    <div class="agetype-form-container form-inline <?=$anniversary_class?>">
        <div class="m-t-5">
            <?=$anniversary_month_dropdown?>
            <?=$anniversary_day_dropdown?>
        </div>
    </div>
    <div id="wash_description" class="<?=$washed_class?>"><small>The age of each life will be calculated for the month being processed using the wash rules specified on the plan type.</small></div>
    <div id="anniversary_description" class="<?=$anniversary_class?>"><small>A life's age will be calculated as of the anniversary date specified.</small></div>
    <div id="issued_description" class="<?=$issued_class?>"><small>The age of each life will be calculated as of the coverage start date specified in the data.</small></div>
    <div id="agerule_best_guess" class="<?=$best_guess_class?>"><small>Based on previous settings, we have pre-populated the age calculation rule for you.</small></div>
    <input class="hidden" type="text" id="agerule_validation_trigger" name="agerule_validation_trigger" value="">

</div>
