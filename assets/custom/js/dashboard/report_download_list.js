$(function(){

    $(document).on('click', '.download-row-info-btn', function(e) {
        e.preventDefault();
        ReportInfoShowClickHandler(this,e);
    });
    $(document).on('click', '.download-row-less-btn', function(e) {
        e.preventDefault();
        ReportInfoHideClickHandler(this,e);
    });
    $(document).on('click', '.report-list-download-btn', function(e) {
        ListDownloadableReports(this, e);
    });
    $(document).on('click', '#download_report_list_form #cancel_btn', function(e) {
        hideForm( $("#download_report_list_form"), true, true );
    });

});

function MultiCompanyRowActionButtonClickHandler(click_obj, e)
{
    e.preventDefault();

    if ( $(click_obj).is(":disabled") )
    {
        return;
    }

    var li = $(click_obj);
    var anchor = $(li).find('a:first');
    var action = $(li).data('action');
    if ( action == 'edit' )
    {
        EditCompanyClickHandler(anchor, e);
    }
    if ( action == 'enable' || action == 'disable' )
    {
        EnableDisableCompanyClickHandler(anchor, e);
    }

    // If the action button is of type "skip", trigger the skip month
    // confirmation box for the company id associated with the menu item.
    if ( action == 'skip' )
    {
        if ( ! $(li).hasClass('disabled') )
        {
            var company_id = $(li).data('company-id');
            if ( getStringValue(company_id) != "" )
            {
                var companies = [ company_id ];
                SkipMonth(companies);
            }
        }
    }

}
function ListDownloadableReports(click_obj, e)
{
    e.preventDefault();

    if ( $(click_obj).is(":disabled") )
    {
        return;
    }

    // If the button that was clicked has a URL, don't show the widget.
    // Instead, just redirect to that location.
    var url = $(click_obj).attr("href");
    if ( getStringValue(url) != "" )
    {
        location.href=url;
        return;
    }

    if ( getStringValue($(click_obj).data("report-code")) != "" )
    {
        // We have a report code.  Redirect to download page for that report code.
        var url = base_url + "auth/error_404";
        var report_code = $(click_obj).data("report-code");
        var company_id = $(click_obj).data("company-id");
        var import_date = $(click_obj).data("import-date");

        if ( report_code === 'issues' )
        {
            url = base_url + "download/" + report_code + "/" + company_id + "/" + import_date;
        }
        location.href = url;
        return;
    }
    else
    {
        // Pick which widget we are going to display base on our data.
        var widget_name = "download_report_list_widget";
        var form_name = "download_report_list_form";
        var widget = $("#" + widget_name);

        // Push the href into the template since this widget will have dynamic data.
        var template = getStringValue( $(widget).data("href-template") );
        if ( template == "" ) {
            $(widget).attr("data-href-template", $(widget).data("href") )
        }

        // Pull the carrier off of the click object.
        var carrier_id = $(click_obj).data("carrier");
        var import_date = $(click_obj).data("import-date");
        var company_id = $(click_obj).data('company-id');

        if ( getStringValue(carrier_id) === '' ) return;
        if ( getStringValue(import_date) === '' ) return;
        if ( getStringValue(company_id) === '' ) return;


        // Pull the URL off the anchor that was clicked and set it on the widget.
        var url = $(widget).data("href-template");
        url = replaceFor(url, "CARRIER", encodeURIComponent(carrier_id) );
        url = replaceFor(url, "DATE", encodeURIComponent(import_date) );
        url = replaceFor(url, "COMPANYID", encodeURIComponent(company_id) );

        $(widget).attr("data-href", url);
        refreshWidget( widget_name, "showForm", form_name );
    }



}
function ReportHideAllReportInfoBlocks()
{
    $(".download-row-less-btn").each(function(){
        var button = $(this);
        var container = $(button).closest(".download-report-row-container");
        var info = $(container).find(".download-help:first");
        if ( ! $(info).hasClass("hidden") )
        {
            $(info).addClass("hidden");
            $(button).addClass("hidden");

            var next_button = $(container).find(".download-row-info-btn:first");
            $(next_button).removeClass("hidden");
        }

    })

}
function ReportInfoShowClickHandler(click_obj, e)
{
    ReportHideAllReportInfoBlocks();

    var button = $(click_obj);
    var container = $(button).closest(".download-report-row-container");
    var info = $(container).find(".download-help:first");
    var next_button = $(container).find(".download-row-less-btn:first");
    $(info).removeClass("hidden");
    $(button).addClass("hidden");
    $(next_button).removeClass("hidden");

}
function ReportInfoHideClickHandler(click_obj, e)
{
    ReportHideAllReportInfoBlocks();
    var button = $(click_obj);
    var container = $(button).closest(".download-report-row-container");
    var info = $(container).find(".download-help:first");
    var next_button = $(container).find(".download-row-info-btn:first");
    $(info).addClass("hidden");
    $(button).addClass("hidden");
    $(next_button).removeClass("hidden");
}

/**
 * DownloadReportButtonClickHandler
 *
 * Process the download button clicks on the report review dialog
 * using this function.  Based on the currently selected value of the
 * multi-option button, take the appropriate action.
 *
 * @param click_obj
 * @param e
 * @constructor
 */
function DownloadReportButtonClickHandler(click_obj, e)
{
    e.preventDefault();

    var button = $(click_obj);
    var value = $(button).attr('value');

    if ( value.startsWith('download') )
    {
        // We have a request to download the report via the browser to the local
        // machine.
        location.href = base_url + value;
        MultiOptionButtonReportSuccess(button)
    }
    else if ( value.startsWith('deliver') )
    {
        // We have a request to deliver the report via their file transfer settings.
        // Do that now.
        var url = base_url + value;
        $.post( url, securePostVariables() ).done(function( responseHTML ) {
            if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
            try {
                var result = JSON.parse(responseHTML);
                if ( result['type'] == "success" )
                {
                    setTimeout(function() { MultiOptionButtonReportSuccess(button) }, 2000);
                }
                else
                {
                    MultiOptionButtonReportFailure(button);
                }

            }catch(err){
                MultiOptionButtonReportFailure(button)
            }
        }).fail(function( jqXHR, textStatus, errorThrown ) {
            MultiOptionButtonReportFailure(button);
        });
    }


}