<?php
function ArchiveManualAdjustments( $company_id, $user_id ) {

    // ArchiveColumnMappings
    //
    // This function will collect all of the information set on the Mappings
    // screen and save a snapshot for future reference.
    // ---------------------------------------------------------------------

    $CI = &get_instance();

    // Organize our Snapshot Data
    $data = $CI->Archive_model->select_manual_adjustments_for_archive($company_id);
    ArchiveHistoricalData($company_id, 'company', "manual_adjustments", $data, array(), $user_id);
}

/* End of file adjustment_helper.php */
/* Location: ./application/helpers/v.php */
