<?php
function ParentImportDataWidget()
{
    $CI = &get_instance();
    $CI->load->helper('s3');

    $companyparent_id = GetSessionValue("companyparent_id");
    if ( ! IsAuthenticated("parent_company_write") ) return "";


    // Using the filename you just created, collect the S3 for data needed to
    // create an upload.
    $upload = GetUploadFormData(null, $companyparent_id);

    // Render the Widget
    $view_array = array();
    $view_array = array_merge($view_array, array("upload_attributes" => $upload['attributes']));
    $view_array = array_merge($view_array, array("upload_inputs" => $upload['inputs']));
    $html = RenderViewAsString("workflows/parent_import_csv/widget_upload", $view_array);
    return $html;
}