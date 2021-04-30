<?php
    if ( ! isset($count) ) $count = "";
    if ( ! isset($max) ) $max = GetAppOption('KEY_POOL_MAX');


    $visible = true;

    $button_class = "";
    if ( $max !== '' && $count !== '' )
    {
        $max = GetIntValue($max);
        $count = GetIntValue($count);
        if ( $count >= $max )
        {
            $button_class = "disabled";
        }
    }

    $max = GetIntValue($max);
    $count = GetIntValue($count);
    $message = "$count of $max";
    if ( $max === '' )  $message = "$count";
    if ( $count >= $max ) $message = "$count";

?>

<?php if ( $visible ) { ?>

    <div class="row">
        <div class="col-sm-12">

            <div class="widget-bg-color-icon card-box fadeInDown animated">
                <div class="bg-icon bg-icon-a2p pull-left" style="margin-top: 25px; ">
                    <i class="md md-lock text-info a2p-blue"></i>
                </div>
                <div class="text-right">
                    <h3 class="text-dark"><b class=""><?=$message?></b></h3>
                    <p class="text-muted mb-0">Security Key Pool</p>
                    <div class='pull-right'><a id="keypool_create_btn" class="<?=$button_class?> btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("support/keytool/create");?>">Add <i class="ion-plus-round"></i></a></div>
                </div>
                <div class="clearfix"></div>
            </div>

        </div>
    </div>

<?php } else {
    $view_array = array();
    echo RenderViewAsString("archive/keytool_noresults", $view_array);
}?>



