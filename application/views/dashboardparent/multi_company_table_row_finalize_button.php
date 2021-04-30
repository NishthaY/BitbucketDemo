<?php

    if ( ! isset($wf_row_description) ) $wf_row_description = "";
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($import_date) ) $import_date = "";
    if ( ! isset($carriers) ) $carriers = array();

    $identifier = $company_id;
    $identifier_type = 'company';

    $draft_reports_ready = true;
    if ( ! StartsWith( strtoupper($wf_row_description), 'DRAFT REPORTS GENERATED') ) $draft_reports_ready = false;
    if ( empty($carriers) ) $draft_reports_ready = false;
    if ( $enabled !== 't' ) $draft_reports_ready = false;

?>

<?php
if ( ! $draft_reports_ready )
{
    // Do not show a button if reports are not ready.
}
else
{
    ?>
    <a class="action-cell-parent-finalize btn btn-primary btn-xs waves-light waves-effect" data-import-date="<?=$import_date?>" data-company-id="<?=$company_id?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>" type="button" formnovalidate=""><i class="glyphicon glyphicon-lock m-r-5"></i> Finalize</a>
    <?php
}
?>
