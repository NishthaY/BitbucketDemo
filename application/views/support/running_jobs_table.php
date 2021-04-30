<?php
    if ( ! isset($data) ) $data = array();
?>
<div class="card-box table-responsive hidden">
    <h4 class="m-t-0 header-title"><b>Running Jobs</b></h4>
    <table id="running_jobs_table" class="table table-hover m-0" >
        <thead>
            <tr>
                <th>Started</th>
                <th>Company</th>
                <th>Job</th>
                <th>Recent Activity</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if ( !empty($data) ) {
                    foreach($data as $item) {
                        $view_array = array();
                        $view_array = array_merge($view_array, array("job_id" => getArrayStringValue("JobId", $item)));
                        $view_array = array_merge($view_array, array("job_name" => getArrayStringValue("JobName", $item)));
                        $view_array = array_merge($view_array, array("company" => getArrayStringValue("CompanyName", $item)));
                        $view_array = array_merge($view_array, array("user" => getArrayStringValue("User", $item)));
                        $view_array = array_merge($view_array, array("started" => getArrayStringValue("Started", $item)));
                        $view_array = array_merge($view_array, array("recent_activity" => getArrayStringValue("RecentActivity", $item)));
                        RenderViewSTDOUT("support/running_jobs_table_row", $view_array);
                    }
                }
            ?>
        </tbody>
    </table>
</div>
