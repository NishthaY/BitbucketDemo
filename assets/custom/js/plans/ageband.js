$(function(){


    // AGE BAND
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

    $(document).on('click', '.default-bands', function(e) {
        LoadDefaultAgeBands( this, e );
    });

    // Age Band Checkbox ( Click Handler )
    $(document).on('click', '#ageband_form .checkbox_outer', function(e){
        if ( $(this).find("#ignore_checkbox").lenth != 0 ) {
            IgnoreAgeBandCheckboxClickHandler($("#ignore_checkbox"), e);
        }
    });

    $(document).on('click', "#ageband_form #clear_form_link", function(e){
        ClearAgeBandFormClickHandler(this, e);
    });
    // Delete Age Band ( Click Handler )
    $(document).on('click', "#ageband_form .row-delete-icon", function(e){
        DeleteAgeBandClickHandler(this, e);
    });

    // Add Age Band ( Click Handler )
    $(document).on('click', "#ageband_form #add_btn", function(e){
        AddAgeBandClickHandler(this, e);
    });

    // Edit Age Band ( Click Handler )
    $(document).on('click', '.ageband-link', function(e) {
        EditAgeBandClickHandler( this, e );
    });

    // Cancel Age Band ( Click Handler )
    $(document).on('click', '#ageband_form #cancel_btn', function(e) {
        var form = $("#ageband_form");
        hideForm(form, true, true);
    });

    // Save Age Band ( Click Handler )
    $(document).on('click', '#ageband_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: AgeBandBeforeSubmit,
            success: AgeBandSuccessHandler,
            error: AgeBandErrorHandler,
            data: {ajax: '1'}
        };
        $('#ageband_form').ajaxForm(options);


        // Define the form validation.
        var ageband_form_validator = undefined;
        if( $("#ageband_form").length ) {
            ageband_form_validator = $("#ageband_form").validate({
                rules: {
                    validation_trigger: {
                        ageband_ignored: true,
                        ageband_row_required: true,
                        ageband_row_range: true,
                        ageband_row_range_order: true,
                        ageband_overlap: true,
                    },
                },
                messages: { },
                ignore: [],
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
            jQuery.validator.addMethod('ageband_ignored', IgnoredAgeBandValidator, 'You must identify at least one age band unless you choose to ignore them.');
            jQuery.validator.addMethod('ageband_row_required', AgeBandRowRequiredValidator, "Age band fields required.  Information must be either the text Birth, Death or a number.");
            jQuery.validator.addMethod('ageband_row_range', AgeBandRowValidAgeRange, 'Numeric ages must not be less than the age of 0 or greater than 999.');
            jQuery.validator.addMethod('ageband_row_range_order', AgeBandRowValidAgeRangeOrder, 'The ending age must not be less than the starting age in a given band.');
            jQuery.validator.addMethod('ageband_overlap', AgeBandRowOverLap, 'Supplied age bands may not overlap.');
        }

        // Validate the form.
        if ( $("#ageband_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });



});
function LoadDefaultAgeBands( click_obj, e)
{
    e.preventDefault();

    var default_group = $(click_obj).attr("id");
    var url = $(click_obj).attr("href");
    var params = {};
    params.ajax = 1;
    params.url = url;
    params.default_group = default_group;

    // Find the carrier and carrier id associated with this band and pass that along
    // on the post.
    var form = $(click_obj).closest('form');
    var carrier = $(form).find('input[name="carrier"]').val();
    var carrier_id = $(form).find('input[name="carrier_id"]').val();
    params.carrier = carrier;
    params.carrier_id = carrier_id;


    // Make an ajax call to remove the record in question.
    $.post( url, securePostVariables(params) ).done(function( responseText ) {
        if ( ! ValidateAjaxResponse(responseText ) ) { return; }
        try{
            var result = JSON.parse(responseText);
            if ( result['type'] == "success" ) {

                // Clear out all but the "input" row.
                ClearAgeBandFormClickHandler();

                // If we are ignored, turn that off.
                if ( $("#ignore_checkbox").is(":checked") )
                {
                    $(ignore_checkbox).prop("checked", false);
                    IgnoreAgeBandCheckboxClickHandler( );
                }

                // Remove the "input" row.

                $("div.age-band-row").each(function(){
                    if ( ! $(this).hasClass("hidden")) {
                        $(this).remove();
                    }
                });


                // Set the HTML.
                var html = result['html'];
                $("#ageband_sample").after(html);


                // If we got a notification back, set that now.
                var notification = getStringValue(result['notification']);

                if ( notification !== '' )
                {
                    var container = $("div.ageband-form-container");
                    var best_guess = $(container).find('div.best-guess');

                    $(best_guess).html('<small>' + notification + '</small>');
                    $(best_guess).removeClass("hidden");
                    $(best_guess).show();
                }


            }
            return;

        }catch(err){
            AJAXPanic(responseText);
            return;
        }

    });


}
function InitHiddenItem(item) {
    if ( $(item).hasClass("hidden") )
    {
        $(item).hide();
        $(item).removeClass("hidden");
    }
}
function AgeCalculationTypeChangeHandler() {

    InitHiddenItem( $("#wash_description") );
    InitHiddenItem( $("#anniversary_description") );
    InitHiddenItem( $("#issued_description") );
    InitHiddenItem( $(".agetype-form-container") );

    var input = $("#age_calculation_type");
    var value = $(input).val();
    if ( value == "washed" )
    {
        $("#wash_description").show();
        $("#anniversary_description").hide();
        $("#issued_description").hide();
        $( $(".agetype-form-container") ).hide();
    }
    if ( value == "anniversary" )
    {
        $("#anniversary_description").show();
        $("#wash_description").hide();
        $("#issued_description").hide();
        $( $(".agetype-form-container") ).show();
    }
    if ( value == "issued" )
    {
        $("#wash_description").hide();
        $("#anniversary_description").hide();
        $("#issued_description").show();
        $( $(".agetype-form-container") ).hide();
    }

    RemoveAgeRuleBestGuessVerbiage();

}
function AgeRuleMonthChangeHandler() {
    RemoveAgeRuleBestGuessVerbiage();
}
function AgeRuleDayChangeHandler() {
    RemoveAgeRuleBestGuessVerbiage();
}
function ClearAgeBandFormClickHandler( click_obj, e ) {
    $("#age_band_container").find(".row-delete-icon").each(function(){
        var delete_button = $(this);
        var row = $(delete_button).parent();
        var first_name = $(row).find("input:first").attr("name");
        $(row).find("input").each(function(){
            $(this).val("");
        });
        var delete_flg = true;
        if ( first_name == "bandX-start" ) { delete_flg = false; }
        if ( first_name == "band1-start" ) { delete_flg = false; }
        if ( delete_flg ) {
            DeleteAgeBandClickHandler( delete_button, e );
        }

    });
    RemoveBestGuessVerbiage();
}
function RemoveBestGuessVerbiage() {
    $("#best_guess").hide();
}
function RemoveAgeRuleBestGuessVerbiage() {
    $("#agerule_best_guess").hide();
}
function DeleteAgeBandClickHandler( click_obj, e) {
    var icon = $(click_obj);
    var add_button = $(container).find("#add_btn");
    var ignore_checkbox = $("#ignore_checkbox");
    var container = $(icon).closest("#age_band_container");


    RemoveBestGuessVerbiage();

    // Remove this row.
    $(click_obj).parent().remove();

    var count = $(container).find(".age-band-row").length;
    if ( count == 1 ) {
        AddAgeBandClickHandler( add_button, e );
        $(ignore_checkbox).prop("checked", true);
        IgnoreAgeBandCheckboxClickHandler( );
        $(".row-delete-icon").hide();
    }


}
function AddAgeBandClickHandler( click_obj, e ) {
    var container = $("#age_band_container");
    var sample = $("#ageband_sample");
    var ignored_checkbox = $("#ignore_checkbox");

    RemoveBestGuessVerbiage();

    // Clone our sample row.
    var new_row = $(sample).clone();

    // Set the name value on the text fields for the new row.
    var row_count = $(container).attr("data-count");
    row_count = parseInt(row_count) + 1;
    $(new_row).find("input:first").attr("name", "band"+getStringValue( row_count )+"-start");
    $(new_row).find("input:last").attr("name", "band"+getStringValue( row_count )+"-end");

    // Add the new row.
    $(container).find("p:first").before(new_row);

    // Update the row counter.
    $(container).attr("data-count", getStringValue(row_count));

    // Show the new row.
    $(new_row).removeClass("hidden");

    // Make sure the remove buttons are visible, if not ignored.
    if ( ! $(ignored_checkbox).is(":checked") ) {
        $(".row-delete-icon").show();
    }

    // Focus on the first field in the new row.
    $(new_row).find("input:first").focus();


}
function AgeBandRowOverLap(value) {
    var retval = true;
    var ranges = [];
    $("div.age-band-row").each(function(){
        var first = $(this).find(".age-band-first").val();
        var second = $(this).find(".age-band-second").val();

        if ( getStringValue(first).toUpperCase() == "BIRTH" ) { first = 0; }
        if ( getStringValue(second).toUpperCase() == "DEATH" ) { second = 999; }
        if ( getStringValue(first).toUpperCase() == "B" ) { first = 0; }
        if ( getStringValue(second).toUpperCase() == "D" ) { second = 999; }
        var range = getStringValue(first) + "," + getStringValue(second);
        if ( range != "," ) { ranges.push(range); }
    });
    var length = ranges.length;
    for(var i=0; i< ranges.length; i++ )
    {
        var range = ranges[i];
        var x1 = parseInt(fLeft(range, ","));
        var x2 = parseInt(fRight(range, ","));

        for( var z=0; z<ranges.length; z++ ) {
            if ( z == i ) { continue; }

            var range_y = ranges[z];
            var y1 = parseInt(fLeft(range_y, ","));
            var y2 = parseInt(fRight(range_y, ","));

            if ( x1 <= y2 && y1 <= x2 ) {
                retval = false;
            }else{
                // what/
            }
        }
    }
    return retval;
}
function AgeBandRowValidAgeRangeOrder( value ){

    // AgeBandRowValidAgeRangeOrder
    //
    // This validator will ensure that an ageband that is filled
    // in does not have a ending range smaller than the starting range.
    // ---------------------------------------------------------
    var retval = true;
    $("div.age-band-row").each(function(){

        var first = $(this).find(".age-band-first").val();
        var second = $(this).find(".age-band-second").val();

        if ( getStringValue(first).toUpperCase() == "BIRTH" ) { first = 0; }
        if ( getStringValue(first).toUpperCase() == "DEATH" ) { first = 1000; }
        if ( getStringValue(first).toUpperCase() == "B" ) { first = 0; }
        if ( getStringValue(first).toUpperCase() == "D" ) { first = 1000; }
        if ( getStringValue(second).toUpperCase() == "BIRTH" ) { second = 0; }
        if ( getStringValue(second).toUpperCase() == "DEATH" ) { second = 1000; }
        if ( getStringValue(second).toUpperCase() == "B" ) { second = 0; }
        if ( getStringValue(second).toUpperCase() == "D" ) { second = 1000; }

        if ( retval == true && $.isNumeric(first) && $.isNumeric(second) )
        {
            if ( parseInt(second) < parseInt(first) ) {
                console.log("second["+second+"] < first["+first+"]");
                retval = false;
            }
        }

    });
    return retval;
}
function AgeBandRowValidAgeRange(value) {
    var retval = true;
    $("div.age-band-row").each(function(){

        var first = $(this).find(".age-band-first").val();
        var second = $(this).find(".age-band-second").val();

        if ( $.isNumeric(first) ){
            if ( first < 0 ) {
                retval = false;
            }
        }
        if ( $.isNumeric(second) ){
            if ( second < 0 ) {
                retval = false;
            }
        }
        if ( $.isNumeric(first) ){
            if ( first >= 1000 ) {
                retval = false;
            }
        }
        if ( $.isNumeric(second) ){
            if ( second >= 1000 ) {
                retval = false;
            }
        }
    });
    return retval;
}
function AgeBandRowRequiredValidator(value) {

    var retval = true;
    $("div.age-band-row").each(function(){

        var first_name = $(this).find(".age-band-first").attr("name");
        var first = $(this).find(".age-band-first").val();
        var second = $(this).find(".age-band-second").val();


        // Row may not be empty. ( Well the sample one can be. )
        if ( first == "" && second == "" )
        {
            if ( first_name != "bandX-start" ){
                retval = false;
            }
        }

        // Row must be fully popluated
        if ( first != "" || second != "" )
        {
            if ( first != "" && ( $.isNumeric(first) || getStringValue(first).toUpperCase() == "BIRTH" || getStringValue(first).toUpperCase() == "B" ) ) {
                // valid
            }else{
                retval = false;
            }

            if ( second != "" && ( $.isNumeric(second) || getStringValue(second).toUpperCase() == "DEATH" || getStringValue(second).toUpperCase() == "D" ) ) {
                // valid
            }else{
                retval = false;
            }

        }

    });

    return retval;
}
function IgnoredAgeBandValidator(value) {

    // Only do a required check if the ignore checkbox is NOT checked.
    var retval = false;
    var ignored_checkbox = $("#ignore_checkbox");
    if ( ! $(ignored_checkbox).is(":checked") ) {

        // Not ignored.  Must provide a value in at least one
        // of the text fields inside the age band container.
        var container = $("#age_band_container");
        $("#age_band_container :input").each(function(){
            if ( $(this).val() != "" ){
                retval = true;
            }
        });

    }
    return retval;

}
function AgeBandBeforeSubmit() {
    beforeFormPost("ageband_form");
}
function AgeBandSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "ageband_form", responseText, true );

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
function AgeBandErrorHandler(response) {
    failedFormPost( response['responseText'], "ageband_form" );
}
function IgnoreAgeBandCheckboxClickHandler( click_obj, e ) {

    // We have a click_obj.  Assume the user triggered this event.
    if ( jQuery.type(click_obj) == "object" ) {
        RemoveBestGuessVerbiage();
        RemoveAgeRuleBestGuessVerbiage();
    }

    // We have no click_obj.  Assume the page is loading.
    if ( jQuery.type(click_obj) != "object" ) {
        click_obj = $("#ignore_checkbox").get();
    }

    var checkbox = $(click_obj);
    var form = $(checkbox).closest("form");
    var container = $("#age_band_container");


    if ( $(checkbox).is(":checked") )
    {
        $("#age_band_container").find("input").each(function(){
            $(this).prop("disabled", true);
            $(this).attr("data-hold", $(this).val());
            $(this).val("");
        });
        $(".row-delete-icon").hide();
        $("#add_btn").prop("disabled", true);
        $(".default-bands").addClass("disabled");
        RemoveBestGuessVerbiage();
        RemoveAgeRuleBestGuessVerbiage();

        $("#age_type_container").find("input").each(function(){
            $(this).prop("disabled", true);
        });
        $("#age_type_container").find("button").each(function(){
            $(this).prop("disabled", true);
        });


    }else{
        $("#age_band_container").find("input").each(function(){
            $(this).prop("disabled", false);
            var hold = getStringValue($(this).data("hold"));
            if ( hold != "" ) {
                $(this).val(hold);
                $(this).attr("data-hold", "");
            }

        });
        $(".row-delete-icon").show();
        $("#add_btn").prop("disabled", false);
        $(".default-bands").removeClass("disabled");


        $("#age_type_container").find("input").each(function(){
            if ( $(this).attr("id") != "annivesary_year" ) {
                $(this).prop("disabled", false);
            }
        });
        $("#age_type_container").find("button").each(function(){
            $(this).prop("disabled", false);
        });
    }
}
function EditAgeBandClickHandler( click_obj, e ) {
    e.preventDefault();

    var div = $(click_obj).parent().parent();
    var carrier = getStringValue( $(div).prev().prev().prev().prev().data('carrier') );
    var plantype = getStringValue( $(div).prev().prev().prev().data('plan-type') );
    var plantypecode = getStringValue( $(div).prev().prev().prev().data('plan-type-code') );
    var plan = getStringValue( $(div).prev().prev().data('plan') );
    var tier = getStringValue( $(div).prev().data('coverage-tier') );

    // Pick which widget we are going to display base on our data.
    var widget_name = "ageband_widget";
    var form_name = "ageband_form";
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
function ShowAgeBandForm( form_name ) {
    InitMultiOptionButtons();   // Initialize the multi option buttons on this form.
    showForm(form_name, "IgnoreAgeBandCheckboxClickHandler");
}
