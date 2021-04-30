<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class ColumnValidation_State extends ColumnValidation {

    private $_USLookup;
    private $_CALookup;
    private $_canada_enabled;

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);
        $this->validation_error_type = "invalid_state";

        // Cash a copy of our mapping lookup so we only
        // go to the database once per object.
        $_USLookup = null;
        $_CALookup = null;

        // Are Canada state codes enabled?
        $this->_canada_enabled = false;
        if ( GetStringValue($this->company_id) !== '' )
        {
            $CI = & get_instance();
            $this->_canada_enabled = $CI->Feature_model->is_feature_enabled($this->company_id, 'CANADA_PROVINCE');
        }
    }

    function looks_like($input)
    {
        // Allow the data to be blank.
        if (getStringValue($input) == "") return true;

        // The input might be encrypted.
        $input = A2PDecryptString($input, $this->encryption_key);

        if ($this->_USLookup === null) $this->_USLookup = GetMappedObjectLookup('USAStates');
        if ($this->_CALookup === null) $this->_CALookup = GetMappedObjectLookup('CAProvinces');

        $mapped = GetArrayStringValue($input, $this->_USLookup);
        if ($mapped !== '') return TRUE;

        if ($this->_canada_enabled)
        {
            $mapped = GetArrayStringValue($input, $this->_CALookup);
            if ( $mapped !== '' ) return TRUE;
        }


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
        if ( $this->_USLookup === null ) $this->_USLookup = GetMappedObjectLookup('USAStates');
        if ( $this->_CALookup === null ) $this->_CALookup = GetMappedObjectLookup('CAProvinces');

        $normalized = GetArrayStringValue($input, $this->_USLookup);

        if ( $this->_canada_enabled )
        {
            if ( $normalized === '' ) $normalized = GetArrayStringValue($input, $this->_CALookup);
        }

        // If it was encrypted coming in, encrypt it going out.
        if ( $encrypted ) $normalized = A2PEncryptString($normalized, $this->encryption_key, true);

        return $normalized;

    }
}
