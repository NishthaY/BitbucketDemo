<?php
if ( ! isset($parent_override) ) $parent_override = false;
if ( ! isset($target) ) $target = "";
if ( ! isset($target_type) ) $target_type = "";
?>
<?php
if ( ! $parent_override )
{
    ?>Enable/Disable the <a  data-target="<?=$target?>" data-targettype="<?=$target_type?>" data-widget-name="default_plan_widget" data-form-name="default_plan_form" class="feature-configuration-link">default plan</a> to be used when not specified on imports.<?php
}
else
{
    ?>Enable/Disable the default plan to be used when not specified on imports.<?php
}
?>