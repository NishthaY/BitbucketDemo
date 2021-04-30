$(function(){

    // Click Handler ( Form Submit Button Handler )
    $(document).on('click', '#upload_form #upload_button', function(e) {
        UploadButtonClickHandler(this, e);
    });

    // Change Handler ( Upload File Has Been Selected )
    $(document).on('change', "#upload_button_browse", function(e){
        ValidateFile(this, e);
    });

    $(document).on('click', "#wizard_adjustments_btn", function(e) {
        WizardButtonClickHandler(this, e);
    })

    
});
function UploadButtonClickHandler(click_obj, e) {
    e.preventDefault();
    var button = $(click_obj);
    $(button).addClass("disabled");
    var button_name = $(click_obj).attr("id");
    var browse_name = button_name + "_browse";
    $("#" + browse_name).trigger('click');
}
function DashboardWizardBeforeRefresh() {

    // spinner buttons need to have the waves effect disabled.
    // Oh, and if you see the file upload button, disable that too.
    if ( $(".a2p-spinner-button").length != 0 ) {
        var l = Ladda.create( document.querySelector( ".a2p-spinner-button" ) );
    	l.start();
        $(".a2p-spinner-button").removeClass("waves-effect");
        $(".a2p-spinner-button").removeClass("waves-light");
        $(".a2p-spinner-button").prop("disabled", true);
    }

    // Make the UI a bit less jumpy by deactivating the forever loading
    // spinner before you plop in a new one.
    if ( $(".a2p-forever-spinner-button").length != 0 )
    {
        var l = Ladda.create( document.querySelector( ".a2p-forever-spinner-button" ) );
        if ( l.isLoading() ) {
            l.stop();
        }
    }

}
function DashboardWizardAfterRefresh() {

    // After we get new HTMl content, if we have a forever spinner, turn it on.
    if ( $(".a2p-forever-spinner-button").length != 0 )
    {
        var l = Ladda.create( document.querySelector( ".a2p-forever-spinner-button" ) );
        if ( ! l.isLoading() ) {
            l.start();
        }

    }

    // If the dashboard wizard widget came back empty, then refresh
    // the various dashboard widgets so they show new updated content.
    var content = $("#wizard_dashboard_widget_widget_wrapper").html();
    if ( getStringValue(content) == "" )
    {
        if ( $(".review-draft-reports-container").length == 0 )
        {
            DashboardReadyHandlerStep1();

            // Nope.  Don't run these at the same time!  One depends on the other when
            // deciding what to display.  Depending on when the return, you might end up with
            // a blank screen.  Instead, run them in series to ensure they finish in the correct order.
            //refreshWidget("report_review_widget", "ReportReviewWidgetSuccessHandler");
            //refreshWidget("dashboard_welcome_widget", "DashboardWelcomeWidgetPostRefreshHandler");
        }
        else
        {
            if ( $(".review-draft-reports-container").is(":visible") )
            {
                // The container is already visible.  Nothing to do here.

            }else
            {
                // The report review widget was loaded and contains data.  However, it is
                // not visible due to the transition from the welcome widget to the first
                // time we show the report review.  We can address than now by just showing
                // the content since we know it has some.
                $(".review-draft-reports-container").show();

            }
        }

    }

}
function DashboardReadyHandlerStep1()
{
    refreshWidget("report_review_widget", "DashboardReadyHandlerStep2");
}
function DashboardReadyHandlerStep2()
{
    // the report_review_widget is now refreshed, so show it.
    $("div.review-draft-reports-container").show();
    $("#dashboard_welcome_widget .row:first").hide();
    InitReportReviewTable();

    refreshWidget("dashboard_welcome_widget");
}


