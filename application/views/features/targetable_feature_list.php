<?php
    if ( ! isset($feature_list) ) $feature_list = array();
    if ( ! isset($dropdowns) ) $dropdowns = array();
?>
<div class="">
    <p>
        Select the new feature you want to create from the list of options below.
    </p>
    <?php
    foreach( $feature_list as $item)
    {
        $code = GetArrayStringValue('code', $item);
        $display = GetArrayStringValue('display', $item);
        $description = GetArrayStringValue('description', $item);
        $target_type = GetArrayStringValue('target_type', $item);
        ?>
        <div class="radio radio-primary p-t-20">
            <input data-targettype="<?=$target_type?>" class="preference-item" type="radio" name="feature_code" id="<?=$code?>" value="<?=$code?>" >
            <label for="<?=$code?>">
                <strong><?=$display?></strong> - <?=$description?>
            </label>
        </div>
        <?php

        // Only check the first one in the list.
        $checked = "";
    }
    ?>
</div>

<?php
    if ( ! empty($dropdowns) )
    {
        foreach ($dropdowns as $target_type => $dropdown) {
            $display = replaceFor($target_type, "_", " ");
            $display = ucwords($display);
            ?>
            <div id="<?=$target_type?>" class="form-group has-feedback p-t-20 targetable-selection">
                <label for="name"><?=$display?></label>
                <?=$dropdown?>
                <p class="help-block text-error"></p>
            </div>
            <?php
        }
    }
?>


