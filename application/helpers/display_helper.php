<?php

function DisplayPhoneNumber($value)
{
    $output = "";
    $value = StripNonNumeric($value);
    if ( strlen($value) == 10 )
    {
        $first = substr($value,0,3);
        $middle = substr($value, 3, 3);
        $last = substr($value, 6, 4);
        $output = "({$first}) {$middle}-{$last}";
    }
    return $output;
}

/* End of file display_helper.php */
/* Location: ./application/helpers/display_helper.php */
