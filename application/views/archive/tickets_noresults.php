<?php
    if ( ! isset($json) ) $json = json_encode("{}");
    if ( ! isset($identifier) ) $identifier = "";
    if ( ! isset($identifier_type) ) $identifier_type = "";
    if ( ! isset($metadata) ) $metadata = array();
    if ( ! isset($list) ) $list = array();
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($company_name) ) $company_name = "";
    if ( ! isset($snapshot_tag) ) $snapshot_tag = "";
    if ( ! isset($snapshots) ) $snapshots = array();
    if ( ! isset($source_upload) ) $source_upload = "";
    if ( ! isset($reports) ) $reports = array();
    if ( ! isset($more_info_form) ) $more_info_form = "";
    if ( ! isset($critical_error_available) ) $critical_error_available = false;

    if ( $identifier_type === 'company' ) $url_identifier = 'company';
    if ( $identifier_type === 'companyparent' ) $url_identifier = 'parent';

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

    // Disable the critical error button if we don't have any data.
    $error_disabled = " disabled ";
    if ( $critical_error_available ) $error_disabled = "";

    // Color the button if disabled or not.
    $error_class = "btn-working";
    if ( $critical_error_available ) $error_class = "btn-white";

    // Do we have permission to download files?
    IsAuthenticated('pii_download', 'company', $company_id) ? $downloads_disabled = "" : $downloads_disabled = " disabled ";

?>
<div class="row">
    <div class="col-sm-9">
        <h4 class="page-title">
        <ol class="breadcrumb">
            <li class="clickable-header-breadcrumb" data-href="<?=base_url("support/manage/{$company_id}")?>">Support</li>
            <li class="" data-href="">Tickets</li>
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

                                $timestamp = strtotime($report_tag);
                                $date = date('Y-m-d', $timestamp);
                                $datetime1 = date_create($date);
                                $datetime2 = date_create();
                                $interval = date_diff($datetime1, $datetime2);
                                $age =  $interval->format('%R%a days');
                                $display = $display . " ({$age})";
                                print "<li><a href='".base_url("support/tickets/{$url_identifier}/{$identifier}/{$report_tag}/{$snapshot_tag}")."'>{$display}</a></li>";
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
        <?php
        $view_array = array();
        $view_array['selected_id'] = $identifier;
        $identifier_type === 'companyparent' ? $view_array['selected_type'] = 'parent' : $view_array['selected_type'] = $identifier_type;
        $view_array['company_parent_flg'] = true;
        $view_array['company_flg'] = true;
        $view_array['uri'] = 'support/tickets/TYPE/ID';
        echo RenderViewAsString('archive/support_widget', $view_array);
        ?>
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
                data-href="<?=base_url("support/archive/download/source/{$url_identifier}/{$identifier}/{$date_tag}");?>"
        ><i class='glyphicon glyphicon-download-alt m-r-5'></i> Original Upload</button>
        <button
                id="download_source"
                type="button"
                class="btn w-lg <?=$source_class?> btn-lg waves-effect waves-light"
            <?=$source_disabled?>
                data-href="<?=base_url("support/archive/download/encrypted/{$url_identifier}/{$identifier}/{$date_tag}");?>"
        ><i class='glyphicon glyphicon-download-alt m-r-5'></i> Encrypted Upload</button>
        <button
            <?=$downloads_disabled?>
            id="download_snapshot"
            type="button"
            class="btn w-lg btn-working disabled btn-lg waves-effect waves-light"
            data-href="<?=base_url("company/archive/download/snapshot/{$snapshot_tag}/{$company_id}/{$date_tag}");?>"
        ><i class='glyphicon glyphicon-download-alt m-r-5'></i> Snapshot File</button>
        <button
                id="additional_information"
                type="button"
                class="btn w-lg <?=$error_class?> btn-lg waves-effect waves-light m-r-15"
            <?=$error_disabled?>
        ><i class='glyphicon glyphicon-eye-open m-r-5'></i>Critical Error</button>
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
<?=$more_info_form?>
