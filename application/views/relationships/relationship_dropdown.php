<?php
    if ( ! isset($company_relationship_id) ) $company_relationship_id = "";
    if ( ! isset($button_label) ) $button_label = "Unassigned";
    if ( ! isset($button_value) ) $button_value = "";
    if ( ! isset($dropdown) ) $dropdown = array();
    if ( ! isset($href) ) $href = "";

    $indicator_class = "hidden";
    if ( $button_label == "" || $button_label == "Unassigned" ) $indicator_class = "";

    $selected_value = $button_value;
    $selected_text = $button_label;
    if ( $selected_text == "Unassigned" ) $selected_text = "";

?>
<div class="relationship-type-group btn-group dropdown m-b-10">
    <button data-href="<?=$href?>" data-dropdown-source="<?=$company_relationship_id?>" type="button" class="btn btn-white waves-light dropdown-toggle waves-effect" data-toggle="dropdown" aria-expanded="false"><span class="button-label p-r-15"><?=$button_label?></span> <i class="caret"></i></button>
    <ul class="dropdown-menu dropdown-menu-right scrollable-menu" style="position: fixed; width:150px;">
        <?php
        if ( ! empty($dropdown) )
        {
            foreach($dropdown as $item)
            {

                $option = getArrayStringValue("RelationshipCode", $item);
                $label = getArrayStringValue("RelationshipDescription", $item);
                ?><li id="<?=$option?>" value="<?=$label?>"><a href="#"><?=$label?></a></li><?php
            }
        }
        ?>
    </ul>
    <span class="relationship-question-indicator <?=$indicator_class?>"><i class="glyphicon glyphicon-question-sign"></i></span>
</div>

<input type="hidden" id="<?=$company_relationship_id?>_selected_value" name="<?=$company_relationship_id?>_selected_value" value="<?=$selected_value?>">
<input type="hidden" id="<?=$company_relationship_id?>_selected_text" name="<?=$company_relationship_id?>_selected_text" value="<?=$selected_text?>">
