$(function(){

    // Click Handler ( Submit Button Handler )
    $(document).on('click', '#lives_page_form #lives_complete_button', function(e) {
        var options = {
            beforeSubmit: LivesPageBeforeSubmit,
            success: LivesPageSuccessHandler,
            error: LivesPageErrorHandler,
            data: {ajax: '1'}
        };
        $('#lives_page_form').ajaxForm(options);
    });

    // Click Handler ( Life Radio Button )
    $(document).on('click', '.clickable-life', function(e) {
        LifeClickHandler(this, e);
    });

    // Draw the update button on Page load.
    UpdateContinueButton();

    // Disable any unique lives that are already selected on one
    // radio button, but exists in another unselected radio button.
    UpdateUsedLives();


});
function UpdateUsedLives() {

    // When we have multiple LifeCompare records for the same EmployeeId
    // we need to make sure the user can not select the same existing life
    // for both of the LifeCompare records.  This JS will update the UI
    // and make sure that we don't show the same live with a radio button
    // more than once on the page at a time.

    // Show all the lives, and hide all the disabled rows.
    $(".clickable-life").removeClass("hidden");
    $(".life-compare-disabled").addClass("hidden");


    $("input[type='radio']:checked").each(function(){

        // Evaluate all the checked radio buttons.
        var radio_button = $(this);
        var name = $(radio_button).attr("name");
        var value = $(radio_button).val();
        var token = getStringValue($(radio_button).data("token"));
        if ( token != "" )
        {
            // For every checked life, look for another life with the same
            // identifier.  If we find one that matches and it's not the
            // checked life, show the disabled version.
            $("input:radio:not(:checked)[data-token='"+token+"']").each(function(){
                var container = $(this).parent().parent().parent();
                $(container).addClass("hidden");
                $(container).next().removeClass("hidden");
            });

        }

    });


}
function LifeClickHandler(click_obj, e) {

    // Grab the info on what was just clicked.
    var radio_button = $(click_obj).find('input[type="radio"]:first');
    var name = $(radio_button).attr("name");
    var value = $(radio_button).val();
    var token = getStringValue($(radio_button).data("token"));

    // If the user checked a unique life that is already has been chcecked
    // ignore the click.  This should not happen if the UI drawing function
    // is working correctly.
    $("input[type='radio']:checked").each(function(){
        if ( token != "" && token == $(this).data("token") ){
            return;
        }
    });

    // Look at what was selected and set the new_life_flg and the
    // updates_life_id values so we can post them.
    var new_life_flg = "f";
    var life_id = fRightBack(name, "-");;
    var updates_life_id = value;

    if ( value == "NEW" ) {
        new_life_flg = "t";
        updates_life_id = "";
    }else{
        new_life_flg = "f";
        updates_life_id = value;
    }

    // Grab our post URL.
    var url = $(click_obj).data('href');

    // Create our payload
    var params = {};
    params.ajax = 1;
    params.new_life_flg = new_life_flg;
    params.updates_life_id = updates_life_id;
    params.life_id = life_id;

    $.post( url, securePostVariables(params) ).done(function( responseText ) {
        try{
            // Handle know failure cases.
            if ( ! ValidateAjaxResponse(responseText ) ) { return; }

            // Look at our results and bail if the save was not successful.
            var result = JSON.parse(responseText);
            if ( result['type'] != "success" ) throw result['message'];

            // Good!  Collect the values associated with the user action.
            $(radio_button).prop("checked", true);

            $("input[type='radio']").not(":checked").each(function(){
                if ( token != "" && token == $(this).data("token") ){
                    var row_container = $(this).parent().parent().parent();
                    $(row_container).addClass("hidden");
                    $(row_container).next().removeClass("hidden");
                    //$(this).parent().parent().parent().addClass("redBorder");
                }
            });

            UpdateContinueButton();
            UpdateUsedLives();

        }catch(err){
            $(click_obj).prop("checked", false);
            AJAXPanic(err)
        }

    });


}
function UpdateContinueButton() {

    // Get a list of all the radio groups on the page.
    var lookup = {};
    $('input[type="radio"]:not(:checked)').each(function(index){
        var name = $(this).attr("name");
        lookup[name] = true;
    });
    var groups = Object.keys(lookup);

    // Count the number of groups not checked.
    var not_checked = 0;
    $.each(groups, function(index, name){
        var value = $('input[name="'+name+'"]').val();
        if ($('input[name="'+name+'"]').is(':checked'))
        {
            //console.log("CHECKED: index["+index+"], name["+name+"], value["+value+"]");
        }
        else
        {
            not_checked++;
            //console.log("NOT CHECKED: index["+index+"], name["+name+"], value["+value+"]");
        }
    })

    var button = $("#lives_complete_button");
    if ( not_checked == 0 ) {
        $(button).prop("disabled", false);
        $(button).addClass('btn-primary');
        $(button).removeClass("btn-working");
    }else{
        $(button).prop("disabled", true);
        $(button).addClass('btn-working');
        $(button).removeClass("btn-primary");
    }
}
function LivesPageBeforeSubmit() {
    beforeFormPost("lives_page_form");
}
function LivesPageSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "lives_page_form", responseText, true );
        var result = JSON.parse(responseText);
    }catch(err){
        var response = Array();
        response['responseText'] = err;
        LivesPageErrorHandler(response);
        return;
    }
}
function LivesPageErrorHandler(response) {
    failedFormPost( response['responseText'], "lives_page_form" );
}
