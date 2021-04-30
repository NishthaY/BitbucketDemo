<?php defined('BASEPATH') OR exit('No direct script access allowed');
require('vendor/autoload.php');

Class CI_Pusher
{
    public function __construct()
    {
        $options = array(
            'cluster' => GetPusherAPICluster(),
            'encrypted' => true
        );

        // Get config variables
        $app_id     = GetPusherAPIAppId();
        $app_key    = GetPusherAPIKey();
        $app_secret = GetPusherAPISecret();
        $options    = $options;

        // Create Pusher object only if we don't already have one
        if (!isset($this->pusher))
        {
            // Create new Pusher object
            $this->pusher = new Pusher\Pusher($app_key, $app_secret, $app_id, $options);
        }
    }
    public function get_pusher()
    {
        return $this->pusher;
    }
    public function __get($var)
    {
        return get_instance()->$var;
    }
}
