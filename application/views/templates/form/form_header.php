<?php
    if ( ! isset($title) ) $title = "";
    if ( ! isset($links) ) $links = array();
    if ( ! isset($buttons) ) $buttons = array();
    if ( ! isset($widgets) ) $widgets = array();
?>
<div class="row form-header">
    <div class="col-sm-6">
        <h4 class="page-title"><?=$title?></h4>
        <?php
        if ( ! empty($links) )
        {
            ?><ol class="breadcrumb"><?php
            foreach($links as $link)
            {
                if ( isset($link['title']) )
                {
                    // If we have title in the link, then this is a dropdown full of links.

                    // Draw a dropdown link that will offer a selection of goodies.
                    $dropdown_links = $link['links'];
                    $selected = GetArrayStringValue('selected', $link);

                    // Make sure the selected text passed exists in the dropdown.
                    // If it doesn't, we will not show this dropdown.
                    $exists = false;
                    foreach($dropdown_links as $link)
                    {
                        $text = GetArrayStringValue('text', $link);
                        if ( $text === $selected ) $exists = true;
                    }
                    if ( ! $exists )
                    {
                        // Let's add it!
                        $item = array();
                        $item['text'] = $selected;
                        $item['url'] = 'IGNORE';
                        array_unshift($dropdown_links , $item);
                    }

                    ?>
                    <li class="clickable-header-breadcrumb">
                    <span class="dropdown">
                    <a href="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class=""><?=$selected?> <i class="ion-arrow-down-b"></i></span></a>
                    <ul class="dropdown-menu" style="margin-left: 0px;">
                    <?php
                    foreach($dropdown_links as $link)
                    {
                        $url = GetArrayStringValue('url', $link);
                        $text = GetArrayStringValue('text', $link);
                        if ( $url === 'IGNORE' ) continue;
                        ?>
                            <li><a href="<?=$url?>"><?=$text?></a></li>
                        <?php
                    }
                    ?>
                    </ul>
                    </span>
                    </li>
                    <?php

                }
                else
                {
                    // Draw a standard link that either does nor does not have a href.
                    $text = GetArrayStringValue('text', $link);
                    $url = GetArrayStringValue('url', $link);
                    if ( $url !== '' )
                    {
                        print "<li class='breadcrumb-item'><a href='{$url}'>{$text}</a></li>\n";
                    }
                    else
                    {
                        print "<li class='breadcrumb-item'>{$text}</li>\n";
                    }
                }

            }
            ?></ol><?php
        }
        ?>


    </div>
    <div class='col-sm-6' style='margin-top: 10px;'>
        <?php
            if ( ! empty($buttons) )
            {
                foreach($buttons as $button)
                {
                    $id = getArrayStringValue($button, "id");
                    $assoc_form_name = getArrayStringValue($button, "assoc_form_name");
                    $assoc_widget_name = replaceFor($assoc_form_name, "_form", "_widget");
                    $label = getArrayStringValue($button, "label");
                    $attributes = getArrayStringValue($button, "attributes");
                    $onclick = "refreshWidget('{$assoc_widget_name}', 'showForm', '{$assoc_form_name}');";
                    //$onclick = "showForm('{$assoc_form_name}');"
                    ?>
                    <button
                        id="<?=$id?>"
                        onclick="<?=$onclick?>"
                        type="button"
                        class="pull-right btn w-lg btn-default btn-lg waves-effect waves-light"
                        <?=$attributes?>
                    ><i class='ion-arrow-right-c'></i> <?=$label?></button>
                    <?php
                }
            }
        ?>
        <?php
            if ( ! empty($widgets) )
            {
                foreach($widgets as $widget)
                {
                    print $widget;
                    print "\n";
                }
            }
        ?>
    </div>
</div>
