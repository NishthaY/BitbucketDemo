<?php
    if( ! isset($EmployeeId) ) $EmployeeId = "";
    if( ! isset($FirstName) ) $FirstName = "";
    if( ! isset($LastName) ) $LastName = "";
    if( ! isset($Relationship) ) $Relationship = "";
    if( ! isset($SSNDisplay) ) $SSNDisplay = "";
    if( ! isset($DateOfBirth) ) $DateOfBirth = "";
    if( ! isset($has_ssn) ) $has_ssn = false;
    if ( ! isset($columns) ) $columns = 12;
    $col_index = 12;

?>
<div class='row'>
    <div class="col-sm-<?=$col_index?>">
        <div class="widget-bg-color-icon card-box fadeInDown animated">
            <div class="row"><div class="col-sm-12"><h4 class="m-t-0 header-title"><b>New / Updated Life Record Discovered:</b></h4></div></div>
            <div class="row"><div class="col-sm-12"><span class="text-muted">Employee Id: <?=$EmployeeId?></span><br></div></div>
            <div class="row">

                <?php
                if ( ! $has_ssn ) {
                ?>
                    <div class="col-sm-4"><span class="text-primary m-r-20"><b><?=$LastName?>, <?=$FirstName?></b></span></div>
                    <div class="col-sm-4"><span class="label label-primary m-r-20"><?=$Relationship?></span></div>
                    <div class="col-sm-4"><span class="text-muted">Date of Birth:</span> <?=$DateOfBirth?></div>
                <?php
                }
                if ( $has_ssn ) {
                ?>
                    <div class="col-sm-3"><span class="text-primary m-r-20"><b><?=$LastName?>, <?=$FirstName?></b></span></div>
                    <div class="col-sm-3"><?=$SSNDisplay?></div>
                    <div class="col-sm-3"><span class="label label-primary m-r-20"><?=$Relationship?></span></div>
                    <div class="col-sm-3"><span class="text-muted">Date of Birth:</span> <?=$DateOfBirth?></div>
                <?php
                }
                ?>
            </div>
            <hr>
            <div class="row"><div class="col-sm-12"><h4 class="m-t-0 header-title"><b>Select Life Record to Update</b></h4></div></div>
