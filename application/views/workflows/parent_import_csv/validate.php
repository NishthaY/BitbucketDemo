<?php

if ( ! isset( $workflow ) ) $workflow = array();
if ( ! isset( $state ) ) $state = array();
if ( ! isset( $properties) ) $properties = "";

$workflow_name  = GetArrayStringValue("Name", $workflow);
$state_name     = GetArrayStringValue("Name", $state);
$running        = GetArrayStringValue("Running", $state);
$completed      = GetArrayStringValue("Complete", $state);
$waiting        = GetArrayStringValue('Waiting', $state);

$running === 't' ? $running = true : $running = false;
$completed === 't' ? $completed = true : $completed = false;
$waiting === 't' ? $waiting = true : $waiting = false;


?>
<?=$properties?>
<?php
if ( $waiting )
{
    // Waiting
    $uri = GetWorkflowStateProperty($workflow_name, $state_name, 'WaitingURI');
    if ( $uri !== '' ) $uri .= "/{$workflow_name}";
    if ( $uri === '' ) $uri = "#";
    ?>
    <a href="<?=base_url($uri);?>" class="ladda-button pull-right btn w-lg btn-primary btn-lg m-r-5" data-style="expand-left"><i class="ion-arrow-right-c"></i> Continue Import</a>
    <?php
}
else if ( ! $running && ! $completed )
{
    // Pending
    ?>
    <a href="#" class="ladda-button a2p-forever-spinner-button pull-right btn w-lg btn-working btn-lg m-r-5" disabled  data-style="expand-left">Validating Data</a>
    <?php

}
else if ( $running && ! $completed )
{
    // Running
    ?>
    <a href="#" class="ladda-button a2p-forever-spinner-button pull-right btn w-lg btn-working btn-lg m-r-5" disabled  data-style="expand-left">Validating Data</a>
    <?php

}else
{
    // Finished
    ?>
    <a href="#" class="ladda-button a2p-forever-spinner-button pull-right btn w-lg btn-working btn-lg m-r-5" disabled  data-style="expand-left">Validating Data</a>
    <?php
}

?>
