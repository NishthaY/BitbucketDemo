<?php
    if ( ! isset($users) ) $users = array();
    if ( ! isset($company_id) ) $company_id = "";

?>

<div class="card-box table-responsive hidden">
    <h4 class="m-t-0 header-title"><b>Inherited Users</b></h4>
    The following user(s) have full access to this company's data because they are have been assigned the role of Manager on the parent company.
    <br><br>
    <table id="managers_table" class="table table-striped" width="100%">
        <thead>
            <tr>
                <th>Email Address</th>
                <th>Name</th>
                <th>Assignment</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if ( !empty($users) ) {
                    foreach($users as $user) {
                        $user = array_change_key_case($user, CASE_LOWER);
                        $user['company_id'] = $company_id;
                        RenderViewSTDOUT("companies/inherited_table_row", $user);
                    }
                }
            ?>
        </tbody>
    </table>
</div>
