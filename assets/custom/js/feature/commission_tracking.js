$(function(){

    $(document).on('change', '#commission_type', function(e) {
        ShowWarning();
    });

    $(document).on('click', '#oed_variant', function(e) {
        ShowWarning();
    });
    $(document).on('click', '.uiform-checkbox-inline-desc', function(e) {
        ShowWarning();
    });

    $(document).on('click', '#cancel_commission_tracking_form', function(e) {
        var form = $("#commission_tracking_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Add Company Parent Submit Button Handler )
    $(document).on('click', '#commission_tracking_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: CommissionTrackingFormBeforeSubmit,
            success: CommissionTrackingFormSuccessHandler,
            error: CommissionTrackingFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#commission_tracking_form').ajaxForm(options);

        // Define the form validation.
        var commission_tracking_form_validator = undefined;

        if( $("#commission_tracking_form").length ) {
            commission_tracking_form_validator = $("#commission_tracking_form").validate({
                rules: {
                    hostname: {
                        required: true
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
        if ( $("#commission_tracking_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });


});

function CommissionTrackingFormBeforeSubmit() {
    beforeFormPost("commission_tracking_form");
}
function CommissionTrackingFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "commission_tracking_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }

}
function CommissionTrackingFormErrorHandler(response) {
    failedFormPost( response['responseText'], "commission_tracking_form" );
}
function ShowWarning()
{
    var form = $("#commission_tracking_form");

    // Collect values on the form.
    var commission_type = $("#commission_type").val();
    var oldest_effective_date = $("#oldest_effective_date").is(":checked");
    var orig_commission_type = $("#orig_commission_type").val();

    // Convert the orig_oed_variant value into TRUE/FALSE.
    var orig_oldest_effective_date = false;
    if ( getStringValue($("#orig_oldest_effective_date").val()) == "1" ) orig_oldest_effective_date = true;

    // Convert the has_data value to TRUE/FALSE.
    var has_data = false;
    if ( getStringValue($("#has_data").val()) == "1" ) has_data = true;

    // If the customer has data and we change something on this form
    // show a warning to the user.
    var show = false;
    if ( has_data && commission_type != orig_commission_type )
    {
        show = true;
    }
    if ( has_data &&  oldest_effective_date != orig_oldest_effective_date )
    {
        show = true;
    }

    if ( show )
    {
        $("#warning").removeClass("hidden");
    }
    else
    {
        $("#warning").addClass("hidden");
    }

}
