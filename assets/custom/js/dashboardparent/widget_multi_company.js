$(function()
{

    // Click Handler ( Report Review Button with no Dropdown )
    $(document).on('click', 'table td.action-cell a.action-cell-parent-reportreview', function(e) {
        if ( $(this).attr('disabled')) return;
        ListDownloadableReports(this, e);
    });

    // Click Handler ( Report Review Button with Dropdown )
    $(document).on('click', 'span.parent-report-review-button-container > ul > li', function(e) {
        ListDownloadableReports(this, e);
    });

    // Click Handler ( Actions Row Button with Dropdown )
    $(document).on('click', 'span.dropdown-action-button-container > ul.options-cell-parent-reportreview > li', function(e) {
        MultiCompanyRowActionButtonClickHandler(this, e);
    });

    // Click Handler ( Continue Button with no Dropdown )
    $(document).on('click', 'table td.action-cell a.action-cell-parent-continue', function(e) {
        MultiCompanyRowContinueButtonClickHandler(this, e);
    });

    // Click Handler ( Finalize Button with no Dropdown )
    $(document).on('click', 'table td.action-cell a.action-cell-parent-finalize', function(e) {
        MultiCompanyRowFinalizeButtonClickHandler(this, e);
    });




    // Click Handler ( Add Company Button )
    $(document).on('click', '#add_company_btn', function(e){
        refreshWidget('add_company_widget', 'showForm', 'add_company_form');
    });

    // Click Handler ( Action Button )
    $(document).on('click', '.multi-company-top-button-dropdown > ul > li', function(e){
        BulkActionMenuClickHandler(this,e);
    });


    $(document).on('click', '.parent-report-review-critical-warning-link', function(e) {
        ShowCriticalWarningsClickHandler(this,e);
    });
    $(document).on('click', '.parent-report-review-review-notice-link', function(e) {
        ShowReviewNoticesClickHandler(this,e);
    });

    $(document).on('click', '#report_warnings_form #no_btn', function(e){
        var form = $("#report_warnings_form");
        hideForm(form, true, true);
    });
    $(document).on('click', '#report_warnings_form #yes_btn', function(e){
        e.preventDefault();
        var form = $("#report_warnings_form");
        hideForm(form, true, true);
    });

    refreshWidget("multi_company_widget", "MultiCompanyWidgetRefreshed");

});
var debug_multi_company_widget = false;

/**
 * MultiCompanyWidgetRefreshed
 *
 * This function is called after the multi company widget has been refreshed and
 * it will manage showing the starting message and enabling/disabling the import
 * button. This allows us to have a directed experience when a parent is created
 * but has no companies.
 * 
 * @constructor
 */
function MultiCompanyWidgetRefreshed()
{
    var message = $('multi_company_starting_message');
    var table = $('#multi_company_table');
    var widget = $('#upload_button');

    var rows = $(table).find('tr');
    var has_rows = true;
    if ( ! rows.length ) has_rows = false;

    if ( has_rows )
    {
        // If we have companies, hide the starting message and
        // show the table.
        $(message).addClass('hidden');
        $(table).removeClass('hidden');

        $(widget).removeClass('btn-working');
        $(widget).addClass('btn-default');
        $(widget).prop('disabled', false);

    }
    else
    {
        // If we have NO companies, show the starting message and
        // hide the table.
        $(message).removeClass('hidden');
        $(table).addClass('hidden');

        $(widget).addClass('btn-working m-r-5');
        $(widget).removeClass('btn-default');
        $(widget).prop('disabled', true);
    }
}
function MultiCompanyRowFinalizeButtonClickHandler(click_obj, e) {
    e.preventDefault();

    // Collect objects in play.
    var button = $(click_obj);

    // This must be for a company
    var identifier_type = $(button).data('identifier_type');
    if ( getStringValue(identifier_type) !== 'company' ) return;

    var company_id = $(button).data('identifier');
    FinalizeReports(company_id);

}
function MultiCompanyRowContinueButtonClickHandler(click_obj, e)
{
    e.preventDefault();

    // Collect objects in play.
    var button = $(click_obj);
    var href = $(button).attr("href");

    // This must be for a company
    var identifier_type = $(button).data('identifier_type');
    if ( getStringValue(identifier_type) !== 'company' ) return;

    // Find the landing location.
    var landing = $(button).data("landing");

    // Place the HREF on the widget.
    var widget_name = "changeto_company_widget";
    var widget = $("#" + widget_name);
    $(widget).attr("data-href", href);

    // Construct a payload for the callback function.
    var params = {};
    params['form_name'] = "changeto_company_form";
    params['landing'] = landing;

    refreshWidget( widget_name, "changeToWithLanding", params );


}
function changeToWithLanding( data, callback )
{
    // pull the form_name and landing location out of the data payload.
    var form_name = getStringValue(data['form_name']);
    var landing = getStringValue(data['landing']);

    // Grab the form object and stash the landing location on the form.
    var form = $("#" + form_name);
    var input = $(form).find('input[name="landing"]');
    $(input).val( landing );

    // show the updated changeto form that also has a landing location.
    showForm( form_name, callback );
}
function ShowReportWarningForm()
{
    showForm("report_warnings_form", "InitParentReportReviewTable");

    setTimeout( function() {
        var table = $("#report_review_warning_table");
        if ( ! $(table).hasClass("dataTable") )
        {
            try {
                $(table).DataTable(
                    {
                        "bFilter": false,
                        "bInfo": false,
                        "initComplete": function(settings, json) {
                            var paginate = $("#report_review_warning_table_paginate");
                            var paginate_parent = $(paginate).parent();
                            var paginate_dropdown = $("#report_review_warning_table_length");

                            // Make the parent span the full row, not 1/2 of it.
                            $(paginate_parent).removeClass('col-sm-6').addClass('col-sm-12');

                            // Pull the paginate buttons to the left side of the screen.
                            $(paginate).addClass("pull-left");

                            // Hide the paginate dropdown.  In a dialog, limit the rows to 10.
                            $(paginate_dropdown).addClass("hidden");

                            var search = $("#report_review_warning_table_filter");
                            var search_parent = $(search).parent();

                            $(search_parent).removeClass("col-sm-6").addClass("col-sm-12");

                            var top_row = $(search_parent).parent();
                            $(top_row).find('div:first').remove();
                        }
                    }
                );
            }catch(err){
                console.log("ERROR: " + err);
            }
        }

    }, 300 );



}



function ShowCriticalWarningsClickHandler(click_obj, e)
{
    var widget_name = "warnings_reports_widget";
    var widget = $("#"+widget_name);
    var company_id = $(click_obj).data('companyid');

    // Save the HREF as a TEMPLATE if needed.
    var url = $(widget).data("href");
    var template = $(widget).data("template");
    if ( getStringValue(template) == "" ){
        $(widget).attr("data-template", url);
    }

    // Build the HREF from the TEMPLATE
    url = $(widget).data("template");
    url = replaceFor(url, "TYPE", "critical");
    url = replaceFor(url, "COMPANYID", company_id);
    $(widget).attr("data-href", url);

    // refresh the widget.
    refreshWidget( widget_name, "ShowReportWarningForm" );

}

function ShowReviewNoticesClickHandler(click_obj, e)
{
    var widget_name = "warnings_reports_widget";
    var widget = $("#"+widget_name);
    var company_id = $(click_obj).data('companyid');

    // Save the HREF as a TEMPLATE if needed.
    var url = $(widget).data("href");
    var template = $(widget).data("template");
    if ( getStringValue(template) == "" ){
        $(widget).attr("data-template", url);
    }

    // Build the HREF from the TEMPLATE
    url = $(widget).data("template");
    url = replaceFor(url, "TYPE", "notices");
    url = replaceFor(url, "COMPANYID", company_id);
    $(widget).attr("data-href", url);

    // refresh the widget.
    refreshWidget( widget_name, "ShowReportWarningForm" );

}

function BulkActionMenuClickHandler(click_obj, e)
{
    var li = $(click_obj);
    var code = $(li).data('value');
    if ( debug_multi_company_widget) console.log("code["+code+"]");
    if ( code == 'FINALIZE' )
    {
        FinalizeMultipleCompanies();
    }
    else if ( code == 'SKIP_MONTH' )
    {
        e.preventDefault();
        SkipMonthMultipleCompanies();
    }
}
function SkipMonthMultipleCompanies()
{
    // Find all of the checkboxes.
    var selected = [];
    var table = $("#multi_company_table");
    $(table).find("input[type='checkbox']").each(function(){
        var checkbox = $(this);
        if ( $(checkbox).is(':checked') )
        {
            selected[selected.length] = checkbox;
        }
    });

    var companies = [];
    for(var i=0;i<selected.length;i++)
    {
        var checkbox = selected[i];
        var tr = $(checkbox).closest('tr');
        var company_id = $(tr).data('companyid');
        companies[companies.length] = company_id;
    }

    if ( companies.length > 0 )
    {
        SkipMonth(companies);
    }
}
function FinalizeMultipleCompanies()
{
    // Find all of the checkboxes.
    var selected = [];
    var table = $("#multi_company_table");
    $(table).find("input[type='checkbox']").each(function(){
        var checkbox = $(this);
        if ( $(checkbox).is(':checked') )
        {
            selected[selected.length] = checkbox;
        }
    });

    var companies = [];
    for(var i=0;i<selected.length;i++)
    {
        var checkbox = selected[i];
        var tr = $(checkbox).closest('tr');
        var company_id = $(tr).data('companyid');
        companies[companies.length] = company_id;
    }

    if ( companies.length > 0 )
    {
        FinalizeReports(companies);
    }


}

function DrawMultiCompanyRowWorkflowChangingButtons(multi_company_row_object)
{
    if ( $(multi_company_row_object).length )
    {
        var tr = $(multi_company_row_object).closest('tr');
        var td = $(tr).find('td.action-cell');
        $(td).find('a.action-cell-edit').addClass("hidden");
        $(td).find('a.action-cell-remove').addClass("hidden");
        $(td).find('a.action-cell-changeto').addClass("hidden");
        $(td).find('a.action-cell-parent-reportreview').addClass("hidden");
    }
}
function MultiCompanyRowWorkflowEventChangingEventHandler(company_id)
{
    if ( debug_multi_company_widget ) console.log("MultiCompanyRowWorkflowEventChangingEventHandler: started");

    if ( jQuery.type(company_id) === "undefined" ) { company_id = ""; }
    if ( company_id == "") return;

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

    // RUNTIME ERROR
    // Once an event starts processing, hide the runtime error message ... if there is one.
    // it will come back when the widget refreshes.
    var runtime_error = $("div.runtime-error");

    $(status_container_span).text("Please wait, processing user request.");
    $(status_container).removeClass("hidden");
    $(message_container).addClass("hidden");
    $(complete_container).addClass("hidden");
    $(runtime_error).addClass("hidden");

    // You just showed the status container, which is the busy wheel.  Show the working status color.
    UpdateMultiCompanyWidgetStatus(company_id, 'working');

    // Update this row so that we show only the buttons allowed during a changing event.
    DrawMultiCompanyRowWorkflowChangingButtons(message_container);

}
function UpdateMultiCompanyWidgetRuntimeError( company_id, error_message )
{
    if (debug_multi_company_widget) console.log("UpdateMultiCompanyWidgetRuntimeError: started");

    if ( getStringValue(company_id) === '' ) return;
    error_message = getStringValue(error_message);

    var table = $('#multi_company_table');
    var tr = $('tr[data-companyid="'+company_id+'"]:first');

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

    // RUNTIME ERROR
    // Once an event starts processing, hide the runtime error message ... if there is one.
    // it will come back when the widget refreshes.
    var runtime_error = $(tr).find("div.runtime-error");

    // Hide everything.
    $(complete_container).addClass("hidden");
    $(status_container).addClass("hidden");
    $(runtime_error).addClass("hidden");
    $(message_container).addClass("hidden");

    // Show only the error.
    $(message_container_span).html("");
    $(message_container).removeClass("hidden");
    $(runtime_error).html(error_message);
    $(runtime_error).removeClass("hidden");

    // Turn off the colored indicator.
    UpdateMultiCompanyWidgetStatus(company_id, '');

}
function UpdateMultiCompanyWidgetStatus(company_id, status)
{
    if (debug_multi_company_widget) console.log("UpdateMultiCompanyWidgetStatus: started.  moving to status ["+status+"]");

    var table = $('#multi_company_table');
    var tr = $('tr[data-companyid="'+company_id+'"]:first');
    var td = $(tr).find('td.status-column:first');
    var i = $(td).find('i.status-indicator');

    // Remove all colors
    $(i).removeClass("status-indicator-attention");
    $(i).removeClass("status-indicator-working");

    // Add back the color that matches the status passed in.
    if ( status === 'attention' ) $(i).addClass("status-indicator-attention");
    if ( status === 'working' ) $(i).addClass("status-indicator-working");

}
function UpdateMultiCompanyWidgetButtons(company_id, button_html)
{
    if (debug_multi_company_widget) console.log("UpdateMultiCompanyWidgetButtons: started");
    if ( getStringValue(company_id) === '' ) return;

    var table = $('#multi_company_table');
    var tr = $('tr[data-companyid="'+company_id+'"]:first');

    var container = $(tr).find('div.action-buttons');
    $(container).empty();
    $(container).html(button_html);

}
function UpdateMultiCompanyWidgetMessage(company_id, message, working)
{
    if (debug_multi_company_widget) console.log("UpdateMultiCompanyWidgetMessage: started");

    if ( jQuery.type(working) != "boolean" ) working = false;
    if ( getStringValue(company_id) === '' ) return;


    var table = $('#multi_company_table');
    var tr = $('tr[data-companyid="'+company_id+'"]:first');


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

    // RUNTIME ERROR
    // Once an event starts processing, hide the runtime error message ... if there is one.
    // it will come back when the widget refreshes.
    var runtime_error = $(tr).find("div.runtime-error");

    // Hide everything.
    $(complete_container).addClass("hidden");
    $(status_container).addClass("hidden");
    $(runtime_error).addClass("hidden");
    $(message_container).addClass("hidden");

    if ( working )
    {
        // If we are "working" then the spinner is rolling.
        $(status_container_span).html(message);
        $(status_container).removeClass("hidden");
        UpdateMultiCompanyWidgetStatus(company_id, 'working');
    }
    else
    {
        // If we show a message without a spinner, hide the color indicator
        // it will come back via an event if the company needs attention.
        $(message_container_span).html(message);
        $(message_container).removeClass("hidden");
        UpdateMultiCompanyWidgetStatus(company_id, '');
    }

}

// call the server and get all the information needed to refresh the row for this company
// without refreshing the whole widget.
function RefreshMultiCompanyRow( company_id )
{
    if (debug_multi_company_widget) console.log("RefreshMultiCompanyRow: started");

    company_id = getStringValue(company_id);
    if ( company_id === '' ) return;
    if (debug_multi_company_widget) console.log("RefreshMultiCompanyRow: company["+company_id+"]");



    var url = base_url + "dashboard/parent/widget/multicompany/details/" + company_id;
    var params = {};
    $.get( url, params ).done(function( responseHTML ) {

        // Validate the ajax response.
        if ( ! ValidateAjaxResponse(responseHTML, url) ) {
            if ( debug_multi_company_widget ) { console.log("RefreshMultiCompanyRow["+company_id+"]: Got a template page back from the server. ALL STOP."); }
            return;
        }

        // Parse the AJAX Repsonse.
        try{
            if ( debug_multi_company_widget ) { console.log("RefreshMultiCompanyRow["+company_id+"]: Got a response back from the server."); }

            var result = JSON.parse(responseHTML);
            var responseText = result['responseText'];
            var payload = JSON.parse(responseText);
            if ( debug_multi_company_widget ) console.log(payload);

            var busy = payload['busy'];
            var error = payload['error'];
            var status = payload['status'];
            var refresh = payload['refresh'];
            var buttons = payload['buttons'];
            var message = getStringValue(payload['message']);

            if ( refresh )
            {
                refreshWidget("multi_company_widget", "MultiCompanyWidgetRefreshed");
            }
            else if ( error )
            {
                UpdateMultiCompanyWidgetRuntimeError( company_id, message );
            }
            else
            {
                UpdateMultiCompanyWidgetMessage( company_id, message, busy );

                if ( debug_multi_company_widget ) console.log("RefreshMultiCompanyRow["+company_id+"]: Message just refreshed to green, but now i'm setting it to ["+status+"]");
                UpdateMultiCompanyWidgetStatus( company_id, status);
            }

            // Always update the buttons.
            UpdateMultiCompanyWidgetButtons(company_id, buttons);

        }catch(err){
            // Shutdown the task.
            if ( debug_multi_company_widget ) { console.log("RefreshMultiCompanyRow["+company_id+"]: ERROR["+err+"]."); }
            return;
        }


    }).fail(function( jqXHR, textStatus, errorThrown ) {

        if ( debug_multi_company_widget ) { console.log("RefreshMultiCompanyRow["+company_id+"]: Got a failure response back from the server. ALL STOP."); }
        if ( debug_multi_company_widget ) { console.log(jqXHR.responseText); }

    });


}
