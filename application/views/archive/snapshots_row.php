<?php
    if ( ! isset($headings) ) $headings = array();
    if ( ! isset($row) ) $row = array();
?>
<tr class="">
    <?php
    foreach($headings as $key)
    {
        $value = getArrayStringValue($key, $row);
        print "<td>{$value}</td>";
    }
    ?>
</tr>
