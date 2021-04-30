<?php
    if ( ! isset($short_description) ) $short_description = "";
    if ( ! isset($long_description) ) $long_description = "";
    if ( ! isset($enabled) ) $enabled = false;
    if ( ! isset($button) ) $button = "";
    if ( ! isset($target) ) $target = "";
    if ( ! isset($target_type) ) $target_type = "";

    if ( $enabled ) $indicator = "text-success";
    if ( ! $enabled ) $indicator = "text-danger";



    $tag_class = "hidden";
    if($target !== '' ) $tag_class = "";
    if($target_type !== '' ) $tag_class = "";


?>
<div class="pull-right">
    <?=$button?>
    <div class="m-b-5 m-t-10 clearfix <?=$tag_class?>">
        <span class="label label-primary pull-right m-l-5"><?=$target?></span>
        <span class="label label-primary pull-right m-l-5"><?=$target_type?></span>
    </div>
</div>
<h4 class="m-t-0 header-title"><span class="m-t-5 m-r-10 pull=left"><i class="fa fa-circle m-0 p-0 <?=$indicator?>"></i></span> <b><?=$short_description?></b></h4>
<p><?=$long_description?></p>
<hr>