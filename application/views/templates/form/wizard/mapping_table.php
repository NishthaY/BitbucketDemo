<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($has_headers) ) $has_headers = true;
    if ( ! isset($mapping_columns) ) $mapping_columns = array();
    if ( ! isset($required_list) ) $required_list = "";
    if ( ! isset($conditional_list) ) $conditional_list = "";
    if ( ! isset($max_sample_rows) ) $max_sample_rows = 13;
    if ( ! isset($attributes) ) $attributes = "";
    if ( ! isset($identifier) ) $identifier = "";
    if ( ! isset($identifier_type) ) $identifier_type = "";

    $first_row_class = "";
    if ( $has_headers ) $first_row_class = "sample-data-header";

    $checked = "";
    if ( $has_headers ) $checked = "checked";

    $first_row = array();
    if ( count($data) >= 1 ) $first_row = $data[0];

    $second_row = array();
    if ( count($data) >= 2 ) $second_row = $data[1];


    if ( $identifier_type == '' )
    {
        $identifier = GetSessionValue("company_id");
        $identifier_type = "company";
    }


?>

<div class="panel panel-color panel-primary panel-matching-table" <?=$attributes?> >

    <div id="sample_data_container"  class="panel-body">


        <div class="p-b-10">
            <span class="has_headers_click_area">
                <input id="has_headers" type="checkbox" name="has_headers" <?=$checked?> > <span class="uiform-checkbox-inline-desc">Use first row for column headings.</span>
            </span>
        </div>

<div class="scroll-bumper-container">
    <div id="review_table_scroller" class="table-responsive">
        <div class="scroll-bumper-button scroll-bumper-button-right hidden"><i class="fa fa-step-forward"></i></div>
        <div class="scroll-bumper-left hidden"></div>
        <div class="scroll-bumper-button scroll-bumper-button-left hidden"><i class="fa fa-step-backward"></i></div>
        <div class="scroll-bumper-right hidden"></div>
        <table id="review_table" class="table table-condensed">
            <?php

            $headers = array();
            if ( count($data) > 0 )
            {
                $row = $data[0];
                if ( $has_headers ) $headers = $data[0];
                print "<thead><tr>";
                $count = 0;
                if ( ! empty($row) )
                {
                    foreach($row as $column)
                    {
                        $view_array = array();
                        $view_array = array_merge($view_array, array_merge($view_array, array("col" => $count)));
                        $view_array = array_merge($view_array, array_merge($view_array, array("mappings" => $mapping_columns)));
                        $view_array = array_merge($view_array, array_merge($view_array, array("required_mappings" => $required_list)));
                        $view_array = array_merge($view_array, array_merge($view_array, array("selected" => BestMappingColumnMatchFAST($identifier, $identifier_type, $count))));
                        $view_array = array_merge($view_array, array_merge($view_array, array("default_label" => GetDefaultColumnLabel($identifier, $identifier_type, $count))));
                        $view_array = array_merge($view_array, array_merge($view_array, array("user_label" => GetUserColumnLabel($identifier, $identifier_type, $count))));
                        print "<th>" . RenderViewAsString("templates/form/wizard/_upload_mapping_dropdown", $view_array) . "</th>";
                        $count++;
                    }
                }
                print "</tr></thead>";
            }

            ?>

            <tbody>
            <?php
            if ( ! empty($data) )
            {
                $count = 0;
                $col_count = null;
                foreach($data as $row)
                {
                    print "<tr class='{$first_row_class}'>";
                    if ( ! empty($row) ) {
                        if ( $col_count == null ) $col_count = count($row);
                        $column_count = 0;
                        foreach($row as $col)
                        {
                            ( $has_headers ) ? $header_value = $headers[$column_count] : $header_value = "";
                            $column_value = getStringValue($col);
                            if ( $has_headers && $count == 0 )
                            {
                                // If we have headers and this is the first row, do not
                                // mask the column value.
                                $masked_value = $column_value;
                            }
                            else
                            {
                                // We are showing life data, mask the data if needed.
                                $masked_value = MaskCustomerData($column_value, $header_value);
                            }
                            print "<td nowrap>{$masked_value}</td>";
                            $column_count++;
                        }
                    }
                    print "</tr>";
                    $count++;
                    if ( $count > 0 ) $first_row_class = "";
                    if ( $count >= $max_sample_rows ) break;
                }

                // Add some empty rows if the file was too small.
                if ( $count < $max_sample_rows ) {
                    while( $count < $max_sample_rows ) {
                        print "<tr class='{$first_row_class}'>";
                        print "  <td nowrap colspan='{$col_count}'>&nbsp;</td>";
                        print "</tr>";
                        $count++;
                    }
                }
            }
            else
            {
                ?>
                <tr><td>No results found.</td></tr>
                <?php
            }
            ?>
            </tbody>
        </table>
</div>
</div>
        <div id="table_bottom_padding" style="min-height: 0px;" class=""></div>
    </div>

</div>
