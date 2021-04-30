<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($company_name) ) $company_name = "";
    if ( ! isset($company_parent_id) ) $company_parent_id = "";
    if ( ! isset($company_parent_name) ) $company_parent_name = "";
    if ( ! isset($selected_view) ) $view = "all";
    if ( ! isset($views) ) $views = array();

    // Decode our data and collect the table HEADINGS for display.
    $headings = array();
    if ( ! empty($data) && count($data) > 0  )
    {
        $first = $data[0];
        $headings = array_keys($first);
    }

    // How many columns do we show before we rollup?
    $default_columns = 3;

    $selected_label = "Unknown";
    if ( ! empty($views[$selected_view]) )
    {
        $selected_label = getArrayStringValue("description", $views[$selected_view]);
    }

    $type = "company";
    $id = $company_id;
    $name = $company_name;
    if ( getStringValue($company_id) == "" ) {
        $id = $company_parent_id;
        $type = "parent";
        $name = $company_parent_name;
    }


?>
<div class="row">
    <div class="col-sm-9">
        <h4 class="page-title">
        <ol class="breadcrumb m-t-0 p-t-0">
            <li class="clickable-header-breadcrumb" data-href="<?=base_url("support/manage/{$type}/{$id}");?>">Support</li>
            <li class="clickable-header-breadcrumb">
                <span class='dropdown'>
                    <a href="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class=""><?=$selected_label?> <i class='ion-arrow-down-b'></i></span></a>
                    <ul class="dropdown-menu" style='margin-left: 0px;'>
                        <?php
                        if ( count($views) == 0 ) {

                        }else{
                            foreach($views as $item) {
                                $display = getArrayStringValue("description", $item);
                                $tag = getArrayStringValue("tag", $item);
                                print "<li><a href='".base_url("support/changes/{$type}/{$tag}/{$id}")."'>{$display}</a></li>";
                            }
                        }
                        ?>
                    </ul>
                </span>
            </li>
        </ol>
        </h4>
    </div>
    <div class="col-sm-3">
        <div>
            <?=RenderViewAsString("archive/support_widget", array( "selected_id" => $id, "selected_type" => $type, "uri" => "support/changes/TYPE/{$selected_view}/ID"));?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        Audit trail of data changes and interactions with the system.
    </div>
</div>
<div class="row m-t-15">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card-box">
                    <div>
                        <table id="audit_accordion" class="table m-b-0 toggle-arrow-tiny"  data-page-size="10">
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
                            				<input id="audit_search" type="text" placeholder="Search" class="form-control input-sm" autocomplete="on">
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
                                    RenderViewSTDOUT("archive/changes_row", $view_array);
                                }
                                ?>
                            </tbody>
                            <tfoot>
								<tr>
									<td colspan="<?=count($headings);?>">
										<div class="text-right">
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
