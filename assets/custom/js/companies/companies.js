$(function(){

    // Initilize any confirm buttons on the page.
    ConfirmButtonInit();


    $(document).on('click', '#edit_company_form #edit_company_features_btn', function(e) {
        EditCompanyFeatures(this, e);
    });

    // Click Handler ( Edit Action Icons )
    $(document).on('click', '#add_company_form #user_add_btn', function(e) {
        AddFirstUserClickHandler(this, e);
    });


    // Click Handler ( Edit Action Icons )
    $(document).on('click', 'table td.action-cell a.action-cell-edit', function(e) {
		EditCompanyClickHandler(this, e);
    });

    // Click Handler ( Delete Action Icons )
    $(document).on('click', 'table td.action-cell a.action-cell-remove', function(e) {
		EnableDisableCompanyClickHandler(this, e);
    });

    // Click Handler ( Change To Action Icons )
    $(document).on('click', 'table td.action-cell a.action-cell-changeto', function(e) {
        ChangeToCompanyClickHandler(this, e);
    });

    // Click Handler ( Change To Action Icons )
    $(document).on('click', 'table td.action-cell a.action-cell-reportreview', function(e) {
        ReportReviewClickHandler(this, e);
    });

    // Click Handler ( Change To Company No Button Handler )
    $(document).on('click', '#changeto_company_form #no_btn', function(e) {
        var form = $("#changeto_company_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Change To Company Yes Button Handler )
    $(document).on('click', '#changeto_company_form #yes_btn', function(e) {
        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: ChangeToCompanyBeforeSubmit,
            success: ChangeToCompanySuccessHandler,
            error: ChangeToCompanyErrorHandler,
            data: {ajax: '1'}
        };
        $('#changeto_company_form').ajaxForm(options);

        // Define the form validation.
        var changeto_company_form_validator = undefined;

        if( $("#changeto_company_form").length ) {
            changeto_company_form_validator = $("#changeto_company_form").validate({
                rules: { },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
        }

        // Validate the form.
        if ( $("#changeto_company_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.
    });

    // Click Handler ( Add Company Cancel Button Handler )
    $(document).on('click', '#add_company_form #cancel_add_btn', function(e) {
        var form = $("#add_company_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Add Company Submit Button Handler )
    $(document).on('click', '#add_company_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: AddCompanyBeforeSubmit,
            success: AddCompanySuccessHandler,
            error: AddCompanyErrorHandler,
            data: {ajax: '1'}
        };
        $('#add_company_form').ajaxForm(options);

        // Define the form validation.
        var add_company_form_validator = undefined;

        if( $("#add_company_form").length ) {
            add_company_form_validator = $("#add_company_form").validate({
                rules: {
                    company_name: {
                        required: true,
                        companynameValidator: true,
                        companyparentnameValidator: true
					},
                    company_address: "required",
                    company_city: "required",
                    company_state: "required",
                    company_postal: {
                        required: true,
                        zipcodeUS: true
                    },
                    first_name: "required",
                    last_name: "required",
                    email_address: {
                        required: true,
                        usernameValidator: true
                    }
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
            jQuery.validator.addMethod('usernameValidator', usernameValidator, 'User with that email address already exists.');
            jQuery.validator.addMethod('companynameValidator', companynameValidator, 'Business with that name already in use.');
            jQuery.validator.addMethod('companyparentnameValidator', companyparentnameValidator, 'Business with that name already in use.');
        }

        // Validate the form.
        if ( $("#add_company_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });

    // Click Handler ( Edit Company Cancel Button Handler )
    $(document).on('click', '#edit_company_form #cancel_edit_btn', function(e) {
        var form = $("#edit_company_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Edit Company Submit Button Handler )
    $(document).on('click', '#edit_company_form button[type="submit"]', function(e) {

        // Create the AJAX hooks for submitting this form.
        var options = {
            beforeSubmit: EditCompanyBeforeSubmit,
            success: EditCompanySuccessHandler,
            error: EditCompanyErrorHandler,
            data: {ajax: '1'}
        };
        $('#edit_company_form').ajaxForm(options);

        // Define the form validation.
        var edit_company_form_validator = undefined;

        if( $("#edit_company_form").length ) {
            edit_company_form_validator = $("#edit_company_form").validate({
                rules: {
                    company_name: {
                        required: true,
                        companynameValidator: true,
                        companyparentnameValidator: true
					},
                    company_address: "required",
                    company_city: "required",
                    company_state: "required",
                    company_postal: {
                        required: true,
                        zipcodeUS: true
                    }
                },
                messages: { },
                ignore: ":hidden:not(.uiform-dropdown)",
                highlight: function(element, errorClass, validClass) { form_highlight(element); },                // draws UI on validation error
                unhighlight: function(element, errorClass, validClass) { form_unhighlight(element); },            // draws UI on validation success
                errorPlacement: function(error, element) { form_error(error, element); }                          // adds validation error message to screen.
            });
            jQuery.validator.addMethod('companynameValidator', companynameValidator, 'Business with that name already in use.');
            jQuery.validator.addMethod('companyparentnameValidator', companyparentnameValidator, 'Business with that name already in use.');
        }

        // Validate the form.
        if ( $("#edit_company_form").validate().form() ) {
            return true;
        }
        return false; // Form not valid.

    });



    InitCompanyTable();
    InitFormHeader();


});
function RefreshCompanyFeatures( widget_name )
{
    refreshWidget(widget_name, "ConfirmButtonInit");
}
function RemoveCompanyFeature( click_obj )
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
function EditCompanyFeatures(click_obj, e)
{
    var form = $("#edit_company_form");
    hideForm(form, true, true);

    var button = $(click_obj);
    var url = $(button).data('href');

    if ( getStringValue(url) != "" )
    {
        location.href = url;
    }
}

function AddCompanyBeforeSubmit() {
    beforeFormPost("add_company_form");
}
function AddCompanySuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "add_company_form", responseText, true );
        startWidget("companies_widget", 0);

        // MULTI COMPANY WIDGET
        // If we have a multi company widget on the screen, refresh it.
        if ( $("#multi_company_widget").length) refreshWidget("multi_company_widget", 'MultiCompanyWidgetRefreshed');
        
    }catch(err){
        AJAXPanic(responseText);
        return;
    }

}
function AddCompanyErrorHandler(response) {
    failedFormPost( response['responseText'], "add_company_form" );
}

function EditCompanyBeforeSubmit() {
    beforeFormPost("edit_company_form");
}
function EditCompanySuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "edit_company_form", responseText, true );
        if ( $("#multi_company_widget").length ) { refreshWidget("multi_company_widget", "MultiCompanyWidgetRefreshed"); }
        if ( $("#companies_widget").length ) { startWidget("companies_widget", 0); }
        if ( $("#whoami_widget").length ) { startWidget("whoami_widget", 0); }
    }catch(err){
        AJAXPanic(responseText);
        return;
    }

}
function EditCompanyErrorHandler(response) {
    failedFormPost( response['responseText'], "edit_company_form" );
}

function ChangeToCompanyBeforeSubmit() {
    beforeFormPost("changeto_company_form");
}
function ChangeToCompanySuccessHandler(responseText, statusText, xhr, form) {
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "changeto_company_form", responseText, true );
        startWidget("companies_widget", 0);
    }catch(err){
        AJAXPanic(responseText);
        return;
    }
}
function ChangeToCompanyErrorHandler(response) {
    failedFormPost( response['responseText'], "changeto_company_form" );
}

function InitCompanyTable() {

    try{
        if ( ! $("#company_table").hasClass("dataTable") )
        {
            $("#company_table").DataTable(
                {
                    "bFilter": true,
                    "bInfo": true,
                    "bPaginate": true,
                    stateSave: true,
                    "initComplete": function(settings, json) {
                        //if ( ! $("#company_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#company_table").closest("div.card-box").removeClass("hidden");
                        //}
                    },
                    "language": {
                        "emptyTable":     "No companies found."
                    },
                    "iDisplayLength": 50,
                    "lengthMenu": [[5, 10, 50, -1], [5, 10, 50, "All"]]
                }
            );
        }

    }catch(err){}


    try{
        $("#example").DataTable(
            {
                "order": [[ 0, "asc" ]]
                , stateSave: true
            }
        );
    }catch(err){}

    $("#example").hide();
    $("#example").removeClass("hidden");
    $("#example").show();

}
function EditCompanyClickHandler( click_obj, e) {
    // Ignore if identifier_type is set and it's not company
    var button = $(click_obj);
    var identifier_type = $(button).data('identifier_type');
    if ( getStringValue(identifier_type) !== 'company' ) return;

	e.preventDefault();
    ActionIconClickHandler( click_obj, "edit_company_form", "edit_company_widget");
}
function EnableDisableCompanyClickHandler( click_obj, e) {

    // Ignore if identifier_type is set and it's not company
    var button = $(click_obj);
    var identifier_type = $(button).data('identifier_type');
    if ( getStringValue(identifier_type) !== 'company' ) return;

    var company_id = getStringValue($(button).data('identifier'));
    if ( company_id === '' )
    {
        company_id = getStringValue($(button).data('company-id'));
    }

    e.preventDefault();

    var url = $(click_obj).attr("href");

    var params = {};
    params.ajax = 1;
    params.company_id = company_id;
    params.url = url;

    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
        try {
            var result = JSON.parse(responseHTML);
            if ( result['type'] == "success" )
            {
                if ( $("#multi_company_widget").length ) { refreshWidget("multi_company_widget", "MultiCompanyWidgetRefreshed"); }
                if ( $("#companies_widget").length ) { startWidget("companies_widget", 0); }
                if ( $("#recent_changeto").length ) { startWidget("recent_changeto", 0); }
            }
            if ( result['type'] == "danger" ){
                var message = result['message'];
                var alert_obj = $("div.container").find(".alert:first");
                ShowAlert(alert_obj, "danger", message);
                return;
            }

        }catch(err){
            AJAXPanic(responseHTML);
        }
    }).fail(function( jqXHR, textStatus, errorThrown ) {
        AJAXPanic(responseHTML);
    });
}
function ChangeToCompanyClickHandler( click_obj, e) {

    // Ignore if identifier_type is set and it's not company
    var button = $(click_obj);
    var identifier_type = $(button).data('identifier_type');
    if ( getStringValue(identifier_type) !== 'company' ) return;

	e.preventDefault();
    ActionIconClickHandler( click_obj, "changeto_company_form", "changeto_company_widget");
}

function ReportReviewClickHandler(click_obj, e)
{
    ListDownloadableReports(click_obj, e);
}
