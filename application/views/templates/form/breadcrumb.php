<?php
    if ( ! isset ( $labels ) ) $labels = array();
	if ( ! isset ( $links ) ) $links = array();
	if ( ! isset ( $classes ) ) $classes = array();
	if ( ! isset ( $crush ) ) $crush = true;

    $skip = array();
    if ( $crush ) 
    {
        $prev = "";
        for($i=0;$i<count($labels);$i++)
        {
            $label = getArrayStringValue($i, $labels);
            if ( strtoupper($prev) == strtoupper($label) )
            {
                $skip[$i] = $i;
            }
            $prev = $label;
        }
    }
    

?>
<ol class="breadcrumb">
    <?php 
    for($i=0;$i<count($labels);$i++)
    {
        $label = getArrayStringValue($i, $labels);
        $link = getArrayStringValue($i, $links);
        $class = getArrayStringValue($i, $classes);
        
        if ( $link != "" ) { 
            $label = "<a href='{$link}'>{$label}</a>"; 
        }
        if ( $label != "" ) { 
            if ( ! $crush )
            {
                echo "<li class={$class}>{$label}</li>"; 
            }
            else
            {
                if ( ! isset($skip[$i]) )
                {
                    echo "<li class={$class}>{$label}</li>"; 
                }
            }
        }
            
    }
    ?>
</ol>