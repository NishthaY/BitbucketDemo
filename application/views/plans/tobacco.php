<?php
    if ( ! isset($details) ) $details = array();

    $has_attribute   = getArrayStringValue("has_attribute", $details);
    $mapped     = getArrayStringValue("mapped", $details);
    $ignored = getArrayStringValue("ignored", $details);
    $column_mapped = getArrayStringValue("column_mapped", $details);

?>
<?php
    if ( $has_attribute == "t" && $mapped == "t" && $column_mapped == "t" && $ignored == "t" ){
        ?><a class='tobacco-link btn btn-white waves-light waves-effect m-b-5' href="#">Tobacco Ignored</a><?php
    }
?>
<?php
    if ( $has_attribute == "t" && $mapped == "t" && $column_mapped == "t" && $ignored == "f" ){
        ?><a class='tobacco-link btn btn-white waves-light waves-effect m-b-5' href="#">Tobacco Settings</a><?php
    }
?>
