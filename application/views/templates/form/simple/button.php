<?php
    if ( ! isset ( $id) ) $id= "";
	if ( ! isset ( $description) ) $description = "";
	if ( ! isset ( $class) ) $class = "";
	if ( ! isset ( $submit) ) $submit = false;
    if ( ! isset ( $attributes) ) $attributes = "";
    if ( ! isset ( $disabled ) ) $disabled = false;

    $button_type = "button";
    if ( $submit ) $button_type = "submit";

    $disabled_tag = "";
    if ( $disabled ) $disabled_tag = " disabled ";

?>
<div class="form-group text-center m-t-40">
    <div class="col-xs-12">
        <button <?=$disabled_tag?> id="<?=$id?>" type="<?=$button_type?>" class="btn btn-white btn-block text-uppercase waves-effect waves-light <?=$class?>"  <?=$attributes?> formnovalidate><?=$description?></button>
    </div>
</div>
