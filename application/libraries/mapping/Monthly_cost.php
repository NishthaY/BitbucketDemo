<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Monthly_cost extends ColumnValidation_Money {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);

    }

    function apply_default_value( $input ) {
        if ( getStringValue($input) == "" ) $input = "0.00";
        return getStringValue($input);
    }

}
