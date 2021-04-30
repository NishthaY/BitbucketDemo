<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// SecurityException
//
// This is really just an Exception, but because it is typed as a SecurityException
// we know that we must redirect the user to the unauthorized page and get them out
// of where ever they were.

class SecurityException extends Exception
{

}
