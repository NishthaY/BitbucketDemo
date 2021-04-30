<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($type) ) $type = "";

    $button_class = '';
    if ( $type == 'company' && GetIntValue($id) === A2P_COMPANY_ID )
    {
        $button_class = 'hidden';
    }
?>
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive "> <!-- dont forget to put back the hidden class -->
            <h4 class="m-t-0 header-title"><b>Recent Exports</b></h4>
            <BR>
            No recent exports available.
            <div class='pull-right <?=$button_class?>'><a class="btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("support/exports/{$type}/{$id}");?>">Create <i class="ion-arrow-right-c"></i></a></div>
        </div>
    </div>
</div>
