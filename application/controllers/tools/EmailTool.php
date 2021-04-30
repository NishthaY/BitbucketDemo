<?php defined('BASEPATH') OR exit('No direct script access allowed');

include_once ( APPPATH . "controllers/tools/Tool.php" );
class EmailTool extends Tool
{
    /**
     * sample
     *
     * This function will generate all of the various emails that we might send to a user.
     * The user running this tool will receive sample copies delivered to the email address
     * associated with their A2P account.
     *
     * Emails are generated at the company and companyparent levels, so you must supply the
     * identifier type to indicate which you are trying to generate.
     *
     * Company or CompanyParent selection is interactive.
     *
     * @param $type
     */
    public function sample( $identifier_type )
    {
        if ( $identifier_type == 'company' ) $this->sample_company();
        if ( $identifier_type == 'companyparent' ) $this->sample_companyparent();

    }

    /**
     * sample_company
     *
     * Interactively select a company and then generate and deliver sample copies of all
     * emails to the email address associated with the user running the support tool.
     */
    public function sample_company( )
    {
        try
        {
            $user_id = GetArrayStringValue("user_id", $this->authenticated_user);
            if ( $user_id === '' )
            {
                print "You must be authenticated to use this tool.\n";
                exit;
            }

            system("clear");
            print "This tool will send sample emails to the user specified formatted for\n";
            print "a specific company.  Press any key to continue or <ctrl-c> to quit.\n";
            readline("");

            system("clear");
            print "Processing ...\n";

            $company = $this->getCompany();
            $company_name = getArrayStringValue("company_name", $company);
            $company_id = getArrayStringValue("company_id", $company);

            SendWelcomeEmail($user_id, "Password Goes Here", $company_name);
            SendPasswordResetEmail( $user_id, "Password Goes Here");
            SendDataValidationCompleteEmail($user_id, $company_id);
            SendDataValidationFailedEmail( $user_id, $company_id );
            SendUploadCompleteEmail($user_id, $company_id);
            SendUploadFailedEmail($user_id, $company_id);
            SendDraftReportsGeneratedEmail($user_id, $company_id);
            SendDraftReportsFailedEmail($user_id, $company_id);

            // SUPPORT EMAILS
            // There are several support emails too.  Let's try and craft samples of those.
            $job = $this->Queue_model->get_most_recent_job();
            if( ! empty($job) )
            {
                $job_id = GetArrayStringValue('Id', $job);
                $warnings = ['This is a test warning'];
                $audit = ['This is a test audit message'];
                SendBackgroundJobReportEmail( $company_id, 'company', $job_id, $user_id, $warnings, $audit );
            }

            $companyparent_id = GetCompanyParentId($company_id);
            SendSupportEmail( $company_id, $companyparent_id, "Testing emails.", '20190101000000', $user_id );
            SendFYISupportEmail( "FYI Test", "This has been a test of the FYI Support email." );

            print "done.\n";
        }
        catch(Exception $e)
        {
            print "Exception! " . $e->getMessage() . "\n";
        }

    }

    /**
     * sample_companyparent
     *
     * Interactively select a companyparent and then generate and deliver sample copies of all
     * emails to the email address associated with the user running the support tool.
     */
    public function sample_companyparent( )
    {
        try
        {
            $user_id = GetArrayStringValue("user_id", $this->authenticated_user);
            if ( $user_id === '' )
            {
                print "You must be authenticated to use this tool.\n";
                exit;
            }

            system("clear");
            print "This tool will send sample emails to the user specified formatted for\n";
            print "a specific companyparent.  Press any key to continue or <ctrl-c> to quit.\n";
            readline("");

            system("clear");
            print "Processing ...\n";

            $companyparent = $this->getCompanyParent();
            if ( !empty($companyparent) ) $companyparent = $companyparent[0];
            $companyparent_name = getArrayStringValue("Name", $companyparent);
            $companyparent_id = getArrayStringValue("Id", $companyparent);


            $company_id = null;
            SendSupportEmail( $company_id, $companyparent_id, "Testing emails.", '20190101000000', $user_id );

            // Upload.
            SendUploadCompleteEmail( $user_id, $company_id, $companyparent_id );
            SendUploadFailedEmail( $user_id, $company_id, $companyparent_id );

            // Parse
            SendParentUploadParseCSVFailed( $user_id, $company_id, $companyparent_id );

            // Validate
            SendDataValidationCompleteEmail( $user_id, $company_id, $companyparent_id );
            SendParentUploadValidateCSVFailed( $user_id, $company_id, $companyparent_id );

            // Map Companies
            SendParentUploadMapCompaniesWaiting( $user_id, $company_id, $companyparent_id );
            SendParentUploadMapCompaniesFailed( $user_id, $company_id, $companyparent_id );

            // Split
            SendParentUploadSplitCSVFailed( $user_id, $company_id, $companyparent_id );

            print "done.\n";
        }
        catch(Exception $e)
        {
            print "Exception! " . $e->getMessage() . "\n";
        }
    }

}

/* End of file EmailTool.php */
/* Location: ./application/controllers/cli/EmailTool.php */
