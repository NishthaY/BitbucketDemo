<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateRetroData extends A2PLibrary {

    function __construct( $debug=false)
    {
        parent::__construct($debug);
    }

    public function execute( $company_id, $user_id=null )
    {
        try {

            parent::execute($company_id);

            $CI = $this->ci;

            if ( getStringValue($company_id) == "" ) throw new Exception("Invalid input company_id.");

            // What is our import date?
            $import_date = GetUploadDate($company_id);
            if ( $import_date == "" ) throw new Exception("Invalid import_date.  How is that possible?");
            $this->debug(" ImportDate: [{$import_date}]");

            $this->debug(" removing old retro data life event records.");
            $CI->LifeEvent_model->delete_all_retrodatalifeevent($company_id);  // Clear out the RetroDataLifeEvent table.
            $this->timer(" removing old retro data life event records.");

            $this->debug(" removing old retro data records.");
            $CI->Retro_model->delete_retro_data($company_id);  // Clear out the RetroData table.
            $this->timer(" removing old retro data records.");

            $this->debug(" inserting the retro data records to the current data.");
            $CI->Retro_model->insert_retro_data($company_id);  // Create the RetroData Records.
            $this->timer(" inserting the retro data records to the current data.");

            $this->debug(" updating the retro data records we just created with information about previous month values. ( C/PT/P/CT Match)");
            $CI->Retro_model->update_retro_data($company_id);  // Update the RetroData records, populating the Before columns ( but on the coverage tier ones )
            $this->timer(" updating the retro data records we just created with information about previous month values. ( C/PT/P/CT Match)");


            // We need to know if a life has switched coverage tiers.  This is a one to many relationship so in order
            // to tell, we need to create an coveragetier_key that holds an ordered list of all of the life's coverage tiers.
            // We will do this for now and before.  If they do not match, we will update the RetroData coverage tier columns
            // so we can detect a change in coverage tiers.
            $before_month = GetArrayStringValue("TargetMonth", $CI->Retro_model->get_retro_month( $company_id, 1));
            $coveragetier_lookup = $this->get_tier_lookup($company_id);
            $before_coveragetier_lookup = $this->get_tier_lookup($company_id, $before_month);


            $this->debug(" updating the retro data records we just created with information about previous month values. ( Coverage Tiers Do Not Match )");
            foreach($coveragetier_lookup as $life_id=>$plantypecodes){
                foreach($plantypecodes as $plantypecode=>$coveragetierkey){
                    if ( empty($before_coveragetier_lookup[$life_id][$plantypecode]) ) continue;
                    $before_coveragetierkey = $before_coveragetier_lookup[$life_id][$plantypecode];
                    if ( $coveragetierkey !=  $before_coveragetierkey )
                    {
                        // Calculate the before values, if you can.
                        $before_coveragestartdate = $CI->Retro_model->get_best_guess_before_coveragestartdate($company_id, $life_id, $before_coveragetierkey);
                        $before_volume = $CI->Retro_model->get_best_guess_before_volume($company_id, $life_id, $before_coveragetierkey);
                        $before_monthlycost = $CI->Retro_model->get_best_guess_before_monthlycost($company_id, $life_id, $before_coveragetierkey);
                        $before_planid = $CI->Retro_model->get_best_guess_before_planid($company_id, $life_id, $before_coveragetierkey);

                        // If the coverage start date appears to be a list, meaning it contains a comma, then
                        // move the value into the list version.
                        $before_coveragestartdatelist = "";
                        if ( strpos($before_coveragestartdate, ",") !== FALSE )
                        {
                            $before_coveragestartdatelist = $before_coveragestartdate;
                            $before_coveragestartdate = "";
                        }

                        // If the plan id appears to be a list, meaning it contains a comma, then
                        // move the value into the list version.
                        $before_planidlist = "";
                        if ( strpos($before_planid, ",") !== FALSE )
                        {
                            $before_planidlist = $before_planid;
                            $before_planid = "";
                        }

                        $CI->Retro_model->update_retro_data_coverage_tier( $company_id, $coveragetierkey, $before_coveragetierkey, $life_id, $plantypecode, $before_coveragestartdate, $before_volume, $before_monthlycost, $before_coveragestartdatelist, $before_planid, $before_planidlist);
                        $this->debug(" now[{$coveragetierkey}] <> before[{$before_coveragetierkey}] for life[{$life_id}] plantypecode[{$plantypecode}] before_coveragestartdate[{$before_coveragestartdate}], before_volume[{$before_volume}], before_monthlycost[{$before_monthlycost}], before_coveragestartdatelist[{$before_coveragestartdatelist}], before_planid[{$before_planid}], before_planidlist[{$before_planidlist}]");
                    }
                }
            }
            $this->timer(" updating the retro data records we just created with information about previous month values. ( Coverage Tiers Do Not Match )");


            // Look for adjustments that we need to investigate in this order.
            // Skip retro data creation if this is our very first import.
            if ( $CI->Retro_model->is_first_import($company_id, $import_date) == "f" )
            {
                $this->debug(" Looking for adjustments since this is not our first import.");
                $CI->Retro_model->update_retro_data_change_new_coverage_tier($company_id);
                $CI->Retro_model->update_retro_data_change_new_employer_cost($company_id);
                $CI->Retro_model->update_retro_data_change_new_volume($company_id);
                $CI->Retro_model->update_retro_data_change_new_effective_date($company_id);
                $CI->Retro_model->update_retro_data_add_new_entry($company_id);
                $CI->Retro_model->update_retro_data_term($company_id);
                $this->timer(" Looking for adjustments since this is not our first import.");

                $this->debug(" Creating adjustments, if needed.");
                $CI->LifeEvent_model->insert_autoselected_retrodatalifeevent($company_id);
                $CI->LifeEvent_model->insert_manual_retrodatalifeevent($company_id);
                $CI->LifeEvent_model->correct_many2many_retrodatalifeevent($company_id);
                $CI->LifeEvent_model->restore_retrodatalifeevent($company_id);
                $this->timer(" Creating adjustments, if needed.");
            }


            // DEFAULT
            // If the default clarification feature has been set, apply those defaults now to anything
            // that has not been automatically selected by our retro engine.
            $default_type = GetClarificationType($company_id, 'company');
            $valid = ['ignore', 'retro'];
            if ( in_array($default_type, $valid) )
            {
                if ( $default_type === 'ignore') $CI->LifeEvent_model->set_default_type_ignore($company_id, $import_date);
                if ( $default_type === 'retro') $CI->LifeEvent_model->set_default_type_retro($company_id, $import_date);
                $CI->LifeEvent_model->create_clarification_warnings($company_id, $import_date, $default_type);
                $CI->Reporting_model->insert_retrodatalifeeventwarnings($company_id, $import_date);
            }


        } catch(Exception $e) {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * get_tier_lookup
     *
     * Create a way to lookup all of the coverage tiers for a given life and
     * and plantype so we can compare what it was this month and last to decide
     * if there have been changes for not.
     *
     * @param $company_id
     * @param null $target_date
     * @return array
     */
    public function get_tier_lookup($company_id, $target_date=null)
    {
        $lookup = array();

        if ( GetStringValue($target_date) === '' ) $target_date = GetUploadDate($company_id);
        $this->debug(" Creating a coverage tier lookup per live/plantypecode for [{$target_date}]");
        $results = $this->ci->Retro_model->get_coverage_tier_lookup($company_id, $target_date);
        foreach($results as $item)
        {
            $life_id = GetArrayStringValue('LifeId', $item);
            $plantypecode = GetArrayStringValue('PlanTypeCode', $item);
            $tiers = GetArrayStringValue("CoverageTierList", $item);
            $lookup[$life_id][$plantypecode] = rtrim($tiers, ',');
        }
        return $lookup;
    }

    public function DEFUNCTget_tier_lookup($company_id, $target_date=null) {

        // get_tier_lookup
        //
        // In order to figure out if the coverage tiers have changed for a life,
        // we need to create a unique "coverate tier key" for the life/plantypecode.
        // This is done by pulling all of the coverate tier ids, sorted for each
        // unique life/planttype an then placing that key into a lookup so we
        // can conduct future business logic against it.
        // ------------------------------------------------------------------

        // Stop, Look, Listen.
        // This is the old get_tier_lookup function.  It has been marked as defunct, but would
        // still run if executed.  It was taking 4 seconds a life to run this function for a 45k file.
        // We replaced this will a different function that does the aggrigation in the database and
        // packs the list automatically to make things faster.  Delete this later after we no longer
        // need this for reference.
        throw new Exception("This function is depricated.  Use newer get_tier_lookup instead.");

        $CI = $this->ci;
        $lookup = array();

        // Pull all of the distinct lives for this retro data set.
        $this->debug(" select_retro_data_distinct_lives [{$company_id}] [{$target_date}]");
        $lives = $CI->Retro_model->DEFUNCTselect_retro_data_distinct_lives($company_id, $target_date);
        foreach($lives as $life)
        {
            // For each life, pull the distinct plan type codes for this retro data set.
            $life_id = GetArrayStringValue("LifeId", $life);
            $plantypes = $CI->Retro_model->DEFUNCTselect_retro_data_distinct_plantypes_by_life($company_id, $life_id, $target_date);
            foreach($plantypes as $plantype)
            {

                // Finally, pull a distinct list of coverage tier ids for this retro data set by life and planttype code.
                $plantypecode = GetArrayStringValue("PlanTypeCode", $plantype);
                $coveragetiers = $CI->Retro_model->DEFUNCTselect_retro_data_distinct_coverage_tiers_by_life_and_plantype($company_id, $life_id, $plantypecode, $target_date);

                $tiers = array();
                foreach($coveragetiers as $coveragetier)
                {
                    $tiers[] = getArrayStringValue("CoverageTierId", $coveragetier);
                }
                $lookup[$life_id][$plantypecode] = implode($tiers, ",");
            }

        }
        return $lookup;
    }

}
