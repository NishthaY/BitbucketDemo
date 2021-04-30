<?php
	if ( ! isset ( $id) ) $id = "";
    if ( ! isset ( $description) ) $description = "";
	if ( ! isset ( $placeholder) ) $placeholder = "";
	if ( ! isset ( $class) ) $class = "btn2";
	if ( ! isset ( $list) ) $list = array();
    if ( ! isset ( $selected) ) $selected = "";
	if ( ! isset ( $change_callback) ) $change_callback = "";
    if ( ! isset ( $button_label) ) $button_label = "Select";

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

?>

<div class="comment-box-row">
    <div class="form-group has-feedback">
        <label for="<?=$id?>" class="control-label col-sm-2 "><?=$description?></label>
        <div class="col-sm-10" >
            <div class="input-group">
                <input id="<?=$id?>_disabled" name="<?=$id?>_disabled" type="text" class="form-control uiform-dropdown-placeholder" placeholder="<?=$placeholder?>" readonly>
                <div class="input-group-btn has-error">
                    <button id="<?=$id?>_button" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-dropdown-source="<?=$id?>"><?=$button_label?> <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <?php
                        if ( ! empty($list) )
                        {
                            foreach($list as $option => $label) {
                                ?><li id="<?=$option?>" value="<?=$label?>"><a href="#"><?=$label?></a></li><?php
                             }
                        }
                        ?>
                    </ul>
                </div>

            </div>
        <input id="<?=$id?>" class="uiform-dropdown" type="hidden" name="<?=$id?>" value="<?=$selected?>" data-change-callback="<?=$change_callback?>">
        <p class="help-block text-error hidden" />
        </div>
    </div>

</div>
