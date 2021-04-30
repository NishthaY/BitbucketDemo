<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class coverage_end_date extends ColumnValidation_Date {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);

        // This column is required in the UI.  That means the constructor
        // will set validation_required to true.  However, we just need to
        // know the column mapping.  The data may not be required so we will
        // turn that off here.
        $this->validation_required = false;

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
