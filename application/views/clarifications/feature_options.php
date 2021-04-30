<?php
    if ( ! isset($clarification_type) ) $clarification_type = "";

    $retro_checked = "";
    if ( $clarification_type === 'retro' ) $retro_checked = " checked ";

    $ignore_checked = "";
    if ( $clarification_type === 'ignore' ) $ignore_checked = " checked ";

?>
<div class="radio radio-primary">
    <input class="preference-item" type="radio" name="clarification_type" id="retro" value="retro" <?=$retro_checked?> >
    <label for="retro">
        <strong>Retro</strong> - Assume all are due to corrections in the source system and apply retro adjustments as necessary.
    </label>
</div>
<div class="radio radio-primary p-t-10">
    <input class="preference-item" type="radio" name="clarification_type" id="ignore" value="ignore" <?=$ignore_checked?> >
    <label for="ignore">
        <strong>Ignore</strong> - Assume all are due to life events  with no adjustments necessary.
    </label>
</div>