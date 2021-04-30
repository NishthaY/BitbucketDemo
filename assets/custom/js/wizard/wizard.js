$(function(){

    $(document).on('click', '#wizard_start_over_btn', function(e) {
        StartOverWizardHandler(this, e);
    });
    $(document).on('click', '#wizard_rematch_btn', function(e) {
        WizardButtonClickHandler(this, e);
    });
    $(document).on('click', '#wizard_review_plans_btn', function(e) {
        WizardButtonClickHandler(this, e);
    });
    $(document).on('click', '#wizard_relationship_btn', function(e) {
        WizardButtonClickHandler(this, e);
    });
    $(document).on('click', '#wizard_lives_btn', function(e) {
        WizardButtonClickHandler(this, e);
    });


});
function StartOverWizardHandler(click_obj, e) {

    // This function will trigger a rollback of the whole
    // upload / validation / review process.

    var url = $(click_obj).data("href");
    if ( getStringValue(url) == "" ) {
        url = $(click_obj).attr("href");
        if ( getStringValue(url) == "" ) {
            return;
        }
    }

    ShowSpinner("Cleaning Up");
    WizardButtonClickHandler(click_obj, e);

}
function WizardButtonClickHandler(click_obj, e){
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
