<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class APIMessage
{
    public $code;
    public $results;
    public $message;
    public $status;


    public function __construct($code=200, $results=array(), $message=null)
    {
        $this->status = true;
        $this->code = $code;
        $this->results = $results;
        $this->message = $message;
    }

    public function getResponseCode($interface=null)
    {
        $interface = strtolower($interface);
        switch($interface)
        {
            case 'cli':
                if ($this->code >= 200 && $this->code <= 299) {
                    return 0;
                }
                else {
                    return intval(floor($this->code/100));
                }
                break;
            default:
                return $this->code;
                break;
        }
    }
}
class APIErrorMessage extends APIMessage
{
    public function __construct($code = 200, $message = null)
    {
        $this->status = false;
        $this->results = array();

        $this->code = $code;
        $this->message = $message;
    }
}