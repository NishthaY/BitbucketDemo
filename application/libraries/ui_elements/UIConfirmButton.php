<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class UIConfirmButton {

    // Variables
    protected $href;
    protected $callback;
    protected $callback_param;
    protected $label;
    protected $color;
    protected $spinner;
    protected $buttons;


    function __construct() {
        $this->href = "";
        $this->callback = "success";
        $this->callback_param = "";
        $this->label = "Confirmed";
        $this->color = "#81c868";
        $this->spinner = true;
        $this->buttons = array();
    }

    // href
    function getHref(){ return $this->href; }
    function setHref($href) {
        $this->href = $href;
        return $this;
    }

    // callback
    function getCallback(){ return $this->callback; }
    function setCallback($callback) {
        $this->callback = $callback;
        return $this;
    }

    // callback parameter
    function getCallbackParameter(){ return $this->callback_param; }
    function setCallbackParameter($callback_param) {
        $this->callback_param = $callback_param;
        return $this;
    }

    // label
    function getLabel(){ return $this->label; }
    function setLabel($label) {
        $this->label = $label;
        return $this;
    }

    // color
    function getColor(){ return $this->color; }
    function setColor($color) {
        $this->color = $color;
        return $this;
    }

    // spinner
    function getSpinner(){ return $this->spinner; }
    function setSpinner($spinner) {
        $this->spinner = $spinner;
        return $this;
    }

    function addExtraEnabledButton( $label, $callback="", $href="", $attributes=array() )
    {
        $details = array();
        $details['label'] = GetStringValue($label);
        $details['callback'] = GetStringValue($callback);
        $details['href'] = GetStringValue($href);
        $details['attributes'] = $attributes;
        $this->buttons[] = $details;

    }


    function render() {

		// render
		//
		// Returns the HTML output for a widget structure.
		// --------------------------------------------------------


        $view_array = array(
            'href' => $this->href,
            'callback' => $this->callback,
            'callback_parameter' => $this->callback_param,
            'label' => $this->label,
            'color' => $this->color,
            'spinner' => $this->spinner,
            'buttons' => $this->buttons
        );

        $output = RenderViewAsString('templates/confirm_button', $view_array);

        return $output;
    }
}
