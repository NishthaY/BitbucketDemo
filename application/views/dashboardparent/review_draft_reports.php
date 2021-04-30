<?php

    if ( ! isset($company_id) ) $company_id = "";

    $this->load->helper("wizard");
    $this->load->helper("dashboard");
    $render_widget = false;
    if ( IsAuthenticated("parent_company_read,company_read", 'company', $company_id ) && IsReportGenerationStepComplete($company_id) && ! IsFinalizingReports($company_id) ) $render_widget = true;

    $report_data = ReportingReviewData($company_id);

    // Figure out how many warnings we have generated and of what type.
    $counts = ReportingReviewWarningCounts($company_id);
    $warning_confirm_count = GetArrayStringValue("Confirm", $counts);
    $warning_notice_count = GetArrayStringValue("Notice", $counts);

    $render_actions = false;
    if ( IsAuthenticated("company_write") ) $render_actions = true;

    $import_date = ReplaceFor(GetUploadDate($company_id), "/", "-");

?>

<?php
if ( $render_widget ) {
?>

    <tr class="">
        <td class="no-border"></td>
        <td class="no-border"></td>
        <td class="no-border" colspan="2">
            <div class="parent-report-review-flex-parent">
                <div class="parent-report-review-flex-a">
                    <table
                            id="parent_report_review_<?=$company_id?>"
                            class="parent-report-review table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Carrier</th>
                            <th>Amount</th>
                            <th>Adjustments</th>
                            <th>Adjusted Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach($report_data as $item)
                        {
                            ?>
                            <tr>
                                <td><?=GetArrayStringValue("Carrier", $item);?></td>
                                <td><?=GetArrayStringValue("Total", $item);?></td>
                                <td><?=GetArrayStringValue("Adjustments", $item);?></td>
                                <td><?=GetArrayStringValue("BalanceDue", $item);?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>

                    </table>
                    <script>InitParentReportReviewTable('parent_report_review_<?=$company_id?>');</script>
                </div>
                <div class="parent-report-review-flex-b">
                    <div class="process-report-box">
                        <div class="title text-center">Process Report</div>
                        <div class="line"><?=RenderViewAsString("dashboardparent/critical_warning_link", array('count' => $warning_confirm_count, 'company_id' => $company_id))?></div>
                        <div class="line"><?=RenderViewAsString("dashboardparent/review_notices_link", array('count' => $warning_notice_count, 'company_id' => $company_id))?></div>
                    </div>
                </div>
            </div>

        </td>
    </tr>

<?php
}
