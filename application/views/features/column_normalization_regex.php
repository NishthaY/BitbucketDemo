<?php
    if ( ! isset($parent_override) ) $parent_override = false;
    if ( ! isset($target) ) $target = "";
    if ( ! isset($target_type) ) $target_type = "";
?>
<?php
if ( ! $parent_override )
{
    ?>Enable/Disable the <a data-target="<?=$target?>" data-targettype="<?=$target_type?>" data-widget-name="column_normalization_widget" data-form-name="column_normalization_form" class="feature-configuration-link">regex replace</a> normalization module for the <?=$target?> column.<?php
}
else
{
    ?>Enable/Disable the regex replace normalization module for the <?=$target?> column.<?php
}

?>

