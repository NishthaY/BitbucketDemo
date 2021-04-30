<?php

    if ( ! isset($company_id) ) $company_id = null;
    if ( ! isset($company_id) ) $company_id = GetSessionValue("company_id");

    $this->load->helper("wizard");
    $this->load->helper("dashboard");
    $render_widget = false;
    if ( IsAuthenticated("company_read" ) && IsReportGenerationStepComplete() && ! IsFinalizingReports() ) $render_widget = true;

    $report_data = ReportingReviewData();
    $warning_data = ReportingReviewWarningData();
    $upload_date_description = GetUploadDateDescription();

    $warning_class = "hidden";
    if ( ! empty($warning_data ) ) $warning_class = "";


    $render_actions = false;
    if ( IsAuthenticated("company_write") ) $render_actions = true;

    $import_date = ReplaceFor(GetUploadDate($company_id), "/", "-");

?>

<?php
if ( $render_widget ) {
?>



    <div style="display:none;" class="row review-draft-reports-container">
        <div class="<?php if ( $render_actions == true ) { echo "col-md-9"; } else { echo "col-md-12"; } ?>">
            <div class="widget-bg-color-icon card-box fadeInDown animated table-responsive">
                <h4 class="m-t-0 header-title"><b>Review Draft Reports for <?=$upload_date_description?></b></h4>
                <table id="report_review_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Carrier</th>
                            <th>Total</th>
                            <th>Adjustments</th>
                            <th>Balance Due</th>
                            <th>Summary Report</th>
                            <th>Downloads</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ( ! empty($report_data) )
                        {
                            foreach($report_data as $item)
                            {
                                ?>
                                <tr>
                                    <td><?=getArrayStringValue("Carrier", $item);?></td>
                                    <td><?=getArrayStringValue("Total", $item);?></td>
                                    <td><?=getArrayStringValue("Adjustments", $item);?></td>
                                    <td><?=getArrayStringValue("BalanceDue", $item);?></td>
                                    <td><a target="_blank" href="<?=GetArrayStringValue('SummaryLink', $item)?>" class="report-review-btn btn btn-xs btn-block btn-default waves-effect" type="button" formnovalidate>View</a></td>
                                    <td><a data-company-id='<?=$company_id?>' data-carrier='<?=getArrayStringValue("CarrierId", $item);?>' data-import-date="<?=$import_date?>"  class="report-list-download-btn report-review-btn btn btn-xs btn-block btn-default waves-effect" type="button" formnovalidate>Reports</a></td>

                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <div class="clearfix"></div>
            </div>

            <?php
            // Report Review Warning Pane
            if ( ! empty($warning_data) ) {

                // Depending on how the client mapped their data, we could have one of two
                // columns that represent the employee identifier.  Figure out which one
                // we have in this data set and then remember the key so we can show the
                // data in either case.
                $first_row = $warning_data[0];
                $employee_identifier_header = "Employee Id";
                if ( GetArrayStringValue("Employee SSN", $first_row) !== '' ) $employee_identifier_header = "Employee SSN";

                ?>
                <div class="widget-bg-color-icon card-box fadeInDown animated table-responsive">
                    <a class="btn btn-xs btn-primary waves-effect pull-right m-b-5" type="button" formnovalidate="" href="<?=base_url("download/issues/{$company_id}");?>">Download List</a>
                    <h4 class="m-t-0 header-title">
                        <b>Please Note the Following Items Prior to Finalizing <?=$upload_date_description?>:</b>
                    </h4>

                    <table id="report_review_warning_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th><?=$employee_identifier_header?></th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Row #</th>
                                <th>Issue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ( ! empty($warning_data) )
                            {
                                foreach($warning_data as $item)
                                {
                                    ?>
                                    <tr>
                                        <td><?=getArrayStringValue($employee_identifier_header, $item);?></td>
                                        <td><?=getArrayStringValue("First Name", $item);?></td>
                                        <td><?=getArrayStringValue("Last Name", $item);?></td>
                                        <td><?=getArrayStringValue("Row Number", $item);?></td>
                                        <td><?=getArrayStringValue("Issue", $item);?></td>
                                    </tr>
                                    <?php
                                }
                            }                            
                            ?>
                        </tbody>
                    </table>
                    <div class="clearfix"></div>
                </div>
                <?php
            }
            ?>

        </div>

        <?php
        if( $render_actions ) {
        ?>

            <div class="col-md-3">
                <div class="review-reports widget-bg-color-icon card-box">
                    <h4 class="m-t-0 header-title"><b>Actions</b></h4>
                    Need to make changes to your data?
                    <br><br>
                    <ul>
                        <li><a id="cancel_link" href="<?=base_url('wizard/cancel');?>">Start Over</a></li>
                        <li><a id="match_link" href="<?=base_url('wizard/rematch');?>">Re-match Columns</a></li>
                        <?php
                        if ( HasRelationship($company_id) ) {
                            ?>
                            <li><a id="relationship_link" href="<?=base_url('wizard/navigate/relationships');?>">Relationships</a></li>
                            <?php
                        }
                        ?>
                        <?php
                        if ( ! isset($company_id) ) $company_id = null;
                        if ( HasLivesToCompare($company_id) ) {
                            ?>
                            <li><a id="lives_link" href="<?=base_url('wizard/navigate/lives');?>">Lives</a></li>
                            <?php
                        }
                        ?>
                        <li><a id="settings_link" href="<?=base_url('wizard/navigate/plans');?>">Plan Settings</a></li>
                        <?php
                        if ( HasClarifications($company_id) ) {
                            ?>
                            <li><a id="clarifications_link" href="<?=base_url('wizard/navigate/clarifications');?>">Clarifications</a></li>
                            <?php
                        }
                        ?>
                        <li><a id="adjustment_link" href="<?=base_url('wizard/navigate/adjustments');?>">Manual Adjustments</a></li>

                    </ul>
                    <br>
                    If data is complete, click the button below to freeze and save current month as final.
                    <br><br>
                    <button id="finalize_button" data-href="<?=base_url("wizard/finalize")?>" type="button" class="btn btn-default btn-block waves-effect waves-light" formnovalidate>Finalize Data</button>
                    <div class="clearfix"></div>
                </div>
            </div>
        <?php
        }
        ?>

    </div>

<?php
}
