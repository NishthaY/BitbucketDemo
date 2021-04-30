<?php
    if ( ! isset($label) ) $label = "Confirmed";
    if ( ! isset($href) ) $href = "";
    if ( ! isset($callback) ) $callback = "";
    if ( ! isset($callback_parameter) ) $callback_parameter = "";
    if ( ! isset($color) ) $color = "";
    if ( ! isset($spinner) ) $spinner = true;
    if ( ! isset($buttons) ) $buttons = array();
?>
<div class="confirm-btn text-right" data-href="<?=$href?>" data-spinner="<?=$spinner?>" data-callback="<?=$callback?>" data-callback-parameter="<?=$callback_parameter?>">
    <span style="padding-right: 10px;"><input class="confirm-btn-checkbox dyno-confirm" type="checkbox" data-plugin="switchery" data-size="small" data-color="<?=$color?>"/></span>
    <a class="confirm-btn-enabled btn-sm btn-primary waves-effect waves-light hidden" href="javascript:void(0);"><?=$label?></a>
    <a class="confirm-btn-disabled btn btn-sm btn-working" href="javascript:void(0);" disabled><?=$label?></a>

    <?php
    foreach($buttons as $button)
    {
        $label = GetArrayStringValue('label', $button);
        $href = GetArrayStringValue('href', $button);
        $attributes = $button['attributes'];
        $alt_button_callback = GetArrayStringValue('callback', $button);

        if ( $href === '' ) $href = "javascript:void(0);";

        // Convert the attributes to a string.
        $temp = "";
        foreach($attributes as $key=>$value)
        {
            if ( strpos($value, '"') !== FALSE && strpos($value, "'") !== FALSE )  continue;
            else if ( strpos($value, '"') !== FALSE ) $temp .= " data-{$key}='{$value}' ";
            else if ( strpos($value, "'") !== FALSE ) $temp .= ' data-{$key}="{$value}" ';
            else $temp .= " data-{$key}='{$value}' ";
        }
        $attributes = $temp;
        ?>
        <a <?=$attributes?> data-callback="<?=$alt_button_callback?>" class="confirm-other-btn-enabled btn-sm btn-white waves-effect waves-light hidden" href="<?=$href?>"><?=$label?></a>
        <a class="confirm-other-btn-disabled btn btn-sm btn-working" href="javascript:void(0);" disabled><?=$label?></a>
        <?php
    }
    ?>


</div>
