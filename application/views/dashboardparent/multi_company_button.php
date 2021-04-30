<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($name) ) $name = $id;
    if ( ! isset($right) ) $right = false;
    if ( ! isset($href) ) $href = "";
    if ( ! isset($label) ) $label = "";

    $position_class = "pull-left";
    if ( $right ) $position_class = 'pull-right';

    $position_padding_class = "m-r-10";
    if ( $right ) $position_padding_class = "m-l-10";



?>
<button id="<?=$id?>" name="<?=$name?>" class="<?=$position_class?> btn w-lg btn-lg waves-effect waves-light btn-default" type="button" data-href="<?=$href?>" formnovalidate ><?=$label?></button>
