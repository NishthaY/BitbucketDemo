<?php
    if( ! isset($parent_id) ) $parent_id = "";
    if( ! isset($parent_row) ) $parent_row = array();

    $checked = "";
    $is_new_life = getArrayStringValue("IsNewLife", $parent_row);
    if ( $is_new_life == "t" ) $checked = "checked";

?>
<div class="row clickable-life" data-href="<?=base_url()?>lives/save"><div class="col-sm-12"><span class="p-l-15"><input class="m-r-5" type="radio" name="LifeReview-<?=$parent_id?>" value="NEW" <?=$checked?>><span>Treat as new life.</span></span></div></div>
</div>
</div>
</div>
