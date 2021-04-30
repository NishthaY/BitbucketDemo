$(function(){

    // Scroll Handler ( Detect Scroll Events )
    $( "#review_table_scroller" ).scroll(function(e) {
        ScrollHandler(this, e);
    });

    // Click Handler ( Has Header Checkbox )
    $(document).on('click', '.has_headers_click_area', function(e) {
        HeaderCheckboxClickHandler();
    });

    // Click Handler ( Scroll Right Div )
    $(document).on('click', '.scroll-bumper-right', function(e) {
        ScrollRightClickHandler();
    });

    // Click Handler ( Scroll Left Div )
    $(document).on('click', '.scroll-bumper-left', function(e) {
        ScrollLeftClickHandler();
    });

    // Click Handler ( Scroll Right Div )
    $(document).on('click', '.scroll-bumper-button-right', function(e) {
        ScrollRightClickHandler();
    });

    // Click Handler ( Scroll Left Div )
    $(document).on('click', '.scroll-bumper-button-left', function(e) {
        ScrollLeftClickHandler();
    });

    // Show Dropdown Event ( Mapping Column Dropdowns )
    $(document).on('show.bs.dropdown', '.mapping-dropdown', function() {
        //GrowSampleDataContainer( $(this).find("button").first() );
        //GrowFormBottomPaddingForDropdown( $(this), "table_bottom_padding");
    });

    // Hide Dropdown Event ( Mapping Column Dropdowns )
    $(document).on('hide.bs.dropdown', '.mapping-dropdown', function() {
        //ShrinkSampleDataContainer();
        //ShrinkFormBottomPaddingForDropdown( "table_bottom_padding" );
    });

    // DROPDOWN BUTTON - CHANGE HANDLER
    $(document).on('click', '.btn-group .dropdown-menu li', function () {
        if ( ! $(this).hasClass('dropdown-header') )
        {
            ActivateDropdownButton(this);
            CheckRequiredMappings();
        }

    });

    // Click Handler ( Submit Button Handler )
    $(document).on('click', '#validate_upload_form button[type="submit"]', function(e) {

        var options = {
            beforeSubmit: ValidateUploadBeforeSubmit,
            success: ValidateUploadSuccessHandler,
            error: ValidateUploadErrorHandler,
            data: {ajax: '1'}
        };
        $('#validate_upload_form').ajaxForm(options);



    });


    InitMappingTable();

});
function InitMappingTable() {

    // Trigger the scroll event so the scroll bumpers will draw.
    $( "#review_table_scroller" ).scroll();

    // Update the dropdown lists such that they do not include anything
    // that is already selected when the page loads.
    $(".mapping-dropdown").find("button[selected-value!='']").each(function() {
        var value = $(this).attr('selected-value');
        //RemoveItemFromColumnDropboxes(value);
        RemoveItemFromMappingDropboxes(value);
    });

    // Update our required mappings message.
    CheckRequiredMappings();

    // Examine conditional blocks and make sure
    // the required highlighting is correct.
    DrawConditionalBlocks();

}
function ValidateUploadBeforeSubmit() {
    ShowSpinner("quick scan");
    beforeFormPost("validate_upload_form");
    SaveAllMatchPreferences();
}
function ValidateUploadSuccessHandler(responseText, statusText, xhr, form) {

    if ( ! ValidateAjaxResponse(responseText ) ) { return; }
    try{
        HideSpinner();
        successfulFormPost( "validate_upload_form", responseText, true );
        var result = JSON.parse(responseText);

    }catch(err){
        var response = Array();
        response['responseText'] = err;
        ValidateUploadErrorHandler(response);
        return;
    }
}
function ValidateUploadErrorHandler(response) {
    HideSpinner();
    failedFormPost( response['responseText'], "validate_upload_form" );
    //AJAXPanic(responseText);
}
function GetMappingColumnDisplayName(key) {
    var mapping_menu = $(".mapping-dropdown ul.dropdown-menu:first li[data-value='"+key+"']");
    return getStringValue($(mapping_menu).data("display"));
}
function CheckRequiredMappings() {


    // You may not access the continue button until all the required mappings
    // are in place.  Hide the button, if not already.
    $("#upload_complete_button").prop("disabled", true);
    $("#upload_complete_button").addClass("btn-working");
    $("#upload_complete_button").removeClass("btn-primary");

    // The first time we see the missing_matches_container, remove the hidden
    // class and init the class as hidden with javascript.
    if ( $("#missing_matches_container").hasClass("hidden") )
    {
        $("#missing_matches_container").hide();
        $("#missing_matches_container").removeClass("hidden");
    }

    // The first time we see the data_validation_container, remove the hidden
    // class and init the class as hidden with javascript.
    if ( $("#data_validation_container").hasClass("hidden") )
    {
        $("#data_validation_container").hide();
        $("#data_validation_container").removeClass("hidden");
    }

    // If the mapping dropdown is selected, color it so we can easily see it.
    $(".mapping-dropdown").find("button[selected-value!='']").removeClass("btn-default");
    $(".mapping-dropdown").find("button[selected-value!='']").addClass("btn-info");


    // Review the DOM and create a list of missing mappings by display name.
    var missing = Array();
    var str = $("#required_list").html();
    try{
        var required = JSON.parse(str);
        for(var i=0, len=required.length; i < len; i++)
        {
            var mapping = required[i];
            var mapped = $(".mapping-dropdown button[selected-value='"+mapping+"']").length;
            if ( mapped == 0 )
            {
                missing.push(GetMappingColumnDisplayName(mapping));
            }
        }
    }catch(error){
    }

    // Review the DOM and create a list of missing conditional mappings by display name.
    var conditional_failed = Array();
    var str = $("#conditional_list").html();
    try{
        var conditional = JSON.parse(str);
        var keys = Object.keys(conditional)
        for(var i=0, len=keys.length; i < len; i++)
        {
            var valid = false;
            var key = keys[i];
            var columns = conditional[key];
            for(var x=0, len2=columns.length; x < len2;x++)
            {
                var mapping = columns[x];
                var mapped = $(".mapping-dropdown button[selected-value='"+mapping+"']").length;
                if ( mapped != 0 )
                {
                    valid = true;
                }
            }
            if ( ! valid )
            {
                conditional_failed[keys] = true;
            }
        }
    }catch(error){

    }

    // REQUIRED
    // Construct an error message that outlines which columns must be matched
    // before you can continue because they are required.
    var message = "";
    for(var i=0, len=missing.length; i<len; i++)
    {
        var missing_mapping = getStringValue(missing[i]);
        message += missing_mapping + ", ";
    }

    // CONDITIONAL_FAILED
    // Examine the conditional_failed list.  These are the columns that at least one
    // must be selected.  Construct a description that lists out the columns and then
    // add them to the list of messages for the end user.
    for (var list in conditional_failed) {
        if (conditional_failed.hasOwnProperty(list)) {
            var desc = "";
            var columns = list.split(",");
            for(var i=0, len=columns.length; i<len; i++)
            {
                if ( desc != "" )
                {
                    desc = desc + " or ";
                }
                var column = columns[i];
                column = GetMappingColumnDisplayName(column);
                desc = desc + column;
            }
            if ( desc != "" )
            {
                message += desc + ", ";
            }
        }
    }

    // DRAW
    // Draw the message to the screen and trigger the UI to display.
    message = fLeftBack(message, ", ");
    $("#missing_matches_error").text(message);
    if ( message != "" )
    {
        $("#missing_matches_container").show();
        if ( $("#data_validation_container:visible").lenth != 0 ) {
            $("#data_validation_container").addClass("hidden");
        }
    }else{
        $("#missing_matches_container").hide();
        $("#upload_complete_button").prop("disabled", false);
        $("#upload_complete_button").addClass("btn-primary");
        $("#upload_complete_button").removeClass("btn-working");
    }


}
function UploadValidationWidgetChangeHandler() {

    if ( $("#upload_validation_error_list li").length != 0 ) {
        $("#data_validation_container").removeClass("hidden");
    }else{
        $("#upload_complete_button").prop("disabled", false);
    }
    HideSpinner();

}
function HeaderCheckboxClickHandler() {
    var has_headers = "f";
    if ( $("#has_headers").is(":checked") ) {
        $("tbody tr:first").addClass("sample-data-header");
    }else{
        $("tbody tr:first").removeClass("sample-data-header");
    }
    SaveAllMatchPreferences();
}

function SaveAllMatchPreferences() {

    var has_headers = "f";
    if ( $("#has_headers").is(":checked") ) {
        $("tbody tr:first").addClass("sample-data-header");
        has_headers = "t";
    }

    var headers  = [];
    var mappings = [];
    var columns  = [];


    $(".mapping-dropdown").each(function(){
        var dropdown = $(this);
        var button = $(this).find("button").first();
        var column_no = $(button).data("column");
        var mapping = $(button).attr("selected-value");
        var header = $(dropdown).data("user-label");

        if ( has_headers == "f" )
        {
            header = "";
        }

        headers.push(getStringValue(header));
        mappings.push(getStringValue(mapping));
        columns.push(getStringValue(column_no));

    });

    // Find the URL we should use to save the match data based on the
    // attribute stored on the match component panel.
    var panel = $("#has_headers").closest('div.panel-matching-table');
    var url = $(panel).data('save');
    if ( getStringValue(url) === '' ) return;

    var params = {};
    params.ajax = 1;
    params.url = url;
    params.headers = headers;
    params.mappings = mappings;
    params.columns = columns;
    params.has_headers = has_headers;
    //console.log(params);


    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
    });

}
function ActivateDropdownButton ( click_obj ) {

    var li = $(click_obj);
    var button = $(li).closest(".mapping-dropdown").find("button");
    var input = $(li).closest(".mapping-dropdown").find("input");
    var div = $(button).closest("div");


    var user_label = $(div).data("user-label");
    var default_label = $(div).data("default-label");
    var column_no = $(button).data("column");

    var previous_value = getStringValue($(button).attr("selected-value"));
    var value = $(li).data("value");
    var display = $(li).data("display");

    if ( value == "" ) {
        display = "Match Column";
    }

    // If the item the user touched has the class "selected" then they picked
    // something that was already mapped.  In this case, find that other column
    // and clear it out as we are about to assign the value to this column.
    if ( $(li).hasClass("selected") )
    {
        $(".btn-column-mapping").each(function(){
            var alt_button = $(this);
            var alt_button_selected_value = $(alt_button).attr("selected-value");
            var alt_input = $(alt_button).next().find("input:first");
            if ( alt_button_selected_value == value )
            {
                $(alt_button).attr("selected-value", "");
                $(alt_button).html("Match Column" + " <span class='caret'>");
                $(input).val("");
                ColorMappingDropbox(alt_button, "");
            }
        });

    }

    // Set the values on mapping column button to match the item
    // that was selected.
    $(button).attr("selected-value", value);
    $(button).html(display + " <span class='caret'>");
    $(input).val(value);


    // Re-draw all of the dropboxes so they are updated and color this
    // mapping dropdown.
    AddItemToMappingDropboxes(previous_value);
    RemoveItemFromMappingDropboxes(value);
    DrawConditionalBlocks();
    ColorMappingDropbox(button, value);


    // Save them all.
    SaveAllMatchPreferences();



}

function RemoveItemFromMappingDropboxes(value) {
    $(".mapping-dropdown").each(function(){
        var button = $(this).find("button:first");
        var list = $(button).next();

        $(list).children("li").each(function(){
            var li = $(this);
            if ( $(li).data("value") == value && value != "" ) {
                $(li).addClass("selected");
                $(li).find('span:first').removeClass('hidden');
                $(li).find('span:nth-child(2)').addClass('strikethrough');
            }

        });

    })
}
function IsConditionalBlockSelected(dropdown, index)
{
    index = parseInt(index);
    var button = $(dropdown).find("button:first");
    var list = $(button).next();
    var search = "li:nth-child("+index+")";
    var header = $(list).find(search);
    var count = parseInt($(header).data('count'));

    while(count != 0)
    {
        var i = parseInt(index) + parseInt(count);
        var item = $(list).find('li:nth-child('+i+')');
        if ( item.hasClass("selected") )
        {
            return true;
        }
        count--;
    }
    return false;
}
function UpdateConditionalBlockSelected(dropdown, index)
{
    // Here will will draw each child as if they are not required
    // unless they have the class selected.  Those will be required.
    var button = $(dropdown).find("button:first");
    var list = $(button).next();
    var search = "li:nth-child("+index+")";
    var header = $(list).find(search);
    var count = parseInt($(header).data('count'));

    while(count != 0)
    {
        var i = parseInt(index) + parseInt(count);
        var item = $(list).find('li:nth-child('+i+')');
        var a = $(item).find('a:first');
        if ( item.hasClass("selected") )
        {
            $(a).css({"font-weight":"bold"});
        }
        else
        {
            $(a).css({"font-weight":"normal"});
        }
        count--;
    }

}
function UpdateConditionalBlockNotSelected(dropdown, index)
{
    console.log("drawing conditional block where all will be bold.");
    var button = $(dropdown).find("button:first");
    var list = $(button).next();
    var search = "li:nth-child("+index+")";
    var header = $(list).find(search);
    var count = parseInt($(header).data('count'));

    while(count != 0)
    {
        var i = parseInt(index) + parseInt(count);
        var item = $(list).find('li:nth-child('+i+')');
        var a = $(item).find('a:first');
        $(a).css({"font-weight":"bold"});
        count--;
    }
}
function DrawConditionalBlocks()
{

    var dropdown = $(".mapping-dropdown:first");
    var button = $(dropdown).find("button:first");
    var list = $(button).next();

    // Scan just the first dropdown looking for conditional blocks.
    var conditional_block_indexes = Array();
    var index = 0;
    $(list).children("li").each(function() {
        index++;
        var li = $(this);
        if ( $(li).hasClass("dropdown-header") && $(li).hasClass("conditional") ) {
            conditional_block_indexes.push(index);
        }
    });


    // Draw the conditional blocks on each dropdown on the screen.
    $(".mapping-dropdown").each(function(){
        dropdown = $(this);
        for( var i=0;i<conditional_block_indexes.length;i++)
        {
            var conditional_block_index = conditional_block_indexes[i];
            if ( IsConditionalBlockSelected( dropdown, conditional_block_index ) )
            {
                UpdateConditionalBlockSelected(dropdown, conditional_block_index);
            }
            else {
                UpdateConditionalBlockNotSelected(dropdown, conditional_block_index);
            }
        }
    });

}
function AddItemToMappingDropboxes(value) {
    $(".mapping-dropdown").each(function(){
        var button = $(this).find("button:first");
        var list = $(button).next();
        $(list).children("li").each(function(){
            var li = $(this);
            if ( $(li).data("value") == value && $(li).hasClass("selected") ) {
                $(li).removeClass("selected");
                $(li).find('span:first').addClass('hidden');
                $(li).find('span:nth-child(2)').removeClass('strikethrough');
            }
        });
    })
}
function ColorMappingDropbox( button, value ) {
    $(button).removeClass("btn-default");
    if ( value != "" )
    {
        SetMappingDropdownButtonClass(value, "btn-info");
    }else{
        SetMappingDropdownButtonClass(value, "btn-default");
    }
}
function SetMappingDropdownButtonClass( key, btn_class ) {
    $(".mapping-dropdown").find("button[selected-value='"+key+"']").removeClass("btn-default");
    $(".mapping-dropdown").find("button[selected-value='"+key+"']").removeClass("btn-danger");
    $(".mapping-dropdown").find("button[selected-value='"+key+"']").removeClass("btn-success");
    $(".mapping-dropdown").find("button[selected-value='"+key+"']").removeClass("btn-info");
    $(".mapping-dropdown").find("button[selected-value='"+key+"']").addClass(btn_class);
}


$(".leftArrow").click(function () {
  var leftPos = $('.innerWrapper').scrollLeft();
  $(".innerWrapper").animate({scrollLeft: leftPos - 200}, 800);
});

$(".rightArrow").click(function () {
  var leftPos = $('.innerWrapper').scrollLeft();
  $(".innerWrapper").animate({scrollLeft: leftPos + 200}, 800);
});



function ScrollLeftClickHandler() {

    // ScrollLeftClickHandler
    //
    // Scroll the matching table to the left for the user.
    // ---------------------------------------------------------
    var scroll_obj = $("#review_table_scroller");
    var distance = Math.abs(scroll_obj.outerWidth() / 2) + (Math.abs(scroll_obj.outerWidth() / 2) / 2);
    var leftPos = $(scroll_obj).scrollLeft();
    $(scroll_obj).animate({scrollLeft: leftPos - distance},500);
}
function ScrollRightClickHandler() {

    // ScrollRightClickHandler
    //
    // Scroll the matching table to the right for the user.
    // ---------------------------------------------------------
    var scroll_obj = $("#review_table_scroller");
    var distance = Math.abs(scroll_obj.outerWidth() / 2) + (Math.abs(scroll_obj.outerWidth() / 2) / 2);
    var leftPos = $(scroll_obj).scrollLeft();
    $(scroll_obj).animate({scrollLeft: leftPos + distance},500);
}
function ScrollHandler(scroll_obj) {

    // ScrollHandler
    //
    // As the matching table scrolls, hide or show the scroll bumpers as needed.
    // ---------------------------------------------------------

    var scroller_left = $(".scroll-bumper-left");
    var scroller_right = $(".scroll-bumper-right");
    var scroller_button_left = $(".scroll-bumper-button-left");
    var scroller_button_right = $(".scroll-bumper-button-right");
    var position = scroll_obj.scrollLeft;
    var minScrollLeft = 0;
    var maxScrollLeft = scroll_obj.scrollWidth - scroll_obj.clientWidth;
    if ( position == minScrollLeft ) {
        if ( $(scroller_left).is(":visible") )
        {
            $(scroller_left).addClass("hidden");
            $(scroller_button_left).addClass("hidden");
        }
    }else{
        if ( ! $(scroller_left).is(":visible") )
        {
            $(scroller_left).removeClass("hidden");
            $(scroller_button_left).removeClass("hidden");
        }
    }
    if ( position == maxScrollLeft ) {
        if ( $(scroller_right).is(":visible") )
        {
            $(scroller_right).addClass("hidden");
            $(scroller_button_right).addClass("hidden");
        }
    }else{
        if ( ! $(scroller_right).is(":visible") )
        {
            $(scroller_right).removeClass("hidden");
            $(scroller_button_right).removeClass("hidden");
        }
    }
}
