<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($placeholder) ) $placeholder = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($value) ) $value = "";
    if ( ! isset($disabled_flg) ) $disabled_flg = true;
    if ( ! isset($hidden_flg) ) $hidden_flg = true;

    $disabled = "";
    if ( $disabled_flg ) $disabled = "disabled";

    $hidden = "";
    if ( $hidden_flg ) $hidden = "hidden";
?>
<div class="form-group has-feedback <?=$hidden?>">
    <label for="name"><?=$description?></label>
    <input type="email" class="form-control" id="<?=$id?>" name="<?=$id?>" placeholder="<?=$placeholder?>"  value="<?=$value?>" <?=$disabled?>>
    <p class="help-block text-error"></p>
</div>
