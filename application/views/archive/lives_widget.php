<?php
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($data) ) $data = array();

?>
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive hidden">
            <h4 class="m-t-0 header-title"><b>Company Lives</b></h4>
            <p>Select the live you want to investigate.</p>
            <table id="lives_table" class="table table-hover" width="100%">
                <thead>
                <tr class="">
                    <?php
                    foreach($headings as $item)
                    {

                        if ( StartsWith(strtoupper($item), "ENCRYPTED") ) continue;
                        if ( strtoupper($item) === 'ID' ) continue;
                        print "<th>{$item}</th>\n";
                    }
                    ?>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach($data as $row)
                {
                    print "<tr>\n";
                    foreach($row as $key=>$value)
                    {
                        if ( StartsWith(strtoupper($key), "ENCRYPTED") ) continue;
                        if ( strtoupper($key) === 'ID' ) {
                            $life_id = $value;
                            continue;
                        }
                        print "<td>{$value}</td>\n";

                    }
                    print "<td><div class='pull-right'><a id='view_commissions' class='btn btn-white btn-xs waves-light waves-effect' href='".base_url("support/commissions/company/{$company_id}/{$life_id}")."'>Commissions <i class='ion-arrow-right-c'></i></a></div></td>\n";
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