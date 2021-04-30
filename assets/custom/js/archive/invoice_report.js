$(function(){
    InitInvoiceReportTable();
});

function InitInvoiceReportTable() {
    try{
        if ( ! $("#invoice_report_details_table").hasClass("dataTable") )
        {
            $("#invoice_report_details_table").DataTable(
                {
                    "bFilter": true,
                    "bInfo": true,
                    "bPaginate": false,
                    "stateSave": false,
                    "ordering": true,
                    "initComplete": function(settings, json) {
                        if ( ! $("#invoice_report_details_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#invoice_report_details_table").closest("div.card-box").removeClass("hidden");
                        }
                    },
                    "language": {
                        "emptyTable":     "No results found."
                    },
                    "iDisplayLength": 5,
                    "lengthMenu": [[5, -1], [5, "All"]],
                    "order": [[ 1, "asc" ]],
                }
            );
        }

    }catch(err){}
}