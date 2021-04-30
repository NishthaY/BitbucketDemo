<?php
    if ( !isset($hostname) ) $hostname = "www.advice2pay.com";
    if ( ! isset($single_use_auth_code) ) $single_use_auth_code = "";
?>
Your temporary Advice2Pay password has been generated: <br><br>&nbsp;&nbsp;&nbsp;<code><?=$single_use_auth_code?></code><br><br>Please return to <?=$hostname?> at your convenience to complete the password reset.
