<?php
    if ( ! isset($workflow_name) ) $workflow_name = "";
    if ( ! isset($wf_jslibrary) ) $wf_jslibrary = "";
    if ( ! isset($properties) ) $properties = array();

    // This will place a DIV on the page with the workflow widget that
    // outlines all of the workflow properties as data tags on a hidden
    // div so we can access them via javascript.

?>
<div class="workflow-widget hidden"
     <?php
        foreach($properties as $property)
        {
            $name = GetArrayStringValue("Name", $property);
            $value = GetArrayStringValue("Value", $property);
            if ($name !== '' )
            {
                $name = strtolower($name);
                $name = replacefor($name, ' ', '');
                $value = replacefor($value, "\"", "");
                ?> data-<?=$name?>="<?=$value?>" <?php
            }
        }
     ?>
     data-workflow="<?=$workflow_name?>"
     data-jslibrary="<?=$wf_jslibrary?>"
>
Workflow Widget Properties
</div>
