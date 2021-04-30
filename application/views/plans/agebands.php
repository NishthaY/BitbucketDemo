<?php
    if ( ! isset($details) ) $details = array();

    $bandable   = getArrayStringValue("bandable", $details);
    $mapped     = getArrayStringValue("mapped", $details);
    $band_count = getArrayIntValue("count", $details);
    $ignored = getArrayStringValue("ignored", $details);
    $id = getArrayStringValue("id", $details);

?>
<?php
    if ( $mapped == "f" ){
        ?><div class="plansetting-text"><i>Plan Type Not Defined</i></div><?php
    }
?>
<?php
    if ( $bandable == "t" && $mapped == "t" && $ignored == "t" ){
        ?><a class='ageband-link btn btn-white waves-light waves-effect m-b-5' href="#">Age Band Ignored</a><?php
    }
?>
<?php
    if ( $bandable == "t" && $mapped == "t" && $ignored == "f" && $band_count == 0 ){
        ?><a class='ageband-link btn btn-white waves-light waves-effect m-b-5' href="#"><?=RenderViewAsString("plans/inline_question_indicator")?> Age Bands Not Defined</a><?php
    }
?>
<?php
    if ( $bandable == "t" && $mapped == "t" && $ignored == "f" && $band_count > 0 )
    {
        ?><a class='ageband-link btn btn-white waves-light waves-effect m-b-5' href="#"><?=$band_count?> Age Bands Defined</a><?php
    }
?>
