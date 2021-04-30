$(function()
{
    // Start Over - This button, when clicked, will delete this workflow.
    $(document).on('click', 'button.btn-wf-rollback', function(e) {
        ShowSpinner("Cleaning Up");
        WorkflowButtonClickHandler(this, e);
    });


    $(document).on('click', 'button.btn-wf-moveto', function(e){
        WorkflowButtonClickHandler(this, e);
    });




});

function WorkflowButtonClickHandler(click_obj, e)
{
    e.preventDefault();

    var alert_obj = $("div.container").find(".alert:first");

    // Grab our href.
    var url = $(click_obj).data("href");
    if ( getStringValue(url) == "" ) {
        url = $(click_obj).attr("href");
        if ( getStringValue(url) == "" ) {
            return;
        }
    }

    // Ensure the ajax flag is set to true.
    var params = {};
    params.ajax = 1;
    params.url = url;
    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
        HideSpinner();
        try{
            var result = JSON.parse(responseHTML);
            ShowAlert( alert_obj, result['type'], result['message']);
        }catch(err){
            ShowAlert( alert_obj, "danger", "Unexpected situation.  Please try again later.");
        }

    }).fail(function( jqXHR, textStatus, errorThrown ) {
        ShowAlert( alert_obj, "danger", "Unexpected situation.  Please try again later.");
    });

}