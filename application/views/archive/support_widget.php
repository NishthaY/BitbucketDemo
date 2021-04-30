<?php
    if ( ! isset($selected_id) ) $selected_id = "";
    if ( ! isset($selected_type) ) $selected_type = "";
    if ( ! isset($uri) ) $uri = "support/manage/TYPE/ID";
    if ( ! isset($company_parent_flg) ) $company_parent_flg = true;
    if ( ! isset($company_flg) ) $company_flg = true;

    $companies = array();
    if ( $company_flg ) $companies = $this->Company_model->get_all_companies();

    $parents = array();
    if ( $company_parent_flg ) $parents = $this->CompanyParent_model->get_all_parents();

?>
<div>
    <select id="view_selector" class="form-control select2">
        <?php
        if ( ! empty($companies) )
        {
            $url = replaceFor($uri, "TYPE", "company");
            $url = replaceFor($url, "ID", "1");
            $url = base_url($url);

            print "<optgroup label='Companies'>\n";
            print "<option value='{$url}'>Advice2Pay</option>\n";
            foreach($companies as $company)
            {
                $id = getArrayIntValue("company_id", $company);
                $name = getArrayStringValue("company_name", $company);
                $url = replaceFor($uri, "TYPE", "company");
                $url = replaceFor($url, "ID", $id);
                $url = base_url($url);
                $selected = "";
                if ( $id == $selected_id && $selected_type == "company" ) $selected = "selected";
                print "<option value='{$url}' {$selected}>{$name}</option>\n";
            }
            print "</optgroup>\n";
        }
        ?>
        <?php
        if ( ! empty($parents) )
        {
            print "<optgroup label='Parents'>\n";
            foreach($parents as $parent)
            {
                $parent = array_change_key_case($parent, CASE_LOWER);
                $id = getArrayIntValue("id", $parent);
                $name = getArrayStringValue("name", $parent);
                $url = replaceFor($uri, "TYPE", "parent");
                $url = replaceFor($url, "ID", $id);
                $url = base_url($url);
                $selected = "";
                if ( $id == $selected_id && $selected_type == "parent" ) $selected = "selected";
                print "<option value='{$url}' {$selected}>{$name}</option>\n";
            }
            print "</optgroup>\n";
        }
        ?>
    </select>
</div>
