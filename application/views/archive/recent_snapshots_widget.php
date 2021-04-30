<?php
    if ( ! isset($recent_snapshots) ) $recent_snapshots = array();
    if ( ! isset($id) ) $id = "";
    if ( ! isset($type) ) $type = "";

    $render_widget = false;
    if ( ! empty($recent_snapshots) ) $render_widget = true;

    $headings = array();
    if ( count($recent_snapshots) > 0 ) $headings = array_keys($recent_snapshots[0]);

?>

<?php
if ( $render_widget ) {
?>
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive "> <!-- dont forget to put back the hidden class -->
            <h4 class="m-t-0 header-title"><b>Recent Snapshots</b></h4>
            <table id="recent_snapshots_table" class="table table-hover" width="100%">
                <thead>
                    <tr class="hidden">
                        <?php
                        foreach($headings as $item)
                        {
                            print "<th>{$item}</th>\n";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($recent_snapshots as $row)
                    {
                        print "<tr>\n";
                        foreach($row as $key=>$value)
                        {
                            print "<td>{$value}</td>\n";
                        }
                        print "</tr>\n";
                    }
                    ?>
                </tbody>
                <tfoot>

                </tfoot>
            </table>
            <div class='pull-right'><a class="btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("support/snapshots/{$type}/{$id}");?>">More <i class="ion-arrow-right-c"></i></a></div>
        </div>
    </div>
</div>

<?php
}
?>
