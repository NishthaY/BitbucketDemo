<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TestHarness extends SecureController {

    protected $app_name = APP_NAME;

    /**
     * _debug
     *
     * output the input as something readable to either STDOUT or the BROWSER
     * depending on your env.
     * @param $input
     */
    private function _debug($input)
    {
        $uri = GetArrayStringValue('REQUEST_URI', $_SERVER);
        if ( $uri === '' )
        {
            $type = gettype($input);
            if ( $type === 'boolean' || $type === 'integer' || $type === 'double' || $type === 'string' )
            {
                $input = GetStringValue($input . '\n');
            }
            $data = print_r($input, true );
            print $data;
        }
        if ( $uri !== '' ) {
            pprint_r($input);
        }
    }

    /**
     * index
     *
     * Run your test code!
     *
     * @param string $input
     */
    public function index($input='execute')
    {
        $company_id = 3;
        pprint_r("action [{$input}]");
        if ( $input === 'execute' )
        {
            pprint_r("done");
        }
        elseif($input === 'rollback' )
        {
            //$obj = new GenerateOriginalEffectiveDateData( true );
            //$obj->rollback($company_id);
        }
    }
}