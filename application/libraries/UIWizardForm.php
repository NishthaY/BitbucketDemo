<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');




class UIWizardForm extends UIForm
{
    protected $top_buttons;
    protected $panel_flg;

    function __construct( $form_name=null, $form_id=null, $action=null, $collapsable=null ){
        parent::__construct($form_name, $form_id, $action, $collapsable);
        $this->top_buttons = array();
        $this->panel_flg = false;
	}


    public function addTopWizardButton($button)
    {
        $this->top_buttons[] = $button;
    }

    public function getPanelFlg() { return $this->panel_flg; }
    public function setPanelFlg($value) { $this->panel_flg = $value; }

    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // ELEMENTS
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function upload_button($id, $description, $class=null, $submit=false, $attributes=array() ) {
        if ( ! is_array($attributes) ) $attributes = array();
		$b = array(
				"template" => $this->viewname(__FUNCTION__),
				"id" => $id,
				"description" => $description,
				"class" => $class,
				"submit" => $submit,
                "attributes" => $this->renderAttributes($attributes)
			);

		return $b;
	}
    public function top_buttons( ) {
        $buttons = "";
        foreach($this->top_buttons as $button)
        {
            $buttons .= $this->renderElement($button);
        }
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "buttons" => $buttons
            );
        return $array;
    }
    public function adjustment_table( $adjustments, $add_button ) {
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "data" => $adjustments,
                "add_button" => $add_button
            );
        return $array;
    }
    public function mapping_table( $sample_data, $mapping_columns, $required_mappings, $conditional_mappings, $has_headers, $attributes=array() ) {
        if ( ! is_array($attributes) ) $attributes = array();
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "identifier" => GetArrayStringValue('identifier', $attributes),
                "identifier_type" => GetArrayStringValue('identifier_type', $attributes),
                "data" => $sample_data,
                "mapping_columns" => $mapping_columns,
                "has_headers" => $has_headers,
                "required_list" => json_encode($required_mappings),
                "conditional_list" => json_encode($conditional_mappings),
                "attributes" => $this->renderAttributes($attributes)
            );
        return $array;
    }
    public function mapping_table_missing_matches( $required_list, $conditional_list ) {
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "required_list" => json_encode($required_list),
                "conditional_list" => json_encode($conditional_list),
        );
        return $array;
    }
    public function plan_type_mapping( $upload_plan_types, $all_plan_types ) {
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "upload_plan_types" => $upload_plan_types,
                "all_plan_types" => $all_plan_types
            );
        return $array;
    }
    public function plan_type_mapping_dropdown( $index, $dropdown_items, $external_value, $selected_value, $unselected_display, $remove_map_display ) {

        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "index" => $index,
                "external_value" => $external_value,
                "dropdown_items" => $dropdown_items,
                "selected_value" => $selected_value,
                "unselected_display" => $unselected_display,
                "remove_map_dispaly" => $remove_map_display,
            );
        return $array;
    }

    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // PROTECTED
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    protected function viewname( $element_name ) {

        $element_name = getStringValue($element_name);
        $element_name = strtoupper($element_name);

        switch($element_name) {
            case "FORM":
                if ( $this->panel_flg ) return "wizard/form_panel";
                return "wizard/form";
                break;
            case "BUTTON":
                return "wizard/button";
                break;
            case "TOP_BUTTONS":
                return "wizard/top_buttons";
                break;
            case "CHECKBOX":
                return "wizard/checkbox";
                break;
            case "MAPPING_TABLE":
                return "wizard/mapping_table";
                break;
            case "MAPPING_TABLE_MISSING_MATCHES":
                return "wizard/mapping_table_missing_matches";
                break;
            case "PLAN_TYPE_MAPPING_DROPDOWN":
                return "wizard/plan_type_mapping_dropdown";
                break;
            case "PLAN_TYPE_MAPPING":
                return "wizard/plan_type_mapping";
                break;
            case "UPLOAD_BUTTON":
                return "wizard/upload_button";
                break;
            case "DROPDOWN":
                return "modal/dropdown";
                break;
            case "HIDDENINPUT":
                return "hiddeninput";
                break;
            case "ADJUSTMENT_TABLE":
                return "wizard/adjustment_table";
                break;
            case "HTMLVIEW":
                return "htmlview";
                break;
            default:
                throw new Exception("Unsupported element {$element_name}");
                break;

        }
    }

    public function renderForm()
    {
        // Render the UIWizardForm.
        $html = parent::renderForm();

        // Run the following Javascript function on page load because
        // we rendered a UIWizardForm.
        $list = array();
        $list[] = "notify_workflow_step_changed";
        $html .= RenderViewAsString("templates/javascript_onload_list", array('list' => $list));

        return $html;
    }
}
