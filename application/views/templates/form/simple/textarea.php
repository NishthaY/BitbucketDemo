<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($placeholder) ) $placeholder = "";
    if ( ! isset($value) ) $value = "";
    if ( ! isset($disabled_flg) ) $disabled_flg = true;
    if ( ! isset($rows) ) $rows = "3";

    $disabled = "";
    if ( $disabled_flg ) $disabled = "disabled";
    
?>
<div class="form-group has-feedback">
    <div class="col-xs-12">
        <textarea class="form-control" rows="<?=$rows?>" id="<?=$id?>" name="<?=$id?>" placeholder="<?=$placeholder?>" <?=$disabled?> ><?=$value?></textarea>
        <p class="help-block text-error"></p>
    </div>
</div>