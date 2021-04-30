<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class ColumnValidation_Date extends ColumnValidation {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);
        $this->validation_error_type = "invalid_date";
    }

    function looks_like($input) {

        // If we have a space, assume it's a timestamp and keep everything to the left.
        if ( strpos($input, " ") !== FALSE ) $input = fLeft($input, " ");

        // Replace known seperators with a dash.
        $input = replaceFor($input, "/", "-");
        $input = replaceFor($input, ".", "-");

        // YYYY-MM-DD is allowed. ( 10 chars )
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $input ) ) return true;

        // MM-DD-YYYY is allowed. ( 10 chars )
        if (preg_match("/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/",$input) ) return true;

        // MM-DD-YY is allowed. ( 8 chars )
        if (preg_match("/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{2}$/",$input) ) return true;

        // YYYYMMDD is allowed. ( 8 chars )
        if (preg_match("/^[0-9]{4}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])$/",$input) ) return true;

        return false;

    }
    function normalize($input) {
        $input = $this->apply_default_value( $input );
        if ( ! $this->looks_like($input) ) return false;

        // If we have a space, assume it's a timestamp and keep everything to the left.
        if ( strpos($input, " ") !== FALSE ) $input = fLeft($input, " ");

        // Replace known seperators with a slash.
        $input = replaceFor($input, "-", "/");
        $input = replaceFor($input, ".", "/");


        // Add century to the date if needed!
        if ( strlen($input) <= 8 && strpos($input, "/") !== FALSE )
        {

            // Dates 8 characters in length will require extra processing.

            // Before we start, there are a few cases where 4 digit year
            // dates could be 8 characters in length total.  If we think we have
            // a 4 digit century, we need to skip the bit below where we try to
            // calculate the century.
            // ---------------------------------------------------------------
            $calculate_cc = true;

            $month = fLeft($input, "/");
            $day = fBetween($input, "/", "/");
            $yy = fRightBack($input, "/");

            if ( strlen($month) == 4 ) $calculate_cc = false;
            if ( strlen($day) == 4 ) $calculate_cc = false;
            if ( strlen($yy) == 4 ) $calculate_cc = false;



            // Requested Business Logic
            // Get the current century ( cc )
            // Take the short date and set the century to be this century. ( guess )
            // Calculate now + 1 year. ( future )
            //
            // If guess > future, then assume the centry is cc - 1 else cc.
            // ---------------------------------------------------------------------

            if ( $calculate_cc )
            {
                $cc = strftime ( "%C" );

                $guess = "{$month}/{$day}/{$cc}{$yy}";
                $guess = strtotime($guess);
                $future = strtotime("+1 year");
                if ( $guess > $future ) $cc = ( $cc - 1 );
                $input = "{$month}/{$day}/{$cc}{$yy}";
            }


        }

        // Normalize it.
        $timestamp = strtotime($input);
        $date_array = getdate($timestamp);
        $input = getArrayStringValue("mon", $date_array) . "/" . getArrayStringValue("mday", $date_array) . "/" . getArrayStringValue("year", $date_array);

        return $input;

    }

}
