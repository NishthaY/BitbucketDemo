<?php
    if ( ! isset($json) ) $json = json_encode(array());
?>
<div>
    <?php
    if ( ! is_array($json) ) $json = json_decode($json);
    if( empty($json) ) print "No additional details.";
    if ( ! empty($json) )
    {
        foreach($json as $key=>$value)
        {
            print "<strong>{$key}</strong>: {$value}<BR>\n";
        }
    }

    ?>
</div>

