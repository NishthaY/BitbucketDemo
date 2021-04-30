<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($headers) ) $headers = array();
    if ( ! isset($identifier) ) $identifier = "";
    if ( ! isset($identifier_type) ) $identifier_type = "";

?>
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive hidden">
            <a id='refresh_btn' class="btn btn-white btn-xs waves-light waves-effect pull-right" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><i class="fa fa-refresh m-r-5"></i> Refresh</a>
            <h4 class="m-t-0 header-title"><b>Available Exports</b></h4>
            <p>Data exports available for download.</p>
            <table id="export_table" class="table table-hover" width="100%">
                <thead>
                <tr class="">
                    <?php
                    foreach($headers as $header)
                    {
                        if ( strtolower($header) === 'actions' ) $header = "&nbsp;";
                        if ( strtolower($header) === 'status' ) $header = "&nbsp;";
                        ?><th><?=$header?></th><?php
                    }
                    ?>
                </tr>
                </thead>
                <tbody>
                        <?php
                        if ( ! empty($data) )
                        {
                            foreach($data as $row)
                            {
                                ?>
                                <tr class='export-table-row'>
                                    <?php
                                    if ( ! empty($row) )
                                    {
                                        foreach($headers as $header)
                                        {
                                            $item = GetArrayStringValue($header, $row);
                                            if ( $header === 'Status' )
                                            {
                                                $status_viewarray = array();
                                                $status_viewarray['status'] = GetArrayStringValue('Status', $row);
                                                RenderViewSTDOUT('archive/export_manage_widget_status_indicator_cell', $status_viewarray);
                                            }
                                            else
                                            {
                                                ?><td style="padding-top: 17px; padding-bottom: 20px;"><?=$item?></td><?php
                                            }


                                        }
                                    }
                                    ?>
                                </tr>
                                <?php
                            }
                        }

                        ?>
                </tbody>
                <tfoot>

                </tfoot>
            </table>
        </div>
    </div>
</div>
