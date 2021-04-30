<?php
    if ( ! isset($list) ) $list = array();
    if ( ! isset($button_size) ) $button_size = "btn";
    if ( ! isset($button_color) ) $button_color = "btn-primary";
    if ( ! isset($button_color_offset) ) $button_color_offset = "btn-offset-primary";
    if ( ! isset($button_type) ) $button_type = "button";
    if ( ! isset($attributes) ) $attributes = "";
    if ( ! isset($selected) ) $selected = "";
    if ( ! isset($id) ) $id = RandomString();
    if ( ! isset($is_hidden) ) $is_hidden = false;
    if ( ! isset($classes) ) $classes = "";
    if ( ! isset($callback_onclick) ) $callback_onclick = "";
    if ( ! isset($success_label) ) $success_label = "";
    if ( ! isset($failed_label) ) $failed_label = "";

    // You must have a label!
    $label = "Click Me";
    foreach($list as $item)
    {
        if( GetStringValue($selected) === GetArrayStringValue("value", $item) )
        {
            $label = GetArrayStringValue("display", $item);
        }
    }

    // Hide the button if so specified.
    $hidden = "";
    if ( $is_hidden ) $hidden = "hidden";

    // Set the name on the hidden input that holds the dropdown value to
    // match the button id value.
    $name = $id;


    $onclick = "";
    if ( $callback_onclick !== '' )
    {
        $onclick = " data-callbackonclick='$callback_onclick' ";
    }

    $data_success = "";
    if ( $success_label !== '' )
    {
        $data_success = " data-success='$success_label' ";
    }

    $data_failed = "";
    if ( $failed_label !== '' )
    {
        $data_failed = " data-failed='$success_label' ";
    }

    $data_buttoncolor = "";
    if ( $button_color !== '' )
    {
        $data_buttoncolor = " data-buttoncolor='$button_color' ";
    }

    $data_buttoncoloroffset = "";
    if ( $button_color_offset !== '' )
    {
        $data_buttoncoloroffset = " data-buttoncoloroffset='$button_color_offset' ";
    }


?>


<span class="dropdown multi-option-btn-container <?=$hidden?> <?=$classes?>" <?=$attributes?> >
    <input type="hidden" name="<?=$name?>" value="<?=$selected?>">
    <div class="btn-group">
        <button <?=$data_success?> <?=$data_failed?> <?=$data_buttoncolor?> <?=$data_buttoncoloroffset?> <?=$onclick?> id="<?=$id?>" class="btn btn-multi-option <?=$button_color?> <?=$button_size?> waves-light waves-effect main-btn" type="<?=$button_type?>" value="<?=$selected?>">
            <?=$label?>
        </button>
        <button type="button" class="btn btn-multi-option <?=$button_color?> <?=$button_size?> <?=$button_color_offset?> waves-light waves-effect dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="multioptionbutton-toggle">
                <i data-togglestate='opened' class="multioptionbutton-icon glyphicon glyphicon-triangle-bottom"></i>
                <i data-togglestate='closed' class="glyphicon glyphicon-triangle-top hidden"></i>
            </span>
            <span class="multioptionbutton-warning hidden"><i data-togglestate='failed' class="multioptionbutton-icon multioptionbutton-warning-icon glyphicon glyphicon-warning-sign"></i></span>
            <span class="multioptionbutton-success hidden"><i data-togglestate='success' class="multioptionbutton-icon multioptionbutton-success-icon glyphicon glyphicon-ok-sign"></i></span>
            <span class="multioptionbutton-working hidden"><i data-togglestate='working' class="multioptionbutton-icon multioptionbutton-working-icon fa fa-spin fa-cog"></i></span>

        </button>

        <ul class="dropdown-menu">
            <?php
            foreach($list as $item)
            {
                $value = GetArrayStringValue('value', $item );
                $display = GetArrayStringValue('display', $item );
                $type = GetArrayStringValue('type', $item );
                $disabled = GetArrayStringValue('disabled', $item );
                $class = GetArrayStringValue('class', $item );

                if ( $disabled === 'TRUE' ) $disabled = 'disabled';
                else $disabled = "";

                if ( $type === 'item' )
                {
                    ?><li class="<?=$class?> <?=$disabled?>" data-value="<?=$value?>"><a href="#"><?=$display?></a></li><?php
                }
                else if ( $type === 'divider' )
                {
                    ?> <li role="separator" class="divider required-value"></li> <?php
                }
            }
            ?>
        </ul>
    </div>
</span>
