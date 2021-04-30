$(function()
{
    // Click Handler ( Form Submit Button Handler )
    $(document).on('click', '#upload_button', function(e) {
        UploadButtonClickHandler(this, e);
    });

    // Change Handler ( Upload File Has Been Selected )
    $(document).on('change', "#upload_button_browse", function(e) {

        // Disable the button once a file is selected.
        var form = $(this).closest('form');
        var button = $(form).find('button:first');
        $(button).addClass('btn-working');
        $(button).prop('disabled', true);

        ValidateFile(this, e);  // s3_upload
    });

    refreshWidget("wf_parent_import_csv_widget", "InitLoadingButtons");

});

function UploadButtonClickHandler(click_obj, e) {
    e.preventDefault();
    var button = $(click_obj);
    var button_name = $(button).attr("id");
    var browse_name = button_name + "_browse";
    $("#" + browse_name).trigger('click');
}
