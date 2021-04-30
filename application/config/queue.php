<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/*
|--------------------------------------------------------------------------
| Delay Processor
|--------------------------------------------------------------------------
|
| If we try and start a background process, but we can't, delay the processor
| by this many minutes before we try processing more jobs.
|
*/
$config['delay_processor'] = ( 2 * SECONDS_PER_MINUTE ); // Every two minutes.


/*
|--------------------------------------------------------------------------
| Max Job Runtime
|--------------------------------------------------------------------------
|
| If any background job runs longer than this time, we will fail it.
|
*/
$config['max_job_runtime'] = ( 720 * SECONDS_PER_MINUTE ); // 12 hours.

/*
|--------------------------------------------------------------------------
| Processor Sleep
|--------------------------------------------------------------------------
|
| The processor will sleep this many seconds before it looks for more work.
|
*/
$config['processor_sleep'] = 3; // 3 seconds.


/*
|--------------------------------------------------------------------------
| Failure Check
|--------------------------------------------------------------------------
|
| The processor will confirm the running jobs on the queue are still running
| at this interval.  If they are not running on the system, but the queue
| thinks they are running, they will be failed.
|
*/
$config['failure_check'] = ( 5 * SECONDS_PER_MINUTE ); // 5 minutes


/*
|--------------------------------------------------------------------------
| Reboot Check
|--------------------------------------------------------------------------
|
| This is the throttle that controls how often the QueueDirector will check
| to see if it should reboot or not.  This values is in seconds.
|
*/
$config['reboot_check'] = ( 10 * SECONDS_PER_MINUTE ); // 10 minutes

/*
|--------------------------------------------------------------------------
| Reboot Window
|--------------------------------------------------------------------------
|
| The QueueDirector will only consider doing a reboot during the w
| window specified.  The time below is in a 24 hour clock at PREFERED_TIMEZONE.
|
*/
$config['reboot_window_start'] = "01:00";
$config['reboot_window_end'] = "06:00";
