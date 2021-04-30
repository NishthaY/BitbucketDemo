$(function(){

    // Click Handler ( Submit Button Handler )
    $(document).on('click', '#clarifications_form #clarifications_complete_button', function(e) {
        var options = {
            beforeSubmit: ClarificationsPageBeforeSubmit,
            success: ClarificationsPageSuccessHandler,
            error: ClarificationsPageErrorHandler,
            data: {ajax: '1'}
        };
        $('#clarifications_form').ajaxForm(options);
    });

    // Click Handler ( Life Radio Button )
    $(document).on('click', '.clickable-clarifications', function(e) {
        ClarificationClickHandler(this, e);
    });

    UpdateContinueButton();

});
function ClarificationsPageBeforeSubmit() {
    beforeFormPost("clarifications_form");
}
function ClarificationsPageSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "clarifications_form", responseText, true );
        var result = JSON.parse(responseText);
    }catch(err){
        var response = Array();
        response['responseText'] = err;
        ClarificationsPageErrorHandler(response);
        return;
    }
}
function ClarificationsPageErrorHandler(response) {
    failedFormPost( response['responseText'], "clarifications_form" );
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
            console.log("CHECKED: index["+index+"], name["+name+"], value["+value+"]");
        }
        else
        {
            not_checked++;
            console.log("NOT CHECKED: index["+index+"], name["+name+"], value["+value+"]");
        }
    })

    var button = $("#clarifications_complete_button");
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
function ClarificationClickHandler(click_obj, e) {

    e.preventDefault();

    // Grab the info on what was just clicked.
    var radio_button = $(click_obj).find('input[type="radio"]:first');
    var name = $(radio_button).attr("name");
    var value = $(radio_button).val();


    // Grab our post URL.
    var url = $(click_obj).data('href');

    // Create our payload
    var params = {};
    params.url = url;
    params.ajax = 1;
    params.value = value;
    params.name = name;

    $.post( url, securePostVariables(params) ).done(function( responseText ) {
        try{
            // Handle know failure cases.
            if ( ! ValidateAjaxResponse(responseText ) ) { return; }

            // Look at our results and bail if the save was not successful.
            var result = JSON.parse(responseText);
            if ( result['type'] != "success" ) throw result['message'];

            // Good!  Collect the values associated with the user action.
            $(radio_button).prop("checked", true);

            UpdateContinueButton();

        }catch(err){
            $(click_obj).prop("checked", false);
            AJAXPanic(err)
        }

    });
}
