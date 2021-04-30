<?php
    if ( !isset($hostname) ) $hostname = "www.advice2pay.com";
    if ( ! isset($key_count) ) $key_count = "";
    if ( ! isset($message) ) $message = "";
?>
A security key has been consumed but we were unable to create a new one automatically on <?=$hostname?>
<BR>
<BR>
There are <?=$key_count?> keys left in the pool.  You may add more via the developer dashboard.  Should the pool become
exhausted, new company and parent company creations will fail.
<BR><BR>
<div>
    <code>
        <?=$message?>
    </code>
</div>
