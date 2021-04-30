<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($value) ) $value = "";
    if ( ! isset($disabled_flg) ) $disabled_flg = true;

    $disabled = "";
    if ( $disabled_flg ) $disabled = "disabled";
?>
<div class="form-group has-feedback">
    <div class="col-xs-12">
        <input data-mask="(999) 999-9999" type="text" class="form-control" id="<?=$id?>" name="<?=$id?>" placeholder="(###) ###-####"  value="<?=$value?>" <?=$disabled?>>
        <p class="help-block text-error"></p>
    </div>
</div>
