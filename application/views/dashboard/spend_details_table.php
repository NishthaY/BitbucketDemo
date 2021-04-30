<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($comany_id) ) $company_id = GetSessionValue("company_id");

    $render_widget = false;
    if ( HasExistingReportData() ) $render_widget = true;
?>

<?php
if ( $render_widget ) {
?>
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive hidden">
            <h4 class="m-t-0 header-title"><b><?=GetRecentDateDescription($company_id)?> Spend By Benefit</b></h4>
            <table id="spend_details_table" class="table table-striped" width="100%">
                <thead>
                    <tr>
                        <th>Benefit</th>
                        <th><?=GetRecentMon($company_id)?> Spend</th>
                        <th>YTD Spend</th>
                        <th><?=GetRecentMon($company_id)?> Wash/Retro Catch</th>
                        <th>YTD Wash/Retro Catch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if ( !empty($data) ) {
                            foreach($data as $item) {
                                RenderViewSTDOUT("dashboard/spend_details_table_row", $item);
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
}
?>
