<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');




class UIModalForm extends UIForm
{

    function __construct( $form_name=null, $form_id=null, $action=null, $collapsable=null ){
        parent::__construct($form_name, $form_id, $action, $collapsable);
	}

    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // ELEMENTS
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    public function agetypeEditor( $id, $description, $age_rules=array(), $best_guess_flg, $attributes=array() ) {

        // Default the age type, if needed.
        $age_type = getArrayStringValue("AgeTypeName", $age_rules);
        if ( getStringValue($age_type) == "" ) $age_type = "washed";

        // Default the anniversary month.
        $default_month = getArrayStringValue("AnniversaryMonth", $age_rules);
        if ( $default_month == "" ) $default_month = "1";
        $default_month = str_pad(getStringValue(getIntValue($default_month)), 2, "0", STR_PAD_LEFT);

        // Default the anniversary day.
        $default_day = getArrayStringValue("AnniversaryDay", $age_rules);
        if ( $default_day == "" ) $default_day = "1";
        $default_day = str_pad(getStringValue(getIntValue($default_day)), 2, "0", STR_PAD_LEFT);

        // Pull the year off of the upload date.
        $anniversary_year = date('Y',strtotime(GetUploadDate()));

        // Show or hide certain sections based on the initial inputs.
        $anniversary_class = "hidden";
        if ( $age_type == "anniversary" ) $anniversary_class = "";
        $washed_class = "hidden";
        if ( $age_type == "washed" ) $washed_class = "";
        $issued_class = "hidden";
        if ( $age_type == "issued" ) $issued_class = "";

        $CI = &get_instance();
        $CI->load->model("Ageband_model", "ageband_model", true);
        $age_type_dropdown = $this->dropdown("age_calculation_type", "", "", $CI->ageband_model->get_agetypes_dropdown(), $age_type, "", "AgeCalculationTypeChangeHandler", false);
        $anniversary_month_dropdown = $this->dropdown("anniversary_month", "", "", DropdownMonths(), $default_month, "", "AgeRuleMonthChangeHandler", true, true);
        $anniversary_day_dropdown = $this->dropdown("anniversary_day", "", "", DropdownDays(), $default_day, "", "AgeRuleDayChangeHandler", true, true);

        if ( ! is_array($attributes) ) $attributes = array();
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "id" => $id,
                "description" => $description,
                "age_type" => $age_type,
                "age_type_dropdown" => $this->renderElement($age_type_dropdown),
                "anniversary_month_dropdown" => $this->renderElement($anniversary_month_dropdown),
                "anniversary_day_dropdown" => $this->renderElement($anniversary_day_dropdown),
                "anniversary_year" => $anniversary_year,
                "anniversary_class" => $anniversary_class,
                "washed_class" => $washed_class,
                "issued_class" => $issued_class,
                "best_guess_flg" => $best_guess_flg,
                "attributes" => $this->renderAttributes($attributes)
            );
        return $array;
    }
    public function agebandEditor( $id, $description, $bands=array(), $best_guess_flg=false, $attributes=array() ) {
        if ( ! is_array($attributes) ) $attributes = array();
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "id" => $id,
                "description" => $description,
                "bands" => $bands,
                "best_guess_flg" => $best_guess_flg,
                "attributes" => $this->renderAttributes($attributes)
            );
        return $array;
    }
    public function monthAndYear( $id, $description, $attributes=array() ) {
        if ( ! is_array($attributes) ) $attributes = array();
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "id" => $id,
                "description" => $description,
                "attributes" => $this->renderAttributes($attributes)
            );
        return $array;
    }
    public function moneyInput( $id, $description, $value=null, $placeholder="", $attributes=array(), $disabled=false ) {
        if ( ! is_array($attributes) ) $attributes = array();
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "id" => $id,
                "description" => $description,
                "placeholder" => $placeholder,
                "value" => $value,
                "disabled_flg" => $disabled,
                "attributes" => $this->renderAttributes($attributes)
            );
        return $array;
    }
    public function ledger( $id, $description, $data, $id_column, $money_column=null, $removable_rows=true ) {
        $array = array(
                "template" => $this->viewname(__FUNCTION__),
                "id" => $id,
                "description" => $description,
                "data" => $data,
                "money_column" => $money_column,
                "id_column" => $id_column,
                "removable_rows" => $removable_rows
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

    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // PROTECTED
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    protected function viewname( $element_name ) {

        $element_name = getStringValue($element_name);
        $element_name = strtoupper($element_name);

        switch($element_name) {
            case "FORM":
                return "modal/form";
                break;
            case "TEXTINPUT":
                return "modal/textinput";
                break;
            case "MONEYINPUT":
                return "modal/moneyinput";
                break;
            case "LEDGER":
                return "modal/ledger";
                break;
            case "PASSWORDINPUT":
                return "modal/passwordinput";
                break;
            case "BUTTON":
                return "modal/button";
                break;
            case "EMAILINPUT":
                return "modal/emailinput";
                break;
            case "HIDDENINPUT":
                return "hiddeninput";
                break;
            case "DROPDOWN":
                return "modal/dropdown";
                break;
                break;
            case "CHECKBOX":
                return "modal/checkbox";
                break;
            case "HTMLVIEW":
                return "htmlview";
                break;
            case "AGEBANDEDITOR":
                return "modal/agebandeditor";
                break;
            case "AGETYPEEDITOR":
                return "modal/agetypeeditor";
                break;
            case "TEXTAREA":
                return "modal/textarea";
                break;
            case "BREADCRUMB":
                return "breadcrumb";
                break;
            case "INLINEINPUT":
                return "modal/inlineinput";
                break;
            default:
                throw new Exception("Unsupported element {$element_name}");
                break;

        }
    }


}
