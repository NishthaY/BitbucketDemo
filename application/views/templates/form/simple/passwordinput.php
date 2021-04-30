<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($placeholder) ) $placeholder = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($value) ) $value = "";
    if ( ! isset($disabled_flg) ) $disabled_flg = false;

    $disabled = "";
    if ( $disabled_flg ) $disabled = "disabled";
?>
<div class="form-group has-feedback">
    <div class="col-xs-12">
        <input type="password" class="form-control" id="<?=$id?>" name="<?=$id?>" placeholder="<?=$placeholder?>"  value="<?=$value?>" <?=$disabled?>  autocomplete="off">
        <p class="help-block text-error hidden"></p>
    </div>
</div>
