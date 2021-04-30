$(function(){

    // Click Handler ( Rollback Action Icons )
    $(document).on('click', 'table td.action-cell a.action-cell-rollback', function(e) {
        RollbackCompanyClickHandler(this, e);
    });
    // Click Handler ( Rollback Company No Button Handler )
    $(document).on('click', '#rollback_company_form #no_btn', function(e) {
        var form = $("#rollback_company_form");
        hideForm(form, true, true);
    });
    // Click Handler ( Rollback Company Yes Button Handler )
    $(document).on('click', '#rollback_company_form #yes_btn', function(e) {
        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: RollbackCompanyBeforeSubmit,
            success: RollbackCompanySuccessHandler,
            error: RollbackCompanyErrorHandler,
            data: {ajax: '1'}
        };
        $('#rollback_company_form').ajaxForm(options);

        // Define the form validation.
        var rollback_company_form_validator = undefined;

        if( $("#rollback_company_form").length ) {
            rollback_company_form_validator = $("#rollback_company_form").validate({
                rules: { },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#rollback_company_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.
    });



});

function RollbackCompanyBeforeSubmit() {
    beforeFormPost("rollback_company_form");
    hideForm("rollback_company_form", true, true);
    ShowSpinner("Cleaning Up");
}
function RollbackCompanySuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "rollback_company_form", responseText, true );
        startWidget("companies_widget", 0);
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function RollbackCompanyErrorHandler(response) {
    failedFormPost( response['responseText'], "rollback_company_form" );
}
function RollbackCompanyClickHandler( click_obj, e) {

    // Ignore if identifier_type is set and it's not company
    var button = $(click_obj);
    var identifier_type = $(button).data('identifier_type');
    if ( getStringValue(identifier_type) !== 'company' ) return;

	e.preventDefault();
    ActionIconClickHandler( click_obj, "rollback_company_form", "rollback_company_widget");
}
