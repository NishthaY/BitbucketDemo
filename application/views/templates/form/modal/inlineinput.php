<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($value) ) $value = "";
    if ( ! isset($button_label) ) $button_label = "";
    if ( ! isset($href) ) $href = "";
    if ( ! isset($callback) ) $callback = "";
    if ( ! isset($failed_callback) ) $failed_callback = "";

?>
<div class="form-group has-feedback">
    <label for="<?=$id?>"><?=$description?></label>
    <div class="input-group has-feedback">
        <input type="text" class="form-control" id="<?=$id?>" name="<?=$id?>" value="<?=$value?>" readonly >
        <span class="input-group-btn">
            <button type="button" class="btn btn-form-inline waves-effect waves-light btn-white" data-href="<?=$href?>" data-callback="<?=$callback?>" data-failure-callback="<?=$failed_callback?>" ><?=$button_label?></button>
        </span>
    </div>
    <p class="help-block text-error hidden"></p>
</div>
