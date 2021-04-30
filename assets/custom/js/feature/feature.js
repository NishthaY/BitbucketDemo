$(function(){

    // Click Handler ( Add Features Button )
    $(document).on('click', '.feature-configuration-link', function(e) {
        FeatureConfigurationHandler(this, e);
    });


    $(document).on('click', '#add_feature_btn', function(e) {
        AddTargetableFeature(this, e);
    });

});

function AddTargetableFeature( click_obj, e )
{
    e.preventDefault();
    var widget_name ="targetable_feature_widget";
    refreshWidget( widget_name, "AddTargetableFeatureHelper");
}
function AddTargetableFeatureHelper( )
{
    showForm("targetable_feature_form");
    InitTargetableFeature();
}


function FeatureConfigurationHandler( click_obj, e)
{
    e.preventDefault();

    var widget_name = $(click_obj).data("widget-name");
    if ( getStringValue(widget_name) == "" )
    {
        return;
    }

    var form_name = $(click_obj).data("form-name");
    if ( getStringValue(form_name) == "" )
    {
        return;
    }

    // If we have a TARGET or TARGETTYPE attribute on the
    // click object, then we need to so a search and replace
    // on the WIDGET url for those datapoints.
    var target = $(click_obj).data('target');
    var target_type = $(click_obj).data('targettype');
    if ( getStringValue(target) != '' )
    {
        var widget = $('#' + widget_name);
        var href = $(widget).attr('data-href');
        var template = $(widget).data('template');
        console.log("widget_name: " + widget_name);
        console.log("target: " + target);
        console.log("target_type: " + target_type);
        console.log("href: " + href);
        console.log("template: " + template);
        if ( getStringValue(template) == "" )
        {
            template = href;
            $(widget).attr('data-template', template);
        }
        href = template;
        href = replaceFor(href, "TARGETTYPE", target_type);
        href = replaceFor(href, "TARGET", target);
        $(widget).attr('data-href', href);
        console.log("href: " + $(widget).attr('data-href'));
    }

    refreshWidget( widget_name, "showForm", form_name );
}