<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tobacco_user extends ColumnValidation_Boolean {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);
    }


    function looks_like($input) {

        // Allow this input to be the empty string.
        if ( getStringValue($input) == "" ) return true;

        // Allow any of the various YES/NO inputs.
        $result = parent::looks_like($input);
        return $result;

    }
    function normalize($input) {

        $input = $this->apply_default_value( $input );
        if ( $input == "" ) return "";      // Allow this input to be the empty string.
        if ( ! $this->looks_like($input) ) return false;

        // If input was not empty, then we will normalize as if it was date.
        return parent::normalize($input);

    }
    function apply_default_value( $input ) {

        // apply_default_value
        //
        // If the input is the empty string, this function will turn that
        // into a default value, if desired given the object type.
        // ------------------------------------------------------------------
        if ( GetStringValue($input) === '' )
        {
            return "N";
        }
        return $input;
    }






}
