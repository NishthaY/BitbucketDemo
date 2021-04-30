<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($company_id) ) $company_id = GetSessionValue("company_id");
    if ( ! isset($description) ) $description = "";


    $starting_message_hidden = "hidden";
    if ( empty($data) ) $starting_message_hidden = "";

    $table_hidden = "";
    if ( empty($data) ) $table_hidden = "hidden ";

?>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive" style="overflow: visible;">
            <div id='multi_company_starting_message' class="<?=$starting_message_hidden?>">
                <h4 class="m-t-0 header-title"><b>Welcome</b></h4>
                Please add a company to get started.
            </div>
            <table id="multi_company_table" class="table <?=$table_hidden?>" width="100%">
                <tbody>
                <?php
                if ( !empty($data) )
                {
                    foreach($data as $item)
                    {
                        $row = array();
                        $row['company_name'] = GetArrayStringValue("CompanyName", $item);
                        $row['company_id'] = GetArrayStringValue("CompanyId", $item);
                        $row['description'] = GetArrayStringValue("WizardDescription", $item);
                        $row['enabled'] = GetArrayStringValue("Enabled", $item);
                        $row['draft_reports_ready'] = GetArrayStringValue("DraftReportsReady", $item);
                        $row['runtime_error'] = GetArrayStringValue("RuntimeError", $item);
                        $row['status'] = GetArrayStringValue("Status", $item);
                        $row['landing'] = GetArrayStringValue("Landing", $item);
                        GetArrayStringValue('Busy', $item) === 't' ? $row['busy'] = true : $row['busy'] = false;

                        // Using the data set associated with the report review widget, pull the
                        // carriers that have reports.
                        $carriers = array();
                        $report_review_data = ReportingReviewData($row['company_id']);
                        if ( ! empty($report_review_data) )
                        {
                            foreach($report_review_data as $item)
                            {

                                $carrier = GetArrayStringValue("Carrier", $item);
                                $carrier_id = GetArrayStringValue("CarrierId", $item);
                                $carriers = Array($carrier => $carrier_id);

                            }
                        }
                        $row['carriers'] = $carriers;
                        $row['import_date'] = replaceFor(GetUploadDate($row['company_id']), '/', '-');

                        if ( ! IsAuthenticated('parent_company_write,company_write', 'company', $row['company_id'] ) )
                        {
                            // Parent Managers: They have parent_company_write an may interact with this company.
                            // Parent Users: If assigned Manager permissions for the company, they will have company_write permission.
                            continue;
                        }

                        RenderViewSTDOUT("dashboardparent/multi_company_table_row", $row);
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
