<?php

    // Make sure our variables are safe.
    if ( ! isset ( $description ) ) $description = "";
    if ( ! isset ( $page_title ) ) $page_title = "";

    if ( ! isset ( $short_title ) ) $short_title = "";
    if ( ! isset ( $short_description ) ) $short_description = "";
    if ( ! isset ( $bottom_message ) ) $bottom_message = "";
    if ( ! isset ( $short_message ) ) $short_message = "";
    if ( ! isset ( $view ) ) $view = "";
    if ( ! isset ( $view_array ) ) $view_array = "";
    if ( ! isset ( $flash_message) ) $flash_message = "";
    if ( ! isset ( $custom_js) ) $custom_js = "";

    if ( ! isset ( $csrf_cookie_name ) ) $csrf_cookie_name = $this->config->item('csrf_cookie_name');
    if ( ! isset ( $csrf_token_name) ) $csrf_token_name = $this->security->get_csrf_token_name();
    if ( ! isset ( $csrf_expire) ) $csrf_expire = $this->config->item('csrf_expire');

    $enterprise_banner = new EnterpriseBanner();

?><!DOCTYPE html>
<html data-template='Advice2Pay' csrf-cookie-name="<?=$csrf_cookie_name?>" csrf-token-name="<?=$csrf_token_name?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="<?=$description?>">
        <meta name="author" content="Advice2Pay">
        <?php
            // If we are on this page template, we are working through the login
            // security and we need thost pages to reload/refresh if the token
            // expires.
            if ( $csrf_expire !== '' ) {
        ?>
            <META HTTP-EQUIV="REFRESH" CONTENT="<?=$csrf_expire?>">
        <?php } ?>

        <link rel="shortcut icon" href="assets/images/favicon_1.ico">

        <title><?=$page_title?></title>


        <link href="<?=base_url();?>assets/css/bootstrap.min.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/css/core.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/css/components.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/css/icons.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/css/pages.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/css/responsive.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/custom/css/override.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/custom/css/site.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/custom/css/debug.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/custom/scss/styles.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />

        <!-- Custom Box -->
        <link href="<?=base_url();?>assets/plugins/custombox/css/custombox.css<?=CachedQS()?>" rel="stylesheet">

        <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

        <script src="<?=base_url();?>assets/js/modernizr.min.js<?=CachedQS()?>"></script>
        <script type="text/javascript">
        //<![CDATA[
            base_url = '<?= base_url();?>';
        //]]>
        </script>

    </head>
    <body>
        <?=$enterprise_banner->render()?>
        <div class="account-pages"></div>
        <div class="clearfix"></div>
        <div class="wrapper-page">

        	<div class=" card-box">
            <div class="panel-heading">
                <?php if ( $short_title == "" ) { ?> <img height="137" width="320" class="logo img-responsive" title="Advice2Pay" src="<?=base_url('assets/custom/images/logo_640x274.png')?>" /> <?php } ?>
                <?php if ( $short_title != "" ) { ?> <h3 class="text-center text-custom"><strong><?=$short_title?></strong></h3> <?php } ?>
            </div>
            <div class="panel-body">
                <div class="text-center p-b-10"> <?=$short_message?> </div>
                <?php if ( getStringValue($short_description) !== '' ) { ?> <div class="text-left p-b-10"> <?=$short_description?> </div> <?php } ?>
                <?=RenderView($view, $view_array)?>
            </div>
            </div>
                <div class="row">
            	<div class="col-sm-12 text-center">
                    <p><?=$bottom_message?></p>
                    </div>
            </div>

        </div>
        <div id="template_overlay" class="overlay" style="display:none;">
            <div class="centered">
                <div class="loading"></div>
                <div class="loading-text">loading</div>
            </div>
        </div>



    	<script>
            var resizefunc = [];
        </script>

        <!-- jQuery  -->
        <script src="<?=base_url();?>assets/js/jquery.min.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/js/bootstrap.min.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/js/detect.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/js/fastclick.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/js/jquery.slimscroll.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/js/jquery.blockUI.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/js/waves.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/js/wow.min.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/js/jquery.nicescroll.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/js/jquery.scrollTo.min.js<?=CachedQS()?>"></script>

        <!-- Input Mask -->
        <script src="<?=base_url()?>assets/plugins/bootstrap-inputmask/bootstrap-inputmask.min.js" type="text/javascript"></script>

        <!-- Modal-Effect -->
        <script src="<?=base_url();?>assets/plugins/custombox/js/custombox.min.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/plugins/custombox/js/legacy.min.js<?=CachedQS()?>"></script>

        <!-- select2 ( text field with dropdown ) -->
        <script src="<?=base_url();?>assets/plugins/select2/js/select2.min.js" type="text/javascript"></script>

        <script src="<?=base_url();?>assets/js/jquery.core.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/js/jquery.app.js<?=CachedQS()?>"></script>

        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/jquery.form.min.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/plugins/jquery-validation/js/jquery.validate.min.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/jquery-validate<?=vJqueryValidate()?>/additional-methods.min.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/app.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/app_behaviors.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/app_validators.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/utilities.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/jquery.cookie.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url("assets/custom/js/ui-components/_loader.js")?>"></script>
        <?=$custom_js?>

        <?php if ( IsDevelopment() ) { ?>
            <?=RenderViewAsString("ajax_panic", array());?>
        <?php } ?>

	</body>
</html>
