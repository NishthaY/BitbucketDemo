<?php

    // Make sure our variables are safe.
    if ( ! isset ( $custom_js ) ) $custom_js = "";
    if ( ! isset ( $page_title ) ) $page_title = "";
    if ( ! isset ( $short_title ) ) $short_title = "";
    if ( ! isset ( $short_message ) ) $short_message = "";
    if ( ! isset ( $view ) ) $view = "";
    if ( ! isset ( $view_array ) ) $view_array = "";
    if ( ! isset ( $side_menu ) ) $side_menu = "";
    if ( ! isset ( $description ) ) $description = "Advice 2 Pay";
    if ( ! isset ( $error_message ) ) $error_message = "";
    if ( ! isset ( $company_id ) ) $company_id = "";
    if ( ! isset ( $companyparent_id ) ) $companyparent_id = "";
    if ( ! isset ( $csrf_cookie_name ) ) $csrf_cookie_name = $this->config->item('csrf_cookie_name');
    if ( ! isset ( $csrf_token_name) ) $csrf_token_name = $this->security->get_csrf_token_name();
    if ( ! isset ( $status_message ) ) $status_message = "";

    $alert_class = "hidden";
    if ( $error_message != "" ) $alert_class = "";

    $edit_profile_widget = new UIWidget("edit_profile_widget");
    $edit_profile_widget->setBody( EditProfileWidget() );
    $edit_profile_widget->setHref(base_url("widgettask/edit_profile"));
    $edit_profile_widget = $edit_profile_widget->render();

    $edit_auth_code_widget = new UIWidget("edit_password_widget");
    $edit_auth_code_widget->setBody( EditPasswordWidget() );
    $edit_auth_code_widget->setHref(base_url("widgettask/edit_password"));
    $edit_auth_code_widget = $edit_auth_code_widget->render();

    $top_bar_widget = new UIWidget("top_bar_widget");
    $top_bar_widget->setBody( TopBarWidget() );
    $top_bar_widget->setHref(base_url("widgettask/top_bar"));
    $top_bar_widget->setInlineFlg(true);
    $top_bar_widget = $top_bar_widget->render();


    $developer_tools_widget = new UIWidget("dev_tools_widget");
    $developer_tools_widget->setBody( TopBarWidget() );
    $developer_tools_widget->setHref(base_url("widgettask/developer_tools"));
    $developer_tools_widget = $developer_tools_widget->render();

    $status_hidden = "";
    if (trim($status_message) == '') $status_hidden = 'hidden';

    $banner = new EnterpriseBanner();


?><!DOCTYPE html>
<html data-template='Advice2Pay' csrf-cookie-name="<?=$csrf_cookie_name?>" csrf-token-name="<?=$csrf_token_name?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="<?=base_url();?>/assets/images/favicon.ico">
        <title>Advice2Pay Dashboard</title>


        <!-- Ladda buttons css -->
        <link href="<?=base_url();?>assets/plugins/ladda-buttons/css/ladda-themeless.min.css" rel="stylesheet" type="text/css" />

        <!-- Switches -->
        <link href="<?=base_url()?>assets/plugins/switchery/css/switchery.min.css" rel="stylesheet" />

        <link href="<?=base_url();?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/css/core.css" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/css/components.css" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/css/icons.css" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/css/pages.css" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/css/responsive.css" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/custom/css/styles.css" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/plugins/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
        <link href="<?=base_url();?>assets/plugins/footable/css/footable.core.css" rel="stylesheet">

        <link href="<?=base_url();?>assets/custom/css/site.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/custom/css/debug.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/custom/css/override.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/custom/css/overlay.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/custom/css/reports.css" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/custom/css/snapshots.css" rel="stylesheet" type="text/css" />
        <link href="<?=base_url();?>assets/custom/scss/styles.css<?=CachedQS()?>" rel="stylesheet" type="text/css" />



        <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

        <script src="<?=base_url();?>assets/js/modernizr.min.js"></script>
        <script type="text/javascript">
        //<![CDATA[
            base_url = '<?= base_url();?>';
        //]]>
        </script>


    </head>


    <body class="fixed-left">

        <?=$edit_profile_widget?>
        <?=$edit_auth_code_widget?>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- ========== Top Navbar Start ========== -->
            <?=$top_bar_widget?>

            <!-- ========== Left Sidebar Start ========== -->
            <?=$this->Menu_model->sidebar()->render()?>
            <!-- Left Sidebar End -->



            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="content-page <?=$banner->getPaddingClass();?>">
                <!-- Start content -->
                <div class="content">
                    <div class="container">
                        <div class="alert alert-danger <?=$alert_class?>" role="alert"><span class="alert-message"><?=$error_message?></span></div>
                        <?=RenderViewSTDOUT( $view, $view_array );?>
                    </div> <!-- container -->
                </div> <!-- content -->

                <footer class="footer text-right">
                    &copy; <?= date("Y") ?> Advice2Pay
                    <div class="pull-right text-muted">
                        <span class="<?=$status_hidden?>" id="background-task-status-message-container" data-companyid="<?=$company_id?>" data-companyparentid="<?=$companyparent_id?>">
                            <i class="fa fa-spin fa-cog"></i>
                            <span id="background-task-status-message"><?=$status_message?></span>
                        </span>
                        <?php
                        if ( IsDevelopment()  || IsUAT() )
                        {
                            // If we are in UAT, hide the icon.  That way if we demo, no one
                            // can see it.  However, I can use browser dev tools to remove the
                            // hidden class and gain access.
                            $icon_class = "";
                            if ( IsUAT() ) $icon_class = "hidden";

                            ?>
                            <span class="pull-right m-l-10 <?=$icon_class?>"><i class="glyphicon glyphicon-ice-lolly-tasted developer-tools"></i></span>
                            <?=$developer_tools_widget?>
                            <?=RenderViewAsString("ajax_panic", array());?>
                        <?php
                        } ?>
                    </div>
                </footer>

            </div>


            <!-- ============================================================== -->
            <!-- End Right content here -->
            <!-- ============================================================== -->


        </div>
        <div id="template_overlay" class="overlay" style="display:none;">
            <div class="centered">
                <div class="loading"></div>
                <div class="loading-text">loading</div>
            </div>
        </div>
        <!-- END wrapper -->

        <div class="clearfix"></div>

        <script>
            var resizefunc = [];
        </script>



        <!-- jQuery  -->
        <script src="<?=base_url();?>assets/js/jquery.min.js"></script>
        <script src="<?=base_url();?>assets/js/bootstrap.min.js"></script>
        <script src="<?=base_url();?>assets/js/detect.js"></script>
        <script src="<?=base_url();?>assets/js/fastclick.js"></script>
        <script src="<?=base_url();?>assets/js/jquery.slimscroll.js"></script>
        <script src="<?=base_url();?>assets/js/jquery.blockUI.js"></script>
        <script src="<?=base_url();?>assets/js/waves.js"></script>
        <script src="<?=base_url();?>assets/js/wow.min.js"></script>
        <script src="<?=base_url();?>assets/js/jquery.nicescroll.js"></script>
        <script src="<?=base_url();?>assets/js/jquery.scrollTo.min.js"></script>
        <script src="<?=base_url();?>assets/plugins/peity/jquery.peity.min.js"></script>
        <script src="<?=base_url();?>assets/plugins/waypoints/lib/jquery.waypoints.js"></script>
        <script src="<?=base_url();?>assets/plugins/counterup/jquery.counterup.min.js"></script>

        <!-- Switches! ( Must be before jquery.core.js ) -->
        <script src="<?=base_url()?>assets/plugins/switchery/js/switchery.min.js"></script>

        <script src="<?=base_url();?>assets/plugins/jquery-knob/jquery.knob.js"></script>

        <script src="<?=base_url();?>assets/js/jquery.core.js"></script>
        <script src="<?=base_url();?>assets/js/jquery.app.js"></script>

<!--
Add these back later once we are ready to add graphs.
We will need to pull these in with composer if possible, else drop them into the project.
        <script src="http://static.pureexample.com/js/flot/excanvas.min.js"></script>
		<script src="http://static.pureexample.com/js/flot/jquery.flot.min.js"></script>
		<script src="http://static.pureexample.com/js/flot/jquery.flot.pie.min.js"></script>
		<script src="https://rawgit.com/krzysu/flot.tooltip/master/js/jquery.flot.tooltip.js"></script>
		<script src='http://people.iola.dk/olau/flot/jquery.flot.stack.js'></script>

		<script type="text/javascript" src="/js/flot/jquery.flot.symbol.js"></script>
   		<script type="text/javascript" src="/js/flot/jquery.flot.axislabels.js"></script>
-->

		<script src="<?=base_url();?>assets/plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="<?=base_url();?>assets/plugins/datatables/dataTables.bootstrap.js"></script>

        <script src="<?=base_url();?>assets/plugins/datatables/dataTables.buttons.min.js"></script>
        <script src="<?=base_url();?>assets/plugins/datatables/buttons.bootstrap.min.js"></script>
        <script src="<?=base_url();?>assets/plugins/datatables/jszip.min.js"></script>
        <script src="<?=base_url();?>assets/plugins/datatables/vfs_fonts.js"></script>
        <script src="<?=base_url();?>assets/plugins/datatables/buttons.html5.min.js"></script>
        <script src="<?=base_url();?>assets/plugins/datatables/buttons.print.min.js"></script>

		<script src="<?=base_url();?>assets/plugins/datatables/dataTables.fixedHeader.min.js"></script>
        <script src="<?=base_url();?>assets/plugins/datatables/dataTables.keyTable.min.js"></script>
        <script src="<?=base_url();?>assets/plugins/datatables/dataTables.responsive.min.js"></script>
        <script src="<?=base_url();?>assets/plugins/datatables/responsive.bootstrap.min.js"></script>
        <script src="<?=base_url();?>assets/plugins/datatables/dataTables.scroller.min.js"></script>
        <script src="<?=base_url();?>assets/plugins/datatables/dataTables.colVis.js"></script>

        <!--FooTable -->
        <script src="<?=base_url();?>assets/plugins/footable/js/footable.all.min.js"></script>

		<!-- select2 ( text field with dropdown ) -->
        <script src="<?=base_url();?>assets/plugins/select2/js/select2.min.js" type="text/javascript"></script>

        <!-- Custom Box -->
        <link href="<?=base_url();?>assets/plugins/custombox/css/custombox.css<?=CachedQS()?>" rel="stylesheet">

        <!-- Modal-Effect -->
        <script src="<?=base_url();?>assets/plugins/custombox/js/custombox.min.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/plugins/custombox/js/legacy.min.js<?=CachedQS()?>"></script>

        <!-- Ladda -->
        <script src="<?=base_url();?>assets/plugins/ladda-buttons/js/spin.min.js<?=CachedQS()?>"></script>
        <script src="<?=base_url();?>assets/plugins/ladda-buttons/js/ladda.min.js<?=CachedQS()?>"></script>



        <!-- Input Mask -->
        <script src="<?=base_url()?>assets/plugins/bootstrap-inputmask/bootstrap-inputmask.min.js" type="text/javascript"></script>

        <?=RenderViewAsString('pusher/background_tasks');?>

        <!-- A2P -->
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/jquery.form.min.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/plugins/jquery-validation/js/jquery.validate.min.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/jquery-validate<?=vJqueryValidate()?>/additional-methods.min.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/plugins/jquery-ui/jquery-ui.min.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/app.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/app_behaviors.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/app_validators.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/utilities.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/settings/account.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/settings/password.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/jquery.cookie.js<?=CachedQS()?>"></script>
        <script type="text/javascript" src="<?=base_url("assets/custom/js/ui-components/_loader.js")?>"></script>
        <?=$custom_js?>
        <script type="text/javascript" src="<?=base_url()?>assets/custom/js/app_events.js<?=CachedQS()?>"></script>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.counter').counterUp({
                    delay: 100,
                    time: 1200
                });

                $(".knob").knob();

            });
        </script>


    </body>
</html>
