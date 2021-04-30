<?php
if ( ! isset($parent_override) ) $parent_override = false;
if ( ! isset($target) ) $target = "";
if ( ! isset($target_type) ) $target_type = "";
?>
<?php
if ( ! $parent_override )
{
    ?>Enable/Disable the customized <a data-widget-name="default_clarifications_widget" data-form-name="default_clarifications_form" class="feature-configuration-link">default clarifications</a> logic.<?php
}
else
{
    ?>Enable/Disable the customized default clarifications logic.<?php
}
?>