<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class UIElement
{

    public $id;
    public $form_type;
    public $is_hidden;
    public $description;
    protected $attributes;

    /**
     * UIElement constructor.
     * @param null $form_type
     */
    function __construct( $form_type=null )
    {
        $this->id = "";
        $this->form_type = strtolower($form_type);
        $this->is_hidden = false;
        $this->description = "";
        $this->attributes = array();
    }


    // id
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    // is_hidden
    public function getIsHidden() { return $this->is_hidden; }
    public function setIsHidden($is_hidden) { $this->is_hidden = $is_hidden; }

    // description
    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }

    // Attributes
    public function getAttributes() { return $this->_renderAttributes(); }
    public function setAttributes($attributes) { $this->attributes = $attributes; }
    public function addAttribute($key, $value)
    {
        if ( getStringValue($key) === "" ) return;
        $this->attributes[$key] = $value;
    }




    /**
     * _renderAttributes
     *
     * Turn the key/value data found in the attributes property
     * and turn it into markup that can be added to an html tag to
     * be used with the jQuery data function.
     *
     * ie: data-author='Brian Headlee'
     * Here the attributes array had a key of author with a value.
     *
     * @return string
     */
    private function _renderAttributes()
    {
        $out = "";
        if ( ! empty($this->attributes) )
        {
            foreach($this->attributes as $key=>$value)
            {
                $out .= "data-{$key}='{$value}' ";
            }
        }
        return $out;
    }




}
