<?php
if( ! isset($export_id) ) $export_id = "";
if( ! isset($identifier) ) $identifier = "";
if( ! isset($identifier_type) ) $identifier_type = "";
if( ! isset($url_identifier) ) $url_identifier = "";
if( ! isset($status) ) $status = "";


?>

<?php
if ( $status === 'REQUESTED')
{
    ?>
    <span class="action-buttons pull-right nowrap">
        <a class="action-cell-cancel btn btn-white btn-xs waves-light waves-effect" href="<?=base_url('support/exports/cancel/'.$export_id);?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><i class="fa fa-trash m-r-5"></i> Cancel</a>
    </span>
    <?php
}
else if ( $status === 'IN_PROGRESS')
{

}
else if ( $status === 'COMPLETE' )
{
    ?>
    <span class="action-buttons pull-right nowrap">
        <a class="action-cell-delete btn btn-white btn-xs waves-light waves-effect" href="<?=base_url('support/exports/confirm/delete/'.$export_id);?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><i class="fa fa-trash m-r-5"></i> Delete</a>
        <a class="action-cell-download btn btn-white btn-xs waves-light waves-effect" href="<?=base_url('download/export/'.$export_id);?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><i class="fa fa-arrow-down m-r-5"></i> Download</a>
    </span>
    <?php
}
else if ( $status === 'FAILED' )
{
    ?>
    <span class="action-buttons pull-right nowrap">
        <a class="action-cell-delete btn btn-white btn-xs waves-light waves-effect" href="<?=base_url('support/exports/confirm/delete/'.$export_id);?>" data-identifier="<?=$identifier?>" data-identifier_type="<?=$identifier_type?>"><i class="fa fa-trash m-r-5"></i> Delete</a>
    </span>
    <?php
}
else if ( $status === 'NO_RESULTS' )
{

}
?>