$(function(){

    $(document).on('click', '#cancel_column_normalization_form', function(e) {
        var form = $("#column_normalization_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Add Company Parent Submit Button Handler )
    $(document).on('click', '#column_normalization_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: ColumnNormalizationFormBeforeSubmit,
            success: ColumnNormalizationFormSuccessHandler,
            error: ColumnNormalizationFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#column_normalization_form').ajaxForm(options);

        // Define the form validation.
        var column_normalization_form_validator = undefined;

        if( $("#column_normalization_form").length ) {
            column_normalization_form_validator = $("#column_normalization_form").validate({
                rules: { },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#column_normalization_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });


});

function ColumnNormalizationFormBeforeSubmit() {
    beforeFormPost("column_normalization_form");
}
function ColumnNormalizationFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "column_normalization_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }

}
function ColumnNormalizationFormErrorHandler(response) {
    failedFormPost( response['responseText'], "column_normalization_form" );
}
