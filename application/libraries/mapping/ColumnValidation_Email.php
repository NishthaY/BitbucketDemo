<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class ColumnValidation_Email extends ColumnValidation {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);
        $this->validation_error_type = "invalid_email";
    }

    function looks_like($input) {

        if ( getStringValue($input) == "" ) return true;
        return filter_var($input, FILTER_VALIDATE_EMAIL);

    }
    function normalize($input) {

        // Allow the empty string.
        $input = parent::normalize($input);
        if ( $input === FALSE ) return FALSE;

        // Must look like an email address, else report error.
        if ( ! $this->looks_like($input ) ) return FALSE;
        
        return $input;

    }
}
