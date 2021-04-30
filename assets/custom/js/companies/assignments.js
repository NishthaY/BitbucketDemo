$(function(){

    // Click Handler ( Delete Action Icons )
    $(document).on('click', 'table td.action-cell a.action-cell-assign', function(e) {
        AssignUnassignCompanyClickHandler(this, e);
    });

    InitAssignedTable();
    InitManagersTable();
    InitEveryoneTable();


});


function InitAssignedTable() {
    try{
        if ( ! $("#assignment_table").hasClass("dataTable") )
        {
            $("#assignment_table").DataTable(
                {
                    "bFilter": true,
                    "bInfo": false,
                    "bPaginate": false,
                    stateSave: true,
                    "initComplete": function(settings, json) {
                        $("#assignment_table").closest("div.card-box").removeClass("hidden");

                    },
                    "language": {
                        "emptyTable":     "No assignments found."
                    },
                    "iDisplayLength": 50,
                    "lengthMenu": [[5, 10, 50, -1], [5, 10, 50, "All"]]
                }
            );
        }

    }catch(err){}

}
function InitManagersTable() {
    try{
        if ( ! $("#managers_table").hasClass("dataTable") )
        {
            $("#managers_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    stateSave: true,
                    "initComplete": function(settings, json) {
                        $("#managers_table").closest("div.card-box").removeClass("hidden");
                    },
                    "language": {
                        "emptyTable":     "No users found."
                    },
                    "iDisplayLength": 50,
                    "lengthMenu": [[5, 10, 50, -1], [5, 10, 50, "All"]]
                }
            );
        }

    }catch(err){}

}

function InitEveryoneTable() {
    try{
        if ( ! $("#everyone_table").hasClass("dataTable") )
        {
            $("#everyone_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    stateSave: true,
                    "initComplete": function(settings, json) {
                        $("#everyone_table").closest("div.card-box").removeClass("hidden");
                    },
                    "language": {
                        "emptyTable":     "No users found."
                    },
                    "iDisplayLength": 50,
                    "lengthMenu": [[5, 10, 50, -1], [5, 10, 50, "All"]]
                }
            );
        }

    }catch(err){}

}

function AssignUnassignCompanyClickHandler( click_obj, e )
{
    e.preventDefault();

    var url = $(click_obj).attr("href");

    var params = {};
    params.ajax = 1;
    params.company_id = $(click_obj).data('company-id');
    params.user_id = $(click_obj).data('user-id');
    params.url = url;

    $.post( url, securePostVariables(params) ).done(function( responseHTML ) {
        if ( ! ValidateAjaxResponse(responseHTML, url) ) { return; }
        try {
            var result = JSON.parse(responseHTML);
            if ( result['type'] == "success" )
            {
                refreshWidget("assignment_widget", "InitAssignedTable");
            }

        }catch(err){
            AJAXPanic(responseHTML);
        }
    }).fail(function( jqXHR, textStatus, errorThrown ) {
        AJAXPanic(responseHTML);
    });

}