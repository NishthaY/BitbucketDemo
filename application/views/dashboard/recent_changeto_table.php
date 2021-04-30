<?php
    if ( ! isset($data) ) $data = array();
?>

<div class="card-box table-responsive">
    <h4 class="m-t-0 header-title"><b>Recent</b></h4>
    <div>
        <table id="admin_recent" class="table table-hover m-0">
            <thead>
                <tr class="hidden">
                    <th>Name</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data as $item) { RenderViewSTDOUT("dashboard/recent_changeto_table_row", $item); } ?>
            </tbody>
        </table>
    </div>
</div>
