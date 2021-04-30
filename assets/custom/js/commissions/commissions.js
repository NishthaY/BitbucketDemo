$(function(){

    $(document).on('change', '#view_selector', function(e) {
        var url = $( "#view_selector option:selected" ).val();
        if ( getStringValue(url) )
        {
            location.href = url;
        }
    });
    $('#view_selector').select2();

    $(document).on('click', '#show_history_btn', function(e) {
        ShowHistoryClickHandler(this,e);
    });
    $(document).on('click', '#hide_history_btn', function(e) {
        HideHistoryClickHandler(this,e);
    });
    $(document).on('click', '.clickable-header-breadcrumb', function(e) {
        ClickBreadcrumHeader(this, e);
    });
    InitLivesTable();

});
function ShowHistoryClickHandler(click_obj,e)
{
    e.preventDefault();

    var show_btn = click_obj;
    var hide_btn = $("#hide_history_btn");

    $(show_btn).addClass("hidden");
    $(hide_btn).removeClass("hidden");

    $("div.commission-history").removeClass("hidden");

}
function HideHistoryClickHandler(click_obj,e)
{
    e.preventDefault();

    var hide_btn = click_obj;
    var show_btn = $("#show_history_btn");

    $(hide_btn).addClass("hidden");
    $(show_btn).removeClass("hidden");

    $("div.commission-history").addClass("hidden");

}
function ClickBreadcrumHeader(click_obj, e) {
    var url = $(click_obj).data('href');
    if ( getStringValue(url) != "" )
    {
        location.href = url;
    }
}
function InitLivesTable() {
    try{
        if ( ! $("#lives_table").hasClass("dataTable") )
        {
            $("#lives_table").DataTable(
                {
                    "bFilter": true,
                    "bInfo": true,
                    "bPaginate": true,
                    "stateSave": false,
                    "ordering": true,
                    "initComplete": function(settings, json) {
                        if ( ! $("#lives_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#lives_table").closest("div.card-box").removeClass("hidden");
                        }
                    },
                    "language": {
                        "emptyTable":     "No results found."
                    },
                    "iDisplayLength": 10
                }
            );
        }

    }catch(err){}
}

