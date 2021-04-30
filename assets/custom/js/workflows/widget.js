$(function()
{
    InitDefaultWorkflowWidget();
});

function InitDefaultWorkflowWidget()
{
    // Confirm we have a default workflow widget by looking at the wf_jslibrary
    // property.  Add a click handler that will refresh the widget on click.  This
    // is the minimum amount of JS layer work that will need to be done to support
    // a workflow widget.

    // If the user writes their own library, they will need to do what ever they need
    // to do which might include adding this same click handler.  That's up to them
    // if they are rolling their own.

    $("div.workflow-widget").each( function() {
        // We found a workflow widget!  Look to see if it's pointing to our
        // default workflow widget library, or a custom one.
        var wf_jslibrary = $(this).data('jslibrary');

        if (wf_jslibrary === '../widget.js') {
            // If the user is using the out of the box workflow widget, just add a click
            // handler.  That is the minimum amount of custom work they could have done
            // had they written their own javascript layer.  There is not additional
            var workflow_name = $(this).data('workflow');
            var widget_name = $(this).data('widgetname');

            if (getStringValue(workflow_name) !== '' && getStringValue(widget_name) !== '') {
                // Here we will add a click handler that will trigger the widget refresh.
                $(document).on('click', '#' + widget_name, function (e) {

                    e.preventDefault();
                    var href = $(this).find('a:first').attr('href');
                    if ( href !== "" && href !== "#" )
                    {
                        // If we have a url in the anchor's href, go there.
                        location.href = href;
                    }
                    else
                    {
                        // If we have no valid url in the anchor's href, refresh the widget.
                        refreshWidget(widget_name);
                    }

                });
            }
        }
    });
}