<?php

if ( ! isset ( $warnings) ) $warnings = array();
if ( ! isset ( $message ) ) $message = "";
if ( ! isset($company_id) ) $company_id = "";
if ( ! isset($upload_date) ) $upload_date = GetUploadDateDescription($company_id);
if ( ! isset($critical) ) $critical = true;

$top_message = "The Following Items are Blocking Finalizing of This Company:";
if ( ! $critical ) $top_message = "The Following Items Should Be Noted Prior to Finalizing This Company:";

$no_warnings = "";
if ( count($warnings) > 0 ) $no_warnings = "hidden";

$has_warnings = "";
if ( count($warnings) == 0 ) $has_warnings = "hidden";

?>
<div class=" <?=$has_warnings?>">

    <div class="clearfix">
        <h4 class="m-t-0 header-title pull-left">
            <b><?=$top_message?></b>
        </h4>
        <a class="btn btn-xs btn-primary waves-effect pull-right m-b-5" type="button" formnovalidate="" href="<?=base_url("download/issues/{$company_id}");?>">Download All Issues</a>
    </div>


    <table id="report_review_warning_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead class="hidden">
        <tr>
            <td>&nbsp;</td>
        </tr>
        </thead>
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
