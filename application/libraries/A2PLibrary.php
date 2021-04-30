<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class A2PLibrary
{
    protected $debug;
    protected $debug_stop_string;
    protected $ci;
    protected $company_id;
    protected $import_date;
    protected $log_debug_messages;
    protected $timers;        // Turn timers on or off.
    protected $timer_array;   // Collection of timers, if they are on.
    protected $encryption_key;

    public function __construct( $debug = false )
    {
        $this->debug = $debug;
        $this->ci = &get_instance();
        $this->company_id = null;
        $this->log_debug_messages = false;
        $this->timers       = false;
        $this->timer_array  = array();
        $this->debug_stop_string = "";

    }
    public function __destruct()
    {
        $this->debug(get_called_class() . " complete.");
        if ($this->debug) $this->timer("end");
        unset($this->debug);
        unset($this->company_id);
        unset($this->import_date);
        unset($this->log_debug_messages);
        unset($this->timers);
        unset($this->timer_array);
        unset($this->encryption_key);
    }
    public function execute( $company_id, $user_id=null )
    {
        $this->company_id = $company_id;
        $this->user_id = $user_id;
        if ( GetStringValue($this->user_id) === '' ) $this->user_id = GetSessionValue('user_id');

        // Turn timers on/off based on database settings.
        $this->timers = $this->ci->Log_model->exists_log_timer_relationship($company_id);

        // Start recording
        $this->timer(__FUNCTION__);

        // Ensure we have the encryption key in the cache
        $this->ci->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $this->encryption_key = $this->ci->cache->get("crypto_{$company_id}");
        if ( GetStringValue($this->encryption_key) === 'FALSE' )
        {
            $this->encryption_key = GetCompanyEncryptionKey($company_id);
            $this->ci->cache->save("crypto_{$company_id}", $this->encryption_key, 300);
        }

        // Capture the debug stop string from app options.  If we ever see this
        // message, we are to exit the job immediately.
        $this->debug_stop_string = trim(GetAppOption("DEBUG_STOP_STRING"));

        if ( strtoupper(GetAppOption(LOG_DEBUG_MESSAGES)) === 'TRUE' ) $this->log_debug_messages = true;
        $this->debug(get_called_class() . " started. ");

    }
    public function rollback( $company_id, $import_date=null )
    {
        $this->company_id = $company_id;
        $this->debug(get_called_class() . " rolling back.");
    }
    protected function debug($input)
    {
        // Keep our debug messages if so configured.
        if ( $this->log_debug_messages && GetStringValue($this->company_id) !== '' )
        {
            LogIt( "A2PLibrary", $input, "", null, $this->company_id, null );
        }

        $uri = GetArrayStringValue('REQUEST_URI', $_SERVER);
        if ( $uri === '' )
        {
            $type = gettype($input);
            if ( $type === 'boolean' || $type === 'integer' || $type === 'double' || $type === 'string' )
            {
                $input = GetStringValue($input);
                if ( $this->debug) print $input . PHP_EOL;
                $this->ci->Wizard_model->update_activity($this->company_id, trim($input));
            }
            else
            {
                if ( $this->debug ) print_r($input);
            }


        }
        if ( $uri !== '' ) {
            if ( $this->debug ) pprint_r($input);

            // Only write simple output to recent activity log.
            $type = gettype($input);
            if ( $type === 'boolean' || $type === 'integer' || $type === 'double' || $type === 'string' )
            {
                $this->ci->Wizard_model->update_activity($this->company_id, trim($input));
            }


        }

        // If we just wrote a debug message and it matches the app option DEBUG_STOP_STRING,
        // stop processing and exit.
        if ( $this->debug_stop_string !== '' && $this->debug_stop_string === trim($input) )
        {
            if ( $this->debug )
            {
                print "All Stop! We found a debug message that matched the DEBUG_STOP_STRING.\n";
                LogIt( "All Stop!", "AppOption has a debug stop string set and we just found it." );
                exit;
            }

        }
    }
    /**
     * timer
     *
     * Each time this function is called, it will report how long
     * the previous item had been running.  If you pass in "end" then
     * then you will get a summary of the full runtime from first timer
     * to "end".
     *
     * Works independently from DEBUG.  Debug does not imply timers.
     * timers will write to STDOUT even if debug is off.
     *
     * @param $code
     */
    protected function timer($code)
    {
        if ($this->timers !== TRUE) return;
        if ( GetStringValue($this->company_id) === '' ) return;
        if ( GetStringValue($this->import_date) === '' ) $this->import_date = GetUploadDate($this->company_id);
        if ( GetStringValue($this->import_date) === '' ) return;

        if ( ! $this->timer_array ) $this->timer_array = array();
        if ( count($this->timer_array) === 0 ) {
            $this->timer_array[$code] = time();
        }elseif ( $code === 'end' ) {

            if ( ! empty($this->timer_array) )
            {
                $keys = array_keys($this->timer_array);
                $first_key = $keys[0];

                $seconds = round(abs(time() - $this->timer_array[$first_key]),2);
                $minutes = round(abs(time() - $this->timer_array[$first_key]) / 60,2);
                if ( $minutes < 1 ) {
                    if ( $seconds < 0 ) TimeIt($this->company_id, $this->import_date, "END: ".get_called_class(), 0, 'MINUTES');
                    if ( $seconds > 0 ) TimeIt($this->company_id, $this->import_date, "END: ".get_called_class(), $seconds, 'MINUTES');


                }else{
                    TimeIt($this->company_id, $this->import_date, "END: ".get_called_class(), 0, 'MINUTES');
                }
            }

        }
        else
        {

            $keys = array_keys($this->timer_array);
            $last_key_index = count($keys) - 1;
            $last_key = $keys[$last_key_index];

            $seconds = round(abs(time() - $this->timer_array[$last_key]),2);
            $minutes = round(abs(time() - $this->timer_array[$last_key]) / 60,2);

            if ( $minutes < 1 ) {
                if ( $seconds <= 0 ) TimeIt($this->company_id, $this->import_date, $code, 0, 'SECONDS');
                if ( $seconds > 0 ) TimeIt($this->company_id, $this->import_date, $code, $seconds, 'SECONDS');
            }else{
                TimeIt($this->company_id, $this->import_date, $code, 0, 'MINUTES');

            }

            $this->timer_array[$code] = time();
        }

    }
}