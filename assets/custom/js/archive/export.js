$(function(){

    // Click Handler ( Manage Export List Row Action Button - CANCEL )
    $(document).on('click', '.action-cell-cancel', function(e) {
        e.preventDefault();
        ExportTableActionButtonClickHandler(this, e);
    });

    // Click Handler ( Manage Export List Row Action Button - DELETE )
    $(document).on('click', '.action-cell-delete', function(e) {
        e.preventDefault();

        // Hide the selector?
        //$('span.select2').closest('div').addClass("hidden");

        // Move the HREF on the delete button onto the delete widget.
        var href = $(this).attr('href');
        var widget = $("#remove_export_widget");
        $(widget).attr('data-href', href);

        // present the remove widget.
        refreshWidget( 'remove_export_widget', "showForm", 'remove_export_form' );
    });

    // Click Handler ( Manage Export List Refresh Button )
    $(document).on('click', '#refresh_btn', function(e) {
       e.preventDefault();
       refreshWidget('available_exports', 'InitExportTable');
    });

    // Click Handler ( Add Export Submit Button Handler )
    $(document).on('click', '#create_export_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: CreateExportBeforeSubmit,
            success: CreateExportSuccessHandler,
            error: CreateExportErrorHandler,
            data: {ajax: '1'}
        };
        $('#create_export_form').ajaxForm(options);

        // Define the form validation.
        var create_export_form_validator = undefined;

        if( $("#create_export_form").length ) {
            create_export_form_validator = $("#create_export_form").validate({
                rules: { },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#create_export_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });

    // Click Handler ( Company/ParentCompany select handler )
    $(document).on('change', '#view_selector', function(e) {
        var url = $( "#view_selector option:selected" ).val();
        if ( getStringValue(url) )
        {
            location.href = url;
        }
    });

    // Click Handler ( Create Export Form Checkbox )
    $(document).on('click', 'input[type="checkbox"]', function(e) {
        ExportCheckboxClickHandler(this, e);
    });

    // Click Handler ( Create Export Form Checkbox Description )
    $(document).on('click', 'span.uiform-checkbox-inline-desc', function(e){
        ExportCheckboxClickHandler(this, e);
    });

    // Click Handler ( Remove Export Widget No Button )
    $(document).on('click', '#remove_export_form #no_btn', function(e) {
        var form = $("#remove_export_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Remove Export Widget Yes Button )
    $(document).on('click', '#remove_export_form #yes_btn', function(e) {
        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: RemoveExportBeforeSubmit,
            success: RemoveExportSuccessHandler,
            error: RemoveExportErrorHandler,
            data: {ajax: '1'}
        };
        $('#remove_export_form').ajaxForm(options);

        // Define the form validation.
        var remove_export_form_validator = undefined;

        if( $("#remove_export_form").length ) {
            remove_export_form_validator = $("#remove_export_form").validate({
                rules: { },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#remove_export_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.
    });


    // On load, initailize these items on the apge.
    InitExportTable();
    $('#view_selector').select2();
});

/**
 * ExportListUpdateHandler
 *
 * This function is executed when an export background task issues an update
 * event.
 *
 * @constructor
 */
function ExportListUpdateHandler()
{
    refreshWidget( 'available_exports', 'InitExportTable');
}

/**
 * RemoveExportBeforeSubmit, RemoveExportSuccessHandler, RemoveExportErrorHandler
 *
 * Hand remove export form state changes.
 *
 * @constructor
 */
function RemoveExportBeforeSubmit() {
    beforeFormPost("remove_export_form");
}
function RemoveExportSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "remove_export_form", responseText, true );
        refreshWidget("available_exports", "InitExportTable");
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function RemoveExportErrorHandler(response) {
    failedFormPost( response['responseText'], "remove_export_form" );
}


/**
 * ExportTableActionButtonClickHandler
 *
 * Trigger the server action when a user clicks on a table row action button.
 *
 * @param click_obj
 * @param e
 * @constructor
 */
function ExportTableActionButtonClickHandler(click_obj, e)
{
    e.preventDefault();

    var url = $(click_obj).attr("href");

    var params = {};
    params.ajax = 1;
    params.identifier = $(click_obj).data('identifier');
    params.identifier_type = $(click_obj).data('identifier_type');
    params.url = url;

    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
        try {
            var result = JSON.parse(responseHTML);
            if ( result['type'] == "success" )
            {
                refreshWidget("available_exports", "InitExportTable");
            }

        }catch(err){
            AJAXPanic(responseHTML);
        }
    }).fail(function( jqXHR, textStatus, errorThrown ) {
        AJAXPanic(responseHTML);
    });
}
function ExportCheckboxClickHandler(click_obj, e)
{
    var form = $('#create_export_form');
    var checked_items = $(form).find('input[type="checkbox"]:checked');
    if ( $(checked_items).length )
    {
        $('#create_export_form button[type="submit"]').prop('disabled', false);
    }
    else
    {
        $('#create_export_form button[type="submit"]').prop('disabled', true);
    }
}
function InitExportTable() {
    try{
        if ( ! $("#export_table").hasClass("dataTable") )
        {
            $("#export_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    "stateSave": false,
                    "ordering": false,
                    "initComplete": function(settings, json) {
                        $("#export_table").closest("div.card-box").removeClass("hidden");
                    },
                    "language": {
                        "emptyTable":     "No results found."
                    },
                    "iDisplayLength": 5,
                    "lengthMenu": [[5, -1], [5, "All"]]
                }
            );
        }
    }catch(err){}
}

function CreateExportBeforeSubmit() {
    beforeFormPost("create_export_form");
}
function CreateExportSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "create_export_form", responseText, true );
        refreshWidget('available_exports', 'InitExportTable');
        refreshWidget('create_exports');

    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function CreateExportErrorHandler(response) {
    failedFormPost( response['responseText'], "create_export_form" );
}
