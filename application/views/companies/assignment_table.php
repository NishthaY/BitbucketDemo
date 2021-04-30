<?php
    if ( ! isset($users) ) $users = array();
    if ( ! isset($company_id) ) $company_id = "";
?>
<div class="card-box table-responsive hidden">
    <h4 class="m-t-0 header-title"><b>User Assignment</b></h4>
    When a user is assigned to a company, that user becomes responsible for the company and is granted full access to that company's data.
    <br><br>
    <table id="assignment_table" class="table table-striped" width="100%">
        <thead>
            <tr>
                <th>Email Address</th>
                <th>Name</th>
                <th>Assignment</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if ( !empty($users) ) {
                    foreach($users as $user) {
                        $user = array_change_key_case($user, CASE_LOWER);
                        $user['company_id'] = $company_id;
                        $user['user_id'] = GetArrayStringValue("id", $user);
                        RenderViewSTDOUT("companies/assignment_table_row", $user);
                    }
                }
            ?>
        </tbody>
    </table>
</div>
