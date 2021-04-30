<?php

    // SUPPORT: Decode Data
    $decode_data_widget = new UIWidget("decode_data_widget");
    $decode_data_widget->setHref(base_url("support/widget/decode_data"));
    $decode_data_widget = $decode_data_widget->render();

    // SUPPORT: Encode Data
    $encode_data_widget = new UIWidget("encode_data_widget");
    $encode_data_widget->setHref(base_url("support/widget/encode_data"));
    $encode_data_widget = $encode_data_widget->render();

?>
<div id="dashboard_type" class="hidden" data-type="admin"></div>
<div class="row form-header">
    <div class="col-sm-6">
        <h4 class="page-title">Advice2Pay Administrative Dashboard</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?=base_url();?>dashboard/support">Quick Look</a></li>
            <?php if ( IsAuthenticated( ) ) { ?>
                <li class="breadcrumb-item"><a href="<?=base_url();?>dashboard/tools">Developer Tools</a></li>
            <?php } ?>
            <?php if ( IsAuthenticated( ) ) { ?>
                <li class="breadcrumb-item active">Security Tools</li>
            <?php } ?>
        </ol>
    </div>
    <div class="col-sm-6"></div>
</div>
<div class="row">
    <div class="col-lg-8">
        <?=$decode_data_widget?>
        <?=$encode_data_widget?>
    </div>
    <div class="col-lg-4">

    </div>
</div>