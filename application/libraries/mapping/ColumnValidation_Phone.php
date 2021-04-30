<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class ColumnValidation_Phone extends ColumnValidation {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);
        $this->validation_error_type = "invalid_phone";
    }

    function looks_like($input)
    {
        // Allow the data to be blank.
        if ( getStringValue($input) == "" ) return true;

        // The input might be encrypted.
        $input = A2PDecryptString($input, $this->encryption_key);

        // The input must contain 10 digits, 11 digits if the first digit is a 1.
        $input = StripNonNumeric($input);
        if ( strlen($input) == 11 && StartsWith($input, "1") ) return TRUE;
        if ( strlen($input) == 10 ) return TRUE;

        return FALSE;

    }
    function normalize($input) {

        // If we were handed an encrypted string, decrypt it before we normalize it.
        $encrypted = false;
        if ( IsEncryptedString($input) ) $encrypted = true;
        $input = A2PDecryptString($input, $this->encryption_key);

        // Default the input, if needed.
        $input = parent::normalize($input);
        if ( $input === FALSE ) return FALSE;

        // Does this pass the looks like test?
        if ( ! $this->looks_like($input ) ) return FALSE;

        // Apply our normalization rules.
        $input = StripNonNumeric($input);
        if ( strlen($input) == 11 && StartsWith($input, "1") ) $input = substr($input, 1);
        $area = substr($input, 0, 3);
        $prefix = substr($input, 3, 3);
        $number = substr($input, 6, 4);

        // Here is what it should look like.
        $normalized = "{$area}{$prefix}{$number}";

        // If it was encrypted coming in, encrypt it going out.
        if ( $encrypted ) $normalized = A2PEncryptString($normalized, $this->encryption_key, true);

        return $normalized;

    }
}
