<?php
    if ( ! isset($company_id) ) $company_id = "";

    // Get the specific object for this type.
    $us_object = $this->ObjectMapping_model->get_mapping_properties_by_code('USAStates');
    $us_object_id = GetArrayStringValue("Id", $us_object);

    $ca_object = $this->ObjectMapping_model->get_mapping_properties_by_code('CAProvinces');
    $ca_object_id = GetArrayStringValue("Id", $ca_object);
?>
<div>
    This field should be identifiable as a state found in the United States of America.<BR>
    A few examples of accepted formats are:
    <ul>
        <li>Iowa</li>
        <li>District of Columbia</li>
        <li>IA</li>
        <li>DC</li>
    </ul>
    This field is case insensitive.<BR>
    For a detailed list of supported state values, please see the link below.
    <ul>
        <li><a href="<?=base_url("download/mappings/{$company_id}/{$us_object_id}");?>">USA<a/></li>
    </ul>
</div>