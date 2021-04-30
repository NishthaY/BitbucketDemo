<?php
    if ( ! isset($data) ) $data = array();
?>
<div class="card-box table-responsive hidden">
    <h4 class="m-t-0 header-title"><b>Failed Jobs</b></h4>
    <table id="failed_jobs_table" class="table table-hover m-0" >
        <thead>
            <tr>
                <th>Failed</th>
                <th>Company</th>
                <th>Job</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if ( !empty($data) ) {
                    foreach($data as $item) {
                        $view_array = array();
                        $view_array = array_merge($view_array, array("job_name" => getArrayStringValue("JobName", $item)));
                        $view_array = array_merge($view_array, array("company" => getArrayStringValue("CompanyName", $item)));
                        $view_array = array_merge($view_array, array("user" => getArrayStringValue("User", $item)));
                        $view_array = array_merge($view_array, array("failed" => getArrayStringValue("Failed", $item)));
                        $view_array = array_merge($view_array, array("job_id" => getArrayStringValue("JobId", $item)));
                        RenderViewSTDOUT("support/failed_jobs_table_row", $view_array);
                    }
                }
            ?>
        </tbody>
    </table>
</div>
