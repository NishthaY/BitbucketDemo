<?php

class Upload_model extends CI_Model {

    function __construct()
    {
        parent::__construct();

        $this->db = $this->load->database('default', TRUE);

    }

    function get_upload_records() {
        return $this->get_top_upload_records(null);
    }

    function get_top_upload_records($row_count=10) {

        $upload_results = GetSessionObject("upload_results");
        $filename = getArrayStringValue("full_path", $upload_results);

        ini_set('auto_detect_line_endings', 1);			// Handle MicroSuck newlines.

        $output = array();
        $row = 0;
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while ( ($data = fgetcsv($handle) ) !== FALSE) {
                $num = count($data);
                $row++;
                if ($row != 1) {
                    $query_data = array();
                    for ($c=0; $c < $num; $c++) {
                        $query_data["col{$c}"] = $data[$c];
                    }
                    $output[] = $query_data;
                }
                if ($row_count != null && $row == $row_count )
                {
                    break;
                }
            }

            // Add blank rows if we don't have our minimum
            if( $row_count != null && true )
            {
                while ( $row <= $row_count )
                {
                    $row++;
                    for($c=0;$c<$num;$c++)
                    {
                        $query_data["col{$c}"] = "&nbsp;";
                    }
                    $output[] = $query_data;
                }
            }

        }
        return $output;
    }

}


/* End of file Upload_model.php */
/* Location: ./system/application/models/Upload_model.php */
