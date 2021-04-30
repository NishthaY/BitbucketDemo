$(function(){

    ConfirmButtonInit();
    InitCompanyParentTable();
    InitFormHeader();

    // Click Handler ( Add Features Button )
    $(document).on('click', '#edit_parent_form #edit_parent_features_btn', function(e) {
        EditCompanyParentFeatures(this, e);
    });

    // Click Handler ( Optional Column Checkbox )
    $(document).on('click', '#optional_columns .checkbox_outer', function(e) {
        OptionalColumnClickHandler(this, e);
    });

    // Click Handler ( Edit Action Icons )
    $(document).on('click', '#add_parent_form #user_add_btn', function(e) {
        AddFirstUserClickHandler(this, e);
    });

    // Click Handler ( Edit Action Icons )
    $(document).on('click', 'table td.action-cell a.action-cell-edit', function(e) {
        EditCompanyParentClickHandler(this, e);
    });

    // Click Handler ( Delete Action Icons )
    $(document).on('click', 'table td.action-cell a.action-cell-remove', function(e) {
        EnableDisableCompanyParentClickHandler(this, e);
    });

    // Click Handler ( Change To Action Icons )
    $(document).on('click', 'table td.action-cell a.action-cell-changeto', function(e) {
        ChangeToCompanyParentClickHandler(this, e);
    });

    // Click Handler ( Change To Company Parent No Button Handler )
    $(document).on('click', '#changeto_parent_form #no_btn', function(e) {
        var form = $("#changeto_parent_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Change To Company Parent Yes Button Handler )
    $(document).on('click', '#changeto_parent_form #yes_btn', function(e) {
        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: ChangeToCompanyParentBeforeSubmit,
            success: ChangeToCompanyParentSuccessHandler,
            error: ChangeToCompanyParentErrorHandler,
            data: {ajax: '1'}
        };
        $('#changeto_parent_form').ajaxForm(options);

        // Define the form validation.
        var changeto_company_parent_form_validator = undefined;

        if( $("#changeto_parent_form").length ) {
            changeto_company_parent_form_validator = $("#changeto_parent_form").validate({
                rules: { },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#changeto_parent_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.
    });

    // Click Handler ( Add Company Parent Cancel Button Handler )
    $(document).on('click', '#add_parent_form #cancel_add_btn', function(e) {
        var form = $("#add_parent_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Add Company Parent Submit Button Handler )
    $(document).on('click', '#add_parent_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: AddCompanyParentBeforeSubmit,
            success: AddCompanyParentSuccessHandler,
            error: AddCompanyParentErrorHandler,
            data: {ajax: '1'}
        };
        $('#add_parent_form').ajaxForm(options);

        // Define the form validation.
        var add_company_parent_form_validator = undefined;

        if( $("#add_parent_form").length ) {
            add_company_parent_form_validator = $("#add_parent_form").validate({
                rules: {
                    name: {
                        required: true,
                        companyparentnameValidator: true,
                        companynameValidator: true
					},
                    address: "required",
                    city: "required",
                    state: "required",
                    postal: {
                        required: true,
                        zipcodeUS: true
                    },
                    first_name: "required",
                    last_name: "required",
                    email_address: {
                        required: true,
                        usernameValidator: true
                    },
                    seats: {
                        required: true,
                        number: true,
                        min: 0
                    }
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
            jQuery.validator.addMethod('usernameValidator', usernameValidator, 'User with that email address already exists.');
            jQuery.validator.addMethod('companyparentnameValidator', companyparentnameValidator, 'Business with that name already exists.');
            jQuery.validator.addMethod('companynameValidator', companynameValidator, 'Business with that name already exists.');
        }

        // Validate the form.
        if ( $("#add_parent_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });

    // Click Handler ( Edit Company Parent Cancel Button Handler )
    $(document).on('click', '#edit_parent_form #cancel_edit_btn', function(e) {
        var form = $("#edit_parent_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Edit Company Parent Submit Button Handler )
    $(document).on('click', '#edit_parent_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: EditCompanyParentBeforeSubmit,
            success: EditCompanyParentSuccessHandler,
            error: EditCompanyParentErrorHandler,
            data: {ajax: '1'}
        };
        $('#edit_parent_form').ajaxForm(options);

        // Define the form validation.
        var edit_company_parent_form_validator = undefined;

        if( $("#edit_parent_form").length ) {
            edit_company_parent_form_validator = $("#edit_parent_form").validate({
                rules: {
                    name: {
                        required: true,
                        companyparentnameValidator: true,
                        companynameValidator: true
					},
                    address: "required",
                    city: "required",
                    state: "required",
                    postal: {
                        required: true,
                        zipcodeUS: true
                    },
                    seats: {
                        required: true,
                        number: true,
                        min: 0
                    }
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
            jQuery.validator.addMethod('companyparentnameValidator', companyparentnameValidator, 'Business with that name already exists.');
            jQuery.validator.addMethod('companynameValidator', companynameValidator, 'Business with that name already exists.');
        }

        // Validate the form.
        if ( $("#edit_parent_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });


    // Click Handler ( Add Company Parent Submit Button Handler )
    $(document).on('click', '#file_transfer_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: FileTransferFormBeforeSubmit,
            success: FileTransferFormSuccessHandler,
            error: FileTransferFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#file_transfer_form').ajaxForm(options);

        // Define the form validation.
        var file_transfer_form_validator = undefined;

        if( $("#file_transfer_form").length ) {
            file_transfer_form_validator = $("#file_transfer_form").validate({
                rules: {
                    name: {
                        required: true
                    }
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#file_transfer_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });


});
function RefreshCompanyParentFeatures( widget_name )
{
    refreshWidget(widget_name, "ConfirmButtonInit");
}
function RemoveCompanyParentFeature( click_obj )
{
    var button = $(click_obj);
    var href = $(button).attr('href');

    if ( getStringValue(href) !== '' )
    {
        var params = {};
        params['identifier'] = $(button).data('identifier');
        params['identifier_type'] = $(button).data('identifier_type');
        params['feature_code'] = $(button).data('feature_code');
        params['target_type'] = $(button).data('target_type');
        params['target'] = $(button).data('target');
        params['href'] = href;
        //pprint_r(params);

        $.post( href, securePostVariables(params) ).done(function( responseHTML ) {
            if ( ! ValidateAjaxResponse(responseHTML, href) ) { return; }
            try{
                var result = JSON.parse(responseHTML);
            }catch(err){

            }

        }).fail(function( jqXHR, textStatus, errorThrown ) {

        });
    }

}
function EditCompanyParentFeatures(click_obj, e)
{
    var form = $("#edit_parent_form");
    hideForm(form, true, true);

    var button = $(click_obj);
    var url = $(button).data('href');

    if ( getStringValue(url) != "" )
    {
        location.href = url;
    }
}
function OptionalColumnClickHandler(click_obj, e)
{
    e.preventDefault();

    var url = $("#optional_columns").data("href");
    var checkbox = $(click_obj).find("input").first();
    var column = $(checkbox).attr("id");
    var params = {};
    if ( $(checkbox).is(":checked" ) )
    {
        // Enable the column.
        params.action = "add";
        params.column = column;
    }
    else
    {
        // Disable the column.
        params.action = "remove";
        params.column = column;
    }
    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        if ( ! ValidateAjaxResponse(responseHTML ) ) { return; }
        try{
            var result = JSON.parse(responseHTML);
            if ( result['type'] != "success" )
            {
                console.log("Unable to save column preference.  reason["+result['message']+"]");

                throw "Server says no.";
            }
        }catch(err){
            // On error, revert what the user did.
            if ( $(checkbox).is(":checked" ) )
            {
                $(checkbox).prop('checked', false);
            }
            else
            {
                $(checkbox).prop('checked', true);
            }

        }
    });

}
function AddFirstUserClickHandler(click_obj, e)
{
    e.preventDefault();

    var button = $(click_obj);
    var form = $(button).parent("form:first");
    var first_name = $(form).find("input[name='first_name']:first");
    var first_group = $(first_name).parent(".form-group:first");
    var last_name = $(form).find("input[name='last_name']:first");
    var last_group = $(last_name).parent(".form-group:first");
    var email_address = $(form).find("input[name='email_address']:first");
    var email_group = $(email_address).parent(".form-group:first");
    var onboarding = $(form).find("input[name='onboarding']:first");
    var onboarding_group = $(onboarding).parent().parent().parent();
    var help = $(form).find("#add_user_help");


    if ( $(first_group).hasClass("hidden") )
    {
        $(first_group).removeClass("hidden");
        $(last_group).removeClass("hidden");
        $(email_group).removeClass("hidden");
        $(onboarding_group).removeClass("hidden");
        $(button).html( $(button).data('closed-label') );
        $(help).removeClass("hidden");
    }
    else
    {
        // Hide all the form fields that have to do with
        // adding a use.
        $(first_group).addClass("hidden");
        $(last_group).addClass("hidden");
        $(email_group).addClass("hidden");
        $(onboarding_group).addClass("hidden");
        $(help).addClass("hidden");

        // Reset any data the user might have placed in them.
        $(first_name).val("");
        $(last_name).val("");
        $(email_address).val("");
        $(onboarding).prop("checked", true);

        // Update the button description.
        $(button).html( $(button).data('open-label') );
    }

}
function InitCompanyParentTable() {
    try{
        if ( ! $("#parents_table").hasClass("dataTable") )
        {
            $("#parents_table").DataTable(
                {
                    "bFilter": true,
                    "bInfo": true,
                    "bPaginate": true,
                    stateSave: true,
                    "initComplete": function(settings, json) {
                        //if ( ! $("#parents_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#parents_table").closest("div.card-box").removeClass("hidden");
                        //}
                    },
                    "language": {
                        "emptyTable":     "No parents found."
                    },
                    "iDisplayLength": 50,
                    "lengthMenu": [[5, 10, 50, -1], [5, 10, 50, "All"]]
                }
            );
        }

    }catch(err){}    
    $("#parents_table").hide();
    $("#parents_table").removeClass("hidden");
    $("#parents_table").show();


}
function EditCompanyParentClickHandler( click_obj, e)
{

    // Ignore if identifier_type is set and it's not company
    var button = $(click_obj);
    var identifier_type = $(button).data('identifier_type');
    if ( getStringValue(identifier_type) !== 'companyparent' ) return;

	e.preventDefault();
    ActionIconClickHandler( click_obj, "edit_parent_form", "edit_parent_widget");
}
function ActionIconClickHandler( click_obj, form_name, widget_name ) {

    // Pull the URL off the anchor that was clicked and set it on the widget.
    var url = $(click_obj).attr("href");
    $("#" + widget_name).attr("data-href", url);
    refreshWidget( widget_name, "showForm", form_name );

}
function EnableDisableCompanyParentClickHandler( click_obj, e) {

    // Ignore if identifier_type is set and it's not company
    var button = $(click_obj);
    var identifier_type = $(button).data('identifier_type');
    if ( getStringValue(identifier_type) !== 'companyparent' ) return;

    e.preventDefault();

    var url = $(click_obj).attr("href");

    var companyparent_id = getStringValue($(click_obj).data('company-parent-id'));
    if ( companyparent_id === '' ) companyparent_id = getStringValue($(click_obj).data('identifier'));

    var params = {};
    params.ajax = 1;
    params.company_parent_id = companyparent_id;
    params.url = url;

    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
        try {
            var result = JSON.parse(responseHTML);
            if ( result['type'] == "success" )
            {
                startWidget("parents_widget", 0);
                refreshWidget('recent_changeto');
            }

        }catch(err){
            AJAXPanic(responseHTML);
        }
    }).fail(function( jqXHR, textStatus, errorThrown ) {
        AJAXPanic(responseHTML);
    });
}
function ChangeToCompanyParentClickHandler( click_obj, e) {

    // Ignore if identifier_type is set and it's not company
    var button = $(click_obj);
    var identifier_type = $(button).data('identifier_type');
    if ( getStringValue(identifier_type) !== 'companyparent' ) return;

    e.preventDefault();
    ActionIconClickHandler( click_obj, "changeto_parent_form", "changeto_parent_widget");
}

function AddCompanyParentBeforeSubmit() {
    beforeFormPost("add_parent_form");
}
function AddCompanyParentSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "add_parent_form", responseText, true );
        startWidget("parents_widget", 0);
    }catch(err){
        AJAXPanic(responseText);
        return;
    }

}
function AddCompanyParentErrorHandler(response) {
    failedFormPost( response['responseText'], "add_parent_form" );
}

function EditCompanyParentBeforeSubmit() {
    beforeFormPost("edit_parent_form");
}
function EditCompanyParentSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "edit_parent_form", responseText, true );
        startWidget("parents_widget", 0);
    }catch(err){
        AJAXPanic(responseText);
        return;
    }

}
function EditCompanyParentErrorHandler(response) {
    failedFormPost( response['responseText'], "edit_parent_form" );
}

function ChangeToCompanyParentBeforeSubmit() {
    beforeFormPost("changeto_parent_form");
}
function ChangeToCompanyParentSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "changeto_parent_form", responseText, true );
        startWidget("parnets_widget", 0);
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function ChangeToCompanyParentErrorHandler(response) {
    failedFormPost( response['responseText'], "changeto_parent_form" );
}
