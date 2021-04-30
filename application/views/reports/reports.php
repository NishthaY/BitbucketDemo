<?php
    if ( ! isset($form_header) ) $form_header = "";
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($finalized) ) $finalized = array();
    if ( ! isset($draft) ) $draft = array();

    // Review Downloadable Reports
    $download_list_widget = new UIWidget("download_report_list_widget");
    $download_list_widget->setHref(base_url("reports/list/" . $company_id . "/CARRIER/DATE"));
    $download_list_widget = $download_list_widget->render();

?>
<?=$form_header?>
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-lg-12 col-md-12">

                <!-- Draft Reports -->
                <div class="card-box table-responsive hidden">
                    <h4 class="m-t-0 header-title"><b>Draft Reports</b></h4>
                    <p class="text-muted font-13 m-b-30">
                        These reports have are not finalized and may yet change.
                    </p>
                    <div>
                        <table id="draft_table" class="table table-hover history m-0">
                            <thead>
                                <tr class="hidden">
                                    <th>Icon</th>
                                    <th>Date</th>
                                    <th>Carrier</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($draft as $item) { RenderViewSTDOUT("reports/history_row", $item); } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Historical Reports -->
                <div class="card-box table-responsive hidden">
                    <h4 class="m-t-0 header-title"><b>Historical Reports</b></h4>
                    <p class="text-muted font-13 m-b-30"></p>
                    <div class="table-responsive">
                        <table id="historical_table" class="table table-hover history m-0">
                            <thead>
                                <tr class="hidden">
                                    <th>Icon</th>
                                    <th>Date</th>
                                    <th>Carrier</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($finalized as $item) { RenderViewSTDOUT("reports/history_row", $item); } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> <!-- end Col-9 -->
        </div><!-- End row -->
    </div>
</div>
<?=$download_list_widget?>
