<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');




class UIForm
{
    protected $form;

    protected $min_height;
    protected $form_title;
    protected $form_name;
    protected $form_id;
    protected $action;
    protected $collapsable;
    protected $value;
    protected $is_hidden;
    protected $attributes;
    protected $elements;
    protected $upload_attributes;
    protected $upload_inputs;
    protected $form_lead;
    protected $form_description;
    protected $form_breadcrumb;


    function __construct( $form_name=null, $form_id=null, $action=null, $collapsable=null ){
        $this->form_name = $form_name;
        $this->form_id = $form_name;
        $this->action = $action;
        if ( $collapsable === TRUE ) $this->collapsable = true;
        if ( $collapsable === FALSE ) $this->collapsable = false;

        $this->value = "";
        $this->is_hidden = false;
        $this->attributes = array();
        $this->elements = array();
        $this->min_height = 0;
        $this->upload_attributes = array();
        $this->upload_inputs = array();


	}

    // upload_attributes
    public function getUploadAttributes() { return $this->upload_attributes; }
    public function setUploadAttributes( $value ) { $this->upload_attributes = $value; }

    // upload_inputs
    public function getUploadInputs() { return $this->upload_inputs; }
    public function setUploadInputs( $value ) { $this->upload_inputs = $value; }

    // form_title
    public function getTitle() { return $this->form_title; }
    public function setTitle($form_title) { $this->form_title = $form_title; }

    // form_lead
    public function getLead() { return $this->form_lead; }
    public function setLead($form_lead) { $this->form_lead = $form_lead; }

    // form_description
    public function getDescription() { return $this->form_description; }
    public function setDescription($value) { $this->form_description = $value; }

    // form_breadcrumb
    public function getBreadcrumb() { return $this->form_description; }
    public function setBreadcrumb($labels, $links=array(), $classes=array(), $crush_flg=true) {
        $view_array = $this->breadcrumb($labels, $links, $classes, $crush_flg);
        $this->form_breadcrumb = RenderViewAsString("templates/form/breadcrumb", $view_array);
    }

    // form_name
    public function getName() { return $this->form_name; }
    public function setName($form_name) { $this->form_name = $form_name; }

    // form_id
    public function getId() { return $this->form_id; }
    public function setId($form_id) { $this->form_id = $form_id; }

    // action
    public function getAction() { return $this->action; }
    public function setAction($action) { $this->action = $action; }

    // collapsable
    public function getCollapsable() { return $this->collapsable; }
    public function setCollapsable($collapsable) { $this->collapsable = $collapsable; }

    // is_hidden
    public function getIsHidden() { return $this->is_hidden; }
    public function setIsHidden($is_hidden) { $this->is_hidden = $is_hidden; }

    // value
    public function getValue() { return $this->value; }
    public function setValue($value) { $this->value = $value; }

    // min_height
    public function getMinHeight() { return $this->min_height; }
    public function setMinHeight($min_height) { $this->min_height = $min_height; }


    // Add Attributes to Form.
    public function addAttribute($key, $value) {
        $key = getStringValue($key);
        $value = getStringValue($value);
        if ( strpos("'", $value) === FALSE )
        {
            $this->attributes[$key] = $value;
        }
    }

    // Add Element to Form.
    public function getElements() { return $this->elements; }
    public function addElement($element)
    {
        if ( is_array($element) && isset($element["parent"] ))
        {
            foreach ($this->elements as &$elements_item)
            {
                if (isset($elements_item["template"] ))
                {
                    if (($elements_item["template"] == $element["parent"]) and ($element["group_id"] == $elements_item["id"]))
                    {
                        array_push($elements_item["list"], $element);
                    }
                }
            }
        }
        else
        {
            array_push($this->elements, $element);
        }
    }


    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // ELEMENTS
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function textInput( $id, $description, $value=null, $placeholder="", $attributes=array(), $disabled=false, $hidden=false ) {
        if ( ! is_array($attributes) ) $attributes = array();
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "id" => $id,
                "description" => $description,
                "placeholder" => $placeholder,
                "value" => $value,
                "disabled_flg" => $disabled,
                "attributes" => $this->renderAttributes($attributes),
                "hidden_flg" => $hidden
            );
        return $array;
    }
    public function emailInput( $id, $description, $value=null, $placeholder="", $attributes=array(), $disabled=false, $hidden=false ) {
        if ( ! is_array($attributes) ) $attributes = array();
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "id" => $id,
                "description" => $description,
                "placeholder" => $placeholder,
                "value" => $value,
                "disabled_flg" => $disabled,
                "attributes" => $this->renderAttributes($attributes),
                "hidden_flg" => $hidden
            );
        return $array;
    }
    public function hiddenInput($id, $value, $attributes=array()) {
        if ( ! is_array($attributes) ) $attributes = array();
		$array = array(
				"template" => $this->viewname(__FUNCTION__),
				"id" => $id,
				"value" => $value,
                "attributes" => $this->renderAttributes($attributes)
			);
		return $array;
	}
    public function passwordInput($id, $description, $value=null, $placeholder="", $attributes=array()) {
        if ( ! is_array($attributes) ) $attributes = array();
		$array = array(
				"template" => $this->viewname(__FUNCTION__),
				"id" => $id,
				"description" => $description,
				"placeholder" => $placeholder,
                "value" => $value,
                "attributes" => $this->renderAttributes($attributes)
			);
		return $array;
	}
    public function button($id, $description, $class=null, $submit=false, $attributes=array(), $disabled = false ) {
        if ( ! is_array($attributes) ) $attributes = array();
		$b = array(
				"template" => $this->viewname(__FUNCTION__),
				"id" => $id,
				"description" => $description,
				"class" => $class,
				"submit" => $submit,
                "attributes" => $this->renderAttributes($attributes),
                "disabled" => $disabled
			);

		return $b;
	}
    public function submitButton($id, $description, $class=null, $attributes=array(), $disabled=false)
    {
        return $this->button($id, $description, $class, true, $attributes, $disabled);
    }
    public function dropdown($id, $description, $placeholder, $list, $selected, $class, $change_callback='', $inline_flg=false, $scrollable=false, $button_label='Select')
    {
            $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "id" => $id,
                "description" => $description,
                "placeholder" => $placeholder,
                "class" => $class,
                "list" => $list,
                "selected" => $selected,
                "change_callback" => $change_callback,
                "inline_flg" => $inline_flg,
                "scrollable_flg" => $scrollable,
                "button_label" => $button_label
            );

        return $array;
    }
    public function htmlView($viewname, $view_array, $id="", $class="", $hidden=false) {
        $array = array(
            "template" => $this->viewname(__FUNCTION__),
            "prerender" => RenderViewAsString($viewname, $view_array),
            "id" => $id,
            "class" => $class,
            "hidden_flg" => $hidden
        );
        return $array;
    }
    public function checkBox($id, $description, $inline_description="", $is_checked=false, $is_disabled = false, $hidden=false )
    {
            $cb = array(
                "template" => $this->viewname(__FUNCTION__),
                "id" => $id,
                "description" => $description,
                "inline_description" => $inline_description,
                "is_checked" => $is_checked,
                "is_disabled" => $is_disabled,
                "is_hidden" => $hidden
            );

        return $cb;
    }
    public function breadcrumb( $labels, $links=array(), $classes=array(), $crush_flg=false) {
        $view_array = array(
            "template" => $this->viewname(__FUNCTION__),
            "labels" => $labels,
            "links" => $links,
            "classes" => $classes,
            "crush" => $crush_flg
        );
        return $view_array;
    }
    public function inlineInput( $id, $button_label, $value, $href, $success_callback, $description='', $failed_callback='' )
    {
        $view_array = array(
            "template" => $this->viewname(__FUNCTION__)
            , "id" => $id
            , "value" => $value
            , "button_label" => $button_label
            , "description" => $description
            , "href" => $href
            , 'callback' => $success_callback
            , 'failed_callback' => $failed_callback
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
                return "form";
                break;
            case "INLINEINPUT":
                return "inlineinput";
                break;
            case "TEXTINPUT":
                return "textinput";
                break;
            case "EMAILINPUT":
                return "emailinput";
                break;
            case "PASSWORDINPUT":
                return "passwordinput";
                break;
            case "HIDDENINPUT":
                return "hiddeninput";
                break;
            case "DROPDOWN":
                return "dropdown";
                break;
            case "BUTTON":
                return "button";
                break;
            case "HTMLVIEW":
                return "htmlview";
                break;
            case "CHECKBOX":
                return "checkbox";
                break;
            case "CHECKBOXTABLE":
                return "table_checkbox";
                break;
            case "BREADCRUMB":
                return "breadcrumb";
                break;
            default:
                throw new Exception("Unsupported element {$element_name}");
                break;

        }
    }

    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // RENDER
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function renderAttributes($attributes=null) {

        // Use the attributes set on the form if not specified on the input.
        if ( $attributes == null ) $attributes = $this->attributes;

		$out = "";
        if ( ! empty($attributes) )
        {
            foreach($attributes as $key=>$value)
    		{
    			$out .= "data-{$key}='{$value}' ";
    		}
        }

		return $out;
	}

	public function renderElement($element) {

        // If we were handed a UIElement object render it.
        if ( ! is_array($element) ) {
            $component_classes = array('UIElement', 'UIButton');
            if ( in_array(get_parent_class($element), $component_classes) )
            {
                return $element->render();
            }
        }

        $output="";
		$templateURL = "";

		$template = $element["template"];
		unset($element["template"]);

		$templateURL = 'templates/form/'.$template;
		$output = RenderViewAsString($templateURL, $element);

		return $output;

	}

    public function render() { return $this->renderForm(); }
	public function renderForm()
	{
		$elementlist = array();

		foreach ($this->elements as $element)
		{
		    $element_html = $this->renderElement($element);
			array_push($elementlist,$element_html);
		}

        // Add a hidden class if the is_hidden property is set.
        $hidden_class = "";
        if ( $this->is_hidden )
        {
            $hidden_class = "hidden";
        }

		$view_data = array(
            "form_title" => $this->form_title,
			"form_id" => $this->form_id,
			"form_name" => $this->form_name,
            "action" => $this->action,
			"elements" => $elementlist,
            "hidden" => $hidden_class,
			"collapsable" => $this->collapsable,
            "min_height" => $this->min_height,
			"attributes" => $this->renderAttributes(),
            "upload_inputs" => $this->upload_inputs,
            "upload_attributes" => $this->upload_attributes,
            "form_lead" => $this->form_lead,
            "form_description" => $this->form_description,
            "form_breadcrumb" => $this->form_breadcrumb
		);

		$output = RenderViewAsString("templates/form/" . $this->viewname("form"), $view_data);

		return $output;

	}



}
