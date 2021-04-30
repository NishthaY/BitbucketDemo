$(function(){

    // Default Carrier Form ( Cancel Button Click Handler )
    $(document).on('click', '#cancel_default_clarifications_form', function(e) {
        var form = $("#default_clarifications_form");
        hideForm(form, true, true);
    });

    // Default Carrier Form ( Submit Button Click Handler )
    $(document).on('click', '#default_clarifications_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: DefaultClarificationsFormBeforeSubmit,
            success: DefaultClarificationsFormSuccessHandler,
            error: DefaultClarificationsFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#default_clarifications_form').ajaxForm(options);

        // Define the form validation.
        var default_clarifications_form_validator = undefined;

        if( $("#default_clarifications_form").length ) {
            default_carrier_form_validator = $("#default_clarifications_form").validate({
                rules: {
                    default_clarifications_code: "required"
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#default_clarifications_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });


});

function DefaultClarificationsFormBeforeSubmit() {
    beforeFormPost("default_clarifications_form");
}
function DefaultClarificationsFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "default_clarifications_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }

}
function DefaultClarificationsFormErrorHandler(response) {
    failedFormPost( response['responseText'], "default_clarifications_form" );
}
