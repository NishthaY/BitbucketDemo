<?php
    if ( ! isset($bands) ) $bands = array();

    if ( ! empty($bands) )
    {
        $count = 0;
        foreach($bands as $band)
        {
            // Each input will need a unique name.  This count will allow us to number each.
            $count++;

            // Grab the start and end values for this band.
            $start = GetArrayStringValue('AgeBandStart', $band);
            $end = GetArrayStringValue('AgeBandEnd', $band);

            // Convert the start and end values that represent birth and death to their UI values.
            if ( $start === '0' ) $start = 'Birth';
            if ( $end === '1000' ) $end = 'Death';

            ?>
            <div class="form-inline form-group age-band-row">
                Age <input value="<?=$start?>" name="band<?=$count?>-start" type="text" class="form-control age-band-first"> through <input value="<?=$end?>" name="band<?=$count?>-end" type="text" class="form-control age-band-second">  <span class="row-delete-icon"><i class="ion-close-circled"></i><span>
            </div>
            <?php

        }
    }
?>