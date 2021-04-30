<?php
if ( ! isset($estimated_runtime) ) $estimated_runtime = "";
if ( ! isset($show_button) ) $show_button = true;
if ( ! isset($id) ) $id = "";

$visible = true;
if ( $estimated_runtime === '' ) $visible = false;

$button_class = "";
if ( ! $show_button ) $button_class = "hidden";

?>

<?php if ( $visible ) { ?>

    <div class="row">
        <div class="col-sm-12">

            <div class="widget-bg-color-icon card-box fadeInDown animated">
                <div class="bg-icon bg-icon-a2p pull-left" style="margin-top: 25px; ">
                    <i class="md md-timer text-info a2p-blue"></i>
                </div>
                <div class="text-right">
                    <h3 class="text-dark"><b class=""><?=$estimated_runtime?></b></h3>
                    <p class="text-muted mb-0">Estimated Run Time</p>
                    <div class='pull-right' style="visibility:<?=$button_class?>;"><a class="btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("support/timers/company/{$id}");?>">More <i class="ion-arrow-right-c"></i></a></div>
                </div>
                <div class="clearfix"></div>
            </div>

        </div>
    </div>

<?php } else {
    $view_array = array();
    echo RenderViewAsString("archive/support_timers_widget_noresults", $view_array);
}?>


