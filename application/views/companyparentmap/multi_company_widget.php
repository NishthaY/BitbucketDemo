<?php
    if ( ! isset($companyparent_id) ) $companyparent_id;
    if ( ! isset($imported_companies) ) $imported_companies = array();
    if ( ! isset($companies) ) $companies = array();
    if ( ! isset($import_date) ) $import_date = "";

    $this->load->helper('parentmapuploadcompanies');

?>
<div class="" style="padding-bottom: 150px;"> <!-- pad enough room at the bottom for dropdowns -->
    <div id="companyparent_map_panel" class="panel panel-color panel-primary ">
        <div id="comapanyparent_map_company_table" class="panel-body ">

        <h4 class="m-t-0 header-title"><b>Mappings</b></h4>
        <p>Map the companies found in the import file to existing companies on the right.</p>
        <br>

        <div class="alert alert-a2p hidden" role="alert">
            <span class="alert-message">
                The companies matched cannot all be imported at this time due to timing requirements.  Please review the column to the left to determine which mapped companies will be imported from the provided data.
            </span>
        </div>

        <div class="row header">
            <div class="col-xs-3 header"><h4><strong>Company</strong></h4></div>
            <div class="col-xs-9 header"><h4><strong>&nbsp;</strong></h4></div>
        </div>
        <?php
        foreach($imported_companies as $data)
        {
            $import_id = GetArrayStringValue("ImportDataId", $data);
            $import_description = GetArrayStringValue("ImportDescription", $data);
            $normalized = GetArrayStringValue("CompanyNormalized", $data);
            $company_id = GetArrayStringValue('CompanyId', $data);

            $selected_text = "Ignore";
            $selected_value = "ignore";
            $exists = $this->CompanyParentMap_model->exists_mapping($companyparent_id, $normalized);
            if ( $exists )
            {
                $ignored = $this->CompanyParentMap_model->is_mapping_ignored($companyparent_id, $normalized);
                if ( ! $ignored )
                {
                    $company_id = GetArrayStringValue('CompanyId', $data);
                    $company = $this->Company_model->get_company($company_id);
                    $selected_text = GetArrayStringValue("company_name", $company);
                    $selected_value = $company_id;
                }
            }

            $dropdown = array();
            $dropdown['ignore'] = "Ignore";
            $dropdown['add'] = "Add New Company";
            $dropdown['separator'] = "";
            foreach($companies as $company)
            {
                $company_id = GetArrayStringValue('company_id', $company);
                $company_name = GetArrayStringValue('company_name', $company);
                $dropdown[$company_id] = $company_name;
            }

            $dropdown_array = array();
            $dropdown_array = array_merge($dropdown_array, array("dropdown_id" => "map-{$import_id}"));
            $dropdown_array = array_merge($dropdown_array, array("selected_text" => $selected_text));
            $dropdown_array = array_merge($dropdown_array, array("selected_value" => $selected_value));
            $dropdown_array = array_merge($dropdown_array, array("dropdown" => $dropdown));
            $dropdown_array = array_merge($dropdown_array, array("href" => base_url("parent/map/company/save/multiple")));
            $dropdown_array = array_merge($dropdown_array, array('import_date' => $import_date));

            $dropdown1 = new Dropdown();
            $dropdown1->setId("map-{$import_id}");
            $dropdown1->selected = $selected_value;
            $dropdown1->callback_onchange = "CompanyParentMapCompanyDropdownSelectHandler";
            $dropdown1->addAttribute('href', base_url("parent/map/company/save/multiple"));
            $dropdown1->addAttribute('import_date', $import_date);
            $dropdown1->addAttribute('companyparent-map-company', true);
            $dropdown1->addItem('ignore', "Ignore");
            $dropdown1->addItem('add', "Add New Company");
            $dropdown1->addDivider();
            foreach($companies as $company)
            {
                $company_id = GetArrayStringValue('company_id', $company);
                $company_name = GetArrayStringValue('company_name', $company);

                // Add a class to the dropdown to reflect if it is in
                $class = 'unavailable';
                if ( IsCompanyAvailableForParentMap($company_id) ) $class = '';

                $dropdown1->addItem($company_id, $company_name, false, $class);
            }
            $dropdown1 = $dropdown1->render();


            ?>
            <div style="height:65px; overflow:visible;">
                <div class="col-xs-4 col line-right dimished-line m-b-0 " style="height: 100%"><div><?=$import_description?></div></div>
                <div class="col-xs-8 col dimished-line  m-b-0" style="height=100%" >
                    <?=$dropdown1?>
                </div>
            </div>
            <?php
        }
        ?>
        </div>
    </div>
</div>

