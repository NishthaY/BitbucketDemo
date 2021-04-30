<?php
    if ( ! isset($value) ) $value = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($icon) ) $icon = "fa fa-usd";

    $render_widget = false;
    if ( HasExistingReportData() ) $render_widget = true;
?>

<?php
if ( $render_widget ) {
?>

<div class="widget-bg-color-icon card-box fadeInDown animated">
    <div class="bg-icon bg-icon-primary pull-left">
        <i class="<?=$icon?> text-primary"></i>
    </div>
    <div class="text-right">
        <h3 class="text-dark">$<b class="counter"><?=$value?></b></h3>
        <p class="text-muted"><?=$description?></p>
    </div>
    <div class="clearfix"></div>
</div>


<?php
}
?>
