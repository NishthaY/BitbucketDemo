<?php
    if ( ! isset($count) ) $count = "0";
?>
<div id="failed_jobs" class="widget-bg-color-icon card-box fadeInDown animated clickable-widget">
    <div class="bg-icon bg-icon-pink pull-left">
        <i class="md md-bug-report text-pink"></i>
    </div>
    <div class="text-right">
        <h3 class="text-dark"><b class="counter"><?=$count?></b></h3>
        <p class="text-muted">Failed Jobs</p>
    </div>
    <div class="clearfix"></div>
</div>
