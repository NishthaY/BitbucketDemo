<?php
    if ( ! isset($data) ) $data = array();
?>

<div class="card-box table-responsive">
    <h4 class="m-t-0 header-title"><b>Recent Companies</b></h4>
    <div>
        <table id="admin_companies" class="table table-hover m-0">
            <thead>
                <tr class="hidden">
                    <th>Company Name</th>
                    <th>Company Address</th>
                    <th>Company Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data as $item) { RenderViewSTDOUT("companies/companies_admin_table_row", $item); } ?>
            </tbody>
        </table>
    </div>
</div>
