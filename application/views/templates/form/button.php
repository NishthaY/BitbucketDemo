<?php
    if ( ! isset ( $id) ) $id= "";
	if ( ! isset ( $description) ) $description = "";
	if ( ! isset ( $class) ) $class = "";
	if ( ! isset ( $submit) ) $submit = false;
    if ( ! isset ( $attributes) ) $attributes = "";

    $button_type = "button";
    if ( $submit ) $button_type = "submit";

?>

<button


    id="<?=$id?>" name="<?=$id?>" class="btn <?=$class?>" type="<?=$button_type?>" <?=$attributes?> formnovalidate><?=$description?></button>
