<?php
    if ( ! isset($companies) ) $companies = array();
    if ( ! isset($a2p_match) ) $a2p_match = false;
    if ( ! isset($selected_company_id) ) $selected_company_id = "";

    $warning_class = "hidden";
    if ( $a2p_match ) $warning_class = "";
?>
<div id="companyparent_map_panel" class="panel panel-color panel-primary" >
    <div id="comapanyparent_map_company_table" class="panel-body">

        <div class="alert alert-a2p <?=$warning_class?>" role="alert">
            <span class="alert-message">
                Your import file appears to have a company column, but it was not matched on the previous step.  If the imported file contains information for multiple companies, it is recommended that your return to the <strong>Match</strong> step and identify the company column before continuing.
            </span>
        </div>

        <p>
            Please select which company the import data belongs to.
        </p>


    <?php
    foreach($companies as $company)
    {
        $company_id = GetArrayStringValue('company_id', $company);
        $company_name = GetArrayStringValue('company_name', $company);
        $checked = "";
        if ( $company_id === $selected_company_id ) $checked = " checked ";
        ?>
        <div class="radio-row radio radio-primary p-t-20">
            <input type="radio" name="company" value="<?=$company_id?>" <?=$checked?> >
            <label for="company"><strong><?=$company_name?></strong></label>
        </div>
        <?php
    }

    ?>

    </div>
</div>
