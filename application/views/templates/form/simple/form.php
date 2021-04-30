<?php
    if ( ! isset($form_title) ) $form_title = "";
    if ( ! isset($form_name) ) $form_name = "";
    if ( ! isset($form_id) ) $form_id = "";
    if ( ! isset($action) ) $action = "";
    if ( ! isset($elements) ) $elements = array();
    if ( ! isset($attributes) ) $attributes = "";
    if ( ! isset($collapsable) ) $collapsable = false;
    if ( ! isset ( $form_lead) ) $form_lead = "";
	if ( ! isset ( $form_description) ) $form_description = "";

	$lead_class = "hidden";
	if ( $form_lead != "" ) $lead_class = "";

	$description_class = "hidden";
	if ( $form_description != "" ) $description_class = "";


    $collapsable_style = "";
    if ( $collapsable ) $collapsable_style = "display:none";

    $collapsable_class = "";
    if ( $collapsable ) $collapsable_class = "collapsable";

    $collapsable_form = "";
    if ( $collapsable ) $collapsable_form = "collapsable-form";



?>
<div class="alert alert-success hidden" role="alert"><span class="alert-message"></span></div>
<div id="<?=$form_id?>_wrapper" style="<?=$collapsable_style?>" class="comment-box overlay_container <?=$collapsable_class?> ">
    <p class="lead <?=$lead_class?>"><?=$form_lead?></p>
    <p class="form-description p-b-30 <?=$description_class?>"><?=$form_description?></p>
    <form
        id="<?=$form_id?>"
        name="<?=$form_name?>"
        class="form-horizontal m-t-20 <?=$collapsable_form?>"
        method="POST"
        action="<?=$action?>"
        <?=$attributes?>
        data-form-type="simple"
    >
        <?php
            foreach($elements as $element)
            {
                print $element;
            }
        ?>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>" />
    </form>
</div><!-- end form wrapper -->
