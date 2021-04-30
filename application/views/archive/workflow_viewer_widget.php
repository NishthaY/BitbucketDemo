<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($type) ) $type = "";

    $workflows = $this->Workflow_model->get_workflows();
    $workflow_count = count($workflows);

    $disabled = "";
    if ( $workflow_count === 0 ) $disabled = " disabled ";

?>

<div class="row">
    <div class="col-sm-12">

        <div class="widget-bg-color-icon card-box fadeInDown animated">
            <div class="bg-icon bg-icon-a2p pull-left" style="margin-top: 25px; ">
                <i class="md md-layers text-info a2p-blue"></i>
            </div>
            <div class="text-right">
                <h3 class="text-dark"><b class=""><?=$workflow_count?></b></h3>
                <p class="text-muted mb-0">Total Workflows</p>
                <div class='pull-right'><a class="<?=$disabled?> btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("dashboard/workflow/parent_import_csv");?>">More <i class="ion-arrow-right-c"></i></a></div>
            </div>
            <div class="clearfix"></div>
        </div>

    </div>
</div>



