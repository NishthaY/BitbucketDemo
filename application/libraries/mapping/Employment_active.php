<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Employment_active extends ColumnValidation_Boolean {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);
        $this->validation_error_type = "invalid_employment_active";

    }
    function looks_like($input) {

        // Allow this input to be the empty string.
        if ( getStringValue($input) == "" ) return true;

        $input = strtoupper($input);

        // Allow special cases for this field.
        if ( $input === 'ACTIVE' ) return true;
        if ( $input === 'NON-ACTIVE' ) return true;
        if ( $input === 'NONACTIVE' ) return true;
        if ( $input === 'NOT ACTIVE' ) return true;
        if ( $input === 'INACTIVE' ) return true;
        if ( $input === 'TERMINATED' ) return true;
        if ( $input === 'A' ) return true;
        if ( $input === 'T' ) return true;
        if ( $input === 'S' ) return true;

        // Allow any of the various YES/NO inputs.
        $result = parent::looks_like($input);
        return $result;

    }
    function normalize($input) {

        $input = $this->apply_default_value( $input );
        if ( $input == "" ) return "";      // Allow this input to be the empty string.
        if ( ! $this->looks_like($input) ) return false;

        $input = strtoupper($input);

        if ( $input === 'ACTIVE' ) return 't';
        if ( $input === 'NON-ACTIVE' ) return 'f';
        if ( $input === 'NONACTIVE' ) return 'f';
        if ( $input === 'NOT ACTIVE' ) return 'f';
        if ( $input === 'INACTIVE' ) return 'f';
        if ( $input === 'TERMINATED' ) return 'f';
        if ( $input === 'A' ) return 't';
        if ( $input === 'T' ) return 'f';
        if ( $input === 'S' ) return 'f';


        // If input was not empty, then we will normalize as if it was date.
        return parent::normalize($input);

    }
}
