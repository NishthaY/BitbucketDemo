<?php
    if ( ! isset($details) ) $details = "";

    $column_name = GetArrayStringValue("ColumnName", $details);
    if ( $column_name === '' ) $column_name = "ssn";

    $additional_rules = "";
    if ( GetArrayStringValue("ColumnName", $details) === 'employee_ssn' ) $additional_rules = "Single digit SSN formats will be treated as if they were the empty string.";
    if ( GetArrayStringValue("ColumnName", $details) === 'ssn' ) $additional_rules = "Seven digit or less SSN formats will be treated as if they were the empty string.";

?>
<div>
    The following SSN formats are currently supported.
    <ul>
        <li>###-##-####</li>
        <li>#########</li>
    </ul>
    The following SSN formats will be modified to include leading zeros for your convenience.
    <ul>
        <li>#-##-####</li>
        <li>##-##-####</li>
        <li>#######</li>
        <li>########</li>
    </ul>
    <?=$additional_rules?>
</div>
