<?php
function ArchiveLifeCompare( $company_id, $user_id, $encryption_key ) {

    // ArchiveLifeCompare
    //
    // This function will collect all of the information set on the Life Compare
    // screen and save a snapshot for future reference.
    // ---------------------------------------------------------------------

    $CI = &get_instance();

    // Organize our Snapshot Data
    $data = $CI->Archive_model->select_life_compare_for_archive($company_id);

    // Consolidate multiple rows into a single cell.
    $output = array();
    foreach($data as $item)
    {
        $row = array();
        $row['UserElection'] = GetArrayStringValue("UserElection", $item);

        $first_name = A2PDecryptString(GetArrayStringValue("UploadLifeFirstName", $item), $encryption_key);
        $middle_name = A2PDecryptString(GetArrayStringValue("UploadLifeMiddleName", $item), $encryption_key);
        $last_name = A2PDecryptString(GetArrayStringValue("UploadLifeLastName", $item), $encryption_key);
        $ssn_display = A2PDecryptString(GetArrayStringValue("UploadLifeSSNDisplay", $item), $encryption_key);
        $dob = A2PDecryptString(GetArrayStringValue("UploadLifeDateOfBirth", $item), $encryption_key);
        $relationship = GetArrayStringValue("UploadLifeRelationship", $item);
        $row['UploadedLife'] = A2PEncryptString("{$first_name} {$middle_name} {$last_name} {$ssn_display} {$dob} {$relationship}", $encryption_key);

        $first_name = A2PDecryptString(GetArrayStringValue("ExistingLifeFirstName", $item), $encryption_key);
        $middle_name = A2PDecryptString(GetArrayStringValue("ExistingLifeMiddleName", $item), $encryption_key);
        $last_name = A2PDecryptString(GetArrayStringValue("ExistingLifeLastName", $item), $encryption_key);
        $ssn_display = A2PDecryptString(GetArrayStringValue("ExistingLifeSSNDisplay", $item), $encryption_key);
        $dob = A2PDecryptString(GetArrayStringValue("ExistingLifeDateOfBirth", $item), $encryption_key);
        $relationship = A2PDecryptString(GetArrayStringValue("ExistingLifeRelationship", $item), $encryption_key);
        $row['ExistingLife'] = A2PEncryptString("{$first_name} {$middle_name} {$last_name} {$ssn_display} {$dob} {$relationship}", $encryption_key);

        $row['EmployeeId'] = A2PDecryptString(GetArrayStringValue("EmployeeId", $item), $encryption_key);
        $row['UploadedLifeId'] = GetArrayStringValue("UploadedLifeId", $item);
        $row['ExistingLifeId'] = GetArrayStringValue("ExistingLifeId", $item);
        $output[] = $row;
    }

    ArchiveHistoricalData($company_id, 'company', "life_compare", $output, array(), $user_id, 3);
}

function SpecialSortUpdateRecords( &$records, &$parent ) {

    // SpecialSortUpdateRecords
    //
    // When giving options to users when resolving life compare updates, it
    // was requested that we "pop to the top" any users that match the parent
    // name.  Thus, in most cases, the item they want to click is always the
    // first item in the list.
    //
    // This function will take the already sorted list of children and search
    // for matches by first and last name against the parent.  As we find a
    // match, those go into the $first array.  If the item is not a match it
    // goes in the $other array.  At the end, we return a new array with
    // the content of first followed by the content of other.
    //
    // In this fasion we can respect the query's sort, what ever it might be
    // but promote ( in order ) any records that match the parents name.
    // ------------------------------------------------------------------------

    $first = array();
    $other = array();

    $firstname = getArrayStringValue("FirstName", $parent);
    $lastname = getArrayStringValue("LastName", $parent);

    $index = 0;
    foreach($records as $record)
    {

        $rfirstname = getArrayStringValue("FirstName", $record);
        $rlastname = getArrayStringValue("LastName", $record);
        if( $firstname == $rfirstname && $lastname == $rlastname )
        {
            $first[] = $record;
        }
        else
        {
            $other[] = $record;
        }

        $index++;
    }
    $out = array_merge($first,$other);
    return $out;


}
function AutoUpdateLifeCompareByNoSSN( $company_id) {
    // AutoUpdateLifeCompareByNoSSN
    //
    // Update existing life all fields EXCEPT SSN.
    // Look up canidates.  If a canidate can be matched by all keys EXCEPT
    // SSN, then auto-match it for the user.
    // ---------------------------------------------------------------------

    // Initialize Singleton
    $CI = &get_instance();

    // Select all new "Lives" that are either new or canidates for update.
    $canidates = $CI->Life_model->select_companylifecompare_auto_update_canidates($company_id);
    foreach($canidates as $canidate)
    {
        // Grab some information about this canidate.
        $life_id = getArrayStringValue("LifeId", $canidate);
        $ssn = getArrayStringValue("SSN", $canidate);
        $firstname = getArrayStringValue("FirstName", $canidate);
        $lastname = getArrayStringValue("LastName", $canidate);
        $middlename = getArrayStringValue("MiddleName", $canidate);
        $eid = getArrayStringValue("EmployeeId", $canidate);
        $dob = getArrayStringValue("DateOfBirth", $canidate);
        $relationship = getArrayStringValue("Relationship", $canidate);

        // Look for a matching life on the whole life key EXCEPT social security number.
        $match = $CI->Life_model->select_companylifecompare_auto_update_canidate_match_no_ssn($company_id, $ssn, $firstname, $lastname, $middlename, $eid, $dob, $relationship);
        if ( ! empty($match) )
        {
            // Yes, we have found a single life in the pool of lives for this
            // company such that the whole life key matches EXCEPT for the SSN.
            // Let's go ahead and auto-select this life for the user to make thier life easier.
            $updates_life_id = getArrayStringValue("UpdatesLifeId", $match);
            if ( $updates_life_id != "" && $life_id != "" )
            {
                $CI->Life_model->backup_companylife_to_companylifecompare( $life_id, $updates_life_id, $company_id );
                $CI->Life_model->update_companylife( $life_id, $updates_life_id, $company_id );
                $CI->Life_model->set_autoselected_companylife( $life_id, $updates_life_id, $company_id );
            }
        }
    }

}
function AutoUpdateLifeCompareBySSN( $company_id ) {

    // AutoUpdateLifeCompareBySSN
    //
    // Update existing life by SSN.
    // Look for canidates.  If we have an SSN, lookup the life by that SSN
    // and make the match for the user.
    // ---------------------------------------------------------------------

    // Initialize Singleton
    $CI = &get_instance();

    // Select all new "Lives" that are either new or canidates for update.
    $canidates = $CI->Life_model->select_companylifecompare_auto_update_canidates($company_id);
    foreach($canidates as $canidate)
    {
        // Grab some base information this this candidate.
        $life_id = getArrayStringValue("LifeId", $canidate);
        $ssn = getArrayStringValue("SSN", $canidate);

        // If this candidate does not hae a SSN, then it would be a neat trick
        // to find an existing life with a matching SSN.  Thus, skip this guy.
        if ( $ssn == "" ) continue;



        // Look for a matching life by social security number.  ( Has to be one to one match !!! )
        $match = $CI->Life_model->select_companylifecompare_auto_update_canidate_match($company_id, $ssn);
        if ( ! empty($match) )
        {
            // At this point, we know there was only one life last month with this
            // ssn.  However, we also need to make sure there is exactly one candidate
            // trying to move to that ssn.  If there are multiple, we will need to
            // have a human decide.
            $count = $CI->Life_model->select_companylifecompare_auto_update_canidates_count( $company_id, $ssn );
            if ( $count !== 1 ) continue;

            // Yes, we have found a single life in the pool of lives for this
            // company such that existing life and the candidate have the same SSN.
            // Let's go ahead and auto-select this life for the user to make their life easier.
            $updates_life_id = getArrayStringValue("UpdatesLifeId", $match);
            if ( $updates_life_id != "" && $life_id != "" )
            {
                $CI->Life_model->backup_companylife_to_companylifecompare( $life_id, $updates_life_id, $company_id );
                $CI->Life_model->update_companylife( $life_id, $updates_life_id, $company_id );
                $CI->Life_model->set_autoselected_companylife( $life_id, $updates_life_id, $company_id );
            }
        }
    }
}
function HasLivesYetToCompare( $company_id ) {
    // HasLivesYetToCompare
    //
    // This function will return true if there are one or more lives that
    // have been identified as needing reviewed by person AND those lives
    // have not yet been resolved by the end user.
    // ---------------------------------------------------------------------

    // Initialize Singleton
    $CI = &get_instance();

    $pref = $CI->Company_model->get_company_preferences_by_value($company_id, "column_map", "relationship");

    if ( ! empty($pref) ) {

        // Have we identified a life that needs to be reviewed?
        if ( $CI->Life_model->select_insert_companylifecompare_has_lifes_yet_to_compare($company_id) == 'f' ) return false;
        return true;
    }
    return false;
}
function HasLivesToCompare( $company_id ) {
    // HasLifeCompare
    //
    // This function will return true if there are one or more lives that
    // have been identified as needing reviewed by person.
    // ---------------------------------------------------------------------

    // Initialize Singleton
    $CI = &get_instance();

    $pref = $CI->Company_model->get_company_preferences_by_value($company_id, "column_map", "relationship");

    if ( ! empty($pref) ) {

        // Have we identified a life that needs to be reviewed?
        if ( $CI->Life_model->select_insert_companylifecompare_has_lifes_to_compare($company_id) == 'f' ) return false;
        return true;
    }
    return false;

}

function HasSSN( $company_id ) {
    // HasSSN
    //
    // This function will return TRUE if the company currently has mapped
    // the SSN column.
    // ---------------------------------------------------------------------

    // Initialize Singleton
    $CI = &get_instance();

    $pref = $CI->Company_model->get_company_preferences_by_value($company_id, "column_map", "ssn");

    if ( ! empty($pref) ) {

        // We do have a ssn column mapped!  Before we say "Yes, we have ssns."
        // let's look and see if the file contains any data in the ssn column.
        if ( $CI->Life_model->has_ssn_data($company_id) == 'f' ) return false;
        return true;
    }
    return false;

}

/* End of file life_helper.php */
/* Location: ./application/helpers/life_helper.php */
