<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Eid extends ColumnValidation {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);

        //$this->validation_required = true;  // Employee ID may not be blank.

    }

    function looks_like($input) {

        // Empty inputs are allowed.
        if ( getStringValue($input) == "" ) return true;

        $input = A2PDecryptString($input, $this->encryption_key);
        if ( StartsWith($input, EUID_TAG) ) return false;

        return true;

    }
    function normalize($input) {

        // If we were handed an encrypted string, decrypt it before we normalize it.
        $encrypted = false;
        if ( IsEncryptedString($input) ) $encrypted = true;
        $input = A2PDecryptString($input, $this->encryption_key);

        // Does this look like an employee id?
        $input = $this->apply_default_value( $input );
        if ( ! $this->looks_like($input) ) return false;

        // NORMALIZE INPUT
        // If the eid contains only numeric characters, remove leading zeros
        // by turning it into an integer and then back to a string.
        if ( $input !== '' && StripNonNumeric($input) === $input )
        {
            $input = GetStringValue(intval($input));
        }

        // If it was encrypted coming in, encrypt it going out.
        if ( $encrypted ) $input = A2PEncryptString($input, $this->encryption_key, true);    // Force identical SSN's to encrypt the same way.

        return $input;

    }

}
