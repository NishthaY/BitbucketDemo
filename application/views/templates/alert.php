<?php
    if ( ! isset($message) ) $message = "";
    if ( ! isset($type) ) $type == "success";
    
    $class = "alert-info";
    if ( strtoupper($type) == "SUCCESS" ) $class = "alert-success";
    if ( strtoupper($type) == "DANGER" ) $class = "alert-danger";
    if ( strtoupper($type) == "A2P" ) $class = "alert-a2p";
?>
<div class="alert <?=$class?>" role="alert"><span class="alert-message"><?=$message?></span></div>