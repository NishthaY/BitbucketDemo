<?php
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($company_name) ) $company_name = "";
    if ( ! isset($company_parent_id) ) $company_parent_id = "";
    if ( ! isset($company_parent_name) ) $company_parent_name = "";
    if ( ! isset($selected_view) ) $view = "recent";
    if ( ! isset($views) ) $views = array();


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
        <ol class="breadcrumb">
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
        <div class="row">
            <div class="col-lg-12 col-md-12">

                <!-- Draft Reports -->
                <div class="card-box table-responsive">
                    <div>
                        <br>
                        No recent changes found.
                        <br><br>
                    </div>
                </div>


            </div> <!-- end Col-9 -->
        </div><!-- End row -->
    </div>
</div>
