<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class ColumnValidation_SSN extends ColumnValidation {



    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);
        $this->validation_error_type = "invalid_ssn";
    }
    function looks_like($input) {

        // If validation is NOT required and we got the empty string, that is
        // okay.  Return that it looks like a SSN we can live with.
        if ( getStringValue($input) == "" && ! $this->validation_required ) return true;

        // Okay, we got something, so let's validate it.  It might be
        // encrypted so decrypt it first just to be safe.
        $input = A2PDecryptString($input, $this->encryption_key);

        // SSN values do not have alpha characters.
        if (preg_match('/[A-Za-z]/', $input)) return false;

        // looks like a social security number.
        $pattern = '#\b[0-9]{3}-[0-9]{2}-[0-9]{4}\b#';
        if ( preg_match($pattern, $input) ) return true;

        // Allow an SSN without the dashes.
        if ( strlen($input) === 9 ) return true;

        // Allow for SSN values that are only 7 or 8 digits long.
        // These are actually valid if they were preceded with zeros.
        $input = StripNonNumeric($input);
        if ( strlen($input) === 7 || strlen($input) === 8 ) return true;



        // BLANK OUT SSN VALUE
        // In some cases, we will blank out the SSN value provided
        if ( strtolower(get_called_class() ) === 'ssn')
        {
            // The SSN column, also known as "Person SSN" in the UI, may turn
            // into a blank value if it is less than 7 digits long.  However,
            // if the valiation flag is set, this is not allowed.
            if ( strlen($input) < 7 && ! $this->validation_required ) return true;
        }
        if ( strtolower(get_called_class() ) === 'employee_ssn')
        {
            // The Employee SSN column may turn into a blank value if it is
            // exaclty one digit.  However, if the validation flag is set, this
            // is not allowed and will be invalid.
            if ( strlen($input) === 1 && ! $this->validation_required ) return true;
        }



        return false;

    }
    function normalize($input) {

        // If we were handed an encrypted string, decrypt it before we normalize it.
        $encrypted = false;
        if ( IsEncryptedString($input) ) $encrypted = true;
        $input = A2PDecryptString($input, $this->encryption_key);

        // Does this look like an ssn?
        $input = $this->apply_default_value( $input );
        if ( ! $this->looks_like($input) ) return false;

        // Normalize it.
        $input = StripNonNumeric($input);

        // If the SSN is 7 or 8 digits in length, padd it with zeros.
        if ( strlen($input) === 7 || strlen($input) === 8 )
        {
            $input = str_pad($input, '9', '0', STR_PAD_LEFT);
        }

        // BLANK OUT SSN
        if ( strtolower(get_called_class() ) === 'ssn')
        {
            // The SSN ( Person SSN ) column will be blank out if it
            // is less than seven digits.
            if ( strlen($input) < 7 ) $input = "";
        }
        if ( strtolower(get_called_class() ) === 'employee_ssn')
        {
            // The EmployeeSSN column will be blanked out if it
            // exactly one digit.
            if ( strlen($input) === 1 ) $input = "";
        }




        // If it was encrypted coming in, encrypt it going out.
        if ( $encrypted ) $input = A2PEncryptString($input, $this->encryption_key, true);    // Force identical SSN's to encrypt the same way.

        return $input;

    }

}
