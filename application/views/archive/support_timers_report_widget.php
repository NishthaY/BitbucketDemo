<?php
if ( ! isset($company_id) ) $company_id = "";
if ( ! isset($data) ) $data = array();
if ( ! isset($headings) ) $headings = array();
if ( ! isset($import_date) ) $import_date = "";

$date_tag = fRightBack($import_date, '/') . fLeft($import_date, "/");


?>
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive hidden">
            <a class="btn btn-xs btn-primary waves-effect pull-right m-b-5" type="button" formnovalidate="" href="<?=base_url("download/timers/{$company_id}/{$date_tag}");?>">Download List</a>
            <h4 class="m-t-0 header-title"><b>Support Timers</b></h4>
            <p>How long did things really take?</p>

            <table id="timers_table" class="table table-hover" width="100%">
                <thead>
                <tr class="">
                    <?php
                    $ignore = array('depth');
                    foreach($headings as $item)
                    {
                        if ( ! in_array(strtolower($item),$ignore ))
                        {
                            print "<th>{$item}</th>\n";
                        }
                    }
                    ?>
                </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($data as $row)
                    {
                        $depth = GetArrayStringValue('depth', $row);

                        $ignore = array('depth');
                        print "<tr class='support-timer-row' data-depth='{$depth}'>\n";
                        foreach($row as $key=>$value)
                        {
                            if ( ! in_array(strtolower($key),$ignore ))
                            {
                                if ( strtolower($key) === 'tag' )
                                {
                                    $indent = "";
                                    for($i=0;$i<$depth;$i++)
                                    {
                                        if ( $i < $depth - 1 )
                                        {
                                            $indent .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</i>";
                                        }
                                        else
                                        {
                                            //$indent .= "&nbsp;&nbsp;&nbsp;<i class='fa fa-minus'></i>";
                                            $indent .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</i>";
                                        }

                                    }
                                    print "<td>{$indent} {$value}</td>\n";
                                }
                                else
                                {
                                    print "<td>{$value}</td>\n";
                                }

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