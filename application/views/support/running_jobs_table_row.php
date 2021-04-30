<?php
    if ( ! isset($job_name) ) $job_name = "";
    if ( ! isset($company) ) $company = "";
    if ( ! isset($user) ) $user = "";
    if ( ! isset($started) ) $started = "";
    if ( ! isset($recent_activity) ) $recent_activity = "";
    if ( ! isset($job_id) ) $job_id = "";

    $recent_activity = getStringValue($recent_activity); // Destroy any data not a string.


?>
<tr data-jobid="<?=$job_id?>">
    <td class="age"><?=getStringValue($started)?></td>
    <td><?=getStringValue($company)?></td>
    <td><?=getStringValue($job_name)?></td>
    <td class="status-message"><?=getStringValue($recent_activity)?></td>
</tr>
