$(function(){
    InitReportsTable();

});

function InitReportsTable() {
    try{
        if ( ! $("#draft_table").hasClass("dataTable") )
        {
            $("#draft_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    stateSave: true,
                    "initComplete": function(settings, json) {
                        if ( ! $("#draft_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#draft_table").closest("div.card-box").removeClass("hidden");
                        }
                    }
                }
            );
        }

        if ( ! $("#historical_table").hasClass("dataTable") )
        {
            $("#historical_table").DataTable(
                {
                    "bFilter": true,
                    "bInfo": false,
                    "bPaginate": false,
                    stateSave: true,
                    "initComplete": function(settings, json) {
                        if ( ! $("#historical_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#historical_table").closest("div.card-box").removeClass("hidden");
                        }
                    },
                    "language": {
                        "emptyTable":     "No reports found."
                    }
                }
            );
        }

    }catch(err){}

}
