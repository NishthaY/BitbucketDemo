<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($bands) ) $bands = array();
    if ( ! isset($best_guess_flg) ) $best_guess_flg = false;


    $count = count($bands);
    if ( $count == 0 ) $count = 1;

    $best_guess = "hidden";
    if ( $best_guess_flg ) $best_guess = "";

?>
<label for="name"><?=$description?></label>
<div>Please set your age bands for this coverage tier below.  Age bands may not overlap.  You may indicate an age band starts at birth by entering 0 or the text <strong>Birth</strong>.  Likewise, you can indicate an age band should run until death by entering the text <strong>Death</strong>. To quickly add industry-standard 5-year or 10-year age bands, click the buttons below.</div>
<div id="age_band_container" class="form-group has-feedback form-border ageband-form-container" data-count="<?=$count?>">
    <div id="best_guess" class="p-b-10 <?=$best_guess?> best-guess"><small>Based on previous settings, we have pre-populated age bands for you.  Click save to keep the age bands or you can <a href="#" id="clear_form_link">remove the age bands</a> and enter custom bands specific to this coverage tier.</small></div>
    <div id="ageband_sample" class="form-inline form-group age-band-row hidden">
        Age <input name="bandX-start" type="text" class="form-control age-band-first"> through <input name="bandX-end" type="text" class="form-control age-band-second">  <span class="row-delete-icon"><i class="ion-close-circled"></i><span>
    </div>
    <?php
    // Show 1 blank lines
    if ( empty($bands) ) {
        ?>
        <div class="form-inline form-group age-band-row">
            Age <input name="band1-start" type="text" class="form-control age-band-first"> through <input name="band1-end" type="text" class="form-control age-band-second">  <span class="row-delete-icon"><i class="ion-close-circled"></i><span>
        </div>
        <?php
    }else{
        $count = 1;
        foreach($bands as $band) {
            $start = getArrayStringValue("AgeBandStart", $band);
            $end = getArrayStringValue("AgeBandEnd", $band);
            if ( $start == "0") $start = "Birth";
            if ( $end == "1000") $end = "Death";
            ?>
            <div class="form-inline form-group age-band-row">
                Age <input value="<?=$start?>" name="band<?=$count?>-start" type="text" class="form-control age-band-first"> through <input value="<?=$end?>" name="band<?=$count?>-end" type="text" class="form-control age-band-second">  <span class="row-delete-icon"><i class="ion-close-circled"></i><span>
            </div>
            <?php
            $count++;
        }
    }
    ?>
    <p class="help-block text-error"></p>
    <input class="hidden" type="text" id="validation_trigger" name="validation_trigger" value="">
    <input type="hidden" name="ageband_carrier_code" value="">
    <div class="clearfix">
        <button id="add_btn" type="button" class="btn btn-default waves-effect waves-light m-r-10" formnovalidate>Add Age Band</button>
        <a tabindex="0" id="5-YEAR" class="btn btn-default waves-effect waves-light default-bands m-r-10" href="<?=base_url()?>wizard/review/ageband/default/5year">5 Year Bands</a>
        <a tabindex="0" id="10-YEAR" class="btn btn-default waves-effect waves-light default-bands m-r-10" href="<?=base_url()?>wizard/review/ageband/default/10year">10 Year Bands</a>

    </div>
</div>
