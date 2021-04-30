<?php
    if ( ! isset($data) ) $data = array();
?>
<div class="card-box table-responsive hidden">
    <h4 class="m-t-0 header-title"><b>Companies</b></h4>
    <table id="company_table" class="table table-striped" width="100%">
        <thead>
            <tr>
                <th>Company Name</th>
                <th>Address</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if ( !empty($data) ) {
                    foreach($data as $item) {
                        $item = array_change_key_case( $item, CASE_LOWER);
                        if ( getArrayStringValue("id", $item) !== '' )
                        {
                            $normalized = array();
                            $normalized['company_id'] = getArrayStringValue("id", $item);
                            $normalized['company_name'] = getArrayStringValue("name", $item);
                            $normalized['company_address'] = getArrayStringValue("address", $item);
                            $normalized['company_city'] = getArrayStringValue("city", $item);
                            $normalized['company_state'] = getArrayStringValue("state", $item);
                            $normalized['company_postal'] = getArrayStringValue("postal", $item);
                            $normalized['enabled'] = getArrayStringValue("enabled", $item);
                            $item = $normalized;
                        }
                        RenderViewSTDOUT("companies/companies_table_row", $item);
                    }
                }
            ?>
        </tbody>
    </table>
</div>
