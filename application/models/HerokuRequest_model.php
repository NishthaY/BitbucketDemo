<?php
REQUIRE 'vendor/autoload.php';

class HerokuRequest_model extends CI_Model
{

    protected $ws_host;
    protected $ws_timeout;
    protected $ws_connect_timeout;
    protected $ws_secret;
    protected $debug;

    function __construct()
    {
        parent::__construct();

        // Set a few values needed for configuring the communication protocol.
        $this->ws_host = GetConfigValue("heroku_ws_host", "heroku");
        $this->ws_timeout = GetConfigValue("heroku_ws_timeout", "heroku");
        $this->ws_connect_timeout = GetConfigValue("heroku_ws_connect_timeout", "heroku");
        $this->ws_secret = GetConfigValue("heroku_ws_secret", "heroku");

        $this->debug = false;

    }

    //  execute
    //
    //  execute a webservice call and return the data.
    // ---------------------------------------------------------------------
    protected function get($wsurl)
    {
        return $this->execute($wsurl, HTTP_METHOD_GET, array());
    }
    protected function put($wsurl, $parameters)
    {
        return $this->execute($wsurl, HTTP_METHOD_PUT, $parameters);
    }
    public function post($wsurl, $parameters)
    {
        return $this->execute($wsurl, HTTP_METHOD_POST, $parameters);
    }
    protected function delete($wsurl)
    {
        return $this->execute($wsurl, HTTP_METHOD_DELETE);
    }
    protected function execute($wsurl, $method, $params = array()) {
        try
        {
            $retval = $this->_initWsReturnObject();
            $retval['request']['url'] = $wsurl;
            $retval['request']['params'] = $params;

            $headers = [];
            $headers['Accept'] = 'application/vnd.heroku+json; version=3';
            $headers['Authorization'] = "Bearer {$this->ws_secret}";
            $headers['Content-Type'] = 'application/json';

            $client_options = [];
            $client_options['headers'] = $headers;
            $client_options['verify'] = false;
            $client_options['timeout'] = $this->ws_timeout;
            $client_options['connect_timeout'] = $this->ws_connect_timeout;

            $client = new GuzzleHttp\Client($client_options);
            $response = $client->request($method, $wsurl, ['form_params' => $params]);

            if ($this->debug) pprint_r($response);

            $status_code = $response->getStatusCode();
            $retval['response']['status_code'] = $status_code;

            if ($status_code >= 200 && $status_code < 300) {
                $json = $response->getBody();
                $retval['result'] = json_decode($json, true);
            }

        }
        catch (Exception $e)
        {
            if ($e != null)
            {
                $retval['request']['url'] = $wsurl;
                $retval['response']['error_message'] = $e->getMessage();
            }
            LogIt('HerokuRequest', $e->getMessage());
        }

        return $retval;
    }

    // WEB SERVICE FUNCTIONS -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    private function _initWsReturnObject()
    {
        // _initWsReturnObject
        //
        // Every call to a web service will return this result object.
        // Create an empty version and return it so we can populate it with
        // data as we move through the communciation process.
        // ------------------------------------------------------------------

        $results = array();
        $results['request'] = array();
        $results['request']['url'] = "";
        $results['response']['error_message'] = "";
        $results['response']['status_code'] = "";
        $results['result'] = Array();

        return $results;
    }

}


/* End of file HerokuRequest_model.php */
/* Location: ./application/models/HerokuRequest_model.php */
