$(function(){

    // Default Carrier Form ( Cancel Button Click Handler )
    $(document).on('click', '#cancel_default_plan_form', function(e) {
        var form = $("#default_plan_form");
        hideForm(form, true, true);
    });

    // Default Carrier Form ( Submit Button Click Handler )
    $(document).on('click', '#default_plan_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: DefaultPlanFormBeforeSubmit,
            success: DefaultPlanFormSuccessHandler,
            error: DefaultPlanFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#default_plan_form').ajaxForm(options);

        // Define the form validation.
        var default_plan_form_validator = undefined;

        if( $("#default_plan_form").length ) {
            default_plan_form_validator = $("#default_plan_form").validate({
                rules: { },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#default_plan_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });


});

function DefaultPlanFormBeforeSubmit() {
    beforeFormPost("default_plan_form");
}
function DefaultPlanFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "default_plan_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function DefaultPlanFormErrorHandler(response) {
    failedFormPost( response['responseText'], "default_plan_form" );
}
