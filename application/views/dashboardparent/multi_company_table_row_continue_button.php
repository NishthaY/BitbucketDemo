<?php

    if ( ! isset($wf_row_description) ) $wf_row_description = "";
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($href) ) $href = "";
    if ( ! isset($landing) ) $landing = "";


    $show_button = true;
    if ( ! StartsWith( strtolower($wf_row_description), 'additional info needed for') ) $show_button = false;
    if ( GetStringValue($landing) === '' ) $show_button = false;

    $identifier = $company_id;
    $identifier_type = 'company';

?>

<?php
if ( ! $show_button )
{
    // Do not show a button
}
else
{
?>
    <a class="action-cell-parent-continue btn btn-primary btn-xs waves-light waves-effect" data-landing="<?=$landing?>" data-company-id="<?=$company_id?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>" type="button" formnovalidate="" href="<?=base_url("companies/widget/changeto/{$company_id}")?>"><i class="ion-arrow-right-c"></i> Continue</a>
<?php
}
?>