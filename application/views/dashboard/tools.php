<?php

    // SUPPORT: QueueDirector Status Bar
    $director_statusbar_widget = new UIWidget("director_statusbar_widget");
    $director_statusbar_widget->setHref(base_url("support/widget/director/details"));
    $director_statusbar_widget = $director_statusbar_widget->render();


    // SUPPORT: App Options
    $app_options_widget = new UIWidget("app_options_widget");
    $app_options_widget->setHref(base_url("support/widget/app_options"));
    $app_options_widget = $app_options_widget->render();

    // SUPPORT: Postgres Options
    $pg_options_widget = new UIWidget("pg_options_widget");
    $pg_options_widget->setHref(base_url("support/widget/pg_options"));
    $pg_options_widget = $pg_options_widget->render();

    $dyno_widget = new UIWidget("dyno_widget");
    $dyno_widget->setHref(base_url("support/widget/dynos"));
    $dyno_widget = $dyno_widget->render();

    $dyno_details_widget = new UIWidget("dyno_details_widget");
    $dyno_details_widget->setHref(base_url("support/dyno/detail"));
    $dyno_details_widget = $dyno_details_widget->render();

    // KeyPool: Create KMS keys ready for action!
    $key_pool_widget = new UIWidget("keypool_widget");
    $key_pool_widget->setHref(base_url("support/widget/keypool"));
    $key_pool_widget = $key_pool_widget->render();

?>
<div id="dashboard_type" class="hidden" data-type="admin"></div>
<div class="row form-header">
    <div class="col-sm-6">
        <h4 class="page-title">Advice2Pay Administrative Dashboard</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?=base_url();?>dashboard/support">Quick Look</a></li>
            <?php if ( IsAuthenticated( ) ) { ?>
                <li class="breadcrumb-item active">Developer Tools</li>
            <?php } ?>
            <?php if ( IsAuthenticated( ) ) { ?>
                <li class="breadcrumb-item"><a href="<?=base_url();?>dashboard/security">Security Tools</a></li>
            <?php } ?>
        </ol>
    </div>
    <div class="col-sm-6"></div>
</div>
<div class="row">
    <div class="col-lg-12">
        <?=$dyno_widget?>
        <?=$dyno_details_widget?>
    </div>
</div>
<div class="row">
    <div class="col-sm-8">
        <?=$director_statusbar_widget?>
        <div class="col-lg-12" style="margin-left: -10px; margin-right: -10px;">
            <div class="row">
                <div class="col-lg-4"><?=$key_pool_widget?></div>
                <div class="col-lg-4"></div>
                <div class="col-lg-4"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">

        <!-- Application Options -->
        <div class="col-xl-4">
            <?=$app_options_widget?>
        </div>
        <!-- Postgres Options -->
        <div class="col-xl-4">
            <?=$pg_options_widget?>
        </div>

    </div>
</div>
