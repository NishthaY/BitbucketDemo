$(function(){

    // Click Handler ( Submit Button Handler )
    $(document).on('click', '#relationships_page_form #relationships_complete_button', function(e) {
        var options = {
            beforeSubmit: RelationshipsPageBeforeSubmit,
            success: RelationshipsPageSuccessHandler,
            error: RelationshipsPageErrorHandler,
            data: {ajax: '1'}
        };
        $('#relationships_page_form').ajaxForm(options);
    });

    $(document).on('click', '.relationship-type-group .dropdown-menu li', function () {
        RelationshipDropdownSelectHandler(this);
    });


    $(document).on('click', '.relationship-type-group .dropdown-toggle', function(e) {
        RelationshiopDropdownClickHandler(this, e);
    });


    $(document).on('click', '.preference-item', function(e) {
        PricingModelChangeHandler(this, e);
    });
    
    UpdateContinueButton();


});
function UpdateContinueButton() {
    var button = $("#relationships_complete_button");
    var count = $(".relationship-question-indicator:visible").length;
    if ( count == 0 ) {
        $(button).prop("disabled", false);
        $(button).addClass('btn-primary');
        $(button).removeClass("btn-working");
    }else{
        $(button).prop("disabled", true);
        $(button).addClass('btn-working');
        $(button).removeClass("btn-primary");
    }
}
function PricingModelChangeHandler( click_obj, e ) {

    // Collect our data.
    var url = $(click_obj).data('href');
    var group = $(click_obj).data('group');
    var group_code = $(click_obj).data('groupcode');
    var value = $(click_obj).val();

    // Verify our data.
    if( getStringValue(url) == "" ) return;
    if( getStringValue(group) == "" ) return;
    if( getStringValue(group_code) == "" ) return;
    if( getStringValue(value) == "" ) return;

    // Create our payload
    var params = {};
    params.url = getStringValue(url);
    params.group = getStringValue(group);
    params.group_code = getStringValue(group_code);
    params.value = getStringValue(value);
    params.ajax = 1;

    // Call the server.
    $.post( url, securePostVariables(params) ).done(function( responseText ) {
        if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    });
}
function RelationshiopDropdownClickHandler(click_obj, e)
{
    e.preventDefault();

    var button = $(click_obj);
    var dropdown = $(button).parent().find("ul.dropdown-menu:first");
    var dropDownTop = button.offset().top + button.outerHeight();

    // Adjust the position of the drop down so that it is left
    // aligned with the button.
    $(dropdown).addClass("hidden");
    dropdown.css('top', dropDownTop + "px");
    dropdown.css('left', button.offset().left + "px");
    $(dropdown).removeClass("hidden");
}
function RelationshipsPageBeforeSubmit() {
    beforeFormPost("relationships_page_form");
}
function RelationshipsPageSuccessHandler(responseText, statusText, xhr, form) {

    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        successfulFormPost( "relationships_page_form", responseText, true );
        var result = JSON.parse(responseText);
    }catch(err){
        var response = Array();
        response['responseText'] = err;
        RelationshipsPageErrorHandler(response);
        return;
    }
}
function RelationshipsPageErrorHandler(response) {
    failedFormPost( response['responseText'], "relationships_page_form" );
}
function RelationshipDropdownSelectHandler( click_obj ) {

    // Collect all of the objects in play.
    var item = $(click_obj);
    var selected_value = $(item).attr("id");
    var selected_text = $(item).text();
    var input_name = $(item).closest('.dropdown').find('button:first').data("dropdown-source");
    //var input_name = $(item).closest(".dropdown-menu").prev().data("dropdown-source");  // No good in Chrome.  Delete later.
    var input_value = $("input[name='"+input_name+"_selected_value']");
    var input_text = $("input[name='"+input_name+"_selected_text']");
    var group_div = $(item).closest(".relationship-type-group");
    var save_href = $(group_div).find("button:first").data("href");

    // Update the display of the dropdown.
    $(input_value).val(selected_value);
    $(input_text).val(selected_text);
    $(item).closest(".relationship-type-group").find("button:first").find("span:first").text(selected_text);
    $(item).closest(".relationship-type-group").find(".relationship-question-indicator:first").addClass("hidden");

    executeFunctionByName( "SaveCompanyRelationship", window, input_name, selected_value, selected_text, save_href );
    
    UpdateContinueButton();

}
function SaveCompanyRelationship( input_name, input_value, input_text, url ) {

    url = getStringValue(url);

    var params = {};
    params.url = url;
    params.id = getStringValue(input_name);
    params.code = getStringValue(input_value);
    params.description = getStringValue(input_text);
    params.ajax = 1;

    if( url == "" ) return;


    $.post( url, securePostVariables(params) ).done(function( responseText ) {
        if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    });

}
