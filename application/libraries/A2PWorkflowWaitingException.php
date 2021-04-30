<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class A2PWorkflowWaitingException extends Exception
{
    protected $tag;

    public function __construct( $message = null, $tag=""  )
    {
        $this->tag = $tag;
        parent::__construct($message);
    }

    public function getTag()
    {
        return GetStringValue($this->tag);
    }
}
