<?php
    if ( ! isset ( $id) ) $id= "";
	if ( ! isset ( $description) ) $description = "";
	if ( ! isset ( $class) ) $class = "";
	if ( ! isset ( $submit) ) $submit = false;
    if ( ! isset ( $attributes) ) $attributes = "";
    if ( ! isset($disabled) ) $disabled = false;

    $button_type = "button";
    if ( $submit ) $button_type = "submit";

    $disabled_tag = "";
    if ( $disabled ) $disabled_tag = " disabled ";

?>
<button <?=$disabled_tag?> id="<?=$id?>" name="<?=$id?>" class="pull-right btn w-lg btn-lg waves-effect waves-light m-l-10 <?=$class?>" type="<?=$button_type?>"  <?=$attributes?> formnovalidate><?=$description?></button>
