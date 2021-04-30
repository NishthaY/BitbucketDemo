<?php
    if ( ! isset($upload_date) ) $upload_date = GetUploadDateDescription();
    if ( ! isset ( $warnings) ) $warnings = array();
    if ( ! isset ( $critical) ) $critical = true;

    $message = "The Following Items are Blocking Finalizing of This Company:";
    if ( ! $critical ) $message = "The Following Items Should Be Noted Prior to Finalizing This Company:";

    $no_warnings = "";
    if ( count($warnings) > 0 ) $no_warnings = "hidden";

    $has_warnings = "";
    if ( count($warnings) == 0 ) $has_warnings = "hidden";
?>
<div class="confirmation-div <?=$no_warnings?>">
    Are you sure you want to finalize the <?=$upload_date?> reports?
</div>
<div class="confirmation-div <?=$has_warnings?>">
    Are you sure you want to finalize the <?=$upload_date?> reports?<BR><BR>

    <h4 class="m-t-0 header-title">
        <b><?=$message?></b>
    </h4>

    <table id="report_review_warning_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <tbody>
        <?php
        if ( ! empty($warnings) )
        {
            foreach($warnings as $item)
            {
                ?>
                <tr>
                    <td><?=getArrayStringValue("Issue", $item);?></td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>

</div>
