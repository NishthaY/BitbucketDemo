<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class UIAlert {

    // Variables
    protected $message;
    protected $color;

    function __construct() {
        $this->message = "";
        $this->type = "success";
    }

    // message
    function getMessage(){ return $this->message; }
    function setMessage($message) {
        $this->message = $message;
        return $this;
    }

	// type
	function getType(){ return $this->type; }
    function setType($type) {
        $this->type = $type;
        return $this;
    }



    function render() {

		// render
		//
		// Returns the HTML output for a widget structure.
		// --------------------------------------------------------


        $view_array = array(
            'message' => $this->message,
            'type' => $this->type
        );

        $output = RenderViewAsString('templates/alert', $view_array);

        return $output;
    }
}
