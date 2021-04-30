<?php
    if ( ! isset($form_title) ) $form_title = "";
    if ( ! isset($form_name) ) $form_name = "";
    if ( ! isset($form_id) ) $form_id = "";
    if ( ! isset($action) ) $action = "";
    if ( ! isset($elements) ) $elements = array();
    if ( ! isset($attributes) ) $attributes = "";

?>
<div class="alert alert-success hidden" role="alert"><span class="alert-message"></span></div>
<div id="<?=$form_id?>_wrapper" class="comment-box overlay_container ">
    <form
        id="<?=$form_id?>"
        name="<?=$form_name?>"
        class="form-horizontal"
        <?=$attributes?>
        method="POST"
        action="<?=$action?>"
        data-form-type="standard"
    >
        <?php
            $count = 0;
            foreach($elements as $element)
            {
                if ($count == 1 ) print '<div class="panel panel-color panel-primary" ><div class="panel-body">';
                print $element;
                $count++;
            }
            print '</div></div>';
        ?>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>" />
    </form>
</div>
