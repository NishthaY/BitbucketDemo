<?php
    if ( ! isset($items) ) $items = array();
?>
<div class="card-box">
    <h4 class="m-t-0 m-b-20 header-title"><b>Runtime Values</b></h4>

    <div class="nicescroll pg-option-box" tabindex="5000" style="overflow: hidden; outline: none;">
        <ul class="list-unstyled transaction-list m-r-5">
            <?php
                foreach($items as $item)
                {
                    $label = getArrayStringValue("label", $item);
                    $value = getArrayStringValue("value", $item);
                    $icon = getArrayStringValue("icon", $item);
                    ?>

                    <li>
                        <i class="<?=$icon?>"></i>
                        <span class="tran-text"><?=$label?></span>
                        <span class="pull-right text-muted"><?=$value?></span>
                        <span class="clearfix"></span>
                    </li>

                    <?php
                }
            ?>
        </ul>
    </div>
</div>
