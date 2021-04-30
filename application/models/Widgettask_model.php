<?php

class Widgettask_model extends CI_Model {

    function __construct()
    {
        parent::__construct();

        $this->db = $this->load->database('default', TRUE);

    }
    public function task_config( $task_name ){

        // task_config
        //
        // Background tasks and widgets repeatedly call our server.  This
        // function will return the configuration data for these types of
        // objects and tell the component how to behave.
        // ----------------------------------------------------------------


        // Pull the configuration data from the database.
        $file = "database/sql/widgettask/SelectBackgroundTask.sql";
        $vars = array(
            getStringValue($task_name)
        );
        $results = GetDBResults( $this->db, $file, $vars );

        // We expect exactly one configuration.  If we don't find that, return
        // a hard coded config which will turn the component off.
        if ( count($results) == 1 ) {
            $results = $results[0];
        }else{

            // If we dont' have exactly 1 row, then we will default the widget config to OFF.
            $results = array();
            $results["refresh_minutes"] = 5;		// Default refresh;
            $results["refresh_enabled"] = 0;		// Do not refresh
            $results["debug"] = 0;
            $results["info"] = 0;

        }

        // DEBUG: The config settings has a debug_user value.  If that value
        // contains the username of the currently logged in user, set the debug value to on.
        // Debug allows you to see in detail everything that is happening during the widgettask execution path.
        $debug = getArrayStringValue("debug_user", $results);
        unset($results["debug_user"]);
        $results["debug"] = 0;
        if ( strpos( $debug, GetSessionValue("email_address")) !== FALSE ) {
            $results["debug"] = 1;
        }

        // INFO: The config settings has a info_user value.  If that value
        // contains the username of the currently logged in user, set the info value to on.
        // Info allows you watch widgettask execution path.
        $debug = getArrayStringValue("info_user", $results);
        unset($results["info_user"]);
        $results["info"] = 0;
        if ( strpos( $debug, GetSessionValue("email_address")) !== FALSE ) {
            $results["info"] = 1;
        }

        return $results;

    }


}


/* End of file Widgettask_model.php */
/* Location: ./system/application/models/Widgettask_model.php */
