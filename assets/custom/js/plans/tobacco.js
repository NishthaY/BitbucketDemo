$(function(){


    // TOBACCO ATTRIBUTE
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

    // Edit Tobacco Settings ( Click Handler )
    $(document).on('click', '.tobacco-link', function(e) {
        EditTobaccoClickHandler( this, e );
    });

    // Cancel Tobacco Settings ( Click Handler )
    $(document).on('click', '#tobacco_form #cancel_btn', function(e) {
        var form = $("#tobacco_form");
        hideForm(form, true, true);
    });

    // Save Tobacco Settings ( Click Handler )
    $(document).on('click', '#tobacco_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: TobaccoBeforeSubmit,
            success: TobaccoSuccessHandler,
            error: TobaccoErrorHandler,
            data: {ajax: '1'}
        };
        $('#tobacco_form').ajaxForm(options);


        // Define the form validation.
        var tobacco_form_validator = undefined;
        if( $("#tobacco_form").length ) {
            tobacco_form_validator = $("#tobacco_form").validate({
                rules: { },
                messages: { },
                ignore: [],
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#tobacco_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });



});
function EditTobaccoClickHandler( click_obj, e ) {
    e.preventDefault();

    var div = $(click_obj).parent().parent();
    var carrier = getStringValue( $(div).prev().prev().prev().prev().data('carrier') );
    var plantype = getStringValue( $(div).prev().prev().prev().data('plan-type') );
    var plantypecode = getStringValue( $(div).prev().prev().prev().data('plan-type-code') );
    var plan = getStringValue( $(div).prev().prev().data('plan') );
    var tier = getStringValue( $(div).prev().data('coverage-tier') );

    // Pick which widget we are going to display base on our data.
    var widget_name = "tobacco_widget";
    var form_name = "tobacco_form";
    var widget = $("#" + widget_name);

    // Push the href into the tempalate since this widget will have dynamic data.
    var template = getStringValue( $(widget).data("href-template") );
    if ( template == "" ) {
        $(widget).attr("data-href-template", $(widget).data("href") );
        template = getStringValue( $(widget).data("href-template") );
    }

    // Pull the URL off the anchor that was clicked and set it on the widget.
    var url = $(widget  ).data("href-template");
    url = replaceFor(url, "CARRIER", encodeURIComponent(ReplaceDisallowedCharacters(carrier)) );
    url = replaceFor(url, "PLANTYPECODE", encodeURIComponent(ReplaceDisallowedCharacters(plantypecode)) );
    url = replaceFor(url, "PLANTYPE", encodeURIComponent(ReplaceDisallowedCharacters(plantype)) );
    url = replaceFor(url, "PLAN", encodeURIComponent(ReplaceDisallowedCharacters(plan)) );
    url = replaceFor(url, "TIER", encodeURIComponent(ReplaceDisallowedCharacters(tier)) );

    /*
    var debug = {};
    debug.widget_name = widget_name;
    debug.form_name = form_name;
    debug.carrier = carrier;
    debug.plantype = plantype;
    debug.plantypecode = plantypecode;
    debug.plan = plan;
    debug.tier = tier;
    debug.url = url;
    debug.template = template;
    pprint_r(debug);
    */


    $(widget).attr("data-href", url);
    refreshWidget( widget_name, "ShowAgeBandForm", form_name );
}
function ShowTobaccoForm( form_name ) {
    showForm(form_name, "IgnoreTobaccoCheckboxClickHandler");
}
function IgnoreTobaccoCheckboxClickHandler() {

}
function TobaccoBeforeSubmit() {
    beforeFormPost("tobacco_form");
}
function TobaccoSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "tobacco_form", responseText, true );

        var result = JSON.parse(responseText);
        if ( result['type'] == "success" ) {
            location.reload();
        }
        return;

    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function TobaccoErrorHandler(response) {
    failedFormPost( response['responseText'], "tobacco_form" );
}
