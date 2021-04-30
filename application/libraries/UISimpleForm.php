<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');




class UISimpleForm extends UIForm
{

    private $_formLinks;

    public function __construct( $form_name=null, $form_id=null, $action=null, $collapsable=null ){
        parent::__construct($form_name, $form_id, $action, $collapsable);
        $this->formLinks = array();
	}

	public function addLink($label,$href, $icon_class="", $attributes=array())
    {
        $label = getStringValue($label);
        $href = getStringValue($href);
        $icon_class = getStringValue($icon_class);

        $link = array();
        $link['label'] = $label;
        $link['href'] = $href;
        $link['icon_class'] = $icon_class;
        $link['attributes'] = $this->renderAttributes($attributes);

        if ( $label !== '' && $href !== '')
        {
            $this->_formLinks[] = $link;
        }
    }

    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // ELEMENTS
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function formLink( $class='', $attributes=array() )
    {
        $array = array(
            "template" => $this->viewname(__FUNCTION__),
            "links" => $this->_formLinks,
            "class" => $class,
            "attributes" => $this->renderAttributes($attributes)
        );

        return $array;
    }
    public function buttonBar( $left_label, $left_href, $right_label="", $right_href="", $attributes=array() ) {
        if ( ! is_array($attributes) ) $attributes = array();
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "left_label" => $left_label,
                "left_href" => $left_href,
                "right_label" => $right_label,
                "right_href" => $right_href,
                "attributes" => $this->renderAttributes($attributes)
            );
        return $array;
    }
    public function phoneInput( $id, $value=null, $attributes=array(), $disabled=false ) {
        if ( ! is_array($attributes) ) $attributes = array();
        $array = array(
            "template" => $this->viewname(__FUNCTION__),
            "id" => $id,
            "value" => $value,
            "disabled_flg" => $disabled,
            "attributes" => $this->renderAttributes($attributes)
        );
        return $array;
    }
    public function codeInput( $id, $value=null, $attributes=array(), $disabled=false ) {
        if ( ! is_array($attributes) ) $attributes = array();
        $array = array(
            "template" => $this->viewname(__FUNCTION__),
            "id" => $id,
            "value" => $value,
            "disabled_flg" => $disabled,
            "attributes" => $this->renderAttributes($attributes)
        );
        return $array;
    }
    public function textarea($id,$description,$value,$rows=3,$placeholder="",$disabled=false)
    {
        $view_array = array(
            "template" => $this->viewname(__FUNCTION__),
            "id" => $id,
            "description" => $description,
            "value" => $value,
            "placeholder" => $placeholder,
            "disabled_flg" => $disabled,
            "rows" => $rows
        );
        return $view_array;
    }
    public function checkboxes($checkboxes, $prefix='')
    {
        $view_array = array(
            "template" => $this->viewname(__FUNCTION__),
            "checkboxes" => $checkboxes,
            "prefix" => $prefix,
        );
        return $view_array;
    }

    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // PROTECTED
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    protected function viewname( $element_name ) {

        $element_name = getStringValue($element_name);
        $element_name = strtoupper($element_name);

        switch($element_name) {
            case "FORM":
                return "simple/form";
                break;
            case "TEXTINPUT":
                return "simple/textinput";
                break;
            case "EMAILINPUT":
                return "simple/emailinput";
                break;
            case "PASSWORDINPUT":
                return "simple/passwordinput";
                break;
            case "PHONEINPUT":
                return "simple/phoneinput";
                break;
            case "CODEINPUT":
                return "simple/codeinput";
                break;
            case "HIDDENINPUT":
                return "hiddeninput";
                break;
            case "BUTTON":
                return "simple/button";
                break;
            case "BUTTONBAR":
                return "simple/buttonbar";
                break;
            case "FORMLINK":
                return "simple/formlink";
                break;
            case "INLINEINPUT":
                return "simple/inlineinput";
                break;
            case "TEXTAREA":
                return "simple/textarea";
                break;
            case "CHECKBOXES":
                return "simple/checkboxes";
                break;
            default:
                throw new Exception("Unsupported element {$element_name}");
                break;

        }
    }


}
