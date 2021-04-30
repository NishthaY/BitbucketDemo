<?php
    if ( ! isset($left_label) ) $left_label = "";
    if ( ! isset($left_href) ) $left_href = "";
    if ( ! isset($right_label) ) $right_label = "";
    if ( ! isset($right_href) ) $right_href = "";
    if ( ! isset($attributes) ) $attributes = "";
?>
<?php if ( $left_label != "" && $right_label == "" ) { ?>
    <div class="form-group m-t-30 m-b-0">
        <div class="col-sm-12">
            <a href="<?=$left_href?>" class="btn btn-primary btn-block" role="button"><?=$left_label?></a>
        </div>
    </div>
<?php } ?>
<?php if ( $left_label == "" && $right_label != "" ) { ?>
    <div class="form-group m-t-30 m-b-0">
        <div class="col-sm-12">
            <a href="<?=$right_href?>" class="btn btn-primary btn-block" role="button"><?=$right_label?></a>
        </div>
    </div>
<?php } ?>
<?php if ( $left_label != "" && $right_label != "" ) { ?>
    <div class="form-group m-t-30 m-b-0">
        <div class="col-sm-6">
            <a href="<?=$left_href?>" class="btn btn-primary btn-block" role="button"><?=$left_label?></a>
        </div>
        <div class="col-sm-6">
            <a href="<?=$right_href?>" class="btn btn-primary btn-block" role="button"><?=$right_label?></a>
        </div>
    </div>
<?php } ?>
