<?php
    if ( ! isset($job_name) ) $job_name = "";
    if ( ! isset($company) ) $company = "";
    if ( ! isset($user) ) $user = "";
    if ( ! isset($failed) ) $failed = "";
    if ( ! isset($job_id) ) $job_id = "";

?>
<tr data-jobid="<?=$job_id?>">
    <td><?=getStringValue($failed)?></td>
    <td><?=getStringValue($company)?></td>
    <td>
        <?=getStringValue($job_name)?>
        <?php if ( IsAuthenticated('support_write') )
        {
            ?>
            <a data-type="clear" class="action-cell-edit btn btn-white btn-xs waves-light waves-effect pull-right" href="<?=base_url()?>support/jobs/clear"><i class='glyphicon glyphicon-remove m-r-5'></i> Clear</a>
            <?php
        }
        ?>
        <a data-type="details" class="action-cell-edit btn btn-white btn-xs waves-light waves-effect pull-right m-r-5" href="<?=base_url()?>support/jobs/detail/<?=$job_id?>"><i class='glyphicon glyphicon-eye-open m-r-5'></i> Details</a>
    </td>
</tr>
