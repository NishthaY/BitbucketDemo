<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MultiOptionButton extends UIButton
{
    //protected $id;
    //protected $form_type;
    //protected $is_hidden;
    //protected $description;
    //protected $attributes;
    //public $size;
    //public $type;
    //public $callback_onclick;

    public $selected;
    public $success_label;
    public $failed_label;
    protected $list;




    function __construct($form_type=null )
    {
        parent::__construct($form_type);

        $this->list = array();
        $this->selected = "";
        $this->success_label = 'Success';
        $this->failed_label = 'Failed';

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

        // Make sure the list has items and that the user has set the selected
        // value to something in the list.
        $this->_validateList();


        $view_array = array();
        $view_array['list'] = $this->list;
        $view_array['selected'] = $this->selected;
        $view_array['button_size'] = $this->getButtonSize();
        $view_array['button_color'] = $this->getButtonColor();
        $view_array['button_color_offset'] = $this->getButtonColorOffset();
        $view_array['attributes'] = $this->getAttributes();
        $view_array['is_hidden'] = $this->is_hidden;
        $view_array['classes'] = $this->getClasses();
        $view_array['button_type'] = $this->getButtonType();
        $view_array['callback_onclick'] = $this->callback_onclick;
        $view_array['id'] = $this->getId();
        $view_array['success_label'] = $this->success_label;
        $view_array['failed_label'] = $this->failed_label;


        return RenderViewAsString("templates/form/multi_option_button", $view_array);
    }

    /**
     * _validateList
     *
     * This function will throw an error if there are not enough properties
     * set for this component to function.  Specifically, you have to have at
     * least one item in the list.  If the selected value can't be found in
     * the list, then the top item in the list will become the selected item.
     *
     * @throws Exception
     */
    private function _validateList()
    {
        // If the user has not set any items in the list, then they can't have a button.
        if ( count($this->list) === 0 ) throw new Exception("MultiOptionButton must have at least one item in the list.");

        // Set the selected value to the first item in the list, if the user did not set one.
        if ( getStringValue($this->selected) === '' ) {

            $item = $this->list[0];
            $this->selected = GetArrayStringValue('value', $item);
        }

        // Make sure the selected item is in the list.
        $matched = false;
        foreach($this->list as $item)
        {
            if ( $this->selected === GetArrayStringValue('value', $item) )
            {
                $matched = true;
            }
        }

        // If the selected item is not in the list, then default to the first
        // item in the list.
        if ( ! $matched )
        {
            $item = $this->list[0];
            $this->selected = GetArrayStringValue('value', $item);
        }


    }



}
