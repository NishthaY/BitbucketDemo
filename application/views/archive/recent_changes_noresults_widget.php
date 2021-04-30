<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($type) ) $type = "";
?>
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive "> <!-- dont forget to put back the hidden class -->
            <h4 class="m-t-0 header-title"><b>Recent Changes</b></h4>
            <BR>
            No recent changes available.
            <BR><BR>
            <div class='pull-right'><a class="btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("support/changes/{$type}/recent/{$id}");?>">More <i class="ion-arrow-right-c"></i></a></div>
        </div>
    </div>
</div>
