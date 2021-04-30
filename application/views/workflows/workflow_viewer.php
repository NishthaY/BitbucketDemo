<?php
    if ( ! isset($workflow_name) ) $workflow_name = "";
    if ( ! isset($workflow_steps) ) $workflow_steps = array();
    if ( ! isset($workflow_properties) ) $workflow_properties = array();
    if ( ! isset($wf_menu) ) $wf_menu = array();
    if ( ! isset($selected) ) $selected = array();
    if ( ! isset($sample_widget) ) $sample_widget = "";

?>





<?php
if ( ! isset($header_html) ) $header_html = "";
?>
<div class="row">
    <div class="col-sm-9">
        <h4 class="page-title">Workflow Viewer</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item active"><a href="<?=base_url("support/manage")?>">Support</a></li>

            <li class="clickable-header-breadcrumb">
                <span class="dropdown">
                    <a href="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class=""><?=GetArrayStringValue('Description', $selected);?> <i class="ion-arrow-down-b"></i></span></a>
                    <ul class="dropdown-menu scrollable-menu" style="margin-left: 0px;">
                        <?php
                        foreach($wf_menu as $menu_item)
                        {
                            $link = GetArrayStringValue('Link', $menu_item);
                            $label = GetArrayStringValue('Description', $menu_item);
                            ?>
                            <li><a href="<?=$link?>"><?=$label?></a></li>
                            <?php
                        }
                        ?>
                    </ul>
                </span>
            </li>


        </ol>
    </div>
    <div class="col-sm-3">
        <?=$sample_widget?>
    </div>
</div>


<div class="row">
    <div class="col-md-9">
        <section id="cd-timeline" class="cd-container">
            <div class="cd-timeline-block">


                <?php
                $step_number = 0;
                foreach($workflow_steps as $workflow_step)
                {
                    $step_number++;
                    $step_name = getArrayStringValue("step_name", $workflow_step);
                    $description = getArrayStringValue("step_description", $workflow_step);
                    array_key_exists('properties', $workflow_step) ? $properties = $workflow_step['properties'] : $properties = [];
                    ?>

                    <div class="cd-timeline-block">
                        <div class="cd-timeline-img cd-a2p">
                            <i class="fa fa-star"></i>
                        </div> <!-- cd-timeline-img -->

                        <div class="cd-timeline-content">
                            <h4 class="text-uppercase font-18 font-600 text-center"><?=$step_name?></h4>
                            <p class="text-muted font-13 m-b-30"><?=$description?></p>
                            <hr>
                            <div class="" style="overflow: hidden; outline: none;">
                                <ul class="list-unstyled transaction-list m-r-5">

                                    <?php
                                    foreach($properties as $property)
                                    {
                                        $pName = GetArrayStringValue('name', $property);
                                        $pValue = GetArrayStringValue('value', $property);
                                        $pDesc = GetArrayStringValue('desc', $property);
                                        ?>
                                        <li>
                                            <div class="wf-property-row pointer">
                                                <i class="fa fa-circle-o"></i>
                                                <span class="tran-text"><strong><?=$pName?></strong></span>
                                                <span class="pull-right text-muted"><?=$pValue?></span>
                                                <span class="clearfix"></span>
                                            </div>
                                            <div class="wf-property-row-details hidden"><?=$pDesc?></div>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                            <span class="cd-date">Step #<?=$step_number?></span>
                        </div> <!-- cd-timeline-content -->
                    </div> <!-- cd-timeline-block -->

                    <?php
                }
                ?>






        </section> <!-- cd-timeline -->
    </div>
    <div class="col-md-3">

        <div class="card-box">
            <h4 class="m-t-0 m-b-20 header-title"><b>Workflow Properties</b></h4>

            <div class="" style="overflow: hidden; outline: none;">
                <ul class="list-unstyled transaction-list m-r-5">

                    <?php
                    foreach($workflow_properties as $workflow_property)
                    {
                        $pName = GetArrayStringValue('Name', $workflow_property);
                        $pValue = GetArrayStringValue('Value', $workflow_property);
                        $pDesc = GetArrayStringValue('Description', $workflow_property);
                        ?>
                        <li>
                            <div class="wf-property-row pointer">
                                <i class="fa fa-circle-o"></i>
                                <span class="tran-text"><strong><?=$pName?></strong></span>
                                <span class="pull-right text-muted"><?=$pValue?></span>
                                <span class="clearfix"></span>
                            </div>
                            <div class="wf-property-row-details hidden"><?=$pDesc?></div>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        </div>







    </div>
</div><!-- Row -->


