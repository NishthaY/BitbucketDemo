<?php

function GetMostRecentSnapshotFolder($identifier, $identifier_type, $prefix)
{
    $lookup = array();

    // Search the archive folder.  Assume the top level item in the archive folder
    // is a folder named with a snapshot tag ( CCYYMMDDHHMMSS ).  Extract this folder
    // name from every file returned from S3 in a lookup so at the end of the day
    // we have a unique list of "DIRS" found in the archive folder.

    $modified_prefix = fLeftBack($prefix, "/");
    $objects = S3ListFiles(S3_BUCKET, $modified_prefix);
    foreach($objects as $obj)
    {
        $key = GetArrayStringValue('Key', $obj);
        $key = replaceFor($key, $modified_prefix, "");
        $key = fRight($key, "/", $key);
        $dir = fLeft($key, "/", $key);
        $lookup[$dir] = true;
    }
    $dirs = array_keys($lookup);

    // Sort the dirs and pop the most recent.
    rsort($dirs);
    if ( count($dirs) !== 0 ) return GetArrayStringValue("0", $dirs);
    return FALSE;
}

/**
 * PruneSnapshotFolder
 *
 * A snapshot folder is an S3 bucket that holds a collection of folders that
 * are named with a snapshot number ( CCYYMMDDHHMMSS ).  This function will
 * collect all of the snapshots and order them DESC.  From there it will delete
 * any in the collection over MAX leaving the newest ones behind.
 *
 * @param $identifier
 * @param $identifier_type
 * @param $prefix
 * @param $max
 * @throws Exception
 */
function PruneSnapshotFolder($identifier, $identifier_type, $prefix, $max)
{
    $CI = &get_instance();

    $lookup = array();

    // Search the archive folder.  Assume the top level item in the archive folder
    // is a folder named with a snapshot tag ( CCYYMMDDHHMMSS ).  Extract this folder
    // name from every file returned from S3 in a lookup so at the end of the day
    // we have a unique list of "DIRS" found in the archive folder.
    //$archive_prefix = GetS3Prefix('archive', $identifier, $identifier_type);
    $modified_prefix = fLeftBack($prefix, "/");
    $objects = S3ListFiles(S3_BUCKET, $modified_prefix);
    foreach($objects as $obj)
    {
        $key = GetArrayStringValue('Key', $obj);
        $key = replaceFor($key, $modified_prefix, "");
        $key = fRight($key, "/", $key);
        $dir = fLeft($key, "/", $key);
        $lookup[$dir] = true;
    }
    $dirs = array_keys($lookup);

    // Sort the dirs descending.  Once we have counted PAST the max, start deleting
    // first the content of the folder and then the folder itself.
    rsort($dirs);
    for($i=0;$i<count($dirs);$i++)
    {
        if ( $i >= $max )
        {
            $dir = $dirs[$i];
            $modified_prefix = replaceFor($prefix, 'DATE', $dir);
            S3DeleteBucketContent(S3_BUCKET, $modified_prefix);
            S3DeleteFile(S3_BUCKET, $modified_prefix, $dir);
        }
    }
}
function TakeSnapshots($company_id, $user_id, $encryption_key)
{
    $CI = &get_instance();

    ArchiveColumnMappings($company_id, $user_id);
    ArchiveRelationshipSettings($company_id, $user_id);
    ArchiveLifeCompare($company_id, $user_id, $encryption_key);
    ArchivePlanSettings($company_id, $user_id);
    ArchiveManualAdjustments($company_id, $user_id);
    ArchiveReportReviewWarnings($company_id, $user_id);
    ArchiveCompanyFeatures($company_id, $user_id);

    // Audit the transaction.
    $company = $CI->Company_model->get_company($company_id);
    $payload = array();
    $payload = array_merge($payload, array('CompanyId' => $company_id));
    $payload = array_merge($payload, array('CompanyName' => GetArrayStringValue('company_name', $company)));
    AuditIt("Snapshot generated.", $payload);

}
function CopySnapshotForSupport( $identifier, $identifier_type, $user_id, $snapshot_tag, $ticket_id, $reason )
{
    try
    {
        $CI = &get_instance();
        $CI->config->load("app");

        // Required Variables.
        if ( getStringValue($identifier) == "" ) throw new Exception("Missing required input. identifier");
        if ( getStringValue($identifier_type) == "" ) throw new Exception("Missing required input. identifier_type");
        if ( getStringValue($user_id) == "" ) throw new Exception("Missing required input. user_id");
        if ( getStringValue($snapshot_tag) == "FALSE" ) throw new Exception("Invalid snapshot_tag. FALSE");
        if ( getStringValue($snapshot_tag) == "" ) throw new Exception("Missing required input. snapshot_tag");
        if ( getStringValue($ticket_id) == "" ) throw new Exception("Missing required input. ticket_id");

        $encryption_key = GetEncryptionKey($identifier, $identifier_type);
        if ( $encryption_key === '' ) throw new Exception("Missing encryption key.");

        if ( $identifier_type === 'company' )
        {
            $company_id = $identifier;
            $companyparent_id = GetCompanyParentId($company_id);
        }
        else if ( $identifier_type === 'companyparent' )
        {
            $company_id = "";
            $companyparent_id = $identifier;
        }


        S3GetClient();
        $support_prefix = GetS3Prefix('support', $identifier, $identifier_type);
        $support_prefix = replaceFor($support_prefix, 'TICKETID', $ticket_id);

        $archive_prefix = GetS3Prefix('archive', $identifier, $identifier_type);
        $archive_prefix = replaceFor($archive_prefix, 'COMPANYID', $company_id);
        $archive_prefix = replaceFor($archive_prefix, 'COMPANYPARENTID', $companyparent_id);
        $archive_prefix = replaceFor($archive_prefix, "DATE", $snapshot_tag);

        // Create the support bucket for this job.
        S3MakeBucketPrefix( S3_BUCKET, $support_prefix );
        S3DeleteBucketContent( S3_BUCKET, $support_prefix );

        S3MakeBucketPrefix( S3_BUCKET, "{$support_prefix}/upload");
        $files = S3ListFiles(S3_BUCKET, "{$archive_prefix}/upload");
        foreach($files as $file)
        {
            $filename = fRightBack(getArrayStringValue("Key", $file), "/");
            S3EncryptExistingFile( S3_BUCKET, "{$archive_prefix}/upload", $filename, "{$support_prefix}/upload", $filename );
        }

        S3MakeBucketPrefix( S3_BUCKET, "{$support_prefix}/json" );
        $files = S3ListFiles(S3_BUCKET, "{$archive_prefix}/json");
        foreach($files as $file)
        {
            $filename = fRightBack(getArrayStringValue("Key", $file), "/");
            S3EncryptExistingFile( S3_BUCKET, "{$archive_prefix}/json", $filename, "{$support_prefix}/json", $filename );
        }

        // Save the reason this ticket was created to S3.  Encrypt it.
        $encrypted_reason = A2PEncryptString($reason, $encryption_key, true);
        S3MakeBucketPrefix( S3_BUCKET, "{$support_prefix}/error" );
        S3SaveEncryptedFile(S3_BUCKET, "{$support_prefix}/error", "reason.txt", $encrypted_reason);
    }
    catch(Exception $e)
    {
        LogIt('ERROR:'.__FUNCTION__, $e->getMessage());
    }


}
function CreateParentSupportTicket( $companyparent_id, $user_id, $reason, $job_id=null)
{
    $CI = &get_instance();
    $CI->config->load("app");

    // Get information about the job that has failed.
    $controller = "";
    if ( getStringValue($job_id) != "" )
    {
        $job = $CI->Queue_model->get_job($job_id);
        $controller = getArrayStringValue("Controller", $job);
    }

    // Decide if we should do a rollback or not.  For now, I'm going to
    // assume yes.
    $rollback = GetAppOption(ROLLBACK_ON_CRIT);

    // Create a unique ticket id.
    $ticket_id = new DateTime();
    $ticket_id = $ticket_id->format('YmdHis');

    // Ensure we have the encryption key in the cache
    $encryption_key = GetCompanyParentEncryptionKey($companyparent_id);


    // Snapshots are taken as parents move through the workflow.  Thus
    // all we need to do is copy them into the support folder.
    $folder_name = GetWorkflowProgressProperty($companyparent_id, 'companyparent', '', 'SupportTag');
    CopySnapshotForSupport($companyparent_id, 'companyparent', $user_id, $folder_name, $ticket_id, $reason);


    // Notify staff there was a problem.
    SendSupportEmail( null, $companyparent_id, $reason, $ticket_id, $user_id );

    // Audit this transaction.
    $payload = array();
    $payload['link'] = base_url("support/tickets/parent/{$companyparent_id}/{$ticket_id}");
    AuditIt("Support ticket created", $payload, $user_id, null, $companyparent_id);


    // Collect the workflow name associated with this job.
    $workflow_name = "";
    $progress = $CI->Workflow_model->get_wf_by_job_id($job_id);
    if ( ! empty($progress) ) $workflow_name = GetArrayStringValue('WorkflowName', $progress[0]);

    // Rollback the user's most recent wizard attempt if needed.
    if ( $rollback === "FALSE" )
    {
        // For debugging, let's not rollback if so specified.
        LogIt('BAH:'.__FUNCTION__, 'You are not going to rollback.  doing refresh. wf_name['.$workflow_name.']');
    }
    else
    {
        WorkflowRollback($companyparent_id, 'companyparent', $workflow_name);
        WorkflowDelete($companyparent_id, 'companyparent', $workflow_name);
        NotifyWizardComplete(null, $companyparent_id);
    }
}
function CreateSupportTicket( $company_id, $user_id, $reason, $job_id=null ) {

    // CreateSupportTicket
    //
    // This function will take a snapshot of the current user's wizard data.
    // We will then move that data into a support folder for the company.
    // If the user appears to have been in the wizard workflow, then we will
    // rollback their attempt.  Last, we will send an email to support indicating
    // there has been a support ticket created.
    // ---------------------------------------------------------------------

    $CI = &get_instance();
    $CI->config->load("app");

    // Get information about the AJAX job that has failed.
    $controller = "";
    if ( getStringValue($job_id) != "" )
    {
        $job = $CI->Queue_model->get_job($job_id);
        $controller = getArrayStringValue("Controller", $job);
    }

    $rollback = false;
    if ( $controller != "" )
    {
        // This error was spawned from an AJAX request.  Check to see if
        // the controller that triggered the event is associated with the
        // wizard.  If it is, we will note that we need rollback the
        // current wizard attempt.
        $controllers = $CI->config->item("wizard_controllers");
        foreach($controllers as $wizard_controller)
        {
            if( strtoupper($wizard_controller) == strtoupper($controller) )
            {
                $rollback = true;
                break;
            }
        }
    }

    // Create a unique ticket id.
    $ticket_id = new DateTime();
    $ticket_id = $ticket_id->format('YmdHis');


    // Ensure we have the encryption key in the cache
    $CI->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $encryption_key = $CI->cache->get("crypto_{$company_id}");
    if ( GetStringValue($encryption_key) === 'FALSE' )
    {
        $encryption_key = GetCompanyEncryptionKey($company_id);
        $CI->cache->save("crypto_{$company_id}", $encryption_key, 300);
    }

    // Take snapshots and move them into the support folder so we
    // have something to review.
    TakeSnapshots($company_id, $user_id, $encryption_key);
    CopySnapshotForSupport( $company_id, 'company', $user_id, GetUploadDateFolderName($company_id), $ticket_id, $reason );

    // Notify staff there was a problem.
    SendSupportEmail( $company_id, null, $reason, $ticket_id, $user_id );

    // Audit this transaction.
    $payload = array();
    $payload['link'] = base_url("support/tickets/company/{$company_id}/{$ticket_id}");
    AuditIt("Support ticket created", $payload, $user_id, $company_id);

    // Rollback the user's most recent wizard attempt if needed.
    if ( GetAppOption(ROLLBACK_ON_CRIT) === "FALSE" )
    {
        // For debugging, let's not rollback if so specified.
    }
    else
    {
        if ($rollback) RollbackWizardAttempt($company_id);
    }


}

/**
 * CreateSecurityKey
 *
 * Create a brand new security key in the KeyPool.
 *
 * @throws Exception
 */
function CreateSecurityKey($company_id, $user_id)
{
    $CI = &get_instance();

    // Reserve a key in the pool that we can work with.
    $key_id = $CI->Support_model->reserve_slot_in_keypool();
    if ( GetStringValue($key_id) === '' ) throw new Exception("Unable to reserve a slot in the key pool");


    // Look for an existing key by that name in AWS.
    $alias = "alias/" . APP_NAME . "/keypool_" . $key_id;
    $cmk = KMSGetAlias($alias);
    if ( empty($cmk) )
    {
        $cmk_description = APP_NAME . ": KeyPool Reservation ( {$key_id} )";
        $cmk = KMSCreateKey($alias, $cmk_description);
        if ( empty($cmk) ) throw new Exception("Unable to create a Customer Master key!");
    }


    // Create an A2P Encryption Key.
    $encryption_key = A2PCreateEncryptionKey();
    if ( GetStringValue($encryption_key) === "" ) throw new Exception("Unable to create a2p encryption key");


    // Secure the encryption key.
    $encryption_key = KMSEncrypt($alias, $encryption_key);

    // Save it.
    $CI->Support_model->update_keypool($key_id, 'reserved', $encryption_key, true);

}

/**
 * ArchiveUpload
 * Relocate the upload file, for either company or companyparent, to the
 * archive folder for storage.  The support page will then have access to
 * them for historical research.
 *
 * @param $user_id
 * @param $identifier
 * @param $identifier_type
 * @throws Exception
 */
function ArchiveUpload($user_id, $identifier, $identifier_type)
{

    if ( $identifier_type === 'company' )
    {
        $company_id = $identifier;
        $snapshot_tag = GetUploadDateFolderName($company_id);       //CCYYMM
    }
    if ( $identifier_type === 'companyparent' )
    {
        $company_id = '';
        $companyparent_id = $identifier;

        // Save the snapshot tag into the preferences so it will stick around
        // for this upload process.
        $snapshot_tag = new DateTime();
        $snapshot_tag = $snapshot_tag->format('YmdHis');    // CCYYMMHHMMSS
        SavePreference($identifier, $identifier_type, 'upload', 'archive_folder', $snapshot_tag);
    }
    $encryption_key = GetEncryptionKey($identifier, $identifier_type);


    $upload_prefix = GetS3Prefix('upload', $identifier, $identifier_type);

    // Look for our upload file and make sure we can see it.
    S3GetClient();
    $files = S3ListFiles(S3_BUCKET, $upload_prefix);
    if ( count($files) != 1 ) throw new Exception("Did not find exactly one file when reviewing uploads on S3.");
    $file = $files[0];
    $filename = "s3://" . S3_BUCKET . "/" . getArrayStringValue("Key", $file);

    // What would we like to archive this file as.  Preferred, is the original filename
    // used by the the end user.
    $original_filename = fRightBack($filename, "/");
    $pref = GetPreferenceValue($identifier, $identifier_type, 'upload', 'original_filename');
    if ( $pref !== '' ) $original_filename = $pref;

    // Create the archive location if needed.
    $archive_prefix = GetS3Prefix('archive', $identifier, $identifier_type);
    $archive_prefix  = replaceFor($archive_prefix, "DATE", $snapshot_tag);
    $archive_prefix .= "/upload";
    S3MakeBucketPrefix(S3_BUCKET, $archive_prefix);

    // Empty the bucket, if there was something from a previous attempt.
    S3DeleteBucketContent( S3_BUCKET, $archive_prefix );

    // Copy an object and add server-side encryption.
    $upload_filename = fRightBack($filename, "/");


    // It's possible that the file that got uploaded was already encrypted.
    // Let's decide that now by looking at the file.  The first few lines of an
    // encrypted file will be an encrypted comment.  Check to see if the first
    // line is really that.
    $is_already_encrypted = false;
    $fh_source = null;
    try
    {
        $fh_source = S3OpenFile(S3_BUCKET, $upload_prefix, $upload_filename, 'r');
        $iterator = ReadTheFile($fh_source);
        foreach ($iterator as $iteration)
        {
            $line = trim($iteration);
            if ( IsEncryptedStringComment($line) || IsEncryptedString($line) )
            {
                $is_already_encrypted = true;
            }
            break;
        }
        if ( is_resource($fh_source) ) fclose($fh_source);
    }
    catch(Exception $e)
    {
        if ( is_resource($fh_source) ) fclose($fh_source);
    }


    if ( $is_already_encrypted )
    {
        // Copy the file from one location to the other.
        S3EncryptExistingFile( S3_BUCKET, $upload_prefix, $upload_filename, $archive_prefix, $upload_filename );
    }
    else
    {
        // Copy the upload file to the archive folder and encrypt it along the way.
        $fh_target = null;
        $fh_source = null;
        try
        {
            $fh_source = S3OpenFile(S3_BUCKET, $upload_prefix, $upload_filename, 'r');
            $fh_target = S3OpenFile(S3_BUCKET, $archive_prefix, $upload_filename, 'w');

            // Tag this file with the information about where and who it was encrypted for.
            if ( $identifier_type === 'company' ) fputs($fh_target, "{a2p-comment}:company_id[{$identifier}]" . PHP_EOL);
            if ( $identifier_type === 'companyparent' ) fputs($fh_target, "{a2p-comment}:companyparent_id[{$identifier}]" . PHP_EOL);
            fputs($fh_target, "{a2p-comment}:app_name[".APP_NAME."]" . PHP_EOL);
            fputs($fh_target, "{a2p-comment}:encrypted_on[".date("c")."]" . PHP_EOL);

            $iterator = ReadTheFile($fh_source);
            foreach ($iterator as $iteration)
            {
                fwrite($fh_target, A2PEncryptString(trim($iteration), $encryption_key) . PHP_EOL);
            }
            if ( is_resource($fh_source) ) fclose($fh_source);
            if ( is_resource($fh_target) ) fclose($fh_target);
        }
        catch(Exception $e)
        {
            if ( is_resource($fh_source) ) fclose($fh_source);
            if ( is_resource($fh_target) ) fclose($fh_target);
        }
    }


    // Secure the encrypted archive file and rename it to the user's original name.
    S3EncryptExistingFile( S3_BUCKET, $archive_prefix, $upload_filename, $archive_prefix, $original_filename );

    // Remove the copy that is not S3 encrypted at rest.
    if ( $upload_filename !== $original_filename )
    {
        S3DeleteFile(S3_BUCKET, $archive_prefix, $upload_filename);
    }



    // Only keep X number of snapshots in the companyparent archive.
    if ( $identifier_type === 'companyparent' )
    {
        $max = GetAppOption('PARENTCOMPANY_SNAPSHOT_MAX');
        if ( $max === '' ) $max = 10;

        $archive_prefix = GetS3Prefix('archive', $identifier, $identifier_type);
        PruneSnapshotFolder($identifier, $identifier_type, $archive_prefix, $max);
    }

}


/* End of file support_helper.php */
/* Location: ./application/helpers/support_helper.php */
