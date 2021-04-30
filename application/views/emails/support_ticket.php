<?php
    if ( !isset($hostname) ) $hostname = "www.advice2pay.com";
    if ( !isset($reason) ) $reason = "";
    if ( !isset($company_name) ) $company_name = "UNKNOWN";
    if ( !isset($ticket_id) ) $ticket_id = "";

    $ticket_id = new DateTime($ticket_id);
    $ticket_id = $ticket_id->format('m/d/Y H:i:s');


?>
<?=$company_name?> has encountered a runtime issue at approximately <?=$ticket_id?>.
<BR><BR>
<div>
    <code>
    <?=$message?>
    </code>
</div>
