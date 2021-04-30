<?php
    if ( ! isset($count) ) $count = "0";
?>
<div id="waiting_jobs" class="widget-bg-color-icon card-box fadeInDown animated clickable-widget">
    <div class="bg-icon bg-icon-warning pull-left">
        <i class="md md-warning text-warning"></i>
    </div>
    <div class="text-right">
        <h3 class="text-dark"><b class="counter"><?=$count?></b></h3>
        <p class="text-muted">Waiting Jobs</p>
    </div>
    <div class="clearfix"></div>
</div>
