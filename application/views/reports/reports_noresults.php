<?php
    if ( ! isset($form_header) ) $form_header = "";
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($finalized) ) $finalized = array();
    if ( ! isset($draft) ) $draft = array();
?>
<?=$form_header?>
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-lg-12 col-md-12">

                <!-- Historical Reports -->
                <div class="card-box table-responsive">
                    <h4 class="m-t-0 header-title"><b>Historical Reports</b></h4>
                    <p class="text-muted font-13 m-b-30"></p>
                    <div class="">
                        <table id="no_reports" class="table table-hover history m-0">
                            <thead>
                                <tr class="hidden">
                                    <th>Icon</th>
                                    <th>Date</th>
                                    <th>Carrier</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4">No reports found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> <!-- end Col-9 -->
        </div><!-- End row -->
    </div>
</div>
