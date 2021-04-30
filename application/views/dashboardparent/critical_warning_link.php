<?php
    if ( ! isset($count) ) $count = 0;
    if ( ! isset($company_id) ) $company_id = 0;

    $disabled = "";
    if ( $count == 0 )  $disabled = "disabled";
?>
<?php
if ( $count == 0 )
{
    ?>
    <i class="md md-warning <?=$disabled?>"></i> <?=$count?> Critical Warnings
    <?php
}
else
{
    ?>
    <i class="md md-warning <?=$disabled?>"></i> <a data-companyid="<?=$company_id?>" data-type="critical" class="parent-report-review-critical-warning-link"> <?=$count?> Critical Warnings</a>
    <?php
}

?>

