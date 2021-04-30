<?php
    if ( ! isset($name) ) $name = "";
    if ( ! isset($type) ) $type = "";
    if ( ! isset($state) ) $state = "";
    if ( ! isset($size) ) $size = "";
    if ( ! isset($updated) ) $updated = "";
    if ( ! isset($revision) ) $revision = "";
    if ( ! isset($confirm_btn) ) $confirm_btn = "";

    strtoupper($state) == "UP" ? $state = "<i class='fa fa-circle m-0 p-0 text-success'></i>" : $state = "<i class='fa fa-circle m-0 p-0 text-danger'></i>";

?>
<tr>
    <td><?=getStringValue($state)?></td>
    <td><?=getStringValue($name)?></td>
    <td><?=getStringValue($type)?></td>
    <td><?=getStringValue($size)?></td>
    <td><?=getStringValue($revision)?></td>
    <td><?=getStringValue($updated)?></td>
    <td>
        <?=$confirm_btn?>


    </td>
</tr>
