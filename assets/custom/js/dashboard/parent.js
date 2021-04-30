$(function(){
    refreshWidget("companies_widget", "InitCompanyParentDashboardCompanyTable");
});

function InitCompanyParentDashboardCompanyTable() {
    try{
        if ( ! $("#parent_companies").hasClass("dataTable") )
        {
            $("#parent_companies").DataTable(
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
