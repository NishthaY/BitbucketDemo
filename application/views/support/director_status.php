<?php
    if ( !isset($max_job_runtime) ) $max_job_runtime = "unknown";
    if ( !isset($failure_check) ) $failure_check = "unknown";
    if ( !isset($reboot_check) ) $reboot_check = "unknown";
    if ( !isset($status) ) $status = "warning";
    if ( !isset($reboot_window_start) ) $reboot_window_start = "unknown";
    if ( !isset($reboot_window_end) ) $reboot_window_end = "unknown";
    if ( !isset($reboot_status) ) $reboot_status = "warning";

    $status_class = $status;
    if ( $status == "warning" ) $status_message = " Unknown ";
    if ( $status == "danger" ) $status_message = " Down ";
    if ( $status == "success" ) $status_message = " Up ";

    if ( $reboot_status == "success" )
    {
        $reboot_status_icon = "fa fa-check";
        $reboot_status_class = "success";
        $reboot_status_text = "working";
    }
    else if ( $reboot_status == "danger" )
    {
        $reboot_status_icon = "fa fa-times";
        $reboot_status_class = "danger";
        $reboot_status_text = "not working";
    }
    else if ( $reboot_status == "unavailable" )
    {
        $reboot_status_icon = "";
        $reboot_status_class = "normal";
        $reboot_status_text = "not supported";
    }
    else
    {
        $reboot_status_icon = "fa fa-question";
        $reboot_status_class = "warning";
        $reboot_status_text = "unknown";
    }


?>
<div class="card-box p-0">
    <div class="profile-widget text-center">
        <span class="p-r-20 pull-right"><h5 class="p-0 m-0"><i class="fa fa-circle m-0 p-0 text-<?=$status_class?>"></i><?=$status_message?></h5></span>
        <h2 class='p-t-20'>Queue Director </h2>

        <p class="m-t-10 text-muted text-left p-20">The queue director continuously runs looking for background jobs to execute.  It can process multiple jobs simultanously and it will monitor the status of those jobs and take action if needed.  Daily, the queue director will look for an opportune time to reboot itself to ensure reliable execution over time.</p>

        <div>
            <ul class="row list-unstyled widget-list mb-0 clearfix">
                <li class="col-md-4"><span><?=MAX_ASYNC_JOBS?></span>Max Async Jobs</li>
                <li class="col-md-4"><span><?=$max_job_runtime?></span>Max Job Runtime</li>
                <li class="col-md-4"><span><?=$failure_check?></span>Failure Check</li>
            </ul>
        </div>
        <div>
            <ul class="row list-unstyled widget-list mb-0 clearfix">
                <li class="col-md-4"><span class=""><?=$reboot_window_start?> - <?=$reboot_window_end?></span>Reboot Window</li>
                <li class="col-md-4"><span class=""><i class="<?=$reboot_status_icon?> text-<?=$reboot_status_class?> m-t-0"></i> <?=$reboot_status_text?> </span>Reboot Status</li>
                <li class="col-md-4"><span class=""><?=$reboot_check?></span>Reboot Check</li>
            </ul>
        </div>
    </div>
</div>
