<?php
    if ( ! isset ( $id) ) $id= "";
	if ( ! isset ( $description) ) $description = "";
	if ( ! isset ( $class) ) $class = "";
	if ( ! isset ( $submit) ) $submit = false;
    if ( ! isset ( $attributes) ) $attributes = "";
    if ( ! isset( $disabled ) ) $disabled = false;

    $button_type = "button";
    if ( $submit ) $button_type = "submit";

    $disabled_tag = "";
    if ( $disabled ) $disabled_tag = " disabled ";
?>

<button <?=$disabled_tag?> id="<?=$id?>" type="<?=$button_type?>" class="btn <?=$class?> waves-effect waves-light" <?=$attributes?> formnovalidate><?=$description?></button>
