<?php
if ( ! isset($data) ) $data = array();
if ( ! isset($total) ) $total = 0;
if ( ! isset($report_date) ) $report_date = "";

$total = GetReportMoneyValue($total);
$now = date('m/d/Y');
$report_date = strtoupper($report_date);
?>
<div class="card-box table-responsive">
    <h4 class="m-t-0 header-title"><b><?=$report_date?></b></h4>
    <p>
        Total: <?=$total?> ( as of <?=$now?> )
    </p>

    <?=RenderViewAsString("archive/invoice_report_details_table", ['data'=>$data]);?>


</div>
