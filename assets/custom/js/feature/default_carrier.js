$(function(){

    // Default Carrier Form ( Cancel Button Click Handler )
    $(document).on('click', '#cancel_default_carrier_form', function(e) {
        var form = $("#default_carrier_form");
        hideForm(form, true, true);
    });

    // Default Carrier Form ( Submit Button Click Handler )
    $(document).on('click', '#default_carrier_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: DefaultCarrierFormBeforeSubmit,
            success: DefaultCarrierFormSuccessHandler,
            error: DefaultCarrierFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#default_carrier_form').ajaxForm(options);

        // Define the form validation.
        var default_carrier_form_validator = undefined;

        if( $("#default_carrier_form").length ) {
            default_carrier_form_validator = $("#default_carrier_form").validate({
                rules: {
                    default_carrier_code: "required"
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#default_carrier_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });


});

function DefaultCarrierFormBeforeSubmit() {
    beforeFormPost("default_carrier_form");
}
function DefaultCarrierFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "default_carrier_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }

}
function DefaultCarrierFormErrorHandler(response) {
    failedFormPost( response['responseText'], "default_carrier_form" );
}
