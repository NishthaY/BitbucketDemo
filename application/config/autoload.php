<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| AUTO-LOADER
| -------------------------------------------------------------------
| This file specifies which systems should be loaded by default.
|
| In order to keep the framework as light-weight as possible only the
| absolute minimal resources are loaded by default. For example,
| the database is not connected to automatically since no assumption
| is made regarding whether you intend to use it.  This file lets
| you globally define which systems you would like loaded with every
| request.
|
| -------------------------------------------------------------------
| Instructions
| -------------------------------------------------------------------
|
| These are the things you can load automatically:
|
| 1. Packages
| 2. Libraries
| 3. Drivers
| 4. Helper files
| 5. Custom config files
| 6. Language files
| 7. Models
|
*/

/*
| -------------------------------------------------------------------
|  Auto-load Packages
| -------------------------------------------------------------------
| Prototype:
|
|  $autoload['packages'] = array(APPPATH.'third_party', '/usr/local/shared');
|
*/
$autoload['packages'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Libraries
| -------------------------------------------------------------------
| These are the classes located in system/libraries/ or your
| application/libraries/ directory, with the addition of the
| 'database' library, which is somewhat of a special case.
|
| Prototype:
|
|	$autoload['libraries'] = array('database', 'email', 'session');
|
| You can also supply an alternative library name to be assigned
| in the controller:
|
|	$autoload['libraries'] = array('user_agent' => 'ua');
*/
$autoload['libraries'] = array(
        'MY_Composer'
        , "APIMessage"
        , "APIErrorMessage"
        , "APIException"
        , 'A2PLibrary'
        , 'A2PWorkflowWaitingException'
        , 'session'
        , 'SecurityException'
        , 'UIException'
        , 'ReportException'
        , 'Menu'
        , 'UIForm'
        , 'UISimpleForm'
        , 'UIModalForm'
        , 'UIWizardForm'
        , 'UIWidget'
        , 'UIFormHeader'
        , 'UIBackgroundTask'
        , 'UIAlert'
        , 'ui_elements/UIConfirmButton'
        , 'ui_elements/UIElement'
        , 'ui_elements/UIButton'
        , 'ui_elements/Select2'
        , 'ui_elements/Dropdown'
        , 'ui_elements/MultiOptionButton'
        , 'ui_elements/EnterpriseBanner'
        , 'mapping/ColumnValidation'
        , 'mapping/ColumnValidation_Money'
        , 'mapping/ColumnValidation_Boolean'
        , 'mapping/ColumnValidation_Date'
        , 'mapping/ColumnValidation_SSN'
        , 'mapping/ColumnValidation_Gender'
        , 'mapping/ColumnValidation_Email'
        , 'mapping/ColumnValidation_Phone'
        , 'mapping/ColumnValidation_State'
        , 'GenerateAutomaticAdjustments'
        , 'GenerateOriginalEffectiveDateData'
        , 'GenerateSummaryData'
        , 'GenerateDownloadableReports'
        , 'GenerateRetroData'
        , 'GenerateAgeData'
        , 'GenerateWashedData'
        , 'GenerateLifeData'
        , 'GenerateRelationshipData'
        , 'GeneratePlanFees'
        , 'A2P_PDF'
        , 'GenerateCommissions'
        , 'GenerateCommissionReport'
        , 'GenerateDuplicateLivesReport'
        , 'GenerateReportTransamerica'
        , 'GenerateReportTransamericaCommissions'
        , 'GenerateReportTransamericaEligibility'
        , 'GenerateReportTransamericaActuarial'
        , 'GenerateWarningReport'
        , 'GenerateUniversalEmployeeId'
        , 'workflow/WorkflowLibrary'
        , 'NoResultsException'
        , 'SkipMonthProcessing'

    );

/*
| -------------------------------------------------------------------
|  Auto-load Drivers
| -------------------------------------------------------------------
| These classes are located in system/libraries/ or in your
| application/libraries/ directory, but are also placed inside their
| own subdirectory and they extend the CI_Driver_Library class. They
| offer multiple interchangeable driver options.
|
| Prototype:
|
|	$autoload['drivers'] = array('cache');
|
| You can also supply an alternative property name to be assigned in
| the controller:
|
|	$autoload['drivers'] = array('cache' => 'cch');
|
*/
$autoload['drivers'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Helper Files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['helper'] = array('url', 'file');
*/
$autoload['helper'] = array(
    'url'
    , 'string'
    , 'app'
    , 'debug'
    , 'auth'
    , 'db'
    , "users"
    , 'widget_task'
    , 'companies'
    , 'wizard'
    , 'report'
    , 'relationship'
    , 'a2p_crypto'
    , 'report'
    , 'life'
    , 'clarifications'
    , 'plans'
    , 'match'
    , 'adjustment'
    , 'dashboard'
    , 'notification'
    , 'support'
    , 'queue'
    , 'display'
    , 'companyparent'
    , 'array'
    , 'kms'
    , 'commissions'
    , 'workflow'
    , 'support_timer'
    , 'wf_parent_upload_helper'
    , 'parsecsv'
    , 'preference'
    , 'api'
    , 'feature'
    , 'infofile'
);

/*
| -------------------------------------------------------------------
|  Auto-load Config files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['config'] = array('config1', 'config2');
|
| NOTE: This item is intended for use ONLY if you have created custom
| config files.  Otherwise, leave it blank.
|
*/
$autoload['config'] = array('aws', 'queue');

/*
| -------------------------------------------------------------------
|  Auto-load Language files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['language'] = array('lang1', 'lang2');
|
| NOTE: Do not include the "_lang" part of your file.  For example
| "codeigniter_lang.php" would be referenced as array('codeigniter');
|
*/
$autoload['language'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Models
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['model'] = array('first_model', 'second_model');
|
| You can also supply an alternative model name to be assigned
| in the controller:
|
|	$autoload['model'] = array('first_model' => 'first');
*/
$autoload['model'] = array(
    "Mapping_model"
    , 'Adjustment_model'
    , "Adjustment_model"
    , "Age_model"
    , "Ageband_model"
    , "AppOption_model"
    , "Archive_model"
    , "Beneficiary_model"
    , "Carrier_model"
    , "Clarifications_model"
    , "Company_model"
    , "CompanyParent_model"
    , "CompanyParentMap_model"
    , "Commissions_model"
    , "Export_model"
    , "Feature_model"
    , "FileTransfer_model"
    , "GenerateCommissions_model"
    , "GenerateOriginalEffectiveDateData_model"
    , "GenerateDuplicateLivesReport_model"
    , "ReportTransamerica_model"
    , "ReportTransamericaActuarial_model"
    , "ReportTransamericaCommissions_model"
    , "ReportTransamericaEligibility_model"
    , "HerokuRequest_model"
    , "HerokuDynoRequest_model"
    , "History_model"
    , "Life_model"
    , "LifeEvent_model"
    , "Log_model"
    , "Login_model"
    , "Mapping_model"
    , "Menu_model"
    , "ObjectMapping_model"
    , "PlanFees_model"
    , "Queue_model"
    , "Relationship_model"
    , "Reporting_model"
    , "Retro_model"
    , "Spend_model"
    , "Support_model"
    , "Tobacco_model"
    , "Tuning_model"
    , "User_model"
    , "Validation_model"
    , "Verbiage_model"
    , "Widgettask_model"
    , "Wizard_model"
    , "Workflow_model"
    , 'UniversalEmployee_model'
);
