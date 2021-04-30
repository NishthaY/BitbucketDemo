$(function()
{

    // NOTE: I do not think this JS file is being used.


    // Click Handler ( Form Submit Button Handler )
    $(document).on('click', '#upload_button', function(e) {
        //UploadButtonClickHandler(this, e);
    });

    // Start Over - This button, when clicked, will delete this workflow.
    $(document).on('click', '#parent_upload_start_over_btn', function(e) {
        ParentUploadStartOverClickHandler(this, e);
    });

    refreshWidget("parent_upload_widget");
});

function UploadButtonClickHandler(click_obj, e)
{
    e.preventDefault();
    var button = $(click_obj);

    // BRIAN, this did not work.
    // If we are disabled, don't register the click.
    if ( ! $(button).hasClass('disabled') )
    {
        var button_name = $(button).attr("id");
        var browse_name = button_name + "_browse";
        $("#" + browse_name).trigger('click');
    }


}
function ParentUploadStartOverClickHandler(click_obj, e)
{
    e.preventDefault();
    alert("Please start over.");
}