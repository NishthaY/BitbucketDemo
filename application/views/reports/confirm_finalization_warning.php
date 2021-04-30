<?php
    if ( ! isset($warnings) ) $warnings = array();
    if ( ! isset($company_name) ) $company_name = "";
    if ( ! isset($upload_desc) ) $upload_desc = "";
    if ( ! isset($critical) ) $critical = false;

    $message = "The Following Items are Blocking Finalizing of This Company:";
    if ( ! $critical ) $message = "The Following Items Should Be Noted Prior to Finalizing This Company:";

?>
<?php
if ( ! empty($warnings) )
{
    ?>
    <div class="confirmation-div p-t-20">
        <h4 class="m-t-0 header-title">
            <b><?=$company_name?> - <?=$upload_desc?></b><BR>
            <?=$message?>
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
    <?php
}
?>

