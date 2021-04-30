<?php

if ( ! isset($company_id) ) $company_id = "";
if ( ! isset($company_name) ) $company_name = "";
if ( ! isset($timers_widget) ) $timers_widget = "";
if ( ! isset($summary_widget) ) $summary_widget = "";
if ( ! isset($reports) ) $reports = array();
if ( ! isset($date_tag) ) $date_tag = "";


?>
<div class="row">
    <div class="col-sm-9">
        <h4 class="page-title">Support Timers</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item active"><a href="<?=base_url("support/manage")?>">Support</a></li>
            <li class="breadcrumb-item"><a href="<?=base_url("support/manage/company/{$company_id}")?>"><?=$company_name?></a></li>
            <li class="clickable-header-breadcrumb">
                <span class='dropdown'>
                    <a href="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class=""><?=$date_tag?> <i class='ion-arrow-down-b'></i></span></a>
                    <ul class="dropdown-menu" style='margin-left: 0px;'>
                        <?php
                        if ( count($reports) == 0 ) {

                        }else{
                            foreach($reports as $report) {
                                $display = getArrayStringValue("description", $report);
                                $report_tag = getArrayStringValue("date_tag", $report);
                                print "<li><a href='".base_url("support/timers/company/{$company_id}/{$report_tag}")."'>{$display}</a></li>";
                            }
                        }
                        ?>
                    </ul>
                </span>
            </li>
        </ol>
    </div>
    <div class="col-sm-3">
    </div>
</div>
<div class="row">
    <div class="col-lg-9">
        <?=$timers_widget?>
    </div>
    <div class="col-lg-3">
        <?=$summary_widget?>
    </div>
</div>
