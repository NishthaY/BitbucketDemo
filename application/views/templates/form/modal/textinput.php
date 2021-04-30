<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($placeholder) ) $placeholder = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($value) ) $value = "";
    if ( ! isset($disabled_flg) ) $disabled_flg = true;
    if ( ! isset($hidden_flg) ) $hidden_flg = false;

    $disabled = "";
    if ( $disabled_flg ) $disabled = "disabled";

    $hidden = "";
    if ( $hidden_flg ) $hidden = "hidden";

    $description_class = "";
    if ( getStringValue($description) == "" ) $description_class = "hidden";
?>
<div class="form-group has-feedback <?=$hidden?>">
    <label class="<?=$description_class?>" for="name"><?=$description?></label>
    <input type="text" class="form-control" id="<?=$id?>" name="<?=$id?>" placeholder="<?=$placeholder?>"  value="<?=$value?>" <?=$disabled?>>
    <p class="help-block text-error hidden"></p>
</div>
