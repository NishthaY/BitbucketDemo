<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Employment_end extends ColumnValidation_Date {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);

    }

    function looks_like($input) {

        // Allow this date input to be the empty string.
        if ( getStringValue($input) == "" ) return true;

        $result = parent::looks_like($input);
        return $result;

    }

    function normalize($input) {

        // Allow nulls.
        $input = $this->apply_default_value( $input );
        if ( $input == "" ) return "";

        // If input was not empty, then we will normalize as if it was date.
        return parent::normalize($input);

    }
}
