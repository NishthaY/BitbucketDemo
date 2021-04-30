<?php
    if ( ! isset($data) ) $data = array();
?>

<div class="card-box table-responsive"> <!-- hidden -->
    <h4 class="m-t-0 header-title"><b>Recent Companies</b></h4>
    <div>
        <table id="parent_companies" class="table table-hover m-0">
            <thead>
                <tr class="hidden">
                    <th>Company Name</th>
                    <th>Company Address</th>
                    <th>Company Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data as $item) {
                    $item = array_change_key_case( $item, CASE_LOWER);
                    RenderViewSTDOUT("companies/companies_parent_table_row", $item);
                } ?>
            </tbody>
        </table>
    </div>
</div>
