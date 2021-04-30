<?php
    if ( ! isset($display) ) $display = "";
    if ( ! isset($download_button) ) $download_button = "";
?>
<div class="download-report-row-container grayBorder-b">
    <div class="m-b-10">
        <div class="download-row">
            <label class="m-t-5"><?=$display?></label>
            <a class="download-row-info-btn btn-sm btn-default pull-right m-l-10" href="#"><i class="fa fa-chevron-circle-down"></i> Info</a>
            <a class="download-row-less-btn btn-sm btn-default pull-right m-l-10 hidden"><i class="fa fa-chevron-circle-up"></i> Less</a>
            <?=$download_button?>

            <div class="clearfix"></div>
        </div>
        <div class="download-help hidden">
            <p class="form-description modal-ignored-text">
                Import file for Transamerica Commissions system.  This file requires the following optional fields to generate: Policy
            </p>
        </div>
    </div>
</div>




