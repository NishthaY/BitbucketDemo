<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class UIButton extends UIElement
{

    public $size;
    public $color;
    public $callback_onclick;
    private $_is_submit;
    private $_classes;

    /**
     * UIElement constructor.
     * @param null $form_type
     */
    function __construct( $form_type=null )
    {
        $this->size = "btn";
        $this->callback_onclick = "";
        $this->color = "btn-primary";
        $this->_classes = array();
        $this->_is_submit_button = false;

    }

    public function setIsSubmit( $boolean )
    {
        if ( ! is_bool($boolean) ) throw new Exception("Expected bool.");
        $this->_is_submit = $boolean;
    }
    public function getButtonType()
    {
        if ( $this->_is_submit ) return "submit";
        return "button";
    }
    public function addClass( $class )
    {
        if ( ! in_array($class, $this->_classes) )
        {
            $this->_classes[] = $class;
        }
    }
    protected function getClasses( )
    {
        $out = "";
        if ( empty($this->_classes) ) return $out;

        foreach($this->_classes as $item)
        {
            $out .= "{$item} ";
        }
        $out = trim($out);
        return $out;
    }

    protected function getButtonSize()
    {
        $size = strtoupper($this->size);
        $size = replaceFor($size, " ", "_");

        if ( $size === 'LARGE' ) return "btn-lg";
        if ( $size === 'BIG' ) return "btn-lg";
        if ( $size === 'LG' ) return "btn-lg";
        if ( $size === 'WF_BUTTON' ) return "btn-lg";
        if ( $size === 'WORKFLOW_BUTTON' ) return "btn-lg";

        if ( $size === 'SMALL' ) return "btn-sm";
        if ( $size === 'SM' ) return "btn-sm";

        if ( $size === 'EXTRA_SMALL' ) return "btn-xs";
        if ( $size === 'REALLY_SMALL' ) return "btn-xs";
        if ( $size === 'VERY_SMALL' ) return "btn-xs";
        if ( $size === 'ACTION_BUTTON' ) return "btn-xs";

        return "btn";
    }

    protected function getButtonColor()
    {
        $color = strtoupper($this->color);

        if ( $color === 'WHITE' )        return "btn-white";
        if ( $color === 'TRANSPARENT' )  return "btn-secondary";
        if ( $color === 'DISABLED' )     return "btn-working";
        if ( $color === 'GRAY' )         return "btn-working";
        if ( $color === 'BUSY' )         return "btn-working";
        if ( $color === 'WORKING' )      return "btn-working";

        return "btn-primary";
    }
    protected function getButtonColorOffset()
    {
        $color = fRight($this->getButtonColor(), "-");
        return "btn-offset-{$color}";
    }




}
