<?php
if ( ! isset($company_id) ) $company_id = "";
if ( ! isset($company_name) ) $company_name = "";
if ( ! isset($date_description) ) $date_description = "";
if ( ! isset($data) ) $data = [];
if ( ! isset($not_eligible) ) $not_eligible = [];


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
