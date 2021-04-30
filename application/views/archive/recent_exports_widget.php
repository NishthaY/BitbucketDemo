<?php
    if ( ! isset($recent_exports) ) $recent_exports = array();
    if ( ! isset($id) ) $id = "";
    if ( ! isset($type) ) $type = "";

    $render_widget = false;
    if ( ! empty($recent_exports) ) $render_widget = true;

    $headings = array();
    if ( count($recent_exports) > 0 ) $headings = array_keys($recent_exports[0]);

?>

<?php
if ( $render_widget ) {
?>
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive">
            <h4 class="m-t-0 header-title"><b>Recent Exports</b></h4>
            <table id="recent_exports_table" class="table table-hover" width="100%">
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
                    foreach($recent_exports as $row)
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
            <div class='pull-right'><a class="btn btn-white btn-xs waves-light waves-effect" href="<?=base_url("support/exports/{$type}/{$id}");?>">More <i class="ion-arrow-right-c"></i></a></div>
        </div>
    </div>
</div>

<?php
}
?>
