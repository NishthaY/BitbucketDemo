<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($critical) ) $critical = false;
?>
<div class="confirmation-div">
    Are you sure you want to finalize the following reports?
    <ul>
        <?php
        foreach($data as $item)
        {
            $strikethrough = "";
            if (isset($item['Warnings']) && count($item['Warnings']) > 0 ) $strikethrough = 'strikethrough';

            $company_desc = GetArrayStringValue("CompanyName", $item);
            $upload_desc = GetArrayStringValue("UploadDescription", $item);
            print "<li class='{$strikethrough}'>{$company_desc} - {$upload_desc}</li>\n";
        }
        ?>
    </ul>
</div>
<?php
foreach($data as $item)
{
    $company_name = GetArrayStringValue("CompanyName", $item);
    $upload_desc = GetArrayStringValue("UploadDescription", $item);
    $warnings = array();
    if (isset($item['Warnings']) ) $warnings = $item['Warnings'];

    $view_array = array();
    $view_array['company_name'] = $company_name;
    $view_array['upload_desc'] = $upload_desc;
    $view_array['warnings'] = $warnings;
    $view_array['critical'] = $critical;
    print RenderViewAsString('reports/confirm_finalization_warning', $view_array);
}
?>