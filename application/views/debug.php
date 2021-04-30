<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($title) ) $title = "";

    $output = print_r($data, true);
?>
<link href="<?=base_url();?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?=base_url();?>assets/css/core.css" rel="stylesheet" type="text/css" />
<link href="<?=base_url();?>assets/css/icons.css" rel="stylesheet" type="text/css" />
<link href="<?=base_url();?>assets/css/pages.css" rel="stylesheet" type="text/css" />
<ul class="sortable-list taskList list-unstyled ui-sortable" id="upcoming">
    <li class="ui-sortable-handle" style="border-left-color: #00aeef;">
        <pre><?=$output?></pre>
        <div class="m-t-20">
            <p class="pull-right m-b-0"><i class="fa fa-clock-o"></i> <span title="<?=date('m/d/Y h:i A')?>"><?=date('m/d/Y')?></span></p>
            <p class="m-b-0"><a href="" class="text-muted"><img class="icon-c-logo thumb-sm rounded-circle m-r-10" src="<?=base_url('assets/custom/images/a2p-logo.png')?>"> <span class="font-bold"><?=$title?></span></a> </p>
        </div>
    </li>
</ul>