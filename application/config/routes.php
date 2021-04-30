<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'dashboard';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "dashboard" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route['bah'] = 'TestHarness/index';
$route['bah/(:any)'] = 'TestHarness/index/$1';
$route['bah/index'] = 'TestHarness/index';
$route['bah/index/(:any)'] = 'TestHarness/index/$1';

// Admin Controller
//$route['admin/query'] = 'admin/query';
//$route['admin/query/submit'] = 'admin/query_run';
//$route['admin/widget/queryeditor'] = 'admin/render_query_editor_form';
//$route['admin/widget/queryresults'] = 'admin/render_query_results';

// Auth Controller
$route['auth']   = 'auth/login';
$route['auth/login']   = 'auth/login';
$route['auth/code']   = 'auth/code';
$route['auth/code/verify']   = 'auth/verify_code';
$route['auth/code/resend']   = 'auth/code_resend';
$route['auth/phone']   = 'auth/phone';
$route['auth/phone/save']   = 'auth/phone_save';
$route['auth/logout']   = 'auth/logout';
$route['auth/forgot']   = 'auth/forgot';
$route['auth/password/reset']   = 'auth/password_reset';
$route['auth/password']   = 'auth/password';
$route['auth/password/save'] = 'auth/password_save';
$route['auth/password/validate']   = 'auth/password_validate';
$route['auth/authenticate']   = 'auth/login_authenticate';
$route['auth/permission']   = 'auth/permission_error';
$route['auth/404']   = 'auth/error_404';
$route['auth/pusher']   = 'auth/pusher';

// Main Application
$route['dashboard'] = 'dashboard';
$route['dashboard/index'] = 'dashboard';
$route['dashboard/support'] = 'dashboard/support';
$route['dashboard/parent'] = 'DashboardParent/quick_look';
$route['dashboard/parent/workflow'] = 'DashboardParent/sample_workflow';
$route['dashboard/parent/widget/multicompany'] = 'DashboardParent/widget_multi_company';
$route['dashboard/parent/widget/multicompany/details/(:num)'] = 'DashboardParent/ajax_multi_company_row_details/$1';
$route['dashboard/parent/save/skip_month'] = 'upload/restore/parent';
$route['dashboard/tools'] = 'dashboard/tools';
$route['dashboard/security'] = 'dashboard/security';
$route['dashboard/changeback'] = 'dashboard/changeback';
$route['dashboard/save/getting_started'] = 'dashboard/save_getting_started';
$route['dashboard/widget/spenddetails'] = 'dashboard/render_spend_details_table';
$route['dashboard/widget/spend'] = 'dashboard/render_spend_cardbox';
$route['dashboard/widget/spend_ytd'] = 'dashboard/render_spend_ytd_cardbox';
$route['dashboard/widget/spend_washretro_ytd'] = 'dashboard/render_spend_wash_retro_ytd_cardbox';
$route['dashboard/widget/spend_washretro_percentage'] = 'dashboard/render_spend_wash_retro_ytd_percentage_cardbox';
$route['dashboard/widget/recent_reports'] = 'dashboard/render_recent_reports_table';
$route['dashboard/widget/recent_changeto'] = 'dashboard/render_recent_changeto_table';
$route['dashboard/company/save/skip_month'] = 'upload/restore/company';

// Settings Controller
$route['settings/account'] = 'settings/account';
$route['settings/account/save'] = 'settings/account_save';
$route['settings/password'] = 'settings/password';
$route['settings/password/save'] = 'auth/password_save';
$route['settings/password/validate'] = 'auth/password_validate';
$route['settings/banner/deactivate'] = 'settings/enterprise_banner_deactivate';

// Companies
$route['companies/features/(:num)'] = 'companies/features/$1';
$route['companies/feature/toggle/(:any)/(:num)'] = 'companies/toggle_feature/$1/$2';
$route['companies/feature/toggle/(:any)/(:num)/(:any)/(:any)'] = 'companies/toggle_feature/$1/$2/$3/$4';
$route['companies/manage'] = 'companies/company_list';
$route['companies/add'] = 'companies/company_add';
$route['companies/edit'] = 'companies/company_edit';
$route['companies/widget/list'] = 'companies/render_companies_table';
$route['companies/widget/add'] = 'companies/render_add_company_form';
$route['companies/widget/edit/(:num)'] = 'companies/render_edit_company_form/$1';
$route['companies/widget/changeto/(:num)'] = 'companies/render_changeto_company_form/$1';
$route['companies/widget/rollback/(:num)'] = 'companies/render_rollback_company_form/$1';
$route['companies/widget/feature/(:num)/(:any)'] = 'companies/render_feature_widget';
$route['companies/widget/file_transfer/(:num)'] = 'companies/render_file_transfer_form/$1';
$route['companies/widget/targetable_feature/(:num)'] = 'companies/render_targetable_feature_form/$1';
$route['companies/feature/save/targetable_feature'] = 'companies/feature_add';
$route['companies/feature/remove/targetable_feature'] = 'companies/feature_remove';
$route['companies/widget/commission_tracking/(:num)'] = 'companies/render_commission_tracking_form/$1';
$route['companies/widget/column_normalization/(:num)/(:any)/(:any)'] = 'companies/render_column_normalization_form/$1/$2/$3';
$route['companies/widget/beneficiary_mapping/(:num)/(:any)/(:any)'] = 'companies/render_beneficiary_mapping_form/$1/$2/$3';
$route['companies/widget/default_carrier/(:num)']     = 'companies/render_default_carrier/$1';
$route['companies/widget/default_plan/(:num)']     = 'companies/render_default_plan/$1';
$route['companies/disable'] = 'companies/company_disable';
$route['companies/enable'] = 'companies/company_enable';
$route['companies/changeto'] = 'companies/company_changeto';
$route['companies/rollback'] = 'companies/company_rollback';
$route['companies/validate/company'] = 'companies/validate_company';
$route['company/preference/save'] = 'companies/company_savepref';
$route['companies/assignment/(:num)'] = 'companies/user_assignment/$1';
$route['companies/assignment/assign'] = 'companies/assign_responsibility';
$route['companies/assignment/unassign'] = 'companies/unassign_responsibility';
$route['companies/widget/assignments'] = 'companies/render_assignment_table';
$route['companies/widget/assignments/(:num)'] = 'companies/render_assignment_table/$1';
$route['companies/feature/save/file_transfer'] = 'companies/file_transfer_save';
$route['companies/feature/save/commission_tracking'] = 'companies/commission_tracking_save';
$route['companies/feature/save/column_normalization'] = 'companies/column_normalization_save';
$route['companies/feature/save/default_carrier'] = 'companies/default_carrier_save';
$route['companies/feature/save/beneficiary_mapping'] = 'companies/beneficiary_mapping_save';
$route['companies/feature/save/default_plan'] = 'companies/default_plan_save';
$route['companies/widget/default_clarifications/(:num)']     = 'companies/render_default_clarifications/$1';
$route['companies/feature/save/default_clarifications'] = 'companies/default_clarifications_save';


// Support
$route['support/widget/director/details'] = 'support/render_director_details';
$route['support/widget/app_options'] = 'support/render_app_options';
$route['support/widget/keypool'] = 'support/render_keypool';
$route['support/widget/pg_options'] = 'support/render_pg_options';
$route['support/widget/dynos'] = 'support/render_dynos';
$route['support/widget/jobs/failed'] = 'support/render_failed_jobs';
$route['support/widget/jobs/waiting'] = 'support/render_waiting_jobs';
$route['support/widget/jobs/running'] = 'support/render_running_jobs';
$route['support/widget/jobs/running/data'] = 'support/render_running_jobs_table';
$route['support/widget/jobs/waiting/data'] = 'support/render_waiting_jobs_table';
$route['support/widget/jobs/failed/data'] = 'support/render_failed_jobs_table';
$route['support/widget/decode_data'] = 'support/render_decode_data_widget';
$route['support/decode_data'] = 'support/decode_data';
$route['support/widget/encode_data'] = 'support/render_encode_data_widget';
$route['support/encode_data'] = 'support/encode_data';
$route['support/jobs/detail/(:num)'] = 'support/render_job_details_form/$1';
$route['support/jobs/clear'] = 'support/clear_job_alert';
$route['support/dyno/reset/(:any)'] = 'support/reset_dyno/$1';
$route['support/dyno/stop/(:any)'] = 'support/stop_dyno/$1';
$route['support/dyno/detail/(:any)'] = 'support/render_dyno_details_form/$1';
$route['support/keytool/create'] = 'support/keypool_create';

// Users
$route['users/manage'] = 'users/users_list';
$route['users/widget/list'] = 'users/render_users_table';
$route['users/widget/add'] = 'users/render_add_user_form';
$route['users/widget/edit/(:num)'] = 'users/render_edit_user_form/$1';
$route['users/widget/delete/(:num)'] = 'users/render_delete_user_form/$1';
$route['users/widget/whoami'] = 'users/render_whoami';
$route['users/add'] = 'users/user_add';
$route['users/edit'] = 'users/user_edit';
$route['users/delete'] = 'users/user_delete';
$route['users/disable'] = 'users/user_disable';
$route['users/enable'] = 'users/user_enable';
$route['users/validate/username'] = 'users/validate_username';
$route['users/reset/phone/(:num)'] = 'users/user_edit_reset_phone/$1';
$route['users/assignment/(:num)'] = 'users/company_assignment/$1';
$route['users/widget/assignments'] = 'users/render_assignment_table';
$route['users/widget/assignments/(:num)'] = 'users/render_assignment_table/$1';

// Parents
$route['parents/features/(:num)'] = 'CompanyParent/features/$1';
$route['parents/feature/toggle/(:any)/(:num)'] = 'CompanyParent/toggle_feature/$1/$2';
$route['parents/feature/toggle/(:any)/(:num)/(:any)/(:any)'] = 'CompanyParent/toggle_feature/$1/$2/$3/$4';
$route['parents/manage'] = 'CompanyParent/parent_list';
$route['parents/widget/rollback/(:num)'] = 'CompanyParent/render_rollback_companyparent_form/$1';
$route['parents/widget/list'] = 'CompanyParent/render_main_table';
$route['parents/widget/add'] = 'CompanyParent/render_add_parent_form';
$route['parents/widget/edit/(:num)'] = 'CompanyParent/render_edit_parent_form/$1';
$route['parents/widget/changeto/(:num)'] = 'CompanyParent/render_changeto_parent_form/$1';
$route['parents/widget/feature/(:num)/(:any)'] = 'CompanyParent/render_feature_widget';
$route['parents/widget/file_transfer/(:num)'] = 'CompanyParent/render_file_transfer_form/$1';
$route['parents/widget/targetable_feature/(:num)'] = 'CompanyParent/render_targetable_feature_form/$1';
$route['parents/feature/save/targetable_feature'] = 'CompanyParent/feature_add';
$route['parents/feature/remove/targetable_feature'] = 'CompanyParent/feature_remove';
$route['parents/widget/commission_tracking/(:num)'] = 'CompanyParent/render_commission_tracking_form/$1';
$route['parents/widget/column_normalization/(:num)/(:any)/(:any)'] = 'CompanyParent/render_column_normalization_form/$1/$2/$3';
$route['parents/widget/beneficiary_mapping/(:num)/(:any)/(:any)'] = 'CompanyParent/render_beneficiary_mapping_form/$1/$2/$3';
$route['parents/add'] = 'CompanyParent/parent_add';
$route['parents/edit'] = 'CompanyParent/parent_edit';
$route['parents/disable'] = 'CompanyParent/parent_disable';
$route['parents/enable'] = 'CompanyParent/parent_enable';
$route['parents/rollback'] = 'CompanyParent/parent_rollback';
$route['parents/changeto'] = 'CompanyParent/parent_changeto';
$route['parents/companies'] = 'CompanyParent/company_list';
$route['parents/company/add'] = 'CompanyParent/company_add';
$route['parents/company/edit'] = 'CompanyParent/company_edit';
$route['parents/company/disable'] = 'CompanyParent/company_disable';
$route['parents/company/enable'] = 'CompanyParent/company_enable';
$route['parents/company/changeto'] = 'CompanyParent/company_changeto';
$route['parents/company/widget/list'] = 'CompanyParent/render_companies_table';
$route['parents/company/widget/edit/(:num)'] = 'CompanyParent/render_edit_company_form/$1';
$route['parents/company/widget/changeto/(:num)'] = 'CompanyParent/render_changeto_company_form/$1';
$route['parents/validate/parent'] = 'CompanyParent/validate_parent';
$route['parents/feature/save/file_transfer'] = 'CompanyParent/file_transfer_save';
$route['parents/feature/save/commission_tracking'] = 'CompanyParent/commission_tracking_save';
$route['parents/feature/save/column_normalization'] = 'CompanyParent/column_normalization_save';
$route['parents/feature/save/default_carrier'] = 'CompanyParent/default_carrier_save';
$route['parents/preference/save'] = 'CompanyParent/parent_savepref';
$route['parents/upload/save/(:any)']     = 'Upload/save_parent_upload/$1';
$route['parents/widget/default_carrier/(:num)']     = 'CompanyParent/render_default_carrier/$1';
$route['parents/widget/default_plan/(:num)']     = 'CompanyParent/render_default_plan/$1';
$route['parents/feature/save/beneficiary_mapping'] = 'CompanyParent/beneficiary_mapping_save';
$route['parents/feature/save/default_plan'] = 'CompanyParent/default_plan_save';
$route['parents/widget/default_clarifications/(:num)'] = 'CompanyParent/render_default_clarifications/$1';
$route['parents/feature/save/default_clarifications'] = 'CompanyParent/default_clarifications_save';


// reports
$route['reports'] = 'reports/index';
$route['reports/finalize/(:any)'] = 'reports/render_finalize_reports_form/$1';              // Generate Finalize Confirmation Dialog
$route['reports/finalize/(:any)/(:num)'] = 'reports/render_finalize_reports_form/$1/$2';    // Generate Finalize Confirmation Dialog Grouped
$route['reports/finalized'] = 'reports/finalize';        // Commit reports finalized.
$route['reports/list/(:num)/(:num)/(:any)'] = 'reports/render_downloadable_reports_form/$1/$2/$3';
$route['reports/warnings'] = 'reports/warnings';
$route['reports/warnings/(:any)/(:num)'] = 'reports/render_reports_warning_form/$2/$1';
$route['reports/settings'] = 'reports/settings';
$route['reports/settings/(:num)'] = 'reports/settings/$1';


// tools
$route['tools/pusher'] = 'tools/PusherTool/push';
$route['tools/FileTransfer'] = 'tools/FileTransfer/resend';
$route['tools/CountCompanyRecords/(:num)'] = 'tools/DBTools/CountCompanyRecords/$1';
$route['tools/RemoveLostKeys'] = 'tools/RemoveLostKeys/index';
$route['tools/RemoveLostKeys/remove'] = 'tools/RemoveLostKeys/remove';
$route['tools/RemoveLostKeys/remove/(:num)'] = 'tools/RemoveLostKeys/remove/$1';
$route['tools/(:any)'] = 'tools/$1';
$route['tools/(:any)/(:any)'] = 'tools/$1/$2';
$route['tools/(:any)/(:any)/(:any)'] = 'tools/$1/$2/$3';
$route['tools/(:any)/(:any)/(:any)/(:any)'] = 'tools/$1/$2/$3/$4';
$route['tools/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'tools/$1/$2/$3/$4/$5';

// cli
$route['cli/ArchiveData/schedule/(:any)/(:any)/(:any)/(:any)/(:any)/']         = 'recurring/ArchiveData/schedule/$1/$2/$3/$4/$5';

/*
$route['cli/WorkflowBackgroundTaskProcessor/index/(:num)/(:any)']                   = 'cli/WorkflowBackgroundTaskProcessor/index/$1/$2';
$route['cli/WorkflowBackgroundTaskProcessor/index/(:num)/(:any)/(:any)']            = 'cli/WorkflowBackgroundTaskProcessor/index/$1/$2/$3';
$route['cli/WorkflowBackgroundTaskProcessor/index/(:num)/(:any)/(:any)/(:num)']     = 'cli/WorkflowBackgroundTaskProcessor/index/$1/$2/$3/$4';
$route['cli/WorkflowBackgroundTaskProcessor/verbose/(:num)/(:any)/(:any)']          = 'cli/WorkflowBackgroundTaskProcessor/verbose/$1/$2/$3';
$route['cli/WorkflowBackgroundTaskProcessor/verbose/(:num)/(:any)/(:any)/(:num)']   = 'cli/WorkflowBackgroundTaskProcessor/verbose/$1/$2/$3/$4';

$route['cli/ParentUploadImport/index/(:num)/(:any)']            = 'cli/ParentUploadImport/index/$1/$2';
$route['cli/ParentUploadImport/index/(:num)/(:any)/(:any)']            = 'cli/ParentUploadImport/index/$1/$2/$3';
$route['cli/ParentUploadImport/index/(:num)/(:any)/(:any)/(:num)']     = 'cli/ParentUploadImport/index/$1/$2/$3/$4';
$route['cli/ParentUploadImport/verbose/(:num)/(:any)/(:any)']          = 'cli/ParentUploadImport/verbose/$1/$2/$3';

$route['cli/ParentUploadParseCSV/index/(:num)/(:any)']            = 'cli/ParentUploadParseCSV/index/$1/$2';
$route['cli/ParentUploadParseCSV/index/(:num)/(:any)/(:any)']            = 'cli/ParentUploadParseCSV/index/$1/$2/$3';
$route['cli/ParentUploadParseCSV/index/(:num)/(:any)/(:any)/(:num)']     = 'cli/ParentUploadParseCSV/index/$1/$2/$3/$4';
$route['cli/ParentUploadParseCSV/verbose/(:num)/(:any)/(:any)']          = 'cli/ParentUploadParseCSV/verbose/$1/$2/$3';

$route['cli/ParentUploadValidateCSV/index/(:num)/(:any)']                   = 'cli/ParentUploadValidateCSV/index/$1/$2';
$route['cli/ParentUploadValidateCSV/index/(:num)/(:any)/(:any)']            = 'cli/ParentUploadValidateCSV/index/$1/$2/$3';
$route['cli/ParentUploadValidateCSV/index/(:num)/(:any)/(:any)/(:num)']     = 'cli/ParentUploadValidateCSV/index/$1/$2/$3/$4';
$route['cli/ParentUploadValidateCSV/verbose/(:num)/(:any)/(:any)']          = 'cli/ParentUploadValidateCSV/verbose/$1/$2/$3';

$route['cli/ParentUploadMapCompanies/index/(:num)/(:any)']            = 'cli/ParentUploadMapCompanies/index/$1/$2';
$route['cli/ParentUploadMapCompanies/index/(:num)/(:any)/(:any)']            = 'cli/ParentUploadMapCompanies/index/$1/$2/$3';
$route['cli/ParentUploadMapCompanies/index/(:num)/(:any)/(:any)/(:num)']     = 'cli/ParentUploadMapCompanies/index/$1/$2/$3/$4';
$route['cli/ParentUploadMapCompanies/verbose/(:num)/(:any)/(:any)']          = 'cli/ParentUploadMapCompanies/verbose/$1/$2/$3';

$route['cli/ParentUploadSplitCSV/index/(:num)/(:any)']                   = 'cli/ParentUploadSplitCSV/index/$1/$2';
$route['cli/ParentUploadSplitCSV/index/(:num)/(:any)/(:any)']            = 'cli/ParentUploadSplitCSV/index/$1/$2/$3';
$route['cli/ParentUploadSplitCSV/index/(:num)/(:any)/(:any)/(:num)']     = 'cli/ParentUploadSplitCSV/index/$1/$2/$3/$4';
$route['cli/ParentUploadSplitCSV/verbose/(:num)/(:any)/(:any)']          = 'cli/ParentUploadSplitCSV/verbose/$1/$2/$3';

$route['cli/SampleWorkflowStep1/index/(:num)/(:any)']            = 'cli/SampleWorkflowStep1/index/$1/$2';
$route['cli/SampleWorkflowStep1/index/(:num)/(:any)/(:any)']            = 'cli/SampleWorkflowStep1/index/$1/$2/$3';
$route['cli/SampleWorkflowStep1/index/(:num)/(:any)/(:any)/(:num)']     = 'cli/SampleWorkflowStep1/index/$1/$2/$3/$4';
$route['cli/SampleWorkflowStep1/verbose/(:num)/(:any)/(:any)']          = 'cli/SampleWorkflowStep1/verbose/$1/$2/$3';

$route['cli/SampleWorkflowStep2/index/(:num)/(:any)']            = 'cli/SampleWorkflowStep2/index/$1/$2';
$route['cli/SampleWorkflowStep2/index/(:num)/(:any)/(:any)']         = 'cli/SampleWorkflowStep2/index/$1/$2/$3';
$route['cli/SampleWorkflowStep2/index/(:num)/(:any)/(:any)/(:num)']         = 'cli/SampleWorkflowStep2/index/$1/$2/$3/$4';
$route['cli/SampleWorkflowStep2/verbose/(:num)/(:any)/(:any)']         = 'cli/SampleWorkflowStep2/verbose/$1/$2/$3';

$route['cli/SampleWorkflowStep3/index/(:num)/(:any)']            = 'cli/SampleWorkflowStep3/index/$1/$2';
$route['cli/SampleWorkflowStep3/index/(:num)/(:any)/(:any)']         = 'cli/SampleWorkflowStep3/index/$1/$2/$3';
$route['cli/SampleWorkflowStep3/index/(:num)/(:any)/(:any)/(:num)']         = 'cli/SampleWorkflowStep3/index/$1/$2/$3/$4';
$route['cli/SampleWorkflowStep3/verbose/(:num)/(:any)/(:any)']         = 'cli/SampleWorkflowStep3/verbose/$1/$2/$3';

$route['cli/ParseCSVUpload/index/(:num)/(:any)']            = 'cli/ParseCSVUpload/index/$1/$2';
$route['cli/ParseCSVUpload/index/(:num)/(:any)/(:any)']         = 'cli/ParseCSVUpload/index/$1/$2/$3';
$route['cli/ParseCSVUpload/index/(:num)/(:any)/(:any)/(:num)']         = 'cli/ParseCSVUpload/index/$1/$2/$3/$4';
$route['cli/ParseCSVUpload/verbose/(:num)/(:any)/(:any)']         = 'cli/ParseCSVUpload/verbose/$1/$2/$3';

$route['cli/ValidateCSVUpload/index/(:num)/(:any)']            = 'cli/ValidateCSVUpload/index/$1/$2';
$route['cli/ValidateCSVUpload/index/(:num)/(:any)/(:any)']      = 'cli/ValidateCSVUpload/index/$1/$2/$3';
$route['cli/ValidateCSVUpload/index/(:num)/(:any)/(:any)/(:num)']      = 'cli/ValidateCSVUpload/index/$1/$2/$3/$4';
$route['cli/ValidateCSVUpload/verbose/(:num)/(:any)/(:any)']      = 'cli/ValidateCSVUpload/verbose/$1/$2/$3';

$route['cli/GenerateImportFiles/index/(:num)/(:any)']            = 'cli/GenerateImportFiles/index/$1/$2';
$route['cli/GenerateImportFiles/index/(:num)/(:any)/(:any)']        = 'cli/GenerateImportFiles/index/$1/$2/$3';
$route['cli/GenerateImportFiles/index/(:num)/(:any)/(:any)/(:num)']        = 'cli/GenerateImportFiles/index/$1/$2/$3/$4';
$route['cli/GenerateImportFiles/verbose/(:num)/(:any)/(:any)']        = 'cli/GenerateImportFiles/verbose/$1/$2/$3';

$route['cli/LoadImportFiles/index/(:num)/(:any)']            = 'cli/LoadImportFiles/index/$1/$2';
$route['cli/LoadImportFiles/index/(:num)/(:any)/(:any)']        = 'cli/LoadImportFiles/index/$1/$2/$3';
$route['cli/LoadImportFiles/index/(:num)/(:any)/(:any)/(:num)']        = 'cli/LoadImportFiles/index/$1/$2/$3/$4';
$route['cli/LoadImportFiles/verbose/(:num)/(:any)']        = 'cli/LoadImportFiles/verbose/$1/$2';
$route['cli/LoadImportFiles/verbose/(:num)/(:any)/(:any)']        = 'cli/LoadImportFiles/verbose/$1/$2/$3';

$route['cli/EchoBatchImport/index/(:num)/(:any)']            = 'cli/EchoBatchImport/index/$1/$2';
$route['cli/EchoBatchImport/index/(:num)/(:any)/(:any)']        = 'cli/EchoBatchImport/index/$1/$2/$3';
$route['cli/EchoBatchImport/index/(:num)/(:any)/(:any)/(:num)']        = 'cli/EchoBatchImport/index/$1/$2/$3/$4';
$route['cli/EchoBatchImport/verbose/(:num)/(:any)/(:any)']        = 'cli/EchoBatchImport/verbose/$1/$2/$3';

$route['cli/GenerateReports/index/(:num)/(:any)']            = 'cli/GenerateReports/index/$1/$2';
$route['cli/GenerateReports/index/(:num)/(:any)/(:any)']        = 'cli/GenerateReports/index/$1/$2/$3';
$route['cli/GenerateReports/index/(:num)/(:any)/(:any)/(:num)']        = 'cli/GenerateReports/index/$1/$2/$3/$4';
$route['cli/GenerateReports/verbose/(:num)/(:any)/(:any)']        = 'cli/GenerateReports/verbose/$1/$2/$3';

$route['cli/FinalizeReports/index/(:num)/(:any)']            = 'cli/FinalizeReports/index/$1/$2';
$route['cli/FinalizeReports/index/(:num)/(:any)/(:any)']        = 'cli/FinalizeReports/index/$1/$2/$3';
$route['cli/FinalizeReports/index/(:num)/(:any)/(:any)/(:num)']        = 'cli/FinalizeReports/index/$1/$2/$3/$4';
$route['cli/FinalizeReports/verbose/(:num)/(:any)/(:any)']        = 'cli/FinalizeReports/verbose/$1/$2/$3';

$route['cli/FileTransfer/index/(:num)/(:any)']                      = 'cli/FileTransfer/index/$1/$2';
$route['cli/FileTransfer/index/(:num)/(:any)/(:any)']               = 'cli/FileTransfer/index/$1/$2/$3';
$route['cli/FileTransfer/index/(:num)/(:any)/(:any)/(:num)']        = 'cli/FileTransfer/index/$1/$2/$3/$4';
$route['cli/FileTransfer/verbose/(:num)/(:any)/(:any)']             = 'cli/FileTransfer/verbose/$1/$2/$3';
$route['cli/FileTransfer/resend/(:num)/(:num)/(:num)/(:num)']       = 'cli/FileTransfer/resend/$1/$2/$3/$4';
$route['cli/FileTransfer/report/(:any)/(:any)/(:any)']              = 'cli/FileTransfer/report/$1/$2/$3';                               // Command line request no entity filters
$route['cli/FileTransfer/report/(:any)/(:any)/(:any)/(:any)/(:any)']              = 'cli/FileTransfer/report/$1/$2/$3/$4/$5';           // Command line request with entity filters
$route['cli/FileTransfer/report/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)']       = 'cli/FileTransfer/report/$1/$2/$3/$4/$5/$6';        // Queued request with entity filters

$route['cli/FileExport/index/(:num)/(:any)']                      = 'cli/FileExport/index/$1/$2';
$route['cli/FileExport/index/(:num)/(:any)/(:any)']               = 'cli/FileExport/index/$1/$2/$3';
$route['cli/FileExport/index/(:num)/(:any)/(:any)/(:num)']        = 'cli/FileExport/index/$1/$2/$3/$4';
$route['cli/FileExport/verbose/(:num)/(:any)/(:any)']             = 'cli/FileExport/verbose/$1/$2/$3';


$route['cli/TestHarness/index/(:num)/(:any)']            = 'cli/TestHarness/index/$1/$2';
$route['cli/TestHarness/index/(:num)/(:any)/(:any)']        = 'cli/TestHarness/index/$1/$2/$3';
$route['cli/TestHarness/index/(:num)/(:any)/(:any)/(:num)']        = 'cli/TestHarness/index/$1/$2/$3/$4';
$route['TestHarness/(:num)/(:any)/(:any)']        = 'cli/TestHarness/index/$1/$2/$3';

$route['cli/MigrateCommissions/index/(:num)/(:any)']            = 'cli/MigrateCommissions/index/$1/$2';
$route['cli/MigrateCommissions/index/(:any)/(:any)'] = 'cli/MigrateCommissions/index/$1/$2';
$route['cli/MigrateCommissions/index/(:any)/(:any)/(:any)'] = 'cli/MigrateCommissions/index/$1/$2/$3';
$route['cli/MigrateCommissions/index/(:any)/(:any)/(:any)/(:num)'] = 'cli/MigrateCommissions/index/$1/$2/$3/$4';

$route['cli/DeleteCompany/index/(:num)/(:any)']            = 'cli/DeleteCompany/index/$1/$2';
$route['cli/DeleteCompany/index/(:any)/(:any)'] = 'cli/DeleteCompany/index/$1/$2';
$route['cli/DeleteCompany/index/(:any)/(:any)/(:any)'] = 'cli/DeleteCompany/index/$1/$2/$3';
$route['cli/DeleteCompany/index/(:any)/(:any)/(:any)/(:num)'] = 'cli/DeleteCompany/index/$1/$2/$3/$4';

$route['cli/DeleteCompanyParent/index/(:num)/(:any)']            = 'cli/DeleteCompanyParent/index/$1/$2';
$route['cli/DeleteCompanyParent/index/(:any)/(:any)'] = 'cli/DeleteCompanyParent/index/$1/$2';
$route['cli/DeleteCompanyParent/index/(:any)/(:any)/(:any)'] = 'cli/DeleteCompanyParent/index/$1/$2/$3';
$route['cli/DeleteCompanyParent/index/(:any)/(:any)/(:any)/(:num)'] = 'cli/DeleteCompanyParent/index/$1/$2/$3/$4';
*/

$route['cli/QueueProcessor/index/(:num)']           = 'cli/QueueProcessor/index/$1';
$route['cli/QueueProcessor/verbose/(:num)']           = 'cli/QueueProcessor/verbose/$1';

$route['cli/QueueDirector']                 = 'cli/QueueDirector/index';
$route['cli/QueueDirector/index']           = 'cli/QueueDirector/index';
$route['cli/QueueDirector/verbose']           = 'cli/QueueDirector/verbose';

$route['cli/MigrateCommissions/index/(:any)/(:num)'] = 'cli/MigrateCommissions/index/$1/$2';
$route['cli/MigrateCommissions/index/(:any)/(:num)/(:num)'] = 'cli/MigrateCommissions/index/$1/$2/$3';

/*
$route['cli/GenerateSecurityKey/index/(:num)/(:any)']        = 'cli/GenerateSecurityKey/index/$1/$2';
$route['cli/GenerateSecurityKey/index/(:num)/(:any)/(:any)']        = 'cli/GenerateSecurityKey/index/$1/$2/$3';
$route['cli/GenerateSecurityKey/index/(:num)/(:any)/(:any)/(:num)'] = 'cli/GenerateSecurityKey/index/$1/$2/$3';
$route['cli/GenerateSecurityKey/verbose/(:num)/(:any)/(:any)']        = 'cli/GenerateSecurityKey/verbose/$1/$2/$3';
*/

$route['cli/(:any)/index/(:num)/(:any)']                   = 'cli/$1/index/$2/$3';
$route['cli/(:any)/index/(:num)/(:any)/(:any)']            = 'cli/$1/index/$2/$3/$4';
$route['cli/(:any)/index/(:num)/(:any)/(:any)/(:num)']     = 'cli/$1/index/$2/$3/$4/$5';
$route['cli/(:any)/verbose/(:num)/(:any)/(:any)']          = 'cli/$1/verbose/$2/$3/$4';
$route['cli/(:any)/verbose/(:num)/(:any)/(:any)/(:num)']   = 'cli/$1/verbose/$2/$3/$4/$5';

// widgettask
$route['widgettask/edit_profile']           = 'widgettask/edit_profile';
$route['widgettask/edit_password']          = 'widgettask/edit_password';
$route['widgettask/top_bar']                = 'widgettask/top_bar';
$route['widgettask/developer_tools']        = 'widgettask/developer_tools';
$route['widgettask/wizard_dashboard']         = 'widgettask/wizard_dashboard';
$route['widgettask/dashboard_task']         = 'widgettask/dashboard_task';
$route['widgettask/admin_dashboard_task']   = 'widgettask/admin_dashboard_task';
$route['widgettask/dashboard_report_review'] = 'widgettask/dashboard_report_review';
$route['widgettask/dashboard_welcome'] = 'widgettask/dashboard_welcome';
$route['widgettask/getting_started'] = 'widgettask/getting_started';
$route['widgettask/getting_started/(:num)'] = 'widgettask/getting_started/$1';
$route['widgettask/manual_adjustment'] = 'widgettask/manual_adjustment';
$route['widgettask/manual_adjustment/(:num)'] = 'widgettask/manual_adjustment/$1';
$route['widgettask/workflow/(:any)/(:num)/(:any)']   = 'widgettask/workflow_widget/$1/$2/$3';
$route['widgettask/workflow/start/(:any)']   = 'widgettask/workflow_widget_start/$1';
$route['widgettask/parent/import_data_widget']  = 'widgettask/parent_upload';
$route['widgettask/company/skip_month/(:any)']          = 'widgettask/skip_month/$1/company';
$route['widgettask/parent/skip_month/(:any)']          = 'widgettask/skip_month/$1/parent';

$route['parent/match/validate']         = 'CompanyParentMatch/validate';
$route['parent/match/save']             = 'CompanyParentMatch/save_match';
$route['parent/match/(:any)']           = 'CompanyParentMatch/index/$1';

$route['parent/correct/(:any)']         = 'CompanyParentCorrect/index/$1';
$route['parent/correct/upload/error/(:num)/(:any)'] = 'CompanyParentCorrect/render_data_error_form/$1/$2';

$route['parent/map/company/widget']         = 'CompanyParentMapCompany/render_company_map_widget';
$route['parent/map/company/widget/summary'] = 'CompanyParentMapCompany/render_company_map_summary_widget';
$route['parent/map/company/confirm/(:any)']        = 'CompanyParentMapCompany/render_company_map_confirm_widget/$1';

$route['parent/map/company/modal/importdate'] = 'CompanyParentMapCompany/render_importdate_modal';
$route['parent/map/company/save/multiple']  = 'CompanyParentMapCompany/save';
$route['parent/map/company/save/importdate']  = 'CompanyParentMapCompany/save_importdate';
$route['parent/map/company/save/single']    = 'CompanyParentMapCompany/save_single_mapping';
$route['parent/map/company/validate']       = 'CompanyParentMapCompany/validate';
$route['parent/map/company/(:any)']         = 'CompanyParentMapCompany/index/$1';



$route['workflow/rollback/(:any)']      = 'Workflow/rollback/$1';
$route['workflow/moveto/(:any)/(:any)'] = 'Workflow/moveto/$1/$2';


$route['waitforit/continue']               = 'SampleWaiting/validate';
$route['waitforit/(:any)']              = 'SampleWaiting/index/$1/three';



//wizard
$route['wizard/upload']     = 'wizard/upload';
$route['wizard/match']      = 'match';
$route['wizard/save']       = 'upload/save';
$route['wizard/validate']   = 'match/validate';
$route['wizard/correct']    = 'correct';
$route['wizard/cancel']     = 'wizard/cancel';
$route['wizard/rematch']    = 'wizard/rematch';
$route['wizard/finalize']    = 'wizard/finalize';
$route['wizard/widget/upload/validate'] = 'wizard/render_upload_validation_widget';
$route['wizard/widget/upload/error/(:num)/(:any)'] = 'correct/render_data_error_form/$1/$2';
$route['wizard/match/save'] = 'match/save_match';
$route['wizard/review/lives'] = 'lives';
$route['wizard/review/plans'] = 'plans';
$route['wizard/review/plans/continue'] = 'plans/planreview_continue';
$route['wizard/review/plantype/save'] = 'plans/plantype_save';
$route['wizard/review/plantype/edit/(:any)/(:any)'] = "plans/render_plantype_form/$1/$2";
$route['wizard/review/plan/save'] = 'plans/plan_save';
$route['wizard/review/plan/edit/(:any)/(:any)/(:any)'] = "plans/render_plan_form/$1/$2/$3";
$route['wizard/review/reports'] = 'reportreview';
$route['wizard/navigate/plans'] = 'wizard/edit_plan_settings';
$route['wizard/review/clarifications'] = 'clarifications';
$route['wizard/review/ageband/save'] = 'plans/ageband_save';
$route['wizard/review/ageband/edit/(:any)/(:any)/(:any)/(:any)/(:any)'] = "plans/render_ageband_form/$1/$2/$3/$4/$5";
$route['wizard/review/tobacco/save'] = 'plans/tobacco_save';
$route['wizard/review/tobacco/edit/(:any)/(:any)/(:any)/(:any)/(:any)'] = "plans/render_tobacco_form/$1/$2/$3/$4/$5";
$route['wizard/review/ageband/default/5year'] = 'plans/render_band_defaults';
$route['wizard/review/ageband/default/10year'] = 'plans/render_band_defaults';
$route['wizard/navigate/adjustments'] = 'wizard/edit_manual_adjustments';
$route['wizard/navigate/relationships'] = 'wizard/edit_relationships';
$route['wizard/navigate/lives'] = 'wizard/edit_lives';
$route['wizard/navigate/clarifications'] = 'wizard/edit_clarifications';
$route['wizard/review/carrier/edit/(:any)'] = "plans/render_carrier_form/$1";
$route['wizard/review/carrier/save'] = 'plans/carrier_save';
$route['wizard/notify/changed'] = 'wizard/notify_workflow_step_changed';
$route['wizard/notify/changing'] = 'wizard/notify_workflow_step_changing';


// Downloads
$route['download/duplicates/(:num)'] = 'downloads/download_duplicate_data_report/$1';
$route['download/issues/(:num)'] = 'downloads/download_issues_report/$1';
$route['download/issues/(:num)/(:any)'] = 'downloads/download_issues_report/$1/$2';
$route['download/errors/(:any)/(:num)'] = 'downloads/validation_errors/$1/$2';
$route['download/detail/(:num)/(:num)']  = 'downloads/download_detail_report/$1/$2';
$route['download/summary/(:num)/(:num)'] = 'downloads/download_summary_report/$1/$2';
$route['download/transamerica_eligibility/(:num)/(:num)'] = 'downloads/download_fixed_width_report/$1/$2';
$route['download/transamerica_commission/(:num)/(:num)'] = 'downloads/download_fixed_width_report/$1/$2';
$route['download/transamerica_actuarial/(:num)/(:num)'] = 'downloads/download_fixed_width_report/$1/$2';
$route['download/mappings/(:num)/(:num)'] = 'downloads/download_object_mappings/$1/$2';
$route['download/commission/(:num)/(:num)']  = 'downloads/download_detail_report/$1/$2';
$route['download/timers/(:num)/(:num)'] = 'downloads/download_support_timers/$1/$2';
$route['deliver/(:any)/(:any)/(:num)/(:num)'] = 'downloads/transfer_report/$1/$2/$3/$4';
$route['download/export/(:num)'] = 'downloads/download_export/$1';

// Reports
$route['report/summary/(:num)/(:num)/(:num)'] = 'viewer/summary_report/$1/$2/$3';

// Adjustments
$route['adjustments'] = 'adjustments/index';
$route['adjustments/continue'] = 'adjustments/adjustments_continue';
$route['adjustments/save/adjustment'] = 'adjustments/save_manual_adjustment';
$route['adjustments/delete/adjustment'] = 'adjustments/delete_manual_adjustment';

// relationships
$route['relationships'] = 'relationships/index';
$route['relationships/continue'] = 'relationships/relationships_continue';
$route['relationships/save'] = 'relationships/relationships_save';

// lives
$route['lives'] = 'lives/index';
$route['lives/continue'] = 'lives/lives_continue';
$route['lives/save'] = 'lives/lives_save';


// clarifications
$route['clarifications'] = 'clarifications/index';
$route['clarifications/continue'] = 'clarifications/clarifications_continue';
$route['clarifications/save'] = 'clarifications/clarifications_save';

// support
$route['support/manage'] = 'archive/support_company';
$route['support/manage/(:num)'] = 'archive/support_company/$1';
$route['support/manage/company/(:num)'] = 'archive/support_company/$1';
$route['support/manage/parent/(:num)'] = 'archive/support_parent/$1';
$route['support/changes/parent/recent/(:num)'] = 'archive/audit_parent/$1/recent';

// support-snapshots
$route['support/snapshots/snap/(:num)/(:num)'] = 'archive/take_snapshot/$1/$2';
$route['support/snapshots/company/(:num)'] = 'archive/snapshot_viewer/$1/company';
$route['support/snapshots/company/(:num)/(:num)'] = 'archive/snapshot_viewer/$1/company/$2';
$route['support/snapshots/company/(:num)/(:num)/(:any)'] = 'archive/snapshot_viewer/$1/company/$2/$3';
$route['support/archive/download/source/company/(:num)/(:num)'] = 'archive/render_original_upload/$1/company/$2';
$route['support/archive/download/encrypted/company/(:num)/(:num)'] = 'archive/render_original_encrypted_upload/$1/company/$2';
$route['support/snapshots/download/company/(:any)/(:num)/(:num)'] = 'archive/render_archive_download/$2/company/$3/$1';
$route['support/snapshots/parent/(:num)'] = 'archive/snapshot_viewer/$1/companyparent';
$route['support/snapshots/parent/(:num)/(:num)'] = 'archive/snapshot_viewer/$1/companyparent/$2';
$route['support/snapshots/parent/(:num)/(:num)/(:any)'] = 'archive/snapshot_viewer/$1/companyparent/$2/$3';
$route['support/archive/download/source/parent/(:num)/(:num)'] = 'archive/render_original_upload/$1/companyparent/$2';
$route['support/archive/download/encrypted/parent/(:num)/(:num)'] = 'archive/render_original_encrypted_upload/$1/companyparent/$2';
$route['support/snapshots/download/parent/(:any)/(:num)/(:num)'] = 'archive/render_archive_download/$2/companyparent/$3/$1';

// support-exports
$route['support/exports/company/(:num)']            = 'archive/export/$1/company';
$route['support/exports/parent/(:num)']             = 'archive/export/$1/companyparent';
$route['support/exports/create']                    = 'archive/export_create';
$route['support/exports/cancel/(:num)']             = 'archive/export_cancel/$1';
$route['support/exports/delete/(:num)']             = 'archive/export_delete/$1';
$route['support/exports/company/manage/(:num)']     = 'archive/render_export_content/$1/company/manage';
$route['support/exports/parent/manage/(:num)']      = 'archive/render_export_content/$1/companyparent/manage';
$route['support/exports/company/create/(:num)']     = 'archive/render_export_content/$1/company/create';
$route['support/exports/parent/create/(:num)']      = 'archive/render_export_content/$1/companyparent/create';
$route['support/exports/confirm/delete/(:num)']     = 'archive/render_export_content/x/x/remove/$1';



// support-changes
$route['support/changes/company/recent/(:num)'] = 'archive/audit_company/$1/recent';
$route['support/changes/company/week/(:num)'] = 'archive/audit_company/$1/week';
$route['support/changes/company/month/(:num)'] = 'archive/audit_company/$1/month';
$route['support/changes/company/months/(:num)'] = 'archive/audit_company/$1/months';
$route['support/changes/company/year/(:num)'] = 'archive/audit_company/$1/year';
$route['support/changes/company/all/(:num)'] = 'archive/audit_company/$1/all';
$route['support/changes/parent/recent/(:num)'] = 'archive/audit_parent/$1/recent';
$route['support/changes/parent/week/(:num)'] = 'archive/audit_parent/$1/week';
$route['support/changes/parent/month/(:num)'] = 'archive/audit_parent/$1/month';
$route['support/changes/parent/months/(:num)'] = 'archive/audit_parent/$1/months';
$route['support/changes/parent/year/(:num)'] = 'archive/audit_parent/$1/year';
$route['support/changes/parent/all/(:num)'] = 'archive/audit_parent/$1/all';


// support-tickets
$route['support/tickets/company/(:num)'] = 'archive/support_ticket/$1/company';
$route['support/tickets/company/(:num)/(:num)'] = 'archive/support_ticket/$1/company/$2';
$route['support/tickets/company/(:num)/(:num)/(:any)'] = 'archive/support_ticket/$1/company/$2/$3';
$route['support/tickets/company/download/(:any)/(:num)/(:num)'] = 'archive/render_archive_download/$2/$3/$1';

$route['support/tickets/parent/(:num)'] = 'archive/support_ticket/$1/companyparent';
$route['support/tickets/parent/(:num)/(:num)'] = 'archive/support_ticket/$1/companyparent/$2';
$route['support/tickets/parent/(:num)/(:num)/(:any)'] = 'archive/support_ticket/$1/companyparent/$2/$3';
$route['support/tickets/parent/download/(:any)/(:num)/(:num)'] = 'archive/render_archive_download/$2/$3/$1';

// support-lives
$route['support/lives/company/(:num)'] = 'archive/lives/$1';

// support-commissions
$route['support/commissions/company/(:num)/(:num)'] = 'commissions/support/$1/$2';
$route['support/commissions/company/(:num)/(:num)/(:num)'] = 'commissions/support/$1/$2/$3';

// support-timers
$route['support/timers/company/(:num)'] = 'archive/timers/$1';
$route['support/timers/company/(:num)/(:any)'] = 'archive/timers/$1/$2';

// support-report: invoice
$route['support/invoice/parent/(:num)'] = 'archive/report/invoice/$1/companyparent';
$route['support/invoice/parent/(:num)/(:any)'] = 'archive/report/invoice/$1/companyparent/$2';

$route['cli/RotateKeys/index/(:num)/(:num)'] = 'cli/RotateKeys/index/$1/$2';
$route['cli/RotateKeys/verbose/(:num)/(:num)'] = 'cli/RotateKeys/verbose/$1/$2';


// workflow
$route['dashboard/workflow/(:any)'] = 'workflow/dashboard/$1';

// ALL STOP!
// We do not want to allow the old CI routing behavior to work.  If we do that it
// will be possible for code to "drift" back to the old URL structure during development.
// To prevent that from happening, the following links will shut down any URL requests that
// are not already defined and handled above.
$route['(:any)'] = 'auth/404';
$route['(:any)/(:any)'] = 'auth/404';
$route['(:any)/(:any)/(:any)'] = 'auth/404';
$route['(:any)/(:any)/(:any)/(:any)'] = 'auth/404';
$route['(:any)/(:any)/(:any)/(:any)/(:any)'] = 'auth/404';
$route['(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'auth/404';
$route['(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'auth/404';

$route['default_controller'] = 'dashboard';
$route['404_override'] = 'auth/error_404';
$route['translate_uri_dashes'] = FALSE;
