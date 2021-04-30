<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Company extends ColumnValidation {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);

    }

    function looks_like($input)
    {
        // If you are running this function, you can assume the column
        // was mapped.  In that case, when mapped, the empty string is
        // not allowed.

        // It is acceptable to not map this column, but if you do it
        // has to be populated.
        if ( trim(GetStringValue($input)) === '' ) return FALSE;

        return TRUE;

    }
    function normalize($input)
    {
        // If we were handed an encrypted string, decrypt it before we normalize it.
        $encrypted = false;
        if ( IsEncryptedString($input) ) $encrypted = true;
        $input = A2PDecryptString($input, $this->encryption_key);

        // Normalize the data using the parent rules.
        $input = parent::normalize($input);
        if ( $input === FALSE ) return FALSE;

        // Okay, take what we have.  If this does not look like a company
        // then we bail.
        if ( ! $this->looks_like($input) ) return FALSE;

        return TRUE;

    }

}
