$(function(){

    $(document).on('click', '#cancel_file_transfer_form', function(e) {
        var form = $("#file_transfer_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Add Company Parent Submit Button Handler )
    $(document).on('click', '#file_transfer_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: FileTransferFormBeforeSubmit,
            success: FileTransferFormSuccessHandler,
            error: FileTransferFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#file_transfer_form').ajaxForm(options);

        // Define the form validation.
        var file_transfer_form_validator = undefined;

        if( $("#file_transfer_form").length ) {
            file_transfer_form_validator = $("#file_transfer_form").validate({
                rules: {
                    hostname: {
                        required: true
                    },
                    username: {
                        required: true
                    },
                    password: {
                        required: true
                    },
                    destination: {
                        required: true
                    },
                    port: {
                        required: true,
                        numeric: true
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
        if ( $("#file_transfer_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });


});

function FileTransferFormBeforeSubmit() {
    beforeFormPost("file_transfer_form");
}
function FileTransferFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "file_transfer_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }

}
function FileTransferFormErrorHandler(response) {
    failedFormPost( response['responseText'], "file_transfer_form" );
}

