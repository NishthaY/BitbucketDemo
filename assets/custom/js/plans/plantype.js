/*global $*/
/*global hideForm*/
/*global form_highlight*/
/*global form_unhighlight*/
/*global form_error*/
/*global IgnoredValidator*/
/*global jQuery*/
/*global beforeFormPost*/
/*global ValidateAjaxResponse*/
/*global successfulFormPost*/
/*global location*/
/*global AJAXPanic*/
/*global failedFormPost*/
/*global getStringValue*/
/*global replaceFor*/
/*global refreshWidget*/
/*global showForm*/

$(function(){

    // PLAN TYPE
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
    // PlanType Checkbox ( Click Handler )
    $(document).on('click', '#plantype_form .checkbox_outer', function(e){
        if ( $(this).find("#ignore_checkbox").lenth != 0 ) {
            IgnoreCheckboxClickHandler($("#ignore_checkbox"), e);
        }
    });

    // Edit Plan Type ( Click Handler )
    $(document).on('click', '.plantype-link', function(e) {
        EditPlanTypeClickHandler( this, e );
    });

    // Click Handler ( Edit User Cancel Button Handler )
    $(document).on('click', '#plantype_form #cancel_btn', function(e) {
        var form = $("#plantype_form");
        hideForm(form, true, true);
    });

    // Change Handler ( Retro Rule Selection )
    $(document).on( 'change', "#plantype_form input[name='retro_rules']", function(e) {
        $("#best_guess").addClass("hidden");
    });

    // Change Handler ( Wash Rule Selection )
    $(document).on( 'change', "#plantype_form input[name='wash_rules']", function(e) {
        $("#best_guess").addClass("hidden");
    });

    // Click Handler ( Add User Submit Button Handler )
    $(document).on('click', '#plantype_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: PlanTypeBeforeSubmit,
            success: PlanTypeSuccessHandler,
            error: PlanTypeErrorHandler,
            data: {ajax: '1'}
        };
        $('#plantype_form').ajaxForm(options);


        // Define the form validation.
        var plantype_form_validator = undefined;
        if( $("#plantype_form").length ) {
            plantype_form_validator = $("#plantype_form").validate({
                rules: {
                    plantype_mapping: {
                        plantype_code: true
                    },
                    retro_rules: {
                        retro_rule: true
                    },
                    wash_rules: {
                        wash_rule: true
                    }
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
            jQuery.validator.addMethod('plantype_code', IgnoredValidator, 'You must identify the plan type unless you choose to ignore this plan type.');
            jQuery.validator.addMethod('wash_rule', IgnoredValidator, 'You must select a wash rule unless you choose to ignore this plan type.');
            jQuery.validator.addMethod('retro_rule', IgnoredValidator, 'You must select a retro rule unless you choose to ignore this plan type.');
        }

        // Validate the form.
        if ( $("#plantype_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });



});
function PlanAnniversaryMonthChangeHandler() {

    // PlanAnniversaryMonthChangeHandler
    //
    // When the plan anniversary month dropdown changes, take a look at
    // the selected value.  If the user selected "No Anniversary", then
    // remove the text from the display portion of the input so it reflects
    // they turned off the Plan Anniversary feature.
    // ---------------------------------------------------------------------
    var submit_input = $("#plantype_form input[name='plan_anniversary_month']");
    var display_input = $("#plantype_form input[name='plan_anniversary_month_disabled']");
    if ( $(submit_input).val() == "" )
    {
        $(display_input).val(" ");
    }

}
function PlanTypeBeforeSubmit() {
    beforeFormPost("plantype_form");
}
function PlanTypeSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "plantype_form", responseText, true );
        location.reload();

    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function PlanTypeErrorHandler(response) {
    failedFormPost( response['responseText'], "plantype_form" );
}
function IgnoreCheckboxClickHandler(click_obj, e) {

    if ( jQuery.type(click_obj) != "object" ) { click_obj = $("#ignore_checkbox").get(); }

    var checkbox = $(click_obj);
    var form = $(checkbox).closest("form");


    // If the ignore checkbox is checked, disable all of the form
    // fields in the UI.
    if ( $(checkbox).is(":checked") ) {
        $("#plantype_mapping_button").closest(".comment-box-row").hide();
        $("#retro_rules").closest(".comment-box-row").hide();
        $("#wash_rules").closest(".comment-box-row").hide();
        $("#retro_rules_help").closest(".comment-box-row").hide();
        $("#plan_anniversary_month").closest(".comment-box-row").hide();
        $("#best_guess").closest(".comment-box-row").hide();
        $(".modal-ignored-text").hide();
    }
    if ( ! $(checkbox).is(":checked") ) {
        $("#plantype_mapping_button").closest(".comment-box-row").show();
        $("#plantype_mapping_button").closest(".comment-box-row").removeClass("hidden");
        $("#retro_rules").closest(".comment-box-row").show();
        $("#retro_rules").closest(".comment-box-row").removeClass("hidden");
        $("#wash_rules").closest(".comment-box-row").show();
        $("#wash_rules").closest(".comment-box-row").removeClass("hidden");
        $("#retro_rules_help").closest(".comment-box-row").show();
        $("#retro_rules_help").closest(".comment-box-row").removeClass("hidden");
        $("#plan_anniversary_month").closest(".comment-box-row").show();
        $("#plan_anniversary_month").closest(".comment-box-row").removeClass("hidden");

        $("#best_guess").closest(".comment-box-row").show();
        $("#best_guess").closest(".comment-box-row").removeClass("hidden");

        $(".modal-ignored-text").show();
    }

}
function EditPlanTypeClickHandler(click_obj, e) {

    e.preventDefault();

    var div = $(click_obj).parent().parent();
    var carrier = getStringValue( $(div).prev().data('carrier') );
    var plantype = getStringValue( $(div).data('plan-type') );
    var plantypecode = getStringValue( $(div).data('plan-type-code') );

    // Pick which widget we are going to display base on our data.
    var widget_name = "plantype_widget";
    var form_name = "plantype_form";
    var code = plantype;
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

/*
    var debug = {};
    debug.code = code;
    debug.widget_name = widget_name;
    debug.form_name = form_name;
    debug.carrier = carrier;
    debug.plantype = plantype;
    debug.plantypecode = plantypecode;
    debug.url = url;
    debug.template = template;
    pprint_r(debug);
*/

    $(widget).attr("data-href", url);
    refreshWidget( widget_name, "ShowPlanTypeForm", form_name );


}
function ShowPlanTypeForm( form_name ) {
    showForm(form_name, "IgnoreCheckboxClickHandler");
}
