$(function(){

	$(document).on('click', 'a[data-type="form-link"][data-ajax="1"]', function (e) {
		AJAXFormLinkHandler(this, e);
	});

    // Click Handler ( Ticket Create Submit Button Handler )
    $(document).on('click', '#verify_code_form #submit_button', function(e) {

		// Create the AJAX hooks for submitting this form.
		var options = {
			beforeSubmit: VerifyCodeFormBeforeSubmit,
			success: VerifyCodeFormSuccessHandler,
			error: VerifyCodeFormErrorHandler,
			data: {ajax: '1'}
		};
		$('#verify_code_form').ajaxForm(options);

		// Define the form validation.
		var verify_code_form_validator = undefined;

		if( $("#verify_code_form").length ) {
			verify_code_form_validator = $("#verify_code_form").validate({
				rules: {
					code: {
                        required: true
					}
				},
				messages: {
					email_address: {
						required: "Please enter a SMS code."
					}
				},
				highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
				unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
				errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
			});
		}

		// Validate the form.
		if ( $("#verify_code_form").validate().form() ) {
			return true;
		}
		return false; // Form not valid.
    });

});
function VerifyCodeFormBeforeSubmit() {
    beforeFormPost("verify_code_form");
}
function VerifyCodeFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText) ) { return; }
    try{
        successfulFormPost( "verify_code_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function VerifyCodeFormErrorHandler(jqXHR, textStatus, errorThrown) {
    failedFormPost( jqXHR['responseText'], "verify_code_form" );
}
function AJAXFormLinkHandler( click_obj, e )
{
	e.preventDefault();

    var alert_obj = $("div.panel-body").find(".alert:first");
	HideAlert(alert_obj);

	var url = $(click_obj).attr('href');
	var params = {};
	params['url'] = url;

    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        // Validate the ajax response.

        if ( ! ValidateAjaxResponse(responseHTML, url) ) {
            return;
        }

        try{

            var result = JSON.parse(responseHTML);
            var responseText = getStringValue(result["responseText"]);

            var status = result['status'];
            if ( status == true )
			{
                var type = result['type'];
                var message = result['message'];


                ShowAlert( alert_obj, type, message );
			}


        }catch(err){

            return;
        }

    }).fail(function( jqXHR, textStatus, errorThrown ) {

    });

}
