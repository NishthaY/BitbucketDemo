$(function(){

    $(document).on('click', '.clickable-header-breadcrumb', function(e) {
        ClickBreadcrumHeader(this, e);
    });


    $(document).on('change', '#view_selector', function(e) {
        var url = $( "#view_selector option:selected" ).val();
        if ( getStringValue(url) )
        {
            location.href = url;
        }
    });

    InitSupportTimerTable();
    $('#view_selector').select2();
});

function InitSupportTimerTable() {
    try{
        if ( ! $("#timers_table").hasClass("dataTable") )
        {
            $("#timers_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    "stateSave": false,
                    "ordering": false,
                    "initComplete": function(settings, json) {
                        if ( ! $("#timers_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#timers_table").closest("div.card-box").removeClass("hidden");
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
function ClickBreadcrumHeader(click_obj, e) {
    var url = $(click_obj).data('href');
    if ( getStringValue(url) != "" )
    {
        location.href = url;
    }
}