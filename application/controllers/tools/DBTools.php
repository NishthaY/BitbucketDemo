<?php defined('BASEPATH') OR exit('No direct script access allowed');

require('vendor/autoload.php');
include_once ( APPPATH . "controllers/tools/Tool.php" );

class DBTools extends Tool
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * CountCompanyRecords
     *
     * This function will take in a company id and then scan all of our databsae
     * tables looking for a table that has a column CompanyId.  If the value
     * in that column matches the input company_id, we will count th number
     * of rows in that table.  This tool can be used to verify if a rollback
     * was clean or not.
     *
     * @param int $company_id
     */
    public function CountCompanyRecords( $company_id = 32 )
    {
        $this->db = $this->load->database('default', TRUE);

        $sql = 'select * from "Company" where "Id" = ?';
        $vars = array( $company_id );
        $results = GetDBResults( $this->db, $sql, $vars );
        if ( count($results) == 0 )
        {
            print "No company found for id [{$company_id}]\n";
            exit;
        } else if ( count($results) > 1 )
        {
            print "Found too many companies for that id! [{$company_id}]\n";
            exit;
        }
        else
        {
            print "Company: " . GetArrayStringValue("CompanyName", $results[0]) . "\n";
            print "-------\n";
        }



        $sql = "SELECT * FROM information_schema.columns WHERE table_schema = ? AND column_name = ?";
        $vars = array( 'public', 'CompanyId' );
        $results = GetDBResults( $this->db, $sql, $vars );

        foreach($results as $item)
        {
            $table = GetArrayStringValue('table_name', $item);

            $replacefor = array();
            $replacefor['{TABLENAME}'] = $table;
            $sql = 'select count(*) as count from "{TABLENAME}" where "CompanyId" = ?';
            $vars = array( $company_id );
            $results2 = GetDBResults( $this->db, $sql, $vars, $replacefor );
            $count = getArrayStringValue('count', $results2[0]);
            print $count . ": " . $table . "\n";
        }

    }
}

/* End of file CompanyTools.php */
/* Location: ./application/controllers/cli/EmailTool.php */
