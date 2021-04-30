<?php
    if( ! isset($EmployeeId) ) $EmployeeId = "";
    if( ! isset($FirstName) ) $FirstName = "";
    if( ! isset($LastName) ) $LastName = "";
    if( ! isset($Relationship) ) $Relationship = "";
    if( ! isset($SSNDisplay) ) $SSNDisplay = "";
    if( ! isset($DateOfBirth) ) $DateOfBirth = "";
    if( ! isset($parent_id) ) $parent_id = "";
    if( ! isset($Id) ) $Id = "";
    if( ! isset($parent_row) ) $parent_row = array();
    if( ! isset($has_ssn) ) $has_ssn = false;

    $checked = "";
    $updates_life_id = getArrayStringValue("UpdatesLifeId", $parent_row);
    if ( $Id == $updates_life_id ) $checked = "checked";

?>
<?php
if ( ! $has_ssn ) {
?>
    <div class="row clickable-life m-b-5" data-href="<?=base_url()?>lives/save">
        <div class="col-sm-4"><span class="p-l-15"><input class="m-r-5" data-token="<?=$EmployeeId?>-<?=$Id?>" data-eid="<?=$EmployeeId?>" type="radio" name="LifeReview-<?=$parent_id?>" value="<?=$Id?>" <?=$checked?>><span class="text-primary m-r-20"><b><?=$LastName?>, <?=$FirstName?></b></span></span></div>
        <div class="col-sm-4"><span class="label label-primary m-r-20"><?=$Relationship?></span></div>
        <div class="col-sm-4"><span class="text-muted">Date of Birth:</span> <?=$DateOfBirth?></div>
    </div>
    <div class="row m-b-5 life-compare-disabled hidden">
        <div class="col-sm-4"><span class="p-l-15"><span class="m-r-20 initialism"><?=$LastName?>, <?=$FirstName?></span></span></div>
        <div class="col-sm-4"><span class="m-r-20 initialism"><?=$Relationship?></span></div>
        <div class="col-sm-4"><span class="initialism">Date of Birth:</span> <?=$DateOfBirth?></div>
    </div>

<?php
}
if ( $has_ssn ) {
    ?>
        <div class="row clickable-life m-b-5" data-href="<?=base_url()?>lives/save">
            <div class="col-sm-3"><span class="p-l-15"><input class="m-r-5" data-token="<?=$EmployeeId?>-<?=$Id?>" data-eid="<?=$EmployeeId?>" type="radio" name="LifeReview-<?=$parent_id?>" value="<?=$Id?>" <?=$checked?>><span class="text-primary m-r-20 life-compare-row-name"><b><?=$LastName?>, <?=$FirstName?></b></span></span></div>
            <div class="col-sm-3"><span class="life-compare-row-ssn"><?=$SSNDisplay?></span></div>
            <div class="col-sm-3"><span class="label label-primary m-r-20 life-compare-row-relationship"><?=$Relationship?></span></div>
            <div class="col-sm-3"><span class="text-muted">Date of Birth:</span> <i><?=$DateOfBirth?></i></div>
        </div>
        <div class="row m-b-5 life-compare-disabled hidden">
            <div class="col-sm-3"><span class="p-l-15"><span class="m-r-20 initialism"><b><?=$LastName?>, <?=$FirstName?></b></span></span></div>
            <div class="col-sm-3"><span class="initialism"><?=$SSNDisplay?></span></div>
            <div class="col-sm-3"><span class="m-r-20 initialism"><?=$Relationship?></span></div>
            <div class="col-sm-3"><span class="initialism">Date of Birth:</span> <i><?=$DateOfBirth?></i></div>
        </div>
    <?php
}
?>
