$(function(){
    InitPageNotifications();
});
var debug_event = false;
/**
 * InitPageNotifications
 *
 * Look for a list of function that should be loaded when the
 * page loads.  These functions are triggered based on the
 * content that was loaded.
 *
 */
function InitPageNotifications()
{
    $('ul.onload-list').each(function(){
        var ul = this;
        $(ul).find('li').each(function(){
            var li = this;
            var text = $(li).text();
            executeFunctionByName(text, window);
        });
    });
}
function notify_workflow_step_changing ( company_id )
{
    if ( jQuery.type(company_id) === "undefined" ) { company_id = ""; }

    setTimeout(function(){

        var url = base_url + "wizard/notify/changing";

        // Ensure the ajax flag is set to true.
        var params = {};
        params.ajax = 1;
        params.url = url;
        if ( getStringValue(company_id) != "" )
        {
            params.company_id = company_id;
        }

        $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
            if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
        });
    }, 300);
}
/**
 * notify_workflow_step_changed
 *
 * Trigger a notification indicating a user has started the
 * review process for a wizard step.
 *
 * @param company_id
 */
function notify_workflow_step_changed( company_id )
{
    if ( jQuery.type(company_id) === "undefined" ) { company_id = ""; }

    setTimeout(function(){

        var url = base_url + "wizard/notify/changed";

        // Ensure the ajax flag is set to true.
        var params = {};
        params.ajax = 1;
        params.url = url;
        if ( getStringValue(company_id) != "" )
        {
            params.company_id = company_id;
        }

        $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
            if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
        });
    }, 300);

}
/**
 * workflow_step_changed
 *
 * Take the following actions when a user changes the workflow step
 * for a given company.
 *
 * @param data
 */
function workflow_step_changed( data )
{
    if ( debug_event ) console.log("[EVENT]: (workflow_step_changed)");
    var pathname = window.location.pathname;
    var company_id = getStringValue(data['company_id']);
    var companyparent_id = getStringValue(data['companyparent_id']);
    var step_name = getStringValue(data['step_name']);

    if ( debug_event ) console.log("[EVENT]: (workflow_step_changed) company_id["+company_id+"]");
    if ( debug_event ) console.log("[EVENT]: (workflow_step_changed) companyparent_id["+companyparent_id+"]");
    if ( debug_event ) console.log("[EVENT]: (workflow_step_changed) step_name["+step_name+"]");


    var company_id = getStringValue(data['company_id']);
    if ( getStringValue(company_id) == "" ) return;

    // MULTI COMPANY WIDGET
    // If we have a multi company widget on the screen, refresh it.
    if ( $("#multi_company_widget").length)
    {
        if ( debug_event ) console.log("[EVENT]: (workflow_step_changed) Found the multi_company_widget, refreshing it.");
        refreshWidget("multi_company_widget", "MultiCompanyWidgetRefreshed");
    }


    // Should we refresh the company dashboard if the workflow step changed?
    if ( pathname == '/dashboard' )
    {
        if ( debug_event ) console.log("[EVENT]: (workflow_step_changed) You are on company dashboard.");
        location.href = base_url + "dashboard";
    }

}

/**
 * workflow_step_changing
 *
 * Take the following actions when a user requests that we change the
 * workflow state.  This event covers the time between "requested" and
 * "changed".
 *
 * @param data
 */
function workflow_step_changing( data )
{
    if ( debug_event ) console.log("[EVENT]: (workflow_step_changing) ");
    var pathname = window.location.pathname;
    var company_id = getStringValue(data['company_id']);
    var companyparent_id = getStringValue(data['companyparent_id']);
    var step_name = getStringValue(data['step_name']);

    if ( getStringValue(company_id) == "" ) return;

    // MULTI COMPANY WIDGET
    // Update the company row in the multi-company widget.
    if ( $("#multi_company_widget").length)
    {
        if ( debug_event ) console.log("[EVENT]: (workflow_step_changing) Updating the multi-company widget.");
        MultiCompanyRowWorkflowEventChangingEventHandler(company_id);
    }


}

/**
 * workflow_step_starting
 *
 * Take the following actions a workflow step has started running in the background.
 *
 * @param data
 */
function workflow_step_starting( data )
{
    if ( debug_event ) console.log("[EVENT]: (workflow_step_starting) ");
    var pathname = window.location.pathname;
    var company_id = getStringValue(data['company_id']);
    var companyparent_id = getStringValue(data['companyparent_id']);
    var step_name = getStringValue(data['step_name']);

    if ( pathname.indexOf("dashboard", pathname) == -1 )
    {
        // We are processing a dashboard workflow widget.  If they
        // are not on a URL that contains dashboard, push them there.
        if ( debug_event ) console.log("[EVENT]: (workflow_step_starting) You are NOT a dashboard of any kind.");
        location.href = base_url + "dashboard";
    }
    else if ( pathname == '/dashboard/parent' )
    {
        // If you are on dashboard/parent, move to dashboard/parent/workflow
        if ( debug_event ) console.log("[EVENT]: (workflow_step_starting) You are on the parent dashboard.");
        //location.href = base_url + "dashboard/parent/workflow";
    }
    else if ( pathname == '/dashboard/parent/workflow' )
    {
        // If you are on the dashboard/parent/workflow, refresh the widget.
        if ( debug_event ) console.log("[EVENT]: (workflow_step_starting) What is a parent/workflow?  I don't remember.");
        //refreshWidget('sample_workflow_widget');
    }
    else {
        if ( debug_event ) console.log("[EVENT]: (workflow_step_starting) Starting event ignored.  Not on a monitored url.");
        //console.log("Pusher: The pathname ["+pathname+"] did nothing for us.");
    }


}

function workflow_complete ( data )
{
    if ( debug_event ) console.log("[EVENT]: (workflow_complete)");
    var pathname = window.location.pathname;
    var company_id = getStringValue(data['company_id']);
    var companyparent_id = getStringValue(data['companyparent_id']);
    var step_name = getStringValue(data['step_name']);

    if ( pathname == '/dashboard')
    {
        // If I am on the dashboard when a workflow_complete hits, we need
        // to refresh it.
        if ( debug_event ) console.log("[EVENT]: (workflow_complete) You are on the DASHBOARD.");
        location.href = base_url + "dashboard";
    }
    if ( pathname == '/dashboard/parent' ) {
        if ( debug_event ) console.log("[EVENT]: (workflow_complete) You are on the PARENT DASHBOARD.");
        location.href = base_url + "dashboard/parent";
    }
    if ( pathname == '/dashboard/parent/workflow' ) {
        if ( debug_event ) console.log("[EVENT]: (workflow_complete) You are on ... what is parent/workflow?");
        location.href = base_url + "dashboard/parent/workflow";
    }
}
function workflow_start ( $workflow_name)
{
    var params = {};
    params.ajax = 1;
    params.url = base_url + 'widgettask/workflow/start/'+$workflow_name;

    $.post( params.url, securePostVariables(params) ).done(function( responseHTML ) {

        // Validate the ajax response.
        if ( ! ValidateAjaxResponse(responseHTML, params.url) ) { return; }

        try{
            var result = JSON.parse(responseHTML);
            console.log(result);
        }catch(err){
            return;
        }
    });
}
function workflow_widget_refresh( data )
{
    if ( debug_event ) console.log("[EVENT]: workflow_widget_refresh");

    var identifier = getStringValue(data['identifier']);
    var identifier_type = getStringValue(data['identifier']);
    var workflow = getStringValue(data['workflow']);
    var widget_name = getStringValue(data['widget_name']);
    var widget_refresh_callback = getStringValue(data['widget_refresh_callback']);

    if ( debug_event ) console.log("[EVENT]: refreshing " + widget_name);
    refreshWidget(widget_name, widget_refresh_callback);
}