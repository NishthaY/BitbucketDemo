<?php

    if ( ! isset($companies_rollback_widget) ) $companies_rollback_widget = "";
    if ( ! isset($companies_edit_widget) ) $companies_edit_widget = "";
    if ( ! isset($companies_changeto_widget) ) $companies_changeto_widget = "";
    if ( ! isset($failed_jobs_widget) ) $failed_jobs_widget = "";
    if ( ! isset($waiting_jobs_widget) ) $waiting_jobs_widget = "";
    if ( ! isset($running_jobs_widget) ) $running_jobs_widget = "";
    if ( ! isset($running_jobs_table_widget) ) $running_jobs_table_widget = "";
    if ( ! isset($failed_jobs_table_widget) ) $failed_jobs_table_widget = "";
    if ( ! isset($waiting_jobs_table_widget) ) $waiting_jobs_table_widget = "";
    if ( ! isset($job_details_widget) ) $job_details_widget = "";
    if ( ! isset($admin_dashboard_task) ) $admin_dashboard_task = "";

    // SUPPORT: Recent ChangeTo Widget
    $recent_widget = new UIWidget("recent_changeto");
    $recent_widget->setHref(base_url("dashboard/widget/recent_changeto"));
    $recent_widget->setCallback("InitAdminDashboardChangeToHistoryTable");
    $recent_widget = $recent_widget->render();
    
    // SUPPORT: Rollback Company Confirmation Dialog.
    $companies_rollback_widget = new UIWidget("rollback_company_widget");
    $companies_rollback_widget->setHref(base_url("companies/widget/rollback"));
    $companies_rollback_widget = $companies_rollback_widget->render();

    // SUPPORT: Rollback CompanyParent Confirmation Dialog.
    $companyparent_rollback_widget = new UIWidget("rollback_companyparent_widget");
    $companyparent_rollback_widget->setHref(base_url("parents/widget/rollback"));
    $companyparent_rollback_widget = $companyparent_rollback_widget->render();

    // SUPPORT: Edit Company Data Dialog
    $companies_edit_widget = new UIWidget("edit_company_widget");
    $companies_edit_widget->setHref(base_url("companies/widget/edit"));
    $companies_edit_widget = $companies_edit_widget->render();

    // SUPPORT: Edit CompanyParent Data Dialog
    $parents_edit_widget = new UIWidget("edit_parent_widget");
    $parents_edit_widget->setHref(base_url("parents/widget/edit"));
    $parents_edit_widget = $parents_edit_widget->render();

    // SUPPORT: Change To Company Data Dialog
    $companies_changeto_widget = new UIWidget("changeto_company_widget");
    $companies_changeto_widget->setHref(base_url("companies/widget/changeto"));
    $companies_changeto_widget = $companies_changeto_widget->render();

    // Change To CompanyParent Form
    $parents_changeto_widget = new UIWidget("changeto_parent_widget");
    $parents_changeto_widget->setHref(base_url("parents/widget/changeto"));
    $parents_changeto_widget = $parents_changeto_widget->render();

    // SUPPORT: Failed Jobs Widget
    $failed_jobs_widget = new UIWidget("failed_jobs_widget");
    $failed_jobs_widget->setHref(base_url("support/widget/jobs/failed"));
    $failed_jobs_widget->setTaskName('admin_dashboard_task');
    $failed_jobs_widget = $failed_jobs_widget->render();

    // SUPPORT: Waiting Jobs Widget
    $waiting_jobs_widget = new UIWidget("waiting_jobs_widget");
    $waiting_jobs_widget->setHref(base_url("support/widget/jobs/waiting"));
    $waiting_jobs_widget->setTaskName('admin_dashboard_task');
    $waiting_jobs_widget = $waiting_jobs_widget->render();

    // SUPPORT: Running Jobs Widget
    $running_jobs_widget = new UIWidget("running_jobs_widget");
    $running_jobs_widget->setHref(base_url("support/widget/jobs/running"));
    $running_jobs_widget->setTaskName('admin_dashboard_task');
    $running_jobs_widget = $running_jobs_widget->render();

    // SUPPORT: Running Jobs Table Widget
    $running_jobs_table_widget = new UIWidget("running_jobs_table_widget");
    $running_jobs_table_widget->setHref(base_url("support/widget/jobs/running/data"));
    $running_jobs_table_widget->setTaskName('admin_dashboard_task');
    $running_jobs_table_widget->setCallback("InitAdminDashboardRunningJobsTable");
    $running_jobs_table_widget = $running_jobs_table_widget->render();

    // SUPPORT: Failed Jobs Table Widget
    $failed_jobs_table_widget = new UIWidget("failed_jobs_table_widget");
    $failed_jobs_table_widget->setHref(base_url("support/widget/jobs/failed/data"));
    $failed_jobs_table_widget = $failed_jobs_table_widget->render();

    // SUPPORT: Waiting Jobs Table Widget
    $waiting_jobs_table_widget = new UIWidget("waiting_jobs_table_widget");
    $waiting_jobs_table_widget->setHref(base_url("support/widget/jobs/waiting/data"));
    $waiting_jobs_table_widget = $waiting_jobs_table_widget->render();

    // SUPPORT: Job Details Dialog
    $job_details_widget = new UIWidget("job_details_widget");
    $job_details_widget->setHref(base_url("support/jobs/detail"));
    $job_details_widget = $job_details_widget->render();

    //TASK: admin_dashboard_task
    $task_config = $this->widgettask_model->task_config('admin_dashboard_task');
    $admin_dashboard_task = new UIBackgroundTask('admin_dashboard_task');
    $admin_dashboard_task->setHref(base_url("widgettask/admin_dashboard_task"));
    $admin_dashboard_task->setRefreshMinutes(getArrayIntValue("refresh_minutes", $task_config));
    $admin_dashboard_task->setDebug(getArrayStringValue("debug", $task_config));
    $admin_dashboard_task->setInfo(getArrayStringValue("info", $task_config));
    $admin_dashboard_task = $admin_dashboard_task->render();


?>
<?=$admin_dashboard_task?>
<div id="dashboard_type" class="hidden" data-type="admin"></div>
<div class="row form-header">
    <div class="col-sm-6">
        <h4 class="page-title">Advice2Pay Administrative Dashboard</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Quick Look</li>
            <?php if ( IsAuthenticated( ) ) { ?>
                <li class="breadcrumb-item"><a href="<?=base_url();?>dashboard/tools">Developer Tools</a></li>
            <?php } ?>
            <?php if ( IsAuthenticated( ) ) { ?>
                <li class="breadcrumb-item"><a href="<?=base_url();?>dashboard/security">Security Tools</a></li>
            <?php } ?>
        </ol>
    </div>
    <div class="col-sm-6"></div>
</div>
<div class="row">
    <div class="col-lg-12">
        <?=$recent_widget?>
        <?=$companies_rollback_widget?>
        <?=$companyparent_rollback_widget?>
        <?=$companies_edit_widget?>
        <?=$parents_edit_widget?>
        <?=$companies_changeto_widget?>
        <?=$parents_changeto_widget?>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <?=$running_jobs_widget?>
        <?=$waiting_jobs_widget?>
        <?=$failed_jobs_widget?>
    </div>
    <div class="col-lg-8">
        <?=$running_jobs_table_widget?>
        <?=$waiting_jobs_table_widget?>
        <?=$failed_jobs_table_widget?>
        <?=$job_details_widget?>
    </div>
</div>