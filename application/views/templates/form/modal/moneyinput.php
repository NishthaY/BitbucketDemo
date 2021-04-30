<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($placeholder) ) $placeholder = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($value) ) $value = "";
    if ( ! isset($disabled_flg) ) $disabled_flg = true;

    $disabled = "";
    if ( $disabled_flg ) $disabled = "disabled";
?>
<div class="form-group has-feedback">
    <label for="name"><?=$description?></label>
    <div class="input-group">
        <span class="input-group-addon">$</span>
        <input type="text" class="form-control" id="<?=$id?>" name="<?=$id?>" placeholder="<?=$placeholder?>"  value="<?=$value?>" <?=$disabled?>>
    </div>
    <p class="help-block text-error hidden"></p>
</div>
