<?php
    if ( ! isset($list) ) $list = array();


    $has_workflow_properties = false;
    foreach($list as $key=>$value)
    {
        if ( StartsWith($key, 'wf') )
        {
            $has_workflow_properties = true;
            break;
        }
    }

    $has_standard_properties = false;
    foreach($list as $key=>$value)
    {
        if ( ! StartsWith($key, 'wf') )
        {
            $has_standard_properties = true;
            break;
        }
    }

    $row_hidden = "";
    if ( empty($list) ) $row_hidden = "hidden";

    $standard_hidden = "";
    if ( ! $has_standard_properties ) $standard_hidden = "hidden";

    $workflow_hidden = "";
    if ( ! $has_workflow_properties ) $workflow_hidden = "hidden";



?>
<?php
    if ( $has_standard_properties && ! $has_workflow_properties  )
    {
        ?>
        <div class="card-box <?=$standard_hidden?>">
            <?php
            foreach($list as $key=>$value)
            {
                if ( StartsWith($key, 'wf') ) continue;
                print "<div><strong>".getStringValue($key).":</strong> " . getStringValue($value) . "</div>";
            }
            ?>
        </div>
        <?php
    }
    else
    {
        ?>

        <div class="row <?$row_hidden?>">
            <div class="col-lg-9">
                <div class="card-box <?=$standard_hidden?>">
                    <h4 class="m-t-0 header-title"><b>Snapshot Properties</b></h4>
                    <?php
                    foreach($list as $key=>$value)
                    {
                        if ( StartsWith($key, 'wf') ) continue;
                        print "<div><strong>".getStringValue($key).":</strong> " . getStringValue($value) . "</div>";
                    }
                    ?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card-box <?=$workflow_hidden?>">
                    <h4 class="m-t-0 header-title"><b>Workflow Properties</b></h4>
                    <?php
                    foreach($list as $key=>$value)
                    {
                        if ( ! StartsWith($key, 'wf') ) continue;
                        print "<div><strong>".getStringValue($key).":</strong> " . getStringValue($value) . "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <?php
    }
?>
