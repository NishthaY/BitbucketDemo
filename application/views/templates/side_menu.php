<?php
    if ( ! isset($menu) ) $menu = array();
    $banner = new EnterpriseBanner();
?>

<div class="left side-menu">
    <div class="sidebar-inner slimscrollleft <?=$banner->getPaddingClass();?>">
        <div id="sidebar-menu">
            <ul>
                <li class="text-muted menu-title">Navigation</li>
                <?php
                foreach($menu as $item) {

                    $href = getArrayStringValue("href", $item);
                    $title = getArrayStringValue("title", $item);
                    $short_desc = getArrayStringValue("short_desc", $item);
                    $selected = getArrayStringValue("selected", $item);
                    $disabled = getArrayStringValue("disabled", $item);
                    $is_child = getArrayStringValue("is_child", $item);
                    $icon_class = getArrayStringValue("icon_class", $item);

                    $active = "";
                    if( $selected == "TRUE" ) $active = "active";

                    $disabled_class = "";
                    if( $disabled == "TRUE" ) $disabled_class = "disabled";

                    $child_class = "";
                    if ( $is_child == "TRUE" ) $child_class = "child-item";

                    ?>
                    <li class="">
                        <a href="<?=$href?>" class="waves-effect <?=$active?>" <?=$disabled?>><i class="<?=$icon_class?>"></i> <span> <?=$title?> </span> </a>
                    </li>
                    <?php
                }
                ?>

            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
