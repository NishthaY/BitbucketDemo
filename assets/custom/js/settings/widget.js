$(function(){

    $(document).on('click', 'li.settings-widget-row-parent > .settings-widget-button', function(e) {
        SettingsWidgetOpenClickHandler(this, e);
    });


});

function SettingsWidgetOpenClickHandler( click_obj, e )
{
    var anchor = $(click_obj);
    var li = $(anchor).closest('li');
    var ul = $(li).closest('ul');
    var title = $(li).find('span.tran-text').text();

    if ( $(li).hasClass('settings-widget-row-parent') )
    {
        SettingsWidgetToggleGroup(ul, li, title);
    }



}
function SettingsWidgetCloseAll( list )
{
    var ul = $(list);
    $(ul).find('li.settings-widget-row-parent.open').each(function(){
        var li = $(this);
        var title = $(li).find('span.tran-text').text();
        SettingsWidgetCloseGroup(ul, li, title);
    });
}
function SettingsWidgetToggleGroup( list, list_item, title )
{
    var ul = $(list);
    var li = $(list_item);

    if ( $(li).hasClass('open') ) {
        SettingsWidgetCloseGroup(list, list_item, title);
    }else
    {
        SettingsWidgetOpenGroup(list, list_item, title);
    }
}
function SettingsWidgetOpenGroup( list, list_item, title )
{
    var ul = $(list);
    var li = $(list_item);

    SettingsWidgetCloseAll(ul);
    if ( ! $(li).hasClass('open') )
    {
        $(li).addClass('open');
        $(li).find(".fa-chevron-circle-down").closest("a").addClass("hidden");
        $(li).find(".fa-chevron-circle-up").closest("a").removeClass("hidden");

        $(ul).find("li[data-parent='"+title+"']").each(function(){
            $(this).removeClass('hidden');
        });
    }
}
function SettingsWidgetCloseGroup( list, list_item, title )
{
    var ul = $(list);
    var li = $(list_item);

    if ( $(li).hasClass('open') )
    {
        $(li).removeClass('open');
        $(li).find(".fa-chevron-circle-down").closest("a").removeClass("hidden");
        $(li).find(".fa-chevron-circle-up").closest("a").addClass("hidden");

        $(ul).find("li[data-parent='"+title+"']").each(function(){
            $(this).addClass('hidden');
        });
    }
}