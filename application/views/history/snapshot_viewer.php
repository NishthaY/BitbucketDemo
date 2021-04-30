<?php
    if ( ! isset($json) ) $json = json_encode("{}");
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($company_name) ) $company_name = "";
    if ( ! isset($snapshot_tag) ) $snapshot_tag = "";
    if ( ! isset($snapshots) ) $snapshots = array();
    if ( ! isset($source_upload) ) $source_upload = "";

    // Disable the source upload button if we don't have
    // the source on file.
    $source_disabled = " disabled ";
    if ( $source_upload != "" ) $source_disabled = "";

    // Color the button if disabled or not.
    $source_class = "btn-working";
    if ( $source_upload != "" ) $source_class = "btn-default";


    // Is a snapshot selected?  If so, pull the label from our collection.
    $selected_label = "Snapshots";
    if ( getStringValue($snapshot_tag) != "" ) {
        if ( isset($snapshots[$snapshot_tag]) ) {
            $selected_label = getArrayStringValue("description", $snapshots[$snapshot_tag]);
        }
    }

    $headings = array();
    $data = json_decode($json, true);
    if ( ! empty($data) )
    {
        $first = $data[0];
        $headings = array_keys($first);
    }

    // Do we have permission to download files?
    IsAuthenticated('pii_download', 'company', $company_id) ? $downloads_disabled = "" : $downloads_disabled = " disabled ";

?>
<div class="row">
    <div class="col-sm-6">
        <h4 class="page-title">
        <ol class="breadcrumb">
            <li class=""><a href='<?=base_url("companies/manage")?>'>Companies</a></li>
            <li class=""><a href='<?=base_url("companies/snapshot/{$company_id}")?>'><?=$company_name?></a></li>
            <li class="">
                <span class='dropdown'>
                    <a href="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class=""><?=$selected_label?> <i class='ion-arrow-down-b'></i></span></a>
                    <ul class="dropdown-menu" style='margin-left: 0px;'>
                        <?php
                        if ( count($snapshots) == 0 ) {

                        }else{
                            foreach($snapshots as $snapshot) {
                                $display = getArrayStringValue("description", $snapshot);
                                $tag = getArrayStringValue("tag", $snapshot);
                                print "<li><a href='".base_url("company/history/viewer/{$company_id}/{$date_tag}/{$tag}")."'>{$display}</a></li>";
                            }
                        }
                        ?>
                    </ul>
                </span>
            </li>
        </ol>
        </h4>
    </div>
    <div class="col-sm-6" style='margin-top: 10px;'>
        <button
            <?=$downloads_disabled?>
            id="download_source"
            type="button"
            class="pull-right btn w-lg <?=$source_class?> btn-lg waves-effect waves-light"
            <?=$source_disabled?>
            data-href="<?=base_url("company/history/download/source/{$company_id}/{$date_tag}");?>"
        ><i class='glyphicon glyphicon-download-alt m-r-5'></i> Original Upload</button>
        <button
            <?=$downloads_disabled?>
            id="download_snapshot"
            type="button"
            class="pull-right btn w-lg btn-white btn-lg waves-effect waves-light m-r-15"
            data-href="<?=base_url("company/history/download/snapshot/{$snapshot_tag}/{$company_id}/{$date_tag}");?>"
        ><i class='glyphicon glyphicon-download-alt m-r-5'></i> Snapshot File</button>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-lg-12 col-md-12">

                <!-- Draft Reports -->
                <div class="card-box table-responsive">
                    <div>
                        <table id="snapshot_table" class="table snapshot table-hover m-0">
                            <thead>
                                <tr class="">
                                    <?php foreach($headings as $item) { print "<th>".ucwords($item)."</th>\n"; } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach($data as $item)
                                {
                                    $view_array = array();
                                    $view_array['headings'] = $headings;
                                    $view_array['row'] = $item;
                                    RenderViewSTDOUT("history/snapshot_viewer_row", $view_array);
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div> <!-- end Col-9 -->
        </div><!-- End row -->
    </div>
</div>
