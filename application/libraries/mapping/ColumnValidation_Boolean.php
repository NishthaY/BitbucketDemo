<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class ColumnValidation_Boolean extends ColumnValidation {


    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);
        $this->validation_error_type = "invalid_boolean";
    }
    function looks_like($input) {

        $input = strtoupper($input);

        if ( $input == "Y" )        return true;
        if ( $input == "YES" )      return true;
        if ( $input == "T" )        return true;
        if ( $input == "TRUE" )     return true;
        if ( $input == "N" )        return true;
        if ( $input == "NO" )       return true;
        if ( $input == "F" )        return true;
        if ( $input == "FALSE" )    return true;
        if ( $input == "1" )        return true;
        if ( $input == "0" )        return true;

        return false;

    }
    function normalize($input) {
        $input = $this->apply_default_value( $input );
        if ( ! $this->looks_like($input) ) return false;

        $input = strtoupper($input);

        if ( $input == "Y" )        return "t";
        if ( $input == "YES" )      return "t";
        if ( $input == "T" )        return "t";
        if ( $input == "TRUE" )     return "t";
        if ( $input == "N" )        return "f";
        if ( $input == "NO" )       return "f";
        if ( $input == "F" )        return "f";
        if ( $input == "FALSE" )    return "f";
        if ( $input == "1" )        return "t";
        if ( $input == "0" )        return "f";

        return false;

    }

}
