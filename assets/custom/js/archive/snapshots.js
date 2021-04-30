$(function(){

    $(document).on('click', '#additional_information', function(e) {
        e.preventDefault();
        showForm("more_info_form");
    });
    $(document).on('click', '#more_info_form_submit_button', function(e) {
        e.preventDefault();
        hideForm("more_info_form");
    });
    $(document).on('click', '#download_source', function(e) {
        StreamFile(this, e);
    });
    $(document).on('click', '#download_snapshot', function(e) {
        StreamFile(this, e);
    });
    $(document).on('click', '.clickable-header-breadcrumb', function(e) {
        ClickBreadcrumHeader(this, e);
    });

    $(document).on('click', "#generate_snapshot", function(e) {
        GenerateSnapshotsClickHandler(this, e);
    });

    InitHistoryTable();

    // Electrify Footable Table
    var footable = $('#snapshot_accordion').footable();

    // Footable Pagination
	// -----------------------------------------------------------------
	$('#demo-show-entries').change(function (e) {
		e.preventDefault();
		var pageSize = $(this).val();
		$('#snapshot_accordion').data('page-size', pageSize);
		$('#snapshot_accordion').trigger('footable_initialized');
	});

    // Footable Accordion
    // -----------------------------------------------------------------
    footable.on('footable_row_expanded', function(e) {
        $('#snapshot_accordion tbody tr.footable-detail-show').not(e.row).each(function() {
            $('#snapshot_accordion').data('footable').toggleDetail(this);
        });
    });

    // Footable Search
    // -----------------------------------------------------------------
    $('#snapshot_search').on('input', function (e) {
        e.preventDefault();
        $('#snapshot_accordion').footable().trigger('footable_filter', {filter: $(this).val()});
    });

});

function GenerateSnapshotsClickHandler( click_obj, e ) {

    e.preventDefault()
    var url = $(click_obj).attr("href");
    var params = {ajax: '1'}
    ShowSpinner("Generating");

    $.post( url, securePostVariables(params) ).done(function( responseHTML )
    {
        try
        {
            // Validate the ajax response.
            if ( ! ValidateAjaxResponse(responseHTML, url) ) {
                throw "Unexpected response from the server.  Looks like we got back a page template rather than an AJAX response.";
            }
            var result = JSON.parse(responseHTML);
            if ( ! result["status"] ) { throw result['message']; }
            if ( result['type'] == "danger" ) { throw result['message']; }
            if( getStringValue(result['href']) != "" )
            {
                alert( getStringValue(result['href']) );
                location.href = getStringValue(result['href']);
                return;
            }            
        }
        catch(err)
        {
            if( getStringValue(err) == "" ) { err = "Unexpected situation."; }
            HideSpinner();
            console.log("GenerateSnapshots Failed: " + err);
        }


    }).fail(function( jqXHR, textStatus, errorThrown ) {
        HideSpinner();
    });


}

function ClickBreadcrumHeader(click_obj, e) {
    var url = $(click_obj).data('href');
    if ( getStringValue(url) != "" )
    {
        location.href = url;
    }
}
function StreamFile(click_obj, e){
    if ( $( click_obj ).hasClass("disabled") ) return;
    var url = getStringValue($(click_obj).data("href"));
    if ( url != "" ) location.href = url;
}
function InitHistoryTable() {
    try{
        if ( ! $("#snapshot_table").hasClass("dataTable") )
        {
            $("#snapshot_table").DataTable(
                {
                    "bFilter": true,
                    "bInfo": true,
                    "bPaginate": true,
                    stateSave: true,
                    "initComplete": function(settings, json) {
                        if ( ! $("#snapshot_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#snapshot_table").closest("div.card-box").removeClass("hidden");
                        }
                    }
                }
            );
        }

        if ( ! $("#snapshot_draft_table").hasClass("dataTable") )
        {
            $("#snapshot_draft_table").DataTable(
                {
                    "bFilter": false,
                    "bInfo": false,
                    "bPaginate": false,
                    stateSave: true,
                    "initComplete": function(settings, json) {
                        if ( ! $("#snapshot_draft_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#snapshot_draft_table").closest("div.card-box").removeClass("hidden");
                        }
                    }
                }
            );
        }

        if ( ! $("#snapshot_historical_table").hasClass("dataTable") )
        {
            $("#snapshot_historical_table").DataTable(
                {
                    "bFilter": true,
                    "bInfo": false,
                    "bPaginate": false,
                    stateSave: true,
                    "initComplete": function(settings, json) {
                        if ( ! $("#snapshot_historical_table").find("td:first").hasClass("dataTables_empty") ) {
                            $("#snapshot_historical_table").closest("div.card-box").removeClass("hidden");
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
