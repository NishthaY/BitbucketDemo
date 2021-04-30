<?php
    if ( ! isset($dropdown_id) ) $dropdown_id = "";
    if ( ! isset($selected) ) $selected = "";       // Selected Value.
    if ( ! isset($list) ) $list = array();
    if ( ! isset($href) ) $href = "";
    if ( ! isset($class) ) $class = ""; //
    if ( ! isset($scrollable) ) $scrollable = true;
    if ( ! isset($attributes) ) $attributes = "";
    if ( ! isset($is_hidden) ) $is_hidden = false;
    if ( ! isset($callback_onchange) ) $callback_onchange = "";


    $selected_text = "";
    $selected_value = "";

    // If the user did not specify the selected value, default it to the first item in the list.
    if ( $selected === '' )
    {
        if ( count($list) > 0 )
        {
            $selected = GetArrayStringValue('value', $list[0]);
        }
    }


    // If we have a selected value, find the label for that item and replace
    // the placeholder with the selected item to make it appear that the selected item
    // has been selected.
    if ( $selected != "" ) {
        $selected_value = $selected;
        foreach($list as $details)
        {
            $type = GetArrayStringValue('type', $details);
            $value = GetArrayStringValue('value', $details);
            $label = GetArrayStringValue('display', $details);
            if ( $value == $selected ) {
                $selected_text = $label;
                break;
            }
        }
    }

    $scrollable_class = "";
    if ( $scrollable ) $scrollable_class = "scrollable-menu";

    $hidden = "";
    if ( $is_hidden ) $hidden = "hidden";

?>
<div class="<?=$class?> btn-group dropdown dropdown-inline m-b-10 <?=$hidden?>" <?=$attributes?> >
        <button data-href="<?=$href?>" data-dropdown-source="<?=$dropdown_id?>" type="button" class="btn btn-white waves-light dropdown-toggle waves-effect" data-toggle="dropdown" aria-expanded="false" data-change-callback="<?=$callback_onchange?>" ><span class="button-label p-r-15"><?=$selected_text?></span> <i class="caret"></i></button>
        <ul class="dropdown-menu dropdown-menu <?=$scrollable_class?>" style="">
            <?php
            foreach($list as $details)
            {
                $type = GetArrayStringValue('type', $details);
                $value = GetArrayStringValue('value', $details);
                $label = GetArrayStringValue('display', $details);
                $item_disabled = GetArrayStringValue('disabled', $details);
                $item_class = GetArrayStringValue('class', $details);

                if ($item_disabled !== 'TRUE' ) $item_disabled = "";
                if ($item_disabled === 'TRUE' ) $item_disabled = "disabled";

                if ( $type === 'item')
                {
                    ?>
                    <li class="<?=$item_class?> <?=$item_disabled?>" value="<?=$value?>"><a href="#"><?=$label?></a></li>
                    <?php
                }
                if ( $type === 'divider')
                {
                    ?>
                    <li role="separator" class="divider required-value"></li>
                    <?php
                }
                ?>

                <?php
            }
            ?>
        </ul>
    <div class="runtime-error hidden"></div>

</div>
<input type="hidden" id="<?=$dropdown_id?>_selected_value" name="<?=$dropdown_id?>_selected_value" value="<?=$selected_value?>">
<input type="hidden" id="<?=$dropdown_id?>_selected_text" name="<?=$dropdown_id?>_selected_text" value="<?=$selected_text?>">

