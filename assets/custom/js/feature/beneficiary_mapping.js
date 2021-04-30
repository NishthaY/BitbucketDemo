$(function(){

    $(document).on('click', '#cancel_beneficiary_mapping_form', function(e) {
        var form = $("#beneficiary_mapping_form");
        hideForm(form, true, true);
    });

    $(document).on('click', '#add_token_button', function(e) {
        BeneficiaryMappingAddTokenClickHandler(this,e);
    });

    // Click Handler ( Add Company Parent Submit Button Handler )
    $(document).on('click', '#beneficiary_mapping_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: BeneficiaryMappingFormBeforeSubmit,
            success: BeneficiaryMappingFormSuccessHandler,
            error: BeneficiaryMappingFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#beneficiary_mapping_form').ajaxForm(options);


        // Define the form validation.
        var beneficiary_mapping_form_validator = undefined;

        if( $("#beneficiary_mapping_form").length ) {
            beneficiary_mapping_form_validator = $("#beneficiary_mapping_form").validate({
                rules: {
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#beneficiary_mapping_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });
});
function BeneficiaryMappingFormBeforeSubmit() {
    beforeFormPost("beneficiary_mapping_form");
}
function BeneficiaryMappingFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "beneficiary_mapping_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function BeneficiaryMappingFormErrorHandler(response) {
    failedFormPost( response['responseText'], "beneficiary_mapping_form" );
}

function BeneficiaryMappingAddTokenClickHandler(click_obj, e)
{
    var button = $(click_obj);
    var input_group = $(button).closest('div.input-group');
    var input = $(input_group).find('input:first');

    var text = $(input).val().trim();
    var display = text.toUpperCase() + " ( "+text+" )";

    // If the user put in a blank space, bail.
    if ( getStringValue(text) == '' ) {
        text = "";
        display = "No data.";
    }

    var container = $("#tokens");
    var clone = $(container).find("div.checkbox-wrapper:nth-child(1)");

    // Get the HTML from the clone object, set the value and display, make it visible.
    var html = $('<div>').append($(clone).clone()).html();
    html = replaceFor(html, 'VALUE', text);
    html = replaceFor(html, 'DISPLAY', display);
    html = replaceFor(html, "hidden", "");


    // Decide if the item that was just selected exists in the checkbox list
    // or not.
    var exists = false;
    $(container).find('input').each(function(){
        var input = $(this);
        var item = $(input).val();
        if ( item.toLowerCase() == text.toLowerCase() )
        {
            exists = true;
            $(input).prop('checked', true);
        }
    });


    if ( ! exists )
    {
        // If the selected item is not in the checkbox list, then
        // add it now.
        $(container).append(html);
    }

    // Reset the input back to empty.
    $(input).val("");

    // Now check the item you just added.
    $(container).find('input').each(function(){
        var input = $(this);
        var item = $(input).val();
        if ( item == text )
        {
            $(input).prop('checked', true);
        }
    });


}