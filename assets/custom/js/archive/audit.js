$(function(){

    $(document).on('click', '.clickable-header-breadcrumb', function(e) {
        ClickBreadcrumHeader(this, e);
    });

    // Electrify Footable Table
    var footable = $('#audit_accordion').footable();

    // Footable Pagination
	// -----------------------------------------------------------------
	$('#demo-show-entries').change(function (e) {
		e.preventDefault();
		var pageSize = $(this).val();
		$('#audit_accordion').data('page-size', pageSize);
		$('#audit_accordion').trigger('footable_initialized');
	});

    // Footable Accordion
    // -----------------------------------------------------------------
    footable.on('footable_row_expanded', function(e) {
        $('#audit_accordion tbody tr.footable-detail-show').not(e.row).each(function() {
            $('#audit_accordion').data('footable').toggleDetail(this);
        });
    });

    // Footable Search
    // -----------------------------------------------------------------
    $('#audit_search').on('input', function (e) {
        e.preventDefault();
        $('#audit_accordion').footable().trigger('footable_filter', {filter: $(this).val()});
    });

});
function ClickBreadcrumHeader(click_obj, e) {
    var url = $(click_obj).data('href');
    if ( getStringValue(url) != "" )
    {
        location.href = url;
    }
}
