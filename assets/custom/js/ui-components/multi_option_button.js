var debug_multi_option_button = false;

$(function(){

    $(document).on('click', 'span.multi-option-btn-container > div.btn-group > ul.dropdown-menu > li', function (e) {
        MultiOptionButtonItemClickHandler(this, e);
    });


    $(document).on('click', 'span.multi-option-btn-container > div.btn-group > button.main-btn', function (e) {
        MultiOptionButtonClickHandler(this, e);
    });

    InitMultiOptionButtons();

});


function InitMultiOptionButtons( )
{
    if ( debug_multi_option_button ) console.log("InitMultiOptionButtons: start");

    $(document).find('span.multi-option-btn-container' ).each(function(){
        if ( debug_multi_option_button ) console.log("InitMultiOptionButtons: adding hide/show handlers.");
        var container = $(this);
        $(container).on('hide.bs.dropdown', function(e){ MultiOptionButtonHideHandler(e) });
        $(container).on('show.bs.dropdown', function(e){ MultiOptionButtonShowHandler(e) });
    });
}
function MultiOptionButtonItemClickHandler( click_obj, e)
{
    if ( debug_multi_option_button ) console.log("MultiOptionButtonItemClickHandler: start");

    var li = $(click_obj);
    var anchor = $(li).find('a:first');
    var value = $(li).data('value');
    var label = $(anchor).text();

    var container = $(li).closest('span.multi-option-btn-container');
    var input = $(container).find("input[type='hidden']");
    var button = $(container).find('button:first');
    $(button).text(label);
    $(button).val(value);
    $(input).val(value);
}
function MultiOptionButtonHideHandler( e )
{
    var container = e.target;
    if ( $(container).length )
    {
        var toggle_button = $(container).find('button.dropdown-toggle:first');
        var icon_container = $(toggle_button).find('span:first');

        $(icon_container).find('i').each(function(){
            var i = $(this);
            if ( $(i).data('togglestate') === 'opened' )
            {
                $(i).removeClass("hidden");
            }
            if ( $(i).data('togglestate') === 'closed' )
            {
                $(i).addClass("hidden");
            }

        });
    }


}
function MultiOptionButtonShowHandler( e )
{
    var container = e.target;
    if ( $(container).length )
    {
        var toggle_button = $(container).find('button.dropdown-toggle:first');
        var icon_container = $(toggle_button).find('span:first');

        $(icon_container).find('i').each(function(){
           var i = $(this);
           if ( $(i).data('togglestate') === 'opened' )
           {
               $(i).addClass("hidden");
           }
            if ( $(i).data('togglestate') === 'closed' )
            {
                $(i).removeClass("hidden");
            }

        });
    }
}

function MultiOptionButtonClickHandler(click_obj, e)
{
    var onclick = $(click_obj).data('callbackonclick');
    var button = $(click_obj);

    if ( getStringValue(onclick) !== '' )
    {
        MultiOptionButtonReportWorking(button);
        executeFunctionByName(onclick, window, click_obj, e);
    }
}

/**
 * MultiOptionButtonReportWorking
 *
 * This function effectively locks the multi-option button so
 * it' cannot be used.  Execute the Success/Failure/Reset function
 * to undo this.
 *
 * @param click_obj
 * @constructor
 */
function MultiOptionButtonReportWorking( click_obj)
{
    var button = $(click_obj);
    var container = $(button).closest('div');
    var button_color = $(button).data('buttoncolor');
    var button_color_offset = $(button).data('buttoncoloroffset');

    var warning_indicator = $(container).find('span.multioptionbutton-warning:first');
    var success_indicator = $(container).find('span.multioptionbutton-success:first');
    var working_indicator = $(container).find('span.multioptionbutton-working:first');
    var toggle_indicator = $(container).find('span.multioptionbutton-toggle:first');

    $(container).find('button.btn-multi-option').each(function(){
        $(this).removeClass(button_color).addClass('btn-working');
        $(this).removeClass(button_color_offset).addClass('btn-working');
        $(this).addClass('disabled');
        $(this).prop('disabled', true);
    });

    $(warning_indicator).addClass('hidden');
    $(success_indicator).addClass('hidden');
    $(working_indicator).removeClass('hidden');
    $(toggle_indicator).addClass('hidden');
}

/**
 * MultiOptionButtonReportReset
 *
 * This function will remove any 'feedback' the button might have in place
 * and restore it back to it's starting usable state.
 *
 * @param click_obj
 * @constructor
 */
function MultiOptionButtonReportReset( click_obj)
{
    var button = $(click_obj);
    $(button).blur();



    var button_color = $(button).data('buttoncolor');
    var button_color_offset = $(button).data('buttoncoloroffset');

    var container = $(button).closest('div.btn-group');
    $(container).find('button.btn-multi-option').each(function(){
        $(this).removeClass('btn-working').addClass(button_color);
        $(this).removeClass('btn-working').addClass(button_color_offset);
        $(this).removeClass('disabled');
        $(this).prop('disabled', false);
    });

    var warning_indicator = $(container).find('span.multioptionbutton-warning:first');
    var success_indicator = $(container).find('span.multioptionbutton-success:first');
    var working_indicator = $(container).find('span.multioptionbutton-working:first');
    var toggle_indicator = $(container).find('span.multioptionbutton-toggle:first');

    $(warning_indicator).addClass('hidden');
    $(success_indicator).addClass('hidden');
    $(working_indicator).addClass('hidden');
    $(toggle_indicator).removeClass('hidden');

    var label = $(button).data('label');
    label = getStringValue(label);
    if ( label != '' )
    {
        $(button).text(label);
    }

}

/**
 * MultiOptionButtonReportSuccess
 *
 * This function will draw the multi-option button in a success state.
 *
 * @param click_obj
 */
function MultiOptionButtonReportSuccess( click_obj )
{

    var button = $(click_obj);
    var container = $(button).closest('div.btn-group');
    var warning_indicator = $(container).find('span.multioptionbutton-warning:first');
    var success_indicator = $(container).find('span.multioptionbutton-success:first');
    var working_indicator = $(container).find('span.multioptionbutton-working:first');
    var toggle_indicator = $(container).find('span.multioptionbutton-toggle:first');

    $(success_indicator).removeClass('hidden');
    $(toggle_indicator).addClass('hidden');
    $(working_indicator).addClass('hidden');
    $(warning_indicator).addClass('hidden');

    var success_message = getStringValue($(button).data('success'));
    if ( success_message != "" )
    {
        var label = $(button).text();
        $(button).attr('data-label', label);
        $(button).text(success_message);
    }

    setTimeout(function() { MultiOptionButtonReportReset(button) }, 2000);



}

/**
 * MultiOptionButtonReportFailure
 *
 * This function will draw the multi-option button in a failure state.  It will
 * then wait a few seconds and clear the error releasing the button so it can
 * be tried again.
 *
 * @param click_obj
 */
function MultiOptionButtonReportFailure( click_obj )
{
    var button = $(click_obj);
    var container = $(button).closest('div.btn-group');
    var warning_indicator = $(container).find('span.multioptionbutton-warning:first');
    var success_indicator = $(container).find('span.multioptionbutton-success:first');
    var working_indicator = $(container).find('span.multioptionbutton-working:first');
    var toggle_indicator = $(container).find('span.multioptionbutton-toggle:first');

    $(warning_indicator).removeClass('hidden');
    $(toggle_indicator).addClass('hidden');
    $(success_indicator).addClass('hidden');
    $(working_indicator).addClass('hidden');

    var failed_message = getStringValue($(button).data('failed'));
    if (  failed_message != "" )
    {
        var label = $(button).text();
        $(button).attr('data-label', label);
        $(button).text(failed_message);
    }

    setTimeout(function() { MultiOptionButtonReportReset(button) }, 2000);
}