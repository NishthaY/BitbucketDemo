<?php
    if ( !isset($hostname) ) $hostname = "www.advice2pay.com";
    if ( !isset($company_id) ) $company_id = "";
    if ( !isset($report_code) ) $report_code = "";
    if ( !isset($import_date) ) $import_date = "";
    if ( !isset($carrier_id) ) $carrier_id = "";
    if ( !isset($critical) ) $critical = true;
    if ( !isset($file_details_saved) ) $file_details_saved = array();
    if ( !isset($file_details_encrypted) ) $file_details_encrypted = array();

    $carrier_name = GetCompanyCarrierDescription($company_id, $carrier_id);
?>
<?php
if ( $critical )
{
?>
    <h2>Please take action!</h2><BR>
    <BR>
    An AWS report has been generated with zero length.  The automatic retry immediately attempted to transfer the file
    again, but was unable to correct the problem.  Please investigate.<BR>
    <BR>
    <b>Company Name:</b> <?=GetCompanyName($company_id)?><br>
    <b>Report Code:</b> <?=$report_code?><br>
    <b>Import Date:</b> <?=$import_date?><br>
    <b>Carrier:</b> <?=$carrier_name?><br>
    <BR>
    <h3>File Info After Save</h3>
    <div>
        <code>
            <?=pprint_r($file_details_saved);?>
        </code>
    </div>
    <BR>
    <h3>File Info After Encrypt</h3>
    <div>
        <code>
            <?=pprint_r($file_details_encrypted);?>
        </code>
    </div>
    <BR>
    <h3>Manual Retry</h3>
    You may resend the report manually using the information above and the following command line tool.
    <div>
        <code>
            <?=pprint_r("php index.php tools/AWSReports resend");?>

        </code>
    </div>
<?php
}else{
?>
    <b>Please be advised.</b><BR>
    <BR>
    An AWS report was generated with zero length.  However, the automatic retry was able to transfer it
    on the second pass.  For more information, please review the specifics below:<BR>
    <BR>
    <b>Company Name:</b> <?=GetCompanyName($company_id)?><br>
    <b>Report Code:</b> <?=$report_code?><br>
    <b>Import Date:</b> <?=$import_date?><br>
    <b>Carrier:</b> <?=$carrier_name?><br>
    <BR>
    <h2>File Info After Save</h2>
    <div>
        <code>
            <?=pprint_r($file_details_saved);?>
        </code>
    </div>
    <BR>
    <h2>File Info After Encrypt</h2>
    <div>
        <code>
            <?=pprint_r($file_details_encrypted);?>
        </code>
    </div>
<?php
}
?>


