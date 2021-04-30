<?php
    if ( ! isset($queued) ) $queued = "";
    if ( ! isset($started) ) $started = "";
    if ( ! isset($ended) ) $ended = "";
    if ( ! isset($company) ) $company = "";
    if ( ! isset($user) ) $user = "";
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($user_id) ) $user_id = "";
    if ( ! isset($job_name) ) $job_name = "";
    if ( ! isset($message) ) $message = "";



?>
<div class="row">
    <div class="col-sm-2"><strong>Queued</strong></div>
    <div class="col-sm-10"><?=$queued?></div>
</div>
<div class="row">
    <div class="col-sm-2"><strong>Started</strong></div>
    <div class="col-sm-10"><?=$started?></div>
</div>
<div class="row">
    <div class="col-sm-2"><strong>Ended</strong></div>
    <div class="col-sm-10"><?=$ended?></div>
</div>
</p>
<div class="row">
    <div class="col-sm-2"><strong>Company</strong></div>
    <div class="col-sm-10"><?=$company?> ( <?=$company_id?> )</div>
</div>
<div class="row">
    <div class="col-sm-2"><strong>User</strong></div>
    <div class="col-sm-10"><?=$user?> ( <?=$user_id?> )</div>
</div>
</p>
<div class="row">
    <div class="col-sm-2"><strong>Job Name</strong></div>
    <div class="col-sm-10"><?=$job_name?></div>
</div>
</p>

