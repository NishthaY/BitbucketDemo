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
            <h4 class="m-t-0 header-title"><b>Workflows In Process</b></h4>
            <table id="in_process_table" class="table table-hover" width="100%">
                <thead>
                    <tr class="hidden">
                        <?php
                        foreach($headings as $item)
                        {
                            if ( strtoupper($item) == "IDENTIFIERNAME" ) continue;
                            print "<th>{$item}</th>\n";
                        }
                        print "<th>Last</th>\n";
                        ?>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($in_process as $row)
                    {
                        print "<tr>\n";
                        for($i=0;$i<count($headings);$i++)
                        {
                            $key = $headings[$i];
                            $value = $row[$key];
                            if ( strtoupper($key) == "IDENTIFIER" ) {
                                $identifier = $value;
                                continue;
                            }
                            if ( strtoupper($key) == "IDENTIFIERTYPE" ) {
                                $identifier_type = $value;
                                if ( $identifier_type === 'comany' ) $url_identifier = 'company';
                                else if ( $identifier_type === 'companyparent' ) $url_identifier = 'parent';
                                else $url_identifier = "";
                                continue;
                            }
                            print "<td>{$value}</td>\n";
                            if ( $i == count($headings) - 1 )
                            {
                                $value = "<div class='pull-right'><a id='view_snapshot' class='btn btn-white btn-xs waves-light waves-effect' href='".base_url("support/snapshots/{$url_identifier}/{$identifier}")."'>View Snapshots <i class='ion-arrow-right-c'></i></a></div>";
                                print "<td>{$value}</td>\n";
                            }
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
