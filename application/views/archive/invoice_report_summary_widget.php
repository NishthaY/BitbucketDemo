<?php
if ( ! isset($money_value) ) $money_value = "";
if ( ! isset($previous_money_value) ) $previous_money_value = "";
if ( ! isset($display_date) ) $display_date = "";
if ( ! isset($show_button) ) $show_button = true;
if ( ! isset($identifier) ) $identifier = "";
if ( ! isset($identifier_type) ) $identifier_type = "";
if ( ! isset($date_tag) ) $date_tag = "";


$url_identifier_type = $identifier_type;
if ( $identifier_type === 'companyparent' ) $url_identifier_type = 'parent';

$visible = true;

$button_class = "";
if ( ! $show_button ) $button_class = "hidden";

?>

<?php if ( $visible ) { ?>

    <div class="row">
        <div class="col-sm-12">

            <div class="widget-bg-color-icon card-box fadeInDown animated">
                <div class="bg-icon bg-icon-a2p pull-left" style="margin-top: 25px; ">
                    <i class="md md-attach-money text-info a2p-blue"></i>
                </div>
                <div class="text-right">
                    <h3 class="text-dark"><b class=""><?=$money_value?></b></h3>
                    <p class="text-muted mb-0">Invoice Report ( <?=$display_date?> )</p>
                    <div class='pull-right' style="visibility:<?=$button_class?>;"><a class="btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("support/invoice/{$url_identifier_type}/{$identifier}/{$date_tag}");?>">More <i class="ion-arrow-right-c"></i></a></div>
                </div>
                <div class="clearfix"></div>
            </div>

        </div>
    </div>

<?php } else {
    $view_array = array();
    echo RenderViewAsString("archive/invoice_report_widget_noresults", $view_array);
}?>


