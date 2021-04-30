<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenerateAgeData extends A2PLibrary {

    function __construct( $debug=false )
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
            $this->debug(" ComapnyId:  [{$company_id}]");

            $this->debug(" Removing age data for specified company and import date.");
            $CI->Age_model->delete_age($company_id);
            $this->timer(" Removing age data for specified company and import date.");

            $this->debug(" Inserting age records for specified company and import date.");
            $CI->Age_model->insert_age($company_id);
            $this->timer(" Inserting age records for specified company and import date.");

            $this->debug(" Looking up all distinct coverage tiers.");
            $tiers = $CI->Age_model->select_age_coverage_tiers($company_id);
            foreach($tiers as $tier)
            {

                $tier = getArrayStringValue("CoverageTierId", $tier);
                $this->debug(" COVERAGE TIER [{$tier}].");

                $this->debug("   Collecting age calculation data.");
                $data = $CI->Age_model->select_age_calculation_data_by_tier($tier);
                $calculation_type = getArrayStringValue("AgeTypeId", $data);
                $anniversary_day = getArrayStringValue("AnniversaryDay", $data);
                $anniversary_month = getArrayStringValue("AnniversaryMonth", $data);
                $this->timer("   Collecting age calculation data.");

                $this->debug("   Updating the age calculation data by tier.");
                $CI->Age_model->update_age_calculation_data( $company_id, $tier, $calculation_type, $anniversary_day, $anniversary_month );
                $this->timer("   Updating the age calculation data by tier.");

                $this->debug("   Updating the issued date age calculation. ");
                $CI->Age_model->update_issued_date($company_id);
                $this->timer("   Updating the issued date age calculation. ");

                $this->debug("   Updating the age on column.");
                $CI->Age_model->update_age_on( $company_id, $tier );
                $this->timer("   Updating the age on column.");
            }


            // We created a string called "AgeOn" which is the date of the lifes birthday this
            // year.  If they were born on a leap year AND this is not a leap year, we will be unable
            // to do date calculations in the database against it.  To fix this, we will mark our leap
            // babies.  Then push their birthday back one day and calculate their age the day before
            // their birthday ( making them on year younger. )
            $this->debug(" Making adjustments for leap babies.");
            $age_on = '2/29/' . date("Y", strtotime($import_date));
            $this->ci->Age_model->set_leap_baby_flg($company_id, $age_on, $import_date );
            $this->ci->Age_model->update_age_on_for_leap_babies($company_id, $import_date);
            $this->timer(" Making adjustments for leap babies.");

            // Calculate everyones age.
            $this->debug(" Calculating ages.");
            $CI->Age_model->update_age($company_id);
            $this->timer(" Calculating ages.");

            // Find our Leap Babies that are one year too young due to the data adjustment
            // we made and add one year to their age to get the correct age for them.
            $this->debug(" Recalculating age for leap babies.");
            $this->ci->Age_model->update_leap_babies_age($company_id, $import_date);
            $this->timer(" Recalculating age for leap babies.");

        } catch(Exception $e) {
            $this->debug("EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }


}
