<?php
    if ( ! isset ( $prerender) ) $prerender= "";
    if ( ! isset ( $id ) ) $id = "";
    if ( ! isset ( $class ) ) $class = "";
    if ( ! isset ($hidden_flg) ) $hidden_flg = false;

    $hidden = "";
    if ( $hidden_flg ) $hidden = "hidden";

?>
<div id="<?=$id?>" class="comment-box-row <?=$class?> <?=$hidden?>">
    <?=$prerender?>
</div>
