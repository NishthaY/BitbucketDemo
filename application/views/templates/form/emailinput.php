<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($placeholder) ) $placeholder = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($value) ) $value = "";
    if ( ! isset($disabled_flg) ) $disabled_flg = true;
    if ( ! isset($hidden_flg) ) $hidden_flg = false;
    if ( ! isset($attributes) ) $attributes = "";

    $disabled = "";
    if ( $disabled_flg ) $disabled = "disabled";

    $hidden = "";
    if ( $hidden_flg ) $hidden = "hidden";
?>
<div class="form-group has-feedback">
  <label for="<?=$id?>" class="col-sm-2 control-label"><?=$description?></label>
  <div class="col-sm-10">
    <input type="email" class="form-control" id="<?=$id?>" name="<?=$id?>" placeholder="<?=$placeholder?>" value="<?=$value?>" <?=$attributes?> <?=$disabled?> >
    <p class="help-block text-error"></p>
  </div>
</div>
