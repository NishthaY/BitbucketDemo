<?php

if ( ! isset($identifier) ) $identifier = "";
if ( ! isset($identifier_type) ) $identifier_type = "";
if ( ! isset($identifier_name) ) $identifier_name = "";
if ( ! isset($url_identifier) ) $url_identifier = "";
if ( ! isset($report_title) ) $report_title = "";
if ( ! isset($detail_widget) ) $detail_widget = "";
if ( ! isset($summary_widget) ) $summary_widget = "";
if ( ! isset($uri) ) $uri = "";
if ( ! isset($date_tag) ) $date_tag = "";
if ( ! isset($reports) ) $reports = array();
if ( ! isset($datetag_menu) ) $datetag_menu = array();

// If the date tag menu was not provided, then we will try and be helpful and
// use the report data.  If the report data has a 'description' and 'date_tag'
// fields, we will use those for the date tag menu.  This will result in one
// menu item per report.  In some cases, this is what we want.  In other cases
// we want a custom menu that show more things like a date range.
if ( empty($datetag_menu) )
{
    foreach($reports as $report) {
        $report_display = getArrayStringValue("description", $report);
        $report_date_tag = getArrayStringValue("date_tag", $report);
        $row = array();
        $row['description'] = $report_display;
        $row['date_tag'] = $report_date_tag;
        $datetag_menu[] = $row;
    }
}



?>
<div class="row">
    <div class="col-sm-9">
        <h4 class="page-title"><?=$report_title?></h4>

        <ol class="breadcrumb">
            <li class="breadcrumb-item active"><a href="<?=base_url("support/manage")?>">Support</a></li>
            <li class="breadcrumb-item"><a href="<?=base_url("{$uri}/{$url_identifier}/{$identifier}")?>"><?=$identifier_name?></a></li>
            <li class="clickable-header-breadcrumb">
                <span class='dropdown'>
                    <a href="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class=""><?=$date_tag?> <i class='ion-arrow-down-b'></i></span></a>
                    <ul class="dropdown-menu scrollable-menu-lg" style='margin-left: 0px;'>
                        <?php
                        if ( count($reports) == 0 ) {

                        }else{
                            foreach($datetag_menu as $item) {
                                $report_display = getArrayStringValue("description", $item);
                                $report_date_tag = getArrayStringValue("date_tag", $item);
                                print "<li><a href='".base_url("{$uri}/{$url_identifier}/{$identifier}/{$report_date_tag}")."'>{$report_display}</a></li>";
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
        <?=$detail_widget?>
    </div>
    <div class="col-lg-3">
        <?=$summary_widget?>
    </div>
</div>
