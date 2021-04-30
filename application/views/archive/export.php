<?php

if ( ! isset($title) ) $title = "";
if ( ! isset($identifier) ) $identifier = "";
if ( ! isset($identifier_type) ) $identifier_type = "";
if ( ! isset($identifier_name) ) $identifier_name = "";
if ( ! isset($url_identifier) ) $url_identifier = "";
if ( ! isset($uri) ) $uri = "";

if ( ! isset($manage_widget) ) $manage_widget = "";
if ( ! isset($create_widget) ) $create_widget = "";
if ( ! isset($confirm_widget) ) $confirm_widget = "";
if ( ! isset($background_task) ) $background_task = "";


?>
<div class="row">
    <div class="col-sm-9">
        <h4 class="page-title"><?=$title?></h4>

        <ol class="breadcrumb">
            <li class="breadcrumb-item active"><a href="<?=base_url("support/manage")?>">Support</a></li>
            <li class="breadcrumb-item"><a href="<?=base_url("{$uri}/{$url_identifier}/{$identifier}")?>"><?=$identifier_name?></a></li>
        </ol>

    </div>
    <div class="col-sm-3">
        <?php
        $view_array = array();
        $view_array['selected_id'] = $identifier;
        $identifier_type === 'companyparent' ? $view_array['selected_type'] = 'parent' : $view_array['selected_type'] = $identifier_type;
        $view_array['company_parent_flg'] = true;
        $view_array['company_flg'] = true;
        $view_array['uri'] = 'support/exports/TYPE/ID';
        echo RenderViewAsString('archive/support_widget', $view_array);
        ?>
    </div>
</div>
<div class="row">
    <div class="col-lg-9">
        <?=$manage_widget?>
    </div>
    <div class="col-lg-3">
        <?=$create_widget?>
    </div>
</div>
<?=$confirm_widget?>
<?=$background_task?>
