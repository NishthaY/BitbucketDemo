<?php
    if ( ! isset($row) ) $row = "";
    if ( ! isset($column) ) $column = "";
    if ( ! isset($details) ) $details = "";
    if ( ! isset($company_id) ) $company_id = "";

    $error_message = getArrayStringValue("ErrorMessage", $details);
    $short_code = getArrayStringValue("ShortCode", $details);
?>
<div class="alert alert-danger" role="alert"><?=$error_message?></div>
<?php
    switch(strtoupper($short_code) ) {
        case "INVALID_DATE":
            echo RenderViewAsString("correct/correct_data_cell_invalid_date");
            break;
        case "INVALID_BOOLEAN":
            echo RenderViewAsString("correct/correct_data_cell_invalid_boolean");
            break;
        case "INVALID_GENDER":
            echo RenderViewAsString("correct/correct_data_cell_invalid_gender");
            break;
        case "INVALID_MONEY":
            echo RenderViewAsString("correct/correct_data_cell_invalid_money");
            break;
        case "INVALID_SSN":
            echo RenderViewAsString("correct/correct_data_cell_invalid_ssn", array('details' => $details));
            break;
        case "INVALID_EMPLOYMENT_ACTIVE":
            echo RenderViewAsString("correct/correct_data_cell_invalid_employment_active");
            break;
        case "INVALID_EMAIL":
            echo RenderViewAsString("correct/correct_data_cell_invalid_email");
            break;
        case "INVALID_PHONE":
            echo RenderViewAsString("correct/correct_data_cell_invalid_phone");
            break;
        case "INVALID_STATE":
            // which view should we show based on feature state.
            $view = "correct/correct_data_cell_invalid_state";
            $canada_support = $this->Feature_model->is_feature_enabled($company_id, 'CANADA_PROVINCE');
            if ( $canada_support ) $view = "correct/correct_data_cell_invalid_state_CA";

            echo RenderViewAsString($view, array('company_id' => $company_id));
            break;
        default:
            echo RenderViewAsString("correct/correct_data_cell_required");
            break;
    }
?>
