<?php
    if ( ! isset($links) ) $links = array();


?>
<div class="form-group m-t-30 m-b-0" <?=$attributes?> >
    <div class="col-sm-12">
        <?php
        foreach($links as $link)
        {
            $label = getArrayStringValue('label', $link);
            $href = getArrayStringValue('href', $link);
            $icon_class = getArrayStringValue('icon_class', $link);
            $attributes = getArrayStringValue('attributes', $link);
            ?>
            <a href="<?=$href?>" class="text-dark" data-type='form-link' <?=$attributes?> ><i class="<?=$icon_class?> m-r-5"></i> <?=$label?></a><br>
            <?php
        }
        ?>

    </div>
</div>
