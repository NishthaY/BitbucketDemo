$(function()
{

});
var debug_notifications = false;


function BackgroundTaskStatusMessageEventHandler( data )
{
    try
    {
        if ( debug_notifications ) console.log("BackgroundTaskStatusMessageEventHandler: (notification.js) started");

        var result = JSON.parse(data);
        var job_id = getStringValue(result['JobId']);
        var words = getStringValue(result['Words']);
        var age = getStringValue(result['Age']);
        var company_id = getStringValue(result['CompanyId']);
        var companyparent_id = getStringValue(result['CompanyParentId']);

        if ( debug_notifications ) console.log("BackgroundTaskStatusMessageEventHandler: company_id["+company_id+"]");
        if ( debug_notifications ) console.log("BackgroundTaskStatusMessageEventHandler: companyparent_id["+companyparent_id+"]");
        if ( debug_notifications ) console.log("BackgroundTaskStatusMessageEventHandler: words["+words+"]");

        if ( company_id !== "" )
        {

            // If we have a company_id, look for the background status message by class and only
            // grab the ones that are tagged with the companyid.

            // STATUS CONTAINER
            // This is where we will report the progress of the background task.
            var status_container = $("span.background-task-status-message-container[data-companyid=\""+company_id+ "\"]");
            var status_container_span =      $("span.background-task-status-message[data-companyid=\""+company_id+ "\"]");

            // MESSAGE CONTAINER
            // This is the message shown when there is no background task.
            var message_container =       $("span.background-task-message-container[data-companyid=\""+company_id + "\"]");
            var message_container_span =            $("span.background-task-message[data-companyid=\""+company_id+ "\"]");

            // COMPLETE CONTAINER
            // This is the message shown once the background task is done, but before the widget is refreshed.
            var complete_container = $("span.background-task-complete-container[data-companyid=\""+company_id + "\"]");

            $(status_container_span).text(words);
            if ( debug_notifications ) console.log("WORDS["+words+"]");
            if ( getStringValue(words) == "" )
            {
                $(status_container).addClass("hidden");
                $(complete_container).removeClass("hidden");
                $(message_container_span).html("&nbsp;");

                RefreshMultiCompanyRow( company_id );
            }
            else
            {
                $(status_container).removeClass("hidden");
                $(message_container).addClass("hidden");
                $(complete_container).addClass("hidden");
                UpdateMultiCompanyWidgetStatus(company_id, 'working'); // Does this fix it?
            }
        }

        if ( companyparent_id !== "" )
        {

            // We have a companyparent_id!  This is the background task notification for the
            // actual dashboard.  Update this at the bottom of the screen.  This background
            // task is not by class, but by ID.


            var result = JSON.parse(data);
            var job_id = getStringValue(result['JobId']);
            var words = getStringValue(result['Words']);
            var age = getStringValue(result['Age']);


            var container = $("#background-task-status-message-container");
            var span = $("#background-task-status-message");
            $(span).text(words);

            if ( getStringValue(words) == "" )
            {
                $(container).addClass("hidden");
            }
            else
            {
                $(container).removeClass("hidden");
            }

        }


    }
    catch(err)
    {
        //alert(err);
    }

}