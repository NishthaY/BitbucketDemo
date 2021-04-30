<?php
    if ( ! isset($dropdown_id) ) $dropdown_id = "";
    if ( ! isset($selected_text) ) $selected_text = "Unassigned";
    if ( ! isset($selected_value) ) $selected_value = "";
    if ( ! isset($dropdown) ) $dropdown = array();
    if ( ! isset($href) ) $href = "";
    if ( ! isset($import_date) ) $import_date = "";

    $this->load->helper('parentmapuploadcompanies');

?>

<div class="companyparent-map-company btn-group dropdown m-b-10">
    <button data-href="<?=$href?>" data-dropdown-source="<?=$dropdown_id?>" type="button" class="btn btn-white waves-light dropdown-toggle waves-effect" data-toggle="dropdown" aria-expanded="false"><span class="button-label p-r-15"><?=$selected_text?></span> <i class="caret"></i></button>
    <ul class="dropdown-menu dropdown-menu scrollable-menu" style="">
        <?php
        if ( ! empty($dropdown) )
        {
            foreach($dropdown as $value=>$label)
            {
                if ( $value === 'separator' )
                {
                    ?><li role="separator" class="divider required-value"></li><?php
                }
                else if ( $value === 'add' || $value === 'ignore' )
                {
                    ?><li value="<?=$value?>"><a href="#"><?=$label?></a></li><?php
                }
                else
                {
                    $disabled ="";
                    $company_id = $value;

                    // Add a class to the dropdown to reflect if it is in
                    $unavailable = 'unavailable';
                    if ( IsCompanyAvailableForParentMap($company_id) ) $unavailable = '';

                    // Need to see some data about the company, set the debug label to view it
                    // in the dropdown.  Empty it when not under development.
                    $debug_label = "( {$company_id} )";
                    if ( $unavailable === 'unavailable' ) $debug_label = "( {$company_id} busy )";
                    $debug_label = "";

                    $disabled = "";
                    //if ( $upload_date !== FormatDateMonYYYY($import_date) ) $disabled = " disabled ";

                    ?><li class="<?=$disabled?> <?=$unavailable?>" value="<?=$value?>" <?=$disabled?> ><a href="#"><?=$label?> <?=$debug_label?> </a></li><?php
                }
            }
        }
        ?>
    </ul>
    <div class="runtime-error hidden"></div>

</div>
<input type="hidden" id="<?=$dropdown_id?>_selected_value" name="<?=$dropdown_id?>_selected_value" value="<?=$selected_value?>">
<input type="hidden" id="<?=$dropdown_id?>_selected_text" name="<?=$dropdown_id?>_selected_text" value="<?=$selected_text?>">

