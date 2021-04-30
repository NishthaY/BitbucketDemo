<?php
    if ( ! isset($description) ) $description = "";
    if ( ! isset($is_hidden) ) $is_hidden = true;
    if ( ! isset($closable) ) $closable = true;

    $hidden_tag = "";
    if ( $is_hidden ) $hidden_tag = " hidden ";

    $close_hidden_tag = " hidden ";
    if ( $closable ) $close_hidden_tag = "";

?>
<div class="enterprise-banner <?=$hidden_tag?> text-center" data-href="<?=base_url('settings/banner/deactivate')?>">
    <button type="button" class="close <?=$close_hidden_tag?>" onclick="EnterpriseBannerHide();">
        <span>×</span><span class="sr-only">Close</span>
    </button>
    <div class="enterprise-text"><?=$description?></div>
</div>