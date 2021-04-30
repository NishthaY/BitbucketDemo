$(function(){

    FormatPlansReviewTable();

    // MAIN PAGE
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

    // Indicator ( Click Handler )
    $(document).on('click', ".okay-indicator", function(e){
        IndicatorClickHandler( this, e);
    });

    // Indicator ( Click Handler )
    $(document).on('click', ".question-indicator", function(e){
        IndicatorClickHandler( this, e);
    });

    // Inline Indicator ( Click Handler )
    $(document).on('click', ".question-indicator-inline", function(e){
        IndicatorClickHandler( this, e);
    });

    // Continue Button ( Click Handler )
    $(document).on('click', '#continue_btn', function(e) {
        WizardButtonClickHandler(this, e);
    });

    // Rematch Button ( Click Handler )
    $(document).on('click', '#rematch_btn', function(e) {
        WizardButtonClickHandler(this, e);
    });



});
function IndicatorClickHandler(click_obj, e) {
    var div = $(click_obj).parent();
    var link = $(div).find("a:first");
    if ( $(link).hasClass("plantype-link") ) {
        EditPlanTypeClickHandler( link, e );
    }
    if ( $(link).hasClass("ageband-link") ) {
        EditAgeBandClickHandler( link, e );
    }
}
function FormatPlansReviewTable() {

    var table = $("#upload_review_table");

    // Draw Column #1 ( Carrier )
    var carrier = "";
    $(table).find(".row").each(function() {
        var row = $(this);
        var field1_div = $(row).find("div:first");
        var field1 = $(field1_div).data("carrier");
        if ( carrier == field1 ) {
            $(field1_div).html("&nbsp;");
        }else{
            if ( $(row).hasClass("body") ){
                $(row).addClass("line");
                $(row).find("div:nth-child(3)").css("border-top", "none");
                $(row).find("div:nth-child(4)").css("border-top", "none");
                $(row).find("div:nth-child(5)").css("border-top", "none");
            }
        }
        carrier = field1;
    });


    // Draw Column #2 ( PlanType )
    var carrier = "";
    var plantype = "";
    $(table).find(".row").each(function() {
        var row = $(this);
        var field1_div = $(row).find("div:first");
        var field2_div = $(row).find("div:nth-child(2)");
        var field1 = $( field1_div ).data("carrier");
        var field2 = $( field2_div ).data("plan-type");
        if ( carrier == field1 && plantype == field2 ) {
            $( field2_div ).html("&nbsp;");
        }else{
            if ( $(row).hasClass("body") ){
                $(field2_div).addClass("dimished-line");
            }
        }
        carrier = field1;
        plantype = field2;
    });


    // Draw Column #3 ( PlanType )
    var carrier = "";
    var plantype = "";
    var plan = "";
    $(table).find(".row").each(function() {
        var row = $(this);
        var field1_div = $(row).find("div:first");
        var field2_div = $(row).find("div:nth-child(2)");
        var field3_div = $(row).find("div:nth-child(3)");
        var field1 = $( field1_div ).data("carrier");
        var field2 = $( field2_div ).data("plan-type");
        var field3 = $( field3_div ).data("plan");
        if ( carrier == field1 && plantype == field2 && plan == field3 ) {
            $(field3_div).html("&nbsp;");
        }else{
            if ( $(row).hasClass("body") && ! $(row).hasClass("line") ){
                $(field3_div).addClass("dimished-line");
            }
        }
        carrier = field1;
        plantype = field2;
        plan = field3;
    });

    $(table).show();

}
function IgnoredValidator(value) {

    // Only do a required check if the ignore checkbox is NOT checked.
    var ignored_checkbox = $("#ignore_checkbox");
    if ( ! $(ignored_checkbox).is(":checked") ) {

        // Not ignored.  Must provide a value.
        if ( getStringValue(value) == "" ) {
            return false;
        }

    }
    return true;

}
