<?php
if ( ! isset($parent_override) ) $parent_override = false;
if ( ! isset($target) ) $target = "";
if ( ! isset($target_type) ) $target_type = "";
?>
<?php
if ( ! $parent_override )
{
    ?>Enable/Disable the ability to identify and collect <a  data-target="<?=$target?>" data-targettype="<?=$target_type?>" data-widget-name="beneficiary_mapping_widget" data-form-name="beneficiary_mapping_form" class="feature-configuration-link">beneficiary</a> data.<?php
}
else
{
    ?>Enable/Disable the ability to identify and collect beneficiary data.<?php
}
?>