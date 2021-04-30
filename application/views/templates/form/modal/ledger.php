<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($description) ) $description = "";
    if ( ! isset($data) ) $data = array();
    if ( ! isset($removable_rows) ) $removable_rows = true;

    $headers = array();
    if ( ! empty($data) )
    {
        $headers = array_keys($data[0]);
    }

?>
<?php
    if (  ! empty($data) ) {
        ?>
        <div id="existing_adjustment_container">
        <label><?=$description?></label>
            <table id="adjustment_ledger" class="table table-striped table-bordered" cellspacing="0" width="100%" style="border: none;">
                <thead>
                    <tr class="hidden">
                        <?php
                        foreach($headers as $header)
                        {
                            if ( $id_column != null && $header == $id_column ) continue;
                            ?><th><?=$header?></th><?php
                        }
                        print "<th>Actions</th>";
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($data as $row)
                    {
                        $id = "";
                        print "<tr>";
                        foreach($headers as $header)
                        {
                            if ( $id_column != null && $header == $id_column )
                            {
                                $id = getArrayStringValue($header, $row);
                                continue;
                            }
                            $value = getArrayStringValue($header, $row);
                            if ( $money_column != null && $header == $money_column ) $value = getMoneyValue($value);
                            if ( $header != "" ) print "<td>{$value}</td>";
                        }
                        print "<td style='width: 80px;'><a  data-id='{$id}' href='".base_url()."dashboard/delete/adjustment' class='delete-manual-adjustment-btn btn btn-xs btn-block btn-default waves-effect' type='button' formnovalidate=''><i class='glyphicon glyphicon-remove'></i> Delete</a></td>";
                        print "</tr>";
                    }

                    ?>
                </tbody>
            </table>
        </div>
    </p>
        <?php
    }
?>
