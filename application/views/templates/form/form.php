<?php
    if ( ! isset($form_title) ) $form_title = "";
    if ( ! isset($form_name) ) $form_name = "";
    if ( ! isset($form_id) ) $form_id = "";
    if ( ! isset($action) ) $action = "";
    if ( ! isset($elements) ) $elements = array();
    if ( ! isset($attributes) ) $attributes = "";
    if ( ! isset($collapsable) ) $collapsable = false;
    if ( ! isset($upload_attributes) ) $upload_attributes = array();
    if ( ! isset($upload_inputs) ) $upload_inputs = array();


    $collapsable_style = "";
    if ( $collapsable ) $collapsable_style = "display:none";

    $collapsable_class = "";
    if ( $collapsable ) $collapsable_class = "collapsable";

    $collapsable_form = "";
    if ( $collapsable ) $collapsable_form = "collapsable-form";


?>
<div class="alert alert-success hidden" role="alert"><span class="alert-message"></span></div>
<div id="<?=$form_id?>_wrapper" style="<?=$collapsable_style?>" class="comment-box overlay_container <?=$collapsable_class?> ">
    <div class="panel panel-color panel-primary" >
        <div class="panel-heading">
            <h3 class="panel-title"><?=$form_title?></h3>
        </div>
        <div class="panel-body">
            <form
                id="<?=$form_id?>"
                name="<?=$form_name?>"
                class="form-horizontal <?=$collapsable_form?>"
                method="POST"
                action="<?=$action?>"
                data-form-type="standard"
                <?=$attributes?>
                <?php if ( ! empty($upload_attributes) ) { foreach($upload_attributes as $key=>$value){ echo "{$key}='{$value}' "; } }?>
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
    </div>
</div>
