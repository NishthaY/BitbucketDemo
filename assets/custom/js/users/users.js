$(function(){

    InitUsersTable();
    InitFormHeader();

    // Click Handler ( Edit Action Icons )
    $(document).on('click', 'table td.action-cell a.action-cell-edit', function(e) {
		EditUserClickHandler(this, e);
    });

    // Click Handler ( Remove Action Icons )
    $(document).on('click', 'table td.action-cell a.action-cell-remove', function(e) {
		EnableDisableUserClickHandler(this, e);
    });

    // Click Handler ( Delete Action Icons )
    $(document).on('click', 'table td.action-cell a.action-cell-delete', function(e) {
		DeleteUserClickHandler(this, e);
    });

    // Click Handler ( Add Delete Cancel Button Handler )
    $(document).on('click', '#delete_user_form #cancel_edit_btn', function(e) {
        var form = $("#delete_user_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Delete User Submit Button Handler )
    $(document).on('click', '#delete_user_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: DeleteUserBeforeSubmit,
            success: DeleteUserSuccessHandler,
            error: DeleteUserErrorHandler,
            data: {ajax: '1'}
        };
        $('#delete_user_form').ajaxForm(options);

        // Define the form validation.
        var delete_user_form_validator = undefined;

        if( $("#delete_user_form").length ) {
            add_user_form_validator = $("#delete_user_form").validate({
                rules: {
                    user_id: "required"
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#delete_user_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });





    // Click Handler ( Add User Cancel Button Handler )
    $(document).on('click', '#add_user_form #cancel_add_btn', function(e) {
        var form = $("#add_user_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Add User Submit Button Handler )
    $(document).on('click', '#add_user_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: AddUserBeforeSubmit,
            success: AddUserSuccessHandler,
            error: AddUserErrorHandler,
            data: {ajax: '1'}
        };
        $('#add_user_form').ajaxForm(options);

        // Define the form validation.
        var add_user_form_validator = undefined;

        if( $("#add_user_form").length ) {
            add_user_form_validator = $("#add_user_form").validate({
                rules: {
                    email_address: {
                        required: true,
                        usernameValidator: true
                    },
                    first_name: "required",
                    last_name: "required"
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
            jQuery.validator.addMethod('usernameValidator', usernameValidator, 'User with that email address already exists.');
        }

        // Validate the form.
        if ( $("#add_user_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });

    // Click Handler ( Edit User Cancel Button Handler )
    $(document).on('click', '#edit_user_form #cancel_edit_btn', function(e) {
        var form = $("#edit_user_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Edit User Submit Button Handler )
    $(document).on('click', '#edit_user_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: EditUserBeforeSubmit,
            success: EditUserSuccessHandler,
            error: EditUserErrorHandler,
            data: {ajax: '1'}
        };
        $('#edit_user_form').ajaxForm(options);

        // Define the form validation.
        var edit_user_form_validator = undefined;

        if( $("#edit_user_form").length ) {
            edit_user_form_validator = $("#edit_user_form").validate({
                rules: {
                    email_address: {
                        required: true,
                        usernameChangeValidator: true
                    },
                    first_name: "required",
                    last_name: "required",

                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
            jQuery.validator.addMethod('usernameChangeValidator', usernameChangeValidator, 'User with that email address already exists.');
        }

        // Validate the form.
        if ( $("#edit_user_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });


});

function InitUsersTable() {

    try{
        if ( ! $("#user_datatable").hasClass("dataTable") )
        {
            $("#user_datatable").DataTable(
                {
                    "bFilter": true,
                    "bInfo": true,
                    "bPaginate": true,
                    stateSave: true,
                    "initComplete": function(settings, json) {
                            $("#user_datatable").closest("div.card-box").removeClass("hidden");
                    },
                    "language": {
                        "emptyTable":     "No users found."
                    },
                    "iDisplayLength": 50,
                    "lengthMenu": [[5, 10, 50, -1], [5, 10, 50, "All"]]
                }
            );
        }

    }catch(err){
    }
    /*
    try{
        $("#user_datatable").DataTable(
            {
                "order": [[ 0, "asc" ]]
                , stateSave: true
            }
        );
    }catch(err){}
    */
    $("#user_datatable").hide();
    $("#user_datatable").removeClass("hidden");
    $("#user_datatable").show();

}

function DeleteUserBeforeSubmit() {
    beforeFormPost("delete_user_form");
}
function DeleteUserSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "delete_user_form", responseText, true );
        startWidget("users_widget", 0);
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function DeleteUserErrorHandler(response) {
    failedFormPost( response['responseText'], "delete_user_form" );
}

function AddUserBeforeSubmit() {
    beforeFormPost("add_user_form");
}
function AddUserSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "add_user_form", responseText, true );
        startWidget("users_widget", 0);
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function AddUserErrorHandler(response) {
    failedFormPost( response['responseText'], "add_user_form" );
}

function EditUserBeforeSubmit() {
    beforeFormPost("edit_user_form");
}
function EditUserSuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "edit_user_form", responseText, true );
        startWidget("users_widget", 0);
        startWidget("whoami_widget", 0);
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function EditUserErrorHandler(response) {
    failedFormPost( response['responseText'], "edit_user_form" );
}

function DeleteUserClickHandler( click_obj, e ) {
    e.preventDefault();
    ActionIconClickHandler( click_obj, "delete_user_form", "delete_user_widget");
}
function EditUserClickHandler( click_obj, e) {
	e.preventDefault();
    ActionIconClickHandler( click_obj, "edit_user_form", "edit_user_widget");
}
function EnableDisableUserClickHandler( click_obj, e) {

    e.preventDefault();

    var url = $(click_obj).attr("href");

    var params = {};
    params.ajax = 1;
    params.user_id = $(click_obj).data('user-id');
    params.url = url;

    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
        try {
            var result = JSON.parse(responseHTML);
            if ( result['type'] == "success" )
            {
                startWidget("users_widget", 0);
            }

        }catch(err){
            AJAXPanic(responseHTML);
        }
    }).fail(function( jqXHR, textStatus, errorThrown ) {
        AJAXPanic(responseHTML);
    });
}
function usernameChangeValidator(value) {

    var form = $("#edit_user_form");
    var input = $(form).find("#original_email_address");


    // If the current email address matches the original address
    // allow it.
    if ( value == getStringValue($(input).val()) ) return true;
    return usernameValidator(value);
}
function UserPhoneResetSuccess( form_input_id )
{
    // We have successfully cleared the phone number.
    // Clear this value on the input.
    var input = $("input[name='"+form_input_id+"']");
    $(input).val("");
}
function UserPhoneResetFailed( form_input_id )
{
    // Nothing to do here.  We could not clear the phone number.
}