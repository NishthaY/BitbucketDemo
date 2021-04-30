<?php
    if ( ! isset($in_process) ) $in_process = array();

    $render_widget = false;
    if ( ! empty($in_process) ) $render_widget = true;

    $headings = array();
    if ( count($in_process) > 0 ) $headings = array_keys($in_process[0]);

    $user_id = GetSessionValue("user_id");

?>
<?php
if ( $render_widget ) {
?>
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive "> <!-- dont forget to put back the hidden class -->
            <h4 class="m-t-0 header-title"><b>Wizards In Process</b></h4>
            <table id="in_process_table" class="table table-hover" width="100%">
                <thead>
                    <tr class="hidden">
                        <?php
                        foreach($headings as $item)
                        {
                            if ( strtoupper($item) == "COMPANYID" ) continue;
                            print "<th>{$item}</th>\n";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($in_process as $row)
                    {
                        print "<tr>\n";
                        foreach($row as $key=>$value)
                        {
                            if ( strtoupper($key) == "COMPANYID" ) {
                                $company_id_row = $value;
                                continue;
                            }
                            if ( strtoupper($key) == "BUTTON1" )
                            {
                                $value = "<div class='pull-right'><a id='generate_snapshot' class='btn btn-white btn-xs waves-light waves-effect' href='".base_url()."support/snapshots/snap/{$company_id_row}/{$user_id}'>Generate Snapshot <i class='ion-arrow-right-c'></i></a></div>";
                            }
                            print "<td>{$value}</td>\n";

                        }
                        print "</tr>\n";
                    }
                    ?>
                </tbody>
                <tfoot>

                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php
}
?>
