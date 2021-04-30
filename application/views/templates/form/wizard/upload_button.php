<?php
    if ( ! isset ( $id) ) $id= "";
	if ( ! isset ( $description) ) $description = "";
	if ( ! isset ( $class) ) $class = "";
	if ( ! isset ( $submit) ) $submit = false;
    if ( ! isset ( $attributes) ) $attributes = "";

    $button_type = "button";
    if ( $submit ) $button_type = "submit";

?>
<button id="<?=$id?>" name="<?=$id?>" class="pull-right btn w-lg btn-lg waves-effect waves-light m-l-10 <?=$class?>" type="<?=$button_type?>" <?=$attributes?> formnovalidate><i class='ion-arrow-right-c'></i>  <?=$description?></button>
<input id="<?=$id?>_browse" type="file" style="display:none;" >
