$(function(){
    refreshWidget("failed_jobs_widget");
    refreshWidget("waiting_jobs_widget");
    refreshWidget("running_jobs_widget");
    refreshWidget("running_jobs_table_widget", "InitAdminDashboardRunningJobsTable");
    refreshWidget("recent_changeto", "InitAdminDashboardChangeToHistoryTable");


    $(document).on('click', '.clickable-widget', function(e) {
        SwitchSupportTable( this, e );
    });

    $(document).on('click', '.action-cell-edit', function(e) {
        ActionCellHandler(this,e);
    });

    $(document).on('click', '#job_details_form #no_btn', function(e) {
        hideForm($("#job_details_form"), true, true);
    });

});
function AdminJobStatusUpdateHandler( data )
{
    try
    {
        var result = JSON.parse(data);
        var job_id = getStringValue(result['JobId']);
        var words = getStringValue(result['Words']);
        var age = getStringValue(result['Age']);


        // Find the table row that matches this job id in the
        // running_jobs table and update the text of the status-message
        // table cell.
        var table = $("#running_jobs_table");
        var row = $(table).find('tr[data-jobid="'+job_id+'"]');

        // Update the row status.
        var td_status = $(row).find('.status-message:first');
        $(td_status).text(words);

        // Update the row age.
        var td_age = $(row).find('.age:first');
        $(td_age).text(age);


    }
    catch(err)
    {
        //alert(err);
    }

}
function ActionCellHandler( click_obj, e ) {
    e.preventDefault();
    var job_id = $(click_obj).closest("tr").data("jobid");
    var type = $(click_obj).data("type");
    var url = $(click_obj).attr("href");

    var params = {};
    params.ajax = 1;
    params.url = url;
    params.jobid = job_id;

    if ( type == "clear" )
    {
        $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
            if ( ! ValidateAjaxResponse(responseHTML ) ) { return; }
            try{
                refreshWidget("failed_jobs_table_widget", "InitAdminDashboardFailedJobsTable");
            }catch(err){
                alert("failed");
                return;
            }
        });
    }

    if ( type == "details" )
    {
        var widget_name = "job_details_widget"
        $("#" + widget_name).attr("data-href", url);
        refreshWidget( widget_name, "showForm", "job_details_form" );
    }


    //alert("jobid["+job_id+"], type["+type+"], url["+url+"]");
}
function SwitchSupportTable( click_obj, e ) {
    var id = $(click_obj).attr("id");
    $("#running_jobs_table").closest(".card-box").addClass("hidden");
    $("#failed_jobs_table").closest(".card-box").addClass("hidden");
    $("#waiting_jobs_table").closest(".card-box").addClass("hidden");
    if ( id == "running_jobs" ) {
        refreshWidget("running_jobs_widget");
        refreshWidget("running_jobs_table_widget", "InitAdminDashboardRunningJobsTable");
    }
    if ( id == "waiting_jobs" ) {
        refreshWidget("waiting_jobs_widget");
        refreshWidget("waiting_jobs_table_widget", "InitAdminDashboardWaitingJobsTable");
    }
    if ( id == "failed_jobs" ) {
        refreshWidget("failed_jobs_widget");
        refreshWidget("failed_jobs_table_widget", "InitAdminDashboardFailedJobsTable");
    }
    refreshWidget("failed_jobs_widget");
    refreshWidget("waiting_jobs_widget");
    refreshWidget("running_jobs_widget");


}

function InitAdminDashboardChangeToHistoryTable() {
    try{
        if ( ! $("#admin_recent").hasClass("dataTable") )
        {
            $("#admin_recent").DataTable(
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

function InitAdminDashboardRunningJobsTable() {
    try{
        if ( ! $("#running_jobs_table").hasClass("dataTable") )
        {
            $("#running_jobs_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    "stateSave": false,
                    "ordering": false,
                    "initComplete": function(settings, json) {
                        $("#running_jobs_table").closest("div.card-box").removeClass("hidden");
                    },
                    "language": {
                        "emptyTable":     "No results found."
                    }
                }
            );
        }

    }catch(err){}
}
function InitAdminDashboardFailedJobsTable() {
    try{
        if ( ! $("#failed_jobs_table").hasClass("dataTable") )
        {
            $("#failed_jobs_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    "stateSave": false,
                    "ordering": false,
                    "initComplete": function(settings, json) {
                        $("#failed_jobs_table").closest("div.card-box").removeClass("hidden");
                    },
                    "language": {
                        "emptyTable":     "No results found."
                    }
                }
            );
        }

    }catch(err){}
}
function InitAdminDashboardWaitingJobsTable() {
    try{
        if ( ! $("#waiting_jobs_table").hasClass("dataTable") )
        {
            $("#waiting_jobs_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    "stateSave": false,
                    "ordering": false,
                    "initComplete": function(settings, json) {
                        $("#waiting_jobs_table").closest("div.card-box").removeClass("hidden");
                    },
                    "language": {
                        "emptyTable":     "No running jobs found."
                    }
                }
            );
        }

    }catch(err){}
}
