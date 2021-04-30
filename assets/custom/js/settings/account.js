$(function(){

    $(document).on('click', '#edit_account_form #reset_button', function(e) {
        EditProfileResetHandler(this, e);
    });

    // Add a click handler on the form submit button.
    $(document).on('click', '#edit_account_form button[type="submit"]', function(e) {

        // Activate ajax submit on the form.
        var options = {
                beforeSubmit: EditAccountBeforeSubmit,
                success: EditAccountSuccessHandler,
                error: EditAccountErrorHandler,
    			data: {ajax: '1'}
        };
        $('#edit_account_form').ajaxForm(options);

        // Create the Javascript validation for this form.
        var edit_account_validator = undefined;
        if( $("#edit_account_form").length && edit_account_validator === undefined ) {
            edit_account_validator = $("#edit_account_form").validate({
                rules: {
                    firstname: {
                        required: true
                    },
                    lastname: {
                        required: true
                    },
                    email_address: {
                        required: true,
                        usernameChangeValidator: true
                    },
                },
                messages: {
                    firsname: "First name is required.",
                    lastname: "Last name is required."
                },
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
				unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
				errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
             });
             jQuery.validator.addMethod('usernameChangeValidator', usernameChangeValidator, 'User with that email address already exists.');
        }

        // Add a click handler that will validate the form.
        if ( $("#edit_account_form").validate().form() ) {
            return true;
        }
        return false;

    });




});
function usernameChangeValidator(value) {
    // If the current email address matches the original address allow it.
    var orig_email_address = getStringValue( $("#original_email_address").val() );
    if ( value == orig_email_address ) return true;
    var valid = usernameValidator(value);
    if ( valid ) {
        // Looks good.  Fill the original with the new value to prevent the
        // the change handler from flashing red as we close the dialog.
        $("#original_email_address").val(value);
        return true;
    }
    return false;
}
function EditProfileResetHandler(click_obj, e) {
    hideForm("edit_account_form", false, true);
}

function EditAccountBeforeSubmit() {
    beforeFormPost("edit_account_form");
}
function EditAccountSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "edit_account_form", responseText, true );
        startWidget("top_bar_widget", 0);
        startWidget("edit_profile_widget", 0);
    }catch(err){
        AJAXPanic("<div>"+err+"</div>" + responseText);
        return;
    }
}
function EditAccountErrorHandler( obj ) {
    failedFormPost( response['responseText'], "add_company_form" );
}
function UserPhoneResetSuccess( form_input_id )
{
    // We have successfully cleared the phone number.
    // Clear this value on the input.
    var input = $("input[name='"+form_input_id+"']");
    $(input).val("");
}
function UserPhoneResetFailed( form_input_id )
{
    // Nothing to do here.  We could not clear the phone number.
}
