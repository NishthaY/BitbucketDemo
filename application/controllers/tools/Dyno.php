<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );

class Dyno extends Tool
{

    protected $delay;
    private $_debug;


    public function __construct()
    {
        parent::__construct();
        $this->_debug = true;   // Write detailed information to STDOUT
    }


    /**
     * find
     *
     * Get a list of all running dynos.
     *
     */
    public function find()
    {
        $results = $this->HerokuDynoRequest_model->get_dynos(APP_NAME, true);
        print_r($results);
    }

    /**
     * info
     *
     * Review the known information about a running dyno.
     *
     * @param string $dyno_name
     */
    public function info($dyno_name="")
    {
        if (  GetStringValue($dyno_name) === '' )
        {
            print "Missing required input dyno_name.\n";
            exit;
        }

        $results = $this->HerokuDynoRequest_model->get_dyno_info(APP_NAME, $dyno_name);
        print_r($results);
    }

    /**
     * test
     *
     * Create a one-off dyno that executes a command.  This will test the
     * POST ability to communicate with Heroku.
     *
     */
    public function test()
    {
        $results = $this->HerokuDynoRequest_model->create_oneoff_dyno( APP_NAME, "ls -lart", true );
        print_r($results);
    }



}

/* End of file Dyno.php */
/* Location: ./application/controllers/cli/Dyno.php */
