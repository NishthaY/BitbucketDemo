$(function(){

    ActivateAlerts();

    // Click Handler ( Ticket Create Submit Button Handler )
    $(document).on('click', '#login_form #submit_button', function(e) {

		// Create the AJAX hooks for submitting this form.
		var options = {
			beforeSubmit: LoginFormBeforeSubmit,
			success: LoginFormSuccessHandler,
			error: LoginFormErrorHandler,
			data: {ajax: '1'}
		};
		$('#login_form').ajaxForm(options);

		// Define the form validation.
		var login_form_validator = undefined;

		if( $("#login_form").length ) {
			login_form_validator = $("#login_form").validate({
				rules: {
					email_address: {
                        required: true
					},
					password: "required"
				},
				messages: {
					email_address: {
						required: "Please enter an email address."
					},
					password: {
                        required: "Password is required."
                    }
				},
				highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
				unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
				errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
			});
		}

		// Validate the form.
		if ( $("#login_form").validate().form() ) {
			return true;
		}
		return false; // Form not valid.
    });

});
function LoginFormBeforeSubmit() {
    beforeFormPost("login_form");
}
function LoginFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "login_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function LoginFormErrorHandler(jqXHR, textStatus, errorThrown) {
    failedFormPost( jqXHR['responseText'], "login_form" );
}
