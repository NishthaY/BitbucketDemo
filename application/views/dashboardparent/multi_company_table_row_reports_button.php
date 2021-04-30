<?php

    if ( ! isset($wf_row_description) ) $wf_row_description = "";
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($import_date) ) $import_date = "";
    if ( ! isset($carriers) ) $carriers = array();

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
else if ( count($carriers) == 1 )
{
    // Report button is a standard button and references the only carrier in the collection.
    foreach($carriers as $carrier_desc=>$carrier_id)
    {
        ?>
        <a class="action-cell-parent-reportreview btn btn-primary btn-xs waves-light waves-effect" data-import-date="<?=$import_date?>" data-company-id="<?=$company_id?>" data-carrier="<?=$carrier_id?>"  type="button" formnovalidate=""><i class="glyphicon glyphicon-file m-r-5"></i> Reports</a>
        <?php
        break;
    }
}
else
{
    // Report button is a dropdown with multiple carriers.
    ?>
    <span class="dropdown parent-report-review-button-container">
        <a class="action-cell-parent-reportreview btn btn-primary btn-xs waves-light waves-effect" type="button" data-toggle="dropdown" formnovalidate=""><i class="glyphicon glyphicon-file m-r-5"></i> Reports</a>
        <ul role="menu" class="dropdown-menu">
            <?php
            foreach($carriers as $carrier_desc=>$carrier_id)
            {
                $carrier_desc = GetStringValue($carrier_desc);
                $carrier_id = GetStringValue($carrier_id);
                ?><li data-import-date="<?=$import_date?>" data-company-id="<?=$company_id?>" data-carrier="<?=$carrier_id?>"><a href="#"><?=$carrier_desc?></a> </li><?php
            }
            ?>
        </ul>
    </span>
    <?php
}
?>
