<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Support extends SecureController {


	function __construct(){
		parent::__construct();
	}

	// SCREENS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	// POST +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
	function clear_job_alert() {
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_write") ) throw new SecurityException("Missing required permission.");



			// Validate our inputs.
			if ( getStringValue("jobid", $_POST) == "" ) throw new UIException("Invalid or missing inputs.");

			// Clean our inputs.
			$jobid = trim(getArrayStringValue("jobid", $_POST));

			// Record this action.
			$this->History_model->insert_history_failedjob($jobid);

			// Audit this action.
            $payload = $this->Queue_model->get_job($jobid);
			AuditIt("Failed job message was cleared.", $payload);

			AJAXSuccess("Done.");


		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }

	}

	function reset_dyno( $dyno_name ) {
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_write") ) throw new SecurityException("Missing required permission.");

			// Validation
			if ( getStringValue($dyno_name) == "" ) throw new UIException("Missing required input dyno_name");


			$this->HerokuDynoRequest_model->restart_dyno( APP_NAME, $dyno_name );
			AJAXSuccess("Done.");

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	function stop_dyno( $dyno_name ) {
		try
		{
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected request method.");
			if ( getArrayStringValue("ajax", $_POST) != "1" ) throw new Exception("Javascript is required.");

			// Security Check!
			// This function requires that you be authenticated in order to use it.
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_write") ) throw new SecurityException("Missing required permission.");

			// Validation
			if ( getStringValue($dyno_name) == "" ) throw new UIException("Missing required input dyno_name");

			// Stop the dyno.
			$this->HerokuDynoRequest_model->stop_dyno( APP_NAME, $dyno_name );

			// Look at the running jobs.  If you find one that has a process id that
            // matches the dyno, clean it up.
			$running_jobs = $this->Queue_model->get_running_jobs();
			foreach($running_jobs as $job)
            {
                $process_id = GetArrayStringValue('ProcessId', $job);
                if ( $process_id === $dyno_name )
                {
                    $display_name = GetSessionValue("display_name");
                    $job_id = GetArrayStringValue('JobId', $job);
                    $this->Queue_model->fail_job($job_id, "A2P-INTERNAL: Job manually terminated by {$display_name}");
                    break;
                }

            }


			AJAXSuccess("Done.");

		}
		catch ( UIException $e ) { AjaxDanger($e->getMessage()); }
		catch( SecurityException $e ) { AccessDenied(); }
		catch( Exception $e ) { Error404(); }
	}
	function decode_data()
    {
        try
        {

            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) !== "POST" ) throw new SecurityException("Unsupported request method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");

            // Collect our encrypted entity and validate it.
            $encrypted_entity = GetArrayStringValue('encrypted_entity', $_POST);
            $type = strtoupper(fLeft($encrypted_entity, "_"));
            $id = fRight($encrypted_entity, "_");

            $valid_type = false;
            if ( ! $valid_type && $type === 'COMPANY' ) $valid_type = true;
            if ( ! $valid_type && $type === 'COMPANYPARENT' ) $valid_type = true;
            if ( ! $valid_type && $type === 'APP' ) $valid_type = true;
            if ( ! $valid_type ) throw new Exception("Unknown encryption entity.");

            if ( $type !== 'APP' && StripNonNumeric($id) !== $id ) throw new Exception("Malformed encrypted_entity.");
            if ( $type === 'APP' && strtoupper($id) !== strtoupper(APP_NAME) ) throw new Exception("Malformed encrypted_entity.");

            // Collect our encrypted data.
            $encrypted_data = GetArrayStringValue('data', $_POST);
            $encrypted_data = trim($encrypted_data, "\n\r");
            $encrypted_data = ltrim($encrypted_data);
            if ( ! IsEncryptedString($encrypted_data) ) throw new Exception("Does not appear to be encrypted text.");

            // Try and decrypt the key
            $encryption_key = EMPTY_ENCRYPTION_KEY;
            if ( $type === 'COMPANY' ) $encryption_key = GetCompanyEncryptionKey($id);
            if ( $type === 'COMPANYPARENT' ) $encryption_key = GetCompanyParentEncryptionKey($id);
            if ( $type === 'APP' ) $encryption_key = A2PGetEncryptionKey();
            $decrypted_text = A2PDecryptString($encrypted_data, $encryption_key);
            if ($decrypted_text === 'FALSE' ) throw new Exception("Unable to decrypt.\nDid you select the correct company/parent/app?\nWas the encrypted string generated with " . APP_NAME . "?");

            $array = array();
            $array['responseText'] = $decrypted_text;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    function encode_data()
    {
        try
        {

            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) !== "POST" ) throw new SecurityException("Unsupported request method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");

            // Collect our encrypted entity and validate it.
            $encrypted_entity = GetArrayStringValue('encrypted_entity', $_POST);
            $type = strtoupper(fLeft($encrypted_entity, "_"));
            $id = fRight($encrypted_entity, "_");

            $valid_type = false;
            if ( ! $valid_type && $type === 'COMPANY' ) $valid_type = true;
            if ( ! $valid_type && $type === 'COMPANYPARENT' ) $valid_type = true;
            if ( ! $valid_type && $type === 'APP' ) $valid_type = true;
            if ( ! $valid_type ) throw new Exception("Unknown encryption entity.");

            if ( $type !== 'APP' && StripNonNumeric($id) !== $id ) throw new Exception("Malformed encrypted_entity.");
            if ( $type === 'APP' && strtoupper($id) !== strtoupper(APP_NAME) ) throw new Exception("Malformed encrypted_entity.");

            // Collect our clear text
            $clear_text = GetArrayStringValue('data', $_POST);
            if ( IsEncryptedString($clear_text) ) throw new Exception("Does not appear to be clear text.");

            // Try and encrypt the data
            $encryption_key = EMPTY_ENCRYPTION_KEY;
            if ( $type === 'COMPANY' ) $encryption_key = GetCompanyEncryptionKey($id);
            if ( $type === 'COMPANYPARENT' ) $encryption_key = GetCompanyParentEncryptionKey($id);
            if ( $type === 'APP' ) $encryption_key = A2PGetEncryptionKey();
            $encrypted_text = A2PEncryptString($clear_text, $encryption_key);
            if ($encrypted_text === 'FALSE' ) throw new Exception("Unable to encrypt.  Try again later.");

            $array = array();
            $array['responseText'] = $encrypted_text;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    function keypool_create()
    {
        try
        {

            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) !== "POST" ) throw new SecurityException("Unsupported request method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_write") ) throw new SecurityException("Missing required permission.");

            // No no no!  You can't create a key if you are in prodcopy.
            if ( APP_NAME === 'a2p-prodcopy' ) throw new Exception("You may not create new encryption keys in a2p-prodcopy.");

            $key_id = "";
            $cmk    = array();
            $alias  = "";
            try
            {
                $company_id = GetSessionValue('company_id');
                $user_id = GetSessionValue('user_id');

                // Generate a security key in the pool.
                CreateSecurityKey($company_id, $user_id);

            }
            catch(Exception $e)
            {
                LogIt("KeyPool Error", $e->getMessage(), null, GetSessionObject('user_id'), GetSessionValue('company_id'));

                // Retire the key in KMS, as the creation attempt failed.
                if (! empty($cmk) && GetStringValue($alias) !== '' )
                {
                    KMSScheduleAliasForDeletion($alias, 7 );
                    LogIt("KeyPool Error", "Cleaning up key in KMS", $alias, GetSessionObject('user_id'), GetSessionValue('company_id'));
                }

                // Remove the key from the keypool on error.
                if (GetStringValue($key_id) !== '')
                {
                    $this->Support_model->delete_keypool_by_id = $key_id;
                    LogIt("KeyPool Error", "Cleaning up key in pool", "Id[$key_id]", GetSessionObject('user_id'), GetSessionValue('company_id'));
                }

                // Report the Error.
                throw $e;
            }


            $array = array();
            $array['responseText'] = "ok";
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
	function render_dyno_details_form( $dyno_name ) {
		try
		{

			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_write") ) throw new SecurityException("Missing required permission.");

			// organize inputs.
			$dyno_name = getStringValue($dyno_name);

			// validate required inputs.
			if ( $dyno_name == "" ) throw new Exception("Invalid input dyno_name");
			if ( strpos($dyno_name, ".") === FALSE ) throw new Exception("Invalid dyno name");

			$pid = fRightBack($dyno_name, ".");
			$details = $this->Support_model->select_job_details_by_pid($dyno_name);

			$form = new UIModalForm("dyno_details_form", "dyno_details_form", base_url("support/dyno/details"));
			$form->setTitle("Dyno Details ( {$dyno_name} )");
			$form->setCollapsable(true);
			$form->addElement($form->htmlView("support/job_details",$details));
			$form->addElement($form->button("no_btn", "Okay", "btn-primary pull-right"));
			$form_html = $form->render();

			$array = array();
			$array['responseText'] = $form_html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}

	// RENDERS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    function render_keypool()
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_write") ) throw new SecurityException("Missing required permission.");

            $count = $this->Support_model->count_ready_keypool_keys();

            $view_array = array();
            $view_array['count'] = $count;
            $html = RenderViewAsString("support/keypool", $view_array);


            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
	function render_app_options() {
		try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_write") ) throw new SecurityException("Missing required permission.");

			$one_off = GetAppOption(ONE_OFF_DYNO_SUPPORT_ENABLED);
			if ( $one_off == "" ) $one_off = "FALSE";
			$one_off = strtoupper($one_off);

			$dynos = GetAppOption(DYNO_SUPPORT_ENABLED);
			if ( $dynos == "" ) $dynos = "FALSE";
			$dynos = strtoupper($dynos);

			$rest = GetAppOption(REST_SECONDS_BETWEEN_QUERIES);
			if ( $rest == "" ) $rest = "";
			$rest = strtoupper($rest);

			$items = array();
			$items[] = array("label"=>"Dynos Enabled","value"=>$dynos, "icon"=>"fa fa-mixcloud");
			$items[] = array("label"=>"One-Off Dynos Enabled","value"=>$one_off, "icon"=>"fa fa-cloud");
            $items[] = array("label"=>"Rest Seconds Between Queries","value"=>$rest, "icon"=>"fa fa-clock-o");

			$view_array = array();
			$view_array = array_merge($view_array, array("items" => $items));

			$html = RenderViewAsString("support/app_options", $view_array);


            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
	}
    function render_pg_options() {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated("support_write") ) throw new SecurityException("Missing required permission.");

            $work_mem = $this->Tuning_model->work_mem();
            $memory_limit = ini_get('memory_limit');

            $items = array();
            $items[] = array("label"=>"PG work_mem","value"=>$work_mem, "icon"=>"fa fa-database");
            $items[] = array("label"=>"PHP memory_limit","value"=>$memory_limit, "icon"=>"fa fa-database");

            $view_array = array();
            $view_array = array_merge($view_array, array("items" => $items));

            $html = RenderViewAsString("support/pg_options", $view_array);


            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
	function render_director_details() {
		// render_director_details
		//
		// Generate the HTML for a cardbox what indicates the status and
		// settings of the queue director.
		// ------------------------------------------------------------------

        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method;");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_write") ) throw new SecurityException("Missing required permission.");

            // validate required inputs.
            //    nothing to do here.

			$max_job_runtime = GetConfigValue("max_job_runtime", "queue");
			if ( $max_job_runtime != "" )
			{
				$max_job_runtime = getIntValue($max_job_runtime);
				$max_job_runtime = $max_job_runtime / SECONDS_PER_MINUTE;
				$max_job_runtime .= " min";
			}

			$failure_check = GetConfigValue("failure_check", "queue");
			if ( $failure_check != "" )
			{
				$failure_check = getIntValue($failure_check);
				$failure_check = $failure_check / SECONDS_PER_MINUTE;
				$failure_check .= " min";
			}

			$reboot_check = GetConfigValue("reboot_check", "queue");
			if ( $reboot_check != "" )
			{
				$reboot_check = getIntValue($reboot_check);
				$reboot_check = $reboot_check / SECONDS_PER_MINUTE;
				$reboot_check .= " min";
			}

			$reboot_window_start = GetConfigValue("reboot_window_start");
			$reboot_window_end = GetConfigValue("reboot_window_end");


			$reboot_status = "danger";
			$last_reboot = GetAppOption(DATE_OF_LAST_WORKER_REBOOT);
			if ( $last_reboot != "" )
			{
		        $last_reboot = new DateTime($last_reboot, new DateTimeZone(PREFERED_TIMEZONE));
		        $one_day_ago = new DateTime(null, new DateTimeZone(PREFERED_TIMEZONE));
		        $oneDayPeriod = new DateInterval('P1D'); //period of 1 day
		        $one_day_ago->sub($oneDayPeriod);

		        if ( $last_reboot > $one_day_ago ) $reboot_status = "success";
			}
			if ( ! HasDynoSupport() ) $reboot_status = "unavailable";

			$view_array = array();
			$view_array = array_merge($view_array, array("max_jobs" => MAX_ASYNC_JOBS));
			$view_array = array_merge($view_array, array("max_job_runtime" => $max_job_runtime ));
			$view_array = array_merge($view_array, array("failure_check" => $failure_check ));
			$view_array = array_merge($view_array, array("reboot_check" => $reboot_check ));
			$view_array = array_merge($view_array, array("reboot_window_start" => $reboot_window_start ));
			$view_array = array_merge($view_array, array("reboot_window_end" => $reboot_window_end ));
			$view_array = array_merge($view_array, array("status" => GetDirectorStatus() ));
			$view_array = array_merge($view_array, array("reboot_status" => $reboot_status ));

            $html = RenderViewAsString("support/director_status", $view_array);

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
	}
	function render_dynos() {
		try
		{

			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_write") ) throw new SecurityException("Missing required permission.");

			$dynos = GetAppOption(DYNO_SUPPORT_ENABLED);
			if ( $dynos == "" ) $dynos = "FALSE";
			$dynos = strtoupper($dynos);


			$data = array();
			$dynos = $this->HerokuDynoRequest_model->get_dynos(APP_NAME);
			foreach($dynos as $dyno)
			{

				$id = getArrayStringValue("id", $dyno);
				$name = getArrayStringValue("name", $dyno);
				$size = getArrayStringValue("size", $dyno);
				$type = getArrayStringValue("type", $dyno);
				$date = getArrayStringValue("updated_at", $dyno);
				$state = getArrayStringValue("state", $dyno);
				$command = getArrayStringValue("command", $dyno);
				if ( $type == "run" && $command == "bash" ) continue;

				$revision = "";
				if ( isset($dyno["release"] ) )
				{
					$revision = getArrayStringValue("version", $dyno['release']);
				}


				// Format the date.
				$prefered_zone = GetConfigValue("timezone_display");
				$d = new DateTime($date);
				if ( $prefered_zone != "" ) $d->setTimezone(new DateTimeZone($prefered_zone));
				$date = $d->format("m/d/Y h:i:s A");

				$item = array();
				$item["Id"] = $id;
				$item["Name"] = $name;
				$item["Type"] = $type;
				$item["Size"] = $size;
				$item["Updated"] = $date;
				$item["State"] = $state;
				$item["Revision"] = $revision;



				$data[] = $item;
			}

			$view_array = array();
			$view_array = array_merge($view_array, array("data" => $data));

			$html = RenderViewAsString("support/dynos_widget", $view_array);


			$array = array();
			$array['responseText'] = $html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
    function render_failed_jobs()
    {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");

            // validate required inputs.
            //    nothing to do here.

            $count = $this->Support_model->count_failed_jobs();
            $html = RenderViewAsString("support/failed_jobs", array("count" => $count));

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }


    }
    function render_waiting_jobs() {

        // render_waiting_jobs
		//
		// Generate the HTML for a small placard what indicates how many
        // jobs are currently waiting.
		// ------------------------------------------------------------------

        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");

            $count = $this->Support_model->count_waiting_jobs();
            $html = RenderViewAsString("support/waiting_jobs", array("count" => $count));

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }


    }
    function render_running_jobs() {

        // render_running_jobs
		//
		// Generate the HTML for a small placard what indicates how many
        // jobs are currently running.
		// ------------------------------------------------------------------

        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");

            $count = $this->Support_model->count_running_jobs();
            $html = RenderViewAsString("support/running_jobs", array("count" => $count));

            $array = array();
            $array['responseText'] = $html;
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
	function render_running_jobs_table() {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");

			// validate required inputs.
			//    nothing to do here.

			$data = $this->Support_model->select_running_jobs();
			$html = RenderViewAsString("support/running_jobs_table", array("data" => $data));

			$array = array();
			$array['responseText'] = $html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
	function render_waiting_jobs_table() {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");

			// validate required inputs.
			//    nothing to do here.

			$data = $this->Support_model->select_waiting_jobs();
			$html = RenderViewAsString("support/waiting_jobs_table", array("data" => $data));

			$array = array();
			$array['responseText'] = $html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
	function render_failed_jobs_table() {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");

			// validate required inputs.
			//    nothing to do here.

			$data = $this->Support_model->select_failed_jobs();
			$html = RenderViewAsString("support/failed_jobs_table", array("data" => $data));

			$array = array();
			$array['responseText'] = $html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}
	function render_job_details_form($job_id) {
		try
		{
			// Check method.
			if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

			// Check Security
			if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
			if ( ! IsAuthenticated("support_read") ) throw new SecurityException("Missing required permission.");

			// organize inputs.
			$job_id = getStringValue($job_id);

			// validate required inputs.
			if ( $job_id == "" ) throw new Exception("Invalid input job_id");

			$details = $this->Support_model->select_job_details($job_id);


			$form = new UIModalForm("job_details_form", "job_details_form", base_url("support/job/details"));
			$form->setTitle("Job Details ( {$job_id} )");
			$form->setCollapsable(true);
			$form->addElement($form->htmlView("support/job_details",$details));
			$form->addElement($form->textarea("error_message", "Message", getArrayStringValue("message", $details), "10"));
			$form->addElement($form->button("no_btn", "Okay", "btn-primary pull-right"));
			$form_html = $form->render();

			$array = array();
			$array['responseText'] = $form_html;
			AJAXSuccess("", null, $array);

		}
		catch (Exception $e)
		{
			AJAXDanger($e->getMessage());
		}
	}



    function render_decode_data_widget() {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method.");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");


            $form = new UISimpleForm('decode_data_form', 'decode_data_form', base_url("support/decode_data"));
            $encrypted_entity = new Select2("simple");
            $encrypted_entity->setId("encrypted_entity");

            $encrypted_entity->addItem("Application", "Global Application Key (" . APP_NAME . ")", "app_" . APP_NAME);

            $companies = $this->Company_model->get_all_companies();
            foreach($companies as $company)
            {
                $key = "company_" . GetArrayStringValue("company_id", $company);
                $description = GetArrayStringValue('company_name', $company);
                $encrypted_entity->addItem("Companies", $description, $key);
            }

            $parents = $this->CompanyParent_model->get_all_parents();
            foreach($parents as $parent)
            {
                $key = "companyparent_" . GetArrayStringValue("Id", $parent);
                $description = GetArrayStringValue('Name', $parent);
                $encrypted_entity->addItem("Parents", $description, $key);
            }
            $form->addElement($encrypted_entity);
            $form->addElement($form->textarea('decode_textarea', 'Money for Nothing.','',3,'Paste encrypted text here.',false));
            $form = $form->render();



            $view_array = array();
            $view_array = array_merge($view_array, array('form' => $form) );

            $array = array();
            $array['responseText'] = RenderViewAsString("support/decode_data_widget", $view_array);
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }
    function render_encode_data_widget() {
        try
        {
            // Check method.
            if ( getStringValue($this->input->server('REQUEST_METHOD')) != "POST" ) throw new SecurityException("Unexpected method");

            // Check Security
            if ( ! IsLoggedIn() ) throw new SecurityException("You must be logged into access this function.");
            if ( ! IsAuthenticated() ) throw new SecurityException("Missing required permission.");


            $form = new UISimpleForm('encode_data_form', 'encode_data_form', base_url("support/encode_data"));
            $encrypted_entity = new Select2("simple");
            $encrypted_entity->setId("encrypted_entity");

            $encrypted_entity->addItem("Application", "Global Application Key (" . APP_NAME . ")", "app_" . APP_NAME);

            $companies = $this->Company_model->get_all_companies();
            foreach($companies as $company)
            {
                $key = "company_" . GetArrayStringValue("company_id", $company);
                $description = GetArrayStringValue('company_name', $company);
                $encrypted_entity->addItem("Companies", $description, $key);
            }

            $parents = $this->CompanyParent_model->get_all_parents();
            foreach($parents as $parent)
            {
                $key = "companyparent_" . GetArrayStringValue("Id", $parent);
                $description = GetArrayStringValue('Name', $parent);
                $encrypted_entity->addItem("Parents", $description, $key);
            }
            $form->addElement($encrypted_entity);
            $form->addElement($form->textarea('encode_textarea', '','',3,'Paste text to be encoded here.',false));
            $form = $form->render();



            $view_array = array();
            $view_array = array_merge($view_array, array('form' => $form) );

            $array = array();
            $array['responseText'] = RenderViewAsString("support/encode_data_widget", $view_array);
            AJAXSuccess("", null, $array);

        }
        catch (Exception $e)
        {
            AJAXDanger($e->getMessage());
        }
    }






}
