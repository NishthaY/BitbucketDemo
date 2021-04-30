<?php
    if ( ! isset($tokens) ) $tokens = array();
?>
<label for="name">Tokens</label>
<div class="form-group has-feedback form-border" >
    <div class="p-b-10"><small>Check to see if the record contains the specified tokens below.  The search is case insensitive and if a match is found, the import record will be flagged.  </small></div>

    <div class="input-group p-b-15">
        <input type="search" class="form-control" placeholder="Add a token to find.">
        <span class="input-group-btn">
            <button id='add_token_button' class="btn btn-white" type="button"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
        </span>
    </div>

    <div id="tokens">
        <div class="checkbox-wrapper p-l-10 hidden">
            <div class="checkbox_outer">
                <input type="checkbox" name="token[]" value="VALUE" > <span class="uiform-checkbox-inline-desc">DISPLAY</span>
                <span></span>
            </div>
        </div>
        <?php
        foreach($tokens as $token)
        {
            $checkbox_name = 'tokens';
            $value = GetArrayStringValue('Token', $token);
            $display = GetArrayStringValue('UserDescription', $token);
            ?>

            <div class="checkbox-wrapper p-l-10">
                <div class="checkbox_outer">
                    <?php
                    $label = "{$value} ({$display})";
                    if ( $value == '' ) $label = "No data.";
                    ?>
                    <input type="checkbox" name="token[]" value="<?=$display?>" checked > <span class="uiform-checkbox-inline-desc"><?=$label?></span>
                    <span></span>
                </div>
            </div>
            <?php
        }
        ?>
    </div>

</div>