<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($add_button) ) $add_button = "";


    $headers = array();
    if ( ! empty($data) ) $headers = array_keys($data[0]);
?>
<div class="panel panel-color panel-primary" >
    <div class="panel-body table-responsive hidden">
        <table id="adjustments_table" class="table table-striped">
            <thead>
                <th>Carrier</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Memo</th>
                <th>Actions</th>
            </thead>
            <tbody>
            <?php
            foreach($data as $row)
            {
                $id = getArrayStringValue("Id", $row);
                $edit_link = base_url("adjustments/edit/adjustment/{$id}");
                $delete_link = base_url("adjustments/delete/adjustment/{$id}");
                print "<tr>";
                print "<td>" . getArrayStringValue("Carrier", $row) . "</td>";
                print "<td>" . getArrayStringValue("Type", $row) . "</td>";
                print "<td>" . getMoneyValue(getArrayStringValue("Amount", $row)) . "</td>";
                print "<td>" . getArrayStringValue("Memo", $row) . "</td>";
                print "<td class='action-cell'>";
                print " <span class='action-buttons pull-right nowrap'>";
                print "    <a class='action-cell-edit btn btn-white btn-xs waves-light waves-effect' href='{$edit_link}'><i class='glyphicon glyphicon-pencil m-r-5'></i> Edit</a>";
                print "    <a class='action-cell-delete btn btn-white btn-xs waves-light waves-effect' href='{$delete_link}'><i class='glyphicon glyphicon-remove m-r-5'></i> Remove</a>";
                //print "   <span data-id='{$id}' data-href='".base_url()."adjustments/edit/adjustment' class='edit-manual-adjustment-btn table-action m-r-20'><i class='glyphicon glyphicon-pencil'></i> Edit</span>";
                //print "   <span data-id='{$id}' data-href='".base_url()."adjustments/delete/adjustment' class='delete-manual-adjustment-btn table-action m-r-20'><i class='glyphicon glyphicon-remove'></i> Delete</span>";
                print " </span>";
                print "</td>";
                print "</tr>";
            }
            ?>
            </tbody>
        </table>
        <?=$add_button?>
    </div>
</div>
