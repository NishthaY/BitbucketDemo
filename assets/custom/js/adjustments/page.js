$(function(){

    // Click Handler ( Submit Button Handler )
    $(document).on('click', '#manual_adjustment_page_form #adjustments_complete_button', function(e) {
        var options = {
            beforeSubmit: AdjustmentPageBeforeSubmit,
            success: AdjustmentPageSuccessHandler,
            error: AdjustmentPageErrorHandler,
            data: {ajax: '1'}
        };
        $('#manual_adjustment_page_form').ajaxForm(options);

    });
});
function AdjustmentPageBeforeSubmit() {
    beforeFormPost("manual_adjustment_page_form");
}
function AdjustmentPageSuccessHandler(responseText, statusText, xhr, form) {

    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "manual_adjustment_page_form", responseText, true );
        var result = JSON.parse(responseText);
    }catch(err){
        var response = Array();
        response['responseText'] = err;
        AdjustmentPageErrorHandler(response);
        return;
    }
}
function AdjustmentPageErrorHandler(response) {
    failedFormPost( response['responseText'], "manual_adjustment_page_form" );
}
