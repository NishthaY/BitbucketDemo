<?php
    if ( ! isset($prefix) ) $prefix = "";
    if ( ! isset($checkboxes) ) $checkboxes = array();
?>

<div class="form-group has-feedback">
    <div class="col-xs-12">
        <div class="checkbox-wrappers">
            <div class="checkbox_outer">

                <?php
                for($i=0;$i<count($checkboxes);$i++)
                {
                    $checkbox = $checkboxes[$i];
                    $id = GetArrayStringValue('id', $checkbox);
                    $checked = GetArrayStringValue('checked', $checkbox);
                    $disabled = GetArrayStringValue('disabled', $checkbox);
                    $inline_description = GetArrayStringValue('inline_description', $checkbox);

                    $more = '<br>';
                    if ( ($i+1) >= count($checkboxes) ) $more = '';

                    if ( $checked === 'TRUE') $checked = ' checked ';
                    else $checked = '';

                    if ( $disabled === 'TRUE') $disabled = ' disabled ';
                    else $disabled = '';

                    ?><input type="checkbox" name="<?=$prefix?><?=$id?>" <?=$checked?> <?=$disabled?> > <span class="uiform-checkbox-inline-desc"><?=$inline_description?></span><?=$more?><?php
                }

                ?>
            </div>
        </div>
        <p class="help-block text-error"></p>
    </div>
</div>