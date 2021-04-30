<?php

    if ( ! isset( $href ) ) $href = "";
    if ( ! isset( $message) ) $message = "Processing Request";
    if ( ! isset( $icon ) ) $icon = "arrow";
    if ( ! isset( $id ) ) $id = "";


    if ( $icon == "arrow") $icon = "<i class='ion-arrow-right-c'></i>";

?>
<?php
    if ( $href == "" )
    {
        ?>
        <div class='' style='margin-top: 10px;'>
            <a href="#" class="ladda-button a2p-forever-spinner-button pull-right btn w-lg btn-working btn-lg" disabled  data-style="expand-left"><?=$message?></a>
        </div>
        <?php
    }
?>
<?php
    if ( $href != "" )
    {
        ?>
        <div class='' style='margin-top: 10px;'>
            <a id="<?=$id?>"  href="<?=$href?>" data-style="expand-left" class="pull-right btn w-lg btn-default btn-lg waves-effect waves-light" ><?=$icon?> <?=$message?></a>
        </div>
        <?php
    }
?>
