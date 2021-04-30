<?php

?>
<div id="developer_tools" class="modal-demo">
    <button type="button" class="close" onclick="Custombox.close();">
        <span>&times;</span><span class="sr-only">Close</span>
    </button>
    <h4 class="custom-modal-title">Sweet! Developer Tools!</h4>
    <div class="custom-modal-text text-left">
        <span><?=pprint_r($_SESSION, "User's Session");?></span>
    </div>
</div>
