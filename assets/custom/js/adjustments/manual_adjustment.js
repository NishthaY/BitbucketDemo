$(function(){
    InitAdjustmentsTable();

    // Manual Adjustment - Delete Adjustment ( Click Handler )
    $(document).on('click', 'table td.action-cell a.action-cell-delete', function(e) {
        ManualAdjustmentDeleteClickHandler(this, e);
    });

    // Manual Adjustment - Delete Adjustment ( Click Handler )
    //$(document).on('click', ".edit-manual-adjustment-btn", function(e) {
    $(document).on('click', 'table td.action-cell a.action-cell-edit', function(e) {
        ManualAdjustmentEditClickHandler(this, e);
    });

    // Manual Adjustment - Add Adjustment ( Click Handler )
    $(document).on('click', "#add_adjustment_btn", function(e){
        ManualAdjustmentButtonClickHandler(this, e);
    });

    // Manual Adjustment Form - Cancel Button - ( Click Handler )
    $(document).on('click', "#manual_adjustment_form #cancel_manual_adjustment_button", function(e){
        ManualAdjustmentButtonCancelClickHandler(this, e);
    });

    // Manual Adjustment Form - Save Button - ( Click Handler )
    $(document).on('click', "#manual_adjustment_form #save_manual_adjustment_button", function(e){

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: ManualAdjustmentBeforeSubmit,
            success: ManualAdjustmentSuccessHandler,
            error: ManualAdjustmentErrorHandler,
            data: {ajax: '1'}
        };
        $('#manual_adjustment_form').ajaxForm(options);

        // Define the form validation.
        var manual_adjustment_form_validator = undefined;

        if( $("#manual_adjustment_form").length ) {
            manual_adjustment_form_validator = $("#manual_adjustment_form").validate({
                rules: {
                    carrier_id: {
                        required: true
                    },
                    amount: {
                        required: true,
                        money:true
                    },
                    description: {
                        required: true
                    }
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
            jQuery.validator.addMethod('money', MoneyValidator, 'Please enter a valid money value.');
        }

        // Validate the form.
        if ( $("#manual_adjustment_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });


});
function ManualAdjustmentEditClickHandler( click_obj, e) {

    e.preventDefault();
    var id = fRightBack($(click_obj).attr("href"), "/");
    ShowManualAdjustmentForm( id );

}
function ManualAdjustmentDeleteClickHandler( click_obj, e) {
    e.preventDefault();

    var url = $(click_obj).attr("href");
    var adjustment_id = fRightBack(url, "/");
    url = fLeftBack(url, "/");
    
    var params = {};
    params.ajax = 1;
    params.url = url;
    params.adjustment_id = adjustment_id;

    // Make an ajax call to remove the record in question.
    $.post( url, securePostVariables(params) ).done(function( responseText ) {
        if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    });


}
function InitAdjustmentsTable(){
    try{
        // Only initialze the datatable if we have not already done so.
        // We can tell this if the table has the dataTable class or not.
        if ( ! $("#adjustments_table").hasClass("dataTable") )
        {
            var filter = true;
            var info = true;
            var pager = false;

            // If we have the empty table, turn off the fancy features.
            if ( $("#adjustments_table tbody tr").length == 0 )
            {
                filter = false;
                info = false;
                pager = false;
            }
            $("#adjustments_table").DataTable(
                {
                    "bFilter": filter,
                    "bInfo": info,
                    "bPaginate": pager,
                    stateSave: true,
                    "initComplete": function(settings, json) {
                        if ( ! $("#adjustments_table").find("td:first").hasClass("dataTable") ) {
                            $("#adjustments_table").closest("div.panel-body").removeClass("hidden");
                        }
                    },
                    "language": {
                        "emptyTable":     "No adjustments found."
                    }
                }
            );
        }
    }catch(err){
    }
}
function ManualAdjustmentButtonCancelClickHandler(click_obj, e) {
    var form = $("#manual_adjustment_form");
    hideForm(form, true, true);
}
function ManualAdjustmentButtonClickHandler(click_obj, e) {
    e.preventDefault();
    ShowManualAdjustmentForm();
}
function ManualAdjustmentBeforeSubmit() {
    beforeFormPost("manual_adjustment_form");
}
function ManualAdjustmentSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        var result = JSON.parse(responseText);
        if ( result['type'] == "success" ) {
            successfulFormPost( "manual_adjustment_form", responseText, true );
            //refreshWidget("wizard_dashboard_widget");
            alert("Done, reload the page?");
            return;
        }

        if ( result['type'] == "danger" ){
            var message = result['message'];
            var alert_obj = $(form).parent().find(".alert:first");
            ShowAlert(alert_obj, "danger", message);
            return;
        }
        throw "Unsupported repsonse type.";

    }catch(err){
        AJAXPanic(err + " " + responseText);
        return;
    }
}
function ManualAdjustmentErrorHandler(response) {
    failedFormPost( response['responseText'], "edit_user_form" );
}
function MoneyValidator(value, element) {
    //var isValidMoney = /^\d{0,4}(\.\d{0,2})?$/.test(value);
    var isValidMoney = /^[+-]?[0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]{2})?$/.test(value);
    return this.optional(element) || isValidMoney;
}
function ShowManualAdjustmentForm( id ) {
    var widget_name = "manual_adjustment_widget";
    var form_name = "manual_adjustment_form";
    var widget = $("#" + widget_name);

    // The URL on the form is really a template to begin with.
    // Push the URL template into an attribute so we can find it
    // later if we need it.
    var url = $(widget).attr("data-href");
    var template = $(widget).attr("data-template");
    if ( getStringValue(template) == "" ){
        $(widget).attr("data-template", url);
    }

    // Update the form URL so that we can post new or existing items.
    url = $(widget).attr("data-template");
    if ( getStringValue(id) == "" )
    {
        url = replaceFor(url, "/ID", "");
    }else{
        url = replaceFor(url, "/ID", "/" + id);
    }
    $(widget).attr("data-href", url);

    // refresh the widget.
    refreshWidget( widget_name, "showForm", "manual_adjustment_form" );
}
