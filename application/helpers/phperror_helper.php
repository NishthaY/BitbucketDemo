<?php

// error_to_exception
//
// redirect PHP errors/warnings/etc to throw exceptions.
// Must be used in conjunction with set_error_handler()
// (and possibly restore_error_handler() if a temporary error
// redirection) to redirect PHP errors to this function.
// -----------------------------------------------------------

function error_to_exception($errno, $errstr) {
    //currently only implementing the required params -- see http://php.net/manual/en/function.set-error-handler.php
    throw new Exception($errstr);
}

/* End of file phperror_helper.php */
/* Location: ./application/helpers/phprror_helper.php */
