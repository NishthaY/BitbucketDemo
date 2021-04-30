<?php

/**
 * SupportTimerStart
 *
 * This function will denote the start of a timed event that will be report
 * on the support dashboard.  The timed event is "the most recent for this
 * companies upload month".  Thus, these will be overwritten each time they
 * process and reprocess a file for the month.  End the end, you will be left
 * with the last set of timers processed by the user for the month.
 *
 * The estimate_flg, when set to true, will let the support dashboard know
 * that this timer should be summed into the estimated run time for the
 * overall month.
 *
 * @param $company_id
 * @param $import_date
 * @param $tag
 * @param bool $estimate_flg
 */
function SupportTimerStart($company_id, $import_date, $tag, $parent_tag='')
{
    $CI = &get_instance();

    if ( GetStringValue($company_id) === '' ) return;
    if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
    if ( GetStringValue($import_date) === '' ) return;
    if ( GetStringValue($tag) == "" ) return;

    $timestamp = date('Y-m-d H:i:s');
    if ( ! $CI->Support_model->exists_support_timer($company_id, $import_date, $tag, $parent_tag) )
    {
        // If we don't have this record in the table yet, go ahead and create it.  This just
        // creates the record, it does not set any timers.  This is important because the  user
        // can 're-run' sections and we want to only create a record once.
        $CI->Support_model->insert_support_timer($company_id, $import_date, $tag, $parent_tag);
    }
    $CI->Support_model->update_support_timer($company_id, $import_date, $tag, $parent_tag, $timestamp, null);
}

/**
 * SupportTimerEnd
 *
 * This function turns off a timer that is shown on the support page.
 * See SupportTimerStart for more information.
 *
 * @param $company_id
 * @param $import_date
 * @param $tag
 * @param bool $estimate_flg
 */
function SupportTimerEnd($company_id, $import_date, $tag, $parent_tag='')
{
    $CI = &get_instance();

    if ( GetStringValue($company_id) === '' ) return;
    if ( GetStringValue($import_date) === '' ) $import_date = GetUploadDate($company_id);
    if ( GetStringValue($import_date) === '' ) return;
    if ( GetStringValue($tag) == "" ) return;

    $timestamp = date('Y-m-d H:i:s');
    if ( ! $CI->Support_model->exists_support_timer($company_id, $import_date, $tag, $parent_tag) )
    {
        // If we don't have this record in the table yet, go ahead and create it.  This just
        // creates the record, it does not set any timers.  This is important because the  user
        // can 're-run' sections and we want to only create a record once.
        $CI->Support_model->insert_support_timer($company_id, $import_date, $tag, $parent_tag);
    }
    $CI->Support_model->update_support_timer($company_id, $import_date, $tag, $parent_tag, null, $timestamp);


}