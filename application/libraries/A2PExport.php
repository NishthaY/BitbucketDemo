<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class A2PExport
 *
 * This class hold all of the business logic behind showing, creating and
 * managing an export.
 *
 */
class A2PExport
{

    function GetS3Filename($export_id)
    {
        $CI =& get_instance();

        // EXPORT
        // Find the export we are talking about.
        $export = $CI->Export_model->select_export($export_id);
        if (empty($export)) throw new Exception("Unable to find the export record.");

        // EXPORT IDENTIFIERS
        // Find the idnetifiers for the export.
        $identifier = GetArrayStringValue('Identifier', $export);
        $identifier_type = GetArrayStringValue('IdentifierType', $export);

        $export_filename = "IDENTIFIER_IDENTIFIERTYPE_EXPORTID.zip";

        $export_filename = ReplaceFor($export_filename, 'EXPORTID', $export_id);
        $export_filename = ReplaceFor($export_filename, 'IDENTIFIERTYPE', $identifier_type);
        $export_filename = ReplaceFor($export_filename, 'IDENTIFIER', $identifier);

        return $export_filename;
    }

    /**
     * GetDownloadFilename
     *
     * This function will return the download filename for a given export.
     *
     * @param $export_id
     * @return string
     * @throws Exception
     */
    function GetDownloadFilename($export_id)
    {

        $CI =& get_instance();

        // EXPORT
        // Find the export we are talking about.
        $export = $CI->Export_model->select_export($export_id);
        if (empty($export)) throw new Exception("Unable to find the export record.");

        // Calculate the level
        $level = LevelTag();
        if ($level === 'PROD') $level = "";
        if ($level !== '') $level .= "_";

        // Calculate the identifier name.
        $identifier = GetArrayStringValue('Identifier', $export);
        $identifier_type = GetArrayStringValue('IdentifierType', $export);
        $identifier_name = GetIdentifierName($identifier, $identifier_type);

        // Get the report code.
        $report_code = $CI->Export_model->select_export_property_by_key($export_id, 'report_code');

        // Get the year the reports will cover.
        $year = $CI->Export_model->select_export_property_by_key($export_id, 'year');

        $output_filename = "{$level}A2P_{$identifier_name}_Export_{$year}_{$report_code}.zip";
        $output_filename = GetFilenameFromString($output_filename);

        return GetFilenameFromString($output_filename);
    }

    /**
     * DeleteExport
     *
     * This function will delete the specified export.
     *
     * @param $export_id
     * @return bool
     * @throws Exception
     */
    function DeleteExport($export_id, $user_id)
    {
        $CI =& get_instance();

        // Get the export record.
        $export = $CI->Export_model->select_export($export_id);

        $identifier = GetArrayStringValue('Identifier', $export);
        $identifier_type = GetArrayStringValue('IdentifierType', $export);
        $identifier_name = GetIdentifierName($identifier, $identifier_type);
        $status = GetArrayStringValue('Status', $export);

        // Make sure we are in a status that allows the delete to happen.
        $allowed_status_values = ['REQUESTED', 'FAILED', 'COMPLETE', 'NO_RESULTS'];
        if (!in_array($status, $allowed_status_values)) return FALSE;

        $export_filename = $this->GetS3Filename($export_id);
        $report_code = $CI->Export_model->select_export_property_by_key($export_id, 'report_code');

        // remove the file from S3.
        S3GetClient();
        $prefix = GetS3Prefix('export', $identifier, $identifier_type);
        S3DeleteFile(S3_BUCKET, $prefix, "{$export_filename}");

        // delete the properties
        $CI->Export_model->delete_export_properties($export_id);

        // delete the export
        $CI->Export_model->delete_export($export_id);

        $payload = array();
        $payload['Identifier'] = $identifier;
        $payload['IdentifierType'] = $identifier_type;
        $payload['IdentifierName'] = $identifier_name;
        $payload['report_code']    = $report_code;
        $payload['export_id']   = $export_id;
        $payload['filename']    = $export_filename;
        AuditIt('Deleted an export.', $payload, $user_id, A2P_COMPANY_ID);

        return TRUE;
    }

    /**
     * CancelExport
     *
     * This function will cancel a pending export.
     *
     * @param $export_id
     * @return bool
     */
    function CancelExport($export_id)
    {
        $CI =& get_instance();

        // Get the export record.
        $export = $CI->Export_model->select_export($export_id);

        $identifier = GetArrayStringValue('Identifier', $export);
        $identifier_type = GetArrayStringValue('IdentifierType', $export);
        $status = GetArrayStringValue('Status', $export);

        // if the status is still REQUESTED
        if ($status !== 'REQUESTED') return FALSE;

        // Find the job
        $job_id = $CI->Export_model->select_export_property_by_key($export_id, 'job_id');


        // delete the job if not started.
        if (GetStringValue($job_id) !== '')
        {
            $job = $CI->Queue_model->get_job($job_id);
            if (GetArrayStringValue('StarTime', $job) !== '') return FALSE;
            $CI->Queue_model->delete_job($job_id);
        }

        // delete the properties
        $CI->Export_model->delete_export_properties($export_id);

        // delete the export
        $CI->Export_model->delete_export($export_id);

        return TRUE;

    }

    /**
     * RenderManageWidget
     *
     * This function will return the HTML that shows the manage widget
     * on the export screen.
     *
     * @param $identifier
     * @param $identifier_type
     * @return string|void
     * @throws Exception
     */
    function RenderManageWidget($identifier, $identifier_type)
    {
        $CI =& get_instance();

        $url_identifier = "";
        if ($identifier_type === 'companyparent') $url_identifier = 'parent';
        if ($identifier_type === 'company') $url_identifier = 'company';

        $exports = $CI->Export_model->select_all_exports($identifier, $identifier_type);
        $total = count($exports);

        $action_array = array();
        $action_array['identifier'] = $identifier;
        $action_array['identifier_type'] = $identifier_type;
        $action_array['url_identifier'] = $url_identifier;

        $table_headers = [];
        $table_headers[] = 'Status';
        $table_headers[] = 'Name';
        $table_headers[] = 'Requested';
        $table_headers[] = 'Actions';

        $table_data = array();
        foreach ($exports as $export) {
            $status = GetArrayStringValue('Status', $export);
            $action_array['status'] = $status;

            $export_id = GetArrayStringValue('Id', $export);
            $action_array['export_id'] = $export_id;

            $timestamp = GetArrayStringValue("Created", $export);
            $date = date('M jS, Y g:i A', strtotime($timestamp));

            $row = [];
            $row['Status'] = $status;
            $row['Name'] = $this->GetDownloadFilename($export_id);
            $row['Requested'] = $date;
            $row['Actions'] = RenderViewAsString('archive/export_manage_widget_row_buttons', $action_array);
            $table_data[] = $row;
        }

        $view = "archive/export_manage_widget";

        $view_array = array();
        $view_array['data'] = $table_data;
        $view_array['headers'] = $table_headers;
        $html = RenderViewAsString($view, $view_array);

        return $html;
    }

    /**
     * RenderCreateWidget
     *
     * This function will display the HTML for the create widget shown on
     * the export screen.
     *
     * @param $identifier
     * @param $identifier_type
     * @return string|void
     */
    function RenderCreateWidget($identifier, $identifier_type)
    {
        $CI =& get_instance();

        $view = "archive/export_create_widget";

        $reports = $CI->Reporting_model->select_report_types();
        $checkboxes = array();
        foreach ($reports as $report) {
            $code = GetArrayStringValue('Name', $report);
            $desc = GetArrayStringValue('Display', $report);
            $checkbox = [];
            $checkbox['id'] = $code;
            $checkbox['checked'] = false;
            $checkbox['inline_description'] = $desc;
            $checkboxes[] = $checkbox;
        }


        $form = new UISimpleForm('create_export_form', 'create_export', base_url('support/exports/create'));
        $form->addElement($form->checkboxes($checkboxes, 'cbox-'));
        $form->addElement($form->hiddenInput('identifier', $identifier));
        $form->addElement($form->hiddenInput('identifier_type', $identifier_type));
        $form->addElement($form->submitButton("submit_button", "Request", "btn-primary", [], true));
        $form = $form->render();

        $view_array = [];
        $view_array['form'] = $form;

        return RenderViewAsString('archive/export_manage_widget_create_card', $view_array);
    }

    /**
     * RenderRemoveWidget
     *
     * This function will return the HTML for the remove confirmation widget
     * shown on the export screen.
     *
     * @param $export_id
     * @return string|void
     * @throws Exception
     */
    function RenderRemoveWidget($export_id)
    {
        $CI =& get_instance();

        // Get the export record.
        $export = $CI->Export_model->select_export($export_id);

        $identifier = GetArrayStringValue('Identifier', $export);
        $identifier_type = GetArrayStringValue('IdentifierType', $export);
        $identifier_name = GetIdentifierName($identifier, $identifier_type);


        $filename = $this->GetDownloadFilename($export_id);

        // Set the form title based on if we are doing an add or edit.
        $title = $filename;

        $form = new UIModalForm("remove_export_form", "remove_export_form", base_url("support/exports/delete/{$export_id}"));
        $form->setTitle($title);
        $form->setCollapsable(true);
        $form->addElement($form->htmlView("archive/export_confirm_remove", [ 'filename' => $filename]));
        $form->addElement($form->hiddenInput("identifier", $identifier));
        $form->addElement($form->hiddenInput("identifier_type", $identifier_type));
        $form->addElement($form->submitButton("yes_btn", "Yes", "btn-primary pull-right"));
        $form->addElement($form->button("no_btn", "No", "btn-default pull-right"));
        $form_html = $form->render();

        return $form_html;
    }

}
