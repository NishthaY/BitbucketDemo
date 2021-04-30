<?php

    $whoami = CompanyDescription();
    $whowasi = WhowasiDescription();
    $display_name = GetSessionValue("display_name");


    $company_class = "";
    $actas_class = "hidden";

    if ( $whowasi != "" )
    {
        $company_class = "hidden";
        $actas_class = "";
    }

    $enterprise_banner = new EnterpriseBanner();

?>
<!-- Top Bar Start -->


<div class="topbar">

    <!-- Enterprise Banner -->
    <?=$enterprise_banner->render()?>

    <!-- LOGO -->
    <div class="topbar-left">
        <div class="text-center">
            <a href="<?=base_url();?>" class="logo"><img class='icon-c-logo' style='margin: 5px;' src='<?=base_url();?>assets/custom/images/a2p-logo.png' /><span><img src='<?=base_url();?>assets/custom/images/advice2pay-logo.png' /></span></a>
        </div>
    </div>

    <!-- Button mobile view to collapse sidebar menu -->
    <div class="navbar navbar-default" role="navigation">
        <div class="container">
            <div class="">
                <div class="pull-left">
                    <button class="button-menu-mobile open-left">
                        <i class="ion-navicon"></i>
                    </button>
                    <span class="clearfix"></span>
                </div>


                <ul class="nav navbar-nav navbar-right pull-right">
                    <?php if ( GetSessionValue("is_logged") == "TRUE" ) { ?>
                        <li class="dropdown">
                            <a href="" class="dropdown-toggle profile" data-toggle="dropdown" aria-expanded="true"><?=$display_name?>, <?=$whoami?> <i class='ion-arrow-down-b'></i></a>
                            <ul class="dropdown-menu">
                                <li class="<?=$actas_class?>" ><a href="<?=base_url("dashboard/changeback");?>"><i class="ion-arrow-return-left"></i> Change Back To <?=$whowasi?></a></li>
                                <li><a href="#" onclick="showForm('edit_account_form');" ><i class="ion-person m-r-5"></i> Profile</a></li>
                                <li><a href="#" onclick="showForm('edit_password_form');"><i class="ion-locked m-r-5"></i> Change Password</a></li>
                                <li><a href="<?=base_url('auth/logout')?>"><i class="ion-log-out m-r-5"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <!--/.nav-collapse -->
        </div>
    </div>
</div>
<!-- Top Bar End -->
