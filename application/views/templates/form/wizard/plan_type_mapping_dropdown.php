<?php
    if ( ! isset($index) ) $index = "";                                             // Every map dropdown on the page has a unique index.
    if ( ! isset($external_value) ) $external_value = "";                           // The external value we are mapping to an internal value.
    if ( ! isset($dropdown_items) ) $dropdown_items = array();                      // array of "name"/"display" items that make up the dropdown.
    if ( ! isset($selected_value) ) $selected_value = "";                           // "name" that is currently selected.
    if ( ! isset($unselected_display) ) $unselected_display = "Select ...";         // Text show when no selection has been made.
    if ( ! isset($remove_map_display) ) $remove_map_display = "Remove Mapping";     // Text shown so you can remove the mapping.

    $selected_display = $unselected_display;
    foreach($dropdown_items as $item)
    {
        if ( getArrayStringValue("name", $item) == $selected_value )
        {
            $selected_display = getArrayStringValue("display", $item);
        }
    }

    $dropdown_class = "default";
    if ( $selected_value != "" ) $dropdown_class = "info";

?>
<div class="btn-group mapping-dropdown" data-not-selected-display="<?=$unselected_display?>" data-not-selected-value="<?=$unselected_display?>">
    <button selected-value="<?=$selected_value?>" type="button" class="btn btn-<?=$dropdown_class?> dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="true"><?=$selected_display?> <span class="caret"></span></button>
    <ul class="dropdown-menu" role="menu">
        <li data-value="ignore" data-display="<?=$remove_map_display?>"><a href="#" ><i>Ignore This Plan Type</i></a></li>
        <li role="separator" class="divider"></li>
        <?php
            foreach($dropdown_items as $item)
            {
                ?><li data-value="<?=getArrayStringValue("name", $item)?>" data-display="<?=getArrayStringValue("display", $item)?>"><a href="#" ><?=getArrayStringValue("display", $item)?></a></li><?php
            }
        ?>
    </ul>
    <input id="map<?=$index?>-internal" name="map<?=$index?>-internal" type="hidden"  value="<?=$selected_value?>">
    <input id="map<?=$index?>-external" name="map<?=$index?>-external" type="hidden"  value="<?=$external_value?>">
</div>
