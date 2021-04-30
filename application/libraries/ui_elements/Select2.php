<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Select2 extends UIElement
{
    // Select2
    // The select 2 form element is a dropdown box that
    // is searchable.

    protected $selected_value;  // Item in list that is currently selected.
    private $data;              // Nest array of categorized data.
    private $sections;          // List of categories in the data.
    private $default_display;   // Shows this text if nothing is selected.  ( empty string for value )
    private $default_value;     // Shows this text if nothing is selected.  ( empty string for value )
    private $placeholder;


    /**
     * Select2 constructor.
     * @param null $form_type
     */
    function __construct($form_type=null )
    {
        parent::__construct($form_type);

        $this->selected_value = "";
        $this->data = array();
        $this->sections = array();
        $this->placeholder = "";
    }

    // selected_value
    public function getSelectedValue() { return $this->selected_value; }
    public function setSelectedValue($selected_value) { $this->selected_value = $selected_value; }


    public function addPlaceholder($display)
    {
        $this->placeholder = GetStringValue($display);
    }

    /**
     * addDefaultItem
     *
     * This function will add a key/value pair to the top of the select
     * list which will be automatically selected if a selected value is
     * not set.
     *
     * @param $display
     * @param $value
     */
    public function addDefaultItem($display, $value)
    {
        $this->default_display = getStringValue($display);
        $this->default_value = getStringValue($value);
    }

    /**
     * addItem
     *
     * This function will add the display/value to the select list.
     * This item will be categorized under the section specified.
     *
     * @param $section
     * @param $display
     * @param $value
     */
    public function addItem($section, $display, $value)
    {
        if (getStringValue($display) === '' ) return;
        if (getStringValue($section) === '' ) $section_key = "null";

        $item = array();
        $item['display'] = getStringValue($display);
        $item['value'] = getStringValue($value);

        // Add a new section if this is the first time we have seen it.
        $section_key = trim(getStringValue($section));
        if ( ! isset( $this->sections[$section_key] ) )
        {
            $this->sections[$section_key] = TRUE;
            $this->data[$section_key] = array();
        }

        $section = $this->data[$section_key];
        $section[] = $item;
        $this->data[$section_key] = $section;

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
        $view_array = array();
        $view_array = array_merge($view_array, array('id' => $this->id));
        $view_array = array_merge($view_array, array('selected_value' => $this->selected_value));
        $view_array = array_merge($view_array, array('is_hidden' => $this->is_hidden));
        $view_array = array_merge($view_array, array('data' => $this->data));
        $view_array = array_merge($view_array, array('description' => $this->description));
        $view_array = array_merge($view_array, array('default_display' => $this->default_display));
        $view_array = array_merge($view_array, array('default_value' => $this->default_value));
        $view_array = array_merge($view_array, array('attributes' => $this->getAttributes()));
        $view_array = array_merge($view_array, array('placeholder' => $this->placeholder));

        if ( file_exists(APPPATH."views/templates/form/{$this->form_type}/select2.php") )
        {
            return RenderViewAsString("templates/form/{$this->form_type}/select2", $view_array);
        }
        if ( file_exists(APPPATH."views/templates/form/select2.php") )
        {
            return RenderViewAsString("templates/form/select2", $view_array);
        }
        return "";



    }




}
