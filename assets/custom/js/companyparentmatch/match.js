$(function()
{


    // Click Handler ( Submit Button Handler )
    $(document).on('click', '#parent_match_form button[type="submit"]', function(e) {

        var form = $('#parent_match_form');
        var options = {
            beforeSubmit: ParentMatchFormBeforeSubmit,
            success: ParentMatchFormSuccessHandler,
            error: ParentMatchFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#parent_match_form').ajaxForm(options);



    });

    InitMappingTable();

});
var parent_match_debug = true;

function ParentMatchFormBeforeSubmit(responseText, statusText, xhr, form)
{
    if ( parent_match_debug ) console.log("ParentMatchFormBeforeSubmit: started");
    ShowSpinner("quick scan");
    beforeFormPost("parent_match_form");
    //SaveAllMatchPreferences();
}
function ParentMatchFormSuccessHandler(responseText, statusText, xhr, form)
{
    if ( parent_match_debug ) console.log("ParentMatchFormSuccessHandler: started");
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    if ( parent_match_debug ) console.log("ParentMatchFormSuccessHandler: was valid");
    try{
        HideSpinner();
        successfulFormPost( "parent_match_form", responseText, true );
        var result = JSON.parse(responseText);
        console.log(result);

    }catch(err){
        var response = Array();
        response['responseText'] = err;
        ParentMatchFormErrorHandler(response);
        return;
    }
}
function ParentMatchFormErrorHandler(responseText, statusText, xhr, form)
{
    if ( parent_match_debug ) console.log("ParentMatchFormErrorHandler: started");
    HideSpinner();
    failedFormPost( response['responseText'], "parent_match_form" );
    //AJAXPanic(responseText);
}
