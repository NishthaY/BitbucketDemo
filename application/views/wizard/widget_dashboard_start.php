<?php

    if ( ! isset($identifier) ) $identifier = "";
    if ( ! isset($identifier_type) ) $identifier_type = "";

    $display = GetUploadDateDescription();
    if ( $display == "" ) {
        $display = "Upload Data";
    }else{
        $display = "Upload {$display}";
    }

    $start_button_class = "hidden";
    if ( ! HasExistingReportData() ) $start_button_class = "";

    $skip_button_class = "hidden";
    if ( IsSkipMonthProcessingAllowed($identifier, $identifier_type) === TRUE ) $skip_button_class = "";
?>
<div class='' style='margin-top: 10px;'>

    <form id="upload_form" <?php if ( ! empty($upload_attributes) ) { foreach($upload_attributes as $key=>$value){ echo "{$key}='{$value}' "; } }?> >
        <?php
            if ( ! empty($upload_inputs) )
            {
                foreach($upload_inputs as $key=>$value)
                {
                    echo "<input type='hidden' name='{$key}' value='{$value}' >\n";
                }
            }
        ?>
        <button id="upload_button" class="ladda-button a2p-spinner-button pull-right btn w-lg btn-primary btn-lg waves-effect waves-light" type="button" formnovalidate data-style="zoom-in"><i class='ion-arrow-right-c'></i> <?=$display?></button>
        <input id="upload_button_browse" type="file" style="display:none;" >
    </form>
    <button
        id="start_button"
        type="button"
        class="btn w-lg btn-white btn-lg waves-effect waves-light m-r-15 pull-right <?=$start_button_class?>"
        data-href=""
        formnovalidate
    >Start Month</button>
    <button
            id="skip_button"
            type="button"
            class="btn w-lg btn-white btn-lg waves-effect waves-light m-r-15 pull-right <?=$skip_button_class?>"
            data-href=""
            formnovalidate
    >Skip Month</button>
</div>
