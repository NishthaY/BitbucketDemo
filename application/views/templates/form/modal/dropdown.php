<?php
	if ( ! isset ( $id) ) $id = "";
    if ( ! isset ( $description) ) $description = "";
	if ( ! isset ( $placeholder) ) $placeholder = "";
	if ( ! isset ( $class) ) $class = "";
	if ( ! isset ( $list) ) $list = array();
    if ( ! isset ( $selected) ) $selected = "";
	if ( ! isset ( $change_callback) ) $change_callback = "";
    if ( ! isset ( $button_label) ) $button_label = "Select";
	if ( ! isset ( $inline_flg) ) $inline_flg = false;
	if ( ! isset ( $scrollable_flg ) ) $scrollable_flg = false;

    // If we have a selected value, find the label for that item and replace
    // the placeholder with the selected item to make it appear that the selected item
    // has been selected.
    if ( $selected != "" ) {
        foreach($list as $option => $label)
        {
            if ( $option == $selected ) {
                $placeholder = $label;
                break;
            }
        }
    }

	$tag = "div";
	if ( $inline_flg ) $tag = "span";

	$inline_class = "";
	if ( $inline_flg ) $inline_class = "form-inline";

	// If you have no description, hide the label.
	$label_class = "";
	if ( getStringValue($description) == "" ) $label_class = "hidden";

    $scrollable_class = "";
    if ( $scrollable_flg ) $scrollable_class = "scrollable-menu";

?>
<<?=$tag?> class="comment-box-row <?=$inline_class?> <?=$class?>">
    <<?=$tag?> class="form-group has-feedback" style="margin-left: 0px">
        <<?=$tag?> class="">
            <label for="<?=$id?>" class="<?=$label_class?>"><?=$description?></label>
            <<?=$tag?> class="input-group">
                <input id="<?=$id?>_disabled" name="<?=$id?>_disabled" type="text" class="form-control uiform-dropdown-placeholder" placeholder="<?=$placeholder?>" readonly>
                <<?=$tag?> class="input-group-btn has-error">
                    <button id="<?=$id?>_button" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-dropdown-source="<?=$id?>"><?=$button_label?> <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-right <?=$scrollable_class?>">
                        <?php
                        if ( ! empty($list) )
                        {
                            foreach($list as $option => $label)
                            {
                                ?><li id="<?=$option?>" value="<?=$label?>"><a href="#"><?=$label?></a></li><?php
                            }
                        }
                        ?>
                    </ul>
                </<?=$tag?>>
            </<?=$tag?>>
            <input id="<?=$id?>" class="uiform-dropdown" type="hidden" name="<?=$id?>" value="<?=$selected?>" data-change-callback="<?=$change_callback?>">
            <p class="help-block text-error hidden" />
        </<?=$tag?>>
    </<?=$tag?>>
</<?=$tag?>>
