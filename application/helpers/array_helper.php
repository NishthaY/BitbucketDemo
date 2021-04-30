<?php

/**
 * ArrayRemoveKeyStartWith
 *
 * Given an array with key/value pairs OR a
 * collection of arrays with key/value pairs search
 * through them all and return the same structure, but
 * with key/values removed where the key starts with
 * our search.
 *
 * @param $search
 * @param $array
 * @return array
 */
function ArrayRemoveKeyStartWith($search, $array)
{
    if ( empty($array) ) return array();
    if ( isset($array['0']) && is_array($array["0"]) )
    {
        // You have a collection of key/value pair arrays.
        $rows = array();
        foreach($array as $row)
        {
            $rows[] = ArrayRemoveKeyStartWith($search, $row);
        }
        return $rows;
    }
    else
    {
        // You have a key/value pair array.
        $output = array();
        foreach($array as $key=>$value)
        {
            if ( ! StartsWith($key, "Encrypted") )
            {
                $output[$key] = $value;
            }
        }
        return $output;
    }
}

/**
 * ArrayMultiSearchIndexOf
 *
 * Walk a multi-dimensional array looking for the needle key.  Once found,
 * check to see if the corresponding value matches our search needle.
 * If they match, return the index of the object in the array that contains
 * the key/value hit.  If not found, this function will return FALSE.
 *
 * @param $key
 * @param $search
 * @param $haystack
 * @return bool|int|string
 */
function ArrayMultiSearchIndexOf($needlekey, $needle, $haystack)
{
    foreach($haystack as $key=>$data)
    {
        if ( isset($data[$needlekey]) && $data[$needlekey] == $needle ) return $key;
    }
    return FALSE;
}


// UASORT FUNCTIONS
// Below this section we will add search functions that can be used
// in conjunction with uasort to search a multi-dimensional array.
//
// https://www.php.net/manual/en/function.uasort.php
// ---------------------------------------------------------------------

function AssociativeArraySortFunction_company_name($elem1, $elem2) {
    $ret = strcmp($elem1['company_name'], $elem2['company_name']);
    return $ret;
}
function AssociativeArraySortFunction_Key_numeric($elem1, $elem2)
{
    if ($elem1 == $elem2) {
        return 0;
    }
    return ($elem1 < $elem2) ? -1 : 1;
}
function AssociativeArraySortFunction_SortOrder_numeric($elem1, $elem2)
{
    if ($elem1 == $elem2) {
        return 0;
    }
    return ($elem1 < $elem2) ? -1 : 1;
}
function AssociativeArraySortFunction_Name($elem1, $elem2) {
    $ret = strcmp($elem1['Name'], $elem2['Name']);
    return $ret;
}
function AssociativeArraySortFunction_Name_lowercase($elem1, $elem2) {
    $ret = strcmp($elem1['name'], $elem2['name']);
    return $ret;
}
function AssociativeArraySortFunction_Display($elem1, $elem2) {
    $ret = strcmp($elem1['display'], $elem2['display']);
    return $ret;
}
function AssociativeArraySortFunction_Sort($elem1, $elem2) {
    $ret = strcmp($elem1['Sort'], $elem2['Sort']);
    return $ret;
}
function AssociativeArraySortFunction_carrier_name($elem1, $elem2) {
    $ret = strcmp($elem1['CarrierName'], $elem2['CarrierName']);
    return $ret;
}
function AssociativeArraySortFunction_carrier_code($elem1, $elem2) {
    $ret = strcmp($elem1['CarrierName'], $elem2['CarrierCode']);
    return $ret;
}




/* End of file array_helper.php */
/* Location: ./application/helpers/array_helper.php */
