$(function(){
    // Wizard Start Here Button ( Click Handler )
    $(document).on('click', "#start_button", function(e){
        StartButtonClickHandler(this, e);
    });

    // Getting Started Form - Cancel Button - ( Click Handler )
    $(document).on('click', "#getting_started_form #cancel_getting_started_button", function(e){
        StartButtonCancelClickHandler(this, e);
    });

    // Getting Started Form - Save Button - ( Click Handler )
    $(document).on('click', "#getting_started_form #save_getting_started_button", function(e){

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: GettingStartedBeforeSubmit,
            success: GettingStartedSuccessHandler,
            error: GettingStartedErrorHandler,
            data: {ajax: '1'}
        };
        $('#getting_started_form').ajaxForm(options);

        // Define the form validation.
        var getting_started_form_validator = undefined;

        if( $("#getting_started_form").length ) {
            getting_started_form_validator = $("#getting_started_form").validate({
                rules: {
                    month: {
                        required: true
                    },
                    year: {
                        required: true
                    }
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#getting_started_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });

});
function StartButtonClickHandler(click_obj, e)
{
    e.preventDefault();

    var widget_name = "getting_started_widget";
    var form_name = "getting_started_form";
    var widget = $("#" + widget_name);
    var href = $(widget).data("href");

    // If the URL has the COMPANYID tag in it, store the URL off as
    // a template and build the URL using the company_id provided
    // in the click object attribute.
    if ( href.indexOf("COMPANYID") > -1 )
    {
        var company_id = $(click_obj).data('companyid');
        var template = $(widget).data("template");

        if ( getStringValue(template) == "" )
        {
            $(widget).attr('data-template', href);
            template = $(widget).data("template");
        }
        href = replaceFor(template, 'COMPANYID', company_id);
        $(widget).attr('data-href', href);
    }
    refreshWidget( widget_name, "showForm", form_name );
}
function StartButtonCancelClickHandler(click_obj, e) {
    var form = $("#getting_started_form");
    hideForm(form, true, true);
}
function GettingStartedBeforeSubmit() {
    beforeFormPost("getting_started_form");
}
function GettingStartedSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        var result = JSON.parse(responseText);
        if ( result['type'] == "success" ) {
            successfulFormPost( "getting_started_form", responseText, true );
            refreshWidget("wizard_dashboard_widget");
            refreshWidget("dashboard_welcome_widget");
            return;
        }

        if ( result['type'] == "danger" ){
            var message = result['message'];
            var alert_obj = $(form).parent().find(".alert:first");
            ShowAlert(alert_obj, "danger", message);
            return;
        }
        throw "Unsupported repsonse type.";

    }catch(err){
        AJAXPanic(err + " " + responseText);
        return;
    }
}
function GettingStartedErrorHandler(response) {
    failedFormPost( response['responseText'], "getting_started_form" );
}
