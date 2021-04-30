<?php
    if ( ! isset($icon) ) $icon = "<i class=\"fa fa-circle-thin\"></i>";
    if ( ! isset($text) ) $text = "";
    if ( ! isset($value) ) $value = "";
    if ( ! isset($parent_title) ) $parent_title = "";
    if ( ! isset($is_parent) ) $is_parent = false;
?>

<?php
if ( ! $is_parent && $parent_title === '' )
{
    ?>
    <li class="settings-widget-row">
        <span class="tran-text"><?=$text?></span>
        <span class="pull-right text-muted"><?=$value?></span>
        <span class="clearfix"></span>
    </li>
    <?php
}
else if ( $is_parent )
{
    ?>
    <li class="settings-widget-row-parent">
        <span class="tran-text setting-title"><?=$text?></span>
        <a class="btn-xs btn-white wave-effects pull-right settings-widget-button"><span class="fa fa-chevron-circle-down"></span><span> More</span></a>
        <a class="btn-xs btn-white waves-effect pull-right settings-widget-button hidden"><span class="fa fa-chevron-circle-up"></span><span> Less</span></a>
        <span class="clearfix"></span>
    </li>
    <?php
}
else if ( $parent_title !== '' )
{
    ?>
    <li class="settings-widget-row-child hidden" data-parent="<?=$parent_title?>">
        <span class="tran-text"><?=$text?></span>
        <span class="pull-right text-muted"><?=$value?></span>
        <span class="clearfix"></span>
    </li>
    <?php
}
?>


