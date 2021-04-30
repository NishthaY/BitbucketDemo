<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($title) ) $title = "";
?>
<div class="card-box">
    <h4 class="m-t-0 m-b-20 header-title"><b><?=$title?></b></h4>
    <p></p>
    <div class="list-settings">
        <ul class="list-unstyled transaction-list m-r-5">
            <?php
            if ( ! empty($data) )
            {
                foreach($data as $item)
                {
                    $icon = GetArrayStringValue('icon', $item);
                    $text = GetArrayStringValue('text', $item);
                    $value = "";
                    if ( isset($item['value'] ) ) $value = $item['value'];

                    if ( is_array($value) )
                    {
                        // Write out the title.
                        $view_array = array();
                        $view_array['text'] = $text;
                        $view_array['is_parent'] = true;
                        RenderViewSTDOUT('settings/settings_widget_row', $view_array);

                        $parent_title = $text;
                        foreach($value as $child)
                        {
                            $icon = GetArrayStringValue('icon', $child);
                            $text = GetArrayStringValue('text', $child);
                            $value = GetArrayStringValue('value', $child);

                            $view_array = array();
                            //$view_array['icon'] = $icon;
                            $view_array['text'] = $text;
                            $view_array['value'] = $value;
                            $view_array['parent_title'] = $parent_title;
                            $view_array['is_parent'] = false;
                            RenderViewSTDOUT('settings/settings_widget_row', $view_array);
                        }
                    }
                    else
                    {
                        $view_array = array();
                        //$view_array['icon'] = $icon;
                        $view_array['text'] = $text;
                        $view_array['value'] = $value;
                        $view_array['is_parent'] = false;
                        $view_array['parent_title'] = "";
                        RenderViewSTDOUT('settings/settings_widget_row', $view_array);
                    }
                }
            }
            else
            {
                ?>No results found.<?php
            }
            ?>
        </ul>
    </div>
</div>
