<?php
    if ( ! isset($company_id) ) $company_id = null;
    if ( ! isset($list) ) $list = "";
?>
<?php
if ( ! empty($list) )
{
    ?>
    <ul class="hidden onload-list">
        <?php
        foreach($list as $item)
        {
            print "<li>{$item}</li>\n";
        }
        ?>
    </ul>
    <?php
}
?>



