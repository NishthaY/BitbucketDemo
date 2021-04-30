<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tool extends CI_Controller
{
    public $authenticated_user;
    public $company;
    public $report_year;
    public $report_month;

    protected       $timers;        // Turn timers on or off.
    protected 	    $timer_array;   // Collection of timers, if they are on.

    public function __construct()
    {
        // Construct our parent class
        parent::__construct();

        //CLI ONLY! GO AWAY!
        if ( ! $this->input->is_cli_request() ) {
            Error404();
            return;
        }

        $unsecured = [ 'a2p-dev'];
        if ( ! in_array(APP_NAME, $unsecured) )
        {
            $this->load->helper("auth");
            $this->load->model("User_model");

            // Collect the username.
            $username = readline("Username: ");

            // Collect the user object for this user.
            $this->authenticated_user = $this->User_model->get_user($username);
            $user_id = GetArrayStringValue("user_id", $this->authenticated_user);

            // Validate the user.  Only super users can run tools.
            if ( ! $this->User_model->is_super_user($user_id) ) $this->_fatal("Invalid privileges.");


            // Collect the user security pin.
            SendAuthSMSCode($user_id);
            $code = readline("Auth Token: ");
            $code = strtoupper($code);

            $login_details = $this->Login_model->get_login_details($user_id);

            // code any good?
            $stored_hash = getArrayStringValue("TwoFactorHash", $login_details);
            if ( ! password_verify($code, $stored_hash) ) $this->_fatal("Invalid code.");

            // Has the code expired?
            if ( $this->Login_model->has_two_factor_code_expired($user_id) ) $this->_fatal("Code has expired.");

            // All done!  empty out the hash as it was a one time use code.
            $this->Login_model->update_hash($user_id, null);

        }
        else
        {
            $this->authenticated_user = $this->User_model->get_user('brian@advice2pay.com');
            $user_id = GetArrayStringValue("user_id", $this->authenticated_user);
            if ( $user_id === '' ) throw new Exception("Unable to auto-authenticate user.");
        }


    }
    public function index()
    {
        $this->help();
    }
    public function help()
    {

        $path = APPPATH . '../docs/' . get_called_class() . '.txt';
        if ( file_exists( $path ) )
        {
            $help = file_get_contents($path);
            print "\n\n";
            print $help;
            exit;
        }
        print "No help file found.\nGood luck.\n";
    }
    protected function confirm($msg="")
    {
        system("clear");
        $app_name = APP_NAME;
        if ( GetStringValue($msg) !== "" )
        {
            $msg = trim($msg);
            print $msg . "\n";
        }
        $input = readline("Type the application name {$app_name} to proceed: ");
        if ( $input !== $app_name )
        {
            print "Operation cancelled.\n";
            exit;
        }
    }
    protected function getCompany()
    {
        system('clear');
        print "\n";
        print "Review the list of companies below and then at the command\n";
        print "prompt type in the company of your choice.\n";
        print "\n";
        $companies = $this->Company_model->get_all_companies();
        uasort($companies, 'AssociativeArraySortFunction_company_name');
        foreach($companies as $company)
        {
            print "  " . GetArrayStringValue("company_name", $company) . "\n";
        }
        $selected_company_name = readline("Company Name: ");

        $company = $this->Company_model->get_company_by_name($selected_company_name);
        $this->company = $company;
        return $company;
    }
    protected function getCompanyParent()
    {
        system('clear');
        print "\n";
        print "Review the list of company parents below and then at the command\n";
        print "prompt type in the item of your choice.\n";
        print "\n";
        $parents = $this->CompanyParent_model->get_all_parents();
        uasort($parents, 'AssociativeArraySortFunction_Name');
        foreach($parents as $parent)
        {
            print "  " . GetArrayStringValue("Name", $parent) . "\n";
        }
        $selected_companyparent_name = readline("Parent Company Name: ");

        $companyparent = $this->CompanyParent_model->get_parent_by_name($selected_companyparent_name);
        $this->companyparent = $companyparent;
        return $companyparent;
    }
    protected function getCompanyOrParent()
    {
        system('clear');
        print "\n";
        print "Review the list of items below and then at the command\n";
        print "prompt type in the item of your choice.\n";
        print "\n";
        $parents = $this->CompanyParent_model->get_all_parents();
        if ( ! empty($parents))
        {
            print " PARENT COMPANIES\n";
            uasort($parents, 'AssociativeArraySortFunction_Name');
            foreach($parents as $parent)
            {
                print "   " . GetArrayStringValue("Name", $parent) . "\n";
            }
            print "\n";
        }
        $companies = $this->Company_model->get_all_companies();
        if ( ! empty($companies) )
        {
            print " COMPANIES\n";
            uasort($companies, 'AssociativeArraySortFunction_company_name');
            foreach($companies as $company)
            {
                print "   " . GetArrayStringValue("company_name", $company) . "\n";
            }
            print "\n";
        }
        $item_name = readline("Company or Parent Name: ");

        $companyparent = $this->CompanyParent_model->get_parent_by_name($item_name);
        $company = $this->Company_model->get_company_by_name($item_name);

        if ( ! empty($companyparent) )
        {
            $this->companyparent = $companyparent;
            return $companyparent;
        }
        if ( ! empty($company) ) {
            $this->company = $company;
            return $company;
        }
        return array();
    }
    protected function getParentCompanyOrCompanies( $companyparent_id )
    {

        system('clear');
        print "\n";
        print "Review the list of companies below and then at the command\n";
        print "prompt type in the item of your choice.\n";
        print "\n";
        $companies = $this->CompanyParent_model->get_companies_by_parent($companyparent_id);

        uasort($companies, 'AssociativeArraySortFunction_company_name');
        foreach($companies as $company)
        {
            print "  " . GetArrayStringValue("company_name", $company) . "\n";
        }
        $selected_company_name = readline("Company Name or ALL: ");

        // If they selected 'All', return the collection!
        if ( $selected_company_name === 'ALL' ) return $companies;

        // If they selected just one, return that.
        $company = $this->Company_model->get_company_by_name($selected_company_name);
        $this->company = $company;
        return [ $company ];
    }
    protected function GetReportYear()
    {
        $done = false;
        while( ! $done )
        {
            system('clear');
            print "\n";
            print "At the command prompt, enter the report year.\n";
            $report_year = readline("Year (YYYY): ");

            $report_year = StripNonNumeric($report_year);
            if ( strlen($report_year) === 4 ) $done = true;
        }
        $this->report_year = $report_year;
        return $report_year;

    }
    protected function GetReportMonth()
    {
        $done = false;
        while ( ! $done )
        {
            system('clear');
            print "\n";
            print "At the command prompt, enter the report month.\n";
            $report_month = readline("Month (MM): ");

            $report_month = StripNonNumeric($report_month);
            $report_month = str_pad($report_month, 2, '0', STR_PAD_LEFT);
            $report_month = substr($report_month, -2);
            if ( strlen($report_month) === 2 ) $done = true;
        }
        $this->report_month = $report_month;
        return $report_month;
    }
    private function _fatal($message)
    {
        $message = GetStringValue($message);
        $message = trim($message);

        print "ABORT: " . $message . PHP_EOL;
        exit;
    }
    protected function timer($code)
    {

        // timer
        //
        // Each time this function is called, it will report how long
        // the previous item had been running.  If you pass in "end" then
        // then you will get a summary of the full runtime from first timer
        // to "end".
        //
        // Works independently from DEBUG.  Debug does not imply timers.
        // timers will write to STDOUT even if debug is off.
        // ------------------------------------------------------------

        if (! $this->timers) return;

        if ( ! $this->timer_array ) $this->timer_array = array();
        if ( count($this->timer_array) === 0 ) {
            $this->timer_array[$code] = time();
        }elseif ( $code === 'end' ) {

            if ( ! empty($this->timer_array) )
            {
                $keys = array_keys($this->timer_array);
                $first_key = $keys[0];

                $output = "";
                print "---\n";
                $seconds = round(abs(time() - $this->timer_array[$first_key]),2);
                $minutes = round(abs(time() - $this->timer_array[$first_key]) / 60,2);
                if ( $minutes < 1 ) {

                    if ( $seconds < 0 ) $output = "< 1 second\n";
                    if ( $seconds > 0 ) $output =  "{$seconds} second(s)\n";
                }else{
                    $output = "{$minutes} minute(s)\n";
                }
                if ( $output !== '' ) print $output;

                // The user told us to end.  smoke the timer array.
                $this->timer_array = array();

                return trim($output);
            }

        }
        else
        {

            $keys = array_keys($this->timer_array);
            $last_key_index = count($keys) - 1;
            $last_key = $keys[$last_key_index];

            $seconds = round(abs(time() - $this->timer_array[$last_key]),2);
            $minutes = round(abs(time() - $this->timer_array[$last_key]) / 60,2);

            if ( $minutes < 1 ) {
                if ( $seconds <= 0 ) print "Timer [{$code}]: < 1 second\n";
                if ( $seconds > 0 ) print "Timer [{$code}]: {$seconds} second(s)\n";
            }else{
                print "Timer [{$code}]: {$minutes} minute(s)\n";
            }

            $this->timer_array[$code] = time();
        }
    }

}

/* End of file Tool.php */
/* Location: ./application/controllers/cli/Tool.php */
