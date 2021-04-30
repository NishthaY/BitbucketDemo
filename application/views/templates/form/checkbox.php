<?php
    if ( ! isset ( $id) ) $id= "";
	if ( ! isset ( $description) ) $description = "";
    if ( ! isset ( $inline_description ) ) $inline_description = "";
	if ( ! isset ( $is_checked ) ) $is_checked = false;
    if ( ! isset ( $is_hidden ) ) $is_hidden = false;

    $checked = "";
    if ( $is_checked ) $checked = "checked";

    $hidden = "";
    if ( $is_hidden ) $hidden = "hidden";

?>
<div class="comment-box-row">
	<div class="form-group has-feedback <?=$hidden?>">
		<label class="col-sm-2 control-label" for="<?=$id?>"><?=$description?></label>
		<div class="col-sm-9">
			<div class="checkbox-wrapper">
				<div class="checkbox_outer">
					<input id="<?=$id?>" type="checkbox" name="<?=$id?>" <?=$checked?> > <span class="uiform-checkbox-inline-desc"><?=$inline_description?></span>
					<span></span>
				</div>
			</div>
            <p class="help-block text-error" />
		</div>
	</div>
</div>
