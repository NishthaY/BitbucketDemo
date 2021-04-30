<?php
class HerokuDynoRequest_model extends HerokuRequest_model
{

    function __construct()
    {
        parent::__construct();

    }
    public function get_dynos($app_name, $verbose=false) {

        // get_dynos
        //
        // https://devcenter.heroku.com/articles/platform-api-reference#dyno-info
        // -----------------------------------------------------------------

        $this->debug = $verbose;
        $url = "https://{$this->ws_host}/apps/{$app_name}/dynos";
        $results = $this->get($url);

        if ( isset($results['result']) ) $results = $results['result'];
        return $results;
    }
    public function get_dyno_info( $app_name, $dyno_name, $verbose=false ) {

        // https://devcenter.heroku.com/articles/platform-api-reference#dyno-info

        $this->debug = $verbose;
        $url = "https://{$this->ws_host}/apps/{$app_name}/dynos/{$dyno_name}";
        $results = $this->get($url);

        // Watch for an error message and log it.  Maybe that will help us one day.
        if ( GetArrayStringValue('error_message' , $results['response'] ) !== '' )
        {
            LogIt(get_class() . ": Error Message", GetArrayStringValue('error_message' , $results['response'] ));
        }

        // Return the results.
        if ( isset($results['result']) ) $results = $results['result'];
        return $results;

    }
    public function stop_dyno( $app_name, $dyno_name, $verbose=false ) {

        // https://devcenter.heroku.com/articles/platform-api-reference#dyno-stop

        $this->debug = $verbose;
        $url = "https://{$this->ws_host}/apps/{$app_name}/dynos/{$dyno_name}/actions/stop";
        $web_results = $this->post($url, array());
        return $web_results;

        // /apps/{app_id_or_name}/dynos/{dyno_id_or_name}/actions/stop
    }
    public function restart_dyno( $app_name, $dyno_name, $verbose=false ) {
        //https://devcenter.heroku.com/articles/platform-api-reference#dyno-restart
        $this->debug = $verbose;
        $url = "https://{$this->ws_host}/apps/{$app_name}/dynos/{$dyno_name}";
        $results = $this->delete($url);
        return $results;
    }
    public function create_oneoff_dyno( $app_name, $command, $verbose=false ) {

        // create_oneoff_dyno
        //
        // Create a new dyno and run the provided command in it.
        // https://devcenter.heroku.com/articles/platform-api-reference#dyno-create
        
        $this->debug = $verbose;

        // Calling Parameters
        $params = array();
        $params['command'] = $command;
        $params['type'] = "run";
        if ( GetAppOption(ONE_OFF_DYNO_SIZE) !== '' )
        {
            // If set, use the dyno size we specified.  Else
            // do not set the size param which will get us the default.
            $params['size'] = GetAppOption(ONE_OFF_DYNO_SIZE);
        }
        $params['time_to_live'] = getIntValue(GetConfigValue("max_job_runtime", "queue")) + ( 5 * SECONDS_PER_MINUTE );  // 5 minutes past max runtime.

        // Make the web service call.
        $url = "https://{$this->ws_host}/apps/{$app_name}/dynos";

        if ( $this->debug )
        {
            print_r("url: " . $url);
            print_r($params);
        }
        $results = $this->post($url, $params);
        if ( $this->debug ) print_r($results);
        if ( isset($results['result']) ) $results = $results['result'];

        // Normalize the output.
        $retval = array();
        $retval["DynoId"] = getArrayStringValue("id", $results);
        $retval["DynoName"] = getArrayStringValue("name", $results);
        if ( isset($results['app']) )
        {
            $retval["AppId"] = getArrayStringValue("id", $results['app']);
            $retval["AppName"] = getArrayStringValue("name", $results['app']);
        }
        $retval["Command"] = getArrayStringValue("command", $results);

        // Validate the results.
        if ( getArrayStringValue("DynoName", $retval) == "" ) return array();
        return $retval;

    }

}


/* End of file HerokuDynoRequest_model.php */
/* Location: ./application/models/HerokuDynoRequest_model.php */
