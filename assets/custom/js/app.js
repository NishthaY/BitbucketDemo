$(function(){

	// Start any forever loading buttons.
	InitLoadingButtons();

    // ALERT MESSAGES
	// Hide alert messages if there is no text on them when the page loads.
	$('.alert').each(function(){

		// Always hide alerts, unless you are on specific pages.
		var alert_container = $(this);
		$(alert_container).find(".alert-message").each(function(){
			if ( $(this).text() == "" ){
				HideAlert($(alert_container));
			}
			if ( $(this).hasClass("hidden") ){
				$(this).removeClass("hidden");
			}
		});
	});


	DeveloperTools();

	WorkflowWidgetsInit();
});

function EnterpriseBannerInit()
{
	var banner = $('div.enterprise-banner');
	if ( $(banner).hasClass('hidden') )
	{
		EnterpriseBannerHide();
	}
	else
	{
		EnterpriseBannerShow();
	}
}
function EnterpriseBannerHide()
{
	var banner = $('div.enterprise-banner');
	var url = $(banner).data('href');

	if ( ! $(banner).hasClass('hidden') )
	{
		$(banner).addClass('hidden');

		// Save to the session that the banner is hidden now.
		var params = {};
		params.ajax = 1;
		params.url = url;
		$.post( url, securePostVariables(params) ).done(function( responseHTML ) {
			if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
		});

	}

	// Shrink any extra padding that was added to accommodate the banner
	$(document).find('div.enterprise-banner-padding').each(function(){
		$(this).addClass('enterprise-banner-no-padding');
		$(this).removeClass('enterprise-banner-padding');
	});


}
function EnterpriseBannerShow()
{
	$('div.enterprise-banner').removeClass('hidden');

	// Grow any extra padding that was added to accommodate the banner
	$(document).find('div.enterprise-banner-no-padding').each(function(){
		$(this).addClass('enterprise-banner-padding');
		$(this).removeClass('enterprise-banner-no-padding');
	});
}

/**
 * WorkflowWidgetsInit
 *
 * Scan the page for any workflow widgets.  If we find some
 * go ahead and load in their javascript.
 * @constructor
 */
function WorkflowWidgetsInit()
{
	$(".workflow-widget").each(function()
	{
		var div = $(this);
		var workflow_name = $(div).data('workflow');
		var js_library = $(div).data('jslibrary');

		var url = base_url + "assets/custom/js/workflows/" + workflow_name + "/" + js_library;
        $.getScript( url );
	});
}
function PusherAlert( data )
{
    var result = JSON.parse(data);
    var message = result['message'];

    if ( getStringValue(message) != "" )
	{
		alert(message);
	}
	else
	{
		alert("Socket communication successful.");
	}
}
function ReplaceDisallowedCharacters( input ) {
	input = replaceFor(input, "+", "::PLUS::");
	input = replaceFor(input, ")", "::RPAR::");
	input = replaceFor(input, "(", "::LPAR::");
	input = replaceFor(input, "%", "::PERCENT::");
	input = replaceFor(input, "/", "::SLASH::");
	return input;
}
function InitLoadingButtons() {

	if ( $(".a2p-forever-spinner-button").length != 0 )
	{
		var l = Ladda.create( document.querySelector( ".a2p-forever-spinner-button" ) );
		if ( ! l.isLoading() ) {
			l.start();
		}
	}

}
function SaveCompanyPreference( group, group_code, value ) {
    var url = base_url + "company/preference/save";

    // Ensure the ajax flag is set to true.
    var params = {};
    params.ajax = 1;
    params.url = url;
    params.group = group;
    params.group_code = group_code;
    params.value = value;

    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
    });
}
function SaveCompanyParentPreference( group, group_code, value ) {
	var url = base_url + "parents/preference/save";

	// Ensure the ajax flag is set to true.
	var params = {};
	params.ajax = 1;
	params.url = url;
	params.group = group;
	params.group_code = group_code;
	params.value = value;

	$.post( url, securePostVariables(params) ).done(function( responseHTML ) {
		if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
	});
}
function ShowSpinner( message ) {
	if ( jQuery.type(message) != "string" ) { message = "loading"; }
	$("#template_overlay").find(".loading-text:first").text(message);
	$("#template_overlay").show();
}
function HideSpinner( ) {
	$("#template_overlay").hide();
}
function UpdateSpinner( message ) {
	if ( jQuery.type(message) != "string" ) { message = ""; }
	if ( $("#template_overlay:visible").length > 0 && message != "" ) {
		$("#template_overlay").find(".loading-text:first").text(message);
	}
}
function IsSpinnerVisible()
{
    if ( $("#template_overlay:visible").length > 0 )
    {
    	return true;
    }
    return false;
}
function ValidateAjaxResponse( responseText ) {

	if ( responseText.indexOf("status\":true") == -1 ){
		if ( responseText.indexOf("template='Advice2Pay'") != -1 )
		{
			// You did not get a TRUE back from the AJAX call AND you appear
			// to have gotten a template page.  Decide which one it is and take
			// action.  I you can't tell, reload the page so automated systems
			// can take over.
			var destination_url = "";

			// We have gotten back a a2p templated page.
			if ( responseText.indexOf("Error 404") != -1 ) {
				// Looks like we got a 404, not an authentication error.
				// Redirect to the detination that does not exist so the user knows
				// what has happened.
				if ( destination_url == "" ) {
					destination_url = base_url + "auth/error_404";
				}
				location.href = destination_url;
			}else if ( responseText.indexOf("Access Denied") != -1 ) {
				// Looks like we got a permission error, not an authentication error.
				// Redirect to the permission screen to show the user.
				if ( destination_url == "" ) {
					destination_url = base_url + "auth/permission";
				}
				location.href = destination_url;
			}else{
				// Assume an authentication error has happend. Redirect back to the
				// browser URL so the authentication system will kick in, log them
				// back and, and then bring them back to this location.
				location.href = window.location.href;
			}

			//alert("TODO: Your session has timed out.  I need to implement the redirect back to the login page.");
			return false;
		}

		// You did not get back TRUE from the AJAX call, but we did not get
		// a web page ... so render this as an AJAXPanic event.
		AJAXPanic(responseText);
		return false;
	}

    // the result was successful if you got here.  Why not
    // make it easieron everyone if we just handle a redirect request right now.
	try{
        var result = JSON.parse(responseText);
        if ( result['type'] == "redirect" ){
            location.href = result['href'];
            return false
        }
    }catch(err){ }



	return true;
}
function AJAXPanic( responseHTML ){
	if ( IsDevelopment() )
	{
		if ( $("#ajax_panic").hasClass("hidden") ) {
			$("#ajax_panic").hide();
			$("#ajax_panic").removeClass("hidden");
		}
		$("#ajax_panic_content").empty();
		$("#ajax_panic_content").append(responseHTML);

        if ( $("#ajax_panic").length != 0 )
		{
            Custombox.open({
                target: "#ajax_panic",
                speed: 300
            });
		}
		else
		{
            console.log("AJAX PANIC!");
            console.log(responseHTML);
		}
	}
}
function ShowModal( title, content ){

	$(".modal").attr("modal-type", "form");

	// Set the title on the modal to match our document id.
	$(".modal-title").html(title);

	// load the url and show modal on success
	$(".modal-body").html(content);
	$(".modal").modal("show");
}
function ActionIconClickHandler( click_obj, form_name, widget_name ) {

    // Pull the URL off the anchor that was clicked and set it on the widget.
    var url = $(click_obj).attr("href");
    $("#" + widget_name).attr("data-href", url);
    refreshWidget( widget_name, "showForm", form_name );

}
function DeveloperTools() {
	$(document).on("click", ".developer-tools", function() {
		if ( IsDevelopment() || IsUAT() )
		{
			//if ( $("#developer_tools").hasClass("hidden") ) {
			//	$("#developer_tools").hide();
			//	$("#developer_tools").removeClass("hidden");
			//}
			refreshWidget("dev_tools_widget", "OpenDevTools")
		}
	});

}
function OpenDevTools() {
	Custombox.open({
		target: "#developer_tools",
		speed: 300
	});
}
function IsDevelopment() {

    if ( window.location.href.indexOf(":3000") != -1 ) { return true; }
    if ( window.location.href.indexOf("nitrousapp.com") != -1) { return true; }
    if ( window.location.href.indexOf("c9users.io") != -1) { return true; }
    if ( window.location.href.indexOf("codeanyapp.com") != -1) { return true; }
	if ( window.location.href.indexOf("dev.advice2pay.com") != -1 ) { return true; }
	if ( window.location.href.indexOf("upgrade.advice2pay.com") != -1 ) { return true; }
    return false;
}
function IsUAT() {
    if ( window.location.href.indexOf("uat.advice2pay.com") != -1 ) { return true; }
    return false;
}
