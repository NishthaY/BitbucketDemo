<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class APIException extends Exception
{
    protected $payload;
    protected $code;

    public function __construct( $message = null, $code = 0, $payload=array() )
    {
        $this->code = $code;
        $this->payload = $payload;

        if ( ! is_array($payload) ) $this->payload = array();
        $this->code = GetIntValue($code);
        parent::__construct($message);
    }

}
