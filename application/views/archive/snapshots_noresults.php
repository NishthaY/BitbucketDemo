<?php
    if ( ! isset($json) ) $json = json_encode("{}");
    if ( ! isset($metadata) ) $metadata = array();
    if ( ! isset($list) ) $list = array();
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($company_name) ) $company_name = "";
    if ( ! isset($snapshot_tag) ) $snapshot_tag = "";
    if ( ! isset($snapshots) ) $snapshots = array();
    if ( ! isset($source_upload) ) $source_upload = "";
    if ( ! isset($reports) ) $reports = array();

    // Disable the source upload button if we don't have
    // the source on file.
    $source_disabled = " disabled ";
    if ( $source_upload != "" ) $source_disabled = "";

    // Color the button if disabled or not.
    $source_class = "btn-working";
    if ( $source_upload != "" ) $source_class = "btn-white";

    // Is a snapshot selected?  If so, pull the label from our collection.
    $selected_label = "Snapshots";
    if ( getStringValue($snapshot_tag) != "" ) {
        if ( isset($snapshots[$snapshot_tag]) ) {
            $selected_label = getArrayStringValue("description", $snapshots[$snapshot_tag]);
        }
    }

    // Decode our data and collect the table HEADINGS for display.
    $headings = array();
    $data = json_decode($json, true);
    if ( ! empty($data) && count($data) > 0  )
    {
        $first = $data[0];
        $headings = array_keys($first);
    }
    // Should we rollup our data for display?
    $default_columns = 0;
    if ( !empty($metadata) ) $default_columns = getArrayIntValue("rollup", $metadata);

    // Who did this snapshot?
    $audit = "";
    if ( ! empty($metadata['user']) )
    {
        $audit = "Created by ";
        $audit .= getArrayStringValue("first_name", $metadata['user']);
        $audit .= " ";
        $audit .= getArrayStringValue("last_name", $metadata['user']);
        $audit .= " @ ";
        $audit .= getArrayStringValue("timestamp", $metadata);
    }

    // Do we have a list to go along with the table?
    $list_class = "hidden";
    if ( count($list) > 0 ) $list_class = "";

    // Do we have data?
    $data_class = "hidden";
    $data_no_results_class = "";
    if ( count($data) > 0 ) {
        $data_class = "";
        $data_no_results_class = "hidden";
    }

    // Do we have permission to download files?
    IsAuthenticated('pii_download', 'company', $company_id) ? $downloads_disabled = "" : $downloads_disabled = " disabled ";

?>
<div class="row">
    <div class="col-sm-9">
        <h4 class="page-title">
        <ol class="breadcrumb">
            <li class="clickable-header-breadcrumb" data-href="<?=base_url("support/manage/{$company_id}")?>">Support</li>
            <li class="" data-href="">Snapshots</li>
            <?php
            if ( ! empty($reports) )
            {
            ?>
                <li class="clickable-header-breadcrumb">
                    <span class='dropdown'>
                        <a href="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class=""><?=$date_tag?> <i class='ion-arrow-down-b'></i></span></a>
                        <ul class="dropdown-menu" style='margin-left: 0px;'>
                            <?php
                            foreach($reports as $report) {
                                $display = getArrayStringValue("description", $report);
                                $report_tag = getArrayStringValue("date_tag", $report);
                                print "<li><a href='".base_url("support/snapshots/company/{$company_id}/{$report_tag}/{$snapshot_tag}")."'>{$display}</a></li>";
                            }
                            ?>
                        </ul>
                    </span>
                </li>
            <?php
            }
            ?>
        </ol>
        </h4>
    </div>
    <div class="col-sm-3" style='margin-top: 10px;'>
        <?=RenderViewAsString("archive/support_widget", array(
                "selected_id" => $company_id
                , "selected_type" => "company"
                , "company_parent_flg" => false
                , "uri" => "support/snapshots/company/ID"
            )
        );?>
    </div>
</div>
<div class="row">
    <div class="col-sm-12" style='margin-top: 10px;'>
        <button
            <?=$downloads_disabled?>
            id="download_source"
            type="button"
            class="btn w-lg <?=$source_class?> btn-lg waves-effect waves-light"
            <?=$source_disabled?>
            data-href="<?=base_url("company/archive/download/source/{$company_id}/{$date_tag}");?>"
        ><i class='glyphicon glyphicon-download-alt m-r-5'></i> Original Upload</button>
        <button
            <?=$downloads_disabled?>
            id="download_snapshot"
            type="button"
            class="btn w-lg btn-working disabled btn-lg waves-effect waves-light m-r-15"
            data-href="<?=base_url("company/archive/download/snapshot/{$snapshot_tag}/{$company_id}/{$date_tag}");?>"
        ><i class='glyphicon glyphicon-download-alt m-r-5'></i> Snapshot File</button>
    </div>
</div>
<div class="row m-t-15">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card-box table-responsive">
                    <div>
                        <br>
                        No snapshots found.
                        <br><br>
                    </div>
                </div>
            </div>
        </div><!-- End row -->
    </div>
</div>
