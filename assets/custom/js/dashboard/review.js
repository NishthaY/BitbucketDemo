$(function(){

    $(document).on('click', 'div.review-reports #cancel_link', function(e) {
        StartOverWizardHandler(this, e);
    });

    $(document).on('click', 'div.review-reports #match_link', function(e) {
        e.preventDefault();
        WizardButtonClickHandler(this, e);
    });

    $(document).on('click', 'div.review-reports #settings_link', function(e) {
        e.preventDefault();
        WizardButtonClickHandler(this, e);
    });

    $(document).on('click', 'div.review-reports #adjustment_link', function(e) {
        e.preventDefault();
        WizardButtonClickHandler(this, e);
    });

    $(document).on('click', 'div.review-reports #relationship_link', function(e) {
        e.preventDefault();
        WizardButtonClickHandler(this, e);
    });

    $(document).on('click', 'div.review-reports #lives_link', function(e) {
        e.preventDefault();
        WizardButtonClickHandler(this, e);
    });

    $(document).on('click', 'div.review-reports #clarifications_link', function(e) {
        e.preventDefault();
        WizardButtonClickHandler(this, e);
    });

    $(document).on('click', '#finalize_button', function(e) {
        FinalizeReports();
    });

    $(document).on('click', '#finalize_reports_form button[type="submit"]', function(e) {
        SuccessFinalizeDataHandler(this, e);
    });

    $(document).on('click', '#finalize_reports_form button[type="button"]', function(e) {
        CancelFinalizeDataHandler(this, e);
    });

    InitReportReviewTable();

});


function FinalizeReports(inputs)
{


    if ( jQuery.type(inputs) === "undefined" ) { inputs = ""; }
    if ( jQuery.type(inputs) === "array") { inputs = inputs.join(":");}

    var widget = $("#finalize_reports_widget");
    var href = $(widget).data('href');

    if ( inputs != "" )
    {
        if ( getStringValue(href).indexOf("COMPANYID") > -1  )
        {
            // Save the HREF value as it contains a template key.
            $(widget).attr('data-template', href);
        }

        // Pull the template and build the href.
        var template = $(widget).data('template');
        if ( template != "" )
        {
            href = replaceFor(template, "COMPANYID", inputs);
            $(widget).attr('data-href', href);
        }
    }

    refreshWidget("finalize_reports_widget", "showForm", "finalize_reports_form");
}

function SuccessFinalizeDataHandler( click_obj, e) {

    // Create the AJAX hooks for submitting this form.
    var options = {
        beforeSubmit: FinalizeReportsBeforeSubmit,
        success: FinalizeReportsSuccessHandler,
        error: FinalizeReportsErrorHandler,
        data: {ajax: '1'}
    };
    $('#finalize_reports_form').ajaxForm(options);

    // Define the form validation.
    var finalize_reports_form_validator = undefined;

    if( $("#finalize_reports_form").length ) {
        finalize_reports_form_validator = $("#finalize_reports_form").validate({
            rules: { },
            messages: { },
            ignore: ":hidden:not(.uiform-dropdown)",
            highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
            unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
            errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
        });
    }

    // Validate the form.
    if ( $("#finalize_reports_form").validate().form() ) {
        return true;
    }
    return false; // Form not valid.
}
function CancelFinalizeDataHandler( click_obj, e) {
    e.preventDefault();
    var form = $("#finalize_reports_form");
    hideForm(form, true, true);
}
function FinalizeReportsBeforeSubmit() {
    beforeFormPost("finalize_reports_form");
}
function FinalizeReportsSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "finalize_reports_form", responseText, true );
        startWidget("finalize_reports_form", 0);
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function FinalizeReportsErrorHandler(response) {
    failedFormPost( response['responseText'], "finalize_reports_form" );
}
function InitReportReviewTable() {

    try{
        if ( ! $("#report_review_table").hasClass("dataTable") )
        {
            $("#report_review_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    "order": [[ 0, "asc" ]],
                    "initComplete": function(settings, json) {
                        $("#report_review_table").closest(".review-draft-reports-container").show();
                    },
                    "language": {
                        "emptyTable":     "No results found."
                    }
                }
            );
        }
        if ( ! $("#report_review_warning_table").hasClass("dataTable") )
        {
            $("#report_review_warning_table").DataTable(
                {
                    "bFilter": true,
                    "bInfo": true,
                    "bPaginate": true,
                    "order": [[ 3, "asc" ]],
                    "initComplete": function(settings, json) {
                        $("#report_review_warning_table").closest(".review-draft-reports-container").show();
                    },
                    "language": {
                        "emptyTable":     "No results found."
                    }
                }
            )
        }
    }catch(err){}

}
