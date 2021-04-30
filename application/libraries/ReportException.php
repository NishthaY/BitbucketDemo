<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class ReportException extends Exception
{
    protected $type;
    protected $type_value;
    protected $ci;

    public function __construct( $message = null, $type='', $type_value='' )
    {
        $this->type = $type;
        $this->type_value = $type_value;
        $this->ci = $CI =& get_instance();
        parent::__construct($message);
    }

    public function getType()
    {
        return GetStringValue($this->type);
    }
    public function getTypeValue()
    {
        return GetStringValue($this->type_value);
    }

    public function getAdditionalMessage($company_id)
    {
        $type = $this->getType();
        if ( $type === 'column' ) return $this->_getColumnMappingMessage($company_id);
        if ( $type === 'plan_code' ) return $this->_getPlanCodeMessage($company_id);
        if ( $type === 'option_code' ) return $this->_getOptionCodeMessage($company_id);
        if ( $type === 'tier_code' ) return $this->_getTierCodeMessage($company_id);
        return "";
    }

    private function _getColumnMappingMessage($company_id)
    {
        $column_code = $this->getTypeValue();

        // If we have a column code, add more information to this message to help tracking it down.
        $additional_message = "";
        if ( $column_code !== '' )
        {
            $column_data = $this->ci->Mapping_model->get_mapping_column_by_name($column_code, $company_id);
            $column_display = GetArrayStringValue("display", $column_data);
            $additional_message = "Please ensure the column '{$column_display}' is mapped and contains valid data to receive this report.";
        }
        return $additional_message;
    }
    private function _getPlanCodeMessage($company_id)
    {
        // GOAL: Unsupported product type Basic AD&D on plan type code A1.
        $plantype_code = $this->getTypeValue();

        // PLANTYPE
        // How do we display the code in the application?
        $plantype_display = $this->ci->Reporting_model->get_plantype_description_by_code($plantype_code);
        if ( $plantype_display === '' ) $plantype_display = $plantype_code;

        // USER ELECTIONS
        // How does the user list
        $list = "";
        $user_descriptions = $this->ci->Reporting_model->list_distinct_plantypes($company_id, $plantype_code);
        foreach($user_descriptions as $user_description)
        {
            $list .= $user_description . ', ';
        }
        $list = fLeftBack($list, ', ', '');

        if ( GetStringValue($plantype_code) === '' ) return "Unsupported product type.";
        if ( $list === '' ) return "Unsupported product type {$plantype_display} on a plan type code.";
        return "Unsupported product type {$plantype_display} on plan type code {$list}.";
    }
    private function _getOptionCodeMessage($company_id)
    {
        // GOAL: Unsupported plan code XXX on plan type xx, xx, xx.

        // PLAN
        $plan = $this->getTypeValue();
        $normalized = trim(strtoupper($plan));

        // USER ELECTIONS
        // How does the user list
        $list = "";
        $user_descriptions = $this->ci->Reporting_model->list_distinct_plantypes_for_plan($company_id, $normalized);
        foreach($user_descriptions as $user_description)
        {
            $list .= $user_description . ', ';
        }
        $list = fLeftBack($list, ', ', '');

        if ( GetStringValue($plan) === '' ) return "Unsupported plan.";
        if ( $list === '' ) return "Unsupported plan {$plan}.";
        return "Unsupported plan {$plan} on plan type {$list}.";
    }
    private function _getTierCodeMessage($company_id)
    {
        // GOAL: Unsupported coverage tier XXX on plan xx, xx, xx.

        // COVERAGE TIER
        $coverage_tier = $this->getTypeValue();
        $normalized = trim(strtoupper($coverage_tier));

        // USER ELECTIONS
        // How does the user list
        $list = "";
        $user_descriptions = $this->ci->Reporting_model->list_distinct_plans_for_tier($company_id, $normalized);
        foreach($user_descriptions as $user_description)
        {
            $list .= $user_description . ', ';
        }
        $list = fLeftBack($list, ', ', '');

        if ( GetStringValue($coverage_tier) === '' ) return "Unsupported coverage tier.";
        if ( $list === '' ) return "Unsupported coverage tier {$coverage_tier}.";
        return "Unsupported coverage tier {$coverage_tier} on plan {$list}.";
    }
}