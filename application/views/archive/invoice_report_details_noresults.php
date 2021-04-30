<?php
if ( ! isset($data) ) $data = array();
if ( ! isset($total) ) $total = 0;
if ( ! isset($report_date) ) $report_date = "";

$total = GetReportMoneyValue($total);
$now = date('m/d/Y');
$report_date = strtoupper($report_date);
?>
<div class="card-box table-responsive">
    <h4 class="m-t-0 header-title"><b>Invoice Report</b></h4>
    <p>&nbsp;</p>

    <p>
        No results found.
    </p>

</div>
