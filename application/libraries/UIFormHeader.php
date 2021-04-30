<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class UIFormHeader {

    // Variables
    protected $title;
    protected $buttons;
    protected $widgets;
    protected $attributes;
    protected $links;

    function __construct($title = '') {
        $this->title = $title;
        $this->buttons = array();
        $this->attributes = array();
        $this->links = array();
        $this->widgets = array();
    }

	// name
	function getTitle(){ return $this->title; }
    function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    // button
    function addButton($button) { $this->buttons[] = $button; }

    function addWidget($widget) { $this->widgets[] = $widget; }

    function addLinkDropdown( $title, $text, $url, $selected )
    {
        if ( GetStringValue($title) === '' ) return;
        if ( GetStringValue($text) === '' ) return;
        if ( GetStringValue($url) === '' ) return;

        $link = array();
        $link['text'] = GetStringValue($text);
        $link['url'] = GetStringValue($url);

        // Search for an existing collection of links that match
        // the title.  If we find it, grab the index number.  Because
        // it exists, we know there is a title and a links collection
        // in place.
        $index = null;
        for($i=0;$i<count($this->links);$i++)
        {
            if ( isset($this->links[$i]['title']) )
            {
                if ( $this->links[$i]['title'] === $title )
                {
                    $index = $i;
                    break;
                }
            }
        }
        if( $index === null )
        {
            $index = count($this->links);
            $this->links[$index]['title'] = $title;
            $this->links[$index]['selected'] = $selected;
            $this->links[$index]['links'] = array();
        }

        $link = array();
        $link['text'] = GetStringValue($text);
        $link['url'] = GetStringValue($url);
        $this->links[$index]['links'][] = $link;

    }
    function addLink( $text, $url="" )
    {
        if ( GetStringValue($text) === '' ) return;

        $link = array();
        $link['text'] = GetStringValue($text);
        $link['url'] = GetStringValue($url);
        $this->links[] = $link;
    }

    // attributes
    public function addAttribute($key, $value) {
        $key = getStringValue($key);
        $value = getStringValue($value);
        if ( strpos("'", $value) === FALSE )
        {
            $this->attributes[$key] = $value;
        }
    }

    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // ELEMENTS
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function button( $id, $label, $assoc_form_name, $attributes=array() ) {
        if ( ! is_array($attributes) ) $attributes = array();
        $array = array(
                "id" => $id,
                "assoc_form_name" => $assoc_form_name,
                "label" => $label,
                "attributes" => $this->_renderAttributes($attributes)
            );
        return $array;
    }


    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // RENDER
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    function render() {

		// render
		//
		// Returns the HTML output for a widget structure.
		// --------------------------------------------------------
        $view_array = array(
            'title' => $this->title,
            'buttons' => $this->buttons,
            'widgets' => $this->widgets,
            'links' => $this->links
        );

        $output = RenderViewAsString('templates/form/form_header', $view_array);

        return $output;
    }

    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // RENDER
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    private function _renderAttributes($attributes=null) {

        // Use the attributes set on the form if not specified on the input.
        if ( $attributes == null ) $attributes = $this->attributes;

        $out = "";
        if ( ! empty($attributes) )
        {
            foreach($this->attributes as $key=>$value)
            {
                $out .= "data-{$key}='{$value}' ";
            }
        }

        return $out;
    }
}
