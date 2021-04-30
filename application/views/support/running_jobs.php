<?php
    if ( ! isset($count) ) $count = "0";
?>
<div id="running_jobs" class="widget-bg-color-icon card-box fadeInDown animated clickable-widget">
    <div class="bg-icon bg-icon-success pull-left">
        <i class="md md-directions-walk text-success"></i>
    </div>
    <div class="text-right">
        <h3 class="text-dark"><b class="counter"><?=$count?></b></h3>
        <p class="text-muted">Running Jobs</p>
    </div>
    <div class="clearfix"></div>
</div>
