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
  <label for="<?=$id?>" class="col-sm-2 control-label"><?=$description?></label>
  <div class="col-sm-10">
    <input type="password" class="form-control" id="<?=$id?>" name="<?=$id?>" placeholder="<?=$placeholder?>" value="<?=$value?>" <?=$disabled?>  autocomplete="off" >
    <p class="help-block text-error"></p>
  </div>
</div>
