<?php
    if ( ! isset($count) ) $count = 0;

    $disabled = "";
    if ( $count == 0 )  $disabled = "disabled";
?>
<?php
if ( $count == 0 )
{
    ?>
    <i class="glyphicon glyphicon-question-sign <?=$disabled?>"></i> 0 Review Notices
    <?php
}
else
{
    ?>
    <i class="glyphicon glyphicon-question-sign <?=$disabled?>"></i> <a data-companyid="<?=$company_id?>" data-type="notice" class="parent-report-review-review-notice-link"><?=$count?> Review Notices</a>
    <?php
}

?>

