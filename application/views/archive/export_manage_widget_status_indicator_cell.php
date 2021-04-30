<?php
    if ( ! isset($status) ) $status = "";

    $class = 'status-indicator';
    if ( strtoupper($status) === 'COMPLETE' )       $class = 'status-indicator-attention';
    if ( strtoupper($status) === 'FAILED' )         $class = 'status-indicator-alert';
    if ( strtoupper($status) === 'IN_PROGRESS' )    $class = 'status-indicator-working';
    if ( strtoupper($status) === 'NO_RESULTS' )     $class = 'status-indicator-noresults';
?>

<td style="width: 30px;"><span><i class="md md-border-circle <?=$class?> "></i></span> </td>
