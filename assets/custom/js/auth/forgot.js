$(function(){

    // Click Handler ( Ticket Create Submit Button Handler )
    $(document).on('click', '#reset_password_form #submit_button', function(e) {

		// Create the AJAX hooks for submitting this form.
		var options = {
			beforeSubmit: ResetPasswordFormBeforeSubmit,
			success: ResetPasswordFormSuccessHandler,
			error: ResetPasswordFormErrorHandler,
			data: {ajax: '1'}
		};
		$('#reset_password_form').ajaxForm(options);

		// Define the form validation.
		var reset_password_form_validator = undefined;

		if( $("#reset_password_form").length ) {
			reset_password_form_validator = $("#reset_password_form").validate({
				rules: {
					email_address: {
                        required: true
					}
				},
				messages: {
					email_address: {
						required: "Please enter an email address."
					}
				},
				highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
				unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
				errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
			});
		}

		// Validate the form.
		if ( $("#reset_password_form").validate().form() ) {
			return true;
		}
		return false; // Form not valid.
    });

});
function ResetPasswordFormBeforeSubmit() {
    beforeFormPost("reset_password_form");
}
function ResetPasswordFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText) ) { return; }
    try{
        successfulFormPost( "reset_password_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function ResetPasswordFormErrorHandler(jqXHR, textStatus, errorThrown) {
    failedFormPost( jqXHR['responseText'], "reset_password_form" );
}
