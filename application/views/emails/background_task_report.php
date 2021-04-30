<?php
    if ( !isset($hostname) ) $hostname = "www.advice2pay.com";
    if ( !isset($identifier) ) $identifier = "";
    if ( !isset($identifier_type) ) $identifier_type = "";
    if ( !isset($identifier_name) ) $identifier_name = "";
    if ( !isset($warnings) ) $warnings = "";
    if ( !isset($audit) ) $audit = "";
    if ( !isset($job) ) $job = "";

    $name_label = "IdentifierName";
    $id_label = "IdentifierId";
    if ( $identifier_type === 'company' )
    {
        $name_label = "CompanyName";
        $id_label = "CompanyId";
    }
    else if ( $identifier_type === 'companyparent' )
    {
        $name_label = "ParentName";
        $id_label = "ParentId";
    }

    $job_id = GetArrayStringValue('Id', $job);
    $task = GetArrayStringValue('Controller', $job);
    $failed = GetArrayStringValue('Failed', $job);
    $error = GetArrayStringValue('ErrorMessage', $job);



?>
The background task has been completed.  Below you will find information
about it's progress.
<BR><BR>
<h3>INFO</h3>
<ul>
    <li>TaskName: <?=$task?></li>
    <li><?=$name_label?>: <?=$identifier_name?></li>
    <li><?=$id_label?>: <?=$identifier?></li>
</ul>
<BR>
<?php
if ( GetStringValue($error) !== '' )
{
    ?>
    <h3>ERROR</h3>
    <pre>
        <?=$error?>
    </pre>
    <?php
}
?>
<BR>
<h3>WARNINGS</h3>
<ul>
<?php
if ( empty($warnings) )
{
    ?><li>none</li><?php
}
else
{
   foreach($warnings as $warning)
   {
       ?><li><?=GetStringValue($warning)?></li><?php
   }
}
?>
</ul>
<BR><BR>
<h3>AUDIT</h3>
<ul>
    <?php
    if ( empty($audit) )
    {
        ?><li>none</li><?php
    }
    else
    {
        foreach($audit as $item)
        {
            ?><li><?=GetStringValue($item)?></li><?php
        }
    }
    ?>
</ul>

