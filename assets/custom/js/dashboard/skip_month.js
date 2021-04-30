$(function(){

    // Click Handler - Skip Month Dashboard Button
    $(document).on('click', "#skip_button", function(e){
        SkipButtonClickHandler(this, e);
    });

    // Click Handler - Cancel Skip Month Processing.
    $(document).on('click', "#skip_month_processing_form #cancel_skip_month_processing_button", function(e){
        StartButtonCancelClickHandler(this, e);
    });

    // Click Handler - Submit Skip Month Processing.
    $(document).on('click', "#skip_month_processing_form #skip_month_processing_button", function(e){

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: SkipMonthFormBeforeSubmit,
            success: SkipMonthFormSuccessHandler,
            error: SkipMonthFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#skip_month_processing_form').ajaxForm(options);

        // Define the form validation.
        var skip_month_form_validator = undefined;

        if( $("#skip_month_form_validator").length ) {
            skip_month_form_validator = $("#skip_month_processing_form").validate({
                rules: { },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#skip_month_processing_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });

});
var debug_skipmonth = false;
function StartButtonCancelClickHandler(click_obj, e) {
    if ( debug_skipmonth ) console.log("StartButtonCancelClickHandler: started");
    var form = $("#skip_month_processing_form");
    hideForm(form, true, true);
}
function SkipMonthFormBeforeSubmit()
{
    if ( debug_skipmonth ) console.log("SkipMonthFormBeforeSubmit: started");
    beforeFormPost("skip_month_processing_form");

    // Here we are going to display a spinner while the file is restored
    // from the archive.  We can go ahead and hide the form.  We are done with it.
    hideForm('skip_month_processing_form', true, true);
    ShowSpinner();
}
function SkipMonthFormSuccessHandler(responseText, statusText, xhr, form)
{
    if ( debug_skipmonth ) console.log("SkipMonthFormSuccessHandler: started");
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        var result = JSON.parse(responseText);

        if ( result['type'] == "success" ) {

            refreshWidget("wizard_dashboard_widget");
            refreshWidget("dashboard_welcome_widget");
            HideSpinner();
            return;
        }

        if ( result['type'] == "danger" ){
            var message = result['message'];
            var alert_obj = $(form).parent().find(".alert:first");
            ShowAlert(alert_obj, "danger", message);
            throw message;
            return;
        }
        throw "Unsupported repsonse type.";

    }catch(err){
        AJAXPanic(err + " " + responseText);
        HideSpinner();
        return;
    }
}
function SkipMonthFormErrorHandler(response)
{
    if ( debug_skipmonth ) console.log("SkipMonthFormErrorHandler: started");
    failedFormPost( response['responseText'], "skip_month_processing_form" );
}
function SkipButtonClickHandler( click_obj, e )
{
    if ( debug_skipmonth ) console.log("SkipButtonClickHandler: started");
    e.preventDefault();

    var button = $(click_obj);

    // Find the form that contains the hidden input with the colon delimited
    // list of company ids.  Explode tht string into an array and call SkipMonth
    // to display the modal dialog.
    var form = $("#skip_month_processing_form");
    var packed_string = $('form input[name="companies"]').val();
    var companies = explode("-", packed_string);
    if ( companies != false )
    {
        SkipMonth(companies)
    }
}
function SkipMonth(companies)
{
    if ( debug_skipmonth ) console.log("SkipMonth: started");
    var widget_name = "skip_month_widget";
    var form_name = "skip_month_processing_form";
    var widget = $("#" + widget_name);
    var href = $(widget).data("href");
    var form = $("#" + form_name);
    var companies_input = $(form).find('input[name="companies"]');

    var packed_string = "";
    for(var i=0;i<companies.length;i++)
    {
        packed_string += companies[i] + "-";
    }
    packed_string = fLeftBack(packed_string, "-");
    $(companies_input).val(packed_string);

    if ( href.indexOf("COMPANYIDS") > -1 )
    {
        var template = $(widget).data("template");

        if ( getStringValue(template) == "" )
        {
            $(widget).attr('data-template', href);
            template = $(widget).data("template");
        }
        href = replaceFor(template, 'COMPANYIDS', packed_string);
        $(widget).attr('data-href', href);
    }

    var debug = {};
    debug['href'] = href;
    debug['packed_string'] = packed_string;
    debug['widget_name'] = widget_name;
    debug['form_name'] = form_name;
    //pprint_r(debug);

    refreshWidget( widget_name, "showForm", form_name );
}

