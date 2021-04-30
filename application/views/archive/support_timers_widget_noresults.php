<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($show_button) ) $show_button = true;

    $button_class = "";
    if ( ! $show_button ) $button_class = "hidden";
?>
<div class="widget-bg-color-icon card-box fadeInDown animated">
    <div class="bg-icon bg-icon-disabled pull-left" style="margin-top: 25px; ">
        <i class="md md-timer text-info a2p-disabled"></i>
    </div>
    <div class="text-right">
        <h3 class="text-dark"><b class="">-</b></h3>
        <p class="text-muted mb-0">Estimated Run Time</p>
        <div class='pull-right' style="visibility:<?=$button_class?>;"><a class="disabled btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("support/timers/company/{$id}");?>">More <i class="ion-arrow-right-c"></i></a></div>
    </div>
    <div class="clearfix"></div>
</div>


