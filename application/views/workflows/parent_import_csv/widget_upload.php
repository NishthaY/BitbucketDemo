<?php
    if ( ! isset($upload_attributes) ) $upload_attributes = array();
    if ( ! isset($upload_inputs) ) $upload_inputs = array();

    $attributes = "";
    if ( ! empty($upload_attributes) ) {
        foreach($upload_attributes as $key=>$value){
            $attributes .= "{$key}='{$value}' ";
        }
    }

    // NOTE: This widget is contained in a "WizardForm" header.
    // That means we are in a form.  However, the S3 upload form
    // will not work while in a form.  Rather than re-architect everything
    // I will just close and open the form before I do my work.  We
    // don't really use the form, except maybe for indicating CSS hooks.

?>
</form> <!-- see NOTE: above -->
<form id="upload_form" <?=$attributes?> >
    <?php
    if ( ! empty($upload_inputs) )
    {
        foreach($upload_inputs as $key=>$value)
        {
            echo "<input type='hidden' name='{$key}' value='{$value}' >\n";
        }
    }
    ?>
    <button data-href="parents/upload/save/parent_import_csv" id="upload_button" class="pull-right btn w-lg btn-lg waves-effect waves-light btn-default" type="button" formnovalidate data-style="zoom-in">Import Bulk Data</button>
    <input id="upload_button_browse" type="file" style="display:none;" >
</form>
<form> <!-- see NOTE: above -->