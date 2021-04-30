<?php

/**
 * RemoveLinesContaining
 * Split the haystack into an array by newlines or, alternatively, the
 * supplied delimiter.  Each item in the resulting array is then searched
 * for the needle.  The haystack is reassembled excluding lines that contained
 * the needle.
 *
 * @param $haystack
 * @param $needle
 * @param string $delimiter
 * @return string
 */
function RemoveLinesContaining($haystack, $needle, $delimiter="\n")
{
    // This is our return value.
    $out = "";

    // split the haystack into an array using the specified delimiter.
    $data = explode($delimiter, $haystack);

    if ( ! empty($data) )
    {
        // Normalize our needle into our search token.
        $search = strtolower($needle);
        foreach($data as $item)
        {
            // Check to see if item contains the search token.  If it does,
            // do not add that chunk to the output.
            $item = strtolower($item);
            if ( strpos($item, $search)  !== FALSE )
            {
                continue;
            }
            $out .= $item . $delimiter;
        }
    }

    // Make sure we don't have any trailing or leading whitespace.
    $out = trim($out);

    return $out;
}

/**
 * GetFilenameFromString
 * Given a string, remove any characters or sequence of characters that would
 * prevent it from being used as a filename on an operating system.
 *
 * @param $filename
 * @return string
 */
function GetFilenameFromString($filename)
{
    // Remove these characters from the filename.
    $filename = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);

    // Remove any runs of periods
    $filename = preg_replace("([\.]{2,})", '', $filename);

    // Remove Spaces, if there are any left after the regex
    $filename = trim($filename);
    $filename = replaceFor($filename, " ", "");

    $out = GetStringValue($filename);
    return $out;
}

/**
 * GetMoneyValue
 * Given an input string, convert the string using the PHP NumberFormatter into US
 * currency.  Once converted format it into USD formatted string.
 *
 * @param $money_value
 * @return string
 */
function GetMoneyValue($money_value)
{
    // Create a number formatter that will turn a money value into US currency.
    $fmt = new NumberFormatter( 'en_US.UTF-8', NumberFormatter::CURRENCY );

    // Format the money value using the number formatter.
    $money_value = GetStringValue($money_value);
    $money_value = $fmt->formatCurrency(GetFloatValue($money_value), 'USD');

    return $money_value;
}

/**
 * RandomString
 * Create a string with a random set of characters upto the length specified.
 * @param int $length
 * @param null $characters
 * @return string
 * @throws Exception
 */
function RandomString($length = 10, $characters = null)
{
    // When creating a random string, use the characters supplied.  If none provided
    // use the default set below.
    if ( getStringValue($characters) === '' )
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }

    // How long is our character set?
    $charactersLength = strlen($characters);

    // For the length specified, randomly select a character from our character set
    // until we have a string of the desired length.
    $out_randomString = '';
    for ($i = 0; $i < $length; $i++)
    {
        $index = random_int(0, $charactersLength - 1);
        $out_randomString .= $characters[$index];
    }
    return $out_randomString;
}

/**
 * StartsWith
 * Boolean check to decide if the haystack starts with the needle.
 * Case sensitive
 *
 * @param $haystack
 * @param $needle
 * @return bool
 */
function StartsWith($haystack, $needle)
{
    $needle = GetStringValue($needle);
    if ( $needle === '' ) return TRUE;
    if ( strpos($haystack, $needle) === 0 ) return TRUE;

    return FALSE;
}

/**
 * EndsWith
 * Boolean check to decide if the haystack ends with the needle.
 * Case sensitive.
 *
 * @param $haystack
 * @param $needle
 * @return bool
 */
function EndsWith($haystack, $needle)
{
    $needle = GetStringValue($needle);
    $haystack = GetStringValue($haystack);

    if ( $needle === '' ) return true;
    if ( substr($haystack, (strlen($needle) * -1) ) === $needle ) return true;
    return false;
}

/**
 * GetArrayStringValue
 * This function will return the value as a string for the key in the supplied
 * associative array.  If the key does not exist in the array, the empty string is
 * returned.
 *
 * @param $key
 * @param $array
 * @return string
 */
function GetArrayStringValue( $key, $array ) {

    // If the user messed up and passed the key and array in the wrong
    // parameters, go ahead and swap them to make their life easier.
    if ( is_array($key) && ! is_array($array) )
    {
        $in_key = $key;
        $in_array = $array;

        $key = $in_array;
        $array = $in_key;
    }

    // Make sure the key is a string.
    $key = GetStringValue($key);

    // Check to see if the key exists before you attempt to access it.
    if ( isset($array[$key]) )
    {
        return GetStringValue($array[$key]);
    }

    return "";
}

/**
 * GetArrayIntValue
 * This function will return the value as an int for the key in the supplied
 * associative array.  If the key does not exist in the array, zero is returned.
 *
 * @param $key
 * @param $array
 * @return int
 */
function GetArrayIntValue( $key, $array )
{

    // If the user messed up and passed the key and array in the wrong
    // parameters, go ahead and swap them to make their life easier.
    if ( is_array($key) && ! is_array($array) )
    {
        $in_key = $key;
        $in_array = $array;

        $key = $in_array;
        $array = $in_key;
    }

    $value = getArrayStringValue($key, $array);
    return getIntValue($value);
}

/**
 * GetArrayFloatValue
 * This function will return the value as a float for the key in the supplied
 * associative array.  If the key does not exist in the array, zero is returned.
 *
 * @param $key
 * @param $array
 * @return float|int
 */
function GetArrayFloatValue( $key, $array )
{
    // If the user messed up and passed the key and array in the wrong
    // parameters, go ahead and swap them to make their life easier.
    if ( is_array($key) && ! is_array($array) )
    {
        $in_key = $key;
        $in_array = $array;

        $key = $in_array;
        $array = $in_key;
    }

    $value = getArrayStringValue($key, $array);
    return getFloatValue($value);
}

/**
 * RemoveWhiteSpaceAndNewlines
 * This function will remove all whitespace, tabs, newlines and carriage returns from
 * the input.  The empty string is returned if the item passed in is not a primitive
 * object.
 *
 * @param $inString
 * @return string
 */
function RemoveWhiteSpaceAndNewlines($inString)
{
    $inString = preg_replace('/[ \t]+/', "", GetStringValue($inString));		// remove white space.
    $inString = preg_replace('/[\n]+/', "", GetStringValue($inString));		// remove newlines.
    $inString = preg_replace('/[\r]+/', "", GetStringValue($inString));		// remove carriage returns.
    return GetStringValue($inString);
}

/**
 * GetStringValue
 * This function will return the input item as a string based on the following
 * rules.
 *
 * - null -> empty string
 * - array -> empty string
 * - object -> empty string
 * - bool -> "TRUE" || "FALSE"
 * - zero -> "0"
 * - Everything else will transform based on PHP string casting.
 *
 * @param $input
 * @return string
 */
function GetStringValue($input) {

    if ( is_null($input) ) return "";           // null becomes the empty string.
    if ( is_array($input) ) return "";          // arrays become the empty string
    if ( is_object($input) ) return "";         // objects become the empty string

    // Bool values become the string "TRUE" or "FALSE"
    if ( is_bool($input) )
    {
        if ( $input ) return "TRUE";
        return "FALSE";
    }

    // If the number is the number zero, return zero as a string.
    if ( $input === 0 ) return "0";

    // Cast the input as a string.
    return (String)$input;

}

/**
 * GetIntValue
 * This function will turn the input item into an integer using the following ruleset
 *
 * - null -> zero
 * - empty string -> zero
 * - Everything else will transform based on php int casting.
 *
 * @param $input
 * @return int
 */
function GetIntValue($input) {

    if ( $input == NULL ) return (int) 0;
    if ( $input == "" ) return (int) 0;

    $out = (int) $input;
    if ( GetStringValue($out) === '' ) return (int) $out;
    return (int) $out;
}

/**
 * GetFloatValue
 * This function will turn the input item into a float using the following ruleset
 *
 * - null -> zero
 * - empty string -> zero
 * - Everything else will transform based on php float casting.
 *
 * @param $input
 * @return float|int
 */
function GetFloatValue($input)
{
    if ( $input == NULL ) return 0;
    if ( $input == "" ) return 0;

    $out = (float) $input;
    if ( GetStringValue($out) === '' ) return (float) 0;
    return (float) $out;


}

/**
 * ReplaceFor
 * Wrapper for PHP str_replace, with the addition making sure the item
 * passed in is a string on the way in and out.
 * @param $haystack
 * @param $search
 * @param $replace
 * @return string
 */
function ReplaceFor($haystack, $search, $replace)
{
    if( GetStringValue($search) === '' ) return "";

    $out = str_replace($search, $replace, $haystack);
    return GetStringValue($out);
}

/**
 * FBetween
 * Returns the haystack content between the first instance of the left
 * and right needle.  The empty string is returned if content cannot
 * be found between the two needles.
 *
 * @param $haystack
 * @param $needleLeft
 * @param $needleRight
 * @return string
 */
function FBetween($haystack, $needleLeft, $needleRight)
{
    $part = FRight($haystack, $needleLeft);
    $output =  FLeft($part, $needleRight);
    return GetStringValue($output);
}

/**
 * FLeftBack
 * Starting from the back of the haystack, search for needle.  If found,
 * return everything to the LEFT of the needle in the haystack.  If the
 * needle is not found, the empty string is returned.
 *
 * @param $haystack
 * @param $needle
 * @return string
 */
function FLeftBack($haystack, $needle)
{
    // Reverse the needle an haystack so an FRight will function before
    // reversing the output.
    $haystack = strrev(GetStringValue($haystack));
    $needle = strrev(GetStringValue($needle));

    $out = strrev(FRight($haystack, $needle));
    return GetStringValue($out);
}

/**
 * FRightBack
 * Starting from the back of the haystack, search for needle.  If found,
 * return everything to the RIGHT of the needle in the haystack.  If the
 * needle is not found, the empty string is returned.
 *
 * @param $haystack
 * @param $needle
 * @return string
 */
function FRightBack($haystack, $needle)
{
    $haystack   = strrev(GetStringValue($haystack));
    $needle    = strrev(GetStringValue($needle));

    return strrev(fLeft($haystack, $needle));
}

/**
 * FLeft
 * Starting from the front of the haystack, search for needle.  If found,
 * return everything to the LEFT of the needle in the haystack.  If the
 * needle is not found, the empty string is returned.
 *
 * @param $haystack
 * @param $needle
 * @return string
 */
function FLeft($haystack, $needle)
{
    $index = strpos(GetStringValue($haystack), GetStringValue($needle));
    if( ! is_bool($index) )
    {
        $index = GetIntValue($index);
        $out = substr($haystack, 0, $index);
        return GetStringValue($out);
    }
    return "";
}

/**
 * FRight
 * Starting from the front of the haystack, search for needle.  If found,
 * return everything to the RIGHT of the needle in the haystack.  If the
 * needle is not found, the empty string is returned.
 *
 * @param $haystack
 * @param $needle
 * @return string
 */
function FRight($haystack, $needle)
{
    $index = strpos(GetStringValue($haystack), GetStringValue($needle));
    if( ! is_bool($index))
    {
        $index = GetIntValue($index);
        $index = $index + strlen($needle);
        $out = substr($haystack, $index);
        return GetStringValue($out);
    }
    return "";
}

/**
 * StripNonNumeric
 * Search input and remove any characters that are not digits.  Return
 * the leftovers as a string.
 *
 * @param string $input
 * @return string
 */
function StripNonNumeric($input="")
{
    $input = GetStringValue($input);
    $value = preg_replace('/\D/', '', $input);
    return GetStringValue($value);
}

/**
 * GetCSVString
 * Given an associative array of data representing a row of CSV data,
 * return the string representation of that data as it would be found
 * in a CSV file.
 *
 * This function will return FALSE on failure.
 *
 * @param array $fields
 * @return bool|string
 */
function GetCSVString(array $fields)
{
    $fh = null;
    try
    {
        $fh = fopen('php://memory', 'r+');
        if (fputcsv($fh, $fields) === FALSE)
        {
            throw new Exception("Unable to put CSV data.");
        }
        rewind($fh);
        $csv_line = stream_get_contents($fh);
        $csv_line = rtrim($csv_line);
        return $csv_line;
    }
    catch(Exception $e)
    {
        if ( is_resource($fh) ) fclose($fh);
    }
    return FALSE;
}

/* End of file string_helper.php */
/* Location: ./application/helpers/string_helper.php */
