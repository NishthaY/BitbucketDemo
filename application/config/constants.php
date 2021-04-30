<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

defined('SECONDS_PER_MINUTE') OR define('SECONDS_PER_MINUTE', 60);

defined('REPORT_TYPE_SUMMARY')     OR define('REPORT_TYPE_SUMMARY', 1);
defined('REPORT_TYPE_DETAIL')      OR define('REPORT_TYPE_DETAIL', 2);
defined('REPORT_TYPE_PE_SUMMARY')     OR define('REPORT_TYPE_PE_SUMMARY', 3);
defined('REPORT_TYPE_PE_DETAIL')      OR define('REPORT_TYPE_PE_DETAIL', 4);
defined('REPORT_TYPE_SUMMARY_CODE')     OR define('REPORT_TYPE_SUMMARY_CODE', 'summary');
defined('REPORT_TYPE_DETAIL_CODE')      OR define('REPORT_TYPE_DETAIL_CODE', 'detail');
defined('REPORT_TYPE_PE_SUMMARY_CODE')     OR define('REPORT_TYPE_PE_SUMMARY_CODE', 'pe_summary');
defined('REPORT_TYPE_PE_DETAIL_CODE')      OR define('REPORT_TYPE_PE_DETAIL_CODE', 'pe_detail');
defined('REPORT_TYPE_COMMISSION_CODE')      OR define('REPORT_TYPE_COMMISSION_CODE', 'commission');
defined('REPORT_TYPE_ISSUES_CODE')     OR define('REPORT_TYPE_ISSUES_CODE', 'issues');
defined('REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE')      OR define('REPORT_TYPE_TRANSAMERICA_ELIGIBILITY_CODE', 'transamerica_eligibility');
defined('REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE') OR define('REPORT_TYPE_TRANSAMERICA_COMMISSIONS_CODE', 'transamerica_commission');
defined('REPORT_TYPE_TRANSAMERICA_ACTUARIAL_CODE') OR define('REPORT_TYPE_TRANSAMERICA_ACTUARIAL_CODE', 'transamerica_actuarial');



defined('ADJUSTMENT_TYPE_MANUAL') OR define('ADJUSTMENT_TYPE_MANUAL', 1);
defined('ADJUSTMENT_TYPE_RETRO_ADD') OR define('ADJUSTMENT_TYPE_RETRO_ADD', 2);
defined('ADJUSTMENT_TYPE_RETRO_TERM') OR define('ADJUSTMENT_TYPE_RETRO_TERM', 3);
defined('ADJUSTMENT_TYPE_RETRO_CHANGE') OR define('ADJUSTMENT_TYPE_RETRO_CHANGE', 4);

defined('RETRO_RULE_1_MONTH')   OR define('RETRO_RULE_1_MONTH', 1);
defined('RETRO_RULE_2_MONTHS')  OR define('RETRO_RULE_2_MONTHS', 2);
defined('RETRO_RULE_3_MONTHS')  OR define('RETRO_RULE_3_MONTHS', 3);
defined('RETRO_RULE_MAX')       OR define('RETRO_RULE_MAX', 3);

defined('WASH_RULE_1ST') OR define('WASH_RULE_1ST', 1);
defined('WASH_RULE_15TH') OR define('WASH_RULE_15TH', 15);

// AppOptions
defined('DELAY_QUEUE_UNTIL') OR define('DELAY_QUEUE_UNTIL', 'DELAY_QUEUE_UNTIL');
defined('DATE_OF_LAST_WORKER_REBOOT') OR define('DATE_OF_LAST_WORKER_REBOOT', 'DATE_OF_LAST_WORKER_REBOOT');
defined('DYNO_SUPPORT_ENABLED') OR define('DYNO_SUPPORT_ENABLED', 'DYNO_SUPPORT_ENABLED');
defined('ONE_OFF_DYNO_SUPPORT_ENABLED') OR define('ONE_OFF_DYNO_SUPPORT_ENABLED', 'ONE_OFF_DYNO_SUPPORT_ENABLED');
defined('REST_SECONDS_BETWEEN_QUERIES') OR define('REST_SECONDS_BETWEEN_QUERIES', 'REST_SECONDS_BETWEEN_QUERIES');
defined('PSQL_WORK_MEM') OR define('PSQL_WORK_MEM', 'PSQL_WORK_MEM');
defined('LOG_DEBUG_MESSAGES') OR define('LOG_DEBUG_MESSAGES', 'LOG_DEBUG_MESSAGES');
defined('ROLLBACK_ON_CRIT') OR define('ROLLBACK_ON_CRIT', 'ROLLBACK_ON_CRIT');
defined('SELECT_INTO_CHUNCK_SIZE') OR define('SELECT_INTO_CHUNCK_SIZE', 'SELECT_INTO_CHUNCK_SIZE');
defined('ONE_OFF_DYNO_SIZE') OR define('ONE_OFF_DYNO_SIZE', 'ONE_OFF_DYNO_SIZE');
defined('ONE_OFF_DYNO_PHP_MEMORY_LIMIT') OR define('ONE_OFF_DYNO_PHP_MEMORY_LIMIT', 'ONE_OFF_DYNO_PHP_MEMORY_LIMIT');
defined('DATE_OF_LAST_KEY_ROTATION') OR define('DATE_OF_LAST_KEY_ROTATION', 'DATE_OF_LAST_KEY_ROTATION');
defined('GETTING_STARTED_YEARS') OR define('GETTING_STARTED_YEARS', 'GETTING_STARTED_YEARS');

// A2P Company Id
defined('A2P_COMPANY_ID') OR define('A2P_COMPANY_ID', 1);

// Prefered Timezone
defined('PREFERED_TIMEZONE') OR define('PREFERED_TIMEZONE', 'US/Central');

// File Transfer Protocols
defined( 'FILE_TRANSFER_SFTP_CODE' ) OR define('FILE_TRANSFER_SFTP_CODE', 'SFTP');

defined('WINDOWS_NEWLINE') OR define('WINDOWS_NEWLINE', "\r\n");

// Universal ID Tag
// Employee Ids that start with this tag have been generated by A2P and
// were not provided by the client.
defined( 'EUID_TAG' ) OR define('EUID_TAG', '{a2p-ueid}:');

// Commission Types
defined( 'COMMISSION_TYPE_LEVEL' ) OR define('COMMISSION_TYPE_LEVEL', 'level');
defined( 'COMMISSION_TYPE_HEAP_FLAT' ) OR define('COMMISSION_TYPE_HEAP_FLAT', 'heap_flat');
defined( 'COMMISSION_TYPE_HEAP_STACKED' ) OR define('COMMISSION_TYPE_HEAP_STACKED', 'heap_stack');

// Commission Effective Date Types
defined( 'RECENT_TIER_CHANGE' ) OR define('RECENT_TIER_CHANGE', 'RECENT_TIER_CHANGE');
defined( 'OLDEST_LIFE_PLAN_EFFECTIVE_DATE' ) OR define('OLDEST_LIFE_PLAN_EFFECTIVE_DATE', 'OLDEST_LIFE_PLAN_EFFECTIVE_DATE');

// HTTP Method Codes
defined('HTTP_METHOD_GET')          OR define('HTTP_METHOD_GET', 'GET');
defined('HTTP_METHOD_POST')         OR define('HTTP_METHOD_POST', 'POST');
defined('HTTP_METHOD_PUT')          OR define('HTTP_METHOD_PUT', 'PUT');
defined('HTTP_METHOD_DELETE')       OR define('HTTP_METHOD_DELETE', 'DELETE');

// Code that represents "empty encryption key".
defined('EMPTY_ENCRYPTION_KEY')     OR define('EMPTY_ENCRYPTION_KEY', 'no encryption key');

