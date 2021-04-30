$(function(){

    // UIForm DROPDOWN - CHANGE HANDLER
    $(document).on('click', '.form-group .dropdown-menu li', function () {
        ActivateDropdown(this);
    });
    $(document).on('click', '.dropdown-inline .dropdown-menu li', function () {
        ActivateInlineDropdown(this);
    });
    $(document).on('change', '.uiform-dropdown-placeholder', function() {
        DropdownAutoFill(this);
    });
    // CHECKBOX TEXT - CLICK HANDLER
    $(document).on('click', '.uiform-checkbox-inline-desc', function() {
        ActivateCheckbox(this);
    });
    $(document).on('click', '.dropdown-toggle', function() {
        FiddleScrollbar(this);
    });

    // Confirm Button Click Handler
    $(document).on('change', '.dyno-confirm', function(e) {
        ConfirmButtonChangeHandler(this, e);
    });
    $(document).on('click', '.confirm-btn-enabled', function(e){
        ConfirmButtonClickHandler(this,e);
    });
    $(document).on('click', '.confirm-other-btn-enabled', function(e){
        ConfirmAltButtonClickHandler(this,e);
    });


    // Form InlineInput Button Click Hander
    $(document).on('click', '.btn-form-inline', function(e) {
        FormInlineButtonHandler(this,e);
    });
});

// FORM - INLINEINPUT
// An inline input is a read only text field with a button attached
// to the for right.  When that button is clicked, this handler will
// fire.  The button will make an ajax call to the href specified and
// then run the appropriate callback as needed to hook up any custom
// logic.
function FormInlineButtonHandler( click_obj, e )
{
    e.preventDefault();

    var url = $(click_obj).data('href');
    var callback = $(click_obj).data('callback');
    var failure_callback = $(click_obj).data('failure-callback');
    var input = $(click_obj).closest(".input-group").find("input:first");
    var input_id = $(input).attr("name");

    var params = {};
    params['ajax'] = 1;
    params['url'] = url;
    params['input_id'] = input_id;

    if ( url != "" )
    {
        $.post( url, securePostVariables(params) ).done(function( responseHTML ) {

            // Validate the ajax response.
            if ( ! ValidateAjaxResponse(responseHTML, url) ) {
                return;
            }

            try{

                var result = JSON.parse(responseHTML);
                var status = result['status'];
                if ( ! status ) throw "bad response from server.";

                executeFunctionByName(callback, window, input_id);

            }catch(err){
                executeFunctionByName(failure_callback, window, input_id);
                return;
            }


        }).fail(function( jqXHR, textStatus, errorThrown ) {
            executeFunctionByName(failure_callback, window, input_id);
        });
    }


}

// CHECKBOX BEHAVIORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
function ActivateCheckbox( click_obj ) {

    // Activate special UIForm checkbox behaviors.

    // Create inline text next to checkboxes clickable to make it
    // easier to hit the change target area.

    var checkbox = $(click_obj).prev();

    if ( $(checkbox).is(":disabled") ) return;

    if ( $(checkbox).is(":checked") ) {
        $(checkbox).prop("checked", false);
    }else{
        $(checkbox).prop("checked", true);
    }

}

// CONFIRM BUTTON BEHAVIORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
function ConfirmButtonInit() {
    $('[data-plugin="switchery"]').each(function (idx, obj) {
        if ( $(this).hasClass("confirm-btn-checkbox") )
        {
            // Only activate switchery on this checkbox if it's not already active.
            if ( getStringValue($(this).data("switchery")) == "" )
            {
                new Switchery($(this)[0], $(this).data());
            }

        }
    });
}
function ConfirmAltButtonClickHandler( click_obj, e )
{
    e.preventDefault();

    var button = $(click_obj);
    var callback = $(button).data('callback');
    if ( getStringValue(callback) !== '' )
    {
        executeFunctionByName(callback, window, button);
    }
}
function ConfirmButtonClickHandler( click_obj, e ) {


    var div = $(click_obj).closest(".confirm-btn");
    var url = $(div).data("href");
    var callback = $(div).data("callback");
    var callback_param = $(div).data("callback-parameter");
    var spinner = $(div).data("spinner");

    var params = {};
    params['ajax'] = 1;
    params['url'] = url;
    params['callback'] = callback;

    if ( spinner )
    {
        ShowSpinner();
    }
    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {

        // Validate the ajax response.
        if ( ! ValidateAjaxResponse(responseHTML, url) ) {
            return;
        }

        try{

            var result = JSON.parse(responseHTML);
            var responseText = getStringValue(result["responseText"]);


            var status = result['status'];
            var type = result['type'];
            var message = result['message'];

            if ( ! status ) throw "Invalid status";
            if ( type != "success" ) throw "did not work";

            if ( getStringValue(callback) != "" )
            {

                if ( getStringValue(callback_param) == "" )
                {
                    executeFunctionByName( getStringValue(callback), window );
                }
                else
                {
                    executeFunctionByName( getStringValue(callback), window, callback_param );
                }

            }
            HideSpinner();


        }catch(err){
            HideSpinner();
            return;
        }


    }).fail(function( jqXHR, textStatus, errorThrown ) {
        HideSpinner();
    });




}
function ConfirmButtonChangeHandler( click_obj ) {

    var confirm_switch = $(click_obj);
    var div = $(confirm_switch).closest(".confirm-btn");
    var enabled_button = $(div).find(".confirm-btn-enabled:first");
    var disabled_button = $(div).find(".confirm-btn-disabled:first");
    var checkbox = $(div).find(".dyno-confirm");

    var checked = false;
    if ($(checkbox).is(':checked') )
    {
        checked = true;
    }

    if ( checked )
    {
        $(enabled_button).removeClass("hidden");
        $(disabled_button).addClass("hidden");
        $(div).find(".confirm-other-btn-enabled").removeClass("hidden");
        $(div).find(".confirm-other-btn-disabled").addClass("hidden");

    }
    else
    {
        $(enabled_button).addClass("hidden");
        $(disabled_button).removeClass("hidden");
        $(div).find(".confirm-other-btn-enabled").addClass("hidden");
        $(div).find(".confirm-other-btn-disabled").removeClass("hidden");

    }
}


// DROPDOWN BEHAVIORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
function ActivateInlineDropdown( click_obj )
{
    var item = $(click_obj);
    var selected_value = $(item).attr("value");
    var selected_text = $(item).text();
    var button = $(item).closest('.dropdown').find('button:first');
    var input_name = $(button).data("dropdown-source");
    var input_value = $("input[name='"+input_name+"_selected_value']");
    var input_text = $("input[name='"+input_name+"_selected_text']");
    var callback = $(button).data('change-callback');

    var group_div = $(item).closest(".dropdown");
    var save_href = $(group_div).find("button:first").data("href");

    // Update the display of the dropdown.
    $(input_value).val(selected_value);
    $(input_text).val(selected_text);
    $(item).closest(".dropdown").find("button:first").find("span:first").text(selected_text);

    if ( getStringValue(callback) !== '' )
    {
        executeFunctionByName( callback, window, input_name, selected_value, selected_text );
    }

}
function ActivateDropdown( click_obj )
{
    // Enable our uiform dropdown.
    var item = $(click_obj);
    var selected_value = $(item).attr("id");

    var selected_text = $(item).text();
    var input_name = $(item).closest('.input-group-btn').find('button:first').data("dropdown-source");

    var input = $("input[name='"+input_name+"']");
    var input_disabled = $("input[name='"+input_name+"_disabled']");

    $(input).val(selected_value).trigger('change');
    $(input_disabled).val(selected_text);

    // If the dropdown has a change-callback defined, call that function
    // and pass it the name of the input that has changed.
    var callback = $(input).data("change-callback")
    if ( getStringValue(callback) != "" ) {
        executeFunctionByName( getStringValue(callback), window, $(input).attr("name") );
    }

}
function DropdownAutoFill( click_obj ) {
    // Form Fillers!  The readonly input that displays what the user selected
    // can get populated by form fillters.  This change handler will detect when
    // someone manages to populate that field and then try to match it up.  If
    // we can, we will build out the dropbox hidden values so it will post.
    // If we can't, we will clear what the form filler tried to set to indicate
    // that we didn't accept it.

    /*
    var input_disabled = $(click_obj);
    var user_input = getStringValue($(input_disabled).val());
    var input_name = $(input_disabled).next().find("button").first().data("dropdown-source");
    var input = $("input[name='"+input_name+"']");
    var dropdown = $(input_disabled).next().find("ul").first();

    var found = false;
    $(dropdown).find("li").each(function(){
        var option = $(this).attr("id");
        var value = $(this).attr("value");

        if ( user_input == value || user_input == option )
        {
            if ( ! found ) {
                $(input).val(option);
                found = true;
            }
        }

    });
    */
}
function FiddleScrollbar( click_obj ) {

    // FiddleScrollbar
    //
    // Some computers ( OSX ) hide the scrollbars until you scroll.  This
    // function will recognize that the dropdown that just activated was
    // a scrollable menu.  When activated it will scroll the menu 1 pixel
    // so that the scrollbar UI will fire and give the user the hint that
    // the menu is scrollable.
    // --------------------------------------------------------------------

    /*
    var menu = $(click_obj).next();
    if ( $(menu).hasClass("scrollable-menu") ||  $(menu).hasClass("scrollable-menu-lg") )
    {
        $(menu).scrollTop(1).scrollTop(0);
    }
    */
}

// FORM BEHAVIORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
function InitFormHeader() {

    $(".form-header-btn").each( function() {
        var form_name = $(this).data("assoc-form-name");
        var button_name = $(this).attr("id");
        if ( getStringValue(form_name) != "" )
        {
            var form = $("#" + form_name);
            if ( $(form).length != 0 )
            {
                if ( getStringValue($(this).data("handled")) != "yes")
                {
                    $(this).data("handled", "yes");
                    $(document).on('click', "#" + button_name, function (e) {
                        e.preventDefault();
                    	showForm( $(this).data("assoc-form-name") );
                    });
                }

            }
        }
    });
}
function form_reset(form) {

    // form_reset
    //
    // Remove the validation warning messages off of a form.
    // ------------------------------------------------------------------

    $(form).find(':input').each( function() {
        form_unhighlight(this);
    });
}
function form_highlight(element) {

    // form_highlight
    //
    // Draw the UI when a form element needs attention.
    // INFO: hooks for CodeIgniter Validation library
    // ------------------------------------------------------------------

    var group_container = $(element).parents('.form-group.has-feedback').first();
    $(group_container).addClass('has-error');
}
function form_unhighlight(element) {

    // form_unhighlight
    //
    // Clear the UI when a form element no longer needs attention.
    // INFO: hooks for CodeIgniter Validation library
    // ------------------------------------------------------------------

    // Remove the has-error class off the element group.
    var group_container = $(element).parents('.form-group.has-feedback').first();
    $(group_container).removeClass('has-error');

    // Empty and hide the error message.
    var msg_element = $(group_container).find('.help-block.text-error');
    $(msg_element).text("");
    $(msg_element).addClass("hidden");

	// Any inline form buttons need to have their error class removed as well.
	$(group_container).find(".input-group-btn button[type='button']").removeClass("btn-danger");

}
function form_error(error, element) {

    // form_error
    //
    // Place the error text
    // INFO: hooks for CodeIgniter Validation library
    // ------------------------------------------------------------------

    var group_container = $(element).parents('.form-group.has-feedback').first();
    var msg_element = $(group_container).find('.help-block.text-error');
    $(msg_element).text(error.text());
    $(msg_element).removeClass("hidden");

	// Any inline form buttons need to pick up the error class.
	$(group_container).find(".input-group-btn button[type='button']").addClass("btn-danger");


}
function form_lock(form) {

    // form_lock
    //
    // Modify the form so that data cannot be changed.
    // ------------------------------------------------------------------

    // NOTE: locking and unlocking messes with dropdowns.  Fix later.
    return;

	$(form).find(':input').each( function() {
		$(this).attr("readonly", true);
	});
	$(form).find("button").each( function() {
		$(this).attr("disabled", true);
	});
}
function form_unlock(form) {

    // form_unlock
    //
    // Restore the form so that it is editable.
    // ------------------------------------------------------------------

    // NOTE: locking and unlocking messes with dropdowns.  Fix later.
    return;

	$(form).find(':input').each( function() {
		$(this).attr("readonly", false);
	});
	$(form).find("button").each( function() {
		$(this).attr("disabled", false);
	});

}
function beforeFormPost( form_name ) {

    // beforeFormPost
    //
    // This function is called before the JQuery Form validation is about
    // to be executed on a form.  Do any of our common form validation logic
    // here.
    // INFO: hooks for CodeIgniter Validation library
    // ------------------------------------------------------------------

    if ( jQuery.type(form_name) != "string" ) { form_name = $(form_name).attr("id"); }
    var form = $("#" + form_name);

    if ( $(form).length == 0 ) { throw "beforeFormPost: Could not find an object named ["+getStringValue(form_name)+"]"; }

}
function failedFormPost( responseText, form_name ) {

    // failedFormPost
    //
    // This function is called on failure of the JQuery Form validation
    // on a form.  Do any of our common form validation logic here.
    // INFO: hooks for CodeIgniter Validation library
    // ------------------------------------------------------------------

    responseText = (typeof responseText === 'undefined') ? "" : responseText;
    if ( jQuery.type(form_name) != "string" ) { form_name = $(form_name).attr("id"); }

    var form = $("#" + form_name);
    if ( $(form).length == 0 ) {
        form_unlock( form );
    }

    AJAXPanic( responseText );

}
function successfulFormPost( form_name, responseText, hide_form_on_success, keep_form_data ) {

    // successfulFormPost
    //
    // This function is called on success of the JQuery Form validation
    // on a form.  Do any common logic to validate the reponse and handle
    // common behaviors for all forms.
    // INFO: hooks for CodeIgniter Validation library
    // ------------------------------------------------------------------

    responseText = (typeof responseText === 'undefined') ? "" : responseText;
    hide_form_on_success = (typeof hide_form_on_success === 'undefined') ? false : hide_form_on_success;
    keep_form_data = (typeof keep_form_data === 'undefind') ? false : keep_form_data;
    if ( jQuery.type(form_name) != "string" ) { form_name = $(form_name).attr("id"); }


    var form = $("#" + form_name);
    var form_wrapper    = $('#' + form_name+'_wrapper');

    if ( $(form).length == 0 ) { throw "successfulFormPost: Could not find an object named ["+getStringValue(form_name)+"]"; }
    if ( $(form_wrapper).length == 0 ) { throw "successfulFormPost: Could not find an object named ["+getStringValue(form_name)+"_wrapper]"; }

    try{
        // Look for runtime PHP errors and throw them out.
        if ( responseText.indexOf("A PHP Error was encountered") != -1 ) {
            throw responseText;
        }

        var result = JSON.parse(responseText);

        if ( result['status'] ) {

            // Hide the form on success.
            if ( hide_form_on_success && getStringValue(result['type']) == "success" ) {
                hideForm(form, !keep_form_data, false);
            }

        }

        // unlock the form.
        form_unlock(form);

        // Remove any old alerts and show the new one that came in on responseText.
        HideAlert();
        if ( getStringValue(result["message"]) != "" ) {

            var form_alert = undefined;
            // modal
            if ( $(form).data("form-type") == "modal" && form_alert == undefined ) {
                form_alert = $(".uiform-modal").find(".alert:first");
                ShowAlert(form_alert, result['type'], getStringValue(result["message"]));
            }

            // standard & simple
            if ( $(form).data("form-type") != "modal" && form_alert == undefined ) {
                //form_alert = $(form_wrapper).prev().show();   -- bah.  I replaced this line with the one below it. trying to get relationship errors to work.
                form_alert = $(form_wrapper).parent().find(".alert:first");
                ShowAlert(form_alert, result['type'], getStringValue(result["message"]));
            }
        }


    }catch(err){
        throw err;
    }

}
function isFormVisible( form_name ) {
    var wrapper = $("#" + form_name + "_wrapper");
    if ( $(wrapper).is(":visible") ) {
        return true;
    }
    return false;
}
function showForm( form_name, callback ) {

    // showForm
    //
    // UIForm function that will animate a form onto the screen if it
    // is collapsable and not currently visible.
    // ------------------------------------------------------------------



    if ( jQuery.type(form_name) != "string" ) { form_name = $(form_name).attr("id"); }
    if ( jQuery.type(callback) != "string" ) { callback = "" }

    var form = $("#" + form_name);
    var type = getStringValue( $(form).data("form-type") ).toUpperCase();

    switch ( type ) {
        case "STANDARD":
            slideFormIn(form_name);
            break;
        case "MODAL":
            Custombox.open({
                target: "#" + form_name + "_modal",
                speed: 300
            });
            $(form).find('.select2').each(function() {
                var placeholder = $(this).data('placeholder');
                if (getStringValue(placeholder) !== '')
                {
                    $(this).select2({placeholder: placeholder});
                }
                else
                {
                    $(this).select2();
                }

            });
            if ( callback != "" ) {
                setTimeout( function() { executeFunctionByName( callback, window ); }, 300 );
            }
        default:
            break;
    }
}
function slideFormIn( form_name ) {

    // slideFormIn
    //
    // Helper function that will slide a form onto the screen at it's
    // designated location.
    // -------------------------------------------------------------------
    var wrapper = $("#" + form_name + "_wrapper");
    if ( $(wrapper).length == 0 ) { throw "showForm: Could not find an object named ["+getStringValue(form_name)+"_wrapper]"; }

    HideAlert();

    var respawn = false;
    if ( $(wrapper).is(":visible") ) {
        respawn = true;
    }

    $('.collapsable', ':visible').each(function(){
        $(this).slideUp("fast");
    });

    $('.collapsable', ':visible').promise().done(function(){
        if (respawn == false) {
            $(wrapper).slideDown("fast");
        }
    });
}
function hideForm ( form_name, reset_form, clear_alerts ) {

    // hideForm
    //
    // UIForm function that will animate a form off the screen if it
    // is collapsable and is currently visible.
    // ------------------------------------------------------------------
    reset_form = (typeof reset_form === 'undefined') ? false : reset_form;
    clear_alerts = (typeof clear_alerts === 'undefined') ? true : clear_alerts;
    if ( jQuery.type(form_name) != "string" ) { form_name = $(form_name).attr("id"); }

    var form = $("#" + form_name);
    var wrapper    = $('#' + form_name+'_wrapper');

    if ( $(form).length == 0 ) { throw "hideForm: Could not find an object named ["+getStringValue(form_name)+"]"; }
    if ( $(wrapper).length == 0 ) { throw "hideForm: Could not find an object named ["+getStringValue(form_name)+"_wrapper]"; }

    if ( reset_form )
    {
        form_reset(form);
        $(form).clearForm();
    }
    if ( clear_alerts ) {
        HideAlert();
    }

    if ( $(form).data("form-type") == "standard" )
    {
        $('.collapsable', ':visible').each(function(){
            $(this).slideUp("fast");
    	});
    }
    else if( $(form).data("form-type") == "modal" )
    {
        // Close the modal form dialog
        Custombox.close();
    }




}
// DATA GRID BEHAVIORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
function InitDataGrid(table_name) {
    alert("hi");
    /*
    if ( jQuery.type(table_name) != "string" ) { table_name = $(table_name).attr("id"); }

    var table = $("#" + table_name);
    $(table).DataTable(
        {
            stateSave: true
        }
    );
    */


}
// ALERT BEHAVIORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
function HideAlert(alert){

	// Hide all alerts on the page if the alert object is
	// not specified.
	if ( alert === undefined ){
		$(".alert").each(function() {
			if (! $(this).hasClass("never-hide") ) {
				$(this).hide(0);
			}
		});
	}

	// Hide the alert specified.
	if ( alert !== undefined ){
		if (! $(alert).hasClass("never-hide") ) {
			$(alert).hide(0);
		}
	}
}
function ShowAlert(obj, type, message){

	$(obj).show();
	$(obj).removeClass("alert-success");
	$(obj).removeClass("alert-info");
    $(obj).removeClass("alert-warning");
	$(obj).removeClass("alert-danger");
    $(obj).addClass("alert-" + type);
	$(obj).find(".alert-message").each(function(){
		$(this).text(message);
	});
    $(obj).removeClass("hidden");
	$(obj).show();

}
function ActivateAlerts() {
    $(".alert-message").each(function(){
        var text = $(this).text();
        if ( text != "" ) {
            $(this).parent().removeClass("hidden");
        }
    });
}


// WIDGET BEHAVIORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
function startWidgets( task_name, delay_ms ) {

	// startWidgets
	//
	// Attempt to start all widgets on this page.  Widgets are identified by the 'widget' class.
	// ------------------------------------------------------------------------------------------

	// Input default values.
	if ( jQuery.type(task_name) === "undefined" ) { task_name = ""; }
	if ( jQuery.type(delay_ms) === "undefined" ) { delay_ms = 500; }

	// Exit if we don't have a task name.
	if ( task_name == "" ) return;

	var count = 0;
	$(".widget").each(function() {

		var obj_name = $(this).attr("id");

		// Only start widgets that match the background-task.
		if ( $(this).data("background-task") == task_name ){

			// Delay for the time specified and also adding a small variable offset for
			// each widget object found on the page so we can add a little breathing room between
			// calls to our server.
			var delay = delay_ms + ( count + 20 );

			startWidget( obj_name, delay );
			count++;

		}

	});

}
function startWidget( widget_name, delay_ms ) {

	// startWidget
	//
	// Refresh the HTML for the widget specified after the given millisecond delay.
	// ------------------------------------------------------------------------------------------

	// Input default values.
	if ( jQuery.type(widget_name) === "undefined" ) { widget_name = ""; }
	if ( jQuery.type(delay_ms) === "undefined" ) { delay_ms = 500; }

	// Bail if we don't have a widget name.
	if ( widget_name == "" ) return;

	// Call the widget after delay.
	var widget = $("#" + widget_name);
	var callback = $(widget).data("callback");
	setTimeout( function() { executeFunctionByName( "refreshWidget", window, widget_name, callback ); }, delay_ms );

}
function refreshWidget( widget_name, success_callback, callback_arg1 ) {

	// refreshWidget
	//
	// Call the server and get new HTML for the specified widget.  The response from
	// the server will contain not only the new HTML, but also information on when
	// we should next refresh.  If anything goes south, the widget will stop refreshing
	// and will pick back up the next time the user refreshes the page.
	// ---------------------------------------------------------------------------------

    var info = false;
	var debug = false;


	// Default Values
	if ( jQuery.type(widget_name) === "undefined" ) { widget_name = ""; }
	if ( jQuery.type(success_callback) === "undefined" ) { success_callback = ""; }
    if ( jQuery.type(callback_arg1) === "undefined" ) { callback_arg1 = ""; }

	// OBJECT
	var widget = $("#" + widget_name);
	if ( ! $( widget ).length ) {
		console.log("widget: Cannot find widget ["+widget_name+"]. ALL STOP.");
		return;
	}

	if ( info ) { console.log( "[" + widget_name + "] widget is running."); }

	// WIDGET CLASS
	if ( ! $(widget).hasClass("widget") ) {
		if ( debug ) { console.log("widget ["+widget_name+"]: Does not have the widget class indicating this object is a widget. ALL STOP."); }
		return;
	}

	// WRAPPER
	var wrapper = $("#" + widget_name + "_widget_wrapper");
	if ( ! $( widget ).length ) {
		if ( debug ) { console.log("widget ["+widget_name+"]: Malformed object.  Missing wrapper. ALL STOP."); }
		return;
	}

	// URL
	var	url = $(widget).attr("data-href");
	if ( getStringValue(url) == "" ) {
		if ( debug ) { console.log("widget ["+widget_name+"]: No URL. ALL STOP."); }
		return;
	}
    if ( info ) { console.log( "[" + widget_name + "] url is ["+url+"]"); }

	// WORKING
	var	working = $(widget).attr("data-working");
	if ( getStringValue(working) == "true" ) {
		if ( debug ) { console.log("widget ["+widget_name+"]: Already running. Skipping this refresh cycle."); }
		return;
	}

    // Ensure the ajax flag is set to true.
	var params = {};
    params.ajax = 1;
    params.url = url;
    params.uri = $(widget).attr('data-uri');

	// CALL SERVER
	$(widget).data("working", "true");

    // Check to see if we have a starting function we should call before we
    // kick things off.
    var starting = getStringValue( $(widget).data("starting") );
    if ( starting != "" )
    {
        executeFunctionByName( starting, window );
    }

    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {

		if ( debug ) { console.log("widget ["+widget_name+"]: Server has responded with a widget payload."); }

        // Validate the ajax response.
        if ( ! ValidateAjaxResponse(responseHTML, url) ) {
			if ( debug ) { console.log("widget ["+widget_name+"]: Got a template page back from the server. ALL STOP."); }
			return;
		}

		try{

			var result = JSON.parse(responseHTML);
			var responseText = getStringValue(result["responseText"]);

			// Fail on bad status.
            var status = result['status'];
            if ( ! status ) throw "Servers status was false.";

            // Fail on danger in a way that helps me figure out what happened.
            var type = result['type'];
            if ( type === 'danger' ) throw "Server reported danger.  " + result['message'];


			if ( debug ) {
				if ( responseText == "") { console.log("widget ["+widget_name+"]: responseText is empty."); }
				if ( responseText != "") { console.log("widget ["+widget_name+"]: responseText is not empty."); }
			}
			//if ( debug ) { console.log(responseText); }

            if ( responseText.indexOf("SHUTDOWN") != -1 )
            {
                // Respond with the empty string so nothing shows and
                // leave it in a "working" state so it no longer rungs.
                $(wrapper).empty();
                $(wrapper).append("");
                //$(widget).data("working", "");
                executeFunctionByName( success_callback, window, callback_arg1 );
            }
			else if ( responseText != "" )
            {

				// Replace the widget with the new HTML
				if ( debug ) { console.log("widget ["+widget_name+"]: Updating the widget with responseText from server."); }

				// Remove everything inside the content wrapper.
				$(wrapper).empty();

				// Place our new content in the DOM in the same location as where we
				// removed the old content.
				$(wrapper).append(responseText);

				// Turn off our working indicator so we can refresh when next asked.
				$(widget).data("working", "");

				// Execute the success_callback function if we have one.
				//executeFunctionByName( success_callback, window, args );
                executeFunctionByName( success_callback, window, callback_arg1 );

			}

		}catch(err){
			// Shutdown the widget.
			if ( debug ) { console.log("widget ["+widget_name+"]: Widget failed to refresh due to error ["+err+"]."); }
			return;
		}


    }).fail(function( jqXHR, textStatus, errorThrown ) {
		if ( debug ) { console.log("widget ["+widget_name+"]: Server call failed. textStatus["+textStatus+"] errorThrown["+errorThrown+"]. ALL STOP."); }
    });
}
function replaceTagsWidget( widget_name, collection )
{

    // Find the widget specified.
    var widget = $("#"+widget_name);
    if ( ! $(widget).length ) return;

    // If we don't have a template on the widget, copy the href
    // attribute to the template attribute.
    var template = $(widget).attr('data-template');
    if ( getStringValue(template) === '' )
    {
        var href = $(widget).data('href');
        $(widget).attr('data-template', href);
    }

    // Take the template value on the widget and do a replaceFor
    // against it, replacing the "tags" with the replaceFor values.
    template = $(widget).attr('data-template');
    var tags = Object.keys(collection);

    var str = template;
    for(var i=0, len=tags.length; i < len; i++)
    {
        var key = tags[i];
        var value = collection[key];
        str = replaceFor(str, key, value);
    }

    // Update the href on the widget with the copy of the template
    // we have ran the replace for against.
    $(widget).attr('data-href', str);

}

// BACKGROUND TASK BEHAVIORS +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
function startBackgroundTasks( delay_ms ) {

	// startBackgroundTasks
	//
	// Attempt to start a background task for any objects that have the background-task class.
	// ------------------------------------------------------------------------------------------

	if ( jQuery.type(delay_ms) === "undefined" ) { delay_ms = 500; }

	var count = 0;
	$(".background-task").each(function() {

		var task_name = $(this).attr("id");

		// Delay for the time specified and also adding a small variable offset for
		// each live object found on the page so we can add a little breathing room between
		// calls to our server.
		var delay = delay_ms + ( count + 20 );

		startBackgroundTask( task_name, delay );
		count++;

	});
}
function startBackgroundTask( task_name, delay_ms ) {

	// startBackgroundServerTask
	//
	// Attempt to start a single background-task for a given object by name after the specified
	// delay.
	// ------------------------------------------------------------------------------------------

	if ( jQuery.type(task_name) === "undefined" ) { task_name = ""; }
	if ( jQuery.type(delay_ms) === "undefined" ) { delay_ms = 500; }

	setTimeout( function() { executeFunctionByName( "backgroundTask", window, task_name ); }, delay_ms );

}
function backgroundTask( task_name ) {

	// backgroundTask
	//
	// Check for the object passed in.  If we can identify all the information needed on that object,
	// a background call to the server will start happening over and over on a timed basis.
	// Any trouble, the task will stop calling the server until the next time the user reloads
	// the page.
	// ------------------------------------------------------------------------------------------

	// Default Values
	if ( jQuery.type(task_name) === "undefined" ) { task_name = ""; }

	// OBJECT
	var task = $("#" + task_name);
	if ( ! $( task ).length ) {
		console.log("bakground-task ["+task_name+"]: Cannot find object. ALL STOP.");
		return;
	}

	// INFO
	var info = false;
	if ( getStringValue($(task).data("info")) == "1" ) {
		info = true;
	}

	// DEBUG
	var debug = false;
	if ( getStringValue($(task).data("debug")) == "1" ) {
		debug = true;
	}


	if ( info ) { console.log( "[" + task_name + "] background-task is running."); }

	// URL
	var	url = $(task).data("href");
	if ( getStringValue(url) == "" ) {
		if ( debug ) { console.log("bakground-task ["+task_name+"]: No URL. Live refresh terminating. ALL STOP."); }
		return;
	}

	// All tasks need to talk to the widget task controller for security reasons.
	// If it looks like we are not doing that, STOP!
	if ( url.toUpperCase().indexOf("WIDGETTASK") < 0 ) {
		if ( debug ) { console.log("background-task ["+task_name+"]: URL does not talk to widgettask controller. ALL STOP."); }
		return;
	}



	// MINUTES
	var refresh_minutes = $(task).attr("data-refresh-minutes");
	if ( getStringValue(refresh_minutes) == "" ) {
		if ( debug ) { console.log("bakground-task ["+task_name+"]: No refresh_minutes.  Live refresh terminating. ALL STOP."); }
		return;
	}else{
		if ( ! debug && ! info ) {
			if ( parseInt(refresh_minutes) < 5 ) {
				refresh_minutes = 5;
				$(task).attr("data-refresh-minutes", refresh_minutes);
			}
		}
	}

	// SPINNER
    // Do not run the background task if we have a spinner showing to the end user.
	if ( IsSpinnerVisible() )
    {
        if ( debug ) { console.log("bakground-task ["+task_name+"]: spinner is running. WAITING."); }
        startBackgroundTask( task_name, ( 1000 * 60 * refresh_minutes) );
        return;
    }


	// WORKING
	var	working = $(task).attr("data-working");
	if ( getStringValue(working) == "true" ) {
		if ( debug ) { console.log("bakground-task ["+task_name+"]: Already running. WAITING."); }
		startBackgroundTask( task_name, ( 1000 * 60 * refresh_minutes) );
		return;
	}

    // Ensure the ajax flag is set to true.
	var params = {};
    params.ajax = 1;

	// CALL SERVER
	$(task).data("working", "true");
    $.get( url, params ).done(function( responseHTML ) {

        // Validate the ajax response.
        if ( ! ValidateAjaxResponse(responseHTML, url) ) {
			if ( debug ) { console.log("bakground-task ["+task_name+"]: Got a template page back from the server. ALL STOP."); }
			return;
		}

		// Parse the AJAX Repsonse.
		try{
			if ( debug ) { console.log("bakground-task ["+task_name+"]: Got a response back from the server."); }

			var result = JSON.parse(responseHTML);
			var refresh_minutes = result["refresh_minutes"];
			var refresh_enabled = result["refresh_enabled"];
			var debug_flg = result["debug"];
			var info_flg = result["info"];

			if ( debug ) { console.log("background-task ["+task_name+"]: refresh_minutes["+refresh_minutes+"]."); }
			if ( debug ) { console.log("background-task ["+task_name+"]: refresh_enabled["+refresh_enabled+"]."); }

			// Update our debugging and info data points so we are sure to turn things on and off each iteration.
			$(task).data("debug", debug_flg);
			$(task).data("info", info_flg);

            var refresh_message = "";
			if ( refresh_message == "" && getStringValue(refresh_enabled) != "t") refresh_message = "refresh flg is not on"; // server says stop refreshing.
			if ( refresh_message == "" && getStringValue(refresh_minutes) == "" ) refresh_message =  "no refresh rate."; // no refresh rate
			if ( refresh_message == "" && getStringValue(parseInt(refresh_minutes)) != getStringValue(refresh_minutes) ) refresh_message =  "malformed refresh minutes";  // malformed refresh rate.

			// Reset the background job so it will refresh the data again.
            // Only do that if we do not have a warning refresh_message.
            if ( refresh_message == "" )
            {
                if ( debug ) { console.log("bakground-task ["+task_name+"]: Starting again in ["+( 1000 * 60 * refresh_minutes)+"]ms, ["+(refresh_minutes * 60)+"]s, ["+refresh_minutes+"]m."); }
    			$(task).attr("data-working", "");
    			startBackgroundTask( task_name, ( 1000 * 60 * refresh_minutes) );
            }

			// Tell all widgets associated with this task to redraw.
			startWidgets( task_name );

            // Okay, if we have a refresh_message then the task will not refresh.
            // throw an error here even though we are done so that we get the warning
            // message in the console that the task will not refresh.
            if ( refresh_message != "" ) throw refresh_message;

		}catch(err){
			// Shutdown the task.
			if ( debug ) { console.log("bakground-task ["+task_name+"]: Task will no longer refresh because ["+err+"]."); }
			return;
		}


    }).fail(function( jqXHR, textStatus, errorThrown ) {

		if ( debug ) { console.log("bakground-task ["+task_name+"]: Got a failure response back from the server. ALL STOP."); }
        if ( debug ) { console.log(jqXHR.responseText); }

    });
}

function backgroundTaskUpdateNotificationHandler( task_name, data )
{
    // backgroundTaskUpdateNotificationHandler
    //
    // This function handles an update notification from a background task.
    //
    // The input task_name is the name of the background task the update is
    // executing against.
    //
    // The data input is a key/value pair array that contains two values.
    //  -js_function: This is the JS function we will execute.
    //  -js_data: This is the json_encoded data we will pass to the above function as argument 1.
    // ------------------------------------------------------------------------------------------
    try
    {
        if ( jQuery.type(debug_event) === "undefined" ) { debug_event = false; }
        if ( debug_event ) console.log("EVENT: " . task_name );

        var js_function = data['js_function'];
        var js_data = data['js_data'];

        if ( getStringValue(js_function) !== '' )
        {
            executeFunctionByName(js_function, window, js_data);
        }
    }catch(err)
    {
    }
}

/**
 * backgroundTaskNotificationHandler
 *
 * This function will look for a background task called 'task_name'
 * that has been defined on the page.  If we find it, we will start
 * any widgets associated with it.
 *
 * @param task_name
 * @param data
 */
function backgroundTaskNotificationHandler(task_name, data)
{
    if ( jQuery.type(debug_event) === "undefined" ) { debug_event = false; }
    if ( debug_event ) console.log("EVENT: " . task_name );

    startWidgets(task_name);
}

// securePostVariables
//
// This function will add additional parameters to your POST
// data.  If you are making and AJAX POST call, you will be
// denied with a 403 if you do not add additional secure values to your post data.
// When called with no parameter, POST data will still be
// generated which is required for your call to be authorized by the
// server.
// ---------------------------------------------------------------
function securePostVariables( params ) {

    if ( jQuery.type(params) != "object" ) { params = {}; }

    // AJAX Flag
    // Set the 'ajax' variable to indicate this call is an AJAX reqeust.
    if ( getStringValue(params['ajax']) == "" )
    {
        params['ajax'] = 1;
    }

    var csrf_cookie_name = $("html").attr("csrf-cookie-name");
    var csrf_token_name = $("html").attr("csrf-token-name");
    var csrf_token = $.cookie(csrf_cookie_name);
    params[csrf_token_name] = csrf_token;

    return params;

}