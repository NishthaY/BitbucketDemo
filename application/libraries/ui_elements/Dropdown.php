<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Dropdown extends UIElement
{
    //protected $id;
    //protected $form_type;
    //protected $is_hidden;
    //protected $description;
    //protected $attributes;
    public $selected;
    public $list;
    public $href;
    public $callback_onclick;
    public $callback_onselect;
    public $scrollable_flg;
    public $class;




    function __construct($form_type=null )
    {
        parent::__construct($form_type);

        $this->list = array();
        $this->selected = "";
        $this->callback_onchange = "";
        $this->scrollable_flg = true;
        $this->class = "";
    }

    public function addItem($value, $display, $disabled=false, $class="")
    {
        if ( GetStringValue($value) === '' ) return;
        if ( GetStringValue($display) === '' ) return;

        $details = array();
        $details['value']       = $value;
        $details['display']     = $display;
        $details['type']        = "item";
        $details['disabled']    = $disabled;
        $details['class']       = $class;

        $this->list[] = $details;

    }
    public function addDivider()
    {
        $details = array();
        $details['value']       = "";
        $details['display']     = "";
        $details['type']        = "divider";
        $details['disabled']    = "";
        $details['class']       = "";

        $this->list[] = $details;
    }


    /**
     * render
     *
     * Return the HTML for this html element.
     *
     * @return string|void
     */
    public function render()
    {
        // TODO: Support other form types beside just "inline";


        $view_array = array();
        $view_array['dropdown_id']          = $this->id;
        $view_array['is_hidden']            = $this->is_hidden;
        $view_array['attributes']           = $this->getAttributes();
        $view_array['list']                 = $this->list;
        $view_array['callback_onchange']    = $this->callback_onchange;
        $view_array['scrollable']           = $this->scrollable_flg;
        $view_array['class']                = $this->class;
        $view_array['selected']             = $this->selected;
        return RenderViewAsString("templates/form/inline/dropdown", $view_array);
    }




}
