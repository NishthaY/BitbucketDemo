<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EchoBatchImport extends A2PWizardStep
{
	private 	$handles;			// Array of file handles used to read our data.

	function __construct()
    {
        // Construct our parent class
        parent::__construct();

        //CLI ONLY! GO AWAY!
        if ( ! $this->input->is_cli_request() ) {
            Error404();
            return;
        }

    }

    function index( $user_id, $company_id='', $companyparent_id='', $job_id='' )
    {
		// index
        //
        //
		// ---------------------------------------------------------------

        $this->prefix  = "";

        try {

            if ( getStringValue($user_id) == "" ) throw new Exception("Invalid input user_id.");
            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");

			$this->prefix  = replaceFor(GetConfigValue("import_prefix"), "COMPANYID", $company_id);

			$items = S3ListFiles(S3_BUCKET, $this->prefix);
			foreach($items as $item)
			{
				$key = getArrayStringValue("Key", $item);
				if ( $key == "" ) continue;

				$filename = "s3://" . S3_BUCKET . "/" . $key;
				$this->sql_handle = fopen($filename, 'r');
				if ( ! $this->sql_handle ) throw new Exception("Could not create {$filename}");

				echo "BEGIN;\n";
				while ( ($line = fgets($this->sql_handle) ) !== false)
				{
					$line = trim($line);
					if ( getStringValue($line) != "" ) echo "{$line}\n";
				}
				fclose($this->sql_handle);
				echo "COMMIT;\n";

			}

        }catch(Exception $e) {
			echo "ROLLBACK;\n";
			// If we have an exception, we must attempt to close the files.
			$this->_close_files();

        }
    }

	private function _close_files() {
		if ( $this->sql_handle )
		{
			fclose($this->sql_handle);
		}
	}


}

/* End of file SaveUpload.php */
/* Location: ./application/controllers/cli/SaveUpload.php */
