<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');




class Menu
{
    private $menu;

    function __construct(){
		$this->menu = array();
	}

    public function add( $title, $short_desc, $href, $is_child=false, $selected=false, $disabled=false, $icon_class='' ) {

        $item = array();
        $item['title'] = getStringValue($title);
        $item['short_desc'] = getStringValue($short_desc);
        $item['href'] = getStringValue($href);
        $item['selected'] = getStringValue($selected);
        $item['disabled'] = getStringValue($disabled);
        $item['is_child'] = getStringValue($is_child);
        $item['icon_class'] = getStringValue($icon_class);

        $this->menu[] = $item;

    }
    public function render() {

        $view_array = array();
        $view_array = array_merge($view_array, array("menu" => $this->menu));
        return RenderViewAsString("templates/side_menu", $view_array);

    }

}
