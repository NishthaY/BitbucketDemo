$(function(){

    $(document).on('click', '#targetable_feature_form input.preference-item', function(e) {
        TargetableFeatureRadioButtonClickHandler(this,e);
    });

    $(document).on('click', '#cancel_targetable_feature_form', function(e) {
        var form = $("#targetable_feature_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Add Company Parent Submit Button Handler )
    $(document).on('click', '#targetable_feature_form button[type="submit"]', function(e) {



        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: TargetableFeatureFormBeforeSubmit,
            success: TargetableFeatureFormSuccessHandler,
            error: TargetableFeatureFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#targetable_feature_form').ajaxForm(options);


        // Define the form validation.
        var targetable_feature_form_validator = undefined;

        if( $("#targetable_feature_form").length ) {
            targetable_feature_form_validator = $("#targetable_feature_form").validate({
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
        if ( $("#targetable_feature_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });



});
function InitTargetableFeature()
{
    var form = $("#targetable_feature_form");
    var radio_button = $(form).find('input[type="radio"]:first');
    $(radio_button).trigger('click');
}
function TargetableFeatureRadioButtonClickHandler(click_obj, e )
{

    var input = $(click_obj);
    var form = $(input).closest('form');
    var target_type = $(input).data('targettype');
    var spacer = $("#spacer");

    // Hide all of the targetable selection items
    $(form).find('div.targetable-selection').each(function(){
        var item = this;
        $(item).addClass('hidden');
    });

    // Show just the one targetable selection associated with the clicked object.
    $("#"+target_type).removeClass("hidden");
    $(spacer).addClass("hidden");

    // Set the hidden target type value to the selected target type.
    $("#target_type").val(target_type);



}

function TargetableFeatureFormBeforeSubmit() {
    beforeFormPost("targetable_feature_form");
}
function TargetableFeatureFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "targetable_feature_form", responseText, true );
    }catch(err){
        AJAXPanic(responseText);
        return;
    }

}
function TargetableFeatureFormErrorHandler(response) {
    failedFormPost( response['responseText'], "targetable_feature_form" );
}

