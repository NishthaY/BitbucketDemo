$(function(){

    // Click Handler ( Ticket Create Submit Button Handler )
    $(document).on('click', '#update_phone_form #submit_button', function(e) {

		// Create the AJAX hooks for submitting this form.
		var options = {
			beforeSubmit: UpdatePhoneFormBeforeSubmit,
			success: UpdatePhoneFormSuccessHandler,
			error: UpdatePhoneFormErrorHandler,
			data: {ajax: '1'}
		};
		$('#update_phone_form').ajaxForm(options);

		// Define the form validation.
		var update_phone_form_validator = undefined;

		if( $("#update_phone_form").length ) {
			update_phone_form_validator = $("#update_phone_form").validate({
				rules: {
					phone: {
                        required: true
					}
				},
				messages: {
					email_address: {
						required: "Please enter a phone number."
					}
				},
				highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
				unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
				errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
			});
		}

		// Validate the form.
		if ( $("#update_phone_form").validate().form() ) {
			return true;
		}
		return false; // Form not valid.
    });

});
function UpdatePhoneFormBeforeSubmit() {
    beforeFormPost("update_phone_form");
}
function UpdatePhoneFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText) ) { return; }
    try{
        successfulFormPost( "update_phone_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function UpdatePhoneFormErrorHandler(jqXHR, textStatus, errorThrown) {
    failedFormPost( jqXHR['responseText'], "update_phone_form" );
}
