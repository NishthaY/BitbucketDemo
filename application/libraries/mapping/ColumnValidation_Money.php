<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class ColumnValidation_Money extends ColumnValidation {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);
        $this->validation_error_type = "invalid_money";
    }

    function looks_like($input) {

        $input = replaceFor($input, "$", "");
        $input = replaceFor($input, ",", "");

        // Negative numbers are not allowed.
        if ( strpos($input, "(") !== FALSE ) return false;
        if ( strpos($input, ")") !== FALSE ) return false;
        if ( strpos($input, "-") !== FALSE ) return false;

        // If we have a precision beyond two digits, round them up.
        if ( strlen(fRight($input, ".")) > 2 ) $input = getStringValue(round(floatval($input), 2, PHP_ROUND_HALF_UP));

        // If we do not have two-digit fraction, add it.
        $input = getStringValue($input);
        if ( strpos($input, "." ) === FALSE ) $input .= ".00";

        // If we have no fraction, but there is a point operator, make the fraction two-digit
        if ( strpos($input, ".") !== FALSE && strpos($input, ".") == strlen($input) - 1  ) $input .= "00";

        // If we have a one-digit fraction, make it two-digit.
        if ( strpos($input, ".") !== FALSE && strlen(fRight($input, ".")) == 1 ) $input .= "0";

        // If we have no dollar value, add it.
        if ( strpos($input, ".") == 0 ) $input = "0" . $input;

        // http://stackoverflow.com/questions/354044/what-is-the-best-u-s-currency-regex
        // Currency amount (cents optional) Optional thousands separators; optional two-digit fraction
        if (preg_match("/^[+-]?[0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]{2})?$/", $input ) ) return true;

        return false;

    }
    function normalize($input) {
        $input = $this->apply_default_value( $input );
        if ( ! $this->looks_like($input) ) return false;

        $input = replaceFor($input, "$", "");
        $input = replaceFor($input, ",", "");

        // Remove any negative indicators.
        if ( strpos($input, "(") !== FALSE ) return false;
        if ( strpos($input, ")") !== FALSE ) return false;
        if ( strpos($input, "-") !== FALSE ) return false;

        // If we have a precision beyond two digits, round them up.
        if ( strlen(fRight($input, ".")) > 2 ) $input = getStringValue(round(floatval($input), 2, PHP_ROUND_HALF_UP));

        // If we do not have two-digit fraction, add it.
        $input = getStringValue($input);
        if ( strpos($input, "." ) === FALSE ) $input .= ".00";

        // If we have no fraction, but there is a point operator, make the fraction two-digit
        if ( strpos($input, ".") !== FALSE && strpos($input, ".") == strlen($input) - 1  ) $input .= "00";

        // If we have a one-digit fraction, make it two-digit.
        if ( strpos($input, ".") !== FALSE && strlen(fRight($input, ".")) == 1 ) $input .= "0";

        // If we have no dollar value, add it.
        if ( strpos($input, ".") == 0 ) $input = "0" . $input;

        return $input;

    }

}
