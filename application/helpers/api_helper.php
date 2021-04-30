<?php

/**
 * Given a collection of required keys, this function will search
 * the data and confirm each required key exists.  Use the allow_empty_fields
 * optional parameter to confirm a key exists, but allow the value for that
 * field to be empty.
 *
 * @param $required
 * @param $data
 * @param bool $allow_empty_fields
 */
function CheckRequired( $required, $data, $allow_empty_fields=false )
{
    $missing = array();
    $data_cleaned = array_change_key_case($data, CASE_LOWER);
    $required_cleaned = array_map('strtolower', $required);

    foreach($required_cleaned as $key)
    {
        if ( ! isset($data_cleaned[$key]))
        {
            $missing[] = $key;
        }
        else
        {
            if(!$allow_empty_fields)
            {
                $value = RemoveWhiteSpaceAndNewlines($data_cleaned[$key]);
                if ( $value === '' )
                {
                    $missing[] = $key;
                }
            }
        }
    }

    if ( empty($missing) ) return TRUE;
    return $missing;
}

/* End of file api_helper.php */
/* Location: ./application/helpers/api_helper.php */
