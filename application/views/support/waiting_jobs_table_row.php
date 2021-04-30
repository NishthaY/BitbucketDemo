<?php
    if ( ! isset($job_name) ) $job_name = "";
    if ( ! isset($company) ) $company = "";
    if ( ! isset($user) ) $user = "";
    if ( ! isset($requested) ) $requested = "";

?>
<tr>
    <td><?=getStringValue($requested)?></td>
    <td><?=getStringValue($company)?></td>
    <td><?=getStringValue($job_name)?></td>
</tr>
