<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class original_effective_date extends ColumnValidation_Date {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);

    }

    function looks_like($input)
    {

        // Empty inputs are allowed.
        if ( getStringValue($input) == "" ) return true;

        $result = parent::looks_like($input);
        return $result;

    }

    function normalize($input) {

        if ( GetStringValue($input) === '' ) return null;
        return parent::normalize($input);
    }

}
