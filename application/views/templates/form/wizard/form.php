<?php
    if ( ! isset($form_title) ) $form_title = "";
    if ( ! isset($form_name) ) $form_name = "";
    if ( ! isset($form_id) ) $form_id = "";
    if ( ! isset($action) ) $action = "";
    if ( ! isset($elements) ) $elements = array();
    if ( ! isset($attributes) ) $attributes = "";
    if ( ! isset($collapsable) ) $collapsable = false;
    if ( ! isset($min_height) ) $min_height = "0";
    if ( ! isset($upload_attributes) ) $upload_attributes = array();
    if ( ! isset($upload_inputs) ) $upload_inputs = array();
    if ( ! isset ( $form_lead) ) $form_lead = "";
	if ( ! isset ( $form_description) ) $form_description = "";

	$lead_class = "hidden";
	if ( $form_lead != "" ) $lead_class = "";

	$description_class = "hidden";
	if ( $form_description != "" ) $description_class = "";

    // Turn our collection of upload_attributes into an HTML string.
    $upload_attributes_html = "";
    foreach($upload_attributes as $key=>$value)
    {
        $upload_attributes_html .= " {$key}='{$value}' ";
    }

    $collapsable_style = "";
    if ( $collapsable ) $collapsable_style = "display:none";

    $collapsable_class = "";
    if ( $collapsable ) $collapsable_class = "collapsable";

    $collapsable_form = "";
    if ( $collapsable ) $collapsable_form = "collapsable-form";


?>
<div id="<?=$form_id?>_wrapper" style="<?=$collapsable_style?>" class="comment-box overlay_container <?=$collapsable_class?> m-b-0">
    <p class="lead <?=$lead_class?>"><?=$form_lead?></p>
    <p class="form-description p-b-30 <?=$description_class?>"><?=$form_description?></p>
    <form
        id="<?=$form_id?>"
        name="<?=$form_name?>"
        class="form-horizontal <?=$collapsable_form?>"
        <?=$attributes?>
        <?php
        if( $upload_attributes_html == "" )
        {
            ?>
            method="POST"
            action="<?=$action?>"
            data-form-type="standard"
            <?php
        }else{
            ?>
            <?=$upload_attributes_html?>
            <?php
        }
        ?>
    >

        <?php
            foreach($elements as $element)
            {
                print $element;
            }
        ?>
        <?php
            if ( ! empty($upload_inputs) )
            {
                foreach($upload_inputs as $key=>$value)
                {
                    echo "<input type='hidden' name='{$key}' value='{$value}' >\n";
                }
            }
        ?>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>" />
    </form>
</div>
<div id="form_bottom_padding" style="min-height: <?=$min_height?>px;" class=""></div>
