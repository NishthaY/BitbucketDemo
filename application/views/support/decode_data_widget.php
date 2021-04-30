<?php

    if ( ! isset($form) ) $form = "";

?>
<div class="card-box table-responsive">
    <h4 class="m-t-0 header-title"><b>Decode Data</b></h4>
    <p class="text-muted">
        Decode a string that was encrypted by this application.<BR>
        Your actions are begin logged.<BR>
        <?=$form?>
    <div class="text-right">
        <a id="encode_data_btn" class="btn btn-sm btn-white m-t-20 pull-left">Encode Data</a>
        <a id="clear_decode_textarea" class="btn btn-sm btn-white m-t-20">Clear</a>
        <a id="submit_decode_textarea" class="btn btn-sm btn-primary m-t-20">Decode</a>
    </div>

    </p>
</div>
