<?php
    if ( ! isset($parent_override) ) $parent_override = false;
?>
<?php
    if ( ! $parent_override )
    {
        ?>Enable/Disable the <a data-widget-name="commission_tracking_widget" data-form-name="commission_tracking_form" class="feature-configuration-link">commission tracking</a> module.<?php
    }
    else
    {
        ?>Enable/Disable the commission tracking module.<?php
    }

?>

