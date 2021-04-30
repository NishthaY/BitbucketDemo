<?php
    if ( ! isset($headings) ) $headings = array();
    if ( ! isset($row) ) $row = array();
?>
<tr class="">
    <?php
    foreach($headings as $key)
    {
        $value = getArrayStringValue($key, $row);
        $json = json_decode($value, true);
        if ( json_last_error() === JSON_ERROR_NONE ) $value = RenderViewAsString('archive/json_viewer', array('json' => $json));//json_encode($json, JSON_PRETTY_PRINT);
        print "<td>{$value}</td>";
    }
    ?>
</tr>
