<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class ColumnValidation_Gender extends ColumnValidation {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);
        $this->validation_error_type = "invalid_gender";
        $this->validation_required = false;                 // The column is required, but the field value is not.
    }
    function looks_like($input) {

        // Allow blank if validation is not required.
        if ( getStringValue($input) == "" && ! $this->validation_required ) return true;

        $input = strtoupper($input);

        if ( $input == "M" )            return true;
        if ( $input == "MALE" )         return true;
        if ( $input == "F" )            return true;
        if ( $input == "FEMALE" )       return true;
        if ( $input == "U" )            return true;    // U
        if ( $input == "UNIVERSAL" )    return true;    // universal
        if ( $input == "UNKNOWN" )      return true;    // unknown
        if ( $input == "O" )            return true;    // O
        if ( $input == "OTHER" )        return true;    // other
        if ( $input == "N" )            return true;    // n
        if ( $input == "NA" )           return true;    // na
        if ( $input == "N/A" )          return true;    // n/a
        if ( $input == "UNSPECIFIED")   return true;    // unspecified

        return false;

    }
    function normalize($input) {

        // Allow blank if validation is not required.
        if ( getStringValue($input) == "" && ! $this->validation_required ) return "";

        $input = $this->apply_default_value( $input );
        if ( ! $this->looks_like($input) ) return false;

        $input = strtoupper($input);
        if ( $input == "M" )            return "m";
        if ( $input == "MALE" )         return "m";
        if ( $input == "F" )            return "f";
        if ( $input == "FEMALE" )       return "f";
        if ( $input == "U" )            return "u";
        if ( $input == "UNIVERSAL" )    return "u";
        if ( $input == "UNKNOWN" )      return "u";
        if ( $input == "O" )            return "u";
        if ( $input == "OTHER" )        return "u";
        if ( $input == "N" )            return "u";
        if ( $input == "NA" )           return "u";
        if ( $input == "N/A" )          return "u";
        if ( $input == "UNSPECIFIED")   return "u";

        return false;

    }

}
