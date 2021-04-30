<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($has_headers) ) $has_headers = true;
    if ( ! isset($mapping_columns) ) $mapping_columns = array();

    $first_row_class = "";
    if ( $has_headers ) $first_row_class = "active";

    $col_count = 0;
    if ( count($data) > 0 ) $col_count = count($data[0]);

?>

<div class="card-box table-responsive">
    <div class="p-b-10">
        <span class="has_headers_click_area">
            <input id="has_headers" type="checkbox" name="has_headers" checked > <span class="uiform-checkbox-inline-desc">Use first row for column headings.</span>
        </span>
    </div>
    <table id="review_table" class="table table-condensed">
        <?php
        if ( count($data) > 0 )
        {
            $row = $data[0];
            print "<thead><tr>";
            $count = 1;
            foreach($row as $column)
            {
                print "<th>" . RenderViewAsString("wizard/_upload_mapping_dropdown", array("col" => $count, 'mappings' => $mapping_columns)) . "</th>";
                $count++;
            }
            print "</tr></thead>";
        }
        ?>

        <tbody>
        <?php
        if ( ! empty($data) )
        {
            $count = 0;
            foreach($data as $row)
            {
                print "<tr class='{$first_row_class}'>";
                foreach($row as $col)
                {
                    print "<td nowrap>" .getStringValue($col). "</td>";
                }
                print "</tr>";
                $count++;
                if ( $count > 0 ) $first_row_class = "";
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
        <tfoot>
            <tr>
              <td colspan="<?=$col_count?>">...</td>
            </tr>
          </tfoot>
    </table>
</div>
