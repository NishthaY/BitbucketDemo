<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class UIbackgroundTask {

    // Variables
    protected $name;
	protected $href;
	protected $refresh_minutes;
    protected $debug;
    protected $info;

    function __construct($name = '') {
        $this->name = $name;
		$this->href = "";
		$this->refresh_minutes = "";
        $this->debug = 0;
        $this->info = 0;
    }

	// name
	function getName(){ return $this->name; }
    function setName($name) {
        $this->name = $name;
        return $this;
    }

	// href
	function getHref(){ return $this->href; }
	function setHref($href=''){
		$this->href = $href;
		return $this;
	}

	// refresh_minutes
	function getRefreshMinutes(){ return $this->refresh_minutes; }
	function setRefreshMinutes($refresh_minutes="5"){
		$this->refresh_minutes = $refresh_minutes;
		return $this;
	}

    // debug
    function getDebug(){ return $this->debug; }
    function setDebug($debug=0){
        $this->debug = $debug;
        return $this;
    }

    // info
    function getInfo(){ return $this->info; }
    function setInfo($info=0){
        $this->info = $info;
        return $this;
    }

    function render() {

		// render
		//
		// Returns the HTML output for a background-task structure.
		// --------------------------------------------------------

        $CI = & get_instance();

        $view_array = array(
            'name' => $this->name,
			'href' => $this->href,
			'refresh_minutes' => $this->refresh_minutes,
            'debug' => $this->debug,
            'info' => $this->info
        );

        $output = $CI->load->view('templates/task/background_task', $view_array, true);

        return $output;
    }
}
