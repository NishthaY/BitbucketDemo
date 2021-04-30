<?php
    if ( ! isset($form_title) ) $form_title = "";
    if ( ! isset($form_name) ) $form_name = "";
    if ( ! isset($form_id) ) $form_id = "";
    if ( ! isset($action) ) $action = "";
    if ( ! isset($elements) ) $elements = array();
    if ( ! isset($attributes) ) $attributes = "";
    if ( ! isset ( $form_lead) ) $form_lead = "";
	if ( ! isset ( $form_description) ) $form_description = "";
	if ( ! isset ( $form_breadcrumb ) ) $form_breadcrumb = "";

	$lead_class = "hidden";
	if ( $form_lead != "" ) $lead_class = "";

	$description_class = "hidden";
	if ( $form_description != "" ) $description_class = "";
	
?>


<div id="<?=$form_id?>_wrapper" class="comment-box overlay_container ">
    <div id="<?=$form_id?>_modal" class="uiform-modal">
        <button type="button" class="close" onclick="Custombox.close();">
            <span>&times;</span><span class="sr-only">Close</span>
        </button>
        <h4 class="custom-modal-title"><?=$form_title?></h4>
        <div class="custom-modal-text text-left">
            <div class="alert alert-success hidden" role="alert"><span class="alert-message"></span></div>
            <?=$form_breadcrumb?>
            <p class="lead <?=$lead_class?>"><?=$form_lead?></p>
			<p class="form-description p-b-30 <?=$description_class?>"><?=$form_description?></p>
            <form
                role="form"
                id="<?=$form_id?>"
                name="<?=$form_name?>"
                class="" method="POST"
                action="<?=$action?>"
                <?=$attributes?>
                data-form-type="modal"
            >

                <?php
                    foreach($elements as $element)
                    {
                        print $element;
                    }
                ?>
                <input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>" />
            </form>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
