<?php
    if ( ! isset( $best_guess ) ) $best_guess = false;

    $class = "hidden";
    if ($best_guess) $class = "";
?>
<div id="best_guess" class="p-t-10 p-b-10 <?=$class?>"><small>Based on previous elections, we have pre-populated the anniversary, retro and wash rule settings for you.</small></div>
