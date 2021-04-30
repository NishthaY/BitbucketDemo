<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($type) ) $type = "";
    if ( ! isset($object_type) ) $object_type = "company";
?>
<?php
if ( $object_type === 'company' )
{
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive "> <!-- dont forget to put back the hidden class -->
                <h4 class="m-t-0 header-title"><b>In Process</b></h4>
                <BR>
                No companies are processing data at this time.
            </div>
        </div>
    </div>
    <?php
}
?>
<?php
if ( $object_type === 'parent' )
{
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive "> <!-- dont forget to put back the hidden class -->
                <h4 class="m-t-0 header-title"><b>In Process</b></h4>
                <BR>
                No parent companies are processing data at this time.
            </div>
        </div>
    </div>
    <?php
}
?>

