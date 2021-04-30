$(function(){

    // Click Handler ( Add User Cancel Button Handler )
    $(document).on('click', '#plan_type_form #back_button', function(e) {
        location.href= base_url + "/wizard/review/plans";
    });

    // Click Handler ( Add User Submit Button Handler )
    $(document).on('click', '#plan_type_form button[type="submit"]', function(e) {
        var options = {
            beforeSubmit: PlanTypeUploadBeforeSubmit,
            success: PlanTypeUploadSuccessHandler,
            error: PlanTypeUploadErrorHandler,
            data: {ajax: '1'}
        };
        $('#plan_type_form').ajaxForm(options);

    });

    // DROPDOWN BUTTON - CHANGE HANDLER
    $(document).on('click', '.btn-group .dropdown-menu li', function () {
        ActivateDropdownButton(this);
    });

    // Show Dropdown Event ( Mapping Column Dropdowns )
    $(document).on('show.bs.dropdown', '.mapping-dropdown', function() {
        GrowFormBottomPaddingForDropdown( $(this) );
    });

    // Hide Dropdown Event ( Mapping Column Dropdowns )
    $(document).on('hide.bs.dropdown', '.mapping-dropdown', function() {
        ShrinkFormBottomPaddingForDropdown();
    });

});

function PlanTypeUploadBeforeSubmit() {
    beforeFormPost("plan_type_form");
}
function PlanTypeUploadSuccessHandler(responseText, statusText, xhr, form) {


    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "plan_type_form", responseText, true );
        var result = JSON.parse(responseText);

        var type = getStringValue(result['type']);
        if ( result['type'] == "danger"  ) {

            if ( result['payload'] != undefined ) {

                // Init the wizard errors object if we have not already done so.
                if ( $("#wizard_errors").hasClass("hidden") )
                {
                    $("#wizard_errors").hide();
                    $("#wizard_errors").removeClass("hidden");
                }

                var payload = result['payload'];
                $("#wizard_error_list").empty();
                for ( var i = 0, l = payload.length; i < l; i++ ) {
                    var item = payload[i];
                    var error = item["message"];
                    var value = item["value"];
                    //SetMappingDropdownButtonClass(value, "btn-danger");
                    $("#wizard_error_list").append("<li key='"+value+"'>"+error+"</li>");
                }
                $("#wizard_errors").show();
                return;

            }
            throw result['message'];
        }

    }catch(err){
        var response = Array();
        response['responseText'] = err;
        ValidateUploadErrorHandler(response);
        return;
    }
}
function PlanTypeUploadErrorHandler(response) {
    failedFormPost( response['responseText'], "plan_type_form" );
    //AJAXPanic(responseText);
}

function ActivateDropdownButton( click_obj ) {

    var li = $(click_obj);
    var container = $(li).closest(".mapping-dropdown");
    var button = $(container).find("button");
    var input = $(container).find("input").first();

    var previous_value = getStringValue($(button).attr("selected-value"));
    var value = $(li).data("value");
    var display = $(li).data("display");

    if ( value == "" ) {
        display = $(container).data("not-selected-display");
    }

    $(button).attr("selected-value", value);
    $(button).html(display + " <span class='caret'>");
    $(input).val(value);

    AddItemToMappingDropboxes(previous_value);
    RemoveItemFromMappingDropboxes(value);
    DrawConditionalBlocks();
    ColorMappingDropbox(button, value);

}
