<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// UIException
//
// This is really just an Exception, but because it is typed as a UIException
// we know that we can show the exception message to the end user.

class UIException extends Exception
{

}
