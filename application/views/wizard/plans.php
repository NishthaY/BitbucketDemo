<?php
    if ( ! isset($page_header) ) $page_header = "";
    if ( ! isset($form_html) ) $form_html = "";
?>
<?=$page_header?>
<div class="row">
    <div class="col-sm-12">
        <p class="text-muted page-title-alt">
            We have found the following Medical plans in your file.<br>
            There does not appear to be a Premium column assigned to your file.  You will need to assign rates.
        </p>
    </div>
</div>
<?=$form_html?>
