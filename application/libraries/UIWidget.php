<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class UIWidget {

    // Variables
    protected $name;
    protected $body;
	protected $href;
	protected $callback;
    protected $starting;
    protected $inline_flg;
    protected $task_name;

    function __construct($name = '') {
        $this->name = $name;
        $this->body = "";
		$this->href = "";
		$this->callback = "";
        $this->inline_flg = false;
    }

    // task_name
    function getTaskName(){ return $this->task_name; }
    function setTaskName($task_name) {
        $this->task_name = $task_name;
        return $this;
    }

	// name
	function getName(){ return $this->name; }
    function setName($name) {
        $this->name = $name;
        return $this;
    }

    // body
    function getBody(){ return $this->body; }
	function setBody($body = '') {
        $this->body = $body;
        return $this;
    }

	// href
	function getHref(){ return $this->href; }
	function setHref($href=''){
		$this->href = $href;
		return $this;
	}

	// callback
	function getCallback(){ return $this->callback; }
	function setCallback($callback=''){
		$this->callback = $callback;
		return $this;
	}

    // starting
	function getStarting(){ return $this->callback; }
	function setStarting($starting=''){
		$this->starting = $starting;
		return $this;
	}

    // inline_flg
    function getInlineFlg(){ return $this->inline_flg; }
    function setInlineFlg($inline_flg=false){
        $this->inline_flg = $inline_flg;
        return $this;
    }

    function render() {

		// render
		//
		// Returns the HTML output for a widget structure.
		// --------------------------------------------------------


        $view_array = array(
            'name' => $this->name,
            'body' => $this->body,
			'href' => $this->href,
			'callback' => $this->callback,
            'starting' => $this->starting,
            'inline_flg' => $this->inline_flg,
            'task_name' => $this->task_name

        );

        $output = RenderViewAsString('templates/widget/widget', $view_array);

        return $output;
    }
}
