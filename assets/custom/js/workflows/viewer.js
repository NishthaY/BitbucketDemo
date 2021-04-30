$(function()
{
    $(document).on('click', 'div.wf-property-row', function (e) {
        ShowWFPropertyDetails(this, e);
    });
});

/**
 * ShowWFPropertyDetails
 *
 * This function will toggle open or close the property details.
 *
 * @param click_obj
 * @param e
 * @constructor
 */
function ShowWFPropertyDetails(click_obj, e)
{
    e.preventDefault();

    var ul = $(click_obj).closest('ul');
    var li = $(click_obj).closest('li');
    var details = $(li).find('div.wf-property-row-details');
    var action = "";
    if ( $(details).hasClass('hidden') )
    {
        action = "open"
    }

    // Close any open details
    $(ul).find('li').each(function(){
        var li = $(this);
        $(li).find('div.wf-property-row-details').addClass('hidden');
    });

    // Open the one we touched.
    if ( action === "open" )
    {
        var li = $(click_obj).closest('li');
        $(li).find('div.wf-property-row-details').removeClass('hidden');
    }


}