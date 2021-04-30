$(function(){

});
var s3upload_debug = false;

function ValidateFile(click_obj, e) {
    try
    {
        if ( s3upload_debug ) console.log("ValidateFile: start");
        // Grab the file object the user specified.
        var file = click_obj.files[0];

        // Clear out any previous alerts.
        var alert_obj = $("div.container").find(".alert:first");
        $(alert_obj).addClass("hidden");

        // If it's undefined, they hit the cancel button. stop processing.
        if ( file == undefined ) {
            return;
        }

        // Look at a few properties on the file.
        var filename = getStringValue(file.name);
        var mime = getStringValue(file.type).toLowerCase();
        var size = parseInt(file.size);

        // Review the file being upload.  If the superficial aspects about this
        // file do not appear to be a data format we support, notify the user
        // right away.
        var shiny = true;
        var supported_types = ['text/csv', 'application/csv', 'text/plain'];
        var supported_extensions = /(\.csv|\.CSV|\.txt|\.TXT|\.text|\.TEXT)$/i;

        // Chrome on Windows gets the empty string for file uploads.  Maybe because the end client does
        // not support .csv.  ( no excel installed or something ) Maybe a bug in that browser.  Either way
        // I'm pulling mime/type validation.
        //if ( shiny && jQuery.inArray( mime, supported_types ) == -1 ) { shiny = false; }

        if ( shiny && ! supported_extensions.exec(filename) ) { shiny = false; }
        if ( shiny && size <= 0 ) { shiny = false; }
        if ( ! shiny ) { throw "Please upload your data as a CSV file."; }

        // Do it!
        UploadFile( click_obj, e );
    }
    catch(err)
    {
        var alert_obj = $("div.container").find(".alert:first");
        ShowAlert(alert_obj, "danger", err);
    }
}
function UploadFile(click_obj, e)
{
    if ( s3upload_debug ) console.log("UploadFile: start");

    // Grab a reference to the file the user has requested.
    var file = click_obj.files[0];

    if ( s3upload_debug ) console.log("UploadFile: grabbed file");

    // Show our loading indicator.
    notify_workflow_step_changing();
    ShowSpinner();

    if ( s3upload_debug ) console.log("UploadFile: showing spinner");

    // Create the FormData object we will use to make the AJAX post.
    // Shove into it, each hidden element on our form.
    var fd = new FormData();
    $("#upload_form input[type='hidden']").each(function(){
        var key = $(this).attr("name");
        var value = $(this).val();
        fd.append(key, value);
    });


    if ( s3upload_debug ) console.log("UploadFile: created form data");

    // Attach/append the file to the FormData for upload.
    fd.append("file",file);

    if ( s3upload_debug ) console.log("UploadFile: appended the file.");

    // Calculate the size of our file.
    var filesize = click_obj.files[0].size;

    if ( s3upload_debug ) console.log("UploadFile: file is ["+filesize+"] big.");

    // Create an XMLHttpRequest object so we can monitor the progress.
    var xhr = new XMLHttpRequest();
    xhr.upload.addEventListener("progress", uploadProgress, false);
    xhr.addEventListener("load", uploadComplete, false);
    xhr.addEventListener("error", uploadFailed, false);
    xhr.addEventListener("abort", uploadCanceled, false);

    if ( s3upload_debug ) console.log("UploadFile: monitoring the request");
    if ( s3upload_debug ) console.log("UploadFile: postin to: " + $("#upload_form").attr("action")  );

    // Post it!
    xhr.open('POST', $("#upload_form").attr("action"), true); //MUST BE LAST LINE BEFORE YOU SEND
    xhr.send(fd);

    // Next, save the name of the file we just uploaded in a preference.
    var form = $("#upload_form");
    var entity = $(form).data('entity');

    if ( entity == 'companyparent' )
    {
        SaveCompanyParentPreference("upload", "original_filename", getStringValue(file.name));
    }
    else
    {
        SaveCompanyPreference("upload", "original_filename", getStringValue(file.name));
    }

}
function uploadProgress(evt) {
    if (evt.lengthComputable) {
        var percentComplete = Math.round(evt.loaded * 100 / evt.total);
        var filesize = $("#upload_form input[type='file']")[0].files[0].size;
        if ( percentComplete == 100 )
        {
            UpdateSpinner("encrypting");
        }
        else if ( filesize > ( 1024 * 2 ) ){
            UpdateSpinner(getStringValue(percentComplete) + "%");
        }else{
            UpdateSpinner("loading");
        }
    }
}
function uploadComplete(evt){

    if ( s3upload_debug ) { console.log("uploadComplete: File upload is complete."); }

    // This function handles upload requests from both the company ( wizard ) upload
    // and the companyparent ( workflow ) upload.  Denote which type we are dealing with
    // so we can do make different calls based on the type.
    var type = 'wizard';
    var url = base_url + "wizard/save";

    // Set the URL to match the href on the button, if we have one.
    var upload_button = $("#upload_button");
    var href = getStringValue($(upload_button).data('href'));
    if ( href != "" )
    {
        type = 'workflow';
        url = base_url + href;
    }

    var params = {};
    params.ajax = 1;
    params.upload_filename = $("#upload_form input[name='key']").first().val();
    params.url = url;


    if ( s3upload_debug ) { console.log("uploadComplete: type["+type+"]"); }
    if ( s3upload_debug ) { console.log("uploadComplete: url["+url+"]"); }


    if ( s3upload_debug ) { console.log("uploadComplete: POSTING: " + url); }
    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        console.log("uploadComplete: responseHTML");
        console.log("[" + responseHTML + "]");

        if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
        try{
            var result = JSON.parse(responseHTML);
            if ( result['type'] == "success") {
                if (s3upload_debug) {
                    console.log("uploadComplete: success");
                }
                if (type == 'wizard') {
                    refreshWidget("wizard_dashboard_widget", $("#wizard_dashboard_widget").data("callback"));
                    refreshWidget("dashboard_welcome_widget");
                    HideSpinner();
                } else
                {
                    refreshWidget("wf_parent_import_csv_widget");
                    HideSpinner();
                }
                return;
            }
            if ( result['type'] == "danger" ){
                if ( s3upload_debug ) { console.log("uploadComplete: danger"); }
                var message = result['message'];
                var alert_obj = $(".container").find(".alert:first");
                ShowAlert(alert_obj, "danger", message);
                HideSpinner();
                $(upload_button).removeClass('disabled');
                return;
            }
            throw "Unsupported response type.";

        }catch(err){
            if ( s3upload_debug ) { console.log("uploadComplete: panic"); }
            AJAXPanic(err + " " + responseHTML);
            return;
        }

    }).fail(function( jqXHR, textStatus, errorThrown ) {
        if ( s3upload_debug ) { console.log("uploadComplete: Oh no!  The upload has failed."); }
        refreshWidget("wizard_dashboard_widget", $("#wizard_dashboard_widget").data("callback"));
        HideSpinner();
        $(upload_button).addClass('disabled');
    });
}
function uploadFailed(evt){
    if ( s3upload_debug ) { console.log("XMLHttpRequest: fired the error event."); }
    if ( type == 'wizard') refreshWidget("wizard_dashboard_widget", $("#wizard_dashboard_widget").data("callback"));
    HideSpinner();

}
function uploadCanceled(evt){
    if ( s3upload_debug ) { console.log("XMLHttpRequest: fired the abort event."); }
    refreshWidget("wizard_dashboard_widget", $("#wizard_dashboard_widget").data("callback"));
    HideSpinner();
}
