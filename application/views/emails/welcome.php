<?php
    if ( !isset($hostname) ) $hostname = "www.advice2pay.com";
    if ( ! isset($single_use_auth_code) ) $single_use_auth_code = "";
    if ( ! isset($name) ) $name = "";
?>
Your Advice2Pay account for <?=$name?> has been created and your temporary password is:<br><br>&nbsp;&nbsp;&nbsp;<code><?=$single_use_auth_code?></code><br><br>Please visit <?=$hostname?> at your convenience to get started.
