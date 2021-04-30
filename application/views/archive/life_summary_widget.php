<?php
if ( ! isset($life_count) ) $life_count = 0;
if ( ! isset($id) ) $id = "";
if ( ! isset($type) ) $type = "";

$visible = true;
if ( $type !== 'company' ) $visible = false;
if ( $visible && $id == 1 ) $visible = false;

?>

<?php if ( $visible ) { ?>

    <div class="row">
        <div class="col-sm-12">

            <div class="widget-bg-color-icon card-box fadeInDown animated">
                <div class="bg-icon bg-icon-a2p pull-left" style="margin-top: 25px; ">
                    <i class="md md-face-unlock text-info a2p-blue"></i>
                </div>
                <div class="text-right">
                    <h3 class="text-dark"><b class=""><?=$life_count?></b></h3>
                    <p class="text-muted mb-0">Total Lives</p>
                    <div class='pull-right'><a class="btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("support/lives/company/{$id}");?>">More <i class="ion-arrow-right-c"></i></a></div>
                </div>
                <div class="clearfix"></div>
            </div>

        </div>
    </div>

<?php } else {
    $view_array = array();
    echo RenderViewAsString("archive/life_summary_noresults_widget", $view_array);
}?>


