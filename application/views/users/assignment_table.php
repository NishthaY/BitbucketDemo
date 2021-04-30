<?php
if ( ! isset($companies) ) $companies = array();
if ( ! isset($user_id) ) $user_id = "";


?>
<div class="card-box table-responsive hidden">
    <h4 class="m-t-0 header-title"><b>Company Assignment</b></h4>
    When a company is assigned to a user, that user becomes responsible for the company and is granted full access to that company's data.
    <br><br>
    <table id="assignment_table" class="table table-striped" width="100%">
        <thead>
        <tr>
            <th>Company Name</th>
            <th>Address</th>
            <th>Assignment</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ( !empty($companies) ) {
            foreach($companies as $company)
            {
                $view_array = array();
                $view_array['user_id'] = $user_id;
                $view_array['company_name'] = GetArrayStringValue("CompanyName", $company);
                $view_array['company_address'] = GetArrayStringValue("CompanyAddress", $company) . " " . GetArrayStringValue("CompanyCity", $company) . " " . GetArrayStringValue("CompanyState", $company). " " . GetArrayStringValue("CompanyPostal", $company);
                $view_array['company_id'] = GetArrayStringValue("Id", $company);
                $view_array['responsiblefor'] = GetArrayStringValue("ResponsibleFor", $company);

                RenderViewSTDOUT("users/assignment_table_row", $view_array);
            }
        }
        ?>
        </tbody>
    </table>
</div>
