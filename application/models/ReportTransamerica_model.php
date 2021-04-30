<?php

class ReportTransamerica_model extends CI_Model {

    protected $company_id;

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

    /**
     * mandatory_check
     *
     * This function will throw an exception if the input string is empty.
     *
     * @param $str
     * @param $column_code
     * @return string
     * @throws Exception
     */
    protected function mandatory_check($str, $column_code )
    {
        try
        {
            // This is a string, right?
            $str = GetStringValue($str);

            // String is empty and we have a column code.  This implies that this piece
            // of data is mandatory and may not be blank.  Throw an exception to stop execution.
            if ( GetStringValue($column_code) !== '' && $str === '' ) throw new Exception("");

        }
        catch ( Exception $e )
        {
            // Convert this into a report exception so we can pass back the column code.
            throw new ReportException( $e->getMessage(), 'column', $column_code );
        }
        return $str;
    }


    /**
     * normalizeMandatoryString
     *
     * Format a string.  If the input is empty, issues a specialized error message
     * so we know what column contained the bad data.
     *
     * @param $str
     * @param $length
     * @param $encryption_key
     * @param $column_code
     * @return bool|string
     */
    protected function normalizeMandatoryString($str, $length, $encryption_key, $column_code)
    {
        $this->mandatory_check($str, $column_code);
        return $this->normalizeString($str, $length, $encryption_key, STR_PAD_RIGHT, ' ', $column_code);
    }


    /**
     * normalizeEmployeeNumber
     *
     * Make sure we don't show EmployeeId values that we generated because
     * the user did not supply the data.
     *
     * @param $str
     * @param $length
     * @param null $encryption_key
     * @param int $type
     * @param string $pad_value
     * @return string
     */
    protected function normalizeEmployeeNumber($str, $length, $encryption_key=null, $type=STR_PAD_RIGHT, $pad_value=' ')
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key) !== '' )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        // Check to see if the employee id starts with our auto-generated tag.
        // If it was, we don't want to show that on the report.
        if ( StartsWith($str, EUID_TAG) ) $str = "";


        // Treat this like a string and normalize it.
        return $this->normalizeString($str, $length, $encryption_key, $type, $pad_value);
    }

    /**
     * normalizeString
     *
     * Format a string for a Transamerica EDI file.
     *
     * @param $str
     * @param $length
     * @param null $encryption_key
     * @param int $type
     * @param string $pad_value
     * @param string $column_code
     * @return bool|string
     * @throws Exception
     */
    protected function normalizeString($str, $length, $encryption_key=null, $type=STR_PAD_RIGHT, $pad_value=' ')
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        $encrypted = false;
        if ( GetStringValue($encryption_key) !== '' )
        {
            $encrypted = IsEncryptedString($str);
            if ( $encrypted )
            {
                $str = A2PDecryptString($str, $encryption_key);
            }
        }

        // Manage the string length.
        if ( strlen($str) > $length )
        {
            // String is too long.  Cut it.
            $str = substr($str, 0, $length);
        }
        else
        {
            // String is too short.  Pad it.
            $str = str_pad($str, $length, $pad_value, $type);
        }

        // Uppercase all strings.
        $str = strtoupper($str);

        return $str;
    }

    /**
     * normalizeMandatoryTier
     *
     * Ths function will convert an A2P coverage tier into the codes
     * expected on a Transamerica EDI file in the "Tier" column.
     *
     * @param $str
     * @param $length
     * @param $encryption_key
     * @param $column_code
     * @return bool|string
     * @throws Exception
     */
    function normalizeMandatoryTier($str, $length, $encryption_key, $column_code)
    {
        // Tier is mandatory.
        $this->mandatory_check($str, $column_code);

        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key) !== '' )
        {
            $encrypted = IsEncryptedString($str);
            if ( $encrypted ) $str = A2PDecryptString($str, $encryption_key);
        }

        // Uppercase everything.
        $research = strtoupper($str);

        // Remove all but characters.
        $research = preg_replace( "/[^a-z]/i", "", $research );


        if ( $research === 'EO' ) $research = 'EO';
        else if ( $research === 'EMPLOYEEONLY' ) $research = 'EO';
        else if ( $research === 'EMPLOYEE' ) $research = 'EO';
        else if ( $research === 'ES' ) $research = 'ES';
        else if ( $research === 'EMPLOYEESPOUSE' ) $research = 'ES';
        else if ( $research === 'EC' ) $research = 'EC';
        else if ( $research === 'EMPLOYEECHILDREN' ) $research = 'EC';
        else if ( $research === 'FM' ) $research = 'FM';
        else if ( $research === 'FA' ) $research = 'FM';
        else if ( $research === 'FAMILY' ) $research = 'FM';
        else if ( $research === 'FAMILYCOVERAGE' ) $research = 'FM';
        else if ( $research === 'SC' ) $research = 'SC';
        else if ( $research === 'SPOUSECHILDREN' ) $research = 'SC';
        else if ( $research === 'CH' ) $research = 'CH';
        else if ( $research === 'CHILDREN' ) $research = 'CH';
        else if ( $research === 'CHILDRENONLY' ) $research = 'CH';
        else if ( $research === 'SP' ) $research = 'SP';
        else if ( $research === 'SPOUSE' ) $research = 'SP';
        else if ( $research === 'SPOUSEONLY' ) $research = 'SP';
        else $research = '';

        if ( $research === '' )
        {
            //return $this->normalizeString('', $length, $encryption_key, STR_PAD_LEFT, 'X');
            throw new ReportException("Unsupported tier [{$str}]", 'tier_code', $str);
        }

        $research = $this->normalizeString($research, $length);
        $str = $research;

        return $str;

    }

    /**
     * normalizeMandatoryOption
     *
     * Ths function will convert an A2P plan into the codes
     * expected on a Transamerica EDI file in the "Option" column.
     *
     * @param $str
     * @param $length
     * @param $encryption_key
     * @param $column_code
     * @return bool|string
     */
    protected function normalizeMandatoryOption($str, $length, $encryption_key, $column_code)
    {
        $this->mandatory_check($str, $column_code);
        return $this->normalizeOption($str, $length, $encryption_key);
    }

    /**
     * normalizeOption
     *
     * This function will convert an A2P plan into the codes
     * expected on a Transamerica EDI file in the "Option" column.
     *
     * @param $str
     * @param $length
     * @param $encryption_key
     * @return bool|string
     * @throws Exception
     */
    protected function normalizeOption($str, $length, $encryption_key)
    {

        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key) !== '' )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        // Convert the option to the desired output for the file.
        $output = "";
        $research = trim($str);
        $research = replaceFor($research, " ", "");
        $research = strtoupper($research);
        
        if ( $research === 'PLAN1' ) $output = "00";
        if ( $research === 'PLAN2' ) $output = "01";
        if ( $research === 'PLAN3' ) $output = "02";
        if ( $research === 'PLAN4' ) $output = "03";
        if ( $research === 'PLAN5' ) $output = "04";
        if ( $research === 'PLAN6' ) $output = "05";
        if ( $research === 'PLAN7' ) $output = "06";
        if ( $research === 'PLAN8' ) $output = "07";
        if ( $research === 'PLAN9' ) $output = "08";
        if ( $research === 'PLAN10' ) $output = "09";

        // The option could not be mapped.
        if ( $output === '' ) throw new ReportException("Unsupported option [{$str}].", 'option_code', $str);

        return $output;
    }

    /**
     * normalizeMandatoryProductType
     *
     * This function will convert an A2P plan type into the codes
     * expected on a Transamerica EDI file in the "ProductType" column.

     * @param $str
     * @param $length
     * @param $encryption_key
     * @param $column_code
     * @return bool|string
     */
    protected function normalizeMandatoryProductType($str, $length, $encryption_key, $column_code)
    {
        $this->mandatory_check($str, $column_code);
        return $this->normalizeProductType($str, $length, $encryption_key);
    }

    /**
     * normalizeProductType
     *
     * This function will convert an A2P plan type into the codes
     * expected on a Transamerica EDI file in the "ProductType" column.
     *
     * @param $str
     * @param $length
     * @param $encryption_key
     * @return bool|string
     * @throws Exception
     */
    protected function normalizeProductType($str, $length, $encryption_key)
    {

        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key) !== '' )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        $output = "";
        if ( $str === 'hospital_indemity' ) $output = "M";
        else if ( $str === 'hospital_indemity_aso' ) $output = "M";
        else if ( $str === 'hospital_indemity_stoploss' ) $output = "M";
        else if ( $str === 'critical_illness' ) $output = "I";
        else if ( $str === 'critical_illness_aso' ) $output = "I";
        else if ( $str === 'critical_illness_stoploss' ) $output = "I";
        else if ( $str === 'accident' ) $output = "A";
        else if ( $str === 'accident_aso' ) $output = "A";
        else if ( $str === 'accident_stoploss' ) $output = "A";
        else if ( $str === 'cancer' ) $output = "C";
        else if ( $str === 'cancer_aso' ) $output = "C";
        else if ( $str === 'cancer_stoploss' ) $output = "C";


        //Unsupported product type Basic AD&D on plan type code A1

        if ( $output === '' ) throw new ReportException("Unsupported product type [{$str}]", 'plan_code', $str);

        $research = $this->normalizeString($output, $length, $encryption_key);
        return $research;

    }

    /**
     * normalizeMandatoryGender
     *
     * Convert the A2P Gender code into the cooresponding Transamerica
     * gender code for EDI files.
     *
     * @param $str
     * @param $length
     * @param $encryption_key
     * @param $column_code
     * @return string
     */
    protected function normalizeMandatoryGender($str, $length, $encryption_key, $column_code)
    {
        $this->mandatory_check($str, $column_code);
        return $this->normalizeGender($str, $length, $encryption_key);
    }

    /**
     * normalizeGender
     *
     * Convert the A2P Gender code into the corresponding Transamerica
     * gender code for EDI files.
     *
     * Per Transamerica, if not recognized we will return "M" for male.
     *
     * @param $str
     * @param $length
     * @param $encryption_key
     * @return string
     */
    protected function normalizeGender($str, $length, $encryption_key)
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key) !== '' )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        $str = strtoupper($str);
        if ( $str === 'F' ) $str = 'F';
        else if ( $str === 'FEMALE' ) $str = 'FEMALE';
        else $str = 'M';

        $str = $this->normalizeString($str, $length);
        return $str;
    }

    /**
     * normalizeMandatoryDate
     *
     * Return the date for a Transamerica report.  This function
     * ensures the input is not blank.
     * @param $str
     * @param $encryption_key
     * @param $column_code
     * @return bool|false|string
     */
    protected function normalizeMandatoryDate($str, $encryption_key, $column_code)
    {
        $this->mandatory_check($str, $column_code);
        return $this->normalizeDate($str, $encryption_key);
    }

    /**
     * normalizeDate
     *
     * Return a 10 character date in the format of MM/DD/YYYY for a Transamerica
     * report.
     *
     * @param $str
     * @param $encryption_key
     * @return bool|false|string
     */
    protected function normalizeDate($str, $encryption_key)
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key) !== '' )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        if ( $str !== '' )
        {
            $timestamp = strtotime($str);
            $str = date('m/d/Y', $timestamp);
        }
        $str = $this->normalizeString($str, 10);
        return $str;
    }

    /**
     * normalizeMandatoryMoney
     *
     * Return a Transamerica money formatted string.  Ensure the input is not blank.
     *
     * @param $str
     * @param $length
     * @param $encryption_key
     * @param $column_code
     * @return string
     */
    protected function normalizeMandatoryMoney($str, $length, $encryption_key, $column_code)
    {
        $this->mandatory_check($str, $column_code);
        return $this->normalizeMoney($str, $length, $encryption_key);
    }

    /**
     * normalizeMoney
     *
     * Return a Transamerica money value for an EDI report.  This money value
     * will remove all commas and dollar indicators.  It will contain a decimal
     * point and support up to two decimal places.  The left will be padded
     * with zeros.
     *
     * Example: 0006924.70  ( length = 10 )
     *
     * @param $str
     * @param $length
     * @param $encryption_key
     * @return string
     */
    protected function normalizeMoney($str, $length, $encryption_key)
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key) !== '' )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }


        if ( strpos($str, '.') === FALSE ) $str = $str . ".00";

        // Cents
        $right = fRightBack($str, ".");
        $right = StripNonNumeric($right);
        $right = $this->normalizeString($right, 2, null, STR_PAD_RIGHT, '0');

        // Dollars
        $left = fLeftBack($str, ".");
        $left = StripNonNumeric($left);
        $left = $this->normalizeString($left, $length - 3, null, STR_PAD_LEFT, '0');

        $str = "{$left}.{$right}";
        return $str;
    }

    /**
     * normalizeMandatoryState
     *
     * Return a state code.  If the input is blank, error.
     *
     * @param $str
     * @param $length
     * @param $encryption_key
     * @param string $column_code
     * @return bool|string
     */
    protected function normalizeMandatoryState($str, $length, $encryption_key, $column_code="" )
    {
        $this->mandatory_check($str, $column_code);
        return $this->normalizeState($str, $length, $encryption_key);
    }

    /**
     * normalizeState
     *
     * Return a USA state code based on the input provided.  Will throw an
     * exception if we can't recognize the state.
     *
     * @param $str
     * @param $length
     * @param $encryption_key
     * @return bool|string
     * @throws Exception
     */
    protected function normalizeState($str, $length, $encryption_key)
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key) !== '' )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        // Map the state to a state/province code.
        $str = trim($str);
        $state_code =   GetMappedObject('USAStates', $str, false);

        // If we have no state code at this point, check to see if other countries are
        // enabled.  If they are, look for their state codes.
        if ( $state_code === '' )
        {
            $enabled = $this->Feature_model->is_feature_enabled($this->company_id, 'CANADA_PROVINCE');
            if ( $enabled ) $state_code =   GetMappedObject('CAProvinces', $str, false);
        }

        if ( $state_code === '' ) throw new ReportException("Unsupported State/Province. {$str}");
    
        $str = $this->normalizeString($state_code, $length);
        return $str;
    }

    /**
     * normalizeMandatoryZip
     *
     * Return a 5 digit zip code.  Error if the input is empty.
     *
     * @param $str
     * @param $encryption_key
     * @param $column_code
     * @return bool|string
     */
    protected function normalizeMandatoryZip($str, $encryption_key, $column_code)
    {
        $this->mandatory_check($str, $column_code);
        return $this->normalizeZip($str, $encryption_key);
    }

    /**
     * normalizeZip
     *
     * Convert the input into a 5 digit USA state code.
     *
     * @param $str
     * @param $encryption_key
     * @return bool|string
     */
    protected function normalizeZip($str, $encryption_key)
    {
        // This is a string, right?
        $str = GetStringValue($str);

        // Decrypt the string, if it's encrypted.
        if ( GetStringValue($encryption_key) !== '' )
        {
            if ( IsEncryptedString($str) ) $str = A2PDecryptString($str, $encryption_key);
        }

        $str = StripNonNumeric($str);
        $str = $this->normalizeString($str, 5, $encryption_key, STR_PAD_LEFT, '0');

        return $str;
    }
}


/* End of file ReportTransamerica_model.php */
/* Location: ./system/application/models/ReportTransamerica_model.php */
