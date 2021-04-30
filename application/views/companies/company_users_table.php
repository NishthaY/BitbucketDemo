<?php
    if ( ! isset($users) ) $users = array();
    if ( ! isset($company_id) ) $company_id = "";

?>

<div class="card-box table-responsive hidden">
    <h4 class="m-t-0 header-title"><b>Company Users</b></h4>
    The following user(s) have access to this company’s data because they have accounts with the company itself.
    <br><br>
    <table id="everyone_table" class="table table-striped" width="100%">
        <thead>
            <tr>
                <th>Email Address</th>
                <th>Name</th>
                <th>Manager</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if ( !empty($users) ) {
                    foreach($users as $user) {
                        $user = array_change_key_case($user, CASE_LOWER);
                        $user['company_id'] = $company_id;
                        RenderViewSTDOUT("companies/company_users_table_row", $user);
                    }
                }
            ?>
        </tbody>
    </table>
</div>
