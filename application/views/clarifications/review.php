<?php
    if ( ! isset($data) ) $data = array();
?>
<?php
    foreach($data as $item)
    {
        print RenderViewAsString("clarifications/widget_lifeevent", $item);
    }
?>
