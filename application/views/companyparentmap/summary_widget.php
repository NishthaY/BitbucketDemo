<?php
    if ( ! isset($import_description) ) $import_description = "";
    if ( ! isset($importing) ) $importing = array();
    if ( ! isset($not_importing ) ) $not_importing = array();
    if ( ! isset($unavailable ) ) $unavailable = array();

    if ( ! isset($start_date) ) $start_date = "";

    if ( ! isset($companies) ) $companies = array();
    if ( ! isset($imported_companies) ) $imported_companies = array();
    if ( ! isset($months) ) $months = array();

    if ( ! isset($start_months) ) $start_months = "";
    if ( ! isset($start_years) ) $start_years = "";




?>
<div id="" class="panel panel-color panel-primary">
    <div id="comapanyparent_map_company_table" class="panel-body">

        <h4 class="m-t-0 header-title"><b>Summary</b></h4>
        <p>
            The information below outlines which companies will be processed for the month and year specified: <?=$start_months?> <?=$start_years?>
        </p>


        <h3 class="m-t-20 header-title"><b>Importing</b></h3>
        <?php
        if ( ! empty($importing) )
        {
            ?>
            <ul id="importing_list">
                <?php
                foreach($importing as $company)
                {
                    $company_id = GetArrayStringValue('company_id', $company);

                    $import_desc = "";
                    if ( GetArrayStringValue('import_date', $company) !== '' ) $import_desc = "(".GetArrayStringValue('import_date_short', $company).")";

                    ?><li data-companyid="<?=$company_id?>"><?=GetArrayStringValue('company_name', $company)?> <?=$import_desc?></li><?php
                }
                ?>
            </ul>
            <?php
        }
        else
        {
            ?>
            <ul>
                <li>None</li>
            </ul>
            <?php
        }

        ?>

        <?php
        if ( ! empty($not_importing) )
        {
            ?>
            <h3 class="m-t-20 header-title"><b>Not Importing</b></h3>
            <ul id="not_importing_list">
                <?php
                foreach($not_importing as $company)
                {
                    $company_id = GetArrayStringValue('company_id', $company);

                    $import_desc = "";
                    if ( GetArrayStringValue('import_date', $company) !== '' ) $import_desc = "(".GetArrayStringValue('import_date_short', $company).")";

                    ?><li data-companyid="<?=$company_id?>"><?=GetArrayStringValue('company_name', $company)?> <?=$import_desc?></li><?php
                }
                ?>
            </ul>
            <?php
        }
        ?>

        <?php
        if ( ! empty($unavailable) )
        {
            ?>
            <h3 class="m-t-20 header-title"><b>Unavailable</b></h3>
            <p>The following companies are currently processing a different import and are not available at this time.</p>
            <ul id="unavailable_list">
                <?php
                foreach($unavailable as $company)
                {
                    $company_id = GetArrayStringValue('company_id', $company);

                    $import_desc = "";
                    if ( GetArrayStringValue('import_date', $company) !== '' ) $import_desc = "(".GetArrayStringValue('import_date_short', $company).")";

                    ?><li data-companyid="<?=$company_id?>"><?=GetArrayStringValue('company_name', $company)?> <?=$import_desc?></li><?php
                }
                ?>
            </ul>
            <?php
        }
        ?>

    </div>
</div>
