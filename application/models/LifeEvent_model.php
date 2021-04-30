<?php

class LifeEvent_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }
    function count_many2many_retrodatalifeevent( $company_id, $before_coveragetier_list, $coverage_start_date ) {

        // count_many2many_retrodatalifeevent
        //
        // Give a coverage tier list, look up and count the number of adjustments
        // we would disable for the associated life event given the coverage
        // date specified.  Return the count.
        // ------------------------------------------------------------------

        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventSELECT_CountMany2Many.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($before_coveragetier_list)
            , getStringValue($coverage_start_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if(count($results) != 1) throw new Exception("Unexpected results while counting retro data life events");
        $results = $results[0];
        return getArrayStringValue("Count", $results);
    }
    function insert_lifeevent_warning($company_id, $before_coveragetier_list) {

        // insert_lifeevent_warning
        //
        // Give a coverage tier list, look up all of the life events assoicated
        // with the list and pull out the details needed to generate a waning.
        // Once collected, write a warning for each record we found.
        // ------------------------------------------------------------------

        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        // 1. Select all of the records that match the before_coveragetier_list.
        $file = "database/sql/lifeevent/RetroDataLifeEventSELECT_ForWarning.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
            , getStringValue($before_coveragetier_list)
        );
        $results = GetDBResults( $this->db, $file, $vars );

        if(count($results) == 0) return;

        // 2. Foreach item we found, write a warning.
        foreach($results as $item)
        {
            $import_data_id = getArrayStringValue("ImportDataId", $item);
            $carrier = getArrayStringValue("Carrier", $item);
            $tiers = $this->select_coveragetier_description($before_coveragetier_list);

            // Look at the list of 'tiers' and make the list more
            // readable by adding an "and" if the list contains more than
            // one item before the last tier.
            $chunks = explode(", ", $tiers);
            if ( count($chunks) > 1 )
            {
                $last = array_pop($chunks);
                $text = implode(", ", $chunks);
                $text .= " and " . $last;
                $tiers = $text;
            }

            // Write the warning.
            $replaceFor = array();
            $replaceFor['{CARRIER}'] = $carrier;
            $replaceFor['{TIERS}'] = $tiers;

            $file = "database/sql/retrodata/ReportReviewWarningINSERT_Many2ManyLifeEvents.sql";
            $vars = array(
                getIntValue($company_id)
                , getStringValue($import_date)
                , getIntValue($import_data_id)
            );
            ExecuteSQL( $this->db, $file, $vars, $replaceFor );

        }


    }
    function select_many2many_retrodatalifeevent_range( $company_id ) {

        // select_many2many_retrodatalifeevent_range
        //
        // This function will select all records that have a coverage tier
        // change ( many2many only ) grouped by the unique list.  It will then
        // pull out the min and max coverage start date for the collection
        // of associated coverage tiers.  This will allow us to see what
        // adjustment would be impacted by a life event using either the
        // min or max date, of which we don't know which one to use.
        // ---------------------------------------------------------------

        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventSELECT_Many2ManyMinMaxCoverageStartDate.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if(count($results) == 0) return array();
        return $results;
    }
    function correct_many2many_retrodatalifeevent( $company_id ) {

        // correct_many2many_retrodatalifeevent
        //
        // When we have a many to many coverage tier situation, we need to
        // do some extra investigation.  We will locate the most recent of
        // the before coverage start dates and then apply the "qualification"
        // start date logic to that date.  If qualified, we will auto-select
        // the record.  If not qualified, we will delete it.
        // ---------------------------------------------------------------

        $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return;

        // Pull all many2many items.
        $file = "database/sql/lifeevent/RetroDataLifeEventSELECT_Many2Many.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0 ) return;

        // Investigate each of the Many2Many items.
        foreach($results as $item)
        {
            $id = getArrayStringValue("RetroDataLifeEventId", $item);
            $coverage_start_date = getArrayStringValue("CoverageStartDate", $item);
            $before_coverage_start_date_list = getArrayStringValue("BeforeCoverageStartDateList", $item);
            $carrier = getArrayStringValue("Carrier", $item);
            $tier_id_list = getArrayStringValue("BeforeCoverageTierIdList", $item);
            $import_data_id = getArrayStringValue("ImportDataId", $item);

            // Sort the before coverage start date list.
            $before_coverage_start_dates = array();
            $dates = explode(",", $before_coverage_start_date_list);
            foreach($dates as $date)
            {
                $timestamp = strtotime($date);
                $before_coverage_start_dates[] = $timestamp;
            }
            sort($before_coverage_start_dates);

            // Pop the most recent before coverage start date.
            $before_coverage_start_date = array_pop($before_coverage_start_dates);  // Most Recent Before Coverage Start dates
            $coverage_start_date = strtotime($coverage_start_date);

            if ( $coverage_start_date > $before_coverage_start_date )
            {
                // Yes!  The most recent before date is less than the new coverage start date.
                // This is to be considered an "automatic" election.  Set the
                // auto select flag to true.

                // Autoselect this item.
                $file = "database/sql/lifeevent/RetroDataLifeEventUPDATE_SetAutoSelectedLifeEventById.sql";
                $vars = array(
                    getIntValue($id)
                );
                ExecuteSQL( $this->db, $file, $vars );

            }
            else
            {
                // No!  The most recent before date is not less than the new coverage start date.
                // This does not qualify as a life event, just delete the record.
                $file = "database/sql/lifeevent/RetroDataLifeEventDELETE_ById.sql";
                $vars = array(
                    getIntValue($id)
                );
                ExecuteSQL( $this->db, $file, $vars );
            }
        }

    }
    function select_coveragetier_description($list) {

        // select_coveragetier_description
        //
        // Given a list of coverage tier ids, turn them into a list of
        // human readable coverage tiers.
        // ---------------------------------------------------------------

        $replaceFor = array();
        $replaceFor["{LIST}"] = $list;

        $file = "database/sql/lifeevent/RetroDataLifeEventSELECT_ListOfCoverageTiers.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars, $replaceFor );
        if(count($results) == 1) {
            $results = $results[0];
            return getArrayStringValue("CoverageTiers", $results);
        }
        return "";

    }
    function delete_lifeeventcompare($company_id, $import_date=null) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/LifeEventCompareDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function insert_lifeeventcompare($company_id, $import_date=null) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/LifeEventCompareINSERT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    function restore_retrodatalifeevent($company_id, $import_date=null) {

        // restore_retrodatalifeevent
        //
        // Using the soft key stored in the LifeEventCompare table, push
        // the life event flag onto the RetroDataLifeEvent table if we can
        // find and exact match.
        // ------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventUPDATE_PreviousUserElection.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function update_retrodatalifeevent($company_id, $id, $is_life_event, $import_date=null) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventUPDATE.sql";
        $vars = array(
            getStringValue($is_life_event)
            , getIntValue($id)
            , getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function select_all_retrodatalifeevent($company_id, $import_date=null) {

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventSELECT.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if(count($results) == 0) return array();
        return $results;

    }

    function delete_all_retrodatalifeevent($company_id, $import_date=null) {

        // delete_all_retrodatalifeevent
        //
        // Delete all data for the given company and the current import file
        // located in the "RetroDataLifeEvent" table.
        // ------------------------------------------------------------------

        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventDELETE.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function insert_autoselected_retrodatalifeevent( $company_id, $import_date=null )
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        // Standard items.
        // Grab the items are obviously a life change per business rules.
        $file = "database/sql/lifeevent/RetroDataLifeEventINSERT_AutomaticItems.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );

        ExecuteSQL( $this->db, $file, $vars );

        // Many 2 Many Items.
        // Grab the items that have a coverage tier change that might be a life event, but
        // we can't say for sure because we cant know the "Before-CoverageStartDate" for sure.
        $file = "database/sql/lifeevent/RetroDataLifeEventINSERT_AutomaticMany2Many.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );


    }
    function insert_manual_retrodatalifeevent( $company_id, $import_date=null )
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventINSERT_Manual.sql";
        $vars = array(
            getIntValue($company_id)
            , getStringValue($import_date)
        );

        ExecuteSQL( $this->db, $file, $vars );
    }

    function set_default_type_ignore( $company_id, $import_date=null )
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventUPDATE_SetDefaultIgnore.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function set_default_type_retro( $company_id, $import_date=null )
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventUPDATE_SetDefaultRetro.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function set_default_type_off( $company_id, $import_date, $clarification_id )
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventUPDATE_SetDefaultOff.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date),
            getIntValue($clarification_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function remove_clarification_warning($company_id, $import_date, $clarification_id, $type)
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventWarningDELETE_ByRetroDataLifeEventId.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date),
            getStringValue($type),
            getIntValue($clarification_id)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function create_clarification_warnings($company_id, $import_date, $default_type)
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventWarningINSERT_DefaultType.sql";
        $vars = array(
            getStringValue($default_type),
            getStringValue($default_type),
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function delete_all_retrodatalifeeventwarning($company_id, $import_date="")
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventWarningDELETE_All.sql";
        $vars = array(
            getIntValue($company_id),
            getStringValue($import_date)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    function select_retrodatalifeevent_by_id($id, $company_id, $import_date)
    {
        if ( getStringValue($import_date) == "" ) $import_date = GetUploadDate($company_id);
        if ( getStringValue($import_date) == "" ) return array();

        $file = "database/sql/lifeevent/RetroDataLifeEventSELECT_ById.sql";
        $vars = array(
            getIntValue($id),
            getIntValue($company_id),
            getStringValue($import_date)
        );
        $results = ExecuteSQL( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many clarification records by id.");
        return $results[0];
    }


}


/* End of file LifeEvent_model.php */
/* Location: ./system/application/models/LifeEvent_model.php */
