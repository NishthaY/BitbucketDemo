<?php

/**
 * GetURLDomainName
 *
 * Combine the HOSTNAME and APP_PORT into the domain name
 * needed to reach the application through a link.
 *
 * @return string
 */
function GetURLDomainName()
{
    $app_port = GetStringValue(APP_PORT);
    if ( $app_port !== '' && $app_port !== '80' )
    {
        return GetStringValue(HOSTNAME . ":" . $app_port);
    }
    return GetStringValue(HOSTNAME);
}

/**
 * Given an identifier, find the human readable name.
 * Possible identifier types are
 *  - company
 *  - companyparent
 *
 * @param $identifier
 * @param $identifier_type
 * @return string
 */
function GetIdentifierName($identifier, $identifier_type)
{
    $CI = &get_instance();
    $identifier_name = "";
    if ( $identifier_type === 'company' )
    {
        $company = $CI->Company_model->get_company($identifier);
        $identifier_name = getArrayStringValue("company_name", $company);
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $companyparent = $CI->CompanyParent_model->get_companyparent($identifier);
        $identifier_name = getArrayStringValue("Name", $companyparent);
    }
    return $identifier_name;
}

/**
 * RegisterA2PStreamWrapper
 *
 * When we want to use a custom stream wrapper, we need to register it.
 * This function will register one of our wrappers only once.  It will
 * check to see if the wrapper exists too.
 *
 * @param $tag
 * @param $classname
 * @return bool
 */
function RegisterA2PStreamWrapper($tag, $classname)
{
    $CI = &get_instance();
    if ( file_exists(APPPATH."libraries/{$classname}.php") )
    {
        $wrappers = stream_get_wrappers();
        if ( ! in_array($tag, $wrappers) )
        {
            $CI->load->Library($classname);
            stream_wrapper_register($tag, $classname);
        }
        return true;
    }
    return false;
}

/**
 * GetPusherAPIKey
 *
 * Return the Pusher API key.
 *
 * @return bool|string
 */
function GetPusherAPIKey()
{
    return fLeft(fRight(PUSHER_URL, "//"), ":");
}

/**
 * GetPusherAPISecret
 *
 * Return the Pusher API secret.
 *
 * @return bool|string
 */
function GetPusherAPISecret()
{
    return fLeft(fRight(fRight(PUSHER_URL, "//"), ":"), "@");
}

/**
 * GetPusherAPIAppId
 *
 * Return the Pusher API app id.
 *
 * @return string
 */
function GetPusherAPIAppId()
{
    return fRightBack(PUSHER_URL, "/");
}

/**
 * GetPusherAPICluster
 *
 * Return the pusher API cluster.
 *
 * @return array|false|string
 */
function GetPusherAPICluster()
{
    return PUSHER_CLUSTER;
}

/**
 * ArchiveHistoricalData
 * This function will take the $data passed in and create a json file on
 * S3 that can be viewed by support. The file will be called by the $tag
 * provided.  Meta data will be created as well to aid in giving the
 * snapshot context.
 *
 * user_id - Who took the snapshot.
 * rollup - allow the resulting data table to rollup after X columns are displayed where X is the value of rollup.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $tag
 * @param $data
 * @param $list
 * @param $user_id
 * @param int $rollup
 * @throws Exception
 */
function ArchiveHistoricalData( $identifier, $identifier_type, $tag, $data, $list, $user_id, $rollup=0 )
{
    $CI = &get_instance();

    if ( $identifier_type === 'company' )
    {
        $snapshot_tag = GetUploadDateFolderName($identifier);      // CCYYMM
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $snapshot_tag = GetWorkflowProgressProperty($identifier, $identifier_type, null, 'SupportTag');
        if ( $snapshot_tag === '' )
        {
            $snapshot_tag = new DateTime();
            $snapshot_tag = $snapshot_tag->format('YmdHis');    // CCYYMMHHMMSS
        }
    }
    else
    {
        throw new Exception("Unknown identifier type.");
    }


    if ( getStringValue($tag) == "" ) return;
    if ( empty($data) ) $data = array();
    if ( empty($list) ) $list = array();
    $timestamp = new DateTime(null, new DateTimeZone('UTC'));

    $metadata = array();
    $metadata['timestamp'] = new DateTime(null, new DateTimeZone('UTC')); //$timestamp->format("c");
    $metadata['user'] = $CI->User_model->get_user_by_id($user_id);
    $metadata['rollup'] = ( getIntValue($rollup) <= 0 ? FALSE : getIntValue($rollup) );
    if ( ! empty($metadata['user']['password']) ) unset($metadata['user']['password']);


    // Create the archive location if needed.
    $archive_prefix = GetS3Prefix('archive', $identifier, $identifier_type);
    $archive_prefix  = replaceFor($archive_prefix, "DATE", $snapshot_tag);
    $archive_prefix  .= "/json";
    S3MakeBucketPrefix(S3_BUCKET, $archive_prefix);

    $payload = array();
    $payload['metadata'] = $metadata;
    $payload['list'] = $list;
    $payload['data'] = $data;

    // Turn the input data into a json object.
    $json = json_encode($payload, JSON_PRETTY_PRINT);

    // Copy an object and add server-side encryption.
    S3SaveEncryptedFile( S3_BUCKET, $archive_prefix, "{$tag}.json", $json );

}

/**
 * RestoreDisallowedCharacters
 *
 * A few places in the application we need to pass data to the server, but the data we collected
 * via Javascript contains reserved characters.  Revert those conversions on the server with this
 * function.
 *
 * See Also: ReplaceDisallowedCharacters ( app.js )
 *
 * @param $input
 * @return mixed
 */
function RestoreDisallowedCharacters($input)
{
    $input = replaceFor($input, "::PLUS::", "+");
    $input = replaceFor($input, "::RPAR::", ")");
    $input = replaceFor($input, "::LPAR::", "(");
    $input = replaceFor($input, "::PERCENT::", "%");
    $input = replaceFor($input, "::SLASH::", "/");
    return $input;
}

/**
 * IsDevelopment
 *
 * This function will investigate the base_url for the site.  If the URL
 * appears to be pointing to a local sandbox, this function will return true.
 *
 * @return bool
 */
function IsDevelopment()
{
    if ( strpos(HOSTNAME, "dev.advice2pay.com") !== FALSE ) return true;
    if ( strpos(HOSTNAME, "upgrade.advice2pay.com") !== FALSE ) return true;

    $segs = explode("/", base_url());
    $indicator = getArrayStringValue("2", $segs);
    if ( $indicator != "" && EndsWith($indicator, "3000") ) return true;
    if ( strpos($indicator, "c9users.io" ) !== FALSE ) return true;
    if ( strpos($indicator, "codeanyapp.com" ) !== FALSE ) return true;
    if ( strpos($indicator, "nitrousapp.com" ) !== FALSE ) return true;
    if ( strpos($indicator, "dev.advice2pay.com" ) !== FALSE ) return true;
    if ( strpos($indicator, "upgrade.advice2pay.com" ) !== FALSE ) return true;

    return false;
}

/**
 * IsDemo
 *
 * This function will investigate the base_url for the site.  If the URL
 * contains demo.advice2pay.com it's Demo!
 *
 * @return bool
 */
function IsDemo()
{
    if ( strpos(HOSTNAME, "demo.advice2pay.com") !== FALSE ) return true;

    $segs = explode("/", base_url());
    $indicator = getArrayStringValue("2", $segs);
    if ( $indicator == "demo.advice2pay.com" ) {
        return true;
    }
    return false;
}

/**
 * IsSandbox
 *
 * This function will investigate the base_url for the site.  If the URL
 * contains sandbox.advice2pay.com it's Sandbox!
 *
 * @return bool
 */
function IsSandbox()
{
    if ( strpos(HOSTNAME, "sandbox.advice2pay.com") !== FALSE ) return true;

    $segs = explode("/", base_url());
    $indicator = getArrayStringValue("2", $segs);
    if ( $indicator == "sandbox.advice2pay.com" ) {
        return true;
    }
    return false;
}

/**
 * IsUAT
 * This function will investigate the base_url for the site.  If the URL
 * contains uat.advice2pay.com it's UAT.
 *
 * @return bool
 */
function IsUAT()
{
    if ( strpos(HOSTNAME, "uat.advice2pay.com") !== FALSE ) return true;

    $segs = explode("/", base_url());
    $indicator = getArrayStringValue("2", $segs);
    if ( $indicator == "uat.advice2pay.com" ) {
        return true;
    }
    return false;

}

/**
 * IsQA
 * This function will investigate the base_url for the site.  If the URL
 * contains qa.advice2pay.com it's UAT.
 *
 * @return bool
 */
function IsQA()
{
    if ( strpos(HOSTNAME, "qa.advice2pay.com") !== FALSE ) return true;

    $segs = explode("/", base_url());
    $indicator = getArrayStringValue("2", $segs);
    if ( $indicator == "qa.advice2pay.com" ) {
        return true;
    }
    return false;
}


/**
 * LevelTag
 *
 * This function will return a tag indicating which release level we are
 * working against.
 *
 * @return string
 */
function LevelTag()
{
    if ( IsDevelopment() ) return "DEV";
    if ( IsDemo() ) return "DEMO";
    if ( IsSandbox() ) return "SBOX";
    if ( IsUAT() ) return "UAT";
    if ( IsQA() ) return "QA";
    return "PROD";
}

/**
 * CompanyDescription
 *
 * Review the session and return a human readable string that
 * describes what company you are acting as.
 *
 * @return string
 */
function CompanyDescription ()
{
    if ( ! IsLoggedIn() ) return "";
    $CI = &get_instance();
    $CI->load->model('User_model','user_model',true);
    $CI->load->model('Company_model','company_model',true);

    if ( GetSessionValue("company_id") != "" )
    {
        $company_id = GetSessionValue("company_id");
        $company = $CI->company_model->get_company($company_id);
        $company = getArrayStringValue("company_name", $company);
    }
    else if ( GetSessionValue("companyparent_id") != "" )
    {
        $company_parent_id = GetSessionValue("companyparent_id");
        $parent = $CI->CompanyParent_model->get_companyparent($company_parent_id);
        $company = getArrayStringValue("Name", $parent);
    }


    return $company;
}

/**
 * WhowasiDescription
 *
 * Review the session and return a human readable string that
 * describes what company you were before you started operating
 * as the current company.
 *
 * @return string
 */
function WhowasiDescription()
{
    if ( ! IsLoggedIn() ) return "";
    $CI = &get_instance();
    $CI->load->model('User_model','user_model',true);
    $CI->load->model('Company_model','company_model',true);

    $company="";
    if ( GetSessionValue("_companyparent_id") !== '' )
    {
        $company_parent_id = GetSessionValue("_companyparent_id");
        $parent = $CI->CompanyParent_model->get_companyparent($company_parent_id);
        $company = getArrayStringValue("Name", $parent);
    }
    else if ( GetSessionValue("_company_id") != "" )
    {
        $company_id = GetSessionValue("_company_id");
        $company = $CI->company_model->get_company($company_id);
        $company = getArrayStringValue("company_name", $company);
    }

    return $company;
}

/**
 * WeakPassword
 *
 * This helper function will redirect you to the preferred
 * location if your password it too weak.
 *
 */
function WeakPassword()
{
    redirect( base_url() . "auth/password" );
    exit;
}

/**
 * AccessDenied
 *
 * This helper function will redirect you to the preferred location
 * if you do not have permission to view the page you are trying to
 * access.
 *
 * @param null $error
 */
function AccessDenied($error=null)
{
    $CI = &get_instance();

    if ( $CI->input->is_cli_request() ) { print "CLI: access denied\n"; exit; }
    if ( ! IsLoggedIn() )
    {
        if ( GetStringValue($error) !== '' ) LogIt('AccessDenied', GetStringValue($error), null, null, null, null);
        redirect( base_url() . "auth" );
    }else{
        if ( GetStringValue($error) !== '' ) LogIt('AccessDenied', GetStringValue($error), null, GetSessionValue('user_id'), GetSessionValue('company_id'), GetSessionValue('companyparent_id'));
        redirect( base_url() . "auth/permission" );
    }

    exit;
}

/**
 * Error404
 *
 * This helper function will redirect you to the preferred location
 * if the page you are accessing cannot be found.
 *
 * @param null $error
 */
function Error404( $error=null )
{
    $CI = &get_instance();
    if ( $CI->input->is_cli_request() ) { print "CLI: file not found\n"; exit; }
    if ( GetStringValue($error) !== "" )
    {
        if ( ! IsLoggedIn() )
        {
            LogIt('Error404', GetStringValue($error), null, null, null, null);
        }
        else
        {
            LogIt('Error404', GetStringValue($error), null, GetSessionValue('user_id'), GetSessionValue('company_id'), GetSessionValue('companyparent_id'));
        }
    }

    redirect( base_url() . "auth/error_404" );
    exit;
}

/**
 * RenderViewSTDOUT
 *
 * This helper function will display a CodeIgniter view directly
 * to standard out.
 *
 * @param $view
 * @param array $view_array
 */
function RenderViewSTDOUT($view, $view_array=array())
{
    print RenderViewAsString($view, $view_array);
}

/**
 * RenderViewAsString
 *
 * This helper function will render a CodeIgniter view as a
 * string.
 *
 * @param $view
 * @param array $view_array
 * @return string|void
 */
function RenderViewAsString($view, $view_array=array())
{
    if ( getStringValue($view) == "" ) return;
    if ( ! isset($view_array) ) $view_array = array();

    $CI = &get_instance();
    return GetStringValue($CI->load->view($view, $view_array, TRUE));
}

/**
 * RenderView
 *
 * This helper function just executes the CodeIgniter view function but
 * allows you to optionally pass in an array.
 *
 * See Also: RenderViewSTDOUT  ( functionally the same )
 *
 * @param $view
 * @param array $view_array
 */
function RenderView($view, $view_array=array())
{
    if ( getStringValue($view) == "" ) return;
    if ( ! isset($view_array) ) $view_array = "";

    $CI = &get_instance();
    $CI->load->view($view, $view_array);

}

/**
 * GetConfigValue
 *
 * Find a CodeIgniter config value and turn it into a string.
 * You may specifiy what config file to look in or optionally default
 * to the app confing file.
 *
 * @param $key
 * @param string $config
 * @return string
 */
function GetConfigValue( $key, $config="app")
{
    $CI = &get_instance();
    $CI->config->load($config);
    return getStringValue($CI->config->item($key));
}

/**
 * CachedQS
 *
 * This is a cache buster.  This allows developers to stop browsers from
 * caching javascript files.  When you release software you may optionally
 * update the 'build_tag' config value in the app config file to but the
 * cache once per release.  When adding a JS file to an HTML page make sure
 * to add the <?=CacheQS?> tag to the end of the URL.
 *
 * @return string
 */
function CachedQS()
{
    $CI = &get_instance();
    $CI->config->load('app');

    $tag = getStringValue($CI->config->item('build_tag'));
    if ( $tag == "" ) return "";

    if ( $tag == "TIMESTAMP" )
    {
        $tag = date("YmdHis");
    }

    return "?tag={$tag}";
}

/**
 * GetSessionValue
 *
 * This function returns a value from the CodeIgniter session and
 * turns it into a string.
 *
 * @param $key
 * @return string
 */
function GetSessionValue($key)
{
    $CI =& get_instance();
    if (isset($CI->session->userdata[$key])) {
        $obj = $CI->session->userdata[$key];
        if ( is_array($obj) ) return $obj;
        return getStringValue($obj);
    }
    return "";
}

/**
 * SetSessionValue
 *
 * This function will set a key/value pair into the user's session.
 *
 * @param $key
 * @param $value
 */
function SetSessionValue($key, $value)
{
    $CI =& get_instance();
    $CI->session->set_userdata($key, $value);
}

/**
 * RemoveSessionValue
 *
 * This function will remove a key and it's value from the user's session.
 *
 * @param $key
 */
function RemoveSessionValue($key)
{
    $CI =& get_instance();
    if (isset($CI->session->userdata[$key]))
    {
        $CI->session->unset_userdata($key);
    }
}

/**
 * GetSessionObject
 *
 * This function will return the raw object from the user's session.
 * If the session is holding an array, you will get an array.  If it
 * is a string, a string, etc.  No modification to the session object.
 * If it's not found, a null will be returned.
 *
 * @param $key
 * @return |null
 */
function GetSessionObject($key)
{
    $CI =& get_instance();
    if (isset($CI->session->userdata[$key])) {
        $obj = $CI->session->userdata[$key];
        if ( is_array($obj) ) return $obj;
        return $obj;
    }
    return null;
}

/**
 * vJqueryValidate
 *
 * We have a copy of the jquery-validate library checked into our codeline.
 * All references to that library in the HTML view will use this function
 * to reference it's path.  This will allow us to add a new folder later with
 * a different version to upgrade.
 *
 * @return string
 */
function vJqueryValidate()
{
    $CI = &get_instance();
    $CI->config->load('app');
    return getStringValue($CI->config->item('vJqueryValidate'));
}

/**
 * vPusher
 * We have a copy of the pusher library checked into our codeline.
 * All references to that library in the HTML view will use this function
 * to reference it's path.  This will allow us to add a new folder later with
 * a different version to upgrade.
 *
 * @return string
 */
function vPusher()
{
    $CI = &get_instance();
    $CI->config->load('app');
    return getStringValue($CI->config->item('vPusher'));
}

/**
 * AJAXSuccess
 *
 * This function will return a JSON object that is compatible
 * with several JavaScript functions in our library.  Use this
 * to return a SUCCESS response from the server to the client.
 *
 * The return object is an array with the following parameters.
 *
 * - status ( true || false : Indicates if the server was or was not able to process the client request)
 * - type ( success, info, warning danger, redirect )
 * - message ( Human readable message for UI. )
 * - href ( when the type is redirect, the browser will redirect to this location. )
 * - ??? ( you may provide any other custom key/value pairs as long as it not on of the 4 reserved items.
 *
 * @param string $message
 * @param null $url
 * @param array $additional
 */
function AJAXSuccess( $message="", $url=null, $additional=array() )
{
	if ( $message == null )
	{
		$message = "";
	}
	else if ( getStringValue($message) == ""  )
	{
		$message = "Success!";
	}

	$payload = array();
	$payload['status'] = true;
	$payload["type"] = "success"; // info, warning, danger
	$payload["message"] = getStringValue($message);
	$payload["href"] = "";

    // add any additional key/value pairs for this success message
    $keys = array_keys($additional);
    if ( ! empty($additional) )
    {
        foreach ($additional as $key=>$value)
        {
            if ($key == "status") continue;
            if ($key == "type") continue;
            if ($key == "message") continue;
            if ($key == "href") continue;
            $payload[$key] = $value;
        }
    }

	if ( getStringValue($url) != "" )
	{
		$payload["href"] = $url;
		$payload["type"] = "redirect";

		if ( getStringValue($message) != "" )
		{
			$CI =& get_instance();
			$CI->session->set_flashdata('error', $message);
			$CI->session->set_flashdata('error-type', "success");
		}


	}

	print json_encode($payload);
	exit;
}


/**
 * AJAXDanger
 *
 * See Also: AJAXSuccess
 * This function operates EXACTLY like AJAXSuccess, but it will operate with the
 * danger type rather than success.
 *
 *
 * @param string $message
 * @param null $url
 * @param array $additional
 */
function AJAXDanger( $message="", $url=null, $additional=array() )
{

	if ( $message == null )
	{
		$message = "";
	}
	else if ( getStringValue($message) == ""  )
	{
		$message = "An unexpected situation has occurred. Please try again later.";
	}

	$payload = array();
	$payload['status'] = true;
	$payload["type"] = "danger"; // info, warning, danger
	$payload["message"] = getStringValue($message);
	$payload["href"] = "";

    // add any additional key/value pairs for this success message
    $keys = array_keys($additional);
    if ( ! empty($additional) )
    {
        foreach ($additional as $key=>$value)
        {
            if ($key == "status") continue;
            if ($key == "type") continue;
            if ($key == "message") continue;
            if ($key == "href") continue;
            $payload[$key] = $value;
        }
    }

	if ( getStringValue($url) != "" )
	{
		$payload["href"] = $url;
		$payload["type"] = "redirect";

		$CI =& get_instance();
		$CI->session->set_flashdata('error', getStringValue($message));
		$CI->session->set_flashdata('error-type', "danger");

	}

	print json_encode($payload);
	exit;
}

/**
 * AJAXWarning
 *
 * See Also: AJAXSuccess
 * This function operates EXACTLY like AJAXSuccess, but it will operate with the
 * warning type rather than success.
 *
 * @param null $message
 * @param null $url
 */
function AJAXWarning( $message=null, $url=null )
{
	if ( $message == null )
	{
		$message = "";
	}
	else if ( getStringValue($message) == ""  )
	{
		$message = "Almost! Somethings worked, others did not.";
	}

	$payload = array();
	$payload['status'] = true;
	$payload["type"] = "warning"; // info, warning, danger
	$payload["message"] = getStringValue($message);
	$payload["href"] = "";

	if ( getStringValue($url) != "" )
	{
		$payload["href"] = $url;
		$payload["type"] = "redirect";

		$CI =& get_instance();
		$CI->session->set_flashdata('error', getStringValue($message));
		$CI->session->set_flashdata('error-type', "warning");
	}

	print json_encode($payload);
	exit;
}

/**
 * GetAppOption
 *
 * Application options are key/value pairs that are stored in the database.
 * Since CodeIgniter does not have a global application session, this serves
 * as that.
 *
 * This function will find the value for the key.  An empty string is returned
 * if not found.
 *
 * @param $key
 * @return string
 */
function GetAppOption( $key )
{
    $CI =& get_instance();
    $CI->load->model('AppOption_model');

    if ( getStringValue($key) == "" ) return "";
    return $CI->AppOption_model->select($key);
}

/**
 * SetAppOption
 *
 * Application options are key/value pairs that are stored in the database.
 * Since CodeIgniter does not have a global application session, this serves
 * as that.
 *
 * This function will set an application value by key.
 *
 * @param $key
 * @param $value
 */
function SetAppOption( $key, $value )
{
    $CI =& get_instance();
    if ( getStringValue($key) == "" ) return;
    return $CI->AppOption_model->upsert($key, $value);
}

/**
 * RemoveAppOption
 *
 * Application options are key/value pairs that are stored in the database.
 * Since CodeIgniter does not have a global application session, this serves
 * as that.
 *
 * This function will remove an application option by key.
 *
 * @param $key
 */
function RemoveAppOption( $key )
{
    $CI =& get_instance();
    if ( getStringValue($key) == "" ) return;
    return $CI->AppOption_model->delete($key);
}


/**
 * GetMappedObjectLookup
 *
 * This function will return a list of key/value pairs from the
 * ObjectMapping by supplied code.  If not found, an empty array
 * is returned.
 *
 * @param $code
 * @return array
 */
function GetMappedObjectLookup($code)
{
    $CI =& get_instance();
    if ( ! IsMappedObject($code) ) return array();
    $result = $CI->ObjectMapping_model->get_mapping_lookup($code);
    $lookup = array();
    foreach($result as $row)
    {
        $lookup[GetArrayStringValue("Input", $row)] = GetArrayStringValue("Output", $row);
    }
    return $lookup;
}

/**
 * GetMappedObject
 *
 * This function will search the ObjectMapping table.  It will look for
 * a matching key ( $value ) in the specified grouping ( $code ).  By default
 * the search is case sensitive, you you may optionally change that
 * with the third parameter.
 *
 * @param $code
 * @param $value
 * @param bool $case_sensitive
 * @return mixed
 * @throws Exception
 */
function GetMappedObject($code, $value, $case_sensitive=true)
{
    $CI =& get_instance();
    if ( ! IsMappedObject($code) ) throw new Exception("Unknown object type.");
    $result = $CI->ObjectMapping_model->get_mapping($code, $value, $case_sensitive);
    return $result;
}

/**
 * IsMappedObject
 *
 * This function will return TRUE or FALSE and will tell you if the
 * code provided is a valid known grouping.  Case matters.
 *
 * @param $code
 * @return mixed
 */
function IsMappedObject($code)
{
    $CI =& get_instance();
    return $CI->ObjectMapping_model->is_valid_object_type($code);
}

/**
 * FormatBytes
 *
 * Given some number of bytes, return the value as a formatted
 * string where we display the bytes converted into its largest
 * unit.
 *
 * @param $bytes
 * @param int $precision
 * @return string
 */
function FormatBytes($bytes, $precision = 2)
{
    $units = array("b", "kb", "mb", "gb", "tb");

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . " " . $units[$pow];
}

/**
 * ReadTheFile
 *
 * Provide this function a handle to read a file using an iterator.
 * This is a memory efficient way to read a large file!
 *
 * @param $handle
 * @return Generator
 */
function ReadTheFile($handle)
{
    while(!feof($handle)) {
        yield trim(fgets($handle));
    }
}
/* End of file app_helper.php */
/* Location: ./application/helpers/app_helper.php */
