$(function(){

    // Edit Plan ( Click Handler )
    $(document).on('click', '.plan-link', function(e) {
        EditPlanClickHandler( this, e );
    });

    // ASO Fee ( Focus Out Handler )
    $(document).on('focusout', '#aso_fee', function(e) {
        ASOFeeChangeHandler( this );
    });

    // ASO Carrier Fee ( Change Handler )
    $(document).on('change', '#aso_carrier', function(e) {
        ASOFeeChangeHandler( this );
    });

    // Stop Loss Fee ( Focus Out Handler )
    $(document).on('focusout', '#stoploss_fee', function(e) {
        StopLossFeeChangeHandler( this );
    });

    // Stop Loss Carrier Fee ( Change Handler )
    $(document).on('change', '#stoploss_carrier', function(e) {
        StopLossFeeChangeHandler( this );
    });

    // Click Handler ( Plan Form Cancel Button Handler )
    $(document).on('click', '#plan_form #cancel_btn', function(e) {
        hideForm( $("#plan_form"), true, true );
    });

    // Click Handler ( Add User Submit Button Handler )
    $(document).on('click', '#plan_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: PlanFormBeforeSubmit,
            success: PlanFormSuccessHandler,
            error: PlanFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#plan_form').ajaxForm(options);

        // Define the form validation.
        var plan_form_validator = undefined;
        if( $("#plan_form").length ) {
            plan_form_validator = $("#plan_form").validate({
                rules: {
                    aso_fee: {
                        fee:true
                    },
                    aso_carrier: {
                        carrier_fee: true
                    },
                    aso_carrier_alt: {
                        alt_carrier_fee: true
                    },
                    stoploss_fee: {
                        fee:true
                    },
                    stoploss_carrier: {
                        carrier_fee: true
                    },
                    stoploss_carrier_alt: {
                        alt_carrier_fee: true
                    }
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
            jQuery.validator.addMethod('fee', feeValidator, 'Please enter a valid money value larger than zero.');
            jQuery.validator.addMethod('carrier_fee', carrierFeeValidator, 'Please select a value.');
            jQuery.validator.addMethod('alt_carrier_fee', altCarrierFeeValidator, 'Please enter a value.');
        }

        // Validate the form.
        if ( $("#plan_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });


});
function feeValidator( value, element ) {

    // Blank is Okay!
    if ( value == "" ) return true;

    // Treat their input as a money value.
    var value = Math.abs(Number(value) || 0 ).toFixed(2);

    if ( parseFloat(value) <= 0 ) return false;
    var isValidMoney = /^[+-]?[0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]{2})?$/.test(getStringValue(value));
    return this.optional(element) || isValidMoney;
}
function carrierFeeValidator(value, element) {

    var form_row = $(element).closest(".comment-box-row");
    var previous_form_row = $(form_row).prev();
    var input = $(previous_form_row).find("input:first");
    var fee = getStringValue($(input).val());
    value = getStringValue(value);

    if ( fee == "" ) return true;
    if ( value != "" ) return true;
    return false;
}
function altCarrierFeeValidator(value, element) {
    var form_row = $(element).closest(".form-group");
    var previous_form_row = $(form_row).prev();
    var input = $(previous_form_row).find('input[type="hidden"]:first');
    if ( $(input).val() != "0") return true;

    // Remove leading and trailing spaces
    value = getStringValue(value).trim();

    if ( value == "" ) return false;
    return true;
}
function EditPlanClickHandler(click_obj, e) {
    e.preventDefault();

    var div = $(click_obj).parent().parent();
    var carrier = getStringValue( $(div).prev().prev().data('carrier') );
    var plantype = getStringValue( $(div).prev().data('plan-type') );
    var plantypecode = getStringValue( $(div).prev().data('plan-type-code') );
    var plan = getStringValue( $(div).data('plan') );

    // Pick which widget we are going to display base on our data.
    var widget_name = "plan_widget";
    var form_name = "plan_form";
    var widget = $("#" + widget_name);

    // Push the href into the tempalate since this widget will have dynamic data.
    var template = getStringValue( $(widget).data("href-template") );
    if ( template == "" ) {
        $(widget).attr("data-href-template", $(widget).data("href") )
    }

    // Pull the URL off the anchor that was clicked and set it on the widget.
    var url = $(widget  ).data("href-template");
    url = replaceFor(url, "CARRIER", encodeURIComponent(ReplaceDisallowedCharacters(carrier)) );
    url = replaceFor(url, "PLANTYPE", encodeURIComponent(ReplaceDisallowedCharacters(plantype)) );
    url = replaceFor(url, "PLAN", encodeURIComponent(ReplaceDisallowedCharacters(plan)) );

    /*
        var debug = {};
        debug.widget_name = widget_name;
        debug.form_name = form_name;
        debug.carrier = carrier;
        debug.plantype = plantype;
        debug.plantypecode = plantypecode;
        debug.plan = plan;
        debug.url = url;
        debug.template = template;
        pprint_r(debug);
    */


    $(widget).attr("data-href", url);
    refreshWidget( widget_name, "UpdatePlanUI", form_name );

}
function StopLossFeeChangeHandler( click_obj, e ) {
    FeeChangeHandler("stoploss");
}
function ASOFeeChangeHandler( click_obj, e) {
    FeeChangeHandler("aso");
}
function FeeChangeHandler( tag ) {

    var fee = $("#"+tag+"_fee");
    var carrier_row = $("#"+tag+"_carrier").closest(".form-group");
    var carrier= $("#"+tag+"_carrier");
    var carrier_btn = $("#"+tag+"_carrier_button");
    var carrier_alt = $("#"+tag+"_carrier_alt");
    var carrier_alt_row = $(carrier_alt).closest(".form-group");

    $(carrier_alt_row).addClass("hidden");
    if ( $(fee).val() == "" || parseFloat($(fee).val()) == 0  )
    {
        $(fee).val("");
        $(carrier_row).addClass("hidden");
        $(carrier_alt_row).addClass("hidden");
    }
    else
    {
        $(carrier_row).removeClass("hidden");
        if ( $(carrier).val() == "0" )
        {
            $(carrier_alt_row).removeClass("hidden");
        }
        else
        {
            $(carrier_alt_row).addClass("hidden");
        }
    }

}
function UpdatePlanUI() {
    showForm("plan_form");

    var aso_fee = $("#aso_fee");
    ASOFeeChangeHandler(aso_fee);
    FeeChangeHandler("stoploss");
}
function PlanFormBeforeSubmit() {
    beforeFormPost("plan_form");
}
function PlanFormSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "plan_form", responseText, true );
        var result = JSON.parse(responseText);
        if ( getStringValue(result['type']) == "success" ) {
            location.reload();
        }
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function PlanFormErrorHandler(response) {
    failedFormPost( response['responseText'], "plan_form" );
}
