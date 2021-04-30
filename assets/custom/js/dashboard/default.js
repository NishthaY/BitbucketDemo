$(function(){

    refreshWidget("spend_details_widget", "InitSpendDetailsTable");
    refreshWidget("spend_widget", "CounterUp", "spend_widget");
    refreshWidget("spend_ytd_widget", "CounterUp", "spend_ytd_widget");
    refreshWidget("spend_washretro_ytd_widget", "CounterUp", "spend_washretro_ytd_widget");
    refreshWidget("spend_washretro_percent_widget", "CounterUp", "spend_washretro_percent_widget");
    refreshWidget("recent_reports_widget", "InitRecentReportsTable");
});
function BackgroundTaskStatusMessageEventHandler( data )
{
    try
    {
        var result = JSON.parse(data);
        var job_id = getStringValue(result['JobId']);
        var words = getStringValue(result['Words']);
        var age = getStringValue(result['Age']);

        var container = $("#background-task-status-message-container");
        var span = $("#background-task-status-message");
        $(span).text(words);

        if ( getStringValue(words) == "" )
        {
            $(container).addClass("hidden");
        }
        else
        {
            $(container).removeClass("hidden");
        }

    }
    catch(err)
    {
        //alert(err);
    }

}
function InitSpendDetailsTable() {
    try{

        if ( ! $("#spend_details_table").hasClass("dataTable") )
        {
            $("#spend_details_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    "stateSave": false,
                    "ordering": false,
                    "initComplete": function(settings, json) {
                        if ( ! $("#spend_details_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#spend_details_table").closest("div.card-box").removeClass("hidden");
                        }
                    },
                    "language": {
                        "emptyTable":     "No results found."
                    },
                    "iDisplayLength": -1,
                    "lengthMenu": [[-1], ["All"]]
                }
            );
        }

    }catch(err){}
}
function InitRecentReportsTable() {
    try{

        if ( ! $("#recent_reports_table").hasClass("dataTable") )
        {
            $("#recent_reports_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    "stateSave": false,
                    "ordering": false,
                    "initComplete": function(settings, json) {
                        if ( ! $("#recent_reports_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#recent_reports_table").closest("div.card-box").removeClass("hidden");
                        }
                    },
                    "language": {
                        "emptyTable":     "No results found."
                    },
                    "iDisplayLength": -1,
                    "lengthMenu": [[-1], ["All"]]
                }
            );
        }

    }catch(err){}
}
function CounterUp( widget_name ) {
    /*
    var widget = $("#"+widget_name);
    if ( $(widget).length != 0 ) {
        var counter = $(widget).find(".counter:first");
        $(counter).counterUp({
            delay: 100,
            time: 1200
        });
    }
    */
}
