<?php
    if ( ! isset($col) ) $col = "";
    if ( ! isset($mappings) ) $mappings = array();
    if ( ! isset($required_mappings) ) $required_mappings = array();
    if ( ! isset($selected) ) $selected = "";
    if ( ! isset($default_label) ) $default_label = "";
    if ( ! isset($user_label)) $user_label = "";

    // SORT
    // Sort the mappings by display name so things are in
    // alphabetical order!
    uasort($mappings, 'AssociativeArraySortFunction_Display');

    // SELECTED
    // Scan the mappings until we find the item that is selected and then
    // pull out it's display value so we have both the code and the human
    // readable description.
    $selected_display = "Match Column";
    foreach($mappings as $mapping)
    {
        if ( getArrayStringValue("name", $mapping) == $selected)
        {
            $selected_display = getArrayStringValue("display", $mapping);
            break;
        }
    }

    // LOOKUPS
    // Make a few dictionaries as we loop through the mappings so we
    // can do lookups later.
    $conditional_lookup = array();
    $mapping_lookup = array();


    // COUNT
    // Count the different types of mapping categories we have.
    $required_count = 0;
    $recommended_count = 0;
    $conditional_count = 0;
    foreach ( $mappings as $mapping )
    {
        $required = GetArrayStringValue('required', $mapping);
        $conditional = GetArrayStringValue('conditional', $mapping);
        $name = GetArrayStringValue('name', $mapping);

        if ( $required === 't' ) $required_count++;
        if ( $required !== 't' && $conditional !== 't' ) $recommended_count++;

        if ( $conditional === 't' )
        {
            // Keep track of the unique sets of conditional columns in the
            // conditional lookup.  We will use this lookup later to count the
            // number of distinct sets we are dealing with.
            $conditional_list = GetArrayStringValue('conditional_list', $mapping);
            $conditional_lookup[$conditional_list] = true;
        }

        // Snag the mapping lookup by name.
        $mapping_lookup[$name] = $mapping;
    }
    $conditional_count = count(array_keys($conditional_lookup));



?>
<div id="mapping_column_<?=$col?>" class="btn-group mapping-dropdown" data-user-label="<?=$user_label?>" data-default-label="<?=$default_label?>">
    <button id="" name="" selected-value="<?=$selected?>" data-column="<?=$col?>" type="button" class="btn btn-default dropdown-toggle waves-effect waves-light btn-column-mapping" data-toggle="dropdown" aria-expanded="true"><?=$selected_display?> <span class="caret"></span></button>
    <ul class="dropdown-menu scrollable-menu-lg" role="menu">


        <li class="dropdown-header">Actions</li>
        <li data-col="<?=$col?>" data-value="" data-display="Remove Match"><a href="#" ><span class="matching-item-action">Remove Match</span></a></li>
        <li role="separator" class="divider required-value"></li>


    <?php
        if ( $required_count > 0 )
        {
            ?><li class="dropdown-header required">Required</li><?php
            foreach ( $mappings as $mapping )
            {
                if ( getArrayStringValue("required", $mapping) == "t")
                {
                    ?><li class="matching-item required" data-value="<?=getArrayStringValue("name", $mapping)?>" data-display="<?=getArrayStringValue("display", $mapping)?>"><a href="#" ><span class="glyphicon glyphicon-ok-circle hidden"></span> <span class=""><?=getArrayStringValue("display", $mapping)?></span></a></li><?php
                }
            }
            ?><li role="separator" class="divider required-value"></li><?php

        }

    ?>
    <?php
        if ( $conditional_count > 0 )
        {
            $keys = array_keys($conditional_lookup);
            foreach($keys as $key)
            {
                $set = explode(',', $key);
                ?><li class="dropdown-header conditional" data-count="<?=count($set)?>">At Least One Required</li><?php
                foreach($set as $item)
                {
                    $mapping = $mapping_lookup[$item];
                    ?><li class="matching-item conditional" data-value="<?=getArrayStringValue("name", $mapping)?>" data-display="<?=getArrayStringValue("display", $mapping)?>"><a href="#" ><span class="glyphicon glyphicon-ok-circle hidden"></span> <span class=""><?=getArrayStringValue("display", $mapping)?></a></span></li><?php
                }
                ?><li role="separator" class="divider required-value"></li><?php
            }
        }
    ?>
    <?php

        if($recommended_count > 0 )
        {
            ?><li class="dropdown-header recommended">Recommended</li><?php
            foreach($mappings as $mapping)
            {
                if ( getArrayStringValue("required", $mapping) != "t")
                {
                    ?><li class="matching-item recommended" data-value="<?=getArrayStringValue("name", $mapping)?>" data-display="<?=getArrayStringValue("display", $mapping)?>"><a href="#" ><span class="glyphicon glyphicon-ok-circle hidden"></span> <span><?=getArrayStringValue("display", $mapping)?></a></span></li><?php
                }
            }
        }

    ?>
    </ul>
    <input id="col<?=$col?>" name="col<?=$col?>" type="hidden"  value="<?=$selected?>">
</div>
