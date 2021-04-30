$(function()
{
    // Click Handler ( Submit Button Handler )
    $(document).on('click', '#sample_waiting_form button[type="submit"]', function(e) {

        var form = $('#sample_waiting_form');
        var options = {
            beforeSubmit: SampleWaitingFormBeforeSubmit,
            success: SampleWaitingFormSuccessHandler,
            error: SampleWaitingFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#sample_waiting_form').ajaxForm(options);
    });

});
var sample_waiting_debug = true;

function SampleWaitingFormBeforeSubmit(responseText, statusText, xhr, form)
{
    if ( sample_waiting_debug ) console.log("SampleWaitingFormBeforeSubmit: started");
    beforeFormPost("sample_waiting_form");
}
function SampleWaitingFormSuccessHandler(responseText, statusText, xhr, form)
{
    if ( sample_waiting_debug ) console.log("SampleWaitingFormSuccessHandler: started");
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    if ( sample_waiting_debug ) console.log("SampleWaitingFormSuccessHandler: was valid");
    try{
        successfulFormPost( "sample_waiting_form", responseText, true );
        var result = JSON.parse(responseText);
        console.log(result);

    }catch(err){
        var response = Array();
        response['responseText'] = err;
        SampleWaitingFormErrorHandler(response);
        return;
    }
}
function SampleWaitingFormErrorHandler(responseText, statusText, xhr, form)
{
    if ( sample_waiting_debug ) console.log("SampleWaitingFormErrorHandler: started");
    failedFormPost( response['responseText'], "sample_waiting_form" );
}
