<?php
    if ( ! isset($companyparent_name) ) $companyparent_name = "";
?>
<div class="confirmation-div">
    Looks like <?=$companyparent_name?> has background tasks running or are about to run.  We need to wait for these to finish first before you can rollback data.  Please try again again later.
</div>
