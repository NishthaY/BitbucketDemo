<?php
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($company_name) ) $company_name = "";
    if ( ! isset($date_description) ) $date_description = "";
    if ( ! isset($data) ) $data = [];
    if ( ! isset($not_eligible) ) $not_eligible = [];


?>

<?php
// Add a waring bubble to indicate which of their elections could not be facilitated.
if ( ! empty($not_eligible) )
{
    ?>
    <div class="alert alert-a2p " role="alert" style="">
        <span class="alert-message">
            The following companies were not eligible for skip month processing this month.
            <p>
            <ul>
                <?php
                foreach($not_eligible as $item)
                {
                    $company_id = getArrayStringValue('company_id', $item);
                    $company_name = getArrayStringValue('company_name', $item);
                    $date_description = getArrayStringValue('date_description', $item);
                    $reason = getArrayStringValue('reason', $item);
                    print "<li>{$company_name} - {$date_description} ( <i>{$reason}</i> )</li>";
                }
                ?>
            </ul>
            </p>
        </span>
    </div>
    <?php
}
?>

<p>
Activating skip month processing will automatically start the data import process using the data provided for the prior finalized month.
</p>




<?php
if ( ! empty($data) )
{
    ?>
    Are you sure you want to activate skip month processing on the following companies?
    <ul>
    <?php
    foreach($data as $item)
    {
        $company_id = getArrayStringValue('company_id', $item);
        $company_name = getArrayStringValue('company_name', $item);
        $date_description = getArrayStringValue('date_description', $item);
        print "<li>{$company_name} - {$date_description}</li>";
    }
    ?>

    <?php
}
else
{
    ?>
        Are you sure you want to activate skip month processing?
    <?php
}
?>


