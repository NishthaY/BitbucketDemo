$(function()
{
    // Upload Date Form Submit Button - Click Handler
    $(document).on('click', '#parent_map_form button[type="submit"]', function(e) {
        var form = $('#parent_map_form');
        var options = {
            beforeSubmit: ParentMapFormBeforeSubmit,
            success: ParentMapFormSuccessHandler,
            error: ParentMapFormErrorHandler,
            data: {ajax: '1'}
        };
        $('#parent_map_form').ajaxForm(options);

    });

    $(document).on('click', 'div.radio', function(e){
        CompanyParentMapCompanyRadioButtonClickHandler(this, e);
    });


    // Click Handler ( Rollback Company No Button Handler )
    $(document).on('click', '#confirm_new_company_form #no_btn', function(e) {
        var form = $("#confirm_new_company_form");
        hideForm(form, true, true);
    });

    // Click Handler ( Rollback Company No Button Handler )
    $(document).on('click', '#confirm_new_company_form #yes_btn', function(e)
    {
        e.preventDefault();

        // Hide the form.
        var form = $("#confirm_new_company_form");
        hideForm(form, true, true);

        // Using the mapping value that was stashed on the confirmation form, find the
        // input value and text.
        var input_name = $(form).find("input[name='mapping']").val();
        var input_value = $("input[name='"+input_name+"_selected_value']").val();
        var input_text = $("input[name='"+input_name+"_selected_text']").val();

        // Find the mapping dropdown button that triggered the confirmation, locate
        // its container and pull of the href.  This is where we will save the
        // users election.
        var button = $('button[data-dropdown-source="'+input_name+'"]');
        var container = $(button).closest("div.btn-group");
        var save_href = $(container).data('href');

        SaveCompanyMapping( input_name, input_value, input_text, save_href );
    });



    autoIgnoreUnavailableCompanies();   // Only do this on load.
    UpdateContinueButton();
});
var parent_map_debug = true;


function CompanyParentMapCompanyRadioButtonClickHandler(click_obj, e)
{
    var container = $(click_obj);
    var input = $(container).find('input[type="radio"]');
    var label = $(container).find('label');

    var selected_value = $(input).val();
    var selected_text = $(label).text();
    $(input).prop('checked', true);

    var url = base_url + "parent/map/company/save/single";

    var params = {};
    params.company_id = getStringValue(selected_value);
    params.company_name = getStringValue(selected_text);
    params.ajax = 1;
    params.url = url;

    // Call the server.
    $.post( url, securePostVariables(params) ).done(function( responseText ) {

        if ( ! ValidateAjaxResponse(responseText ) ) { return; }
        var result = JSON.parse(responseText);

        if ( result['type'] == "success" )
        {
            // Refresh the summary widget to reflect the new election.  Once done, activate the
            // update button if possible.
            refreshSummaryWidget();
        }

    });


}
function UpdateContinueButton()
{
    // Find the continue button and assume disabled.
    var button = $("#parent_map_continue_btn");
    var disabled = true;

    // Make the decision on if we should unlock the continue button
    // based on the type of selector we are showing the user.
    var type = $("#widget_type").val();
    if ( type == "multiple_companies" )
    {
        var total       = 0;
        var ignore      = 0;
        var add         = 0;
        var selected    = 0;

        $("div[data-companyparent-map-company='1']").each(function()
        {
            total = total + 1;

            var container = $(this);
            var button = $(container).find("button:first");
            var ul = $(container).find("ul:first");
            var source = $(button).data("dropdown-source");
            var search = source + "_selected_value";
            var val = $("#" + search).val();

            if ( val === "ignore" )
            {
                ignore = ignore + 1;
            }
            else if ( val === "add" ) {
                add = add + 1;
            }
            else if ( val != "" ) {

                var li = $(ul).find('li[value="'+val+'"]');
                if ( ! $(li).hasClass('unavailable'))
                {
                    selected = selected + 1;
                }


            }
        });

        disabled = false;
        if ( add != 0 )
        {
            disabled = true;
        }
        else if ( total == ignore )
        {
            disabled = true;
        }
        else if ( total != (selected + ignore ) )
        {
            disabled = true;
        }

        console.log("RESULTS");
        console.log("  total: " + total);
        console.log("  ignore: " + ignore);
        console.log("  add: " + add);
        console.log("  selected: " + selected);
    }


    if( type === 'single_company' )
    {
        // Make sure we have a radio button election.
        var company_election = false;
        var value = $("input[name='company']:checked").val();
        value = getStringValue(value);
        if ( value != "" ) company_election = true;


        // Make sure we have exactly one file importing that is not NONE.
        var importing_election = false;
        var importing = $("#importing_list");
        var items = $(importing).find('li');
        if ( items.length == 1 )
        {
            var label = $(items).text().toUpperCase();
            if ( label != "NONE" ) importing_election = true;
        }

        // Enable or disable the button.
        disabled = true;
        if ( company_election && importing_election ) disabled = false;


    }

    // Draw the continue button enabled or disabled.
    if ( ! disabled ) {
        $(button).prop("disabled", false);
        $(button).addClass('btn-primary');
        $(button).removeClass("btn-working");
    }else{
        $(button).prop("disabled", true);
        $(button).addClass('btn-working');
        $(button).removeClass("btn-primary");
    }

    // If the continue button changed, then we should check a
    // few more places to see if we should show some helpful information or not.
    updateMappingAlert();
}
function CompanyParentMapStartDateDropdownOnChange( name, value, text )
{
    var input = $("input[name='start_month_selected_value']");
    var month = $(input).val();

    input = $("input[name='start_year_selected_value']");
    var year = $(input).val();

    var url = base_url + "parent/map/company/save/importdate";

    var params = {};
    params['month'] = month;
    params['year'] = year;
    params['url'] = url;
    params['ajax'] = 1;

    $.post( url, securePostVariables(params) ).done(function( responseText ) {

        if ( ! ValidateAjaxResponse(responseText ) ) { return; }
        var result = JSON.parse(responseText);
        if ( result['type'] == "success" )
        {
            refreshSummaryWidget();
        }

    }).failed(function(responseText){

    });


}
function CompanyParentMapCompanyDropdownClickHandler(click_obj, e)
{
    e.preventDefault();
}
function CompanyParentMapCompanyDropdownSelectHandler( name, value, text ) {

    var input_name = name;
    var selected_value = value;
    var selected_text = text;

    var input_value = $("input[name='"+input_name+"_selected_value']");
    var input_text = $("input[name='"+input_name+"_selected_text']");

    var button = $('button[data-dropdown-source="'+input_name+'"]');
    var container = $(button).closest("div.btn-group");
    var save_href = $(container).data('href');

    // Update the display of the dropdown.
    $(input_value).val(selected_value);
    $(input_text).val(selected_text);
    $(button).find("span:first").text(selected_text);

    if ( selected_value === 'add' )
    {
        // If the selected value is 'add', don't call the save company mapping function just yet.
        // Rather, show them a dialog asking them to confirm they really want to make a new company.
        var widget_name = 'confirm_company_create_widget';

        var replaceFor = {};
        replaceFor['IDENTIFIER'] = input_name;
        replaceTagsWidget(widget_name, replaceFor);

        refreshWidget('confirm_company_create_widget', 'showForm', 'confirm_new_company_form');
    }
    else
    {
        executeFunctionByName("SaveCompanyMapping", window, input_name, selected_value, selected_text, save_href);
    }




}

function SaveCompanyMapping( input_name, input_value, input_text, url ) {

    var button = $('button[data-dropdown-source="'+input_name+'"]');
    var container = $(button).closest("div.btn-group");
    var error = $(container).find('div.runtime-error:first');

    url = getStringValue(url);

    var params = {};
    params.url = url;
    params.name = getStringValue(input_name);
    params.value = getStringValue(input_value);
    params.label = getStringValue(input_text);
    params.ajax = 1;
    params.url = url;

    if( url == "" ) return;

    // Find the dropdown button that was selected.
    var button = null;
    $(container).find('button').each(function()
    {
       if ( $(this).attr('data-dropdown-source') == input_name)
       {
           button = $(this);
       }
    });

    // Disable the dropdown while we are processing it.
    $(button).addClass("disabled");
    $(button).prop("disabled", true);
    $(error).text("");
    $(error).addClass('hidden');

    // Call the server.
    $.post( url, securePostVariables(params) ).done(function( responseText ) {

        // Reactivate the dropdown
        $(button).removeClass("disabled");
        $(button).prop("disabled", false);
        if ( ! ValidateAjaxResponse(responseText ) ) { return; }
        var result = JSON.parse(responseText);
        if ( result['type'] == "danger" )
        {
            var message = result['message'];
            if(getStringValue(message) == '' )
            {
                message = "Please try again.";
            }
            $(error).text(message);
            $(error).removeClass('hidden');
        }
        if ( result['type'] == "success" )
        {
            var message = result['message'];
            refreshMapAndSummaryWidgets();
        }

    });

}

function ParentMapFormBeforeSubmit(responseText, statusText, xhr, form)
{
    if ( parent_map_debug ) console.log("ParentMapFormBeforeSubmit: started");
    beforeFormPost("parent_map_form");
}
function ParentMapFormSuccessHandler(responseText, statusText, xhr, form)
{
    if ( parent_map_debug ) console.log("ParentMapFormSuccessHandler: started");
    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    if ( parent_map_debug ) console.log("ParentMapFormSuccessHandler: was valid");
    try{
        successfulFormPost( "parent_map_form", responseText, true );
        var result = JSON.parse(responseText);
        refreshSummaryWidget();

    }catch(err){
        var response = Array();
        response['responseText'] = err;
        ParentMapFormErrorHandler(response);
        return;
    }
}
function ParentMapFormErrorHandler(responseText, statusText, xhr, form)
{
    if ( parent_map_debug ) console.log("ParentMapFormErrorHandler: started");
    failedFormPost( response['responseText'], "parent_map_form" );
    //AJAXPanic(responseText);
}
function refreshMapAndSummaryWidgets( step )
{
    if ( jQuery.type(step) === "undefined" ) step = 1;
    step = parseInt(step);

    var next_step = getStringValue(step + 1);
    if ( step == 1 ) refreshWidget('map_companies_widget', "refreshMapAndSummaryWidgets", next_step);
    if ( step == 2 ) refreshWidget('company_map_summary_widget', "refreshMapAndSummaryWidgets", next_step);
    if ( step == 3 ) UpdateContinueButton();
}
function refreshSummaryWidget( step )
{
    if ( jQuery.type(step) === "undefined" ) step = 1;
    step = parseInt(step);

    var next_step = getStringValue(step + 1);
    if ( step == 1 ) refreshWidget('company_map_summary_widget', "refreshSummaryWidget", next_step);
    if ( step == 2 ) UpdateContinueButton();

}

/**
 * updateMappingAlert
 *
 * On the map companies widget, we have an info message explaining that not
 * all of the mapped companies will be imported and they should review the
 * left hand column for more information.  This function will display
 * that message not all of the imported companies are not scheduled to
 * load.
 *
 */
function updateMappingAlert()
{
    var widget = $("#map_companies_widget");
    var alert = $(widget).find('div.alert:first');
    var show_warning = false;

    $(widget).find('button').each(function()
    {
        var button = $(this);
        var input_name = $(button).data('dropdown-source');
        var company_id = $("input[name='"+input_name+"_selected_value']").val();

        if ( company_id === 'ignore' ) show_warning = true;
        if ( searchListForCompanyId("unavailable_list", company_id) ) show_warning = true;

        if ( searchListForCompanyId("not_importing_list", company_id) ) show_warning = true;
    });

    if ( show_warning )
    {
        $(alert).removeClass("hidden");
    }
    else
    {
        $(alert).addClass("hidden");
    }

}

/**
 * autoIgnoreUnavailableCompanies
 *
 * When the page loads, look for companies that are in the unavailable list
 * and then auto-select them as ignored.  They cannot be loaded because they
 * are already processing.  Help the user out by setting them to ignored for
 * them.
 *
 */
function autoIgnoreUnavailableCompanies()
{
    // Find all of the dropdown buttons in the map companies widget.
    var widget = $("#map_companies_widget");

    $(widget).find('button').each(function()
    {

        var button = $(this);
        var input_name = $(button).data('dropdown-source');
        var selected_value = $("input[name='"+input_name+"_selected_value']").val();

        // If one of the select lists have a company mapped that is in the unavailable list,
        // then we want to set that dropdown to ignore.
        if ( searchListForCompanyId("unavailable_list", selected_value) )
        {
           CompanyParentMapCompanySetDropdown( input_name, "ignore", "Ignored");
        }

    });
}

/**
 * CompanyParentMapCompanySetDropdown
 *
 * Manually set a mapping dropdown.
 *
 * @param input_name
 * @param selected_value
 * @param selected_text
 * @constructor
 */
function CompanyParentMapCompanySetDropdown( input_name, selected_value, selected_text )
{
    var button = $('button[data-dropdown-source="'+input_name+'"]');
    var input_value = $("input[name='"+input_name+"_selected_value']");
    var input_text = $("input[name='"+input_name+"_selected_text']");

    $(input_value).val(selected_value);
    $(input_text).val(selected_text);
    $(button).find("span:first").text(selected_text);

}

/**
 * searchListForCompanyId
 *
 * The lists show in the left hand summary widget are named and
 * contain the company ids for each of the corresponding companies.
 * This function will search the list specified for the company
 * provided.  It will return TRUE if it is found, else FALSE;
 *
 * @param list_name
 * @param company_id
 * @returns {boolean}
 */
function searchListForCompanyId(list_name, company_id)
{

    list_name = getStringValue(list_name);
    if ( list_name === '' ) return false;

    company_id = getStringValue(company_id);
    if ( company_id === '' ) false;

    var found = false;
    var list = $("#" + list_name);
    if ( $(list).length > 0 )
    {
        $(list).find('li').each(function(){
            var item_company_id = $(this).data('companyid');
            if ( getStringValue(item_company_id) === getStringValue(company_id) )
            {
                found = true;
            }
        });
    }

    return found;
}