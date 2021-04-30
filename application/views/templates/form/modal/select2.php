<?php
    if (! isset($data) ) $data = array();
    if (! isset($selected_value) ) $selected_value = "";
    if (! isset($default_display) ) $default_display = "";
    if (! isset($default_value) ) $default_value = "";
    if (! isset($id) ) $id = "";
    if (! isset($is_hidden) ) $is_hidden = false;
    if (! isset($attributes) ) $attributes = "";
    if (! isset($description) ) $description = "";
    if (! isset($placeholder) ) $placeholder = "";

    $sections = array_keys($data);

    $description_class = "";
    if ( getStringValue($description) == "" ) $description_class = "hidden";

    $hidden = "";
    if ( $is_hidden ) $hidden = "hidden";

    $default = "";
    if ( getStringValue($default_display) !== '' )
    {
        $default = "<option value='{$default_value}'>{$default_display}</option>\n";
    }


?>

<div class="form-group has-feedback <?=$hidden?>" <?=$attributes?> >
    <label class="<?=$description_class?>" for="name"><?=$description?></label>
    <select id="<?=$id?>" name="<?=$id?>" class="form-control select2" data-placeholder="<?=$placeholder?>">
        <?=$default?>
        <?php
        $first = true;
        foreach($sections as $section_label)
        {
            $section = $data[$section_label];
            if ( ! empty($section) )
            {
                if( $section_label !== "null" ) print "<optgroup label='{$section_label}'>\n";
                foreach($section as $item)
                {
                    // If we have a placeholder, then the very first option we print MUST be empty.
                    if ( $first && $placeholder !== '' ) print "<option></option>";

                    $display = getArrayStringValue("display", $item);
                    $value = getArrayStringValue("value", $item);
                    $selected = "";
                    if ( $value === $selected_value ) $selected = "selected";
                    print "<option value='{$value}' {$selected}>{$display}</option>\n";
                    $first = false;
                }
                if( $section_label !== "null" ) print "</optgroup>\n";
            }
        }
        ?>
    </select>
</div>


