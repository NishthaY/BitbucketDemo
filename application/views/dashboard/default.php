<?php
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($company_name) ) $company_name = "";
    if ( ! isset($dashboard_task) ) $dashboard_task = "";

    // Skip Month Processing Widget
    $skip_month_widget = new UIWidget("skip_month_widget");
    $skip_month_widget->setBody( SkipMonthProcessingWidget([$company_id]) );
    $skip_month_widget->setHref(base_url("widgettask/company/skip_month/COMPANYIDS"));
    $skip_month_widget = $skip_month_widget->render();

    // Getting Started widget
    $getting_started_widget = new UIWidget("getting_started_widget");
    $getting_started_widget->setBody( GettingStartedWidget() );
    $getting_started_widget->setHref(base_url("widgettask/getting_started"));
    $getting_started_widget = $getting_started_widget->render();

    // Upload Widget
    $upload_widget = new UIWidget("wizard_dashboard_widget");
    $upload_widget->setBody( WizardDashboardWidget() );
    $upload_widget->setHref(base_url("widgettask/wizard_dashboard"));
    $upload_widget->setTaskName("dashboard_task");
    $upload_widget->setStarting("DashboardWizardBeforeRefresh");
    $upload_widget->setCallback("DashboardWizardAfterRefresh");
    $upload_widget = $upload_widget->render();

    // Report Review Widget
    $report_review_widget = new UIWidget("report_review_widget");
    $report_review_widget->setBody( ReportReviewWidget() );
    $report_review_widget->setHref(base_url("widgettask/dashboard_report_review"));
    $report_review_widget->setTaskName("dashboard_task");
    $report_review_widget = $report_review_widget->render();

    // Recent Reports Widget
    $recent_reports_widget = new UIWidget("recent_reports_widget");
    $recent_reports_widget->setHref(base_url("dashboard/widget/recent_reports"));
    $recent_reports_widget = $recent_reports_widget->render();

    // Manual Adjustments
    // Add the manual adjustment widget, if needed.
    $manual_adjustment_widget = new UIWidget("manual_adjustment_widget");
    $manual_adjustment_widget->setBody( ManualAdjustmentWidget() );
    $manual_adjustment_widget->setHref(base_url("widgettask/manual_adjustment"));
    $manual_adjustment_widget = $manual_adjustment_widget->render();

    // Spend Details Widgettask_model
    $spend_details_widget = new UIWidget("spend_details_widget");
    $spend_details_widget->setHref(base_url("dashboard/widget/spenddetails"));
    $spend_details_widget = $spend_details_widget->render();

    // Spend Widget
    $spend_widget = new UIWidget("spend_widget");
    $spend_widget->setHref(base_url("dashboard/widget/spend"));
    $spend_widget = $spend_widget->render();

    // Spend YTD Widget
    $spend_ytd_widget = new UIWidget("spend_ytd_widget");
    $spend_ytd_widget->setHref(base_url("dashboard/widget/spend_ytd"));
    $spend_ytd_widget = $spend_ytd_widget->render();

    // Wash/Retro YTD Widget
    $spend_washretro_ytd_widget = new UIWidget("spend_washretro_ytd_widget");
    $spend_washretro_ytd_widget->setHref(base_url("dashboard/widget/spend_washretro_ytd"));
    $spend_washretro_ytd_widget = $spend_washretro_ytd_widget->render();

    // Wash/Retro Percent Widget
    $spend_washretro_percent_widget = new UIWidget("spend_washretro_percent_widget");
    $spend_washretro_percent_widget->setHref(base_url("dashboard/widget/spend_washretro_percentage"));
    $spend_washretro_percent_widget = $spend_washretro_percent_widget->render();

    // Finalization
    // Add the finalization confirmation widget if needed.
    $finalization_widget = new UIWidget("finalize_reports_widget");
    $finalization_widget->setHref(base_url("reports/finalize/{$company_id}"));
    $finalization_widget = $finalization_widget->render();

    // Welcome Widget
    $welcome_widget = new UIWidget("dashboard_welcome_widget");
    $welcome_widget->setBody( DashboardWelcomeWidget() );
    $welcome_widget->setHref(base_url("widgettask/dashboard_welcome"));
    $welcome_widget->setTaskName("dashboard_task");
    $welcome_widget = $welcome_widget->render();
    if ( HasExistingReportData() ) $welcome_widget = "";

    // Review Downloadable Reports
    $download_list_widget = new UIWidget("download_report_list_widget");
    $download_list_widget->setHref(base_url("reports/list/" . $company_id . "/CARRIER/DATE"));
    $download_list_widget = $download_list_widget->render();


?>
<div class="row form-header">
    <div class="col-sm-6">
        <h4 class="page-title"><?=$company_name?> Reporting Dashboard</h4>
    </div>
    <div class="col-sm-6">
        <?=$upload_widget?>
    </div>
</div>
<?=$report_review_widget?>
<?=$welcome_widget?>
<div class="row">
    <div class="col-sm-3"><?=$spend_widget?></div>
    <div class="col-sm-3"><?=$spend_ytd_widget?></div>
    <div class="col-sm-3"><?=$spend_washretro_ytd_widget?></div>
    <div class="col-sm-3"><?=$spend_washretro_percent_widget?></div>
</div>
<?=$recent_reports_widget?>
<?=$spend_details_widget?>

<?=$dashboard_task?>
<?=$finalization_widget?>
<?=$getting_started_widget?>
<?=$manual_adjustment_widget?>
<?=$download_list_widget?>
<?=$skip_month_widget?>
