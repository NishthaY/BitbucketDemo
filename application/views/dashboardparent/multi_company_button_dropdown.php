<?php
    if ( ! isset($id) ) $id = "";
    if ( ! isset($name) ) $name = $id;
    if ( ! isset($right) ) $right = false;
    if ( ! isset($href) ) $href = "";
    if ( ! isset($label) ) $label = "";
    if ( ! isset($dropdown) ) $dropdown = array();

    $position_class = "pull-left";
    if ( $right ) $position_class = 'pull-right';

    $position_padding_class = "m-r-10";
    if ( $right ) $position_padding_class = "m-l-10";

    $down_arrow = "";
    if ( $dropdown )
    {
        $down_arrow = " ";
    }



?>
<div class="multi-company-top-button-dropdown">
    <button id="<?=$id?>" name="<?=$name?>" data-toggle="dropdown" class="<?=$position_class?> btn w-lg btn-lg waves-effect waves-light btn-default" type="button" data-href="<?=$href?>" formnovalidate ><?=$label?> <i class='ion-arrow-down-b'></i></button>
    <ul role="menu" class="dropdown-menu">
        <?php
        foreach($dropdown as $code=>$display)
        {
            print "<li data-value='{$code}'>{$display}</li>\n";
        }
        ?>
    </ul>
</div>

