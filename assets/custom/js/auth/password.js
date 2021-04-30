
$(function(){



    $(document).on('click', '#edit_password_form #reset_button', function(e) {
        EditPasswordResetHandler(this, e);
    });

    // Add a click handler on the form submit button.
    $(document).on('click', '#edit_password_form button[type="submit"]', function(e) {

        // Activate ajax submit on the form.
        var options = {
                beforeSubmit: EditPasswordBeforeSubmit,
                success: EditPasswordSuccessHandler,
                error: EditPasswordErrorHandler,
    			data: {ajax: '1'}
        };
        $('#edit_password_form').ajaxForm(options);

        // Create the Javascript validation for this form.
        var edit_password_validator = undefined;
        if( $("#edit_password_form").length && edit_password_validator === undefined ) {
            edit_password_validator = $("#edit_password_form").validate({
                rules: {
                    old_password: {
                        required: true,
                        oldPasswordValidator: true
                    },
                    new_password: {
                        required: true,
                        minlength: 7,
                        maxlength: 80,
                        mustHaveThreeOfTheseValidator: true
                    },
                    confirm_password: {
                        required: true,
                        minlength: 7,
                        maxlength: 80,
                        confirmPasswordValidator: true
                    }

                },
                messages: {
                    password: "Current password is required.",
                    new_password: {
                        required: "New password is required.",
                        maxlength: "Passwords can not exceed 80 characters.",
                        minlength: "Passwords must be at least 7 characters."
                    },
                    confirm_password: {
                        required: "Please enter your new password.",
                        maxlength: "Passwords can not exceed 80 characters.",
                        minlength: "Passwords must be at least 7 characters.",
                        confirmPasswordValidator: "Password does not match your new password."
                    }
                },
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
				unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
				errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
             });
             jQuery.validator.addMethod('oldPasswordValidator', oldPasswordValidator, 'Current password is not correct.');
             jQuery.validator.addMethod('mustHaveThreeOfTheseValidator', mustHaveThreeOfTheseValidator, 'Password must contain 3 of these: lowercase letters, capital letters, symbols or numbers.');
             jQuery.validator.addMethod('confirmPasswordValidator', confirmPasswordValidator, 'Passwords do not match.');
        }

        // Add a click handler that will validate the form.
        if ( $("#edit_password_form").validate().form() ) {
            
            // The form has been validated! Note this to the validator does
            // not think the old password is broken after it is updated on
            // the server.
            $("input[name='old_password']").attr("complete", "yes");
            
            return true;
        }
        return false;

    });
});




function EditPasswordBeforeSubmit() {
    beforeFormPost("edit_password_form");
}
function EditPasswordSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "edit_password_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function EditPasswordErrorHandler( jqXHR, textStatus, errorThrown ) {
    failedFormPost( jqXHR['responseText'], "edit_password_form" );
}

function oldPasswordValidator(value) {

    // Empty string should pass. Use the required validator to
    // catch empty if desired.
    if ( getStringValue(value) == "" ) return true;
    
    // If novalidate is set, it should pass.
    if ( $("input[name='old_password']").attr("complete") == "yes" ) return true;

    var p = {};
    p['ajax'] = 1;
    p['old_password'] = value;



    var url = replaceFor($("#edit_password_form").attr("action"), "save", "validate");

    var retval;
    var ajaxOptions = {
        type: 'POST',
        url: url,
        data: securePostVariables(p),
        async : false,
        dataType: "json",
        success: function(data) {
            if (data.validation) {
                //validation passed
                retval = true;
            }
            else {
                //validation failed
                retval = false;
            }
        }
    };
    $.ajax(ajaxOptions);
    return retval;
}
function mustHaveThreeOfTheseValidator(value) {
	if( getStringValue(value) == "" ) return false;
	var count = 0;

	if( containsLowerCaseLettersValidator(value) == true ) { count++ };
	if( containsUpperCaseLettersValidator(value) == true ) { count++ };
	if( containsNumbersValidator(value) == true ) { count++ };
	if( containsSymbolsValidator(value) == true ) { count++ };

	if(count >= 3) return true;
	return false;
}
function confirmPasswordValidator(value) {
	return stringsMustMatchValidator( value, $('#new_password').val() );
}
function EditPasswordResetHandler(click_obj, e) {
    var form = $(click_obj).closest("form");
    $(form).resetForm();
    form_reset(form);		// remove any error indicators.
}
