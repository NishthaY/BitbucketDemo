<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($company) ) $company = "";
    if ( ! isset($table_title) ) $table_title = "{$company} Advice2Pay Users";
    if ( ! isset($table_description) ) $table_description = "";
    if ( ! isset($role_label) ) $role_label = "Manager";

    if ( GetSessionValue("company_id") == "1" ) $role_label = "Admin";
?>
<div class="card-box table-responsive hidden">
    <h4 class="m-t-0 header-title"><b><?=$table_title?></b></h4>
    <p class="text-muted font-13 m-b-30">
        <?=$table_description?>
    </p>

    <table id="user_datatable" class="table table-striped hidden" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Email Address</th>
                <th>Name</th>
                <th><?=$role_label?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
             <?php
                if ( !empty($data) ) {
                    foreach($data as $item) {
                        RenderViewSTDOUT("users/users_table_row", $item);
                    }
                }
            ?>
        </tbody>
    </table>
</div>
