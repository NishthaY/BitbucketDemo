<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($placeholder) ) $placeholder = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($value) ) $value = "";
    if ( ! isset($disabled_flg) ) $disabled_flg = true;
    if ( ! isset($rows) ) $rows = "3";

    $disabled = "";
    if ( $disabled_flg ) $disabled = "disabled";
    
?>
<div class="form-group has-feedback">
    <label for="name"><?=$description?></label>
    <textarea class="form-control" rows="<?=$rows?>" id="<?=$id?>" name="<?=$id?>" placeholder="<?=$placeholder?>" <?=$disabled?> ><?=$value?></textarea>
    <p class="help-block text-error"></p>
</div>