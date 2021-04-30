$(function(){

    // Edit Plan ( Click Handler )
    $(document).on('click', '.carrier-link', function(e) {
        EditCarrierClickHandler( this, e );
    });


    // Click Handler ( Carrier Form Cancel Button Handler )
    $(document).on('click', '#carrier_form #cancel_btn', function(e) {
        hideForm( $("#carrier_form"), true, true );
    });

    // Click Handler ( Add User Submit Button Handler )
    $(document).on('click', '#carrier_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: CarrierBeforeSubmit,
            success: CarrierSuccessHandler,
            error: CarrierErrorHandler,
            data: {ajax: '1'}
        };
        $('#carrier_form').ajaxForm(options);


        // Define the form validation.
        var plantype_form_validator = undefined;
        if( $("#carrier_form").length ) {
            plantype_form_validator = $("#carrier_form").validate({
                rules: {
                    carrier_code: {
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
        if ( $("#carrier_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });



});
function EditCarrierClickHandler(click_obj, e) {
    e.preventDefault();

    var div = $(click_obj).parent().parent();
    var carrier = getStringValue( $(div).data('carrier') );

    // Pick which widget we are going to display base on our data.
    var widget_name = "carrier_widget";
    var form_name = "carrier_form";
    var widget = $("#" + widget_name);

    // Push the href into the tempalate since this widget will have dynamic data.
    var template = getStringValue( $(widget).data("href-template") );
    if ( template == "" ) {
        $(widget).attr("data-href-template", $(widget).data("href") )
    }

    // Pull the URL off the anchor that was clicked and set it on the widget.
    var url = $(widget  ).data("href-template");
    url = replaceFor(url, "CARRIER", encodeURIComponent(ReplaceDisallowedCharacters(carrier)) );

    /*
        var debug = {};
        debug.widget_name = widget_name;
        debug.form_name = form_name;
        debug.carrier = carrier;
        debug.url = url;
        debug.template = template;
        pprint_r(debug);
    */


    $(widget).attr("data-href", url);
    refreshWidget( widget_name, "showForm", form_name );

}
function CarrierBeforeSubmit()
{
    beforeFormPost("carrier_form");
}
function CarrierSuccessHandler(responseText, statusText, xhr, form)
{
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "carrier_form", responseText, true );
        location.reload();

    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function CarrierErrorHandler(response)
{
    failedFormPost( response['responseText'], "carrier_form" );
}