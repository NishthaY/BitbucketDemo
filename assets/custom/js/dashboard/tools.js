$(function(){

    refreshWidget("director_statusbar_widget");
    refreshWidget("core_backup_widget");
    refreshWidget("app_options_widget");
    refreshWidget("keypool_widget");
    refreshWidget("pg_options_widget");
    refreshWidget("dyno_widget", "InitDynoWidget");


    $(document).on('click', '#keypool_create_btn', function(e) {
        KeyPoolCreateClickHandler(this, e);
    });
    $(document).on('click', '#core_backup_link', function(e) {
        CoreBackupClickHandler(this, e);
    });
    $(document).on('click', '#core_restore_link', function(e) {
        CoreBackupClickHandler(this, e);
    });
    $(document).on('click', '#core_refresh_link', function(e) {
        refreshWidget("core_backup_widget");
    });
    $(document).on('click', "#refresh_dyno_list", function(e) {
        e.preventDefault();
        RefreshDynoWidget();
    });
    $(document).on('click', ".dyno-details", function(e) {
        DynoClickHandler(this,e);
    });
    $(document).on('click', '#dyno_details_form #no_btn', function(e) {
        hideForm($("#dyno_details_form"), true, true);
    });

});
function KeyPoolCreateClickHandler(click_obj, e)
{
    e.preventDefault();
    var btn = $(click_obj);
    $(btn).addClass("disabled");

    var url = $(click_obj).attr("href");
    var params = {};
    params['url'] = url;
    params['ajax'] = 1;
    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        if ( ! ValidateAjaxResponse(responseHTML, url) ) return;
        var result = JSON.parse(responseHTML);
        refreshWidget("keypool_widget");
    });


}
function DynoClickHandler(click_obj, e) {

    e.preventDefault();
    var widget = $("#dyno_details_widget");
    var url = $(click_obj).attr("href");
    var dyno_name = fRightBack(url, "/");

    // This widget needs to have it's URL updated to include the dyno name.
    url = $(widget).data("href");
    url = $(widget).data("href") + "/" + dyno_name;
    $(widget).attr("data-href", url);

    refreshWidget( "dyno_details_widget", "showForm", "dyno_details_form" );

}
function RefreshDynoWidget() {
    refreshWidget("dyno_widget", "InitDynoWidget");
}
function InitDynoWidget() {

    ConfirmButtonInit();

    try{
        $("#active_dynos").parent().removeClass("hidden");
        if ( ! $("#active_dynos").hasClass("dataTable") )
        {
            $("#active_dynos").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    "stateSave": false,
                    "ordering": false,
                    "language": {
                        "emptyTable":     "No results found."
                    },
                    "iDisplayLength": 5,
                    "lengthMenu": [[5, -1], [5, "All"]]
                }
            );
        }

    }catch(err){}
}
function CoreBackupClickHandler(click_obj, e)
{
    e.preventDefault();
    var url = $(click_obj).attr("href");
    var params = {};
    params['url'] = url;
    params['ajax'] = 1;
    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        if ( ! ValidateAjaxResponse(responseHTML, url) ) return;
        var result = JSON.parse(responseHTML);
        refreshWidget("core_backup_widget");
    });

}
