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

    $url_identifier = $identifier_type;
    if ( $url_identifier === 'companyparent' ) $url_identifier = 'parent';

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
                                print "<li><a href='".base_url("support/snapshots/{$url_identifier}/{$identifier}/{$report_tag}/{$snapshot_tag}")."'>{$display}</a></li>";
                            }
                        }
                        ?>
                    </ul>
                </span>
            </li>
            <li class="clickable-header-breadcrumb">
                <span class='dropdown'>
                    <a href="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class=""><?=$selected_label?> <i class='ion-arrow-down-b'></i></span></a>
                    <ul class="dropdown-menu" style='margin-left: 0px;'>
                        <?php
                        if ( count($snapshots) == 0 ) {

                        }else{
                            foreach($snapshots as $snapshot) {
                                $display = getArrayStringValue("description", $snapshot);
                                $tag = getArrayStringValue("tag", $snapshot);
                                print "<li><a href='".base_url("support/snapshots/{$url_identifier}/{$identifier}/{$date_tag}/{$tag}")."'>{$display}</a></li>";
                            }
                        }
                        ?>
                    </ul>
                </span>
            </li>
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
        $view_array['uri'] = 'support/snapshots/TYPE/ID';
        echo RenderViewAsString('archive/support_widget', $view_array);
        ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        Review the user supplied data and user elections for a particular workflow run.
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
            class="btn w-lg btn-white btn-lg waves-effect waves-light m-r-15"
            data-href="<?=base_url("support/snapshots/download/{$url_identifier}/{$snapshot_tag}/{$identifier}/{$date_tag}");?>"
        ><i class='glyphicon glyphicon-download-alt m-r-5'></i> Snapshot File</button>
    </div>
</div>
<div class="row m-t-15">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <?=RenderViewAsString('archive/snapshot_list_row', array('list' => $list));?>

                <div class="card-box <?=$data_no_results_class?>"><div><h4 class="m-t-0 header-title"><b>Snapshot Data</b></h4>No results found.</div></div>
                <!-- Draft Reports -->
                <div class="card-box <?=$data_class?>">
                    <h4 class="m-t-0 header-title"><b>Snapshot Data</b></h4>
                    <div>

                        <table id="snapshot_accordion" class="table m-b-0 toggle-arrow-tiny"  data-page-size="10">
                            <thead>
                                <tr class="">
                                    <?php
                                    $col_count = 0;
                                    $temp = "data-toggle='true'";
                                    if ( $default_columns == 0 ) $temp = "";
                                    foreach($headings as $item) {
                                        print "<th {$temp}>".ucwords($item)."</th>\n";
                                        $col_count++;
                                        if ( $default_columns != 0 && $col_count >= $default_columns )
                                        {
                                            $temp = "data-hide='all'";
                                        }
                                    } ?>
                                </tr>
                            </thead>
                            <div class="form-inline m-b-20">
                            	<div class="row">
                            		<div class="col-sm-6 text-xs-center">
                                        <label class="form-inline">Show
										<select id="demo-show-entries" class="form-control input-sm">
											<option value="5">5</option>
											<option value="10" selected>10</option>
											<option value="15">15</option>
											<option value="20">20</option>
										</select>
										entries
									</label>
                            		</div>
                            		<div class="col-sm-6 text-xs-center text-right">
                            			<div class="form-group">
                            				<input id="snapshot_search" type="text" placeholder="Search" class="form-control input-sm" autocomplete="on">
                            			</div>
                            		</div>
                            	</div>
                            </div>
                            <tbody>
                                <?php
                                foreach($data as $item)
                                {
                                    $view_array = array();
                                    $view_array['headings'] = $headings;
                                    $view_array['row'] = $item;
                                    RenderViewSTDOUT("archive/snapshots_row", $view_array);
                                }
                                ?>
                            </tbody>
                            <tfoot>
								<tr>
									<td colspan="<?=count($headings);?>">
										<div class="text-right">
                                            <span class="pull-left p-t-20"><small><?=$audit?></small></span>
											<ul class="pagination pagination-split m-t-30 m-b-0"></ul>
										</div>
									</td>
								</tr>
							</tfoot>
                        </table>
                    </div>
                </div>


            </div> <!-- end Col-9 -->
        </div><!-- End row -->
    </div>
</div>
