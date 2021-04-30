$(function(){

    $(document).on('change', '#view_selector', function(e) {
        var url = $( "#view_selector option:selected" ).val();
        if ( getStringValue(url) )
        {
            location.href = url;
        }
    });

    InitRecentChangesTable();
    $('#view_selector').select2();
});

function InitRecentChangesTable() {
    try{
        if ( ! $("#recent_changes_table").hasClass("dataTable") )
        {
            $("#recent_changes_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    "stateSave": false,
                    "ordering": false,
                    "initComplete": function(settings, json) {
                        if ( ! $("#recent_changes_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#recent_changes_table").closest("div.card-box").removeClass("hidden");
                        }
                    },
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
