<?php

    if ( ! isset($form) ) $form = "";

?>
<div class="card-box table-responsive hidden">
    <h4 class="m-t-0 header-title"><b>Encode Data</b></h4>
    <p class="text-muted">
        Encode a string specific to the selected entity.<BR>
        <?=$form?>
    <div class="text-right">
        <a id="decode_data_btn" class="btn btn-sm btn-white m-t-20 pull-left">Decode Data</a>
        <a id="clear_encode_textarea" class="btn btn-sm btn-white m-t-20">Clear</a>
        <a id="submit_encode_textarea" class="btn btn-sm btn-primary m-t-20">Encode</a>
    </div>

    </p>
</div>
