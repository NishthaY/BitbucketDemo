$(function(){

    // Click Handler ( Error Data Cell )
    $(document).on('click', '.danger-cell', function(e){
        BadCellHandler(this, e);
    });
    // Click Handler ( Error Data Cell )
    $(document).on('click', '#wizard_error_form #no_btn', function(e){
        CloseBadCellModal(this, e);
    });

    InitCorrectionTable();

});

function InitCorrectionTable() {

    // NOTE: This is not working correctly.  this throws an error
    // and does not execute the options.  To that end, I pulled the
    // init complete out and below the table init.  Look at this
    // more later.
    try{
        var table = $("#corrections_table");
        if ( ! $(table).hasClass("dataTable") )
        {
            $(table).DataTable(
                {
                    "initComplete": function(settings, json) {
                        $(table).removeClass("hidden");
                        $(table).closest(".card-box").hide();
                        $(table).closest(".card-box").removeClass("hidden");
                        $(table).closest(".card-box").show();
                    },
                    "columnDefs": [
                        { "targets": [0], "width": "100px"},
                    ],
                    "autoWidth": false
                }
            );
        }
    }catch(err){
        //alert(err);
    }


}
function BadCellHandler(click_obj, e) {
    var row = $(click_obj).data("row");
    var column = $(click_obj).data("column-name");
    var url = $(click_obj).closest("div.card-box").data("href");
    url = replaceFor(url, "ROW", row);
    url = replaceFor(url, "COLUMN", column);
    $("#data_error_widget").attr("data-href", url);
    refreshWidget( "data_error_widget", "showForm", "wizard_error_form" );
}
function CloseBadCellModal(click_obj, e) {
    Custombox.close();
}
