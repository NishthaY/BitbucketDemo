<?php
if ( ! isset($parent_override) ) $parent_override = false;
if ( ! isset($target) ) $target = "";
if ( ! isset($target_type) ) $target_type = "";
?>
<?php
if ( ! $parent_override )
{
    ?>Enable/Disable the <a data-widget-name="default_carrier_widget" data-form-name="default_carrier_form" class="feature-configuration-link">default carrier</a> to be used when not specified on imports.<?php
}
else
{
    ?>Enable/Disable the default carrier to be used when not specified on imports.<?php
}
?>